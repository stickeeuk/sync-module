<?php

namespace Stickee\Sync\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Stickee\Sync\Helpers;

class GetFileHashesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'config_name' => [
                'required',
                'string',
                Rule::in(array_keys(Helpers::serverConfig('directories'))),
            ],
        ];
    }

    /**
     * Get validation error messages
     *
     * @return string[] The messages
     */
    public function messages()
    {
        return [
            'config_name.in' => 'Config name not in sync-server.directories',
        ];
    }

    /**
     * Failed validation disable redirect
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
