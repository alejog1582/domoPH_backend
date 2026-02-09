<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Propiedad;
use App\Models\Residente;
use Carbon\Carbon;

class EcommerceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🛒 Iniciando creación de datos de Ecommerce...');

        // Obtener la propiedad demo
        $propiedad = Propiedad::where('email', 'demo@domoph.com')->first();

        if (!$propiedad) {
            $this->command->warn('   ⚠ No se encontró la propiedad demo. Ejecuta primero el DemoSeeder.');
            return;
        }

        // Crear categorías si no existen
        $this->crearCategorias();

        // Obtener categorías
        $categoriaParqueaderos = DB::table('ecommerce_categorias')->where('slug', 'parqueaderos')->first();
        $categoriaProductos = DB::table('ecommerce_categorias')->where('slug', 'productos')->first();
        $categoriaServicios = DB::table('ecommerce_categorias')->where('slug', 'servicios')->first();
        $categoriaDepositos = DB::table('ecommerce_categorias')->where('slug', 'depositos')->first();

        // Obtener algunos residentes de la propiedad con sus usuarios
        $residentes = Residente::with('user')
            ->whereHas('unidad', function ($query) use ($propiedad) {
                $query->where('propiedad_id', $propiedad->id);
            })
            ->limit(8)
            ->get();

        if ($residentes->isEmpty()) {
            $this->command->warn('   ⚠ No se encontraron residentes. Ejecuta primero el DemoSeeder.');
            return;
        }

        // Crear 8 publicaciones variadas con sus imágenes
        $publicaciones = [
            [
                'titulo' => 'Parqueadero en venta - Torre 1',
                'descripcion' => 'Parqueadero cubierto ubicado en el sótano 1, ideal para vehículo mediano. Excelente ubicación cerca del ascensor. Incluye bodega pequeña.',
                'tipo_publicacion' => 'venta',
                'categoria_id' => $categoriaParqueaderos->id,
                'precio' => 15000000,
                'es_negociable' => true,
                'imagen' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770653994/domoph/demo/Imagen_publicacion1_ecomm.png',
            ],
            [
                'titulo' => 'Servicio de limpieza de apartamentos',
                'descripcion' => 'Ofrezco servicio profesional de limpieza de apartamentos. Incluye limpieza profunda, organización y mantenimiento. Precio por hora o servicio completo.',
                'tipo_publicacion' => 'servicio',
                'categoria_id' => $categoriaServicios->id,
                'precio' => 50000,
                'es_negociable' => false,
                'imagen' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770654277/domoph/demo/Imagen_publicacion2_ecomm.png',
            ],
            [
                'titulo' => 'Sofá cama en excelente estado',
                'descripcion' => 'Sofá cama de 2 plazas, color gris, en perfecto estado. Incluye cojines y fundas. Ideal para espacios pequeños. Se entrega en el conjunto.',
                'tipo_publicacion' => 'venta',
                'categoria_id' => $categoriaProductos->id,
                'precio' => 350000,
                'es_negociable' => true,
                'imagen' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770654643/domoph/demo/Imagen_publicacion3_ecomm.png',
            ],
            [
                'titulo' => 'Parqueadero en arriendo mensual',
                'descripcion' => 'Parqueadero descubierto disponible para arriendo mensual. Ubicado en la zona exterior, fácil acceso. Ideal para segundo vehículo o moto.',
                'tipo_publicacion' => 'arriendo',
                'categoria_id' => $categoriaParqueaderos->id,
                'precio' => 150000,
                'es_negociable' => false,
                'imagen' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770655705/domoph/demo/Imagen_publicacion4_ecomm.png',
            ],
            [
                'titulo' => 'Servicio de plomería y electricidad',
                'descripcion' => 'Técnico certificado ofrece servicios de plomería y electricidad. Reparaciones, instalaciones y mantenimiento. Precios justos y trabajo garantizado.',
                'tipo_publicacion' => 'servicio',
                'categoria_id' => $categoriaServicios->id,
                'precio' => 80000,
                'es_negociable' => true,
                'imagen' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770655893/domoph/demo/Imagen_publicacion5_ecomm.png',
            ],
            [
                'titulo' => 'Depósito en venta - Sótano 2',
                'descripcion' => 'Depósito de 12 m² en venta. Ubicado en sótano 2, fácil acceso. Perfecto para almacenar muebles, herramientas o uso como bodega.',
                'tipo_publicacion' => 'venta',
                'categoria_id' => $categoriaDepositos->id,
                'precio' => 8000000,
                'es_negociable' => true,
                'imagen' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770656095/domoph/demo/Imagen_publicacion6_ecomm.png',
            ],
            [
                'titulo' => 'Lavadora automática Samsung',
                'descripcion' => 'Lavadora automática Samsung de 15 kg, 2 años de uso, en perfecto estado. Incluye manual y garantía. Se entrega en el conjunto, precio negociable.',
                'tipo_publicacion' => 'venta',
                'categoria_id' => $categoriaProductos->id,
                'precio' => 1200000,
                'es_negociable' => true,
                'imagen' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770656345/domoph/demo/Imagen_publicacion7_ecomm.png',
            ],
            [
                'titulo' => 'Servicio de jardinería y mantenimiento',
                'descripcion' => 'Servicio profesional de jardinería y mantenimiento de zonas verdes. Podas, riego, fertilización y diseño de jardines. Precio por visita o mensual.',
                'tipo_publicacion' => 'servicio',
                'categoria_id' => $categoriaServicios->id,
                'precio' => 200000,
                'es_negociable' => true,
                'imagen' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770656378/domoph/demo/Imagen_publicacion8_ecomm.png',
            ],
        ];

        foreach ($publicaciones as $index => $publicacionData) {
            $residente = $residentes[$index % $residentes->count()];
            $fechaPublicacion = Carbon::now()->subDays(rand(1, 30));

            // Crear publicación
            $publicacionId = DB::table('ecommerce_publicaciones')->insertGetId([
                'copropiedad_id' => $propiedad->id,
                'residente_id' => $residente->id,
                'categoria_id' => $publicacionData['categoria_id'],
                'tipo_publicacion' => $publicacionData['tipo_publicacion'],
                'titulo' => $publicacionData['titulo'],
                'descripcion' => $publicacionData['descripcion'],
                'precio' => $publicacionData['precio'],
                'moneda' => 'COP',
                'es_negociable' => $publicacionData['es_negociable'],
                'estado' => 'publicado',
                'fecha_publicacion' => $fechaPublicacion,
                'fecha_cierre' => null,
                'activo' => true,
                'created_at' => $fechaPublicacion,
                'updated_at' => $fechaPublicacion,
            ]);

            // Obtener información del usuario del residente para contacto
            $user = $residente->user;
            $telefono = $user->telefono ?? '300' . rand(1000000, 9999999);
            $nombreContacto = $user->nombre ?? 'Residente';

            // Crear contacto
            DB::table('ecommerce_publicacion_contactos')->insert([
                'publicacion_id' => $publicacionId,
                'nombre_contacto' => $nombreContacto,
                'telefono' => $telefono,
                'whatsapp' => true,
                'email' => $user->email ?? null,
                'observaciones' => 'Contactar preferiblemente por WhatsApp',
                'created_at' => $fechaPublicacion,
                'updated_at' => $fechaPublicacion,
            ]);

            // Crear imagen si está definida
            if (isset($publicacionData['imagen']) && !empty($publicacionData['imagen'])) {
                DB::table('ecommerce_publicacion_imagenes')->insert([
                    'publicacion_id' => $publicacionId,
                    'ruta_imagen' => $publicacionData['imagen'],
                    'orden' => 0,
                    'created_at' => $fechaPublicacion,
                    'updated_at' => $fechaPublicacion,
                ]);
            }

            $this->command->info("   ✓ Publicación creada: {$publicacionData['titulo']}");
        }

        $this->command->info('✅ Datos de Ecommerce creados exitosamente!');
        $this->command->info('   ✓ 8 publicaciones creadas con sus contactos e imágenes');
    }

    /**
     * Crear categorías del ecommerce si no existen
     */
    private function crearCategorias(): void
    {
        $categorias = [
            [
                'nombre' => 'Parqueaderos',
                'slug' => 'parqueaderos',
                'descripcion' => 'Parqueaderos en venta o arriendo',
                'icono' => 'parking',
                'activo' => true,
            ],
            [
                'nombre' => 'Depósitos',
                'slug' => 'depositos',
                'descripcion' => 'Depósitos y bodegas en venta o arriendo',
                'icono' => 'warehouse',
                'activo' => true,
            ],
            [
                'nombre' => 'Servicios',
                'slug' => 'servicios',
                'descripcion' => 'Servicios ofrecidos por residentes',
                'icono' => 'services',
                'activo' => true,
            ],
            [
                'nombre' => 'Productos',
                'slug' => 'productos',
                'descripcion' => 'Productos en venta',
                'icono' => 'products',
                'activo' => true,
            ],
            [
                'nombre' => 'Inmuebles',
                'slug' => 'inmuebles',
                'descripcion' => 'Inmuebles en venta o arriendo',
                'icono' => 'real-estate',
                'activo' => true,
            ],
            [
                'nombre' => 'Otros',
                'slug' => 'otros',
                'descripcion' => 'Otras publicaciones',
                'icono' => 'other',
                'activo' => true,
            ],
        ];

        foreach ($categorias as $categoria) {
            DB::table('ecommerce_categorias')->updateOrInsert(
                ['slug' => $categoria['slug']],
                array_merge($categoria, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('   ✓ Categorías del ecommerce creadas/verificadas');
    }
}
