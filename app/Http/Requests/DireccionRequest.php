<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class DireccionRequest extends FormRequest
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
            'linea_direccion_1' => [
                'required',
                'string',
                'max:220',
            ],

            'linea_direccion_2' => [
                'nullable',
                'string',
                'max:220',
            ],

            'codigo_postal' => [
                'nullable',
                'string',
                'max:15',
            ],

            'municipio_id' => [
                'required',
                'integer',
                'exists:municipios,id',
            ],

            'observaciones' => [
                'nullable',
                'string',
            ],

            'requiere_revision' => [
                'nullable',
                'boolean',
            ],

            'tipo' => [
                'nullable',
                'in:general,fiscal,postal',
            ],

            'principal' => [
                'nullable',
                'boolean',
            ],

            'activo' => [
                'nullable',
                'boolean',
            ],

            'observaciones_relacion' => [
                'nullable',
                'string',
            ],
        ];
    }
}
