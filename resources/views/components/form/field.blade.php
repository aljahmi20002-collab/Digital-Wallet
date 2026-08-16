@php
    $errorKey = preg_replace('/\[(.*?)\]/', '.$1', $name);
    $errorKey = rtrim((string) $errorKey, '.');
    $fieldId = preg_replace('/[^A-Za-z0-9_-]/', '_', $name);
@endphp

<div class="{{ $colClass }}">
    <label for="{{ $fieldId }}" class="form-label">{{ $label }}</label>
    @if ($type === 'text' || $type === 'file')
        <input
                type="{{ $type }}"
                name="{{ $name }}"
                class="form-control @error($errorKey) is-invalid @enderror"
                id="{{ $fieldId }}"
                placeholder="{{ $placeholder }}"
                value="{{ $type === 'text' ? old($errorKey, $value) : '' }}"
                {{ $required ? 'required' : '' }}>
    @elseif ($type === 'textarea')
        <textarea
                name="{{ $name }}"
                class="form-control @error($errorKey) is-invalid @enderror"
                id="{{ $fieldId }}"
                placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
    >{{ old($errorKey, $value) }}</textarea>
    @elseif ($type === 'select')
        <select
                name="{{ $name }}"
                class="form-select @error($errorKey) is-invalid @enderror"
                id="{{ $fieldId }}"
            {{ $required ? 'required' : '' }}>
            <option value="" disabled @selected(blank(old($errorKey, $value)))>{{ $placeholder }}</option>
            @foreach($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) old($errorKey, $value) === (string) $optionValue)>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
    @endif

    @error($errorKey)
    <div class="invalid-feedback">
        {{ $message }}
    </div>
    @enderror
</div>
