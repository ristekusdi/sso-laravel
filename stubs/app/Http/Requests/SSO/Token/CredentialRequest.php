<?php

namespace App\Http\Requests\SSO\Token;

use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CredentialRequest extends FormRequest
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
            'username' => 'required_if:grant_type,==,password',
            'password' => 'required_if:grant_type,==,password',
            'client_id' => 'required',
            'client_secret' => 'required',
            'grant_type' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'Username wajib diisi',
            'password.required' => 'Password wajib diisi',
            'client_id.required' => 'Client ID wajib diisi',
            'client_secret.required' => 'Client secret wajib diisi',
            'grant_type.required' => 'Grant type wajib diisi',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        if (request()->is('api/*')) {
            $errors = (new ValidationException($validator))->errors();
            throw new HttpResponseException(response()->json(['errors' => $errors],
            JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
        } else {
            throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
        }
    }
}
