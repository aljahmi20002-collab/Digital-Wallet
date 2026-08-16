@foreach(\App\Support\WithdrawFieldNormalizer::normalize($method->fields) as $field)
    <x-form.field
            :type="$field['type']"
            :name="'credentials['.$field['name'].']'"
            :label="$field['label'] ?? title($field['name'])"
            :placeholder="$field['placeholder'] ?? title($field['name'])"
            :value="$field['value'] ?? null"
            :required="($field['validation'] ?? null) === 'required'"
            :options="$field['options'] ?? []"
            :colClass="'col-md-6 single-input-inner style-border'"
    />
@endforeach
