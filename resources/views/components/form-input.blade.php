@props(['label', 'name', 'type' => 'text'])
<div class="form-group">
    <label for="{{ $name }}">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" {{ $attributes->merge(['class' => 'form-control']) }}>
    <x-validation-error :field="$name" />
</div>
