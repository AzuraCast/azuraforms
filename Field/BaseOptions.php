<?php
namespace NibbleForms\Field;

abstract class BaseOptions extends FormField 
{

    protected $label, $options = array();
    protected $required = true;
    protected $false_values = array();
    public $error = array();

    public function __construct($label, $attributes = array()) 
    {
        $this->label = $label;
        if(isset($attributes["choices"])){
            $this->options = $attributes["choices"];
        }
        if (isset($attributes['required'])){
            $this->required = $attributes['required'];
        }
        if (isset($attributes['false_values'])){
            $this->false_values = $attributes['false_values'];
        }
    }

    public function getAttributeString($val) 
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
    
    public function validate($val){
        print_r($this->false_values);
        echo $val;
        if (in_array($val, $this->false_values)){
            $this->error[] = "$val is not a valid choice";
        }
    }

}