<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - domoPH SuperAdmin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- domoPH Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/domoph-styles.css') }}">
    
    <style>
        .login-background {
            background: linear-gradient(135deg, hsl(217, 91%, 54%) 0%, hsl(142, 71%, 45%) 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="login-background min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8 px-10 pt-4 pb-10 bg-white rounded-xl shadow-2xl">
        <div class="text-center mb-2">
            <div class="flex justify-center mb-1">
                <img src="{{ asset('imagenes/logo.png') }}" alt="domoPH Logo" class="h-40 md:h-56 w-auto object-contain">
            </div>
            <p class="text-sm text-gray-600">
                <b>Panel de Superadministrador</b>
            </p>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('superadmin.login.post') }}" method="POST">
            @csrf
            
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">Email</label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        autocomplete="email" 
                        required 
                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                        placeholder="Correo electrónico"
                        value="{{ old('email') }}"
                    >
                </div>
                <div>
                    <label for="password" class="sr-only">Contraseña</label>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        autocomplete="current-password" 
                        required 
                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                        placeholder="Contraseña"
                    >
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input 
                        id="remember" 
                        name="remember" 
                        type="checkbox" 
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="remember" class="ml-2 block text-sm text-gray-900">
                        Recordarme
                    </label>
                </div>
            </div>

            <div>
                <button 
                    type="submit" 
                    class="btn-gradient-primary w-full relative"
                >
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-lock text-white"></i>
                    </span>
                    Iniciar Sesión
                </button>
            </div>
        </form>
    </div>
</body>
</html>
