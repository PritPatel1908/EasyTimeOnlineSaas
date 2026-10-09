@props(['field', 'bag' => 'default'])

@php($errorMessage = $errors->getBag($bag)->first($field))

<div
    data-validation-error-for="{{ $field }}"
    {{ $attributes->merge(['class' => 'text-danger mt-1 mb-2']) }}
    role="alert"
    aria-live="polite"
    @if (!$errorMessage) hidden @endif
>{{ $errorMessage }}</div>
