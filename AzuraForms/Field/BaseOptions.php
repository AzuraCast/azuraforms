<?php
namespace AzuraForms\Field;

abstract class BaseOptions extends AbstractField
{
    protected $options = [];
    protected $false_values = [];

    public function __construct($label, $attributes = array())
    {
        $this->label = $label;
        if (isset($attributes["choices"])) {
            $this->options = $attributes["choices"];
        }
        if (isset($attributes['required'])) {
            $this->required = $attributes['required'];
        }
    }

    protected function _getAttributeString($val)
    {
        $attribute_string = '';
        if (is_array($val)) {
            $attributes = $val;
            $val = $val[0];
            unset($attributes[0]);
            foreach ($attributes as $attribute => $arg) {
                $attribute_string .= $arg ? ' ' . ($arg === true ? $attribute : "$attribute=\"$arg\"") : '';
            }
        }

        return array('val' => $val, 'string' => $attribute_string);
    }
}