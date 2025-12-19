<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Nueva solicitud de sonido
        </h2>
    </x-slot>

    <div class="intro-y box mt-5 p-5">
        @if(session('warning'))
            <div class="alert alert-warning mb-4">
                {{ session('warning') }}
            </div>
        @endif

        <form action="{{ route('sound-requests.store') }}" method="POST">
            @include('sound_requests._form')
        </form>
    </div>
</x-app-layout>
