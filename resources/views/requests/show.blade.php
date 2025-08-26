<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-bold">Detalle de la solicitud</h1>
    </x-slot>

    @extends('layouts.app')

    @section('content')
        <div class="p-6 max-w-5xl mx-auto space-y-6">
            {{-- Flash messages (opcional) --}}
            @if(session('ok'))
                <div class="rounded border border-green-200 bg-green-50 text-green-800 px-4 py-3">
                    {{ session('ok') }}
                </div>
            @endif
            @if($errors->any())
                <div class="rounded border border-red-200 bg-red-50 text-red-800 px-4 py-3">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Encabezado --}}
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">Solicitud #{{ $rq->id }}</h1>
                    <p class="text-sm text-gray-500">
                        Tipo: <span class="capitalize">{{ $rq->type }}</span> ·
                        Estado: <span class="font-medium">{{ str_replace('_', ' ', $rq->status) }}</span> ·
                        Creada: {{ $rq->created_at->format('d/m/Y H:i') }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Solicitante: <span class="font-medium">{{ $rq->user->name ?? '—' }}</span>
                        @if($rq->user && $rq->user->department)
                            · Departamento: <span class="font-medium">{{ $rq->user->department->name }}</span>
                        @endif
                    </p>
                </div>

                {{-- Descargar documento si ya existe --}}
                <div class="shrink-0">
                    @if($rq->documents && $rq->documents->count())
                        <a href="{{ route('requests.document', $rq) }}"
                            class="inline-flex items-center gap-2 rounded bg-primary text-white px-4 py-2 hover:opacity-90">
                            <i data-lucide="download" class="w-4 h-4"></i> Descargar documento
                        </a>
                    @else
                        <span class="inline-flex items-center gap-2 rounded border px-4 py-2 text-gray-500">
                            <i data-lucide="file" class="w-4 h-4"></i> Documento no disponible
                        </span>
                    @endif
                </div>
            </div>

            {{-- Datos capturados (payload) --}}
            <div class="rounded border bg-white dark:bg-gray-900 dark:border-gray-700">
                <div class="px-4 py-3 border-b dark:border-gray-700 font-semibold">Datos de la solicitud</div>
                <div class="p-4 overflow-x-auto">
                    @php
                        $pairs = collect($rq->payload ?? [])->map(function ($v, $k) {
                            // Mejorar legibilidad de la clave
                            $label = ucwords(str_replace(['_', '-'], ' ', $k));
                            // Formateos básicos
                            if (is_bool($v))
                                $v = $v ? 'Sí' : 'No';
                            if (is_array($v))
                                $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                            return ['k' => $label, 'v' => $v];
                        });
                    @endphp
                    @if($pairs->isEmpty())
                        <div class="text-sm text-gray-500">Sin datos capturados.</div>
                    @else
                        <table class="min-w-full text-sm">
                            <tbody class="divide-y dark:divide-gray-700">
                                @foreach($pairs as $row)
                                    <tr>
                                        <td class="px-3 py-2 font-medium w-1/3">{{ $row['k'] }}</td>
                                        <td class="px-3 py-2">{{ $row['v'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            {{-- Línea de aprobaciones --}}
            <div class="rounded border bg-white dark:bg-gray-900 dark:border-gray-700">
                <div class="px-4 py-3 border-b dark:border-gray-700 font-semibold">Aprobaciones</div>
                <div class="p-4">
                    @if(!$rq->approvals || $rq->approvals->isEmpty())
                        <div class="text-sm text-gray-500">Aún no hay pasos de aprobación creados.</div>
                    @else
                        <ol class="space-y-3">
                            @foreach($rq->approvals as $ap)
                                @php
                                    $badgeClasses = match ($ap->decision) {
                                        'aprobado' => 'bg-green-100 text-green-800 border-green-200',
                                        'rechazado' => 'bg-red-100 text-red-800 border-red-200',
                                        default => 'bg-yellow-50 text-yellow-800 border-yellow-200',
                                    };
                                    $stepLabel = ucfirst($ap->step); // encargado, contabilidad, compras, rectoria
                                @endphp
                                <li class="rounded border px-3 py-2 {{ $badgeClasses }}">
                                    <div class="flex justify-between items-center">
                                        <div class="font-medium">
                                            Paso: {{ $stepLabel }}
                                            @if($ap->approver)
                                                · Aprobador: {{ $ap->approver->name }}
                                            @endif
                                        </div>
                                        <div class="text-sm">
                                            Estado: <strong>{{ ucfirst($ap->decision) }}</strong>
                                            @if($ap->decided_at)
                                                · {{ \Illuminate\Support\Carbon::parse($ap->decided_at)->format('d/m/Y H:i') }}
                                            @endif
                                        </div>
                                    </div>
                                    @if($ap->comments)
                                        <div class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                            Comentarios: “{{ $ap->comments }}”
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </div>

            {{-- Bloque de acción: Aprobar / Rechazar (solo si tengo un paso pendiente) --}}
            @php
                $myPending = $rq->approvals
                    ->where('approver_id', auth()->id())
                    ->where('decision', 'pendiente')
                    ->first();
            @endphp

            @if($myPending)
                <div class="rounded border bg-white dark:bg-gray-900 dark:border-gray-700">
                    <div class="px-4 py-3 border-b dark:border-gray-700 font-semibold">
                        Tu decisión (Paso: {{ ucfirst($myPending->step) }})
                    </div>
                    <div class="p-4">
                        <form method="POST" action="{{ route('requests.approve', $rq) }}" class="space-y-4">
                            @csrf
                            <label class="block text-sm font-medium">Comentarios (opcional)</label>
                            <textarea name="comments" rows="3" class="w-full border rounded px-3 py-2"
                                placeholder="Agregar un comentario para el solicitante..."></textarea>

                            <div class="flex items-center gap-3">
                                <button type="submit"
                                    class="inline-flex items-center gap-2 rounded bg-green-600 text-white px-4 py-2 hover:bg-green-700">
                                    <i data-lucide="check" class="w-4 h-4"></i> Aprobar
                                </button>

                                {{-- Rechazar: envío a otra ruta --}}
                                <button type="button" onclick="document.getElementById('reject-form').submit()"
                                    class="inline-flex items-center gap-2 rounded bg-red-600 text-white px-4 py-2 hover:bg-red-700">
                                    <i data-lucide="x" class="w-4 h-4"></i> Rechazar
                                </button>
                            </div>
                        </form>

                        <form id="reject-form" method="POST" action="{{ route('requests.reject', $rq) }}" class="hidden">
                            @csrf
                            <input type="hidden" name="comments" value="" onfocus="/* placeholder para mantener estructura */">
                        </form>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sin JS de framework: rellena comentarios también al rechazar --}}
        <script>
            (function () {
                const approveTextarea = document.querySelector('form[action*="approve"] textarea[name="comments"]');
                const rejectForm = document.getElementById('reject-form');
                if (approveTextarea && rejectForm) {
                    const hiddenInput = rejectForm.querySelector('input[name="comments"]');
                    // Sincroniza el comentario entre ambos formularios
                    approveTextarea.addEventListener('input', () => {
                        hiddenInput.value = approveTextarea.value;
                    });
                }
            })();
        </script>
    @endsection

</x-app-layout>