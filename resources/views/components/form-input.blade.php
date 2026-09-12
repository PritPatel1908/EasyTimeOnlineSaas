@props(['label', 'name', 'type' => 'text'])
<div class="form-group">
    <label for="{{ $name }}">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" {{ $attributes->merge(['class' => 'form-control']) }}>
    @error($name)<small class="text-danger">{{ $message }}</small>@enderror
</div>
