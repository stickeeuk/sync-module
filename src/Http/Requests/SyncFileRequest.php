<?php

namespace Stickee\Sync\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class SyncFileRequest extends FormRequest
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
            'file' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // TODO
                    // Storage::disk($disk)->exists($value);
                    //$fail($attribute . ' is invalid.');
                },
            ],
            'hash' => 'sometimes|string|max:255',
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
