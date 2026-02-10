<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Propiedad;
use App\Models\User;
use App\Models\Unidad;
use App\Models\Residente;
use App\Models\Mascota;
use App\Models\Parqueadero;
use App\Models\Deposito;
use App\Models\ZonaSocial;
use App\Models\Licitacion;
use Carbon\Carbon;

class LimpiarDatosDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:limpiar-datos {--confirm : Confirmar la eliminación sin preguntar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina todos los datos adicionales de la propiedad demo (id=1) creados después del seeder, preservando solo los datos base del DemoSeeder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $propiedadId = 1;
        
        // Verificar que la propiedad existe
        $propiedad = Propiedad::find($propiedadId);
        if (!$propiedad) {
            $this->error("❌ No se encontró la propiedad con id={$propiedadId}");
            return 1;
        }

        if ($propiedad->email !== 'demo@domoph.com') {
            $this->error("❌ La propiedad con id={$propiedadId} no es la propiedad demo (email: {$propiedad->email})");
            return 1;
        }

        $this->info("🧹 Iniciando limpieza de datos adicionales de la propiedad demo...");
        $this->info("📋 Propiedad: {$propiedad->nombre} (ID: {$propiedad->id})");
        $this->newLine();

        // Confirmar acción
        if (!$this->option('confirm')) {
            if (!$this->confirm('⚠️  Esta acción eliminará todos los datos adicionales creados después del seeder. ¿Deseas continuar?')) {
                $this->info('❌ Operación cancelada.');
                return 0;
            }
        }

