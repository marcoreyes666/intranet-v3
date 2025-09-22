<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Bienvenida --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __('Bienvenidos a Intranet!') }}
                </div>
            </div>

            {{-- Avisos --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">Avisos</h3>
                    @include('announcements.feed')
                </div>
            </div>

            {{-- Próximos eventos (10 días) --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                            Próximos eventos (10 días)
                        </h3>
                        <a href="{{ route('calendar.index') }}"
                           class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                            Ver calendario
                        </a>
                    </div>

                    @if(($upcoming ?? collect())->isEmpty())
                        <p class="text-sm text-gray-600 dark:text-gray-300">No hay eventos próximos.</p>
                    @else
                        <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($upcoming as $i)
                                <li class="py-2">
                                    <div class="flex items-start gap-3">
                                        <div class="shrink-0 mt-0.5 text-xs px-2 py-1 rounded
                                            {{ $i['type'] === 'birthday'
                                                ? 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300'
                                                : 'bg-gray-100 dark:bg-gray-700 dark:text-gray-200' }}">
                                            {{ optional($i['start'])->timezone(config('app.timezone'))->format('d/M') }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                @if($i['type'] === 'birthday')
                                                    🎂 Cumple: {{ $i['name'] ?? '' }}
                                                @else
                                                    {{ $i['title'] }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-600 dark:text-gray-300">
                                                @if($i['all_day'])
                                                    Todo el día
                                                @else
                                                    {{ optional($i['start'])->timezone(config('app.timezone'))->format('H:i') }}
                                                    @if($i['end'])
                                                        – {{ optional($i['end'])->timezone(config('app.timezone'))->format('H:i') }}
                                                    @endif
                                                @endif
                                                @if(!empty($i['location']))
                                                    • {{ $i['location'] }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
