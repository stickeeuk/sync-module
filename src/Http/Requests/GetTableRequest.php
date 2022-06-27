<?php

namespace Stickee\Sync\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class GetTableRequest extends FormRequest
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
                'max:255',
                Rule::in(array_keys(config('sync-server.tables'))),
            ],
            'hash' => 'sometimes|string|max:255',
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
            'config_name.in' => 'Config name not in sync-server.tables',
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
