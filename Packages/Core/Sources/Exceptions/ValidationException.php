<?php
namespace Packages\Core\Sources\Exceptions;

use Illuminate\Validation\ValidationException as Exception;

class ValidationException extends Exception {

    /**
     * Get all of the validation error messages.
     *
     * @return array
     */
    public function errors()
    {
        return $this->customErrors($this->validator->errors()->messages());
    }
    
}