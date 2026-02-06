<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Propiedad;
use App\Models\Unidad;
use App\Models\Residente;
use App\Models\Mascota;
use App\Models\Parqueadero;
use App\Models\Deposito;
use App\Models\ZonaSocial;
use App\Models\ZonaSocialHorario;
use App\Models\ZonaSocialImagen;
use App\Models\ZonaSocialRegla;
use App\Models\AdministradorPropiedad;
use App\Models\Plan;
use App\Models\Modulo;
use App\Models\Permission;
use Carbon\Carbon;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando creación de datos DEMO...');

        // Limpiar datos existentes de la propiedad demo (si existe)
        //$this->limpiarDatosDemo();

        // 1. Crear usuario administrador demo
        $adminDemo = $this->crearAdministradorDemo();

        // 2. Crear propiedad demo
        $propiedad = $this->crearPropiedadDemo($adminDemo);
        
        // 2.1. Asignar propiedad_id al administrador demo
        $adminDemo->propiedad_id = (string) $propiedad->id;
        $adminDemo->save();

        // 2.1. Asociar módulos del plan a la propiedad
        $this->asociarModulosAPropiedad($propiedad, $adminDemo);

        // 3. Crear 20 unidades
        $unidades = $this->crearUnidades($propiedad);

        // 4. Crear residentes (20 propietarios + adicionales)
        $residentes = $this->crearResidentes($unidades);

        // 5. Crear 8 mascotas
        $this->crearMascotas($residentes, $unidades);

        // 6. Crear 20 parqueaderos y 20 depósitos asignados
        $this->crearParqueaderosYDepositos($propiedad, $unidades, $residentes);

        // 7. Crear 4 zonas comunes con horarios
        $this->crearZonasComunes($propiedad);

        $this->command->info('✅ Datos DEMO creados exitosamente!');
        $this->command->info('📧 Email: demo@domoph.com');
        $this->command->info('🔑 Password: 12345678');
    }

    /**
     * Limpiar datos existentes de la propiedad demo
     */
    private function limpiarDatosDemo(): void
    {
        $this->command->info('🧹 Limpiando datos existentes de la propiedad demo...');

        // Buscar propiedad demo por email
        $propiedadDemo = Propiedad::where('email', 'demo@domoph.com')->first();

        if ($propiedadDemo) {
            // Eliminar registros de administradores_propiedad
            AdministradorPropiedad::where('propiedad_id', $propiedadDemo->id)->delete();
            // Eliminar en cascada (soft deletes)
            $propiedadDemo->delete();
            $this->command->info('   ✓ Propiedad demo eliminada');
        }

        // Eliminar usuario demo si existe
        $userDemo = User::where('email', 'demo@domoph.com')->first();
        if ($userDemo) {
            // Eliminar relaciones en role_user
            DB::table('role_user')->where('user_id', $userDemo->id)->delete();
            // Eliminar registros de administradores_propiedad
            AdministradorPropiedad::where('user_id', $userDemo->id)->delete();
            $userDemo->delete();
            $this->command->info('   ✓ Usuario demo eliminado');
        }
    }

    /**
     * Crear usuario administrador demo
     */
    private function crearAdministradorDemo(): User
    {
        $this->command->info('👤 Creando usuario administrador demo...');

        $adminDemo = User::updateOrCreate(
            ['email' => 'demo@domoph.com'],
            [
                'nombre' => 'Usuario Administrador DEMO',
                'email' => 'demo@domoph.com',
                'password' => Hash::make('12345678'),
                'telefono' => '3001234567',
                'documento_identidad' => '1234567890',
                'tipo_documento' => 'CC',
                'activo' => true,
                'perfil' => 'administrador', // Asignar perfil de administrador
            ]
        );

        // Asignar rol de administrador
        $rolAdmin = Role::where('slug', 'administrador')->first();

        if ($rolAdmin) {
            // Eliminar roles anteriores
            DB::table('role_user')->where('user_id', $adminDemo->id)->delete();

            // Asignar nuevo rol (se asignará la propiedad después)
            DB::table('role_user')->insert([
                'user_id' => $adminDemo->id,
                'role_id' => $rolAdmin->id,
                'propiedad_id' => null, // Se actualizará después
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('   ✓ Usuario administrador demo creado');
        return $adminDemo;
    }

    /**
     * Crear propiedad demo
     */
    private function crearPropiedadDemo(User $adminDemo): Propiedad
    {
        $this->command->info('🏢 Creando propiedad demo...');

        $propiedad = Propiedad::updateOrCreate(
            ['email' => 'demo@domoph.com'],
            [
                'nombre' => 'Conjunto Residencial Los Alamos',
                'nit' => '900123456-1',
                'direccion' => 'Carrera 15 # 45-30',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'codigo_postal' => '110111',
                'telefono' => '6012345678',
                'email' => 'demo@domoph.com',
                'logo' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770319463/domoph/demo/logo_propiedad_demo.png',
                'descripcion' => 'Conjunto residencial moderno con amplias zonas comunes y excelente ubicación.',
                'total_unidades' => 20,
                'estado' => 'activa',
                'plan_id' => Plan::where('slug', 'plan-base-domoph')->first()->id,
            ]
        );

        // Crear registro en administradores_propiedad
        AdministradorPropiedad::updateOrCreate(
            [
                'user_id' => $adminDemo->id,
                'propiedad_id' => $propiedad->id,
            ],
            [
                'fecha_inicio' => Carbon::now()->subMonths(6),
                'fecha_fin' => null,
                'es_principal' => true,
                'permisos_especiales' => null,
                'observaciones' => 'Administrador demo de la propiedad',
            ]
        );

        $this->command->info('   ✓ Propiedad demo creada');
        $this->command->info('   ✓ Administrador asociado a la propiedad');
        return $propiedad;
    }

    /**
     * Asociar módulos del plan a la propiedad
     */
    private function asociarModulosAPropiedad(Propiedad $propiedad, User $adminDemo): void
    {
        $this->command->info('📦 Asociando módulos del plan a la propiedad...');

        // Obtener el plan de la propiedad
        $plan = $propiedad->plan;
        
        if (!$plan) {
            $this->command->warn('   ⚠ La propiedad no tiene plan asignado');
            return;
        }

        // Obtener los slugs de módulos del plan desde características
        $caracteristicas = $plan->caracteristicas ?? [];
        $modulosSlugs = $caracteristicas['modulos'] ?? [];

        if (empty($modulosSlugs)) {
            $this->command->warn('   ⚠ El plan no tiene módulos definidos');
            return;
        }

        // Obtener los módulos por sus slugs
        $modulos = Modulo::whereIn('slug', $modulosSlugs)->get();

        if ($modulos->isEmpty()) {
            $this->command->warn('   ⚠ No se encontraron módulos con los slugs del plan');
            return;
        }

        // Preparar datos para la asociación
        $modulosData = [];
        $fechaActivacion = Carbon::now()->subMonths(6);

        foreach ($modulos as $modulo) {
            // Convertir configuracion_default a JSON si es un array
            $configuracion = null;
            if ($modulo->configuracion_default && is_array($modulo->configuracion_default)) {
                $configuracion = json_encode($modulo->configuracion_default);
            } elseif ($modulo->configuracion_default) {
                $configuracion = $modulo->configuracion_default;
            }

            $modulosData[$modulo->id] = [
                'activo' => true,
                'fecha_activacion' => $fechaActivacion,
                'fecha_desactivacion' => null,
                'configuracion' => $configuracion,
            ];
        }

        // Asociar módulos a la propiedad
        $propiedad->modulos()->sync($modulosData);

        $this->command->info('   ✓ ' . count($modulos) . ' módulos asociados a la propiedad');

        // Crear rol específico para la propiedad y asignar permisos
        $modulosIds = $modulos->pluck('id')->toArray();
        $this->crearRolYAsignarPermisos($propiedad, $modulosIds, $adminDemo);
    }

    /**
     * Crear o actualizar rol específico de la propiedad y asignar permisos
     *
     * @param Propiedad $propiedad
     * @param array $modulosIds
     * @param User $adminUser
     * @return void
     */
    private function crearRolYAsignarPermisos(Propiedad $propiedad, array $modulosIds, User $adminUser): void
    {
        $this->command->info('🔐 Creando rol específico de la propiedad y asignando permisos...');

        // Generar nombre y slug del rol
        $nombreRol = "Administrador {$propiedad->nombre}";
        $slugRol = 'administrador_' . \Illuminate\Support\Str::slug($propiedad->nombre, '_');

        // Crear o actualizar el rol específico de la propiedad
        $rolPropiedad = Role::updateOrCreate(
            ['slug' => $slugRol],
            [
                'nombre' => $nombreRol,
                'descripcion' => "Rol de administrador específico para la propiedad {$propiedad->nombre}",
                'activo' => true,
            ]
        );

        // Si hay módulos asignados, obtener sus permisos
        if (!empty($modulosIds)) {
            // Obtener los slugs de los módulos asignados
            $modulosAsignados = Modulo::whereIn('id', $modulosIds)->pluck('slug')->toArray();
            
            // Obtener los permisos cuyo campo modulo coincida con los slugs de los módulos asignados
            $permisos = Permission::whereIn('modulo', $modulosAsignados)->pluck('id')->toArray();
            
            // Asignar permisos al rol específico de la propiedad
            $rolPropiedad->permissions()->sync($permisos);
            
            $this->command->info("   ✓ Rol '{$nombreRol}' creado/actualizado");
            $this->command->info('   ✓ ' . count($permisos) . ' permisos asignados al rol');
        } else {
            // Si no hay módulos, eliminar todos los permisos del rol
            $rolPropiedad->permissions()->sync([]);
            $this->command->info("   ✓ Rol '{$nombreRol}' creado/actualizado (sin permisos)");
        }

        // Eliminar roles anteriores del usuario para esta propiedad
        DB::table('role_user')
            ->where('user_id', $adminUser->id)
            ->where('propiedad_id', $propiedad->id)
            ->delete();

        // Asignar el rol específico al usuario administrador
        DB::table('role_user')->insert([
            'user_id' => $adminUser->id,
            'role_id' => $rolPropiedad->id,
            'propiedad_id' => $propiedad->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('   ✓ Rol asignado al usuario administrador');
    }

    /**
     * Crear 20 unidades
     */
    private function crearUnidades(Propiedad $propiedad): array
    {
        $this->command->info('🏠 Creando 20 unidades...');

        $unidades = [];
        $tipos = ['apartamento', 'apartamento', 'apartamento', 'casa'];
        $estados = ['ocupada', 'ocupada', 'ocupada', 'desocupada'];

        for ($i = 1; $i <= 20; $i++) {
            $numero = str_pad($i, 3, '0', STR_PAD_LEFT);
            $torre = $i <= 10 ? 'Torre 1' : 'Torre 2';
            $bloque = $i <= 5 ? 'Bloque A' : ($i <= 10 ? 'Bloque B' : ($i <= 15 ? 'Bloque C' : 'Bloque D'));

            $unidad = Unidad::updateOrCreate(
                [
                    'propiedad_id' => $propiedad->id,
                    'numero' => $numero,
                ],
                [
                    'torre' => $torre,
                    'bloque' => $bloque,
                    'tipo' => $tipos[array_rand($tipos)],
                    'area_m2' => rand(60, 120),
                    'coeficiente' => rand(80, 120),
                    'habitaciones' => rand(2, 4),
                    'banos' => rand(1, 3),
                    'estado' => $estados[array_rand($estados)],
                ]
            );

            $unidades[] = $unidad;
        }

        $this->command->info('   ✓ 20 unidades creadas');
        return $unidades;
    }

    /**
     * Crear residentes (20 propietarios + adicionales)
     */
    private function crearResidentes(array $unidades): array
    {
        $this->command->info('👨‍👩‍👧 Creando residentes...');

        $residentes = [];
        $nombres = [
            'Carlos', 'María', 'Juan', 'Ana', 'Luis', 'Laura', 'Pedro', 'Sofia',
            'Diego', 'Camila', 'Andrés', 'Valentina', 'Jorge', 'Isabella', 'Ricardo', 'Daniela',
            'Fernando', 'Natalia', 'Gustavo', 'Andrea', 'Roberto', 'Paula', 'Miguel', 'Carolina'
        ];
        $apellidos = [
            'García', 'Rodríguez', 'López', 'Martínez', 'González', 'Pérez', 'Sánchez', 'Ramírez',
            'Torres', 'Flores', 'Rivera', 'Gómez', 'Díaz', 'Cruz', 'Morales', 'Ortiz'
        ];
        $tiposRelacion = ['propietario', 'arrendatario', 'residente_temporal', 'otro'];

        // Crear 20 propietarios (uno por unidad)
        foreach ($unidades as $index => $unidad) {
            $nombre = $nombres[$index % count($nombres)];
            $apellido = $apellidos[$index % count($apellidos)];
            $email = strtolower($nombre . '.' . $apellido . '@demo.com');
            $telefono = '3' . rand(100000000, 999999999);

            // Obtener propiedad_id de la unidad
            $propiedadId = $unidad->propiedad_id;
            
            // Crear usuario para el residente
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'nombre' => $nombre . ' ' . $apellido,
                    'email' => $email,
                    'password' => Hash::make($telefono),
                    'telefono' => $telefono,
                    'documento_identidad' => rand(1000000000, 9999999999),
                    'tipo_documento' => 'CC',
                    'activo' => true,
                    'perfil' => 'residente', // Asignar perfil de residente
                ]
            );
            
            // Asignar propiedad_id al usuario (agregar si ya existe)
            if (empty($user->propiedad_id)) {
                $user->propiedad_id = (string) $propiedadId;
                $user->save();
            } else {
                $user->agregarPropiedadId($propiedadId);
            }
            
            // Asegurar que el perfil sea residente
            if ($user->perfil !== 'residente') {
                $user->perfil = 'residente';
                $user->save();
            }

            // Crear residente propietario
            $residente = Residente::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'unidad_id' => $unidad->id,
                ],
                [
                    'tipo_relacion' => 'propietario',
                    'fecha_inicio' => Carbon::now()->subMonths(rand(6, 24)),
                    'fecha_fin' => null,
                    'es_principal' => true,
                    'recibe_notificaciones' => true,
                ]
            );

            $residentes[] = $residente;
        }

        // Crear 5 residentes adicionales (arrendatarios, familiares, etc.)
        for ($i = 0; $i < 5; $i++) {
            $unidad = $unidades[rand(0, 19)];
            $nombre = $nombres[rand(0, count($nombres) - 1)];
            $apellido = $apellidos[rand(0, count($apellidos) - 1)];
            $email = strtolower($nombre . '.' . $apellido . '.' . rand(1, 100) . '@demo.com');

            // Obtener propiedad_id de la unidad
            $propiedadId = $unidad->propiedad_id;
            
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'nombre' => $nombre . ' ' . $apellido,
                    'email' => $email,
                    'password' => Hash::make('12345678'),
                    'telefono' => '3' . rand(100000000, 999999999),
                    'documento_identidad' => rand(1000000000, 9999999999),
                    'tipo_documento' => 'CC',
                    'activo' => true,
                    'perfil' => 'residente', // Asignar perfil de residente
                ]
            );
            
            // Asignar propiedad_id al usuario (agregar si ya existe)
            if (empty($user->propiedad_id)) {
                $user->propiedad_id = (string) $propiedadId;
                $user->save();
            } else {
                $user->agregarPropiedadId($propiedadId);
            }
            
            // Asegurar que el perfil sea residente
            if ($user->perfil !== 'residente') {
                $user->perfil = 'residente';
                $user->save();
            }

            $tipoRelacion = $tiposRelacion[rand(1, 3)]; // No propietario

            $residente = Residente::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'unidad_id' => $unidad->id,
                ],
                [
                    'tipo_relacion' => $tipoRelacion,
                    'fecha_inicio' => Carbon::now()->subMonths(rand(1, 12)),
                    'fecha_fin' => rand(0, 1) ? Carbon::now()->addMonths(rand(1, 6)) : null,
                    'es_principal' => false,
                    'recibe_notificaciones' => rand(0, 1) == 1,
                ]
            );

            $residentes[] = $residente;
        }

        $this->command->info('   ✓ ' . count($residentes) . ' residentes creados');
        return $residentes;
    }

    /**
     * Crear 8 mascotas
     */
    private function crearMascotas(array $residentes, array $unidades): void
    {
        $this->command->info('🐾 Creando 8 mascotas...');

        $tipos = ['perro', 'gato', 'perro', 'gato', 'perro', 'gato', 'ave', 'otro'];
        $razas = ['Labrador', 'Pastor Alemán', 'Persa', 'Siames', 'Canario', 'Cocker', 'Bulldog', 'Mestizo'];
        $colores = ['Negro', 'Blanco', 'Marrón', 'Gris', 'Atigrado', 'Dorado', 'Tricolor'];
        $sexos = ['macho', 'hembra', 'desconocido'];
        $tamanios = ['pequeño', 'mediano', 'grande'];
        $estadosSalud = ['saludable', 'saludable', 'saludable', 'en_tratamiento', 'crónico'];

        $nombresMascotas = ['Max', 'Luna', 'Toby', 'Mia', 'Rocky', 'Lola', 'Zeus', 'Coco'];

        for ($i = 0; $i < 8; $i++) {
            $residente = $residentes[rand(0, count($residentes) - 1)];
            $unidad = $residente->unidad;

            Mascota::create([
                'unidad_id' => $unidad->id,
                'residente_id' => $residente->id,
                'nombre' => $nombresMascotas[$i],
                'tipo' => $tipos[$i],
                'raza' => $razas[$i],
                'color' => $colores[rand(0, count($colores) - 1)],
                'sexo' => $sexos[rand(0, count($sexos) - 1)],
                'fecha_nacimiento' => Carbon::now()->subYears(rand(1, 8)),
                'edad_aproximada' => rand(1, 8),
                'peso_kg' => rand(3, 35),
                'tamanio' => $tamanios[rand(0, count($tamanios) - 1)],
                'numero_chip' => 'CHIP' . rand(100000, 999999),
                'vacunado' => rand(0, 1) == 1,
                'esterilizado' => rand(0, 1) == 1,
                'estado_salud' => $estadosSalud[rand(0, count($estadosSalud) - 1)],
                'activo' => true,
            ]);
        }

        $this->command->info('   ✓ 8 mascotas creadas');
    }

    /**
     * Crear 20 parqueaderos y 20 depósitos asignados
     */
    private function crearParqueaderosYDepositos(Propiedad $propiedad, array $unidades, array $residentes): void
    {
        $this->command->info('🚗📦 Creando parqueaderos y depósitos...');

        $tiposParqueadero = ['privado', 'comunal', 'privado', 'comunal'];
        $tiposVehiculo = ['carro', 'moto', 'carro', 'carro'];
        $nivelesParqueadero = ['Sótano 1', 'Sótano 2', 'Exterior', 'Nivel 1'];
        $nivelesDeposito = ['Sótano 1', 'Sótano 2', 'Nivel 1', 'Nivel 2'];
        $estados = ['asignado', 'asignado', 'asignado', 'disponible'];

        // Crear 20 parqueaderos
        for ($i = 1; $i <= 20; $i++) {
            $codigo = 'P-' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $unidad = $unidades[$i - 1];
            $residente = $residentes[$i - 1] ?? $residentes[0];
            $estado = $estados[rand(0, count($estados) - 1)];

            Parqueadero::create([
                'copropiedad_id' => $propiedad->id,
                'codigo' => $codigo,
                'tipo' => $tiposParqueadero[rand(0, count($tiposParqueadero) - 1)],
                'tipo_vehiculo' => $tiposVehiculo[rand(0, count($tiposVehiculo) - 1)],
                'nivel' => $nivelesParqueadero[rand(0, count($nivelesParqueadero) - 1)],
                'estado' => $estado,
                'es_cubierto' => rand(0, 1) == 1,
                'unidad_id' => $estado === 'asignado' ? $unidad->id : null,
                'residente_responsable_id' => $estado === 'asignado' ? $residente->id : null,
                'fecha_asignacion' => $estado === 'asignado' ? Carbon::now()->subMonths(rand(1, 12)) : null,
                'activo' => true,
            ]);
        }

        // Crear 20 depósitos
        for ($i = 1; $i <= 20; $i++) {
            $codigo = 'D-' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $unidad = $unidades[$i - 1];
            $residente = $residentes[$i - 1] ?? $residentes[0];
            $estado = $estados[rand(0, count($estados) - 1)];

            Deposito::create([
                'copropiedad_id' => $propiedad->id,
                'codigo' => $codigo,
                'nivel' => $nivelesDeposito[rand(0, count($nivelesDeposito) - 1)],
                'estado' => $estado,
                'area_m2' => rand(5, 15),
                'unidad_id' => $estado === 'asignado' ? $unidad->id : null,
                'residente_responsable_id' => $estado === 'asignado' ? $residente->id : null,
                'fecha_asignacion' => $estado === 'asignado' ? Carbon::now()->subMonths(rand(1, 12)) : null,
                'activo' => true,
            ]);
        }

        $this->command->info('   ✓ 20 parqueaderos y 20 depósitos creados');
    }

    /**
     * Crear 4 zonas comunes con horarios específicos
     */
    private function crearZonasComunes(Propiedad $propiedad): void
    {
        $this->command->info('🏋️‍♂️ Creando zonas comunes...');

        // 1. Gimnasio
        $gimnasio = $this->crearGimnasio($propiedad);

        // 2. Piscina
        $piscina = $this->crearPiscina($propiedad);

        // 3. Salón Social
        $salon = $this->crearSalonSocial($propiedad);

        // 4. Zona BBQ
        $bbq = $this->crearZonaBBQ($propiedad);

        $this->command->info('   ✓ 4 zonas comunes creadas con sus horarios');
    }

    /**
     * Crear Gimnasio
     */
    private function crearGimnasio(Propiedad $propiedad): ZonaSocial
    {
        $gimnasio = ZonaSocial::create([
            'propiedad_id' => $propiedad->id,
            'nombre' => 'Gimnasio',
            'descripcion' => 'Gimnasio completamente equipado con máquinas de cardio, pesas y área de entrenamiento funcional.',
            'ubicacion' => 'Torre 1 - Piso 1',
            'capacidad_maxima' => 8,
            'max_invitados_por_reserva' => 2,
            'tiempo_minimo_uso_horas' => 1,
            'tiempo_maximo_uso_horas' => 1,
            'reservas_simultaneas' => true,
            'valor_alquiler' => null,
            'valor_deposito' => null,
            'requiere_aprobacion' => false,
            'permite_reservas_en_mora' => true,
            'acepta_invitados' => true,
            'estado' => ZonaSocial::ESTADO_ACTIVA,
            'activo' => true,
        ]);

        // Horarios: lunes a domingo, 5:00 a.m. - 11:00 p.m., fraccionado en bloques de 59 minutos
        $dias = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
        
        foreach ($dias as $dia) {
            // Crear bloques de 5:00 a 23:00 (18 bloques: 5:00-5:59, 6:00-6:59, ..., 22:00-22:59, 23:00-23:59)
            for ($hora = 5; $hora <= 23; $hora++) {
                if ($hora == 23) {
                    // Último bloque: 23:00 - 23:59
                    ZonaSocialHorario::create([
                        'zona_social_id' => $gimnasio->id,
                        'dia_semana' => $dia,
                        'hora_inicio' => '23:00',
                        'hora_fin' => '23:59',
                        'activo' => true,
                    ]);
                } else {
                    ZonaSocialHorario::create([
                        'zona_social_id' => $gimnasio->id,
                        'dia_semana' => $dia,
                        'hora_inicio' => sprintf('%02d:00', $hora),
                        'hora_fin' => sprintf('%02d:59', $hora),
                        'activo' => true,
                    ]);
                }
            }
        }

        // Crear imagen del gimnasio
        ZonaSocialImagen::create([
            'zona_social_id' => $gimnasio->id,
            'url_imagen' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770320837/domoph/demo/Zona_gimnasio.png',
            'orden' => 0,
            'activo' => true,
        ]);

        // Crear reglas del gimnasio
        $this->crearReglasZona($gimnasio);

        return $gimnasio;
    }

    /**
     * Crear Piscina
     */
    private function crearPiscina(Propiedad $propiedad): ZonaSocial
    {
        $piscina = ZonaSocial::create([
            'propiedad_id' => $propiedad->id,
            'nombre' => 'Piscina',
            'descripcion' => 'Piscina olímpica con área de descanso, duchas y vestuarios. Incluye área de juegos infantiles.',
            'ubicacion' => 'Exterior - Zona Central',
            'capacidad_maxima' => 12,
            'max_invitados_por_reserva' => 4,
            'tiempo_minimo_uso_horas' => 1,
            'tiempo_maximo_uso_horas' => 1,
            'reservas_simultaneas' => true,
            'valor_alquiler' => null,
            'valor_deposito' => null,
            'requiere_aprobacion' => false,
            'permite_reservas_en_mora' => false,
            'acepta_invitados' => true,
            'estado' => ZonaSocial::ESTADO_ACTIVA,
            'activo' => true,
        ]);

        // Horarios: martes a domingo
        // AM: 6:00 a.m. - 11:00 a.m. (fraccionado)
        // PM: 3:00 p.m. - 8:00 p.m. (fraccionado)
        $dias = ['martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
        
        foreach ($dias as $dia) {
            // Horario AM: 6:00 - 11:00
            for ($hora = 6; $hora < 11; $hora++) {
                ZonaSocialHorario::create([
                    'zona_social_id' => $piscina->id,
                    'dia_semana' => $dia,
                    'hora_inicio' => sprintf('%02d:00', $hora),
                    'hora_fin' => sprintf('%02d:59', $hora),
                    'activo' => true,
                ]);
            }

            // Horario PM: 15:00 - 20:00
            for ($hora = 15; $hora < 20; $hora++) {
                ZonaSocialHorario::create([
                    'zona_social_id' => $piscina->id,
                    'dia_semana' => $dia,
                    'hora_inicio' => sprintf('%02d:00', $hora),
                    'hora_fin' => sprintf('%02d:59', $hora),
                    'activo' => true,
                ]);
            }
        }

        // Crear imagen de la piscina
        ZonaSocialImagen::create([
            'zona_social_id' => $piscina->id,
            'url_imagen' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770320836/domoph/demo/Zona_piscina.png',
            'orden' => 0,
            'activo' => true,
        ]);

        // Crear reglas de la piscina
        $this->crearReglasZona($piscina);

        return $piscina;
    }

    /**
     * Crear Salón Social
     */
    private function crearSalonSocial(Propiedad $propiedad): ZonaSocial
    {
        $salon = ZonaSocial::create([
            'propiedad_id' => $propiedad->id,
            'nombre' => 'Salón Social',
            'descripcion' => 'Amplio salón social con capacidad para eventos y reuniones. Incluye cocina, sistema de sonido y proyector.',
            'ubicacion' => 'Torre 1 - Piso 2',
            'capacidad_maxima' => 80,
            'max_invitados_por_reserva' => 80,
            'tiempo_minimo_uso_horas' => 2,
            'tiempo_maximo_uso_horas' => 12,
            'reservas_simultaneas' => false,
            'valor_alquiler' => 150000.00,
            'valor_deposito' => 300000.00,
            'requiere_aprobacion' => true,
            'permite_reservas_en_mora' => false,
            'acepta_invitados' => true,
            'estado' => ZonaSocial::ESTADO_ACTIVA,
            'activo' => true,
        ]);

        // Horarios: martes a domingo
        // Lunes a jueves: 9:00 a.m. - 10:00 p.m. (un solo bloque)
        // Viernes y sábado: 9:00 a.m. - 11:59 p.m. (un solo bloque)
        $dias = ['martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
        
        foreach ($dias as $dia) {
            if (in_array($dia, ['martes', 'miércoles', 'jueves', 'domingo'])) {
                // Lunes a jueves y domingo: 9:00 - 22:00
                ZonaSocialHorario::create([
                    'zona_social_id' => $salon->id,
                    'dia_semana' => $dia,
                    'hora_inicio' => '09:00',
                    'hora_fin' => '22:00',
                    'activo' => true,
                ]);
            } else {
                // Viernes y sábado: 9:00 - 23:59
                ZonaSocialHorario::create([
                    'zona_social_id' => $salon->id,
                    'dia_semana' => $dia,
                    'hora_inicio' => '09:00',
                    'hora_fin' => '23:59',
                    'activo' => true,
                ]);
            }
        }

        // Crear imagen del salón social
        ZonaSocialImagen::create([
            'zona_social_id' => $salon->id,
            'url_imagen' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770320836/domoph/demo/Zona_salon_social.png',
            'orden' => 0,
            'activo' => true,
        ]);

        // Crear reglas del salón social
        $this->crearReglasZona($salon);

        return $salon;
    }

    /**
     * Crear Zona BBQ
     */
    private function crearZonaBBQ(Propiedad $propiedad): ZonaSocial
    {
        $bbq = ZonaSocial::create([
            'propiedad_id' => $propiedad->id,
            'nombre' => 'Zona BBQ',
            'descripcion' => 'Área de parrillas con mesas, sillas y zona de descanso. Ideal para reuniones familiares.',
            'ubicacion' => 'Exterior - Zona Recreativa',
            'capacidad_maxima' => 30,
            'max_invitados_por_reserva' => 30,
            'tiempo_minimo_uso_horas' => 2,
            'tiempo_maximo_uso_horas' => 12,
            'reservas_simultaneas' => false,
            'valor_alquiler' => 80000.00,
            'valor_deposito' => 150000.00,
            'requiere_aprobacion' => true,
            'permite_reservas_en_mora' => false,
            'acepta_invitados' => true,
            'estado' => ZonaSocial::ESTADO_ACTIVA,
            'activo' => true,
        ]);

        // Horarios: martes a domingo
        // Lunes a jueves: 9:00 a.m. - 10:00 p.m. (un solo bloque)
        // Viernes y sábado: 9:00 a.m. - 11:59 p.m. (un solo bloque)
        $dias = ['martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
        
        foreach ($dias as $dia) {
            if (in_array($dia, ['martes', 'miércoles', 'jueves', 'domingo'])) {
                // Lunes a jueves y domingo: 9:00 - 22:00
                ZonaSocialHorario::create([
                    'zona_social_id' => $bbq->id,
                    'dia_semana' => $dia,
                    'hora_inicio' => '09:00',
                    'hora_fin' => '22:00',
                    'activo' => true,
                ]);
            } else {
                // Viernes y sábado: 9:00 - 23:59
                ZonaSocialHorario::create([
                    'zona_social_id' => $bbq->id,
                    'dia_semana' => $dia,
                    'hora_inicio' => '09:00',
                    'hora_fin' => '23:59',
                    'activo' => true,
                ]);
            }
        }

        // Crear imagen de la zona BBQ
        ZonaSocialImagen::create([
            'zona_social_id' => $bbq->id,
            'url_imagen' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770320837/domoph/demo/Zona_bbq.png',
            'orden' => 0,
            'activo' => true,
        ]);

        // Crear reglas de la zona BBQ
        $this->crearReglasZona($bbq);

        return $bbq;
    }

    /**
     * Crear reglas para una zona social
     */
    private function crearReglasZona(ZonaSocial $zona): void
    {
        // Reglas comunes para todas las zonas
        $reglas = [
            [
                'clave' => ZonaSocialRegla::CLAVE_MAX_RESERVAS_MES,
                'valor' => '3',
                'descripcion' => 'Número máximo de reservas que un residente puede hacer por mes',
            ],
            [
                'clave' => ZonaSocialRegla::CLAVE_DIAS_ANTICIPACION,
                'valor' => '7',
                'descripcion' => 'Días de anticipación mínimos para hacer una reserva',
            ],
            [
                'clave' => ZonaSocialRegla::CLAVE_HORAS_CANCELACION,
                'valor' => '48',
                'descripcion' => 'Horas mínimas de anticipación para cancelar una reserva',
            ],
            [
                'clave' => ZonaSocialRegla::CLAVE_PERMITE_INVITADOS,
                'valor' => $zona->acepta_invitados ? 'true' : 'false',
                'descripcion' => 'Indica si se permiten invitados en las reservas',
            ],
        ];

        // Reglas específicas según la configuración de la zona
        if ($zona->valor_deposito) {
            $reglas[] = [
                'clave' => ZonaSocialRegla::CLAVE_REQUIERE_DEPOSITO,
                'valor' => 'true',
                'descripcion' => 'Indica si se requiere depósito para reservar la zona',
            ];
        }

        if (!$zona->permite_reservas_en_mora) {
            $reglas[] = [
                'clave' => ZonaSocialRegla::CLAVE_BLOQUEAR_EN_MORA,
                'valor' => 'true',
                'descripcion' => 'Indica si se bloquean reservas para residentes en mora',
            ];
        }

        if ($zona->max_invitados_por_reserva) {
            $reglas[] = [
                'clave' => ZonaSocialRegla::CLAVE_MAX_INVITADOS,
                'valor' => (string) $zona->max_invitados_por_reserva,
                'descripcion' => 'Número máximo de invitados permitidos',
            ];
        }

        // Crear las reglas
        foreach ($reglas as $regla) {
            ZonaSocialRegla::create([
                'zona_social_id' => $zona->id,
                'clave' => $regla['clave'],
                'valor' => $regla['valor'],
                'descripcion' => $regla['descripcion'],
            ]);
        }
    }
}
