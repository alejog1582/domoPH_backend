<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
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
        $planId = $this->route('plan');

        return [
            'nombre' => 'sometimes|required|string|max:255',
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('planes', 'slug')->ignore($planId)
            ],
            'descripcion' => 'nullable|string',
            'precio_mensual' => 'sometimes|required|numeric|min:0',
            'precio_anual' => 'nullable|numeric|min:0',
            'max_unidades' => 'nullable|integer|min:0',
            'max_usuarios' => 'nullable|integer|min:0',
            'max_almacenamiento_mb' => 'nullable|integer|min:0',
            'soporte_prioritario' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
            'orden' => 'nullable|integer|min:0',
            'caracteristicas' => 'nullable|array',
        ];
    }
}
