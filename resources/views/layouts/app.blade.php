<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name', 'Laravel') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>[x-cloak]{display:none!important}</style>
</head>

<body class="font-sans antialiased">
<div class="min-h-screen bg-gray-100 dark:bg-gray-900">
  @includeIf('layouts.navigation')

  @auth
  <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
      <div class="flex items-center justify-end gap-3">
        @include('partials.notifications-bell')
      </div>
    </div>
  </div>
  @endauth

  <div class="flex">
    @includeIf('partials.sidebar')

    <div class="flex-1">
      @if (isset($header))
        <header class="bg-white dark:bg-gray-800 shadow">
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            {{ $header }}
          </div>
        </header>
      @elseif(View::hasSection('header'))
        <header class="bg-white dark:bg-gray-800 shadow">
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            @yield('header')
          </div>
        </header>
      @endif

      <main class="p-4">
        @isset($slot)
          {{ $slot }}
        @else
          @yield('content')
        @endisset
      </main>
    </div>
  </div>
</div>

@stack('modals')
@stack('scripts')
</body>
</html>
