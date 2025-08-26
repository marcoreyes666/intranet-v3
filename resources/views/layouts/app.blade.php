<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- MUY IMPORTANTE PARA FETCH POST/PUT/DELETE --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- Fuentes / estilos propios si los tienes --}}
        <!-- <link rel="preconnect" href="https://fonts.bunny.net"> -->
        <!-- <link href="..." rel="stylesheet" /> -->

        {{-- Vite de Breeze (no lo quites) --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            {{-- Barra superior de Breeze --}}
            @include('layouts.navigation')

            {{-- Contenedor con SIDEBAR + CONTENIDO --}}
            <div class="flex">
                {{-- SIDEBAR (ocupa 16rem) --}}
                @include('partials.sidebar')

                {{-- CONTENIDO --}}
                <div class="flex-1">
                    @isset($header)
                        <header class="bg-white dark:bg-gray-800 shadow">
                            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <main class="p-4">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>

        {{-- Breeze trae este stack para modales; lo dejamos --}}
        @stack('modals')

        {{-- ⭐️ CLAVE: sin esto, nada de lo que pusiste con @push('scripts') se imprime --}}
        @stack('scripts')
    </body>
</html>