        DB::beginTransaction();
        try {
            // 1. Identificar usuarios base del seeder (emails @demo.com)
            $this->info('🔍 Identificando usuarios base del seeder...');
            $usuariosBaseIds = User::where('email', 'like', '%@demo.com')
                ->orWhere('email', 'demo@domoph.com')
                ->pluck('id')
                ->toArray();
            
            $this->info("   ✓ Encontrados " . count($usuariosBaseIds) . " usuarios base");

            // 2. Identificar unidades base (001-020)
            $this->info('🔍 Identificando unidades base del seeder...');
            $unidadesBaseIds = Unidad::where('propiedad_id', $propiedadId)
                ->whereIn('numero', array_map(function($i) {
                    return str_pad($i, 3, '0', STR_PAD_LEFT);
                }, range(1, 20)))
                ->pluck('id')
                ->toArray();
            
            $this->info("   ✓ Encontradas " . count($unidadesBaseIds) . " unidades base");

            // 3. Identificar residentes base (asociados a usuarios y unidades base)
            $this->info('🔍 Identificando residentes base del seeder...');
            $residentesBaseIds = Residente::whereIn('user_id', $usuariosBaseIds)
                ->whereIn('unidad_id', $unidadesBaseIds)
                ->pluck('id')
                ->toArray();
            
            $this->info("   ✓ Encontrados " . count($residentesBaseIds) . " residentes base");

            // 4. Identificar mascotas base (asociadas a residentes base)
            $this->info('🔍 Identificando mascotas base del seeder...');
            $mascotasBaseIds = Mascota::whereIn('residente_id', $residentesBaseIds)
                ->whereIn('unidad_id', $unidadesBaseIds)
                ->pluck('id')
                ->toArray();
            
            $this->info("   ✓ Encontradas " . count($mascotasBaseIds) . " mascotas base");

            // 5. Identificar parqueaderos base (P-001 a P-020)
            $this->info('🔍 Identificando parqueaderos base del seeder...');
            $parqueaderosBaseIds = Parqueadero::where('copropiedad_id', $propiedadId)
                ->whereIn('codigo', array_map(function($i) {
                    return 'P-' . str_pad($i, 3, '0', STR_PAD_LEFT);
                }, range(1, 20)))
                ->pluck('id')
                ->toArray();
            
            $this->info("   ✓ Encontrados " . count($parqueaderosBaseIds) . " parqueaderos base");

            // 6. Identificar depósitos base (D-001 a D-020)
            $this->info('🔍 Identificando depósitos base del seeder...');
            $depositosBaseIds = Deposito::where('copropiedad_id', $propiedadId)
                ->whereIn('codigo', array_map(function($i) {
                    return 'D-' . str_pad($i, 3, '0', STR_PAD_LEFT);
                }, range(1, 20)))
                ->pluck('id')
                ->toArray();
            
            $this->info("   ✓ Encontrados " . count($depositosBaseIds) . " depósitos base");

            // 7. Identificar zonas sociales base (nombres específicos)
            $this->info('🔍 Identificando zonas sociales base del seeder...');
            $zonasBaseNombres = ['Gimnasio', 'Piscina', 'Salón Social', 'Zona BBQ'];
            $zonasBaseIds = ZonaSocial::where('propiedad_id', $propiedadId)
                ->whereIn('nombre', $zonasBaseNombres)
                ->pluck('id')
                ->toArray();
            
            $this->info("   ✓ Encontradas " . count($zonasBaseIds) . " zonas sociales base");

            // 8. Identificar licitaciones base (títulos específicos)
            $this->info('🔍 Identificando licitaciones base del seeder...');
            $licitacionesBaseTitulos = [
                'Licitación para Mantenimiento de Ascensores',
                'Licitación para Servicio de Seguridad',
                'Licitación para Obra Civil - Reparación de Fachada'
            ];
            $licitacionesBaseIds = Licitacion::where('copropiedad_id', $propiedadId)
                ->whereIn('titulo', $licitacionesBaseTitulos)
                ->pluck('id')
                ->toArray();
            
            $this->info("   ✓ Encontradas " . count($licitacionesBaseIds) . " licitaciones base");

            // 9. Identificar publicaciones ecommerce base (títulos específicos)
            $this->info('🔍 Identificando publicaciones ecommerce base del seeder...');
            $publicacionesBaseTitulos = [
                'Parqueadero en venta - Torre 1',
                'Servicio de limpieza de apartamentos',
                'Sofá cama en excelente estado',
                'Parqueadero en arriendo mensual',
                'Servicio de plomería y electricidad',
                'Depósito en venta - Sótano 2',
                'Lavadora automática Samsung',
                'Servicio de jardinería y mantenimiento'
            ];
            $publicacionesBaseIds = DB::table('ecommerce_publicaciones')
                ->where('copropiedad_id', $propiedadId)
                ->whereIn('titulo', $publicacionesBaseTitulos)
                ->pluck('id')
                ->toArray();
            
            $this->info("   ✓ Encontradas " . count($publicacionesBaseIds) . " publicaciones ecommerce base");

            $this->newLine();
            $this->info('🗑️  Iniciando eliminación de datos adicionales...');
            $this->newLine();

            // ELIMINAR DATOS ADICIONALES

            // 1. Eliminar publicaciones ecommerce adicionales
            $eliminadas = DB::table('ecommerce_publicaciones')
                ->where('copropiedad_id', $propiedadId)
                ->whereNotIn('id', $publicacionesBaseIds)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} publicaciones ecommerce adicionales");
            }

            // Eliminar imágenes de publicaciones eliminadas
            $publicacionesEliminadasIds = DB::table('ecommerce_publicaciones')
                ->where('copropiedad_id', $propiedadId)
                ->whereNotIn('id', $publicacionesBaseIds)
                ->pluck('id')
                ->toArray();
            if (!empty($publicacionesEliminadasIds)) {
                DB::table('ecommerce_publicacion_imagenes')
                    ->whereIn('publicacion_id', $publicacionesEliminadasIds)
                    ->delete();
                DB::table('ecommerce_publicacion_contactos')
                    ->whereIn('publicacion_id', $publicacionesEliminadasIds)
                    ->delete();
                DB::table('ecommerce_publicacion_estados_historial')
                    ->whereIn('publicacion_id', $publicacionesEliminadasIds)
                    ->delete();
            }

            // 2. Eliminar licitaciones adicionales
            $eliminadas = Licitacion::where('copropiedad_id', $propiedadId)
                ->whereNotIn('id', $licitacionesBaseIds)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} licitaciones adicionales");
            }

            // 3. Eliminar zonas sociales adicionales
            $zonasEliminadasIds = ZonaSocial::where('propiedad_id', $propiedadId)
                ->whereNotIn('id', $zonasBaseIds)
                ->pluck('id')
                ->toArray();
            
            if (!empty($zonasEliminadasIds)) {
                // Eliminar relaciones de zonas sociales
                DB::table('zona_social_horarios')->whereIn('zona_social_id', $zonasEliminadasIds)->delete();
                DB::table('zona_social_imagenes')->whereIn('zona_social_id', $zonasEliminadasIds)->delete();
                DB::table('zona_social_reglas')->whereIn('zona_social_id', $zonasEliminadasIds)->delete();
                DB::table('reservas')->whereIn('zona_social_id', $zonasEliminadasIds)->delete();
                
                $eliminadas = ZonaSocial::whereIn('id', $zonasEliminadasIds)->delete();
                if ($eliminadas > 0) {
                    $this->info("   ✓ Eliminadas {$eliminadas} zonas sociales adicionales");
                }
            }

            // 4. Eliminar depósitos adicionales
            $eliminadas = Deposito::where('copropiedad_id', $propiedadId)
                ->whereNotIn('id', $depositosBaseIds)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminados {$eliminadas} depósitos adicionales");
            }

            // 5. Eliminar parqueaderos adicionales
            $eliminadas = Parqueadero::where('copropiedad_id', $propiedadId)
                ->whereNotIn('id', $parqueaderosBaseIds)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminados {$eliminadas} parqueaderos adicionales");
            }

            // 6. Eliminar mascotas adicionales
            $eliminadas = Mascota::whereIn('unidad_id', $unidadesBaseIds)
                ->whereNotIn('id', $mascotasBaseIds)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} mascotas adicionales");
            }

            // 7. Eliminar residentes adicionales
            $residentesEliminadosIds = Residente::whereIn('unidad_id', $unidadesBaseIds)
                ->whereNotIn('id', $residentesBaseIds)
                ->pluck('id')
                ->toArray();
            
            if (!empty($residentesEliminadosIds)) {
                // Eliminar relaciones de residentes
                DB::table('residente_vehiculos')->whereIn('residente_id', $residentesEliminadosIds)->delete();
                DB::table('residente_invitados')->whereIn('residente_id', $residentesEliminadosIds)->delete();
                
                $eliminadas = Residente::whereIn('id', $residentesEliminadosIds)->delete();
                if ($eliminadas > 0) {
                    $this->info("   ✓ Eliminados {$eliminadas} residentes adicionales");
                }
            }

            // 8. Eliminar unidades adicionales
            $eliminadas = Unidad::where('propiedad_id', $propiedadId)
                ->whereNotIn('id', $unidadesBaseIds)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} unidades adicionales");
            }

            // 9. Eliminar usuarios adicionales (que no sean del seeder)
            $usuariosEliminadosIds = User::where('propiedad_id', 'like', "%{$propiedadId}%")
                ->whereNotIn('id', $usuariosBaseIds)
                ->pluck('id')
                ->toArray();
            
            if (!empty($usuariosEliminadosIds)) {
                // Eliminar relaciones de usuarios
                DB::table('role_user')->whereIn('user_id', $usuariosEliminadosIds)->where('propiedad_id', $propiedadId)->delete();
                DB::table('administradores_propiedad')->whereIn('user_id', $usuariosEliminadosIds)->delete();
                DB::table('consejo_integrantes')->whereIn('user_id', $usuariosEliminadosIds)->delete();
                
                // Actualizar propiedad_id de usuarios (remover el id de la propiedad)
                foreach ($usuariosEliminadosIds as $userId) {
                    $user = User::find($userId);
                    if ($user && $user->propiedad_id) {
                        $propiedades = explode(',', $user->propiedad_id);
                        $propiedades = array_filter($propiedades, function($id) use ($propiedadId) {
                            return $id != $propiedadId;
                        });
                        if (empty($propiedades)) {
                            $user->propiedad_id = null;
                        } else {
                            $user->propiedad_id = implode(',', $propiedades);
                        }
                        $user->save();
                    }
                }
                
                // Solo eliminar usuarios que no tienen otras propiedades
                $usuariosSinPropiedades = User::whereIn('id', $usuariosEliminadosIds)
                    ->where(function($query) {
                        $query->whereNull('propiedad_id')
                              ->orWhere('propiedad_id', '');
                    })
                    ->pluck('id')
                    ->toArray();
                
                if (!empty($usuariosSinPropiedades)) {
                    $eliminadas = User::whereIn('id', $usuariosSinPropiedades)->delete();
                    if ($eliminadas > 0) {
                        $this->info("   ✓ Eliminados {$eliminadas} usuarios adicionales");
                    }
                }
            }

            // 10. Eliminar otros datos relacionados
            $this->info('🔍 Limpiando otros datos relacionados...');
            
            // Eliminar comunicados adicionales (mantener los del ComunicadoSeeder)
            $comunicadosBaseIds = DB::table('comunicados')
                ->where('copropiedad_id', $propiedadId)
                ->whereIn('tipo', ['mantenimiento', 'informativo', 'urgente'])
                ->pluck('id')
                ->toArray();
            
            $eliminadas = DB::table('comunicados')
                ->where('copropiedad_id', $propiedadId)
                ->whereNotIn('id', $comunicadosBaseIds)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminados {$eliminadas} comunicados adicionales");
            }

            // Eliminar PQRS adicionales
            $eliminadas = DB::table('pqrs')
                ->where('copropiedad_id', $propiedadId)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} PQRS adicionales");
            }

            // Eliminar reservas adicionales (excepto las de zonas base)
            $eliminadas = DB::table('reservas')
                ->where('copropiedad_id', $propiedadId)
                ->whereNotIn('zona_social_id', $zonasBaseIds)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} reservas adicionales");
            }

            // Eliminar visitas adicionales
            $eliminadas = DB::table('visitas')
                ->where('copropiedad_id', $propiedadId)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} visitas adicionales");
            }

            // Eliminar correspondencias adicionales
            $eliminadas = DB::table('correspondencias')
                ->where('copropiedad_id', $propiedadId)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} correspondencias adicionales");
            }

            // Eliminar autorizaciones adicionales
            $eliminadas = DB::table('autorizaciones')
                ->where('copropiedad_id', $propiedadId)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} autorizaciones adicionales");
            }

            // Eliminar llamados de atención adicionales
            $eliminadas = DB::table('llamados_atencion')
                ->where('copropiedad_id', $propiedadId)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminados {$eliminadas} llamados de atención adicionales");
            }

            // Eliminar cuentas de cobro adicionales
            $eliminadas = DB::table('cuenta_cobros')
                ->where('copropiedad_id', $propiedadId)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} cuentas de cobro adicionales");
            }

            // Eliminar recaudos adicionales
            $eliminadas = DB::table('recaudos')
                ->where('copropiedad_id', $propiedadId)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminados {$eliminadas} recaudos adicionales");
            }

            // Eliminar acuerdos de pago adicionales
            $eliminadas = DB::table('acuerdos_pagos')
                ->where('copropiedad_id', $propiedadId)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminados {$eliminadas} acuerdos de pago adicionales");
            }

            // Eliminar encuestas adicionales
            $eliminadas = DB::table('encuestas')
                ->where('copropiedad_id', $propiedadId)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} encuestas adicionales");
            }

            // Eliminar votaciones adicionales
            $eliminadas = DB::table('votaciones')
                ->where('copropiedad_id', $propiedadId)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} votaciones adicionales");
            }

            // Eliminar asambleas adicionales
            $eliminadas = DB::table('asambleas')
                ->where('copropiedad_id', $propiedadId)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} asambleas adicionales");
            }

            // Eliminar integrantes del consejo adicionales (mantener los 5 del seeder)
            $integrantesBaseIds = DB::table('consejo_integrantes')
                ->where('copropiedad_id', $propiedadId)
                ->whereIn('cargo', ['presidente', 'vicepresidente', 'secretario', 'vocal'])
                ->limit(5)
                ->pluck('id')
                ->toArray();
            
            $eliminadas = DB::table('consejo_integrantes')
                ->where('copropiedad_id', $propiedadId)
                ->whereNotIn('id', $integrantesBaseIds)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminados {$eliminadas} integrantes del consejo adicionales");
            }

            // Eliminar reuniones del consejo adicionales
            $eliminadas = DB::table('consejo_reuniones')
                ->where('copropiedad_id', $propiedadId)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} reuniones del consejo adicionales");
            }

            // Eliminar actas del consejo adicionales
            $eliminadas = DB::table('consejo_actas')
                ->where('copropiedad_id', $propiedadId)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} actas del consejo adicionales");
            }

            // Eliminar tareas del consejo adicionales
            $eliminadas = DB::table('consejo_tareas')
                ->where('copropiedad_id', $propiedadId)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} tareas del consejo adicionales");
            }

            // Eliminar comunicaciones del consejo adicionales
            $eliminadas = DB::table('consejo_comunicaciones')
                ->where('copropiedad_id', $propiedadId)
                ->delete();
            if ($eliminadas > 0) {
                $this->info("   ✓ Eliminadas {$eliminadas} comunicaciones del consejo adicionales");
            }

            DB::commit();
            
            $this->newLine();
            $this->info('✅ Limpieza completada exitosamente!');
            $this->info('📊 Los datos base del seeder han sido preservados.');
            
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error durante la limpieza: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}
