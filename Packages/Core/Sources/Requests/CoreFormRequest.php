<?php
namespace Packages\Core\Sources\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Packages\Core\Sources\Exceptions\ValidationException;
use Packages\Core\Sources\Response\Response;

class CoreFormRequest extends FormRequest
{
    use FormatMessageTrait;

    public function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            
            $json = [
                'status'    => Response::STATUS_VALIDATION_ERROR,
                'message'   => 'Validation error.',
                'data'      => $this->customErrors($validator->errors()->getMessages()),
            ];

            $response = new JsonResponse($json, 400 );
            throw (new ValidationException($validator, $response))->status(400);
        }

        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());

        // $response = new JsonResponse($json, 400 );
        // throw (new ValidationException($validator, $response))->status(400);
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        if($validator->passes()){
            $this->afterPasses();
        }
    }

    /**
     * Format data after passed
     */
    protected function afterPasses(){}

    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}