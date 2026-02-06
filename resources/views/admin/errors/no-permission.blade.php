@extends('admin.layouts.app')

@section('title', 'Acceso Denegado - domoPH')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <!-- Icono de bloqueo -->
            <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-red-100 mb-6">
                <svg class="h-12 w-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Acceso Denegado</h1>
            <p class="text-lg text-gray-600 mb-6">
                No tienes permisos para acceder a esta sección
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-gray-800">
                            Permisos requeridos
                        </h3>
                        <div class="mt-2 text-sm text-gray-600">
                            <p class="mb-2">Para acceder a esta sección necesitas uno de los siguientes permisos:</p>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($permissions as $permission)
                                    <li class="font-mono text-xs bg-gray-50 px-2 py-1 rounded">{{ $permission }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <p class="text-sm text-gray-600 mb-4">
                        Si crees que deberías tener acceso a esta sección, contacta al administrador del sistema.
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('admin.dashboard') }}" 
               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Volver al Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
