<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Solicitud #{{ $requestForm->id }} — {{ ucfirst($requestForm->type) }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('requests.index') }}" class="btn btn-secondary">Volver</a>
                <a href="{{ request()->fullUrlWithQuery(['export'=>'pdf']) }}" class="btn btn-outline">Exportar PDF</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-12 gap-6">
        {{-- Detalle --}}
        <div class="col-span-12 lg:col-span-7">
            <div class="bg-white dark:bg-gray-800 p-6 rounded shadow space-y-4">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-sm">Estado:
                        <span class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 capitalize">{{ $requestForm->status }}</span>
                    </span>
                    <span class="text-sm">Nivel actual: {{ $requestForm->current_level }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500">Solicitante</div>
                        <div>{{ $requestForm->user->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Departamento</div>
                        <div>{{ $requestForm->department->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Creada</div>
                        <div>{{ $requestForm->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Enviada a revisión</div>
                        <div>{{ $requestForm->submitted_at?->format('d/m/Y H:i') ?? '—' }}</div>
                    </div>
                </div>

                {{-- Detalle por tipo --}}
                @if($requestForm->type === 'permiso' && $requestForm->permiso)
                    <div class="border-t pt-4">
                        <h3 class="font-medium mb-2">Detalle del permiso</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-slate-500">Fecha</div>
                                <div>{{ $requestForm->permiso->date?->format('d/m/Y') }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-slate-500">Motivo</div>
                                <div>{{ $requestForm->permiso->reason ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-slate-500">Salida</div>
                                <div>{{ $requestForm->permiso->start_time?->format('H:i') ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-slate-500">Regreso</div>
                                <div>{{ $requestForm->permiso->end_time?->format('H:i') ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($requestForm->type === 'cheque' && $requestForm->cheque)
                    <div class="border-t pt-4">
                        <h3 class="font-medium mb-2">Detalle del cheque</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-slate-500">A favor de</div>
                                <div>{{ $requestForm->cheque->pay_to }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-slate-500">Moneda</div>
                                <div>{{ $requestForm->cheque->currency }}</div>
                            </div>
                            <div class="sm:col-span-2">
                                <div class="text-xs text-slate-500">Concepto</div>
                                <div>{{ $requestForm->cheque->concept }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-slate-500">Importe</div>
                                <div>{{ number_format($requestForm->cheque->amount,2) }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($requestForm->type === 'compra' && $requestForm->compra)
                    <div class="border-t pt-4 space-y-3">
                        <h3 class="font-medium">Detalle de la compra</h3>
                        <div>
                            <div class="text-xs text-slate-500">Justificación</div>
                            <div>{{ $requestForm->compra->justification }}</div>
                        </div>
                        @if($requestForm->compra->urls)
                            <div>
                                <div class="text-xs text-slate-500">URLs</div>
                                <ul class="list-disc ml-6">
                                    @foreach($requestForm->compra->urls as $u)
                                        <li><a href="{{ $u }}" class="text-blue-600 underline" target="_blank">{{ $u }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="overflow-x-auto rounded border">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr><th class="px-3 py-2 text-left">Cantidad</th><th class="px-3 py-2 text-left">Unidad</th><th class="px-3 py-2 text-left">Descripción</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($requestForm->compra->items as $it)
                                        <tr class="border-t">
                                            <td class="px-3 py-2">{{ rtrim(rtrim(number_format($it->qty,2,'.',''), '0'),'.') }}</td>
                                            <td class="px-3 py-2">{{ $it->unit }}</td>
                                            <td class="px-3 py-2">{{ $it->description }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($requestForm->status === 'completada')
                            <div class="text-xs text-slate-600">
                                Entregada el {{ $requestForm->compra->delivered_at?->format('d/m/Y H:i') }}
                                por {{ $requestForm->compra->completedBy?->name }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Flujo y acciones --}}
        <div class="col-span-12 lg:col-span-5 space-y-6">
            <div class="bg-white dark:bg-gray-800 p-6 rounded shadow">
                <h3 class="font-medium mb-3">Flujo de aprobación</h3>
                <div class="space-y-3">
                    @foreach($requestForm->approvals->sortBy('level') as $ap)
                        <div class="flex items-start justify-between gap-2 border-b pb-2 last:border-b-0">
                            <div>
                                <div class="text-sm">Nivel {{ $ap->level }} — {{ $ap->role }}</div>
                                <div class="text-xs text-slate-500">
                                    Estado: <span class="capitalize">{{ $ap->state }}</span>
                                    @if($ap->decided_at)
                                        • {{ $ap->decided_at->format('d/m/Y H:i') }} por {{ $ap->decider?->name }}
                                    @endif
                                </div>
                                @if($ap->comment)
                                    <div class="text-xs text-slate-600 mt-1">“{{ $ap->comment }}”</div>
                                @endif
                            </div>
                            <span class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 capitalize">{{ $ap->state }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            @can('approve', $requestForm)
            @if($requestForm->status === 'en_revision')
            <div class="bg-white dark:bg-gray-800 p-6 rounded shadow">
                <h3 class="font-medium mb-3">Decisión</h3>
                <form method="POST" action="{{ route('requests.approve',$requestForm) }}" class="space-y-3">
                    @csrf
                    <textarea name="comment" class="form-textarea w-full" rows="2" placeholder="Comentario (opcional)"></textarea>
                    <button class="btn btn-success w-full">Aprobar</button>
                </form>
                <form method="POST" action="{{ route('requests.reject',$requestForm) }}" class="space-y-3 mt-3">
                    @csrf
                    <textarea name="comment" class="form-textarea w-full" rows="2" placeholder="Motivo de rechazo"></textarea>
                    <button class="btn btn-danger w-full">Rechazar</button>
                </form>
            </div>
            @endif
            @endcan

            @can('complete', $requestForm)
            @if($requestForm->type === 'compra' && $requestForm->status === 'aprobada')
            <div class="bg-white dark:bg-gray-800 p-6 rounded shadow">
                <h3 class="font-medium mb-3">Finalizar compra</h3>
                <form method="POST" action="{{ route('requests.complete',$requestForm) }}">
                    @csrf
                    <button class="btn btn-primary w-full">Marcar como Completada</button>
                </form>
            </div>
            @endif
            @endcan
        </div>
    </div>
</x-app-layout>
