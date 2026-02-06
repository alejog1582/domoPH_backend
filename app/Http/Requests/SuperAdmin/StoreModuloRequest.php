<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreModuloRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('superadministrador');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:modulos,slug',
            'descripcion' => 'nullable|string',
            'icono' => 'nullable|string|max:100',
            'ruta' => 'nullable|string|max:255',
            'activo' => 'nullable|boolean',
            'requiere_configuracion' => 'nullable|boolean',
            'es_admin' => 'required|boolean',
            'orden' => 'nullable|integer|min:0',
            'configuracion_default' => 'nullable|string|json',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Si configuracion_default viene como string JSON, intentar decodificarlo
        if ($this->has('configuracion_default') && is_string($this->configuracion_default)) {
            $decoded = json_decode($this->configuracion_default, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->merge([
                    'configuracion_default' => $decoded,
                ]);
            } elseif (empty(trim($this->configuracion_default))) {
                // Si está vacío, establecer como array vacío
                $this->merge([
                    'configuracion_default' => [],
                ]);
            }
        }

        // Convertir es_admin a boolean
        if ($this->has('es_admin')) {
            $this->merge([
                'es_admin' => filter_var($this->es_admin, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
