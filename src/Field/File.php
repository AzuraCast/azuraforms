<?php
namespace AzuraForms\Field;

use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Psr\Http\Message\UploadedFileInterface;

use const UPLOAD_ERR_OK;

class File extends AbstractField
{
    protected array $mime_types = [
        'image' => [
            'image/gif', 'image/gi_', 'image/png', 'application/png', 'application/x-png',
            'image/jp_', 'application/jpg', 'application/x-jpg', 'image/pjpeg', 'image/jpeg'
        ],
        'document' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/mspowerpoint', 'application/powerpoint', 'application/vnd.ms-powerpoint',
            'application/x-mspowerpoint', 'application/plain', 'text/plain', 'application/pdf',
            'application/x-pdf', 'application/acrobat', 'text/pdf', 'text/x-pdf', 'application/msword',
            'pplication/vnd.ms-excel', 'application/msexcel', 'application/doc',
            'application/vnd.oasis.opendocument.text', 'application/x-vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.spreadsheet', 'application/x-vnd.oasis.opendocument.spreadsheet',
            'application/vnd.oasis.opendocument.presentation', 'application/x-vnd.oasis.opendocument.presentation'
        ],
        'archive' => [
            'application/x-compressed', 'application/gzip-compressed', 'gzip/document',
            'application/x-zip-compressed', 'application/zip', 'multipart/x-zip',
            'application/tar', 'application/x-tar', 'applicaton/x-gtar', 'multipart/x-tar',
            'application/gzip', 'application/x-gzip', 'application/x-gunzip', 'application/gzipped'
        ]
    ];

    protected array $error_types = [
        'image' => 'File must be an image, e.g example.jpg or example.gif',
        'archive' => 'File must be an archive, e.g example.zip or example.tar',
        'document' => 'File must be a document, e.g example.doc or example.pdf',
        'all' => 'File must be a document, archive or image.',
        'custom' => 'File is invalid.'
    ];

    public function configure(array $config = []): void
    {
        parent::configure($config);

        $file_options = [
            'max_size' => 10 * 1024 * 1024,
            'width' => 1600,
            'height' => 1600,
            'min_width' => 0,
            'min_height' => 0,
            'type' => 'all',
        ];

        foreach($file_options as $option_key => $option_default) {
            $this->options[$option_key] = $this->attributes[$option_key] ?? $option_default;
            unset($this->attributes[$option_key]);
        }

        if (is_array($this->options['type'])) {
            $this->options['mime_types'] = $this->options['type'];
            $this->options['type'] = 'custom';
        } else if (isset($this->mime_types[$this->options['type']])) {
            $this->options['mime_types'] = $this->mime_types[$this->options['type']];
        }
    }

    public function getField(string $form_name): ?string
    {
        [$attribute_string, $class] = $this->_attributeString();

        return sprintf('<input type="file" name="%1$s" id="%2$s_%1$s" class="%3$s"/>',
            $this->getFullName(),
            $form_name,
            $class
        );
    }

    protected function validateValue($value): bool
    {
        if (null === $value && $this->options['required']) {
            $this->errors[] = 'This field is required.';
            return false;
        }

        if ($value instanceof UploadedFileInterface) {
            $fileSize = $value->getSize() ?? 0;
            $fileError = $value->getError();

            if (UPLOAD_ERR_OK !== $fileError || 0 === $fileSize) {
                $this->errors[] = 'Error uploading file.';
                return false;
            }

            if ($fileSize > $this->options['max_size']) {
                $this->errors[] = sprintf(
                    'File must be less than %sMB.',
                    round($this->options['max_size'] / 1024 / 1024, 2)
                );
                return false;
            }

            if ('all' !== $this->options['type']) {
                $mimeType = (new FinfoMimeTypeDetector())->detectMimeType(
                    $value->getClientFilename() ?? 'noname',
                    $value->getStream()->getContents()
                );

                if (!in_array($mimeType, $this->options['mime_types'], true)) {
                    $this->errors[] = $this->error_types[$this->options['type']];
                    return false;
                }
            }
        }

        return true;
    }

    protected function _attributeString(): array
    {
        $class = '';

        if (!empty($this->error)) {
            $class = 'error';
        }

        $attribute_string = '';
        foreach ($this->attributes as $attribute => $val) {
            if ($attribute === 'class') {
                $class .= ' ' . $val;
            } else if ($val !== false) {
                $attribute_string .= ' '.($val === true ? $attribute : "$attribute=\"$val\"");
            }
        }

        return [$attribute_string, $class];
    }
}
