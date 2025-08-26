@props(['type' => 'submit'])

<button type="{{ $type }}"
  {{ $attributes->merge([
    'class' => 'inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500'
  ]) }}>
  {{ $slot }}
</button>
