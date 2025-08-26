@props(['name','label'=>null,'rows'=>3,'required'=>false,'placeholder'=>null])

<div class="space-y-1">
  @if($label)
    <label for="{{ $name }}" class="block text-sm font-medium">{{ $label }}</label>
  @endif
  <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
    @if($required) required @endif placeholder="{{ $placeholder }}"
    {{ $attributes->merge([
      'class'=>'mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:border-indigo-500 focus:ring-indigo-500'
    ]) }}
  >{{ old($name) }}</textarea>
  @error($name)<p class="text-sm text-red-600">{{ $message }}</p>@enderror
</div>
