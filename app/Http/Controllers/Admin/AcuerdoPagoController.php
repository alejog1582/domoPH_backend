<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcuerdoPago;
use App\Models\Cartera;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AcuerdoPagoController extends Controller
{
    /**
     * Mostrar el formulario para crear un acuerdo de pago
     */
    public function create(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Validar que se proporcione el ID de la cartera
        $carteraId = $request->input('cartera_id');
        
        if (!$carteraId) {
            return redirect()->route('admin.cartera.index')
                ->with('error', 'Debe seleccionar una cartera para crear el acuerdo de pago.');
        }

        // Obtener la cartera con sus relaciones
        $cartera = Cartera::with(['unidad'])
            ->where('copropiedad_id', $propiedad->id)
            ->where('id', $carteraId)
            ->first();

        if (!$cartera) {
            return redirect()->route('admin.cartera.index')
                ->with('error', 'Cartera no encontrada.');
        }

        // Validar que la cartera tenga saldo en mora
        if ($cartera->saldo_mora <= 0) {
            return redirect()->route('admin.cartera.index')
                ->with('error', 'La unidad no tiene saldo en mora para crear un acuerdo de pago.');
        }

        // Generar número de acuerdo sugerido
        $ultimoAcuerdo = AcuerdoPago::where('copropiedad_id', $propiedad->id)
            ->orderBy('id', 'desc')
            ->first();
        
        $numeroAcuerdo = 'ACU-' . str_pad(($ultimoAcuerdo ? $ultimoAcuerdo->id + 1 : 1), 6, '0', STR_PAD_LEFT);

        return view('admin.acuerdos-pagos.create', compact('cartera', 'propiedad', 'numeroAcuerdo'));
    }

    /**
     * Guardar un nuevo acuerdo de pago
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $validated = $request->validate([
            'cartera_id' => 'required|exists:carteras,id',
            'cuenta_cobro_id' => 'nullable|exists:cuenta_cobros,id',
            'numero_acuerdo' => 'required|string|max:50',
            'fecha_acuerdo' => 'required|date',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'descripcion' => 'nullable|string',
            'saldo_original' => 'required|numeric|min:0',
            'valor_acordado' => 'required|numeric|min:0',
            'valor_inicial' => 'nullable|numeric|min:0',
            'numero_cuotas' => 'required|integer|min:1',
            'valor_cuota' => 'required|numeric|min:0',
            'interes_acuerdo' => 'nullable|numeric|min:0|max:100',
            'valor_intereses' => 'nullable|numeric|min:0',
        ], [
            'cartera_id.required' => 'La cartera es obligatoria.',
            'cartera_id.exists' => 'La cartera seleccionada no existe.',
            'numero_acuerdo.required' => 'El número de acuerdo es obligatorio.',
            'fecha_acuerdo.required' => 'La fecha del acuerdo es obligatoria.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
            'saldo_original.required' => 'El saldo original es obligatorio.',
            'valor_acordado.required' => 'El valor acordado es obligatorio.',
            'numero_cuotas.required' => 'El número de cuotas es obligatorio.',
            'numero_cuotas.min' => 'El número de cuotas debe ser al menos 1.',
            'valor_cuota.required' => 'El valor de la cuota es obligatorio.',
        ]);

        try {
            // Verificar que la cartera pertenezca a la propiedad activa
            $cartera = Cartera::where('copropiedad_id', $propiedad->id)
                ->where('id', $validated['cartera_id'])
                ->first();

            if (!$cartera) {
                return back()->with('error', 'La cartera no pertenece a la propiedad activa.')
                    ->withInput();
            }

            // Verificar unicidad del número de acuerdo en la copropiedad
            $acuerdoExistente = AcuerdoPago::where('copropiedad_id', $propiedad->id)
                ->where('numero_acuerdo', $validated['numero_acuerdo'])
                ->first();

            if ($acuerdoExistente) {
                return back()->with('error', 'Ya existe un acuerdo de pago con este número.')
                    ->withInput();
            }

            // Calcular saldo pendiente
            $valorInicial = $validated['valor_inicial'] ?? 0;
            $saldoPendiente = $validated['valor_acordado'] - $valorInicial;

            // Determinar estado inicial
            $estado = 'pendiente';
            if ($saldoPendiente <= 0) {
                $estado = 'cumplido';
            } elseif ($validated['fecha_inicio'] <= Carbon::now()->toDateString()) {
                $estado = 'activo';
            }

            // Crear el acuerdo de pago
            $acuerdoPago = AcuerdoPago::create([
                'copropiedad_id' => $propiedad->id,
                'unidad_id' => $cartera->unidad_id,
                'cartera_id' => $cartera->id,
                'cuenta_cobro_id' => $validated['cuenta_cobro_id'] ?? null,
                'numero_acuerdo' => $validated['numero_acuerdo'],
                'fecha_acuerdo' => $validated['fecha_acuerdo'],
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'] ?? null,
                'descripcion' => $validated['descripcion'] ?? null,
                'saldo_original' => $validated['saldo_original'],
                'valor_acordado' => $validated['valor_acordado'],
                'valor_inicial' => $valorInicial,
                'saldo_pendiente' => $saldoPendiente,
                'numero_cuotas' => $validated['numero_cuotas'],
                'valor_cuota' => $validated['valor_cuota'],
                'interes_acuerdo' => $validated['interes_acuerdo'] ?? 0,
                'valor_intereses' => $validated['valor_intereses'] ?? 0,
                'estado' => $estado,
                'activo' => true,
                'usuario_id' => Auth::id(),
            ]);

            return redirect()->route('admin.cartera.index')
                ->with('success', 'Acuerdo de pago creado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al crear acuerdo de pago: ' . $e->getMessage());
            return back()->with('error', 'Error al crear el acuerdo de pago: ' . $e->getMessage())
                ->withInput();
        }
    }
}
