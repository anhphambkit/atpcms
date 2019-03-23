<?php
namespace Packages\Core\Sources\Requests;

use Illuminate\Support\Str;

trait FormatMessageTrait
{
	/**
     * Description
     * @param type &$errors 
     * @return type
     */
    protected function customErrors($errors)
    {
        foreach($errors as $error => &$value)
        {
            $this->formatMessage($error, $value);
        }

        return $errors;
    }

    /**
     * Description
     * @param type $error 
     * @param type &$value 
     * @return type
     */
    protected function formatMessage($error, &$value)
    {
        $str = '';
        $keys = explode('_', $error);
        $replace = '';
        foreach($keys as $key)
        {
            $replace .= ' ' . $key;
            $specialKey = explode('.', $key);
            if(count($specialKey) > 1)
            {
                //TODO
                foreach($specialKey as $key)
                {
                    $str  .= ' ' . Str::ucfirst($key);
                }
                continue;
            }
            $str .= ' ' . Str::ucfirst($key);
        }
        $value = str_replace(ltrim($replace),ltrim($str),$value);
    }
}