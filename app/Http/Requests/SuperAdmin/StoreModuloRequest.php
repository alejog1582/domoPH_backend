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
            'orden' => 'nullable|integer|min:0',
            'configuracion_default' => 'nullable|array',
        ];
    }
}
