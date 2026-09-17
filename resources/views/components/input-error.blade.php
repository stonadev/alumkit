@props(['name'])

@error($name)
    <p {{ $attributes->class(['mt-1.5 text-sm font-medium text-error']) }} role="alert">{{ $message }}</p>
@enderror
