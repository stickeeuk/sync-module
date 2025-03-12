<?php

namespace Stickee\Sync\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Stickee\Sync\Helpers;

class GetTableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'config_name' => [
                'required',
                'string',
                'max:255',
                Rule::in(array_keys(Helpers::serverConfig('tables'))),
            ],
            'hash' => 'sometimes|string|max:255',
        ];
    }

    /**
     * Get validation error messages
     *
     * @return string[] The messages
     */
    #[\Override]
    public function messages()
    {
        return [
            'config_name.in' => 'Config name not in sync-server.tables',
        ];
    }

    /**
     * Failed validation disable redirect
     */
    #[\Override]
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
