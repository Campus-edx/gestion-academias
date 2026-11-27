<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

abstract class CorreoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'tipo' => [
                'required',
                'string',
                'max:40',
            ],

            'principal' => [
                'nullable',
                'boolean',
            ],

            'activo' => [
                'nullable',
                'boolean',
            ],

            'observaciones' => [
                'nullable',
                'string',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'email.max' => 'El correo electrónico no puede superar los 255 caracteres.',
            'tipo.required' => 'Selecciona un tipo de correo.',
            'tipo.max' => 'El tipo de correo no puede superar los 40 caracteres.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $activo = $this->has('activo')
                    ? $this->boolean('activo')
                    : true;

                if ($this->boolean('principal') && ! $activo) {
                    $validator->errors()->add(
                        'principal',
                        'El contacto principal debe estar activo.'
                    );
                }
            },
        ];
    }
}
