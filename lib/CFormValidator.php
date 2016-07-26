<?php

namespace lib;

use \lib\ConfigLoader;

class CFormValidator
{
    /**
     * [validate description]
     * @param  array  $input       [description]
     * @param  string $patten      [description]
     * @param  string $config_file [description]
     * @return [type]              [description]
     */
    public function validate($input = array(), $patten = 'default', $config_file = '')
    {
        if (empty($config_file)) {
            $rules = ConfigLoader::load('form_validate_rule');
            if (empty($rules[$patten])) {
                return array('patten' => "Patten '{$patten}' doesn't exist.");
            }
        }

        if (!is_array($input)) {
            return array('Input' => "Input Data must be array.");
        }

        require_once EXT_PATH . "/formvalidator.php";
        $validator = new \FormValidator();

        foreach ($input as $k => $v) {
            if (!isset($rules[$patten][$v])) {
                continue;
            }
            foreach ($rules[$patten][$v] as $restrict => $message) {
                $validator->addValidation($v, $restrict, $message);
            }
        }

        if (!$validator->ValidateForm()) {
            return $validator->GetErrors();
        }

        return true;
    }
}
