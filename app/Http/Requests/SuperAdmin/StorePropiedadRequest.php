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
            'nombre' => 'required|string|max:255',
            'nit' => 'nullable|string|max:50|unique:propiedades,nit',
            'direccion' => 'required|string|max:500',
            'ciudad' => 'required|string|max:100',
            'departamento' => 'required|string|max:100',
            'codigo_postal' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|string|max:500',
            'color_primario' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_secundario' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'descripcion' => 'nullable|string',
            'total_unidades' => 'nullable|integer|min:0',
            'estado' => 'nullable|in:activa,suspendida,cancelada',
            'plan_id' => 'nullable|exists:planes,id',
            'fecha_inicio_suscripcion' => 'nullable|date',
            'fecha_fin_suscripcion' => 'nullable|date|after:fecha_inicio_suscripcion',
            'trial_activo' => 'nullable|boolean',
            'fecha_fin_trial' => 'nullable|date',
            'configuracion_personalizada' => 'nullable|array',
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
            'nombre.required' => 'El nombre de la propiedad es obligatorio',
            'nit.unique' => 'El NIT ya está registrado',
            'direccion.required' => 'La dirección es obligatoria',
            'ciudad.required' => 'La ciudad es obligatoria',
            'departamento.required' => 'El departamento es obligatorio',
            'email.email' => 'El email debe ser válido',
            'plan_id.exists' => 'El plan seleccionado no existe',
            'fecha_fin_suscripcion.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
        ];
    }
}
