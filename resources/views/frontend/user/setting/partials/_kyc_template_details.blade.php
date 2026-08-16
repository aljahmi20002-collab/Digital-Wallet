{{--
    KYC template field group partial.
    Rendered inside the parent verification form; must NOT contain a nested <form>.
--}}
<div class="kyc-template-fields">
    <header class="kyc-template-fields__header">
        <h6 class="kyc-template-fields__title">{{ $template->title }}</h6>
        @if(! empty($template->description))
            <p class="kyc-template-fields__description">{{ $template->description }}</p>
        @endif
    </header>

    <div class="row g-3 kyc-template-fields__grid">
        @foreach($template->fields as $field)
            @php
                $fieldLabel    = ucfirst(str_replace('_', ' ', $field['label']));
                $fieldKey      = $field['label'];
                $fieldType     = $field['type'] ?? 'text';
                $isRequired    = ! empty($field['validation']);
                $fieldId       = 'kyc-credential-' . \Illuminate\Support\Str::slug($fieldKey);
                $fieldName     = "credentials[{$fieldKey}]";
            @endphp

            <div class="col-md-6 kyc-template-fields__item">
                <label for="{{ $fieldId }}" class="form-label">
                    {{ $fieldLabel }}
                    @if($isRequired)
                        <span class="text-danger" aria-hidden="true">*</span>
                    @endif
                </label>

                @if($fieldType === 'file')
                    <input type="file"
                           id="{{ $fieldId }}"
                           name="{{ $fieldName }}"
                           class="form-control"
                           @if($isRequired) required @endif>
                @elseif($fieldType === 'textarea')
                    <textarea id="{{ $fieldId }}"
                              name="{{ $fieldName }}"
                              class="form-control rounded"
                              rows="3"
                              placeholder="{{ $fieldLabel }}"
                              @if($isRequired) required @endif></textarea>
                @else
                    <input type="{{ $fieldType }}"
                           id="{{ $fieldId }}"
                           name="{{ $fieldName }}"
                           class="form-control"
                           placeholder="{{ $fieldLabel }}"
                           @if($isRequired) required @endif>
                @endif
            </div>
        @endforeach
    </div>
</div>
