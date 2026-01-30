<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\Residente;
use Illuminate\Support\Facades\DB;

class AsignarRolResidente extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'residentes:asignar-rol 
                            {--user-id= : ID específico del usuario}
                            {--propiedad-id= : ID de la propiedad (opcional, si no se especifica se asignará a todas las propiedades donde sea residente)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asigna el rol de residente a usuarios que son residentes pero no tienen el rol asignado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rolResidente = Role::where('slug', 'residente')->first();

        if (!$rolResidente) {
            $this->error('No se encontró el rol "residente" en la base de datos.');
            return 1;
        }

        $userId = $this->option('user-id');
        $propiedadId = $this->option('propiedad-id');

        if ($userId) {
            // Asignar rol a un usuario específico
            $user = User::find($userId);
            if (!$user) {
                $this->error("No se encontró el usuario con ID: {$userId}");
                return 1;
            }

            $this->asignarRolAUsuario($user, $rolResidente, $propiedadId);
        } else {
            // Buscar todos los usuarios que son residentes pero no tienen el rol
            $this->info('Buscando usuarios residentes sin rol asignado...');

            $residentes = Residente::with(['user', 'unidad.propiedad'])
                ->whereHas('user', function($query) {
                    $query->where('activo', true);
                })
                ->get();

            $asignados = 0;
            $yaTienenRol = 0;

            foreach ($residentes as $residente) {
                if (!$residente->user || !$residente->unidad || !$residente->unidad->propiedad) {
                    continue;
                }

                $user = $residente->user;
                $propiedad = $residente->unidad->propiedad;

                // Si se especificó una propiedad, solo procesar esa
                if ($propiedadId && $propiedad->id != $propiedadId) {
                    continue;
                }

                // Verificar si ya tiene el rol para esta propiedad
                $tieneRol = $user->roles()
                    ->where('roles.id', $rolResidente->id)
                    ->wherePivot('propiedad_id', $propiedad->id)
                    ->exists();

                if (!$tieneRol) {
                    $user->roles()->attach($rolResidente->id, [
                        'propiedad_id' => $propiedad->id
                    ]);
                    $this->info("✓ Rol asignado a {$user->nombre} (ID: {$user->id}) para propiedad {$propiedad->nombre} (ID: {$propiedad->id})");
                    $asignados++;
                } else {
                    $yaTienenRol++;
                }
            }

            $this->info("\nResumen:");
            $this->info("  - Roles asignados: {$asignados}");
            $this->info("  - Ya tenían el rol: {$yaTienenRol}");
        }

        return 0;
    }

    /**
     * Asignar rol a un usuario específico
     */
    private function asignarRolAUsuario(User $user, Role $rolResidente, $propiedadId = null)
    {
        // Obtener todas las propiedades donde el usuario es residente
        $propiedades = Residente::where('user_id', $user->id)
            ->with('unidad.propiedad')
            ->get()
            ->map(function($residente) {
                return $residente->unidad->propiedad ?? null;
            })
            ->filter()
            ->unique('id');

        if ($propiedadId) {
            $propiedades = $propiedades->where('id', $propiedadId);
        }

        if ($propiedades->isEmpty()) {
            $this->warn("El usuario {$user->nombre} (ID: {$user->id}) no es residente en ninguna propiedad.");
            return;
        }

        $asignados = 0;
        foreach ($propiedades as $propiedad) {
            $tieneRol = $user->roles()
                ->where('roles.id', $rolResidente->id)
                ->wherePivot('propiedad_id', $propiedad->id)
                ->exists();

            if (!$tieneRol) {
                $user->roles()->attach($rolResidente->id, [
                    'propiedad_id' => $propiedad->id
                ]);
                $this->info("✓ Rol asignado a {$user->nombre} para propiedad {$propiedad->nombre} (ID: {$propiedad->id})");
                $asignados++;
            } else {
                $this->info("  El usuario ya tiene el rol para la propiedad {$propiedad->nombre}");
            }
        }

        if ($asignados > 0) {
            $this->info("\n✓ Se asignaron {$asignados} rol(es) al usuario.");
        }
    }
}
