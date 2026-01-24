<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropiedadRequest extends FormRequest
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
        $propiedadId = $this->route('propiedad');

        return [
            'nombre' => 'sometimes|required|string|max:255',
            'nit' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('propiedades', 'nit')->ignore($propiedadId)
            ],
            'direccion' => 'sometimes|required|string|max:500',
            'ciudad' => 'sometimes|required|string|max:100',
            'departamento' => 'sometimes|required|string|max:100',
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
}
