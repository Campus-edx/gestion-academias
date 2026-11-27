<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

abstract class TelefonoRequest extends FormRequest
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
            'prefijo_internacional' => [
                'nullable',
                'string',
                'max:8',
            ],

            'numero' => [
                'required',
                'string',
                'max:30',
            ],

            'extension' => [
                'nullable',
                'string',
                'max:10',
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
            'numero.required' => 'El número de teléfono es obligatorio.',
            'numero.max' => 'El número de teléfono no puede superar los 30 caracteres.',
            'tipo.required' => 'Selecciona un tipo de teléfono.',
            'tipo.max' => 'El tipo de teléfono no puede superar los 40 caracteres.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $normalizado = preg_replace(
                    '/[^0-9+]/',
                    '',
                    trim(
                        (string) $this->input('prefijo_internacional')
                        . (string) $this->input('numero')
                    )
                ) ?? '';

                if ($normalizado === '' || ! preg_match('/\d/', $normalizado)) {
                    $validator->errors()->add(
                        'numero',
                        'El teléfono debe contener al menos un dígito.'
                    );
                }

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
