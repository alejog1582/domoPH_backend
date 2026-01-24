<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminRequest extends FormRequest
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
        $adminId = $this->route('administrador');

        return [
            'nombre' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($adminId)
            ],
            'password' => 'sometimes|string|min:8|confirmed',
            'telefono' => 'nullable|string|max:20',
            'documento_identidad' => 'nullable|string|max:50',
            'tipo_documento' => 'nullable|in:CC,CE,NIT,PASAPORTE',
            'activo' => 'nullable|boolean',
            'propiedad_id' => 'sometimes|required|exists:propiedades,id',
            'es_principal' => 'nullable|boolean',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'permisos_especiales' => 'nullable|array',
        ];
    }
}
