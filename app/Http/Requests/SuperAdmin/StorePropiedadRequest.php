<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StorePropiedadRequest extends FormRequest
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
            // Campos de la propiedad
            'nombre' => 'required|string|max:255',
            'nit' => 'nullable|string|max:50|unique:propiedades,nit',
            'direccion' => 'required|string|max:500',
            'ciudad' => 'required|string|max:100',
            'departamento' => 'required|string|max:100',
            'codigo_postal' => 'nullable|string|max:20',
            'telefono' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'logo' => 'nullable|string|max:500',
            'color_primario' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_secundario' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'descripcion' => 'nullable|string',
            'total_unidades' => 'required|integer|min:0',
            'estado' => 'required|in:activa,suspendida,cancelada',
            'plan_id' => 'required|exists:planes,id',
            
            // Campos del administrador
            'admin_nombre' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
            'admin_telefono' => 'required|string|max:20',
            'admin_documento_identidad' => 'required|string|max:50',
            'admin_tipo_documento' => 'required|in:CC,CE,NIT,PASAPORTE',
            
            // Módulos
            'modulos' => 'required|array|min:1',
            'modulos.*' => 'exists:modulos,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Propiedad
            'nombre.required' => 'El nombre de la propiedad es obligatorio',
            'nit.unique' => 'El NIT ya está registrado',
            'direccion.required' => 'La dirección es obligatoria',
            'ciudad.required' => 'La ciudad es obligatoria',
            'departamento.required' => 'El departamento es obligatorio',
            'telefono.required' => 'El teléfono de la propiedad es obligatorio',
            'email.required' => 'El email de la propiedad es obligatorio',
            'email.email' => 'El email debe ser válido',
            'color_primario.required' => 'El color primario es obligatorio',
            'color_primario.regex' => 'El color primario debe ser un código hexadecimal válido (ej: #0066CC)',
            'color_secundario.required' => 'El color secundario es obligatorio',
            'color_secundario.regex' => 'El color secundario debe ser un código hexadecimal válido (ej: #FFFFFF)',
            'total_unidades.required' => 'El total de unidades es obligatorio',
            'total_unidades.min' => 'El total de unidades debe ser mayor o igual a 0',
            'estado.required' => 'El estado es obligatorio',
            'plan_id.required' => 'El plan es obligatorio',
            'plan_id.exists' => 'El plan seleccionado no existe',
            
            // Administrador
            'admin_nombre.required' => 'El nombre del administrador es obligatorio',
            'admin_email.required' => 'El email del administrador es obligatorio',
            'admin_email.unique' => 'El email del administrador ya está registrado',
            'admin_email.email' => 'El email del administrador debe ser válido',
            'admin_password.required' => 'La contraseña del administrador es obligatoria',
            'admin_password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'admin_password.confirmed' => 'Las contraseñas no coinciden',
            'admin_telefono.required' => 'El teléfono del administrador es obligatorio',
            'admin_documento_identidad.required' => 'El documento de identidad del administrador es obligatorio',
            'admin_tipo_documento.required' => 'El tipo de documento del administrador es obligatorio',
            'admin_tipo_documento.in' => 'El tipo de documento debe ser CC, CE, NIT o PASAPORTE',
            
            // Módulos
            'modulos.required' => 'Debe seleccionar al menos un módulo',
            'modulos.array' => 'Los módulos deben ser un array',
            'modulos.min' => 'Debe seleccionar al menos un módulo',
            'modulos.*.exists' => 'Uno o más módulos seleccionados no existen',
        ];
    }
}
