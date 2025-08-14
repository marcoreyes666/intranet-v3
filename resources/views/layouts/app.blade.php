<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6">
                @auth
                    <aside class="col-span-12 md:col-span-3">
                        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                            <nav class="space-y-2">
                                @role('Administrador')
                                <a href="{{ route('admin.departments.index') }}"
                                    class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700
                                       {{ request()->routeIs('admin.departments.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                                    Departamentos
                                </a>
                                @endrole
                                <!-- aquí más links después -->
                            </nav>
                        </div>
                    </aside>
                @endauth

                <section class="@auth col-span-12 md:col-span-9 @else col-span-12 @endauth">
                    {{ $slot }}
                </section>
            </div>
        </main>
    </div>
</body>

</html>