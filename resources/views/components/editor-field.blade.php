@props(['name', 'label' => null, 'value' => null])

<div>
    @if ($label)
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
        </label>
    @endif

    <div class="rounded-md border border-gray-300 bg-white shadow-sm"
         data-alumkit-editor
         data-upload-url="{{ route('alumkit.editor.image') }}"
         @if ($value) data-value="{{ $value }}" @endif>
        <input type="hidden" name="{{ $name }}" value="{{ $value ?? '' }}">
        <div class="alumkit-editor-holder"></div>
    </div>

    <link rel="stylesheet" href="{{ url('alumkit/style/alumkit-editor.css') }}">
    <script defer src="{{ url('alumkit/style/alumkit-editor.js') }}"></script>
</div>
