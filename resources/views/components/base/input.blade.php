@props([
  'name',
  'label' => null,
  'type' => 'text',
  'value' => null,
  'required' => false,
  'placeholder' => null,
])

<div class="space-y-1">
  @if($label)
    <label for="{{ $name }}" class="block text-sm font-medium">{{ $label }}</label>
  @endif

  <input
    id="{{ $name }}"
    name="{{ $name }}"
    type="{{ $type }}"
    value="{{ old($name, $value) }}"
    placeholder="{{ $placeholder }}"
    @if($required) required @endif
    {{ $attributes->merge([
      'class' => 'mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:border-indigo-500 focus:ring-indigo-500'
    ]) }}
  />

  @error($name)
    <p class="text-sm text-red-600">{{ $message }}</p>
  @enderror
</div>
