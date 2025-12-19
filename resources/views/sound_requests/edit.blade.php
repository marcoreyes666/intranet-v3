<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar solicitud de sonido
        </h2>
    </x-slot>

    <div class="intro-y box mt-5 p-5">
        <form action="{{ route('sound-requests.update', $soundRequest) }}" method="POST">
            @method('PUT')
            @include('sound_requests._form', ['soundRequest' => $soundRequest])
        </form>

        @if($soundRequest->is_late)
            <div class="alert alert-warning mt-4">
                Esta solicitud es extemporánea (menos de 3 días de anticipación). El departamento de sistemas deberá valorar si es posible atenderla.
            </div>
        @endif

        @if($soundRequest->review_comment)
            <div class="alert alert-info mt-4">
                <strong>Comentario de sistemas:</strong> {{ $soundRequest->review_comment }}
            </div>
        @endif
    </div>
</x-app-layout>
