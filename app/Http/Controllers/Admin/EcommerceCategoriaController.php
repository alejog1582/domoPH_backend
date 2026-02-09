<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EcommerceCategoriaController extends Controller
{
    /**
     * Mostrar la lista de categorías
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $query = DB::table('ecommerce_categorias');

        // Filtro por activo
        if ($request->filled('activo')) {
            $query->where('activo', $request->activo == '1');
        } else {
            // Por defecto mostrar solo activas
            $query->where('activo', true);
        }

        // Búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        $categorias = $query->orderBy('nombre', 'asc')
            ->paginate(15)
            ->appends($request->query());

        return view('admin.ecommerce-categorias.index', compact('categorias', 'propiedad'));
    }

    /**
     * Mostrar el formulario de creación de una categoría
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        return view('admin.ecommerce-categorias.create', compact('propiedad'));
    }

    /**
     * Guardar una nueva categoría
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'icono' => 'nullable|string|max:100',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
        ]);

        // Generar slug único
        $slug = Str::slug($request->nombre);
        $slugOriginal = $slug;
        $counter = 1;
        
        while (DB::table('ecommerce_categorias')->where('slug', $slug)->exists()) {
            $slug = $slugOriginal . '-' . $counter;
            $counter++;
        }

        DB::table('ecommerce_categorias')->insert([
            'nombre' => $request->nombre,
            'slug' => $slug,
            'descripcion' => $request->descripcion,
            'icono' => $request->icono,
            'activo' => $request->has('activo') ? true : false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.ecommerce-categorias.index')
            ->with('success', 'Categoría creada exitosamente.');
    }

    /**
     * Mostrar una categoría específica
     */
    public function show($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $categoria = DB::table('ecommerce_categorias')->where('id', $id)->first();

        if (!$categoria) {
            return redirect()->route('admin.ecommerce-categorias.index')
                ->with('error', 'Categoría no encontrada.');
        }

        return view('admin.ecommerce-categorias.show', compact('categoria', 'propiedad'));
    }

    /**
     * Mostrar el formulario de edición de una categoría
     */
    public function edit($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $categoria = DB::table('ecommerce_categorias')->where('id', $id)->first();

        if (!$categoria) {
            return redirect()->route('admin.ecommerce-categorias.index')
                ->with('error', 'Categoría no encontrada.');
        }

        return view('admin.ecommerce-categorias.edit', compact('categoria', 'propiedad'));
    }

    /**
     * Actualizar una categoría
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $categoria = DB::table('ecommerce_categorias')->where('id', $id)->first();

        if (!$categoria) {
            return redirect()->route('admin.ecommerce-categorias.index')
                ->with('error', 'Categoría no encontrada.');
        }

        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'icono' => 'nullable|string|max:100',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
        ]);

        // Si el nombre cambió, actualizar el slug
        $slug = $categoria->slug;
        if ($request->nombre !== $categoria->nombre) {
            $slug = Str::slug($request->nombre);
            $slugOriginal = $slug;
            $counter = 1;
            
            while (DB::table('ecommerce_categorias')
                ->where('slug', $slug)
                ->where('id', '!=', $id)
                ->exists()) {
                $slug = $slugOriginal . '-' . $counter;
                $counter++;
            }
        }

        DB::table('ecommerce_categorias')
            ->where('id', $id)
            ->update([
                'nombre' => $request->nombre,
                'slug' => $slug,
                'descripcion' => $request->descripcion,
                'icono' => $request->icono,
                'activo' => $request->has('activo') ? true : false,
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.ecommerce-categorias.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    /**
     * Eliminar una categoría
     */
    public function destroy($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $categoria = DB::table('ecommerce_categorias')->where('id', $id)->first();

        if (!$categoria) {
            return redirect()->route('admin.ecommerce-categorias.index')
                ->with('error', 'Categoría no encontrada.');
        }

        // Verificar si hay publicaciones usando esta categoría
        $publicacionesCount = DB::table('ecommerce_publicaciones')
            ->where('categoria_id', $id)
            ->count();

        if ($publicacionesCount > 0) {
            return redirect()->route('admin.ecommerce-categorias.index')
                ->with('error', "No se puede eliminar la categoría porque tiene {$publicacionesCount} publicación(es) asociada(s).");
        }

        DB::table('ecommerce_categorias')->where('id', $id)->delete();

        return redirect()->route('admin.ecommerce-categorias.index')
            ->with('success', 'Categoría eliminada exitosamente.');
    }
}
