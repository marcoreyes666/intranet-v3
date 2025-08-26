@props(['name','label'=>null,'options'=>[],'required'=>false])

<div class="space-y-1">
  @if($label)
    <label for="{{ $name }}" class="block text-sm font-medium">{{ $label }}</label>
  @endif
  <select id="{{ $name }}" name="{{ $name }}" @if($required) required @endif
    {{ $attributes->merge([
      'class'=>'mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:border-indigo-500 focus:ring-indigo-500'
    ]) }}>
    <option value="" disabled @selected(!old($name))>Seleccione…</option>
    @foreach($options as $val => $text)
      <option value="{{ $val }}" @selected(old($name)==$val)>{{ $text }}</option>
    @endforeach
  </select>
  @error($name)<p class="text-sm text-red-600">{{ $message }}</p>@enderror
</div>
