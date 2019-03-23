<?php 
namespace Packages\Core\Sources\Response;
use Illuminate\Support\Facades\Validator;

trait CoreValidation
{
    /**
     * @var Response via web or api
     */
    protected $prefixWeb = false;

	/**
     * rules of request params
     * @var array
     */
    protected $rules = [];

    /**
     * custom message default validations system
     * @var array
     */
    protected $messages = [];


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, $this->getRules(), $this->getMessages());
    }

    /**
     * Send response json if request fail validations
     * @param type $validations 
     * @return type
     */
    protected function sendResponseFailValidation( array $data)
    {
        $validations = $this->validator($data);
        if($this->prefixWeb === true)
            $this->validator($data)->validate();
        else
        {
            if ($validations->fails()){
                $this->response($validations->getMessageBag()->toArray(),\Packages\Core\Sources\Response\Response::STATUS_VALIDATION_ERROR);
                exit;
            }
        }
    }

    /**
     * Set rules
     * @param type|array $rules 
     * @return type
     */
    protected function setRules($rules = [])
    {
        $this->rules = $rules;
    }

    /**
     * Get rules
     * @return type
     */
    protected function getRules()
    {
        return $this->rules;
    }

    /**
     * Set custom messages
     * @param type|array $messages 
     * @return type
     */
    protected function setMessages($messages = [])
    {
        $this->messages = $messages;
    }

    /**
     * Get custom messages
     * @return type
     */
    protected function getMessages()
    {
        return $this->messages;
    }
}