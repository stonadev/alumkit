<div>
    @foreach ($rows as $index => $row)
        <div class="border border-gray-200 rounded-lg p-4 mb-3 bg-gray-50">
            <div class="flex justify-between items-start mb-3">
                <span class="text-sm font-medium text-gray-700">Row {{ $index + 1 }}</span>
                <button type="button" wire:click="removeRow({{ $index }})" class="text-red-600 hover:text-red-900 text-sm">
                    Remove
                </button>
            </div>

            <div class="space-y-3">
                @foreach ($fields as $field)
                    @php
                        $fieldName = "{$name}[{$index}][{$field->name}]";
                        $fieldValue = $row[$field->name] ?? '';
                    @endphp

                    @if ($field->type === 'text')
                        <x-input :name="$fieldName" :label="$field->label ?? ucfirst($field->name)" :value="$fieldValue" />
                    @elseif ($field->type === 'textarea')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $field->label ?? ucfirst($field->name) }}
                            </label>
                            <textarea name="{{ $fieldName }}" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring-gold">{{ $fieldValue }}</textarea>
                        </div>
                    @elseif ($field->type === 'select')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $field->label ?? ucfirst($field->name) }}
                            </label>
                            <select name="{{ $fieldName }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring-gold">
                                <option value="">Select...</option>
                                @foreach (($field->options ?? []) as $value => $label)
                                    <option value="{{ $value }}" @selected($fieldValue === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif ($field->type === 'checkbox')
                        <x-alumkit::checkbox :name="$fieldName" :label="$field->label ?? ucfirst($field->name)" :checked="$fieldValue" />
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach

    <button type="button" wire:click="addRow" class="text-navy hover:text-gold text-sm font-medium">
        + Add Row
    </button>
</div>
