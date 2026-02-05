<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - domoPH Administrador</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-blue-600 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-lg shadow-xl px-8 pt-4 pb-8 w-full max-w-md">
        <div class="text-center mb-2">
            <div class="flex justify-center mb-1">
                <img src="{{ asset('imagenes/logo.png') }}" alt="domoPH Logo" class="h-40 md:h-56 w-auto object-contain">
            </div>
            <p class="text-gray-600"><b>Panel de Administrador</b></p>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">
                    Email
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror"
                    placeholder="tu@email.com"
                >
            </div>

            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">
                    Contraseña
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror"
                    placeholder="••••••••"
                >
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        class="mr-2"
                    >
                    <span class="text-sm text-gray-700">Recordarme</span>
                </label>
            </div>

            <button 
                type="submit" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full focus:outline-none focus:shadow-outline transition duration-150"
            >
                <i class="fas fa-lock mr-2"></i>
                Iniciar Sesión
            </button>
        </form>
        <div class="text-center text-sm text-gray-600 mt-4">
            <p>Credenciales Demo:</p>
            <p class="font-mono text-xs mt-1">demo@domoph.com / 12345678</p>
        </div>
    </div>
</body>
</html>
