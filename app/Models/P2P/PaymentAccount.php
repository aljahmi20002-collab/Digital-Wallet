<?php

declare(strict_types=1);

namespace App\Models\P2P;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'p2p_payment_accounts';

    protected $fillable = [
        'user_id',
        'payment_method_id',
        'label',
        'account_name',
        'account_number',
        'instructions',
        'field_values',
        'display_name',
        'display_value',
    ];

    protected $casts = [
        'field_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    protected function effectiveLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->label ?: $this->display_name ?: $this->account_name ?: __('Payment Account')
        );
    }

    public function normalizedFieldValues(?PaymentMethod $method = null): array
    {
        $method ??= $this->relationLoaded('paymentMethod') ? $this->paymentMethod : $this->paymentMethod()->first();

        $values = is_array($this->field_values) ? $this->field_values : [];
        if ($values !== []) {
            return $values;
        }

        $legacy = [
            'account_name'   => (string) ($this->account_name ?? ''),
            'account_number' => (string) ($this->account_number ?? ''),
            'instructions'   => (string) ($this->instructions ?? ''),
        ];

        if (! $method) {
            return array_filter($legacy, fn ($value) => trim((string) $value) !== '');
        }

        $normalized = [];
        foreach ($method->normalizedFields() as $field) {
            $key = (string) ($field['key'] ?? '');
            if ($key === '') {
                continue;
            }

            if (array_key_exists($key, $legacy) && trim((string) $legacy[$key]) !== '') {
                $normalized[$key] = $legacy[$key];
            }
        }

        return $normalized !== [] ? $normalized : array_filter($legacy, fn ($value) => trim((string) $value) !== '');
    }

    public function renderedDetails(?PaymentMethod $method = null): array
    {
        $method ??= $this->relationLoaded('paymentMethod') ? $this->paymentMethod : $this->paymentMethod()->first();
        $values  = $this->normalizedFieldValues($method);
        $details = [];

        if ($method) {
            foreach ($method->normalizedFields() as $field) {
                $key   = (string) ($field['key'] ?? '');
                $value = $values[$key] ?? null;
                if (trim((string) $value) === '') {
                    continue;
                }

                $details[] = [
                    'key'   => $key,
                    'label' => (string) ($field['label'] ?? $key),
                    'value' => (string) $value,
                    'type'  => (string) ($field['type'] ?? 'text'),
                ];
            }
        }

        if ($details === []) {
            foreach ($values as $key => $value) {
                if (trim((string) $value) === '') {
                    continue;
                }

                $details[] = [
                    'key'   => (string) $key,
                    'label' => ucwords(str_replace('_', ' ', (string) $key)),
                    'value' => (string) $value,
                    'type'  => 'text',
                ];
            }
        }

        return $details;
    }

    public function syncDerivedAttributes(?PaymentMethod $method = null): void
    {
        $method ??= $this->relationLoaded('paymentMethod') ? $this->paymentMethod : $this->paymentMethod()->first();
        $details = $this->renderedDetails($method);

        $primary   = $details[0]['value'] ?? $this->account_name ?? '';
        $secondary = $details[1]['value'] ?? $this->account_number ?? '';
        $values    = $this->normalizedFieldValues($method);

        $this->display_name  = trim((string) ($primary ?: $this->display_name ?: '')) ?: null;
        $this->display_value = trim((string) ($secondary ?: $this->display_value ?: '')) ?: null;
        $this->label         = trim((string) ($this->label ?: $this->display_name ?: '')) ?: null;

        if (array_key_exists('account_name', $values) && trim((string) $values['account_name']) !== '') {
            $this->account_name = (string) $values['account_name'];
        }

        if (array_key_exists('account_number', $values) && trim((string) $values['account_number']) !== '') {
            $this->account_number = (string) $values['account_number'];
        }

        $this->account_name = trim((string) ($this->account_name ?? '')) !== ''
            ? (string) $this->account_name
            : (string) ($this->display_name ?? '');

        $this->account_number = trim((string) ($this->account_number ?? '')) !== ''
            ? (string) $this->account_number
            : (string) ($this->display_value ?? '');

        if (array_key_exists('instructions', $values)) {
            $this->instructions = trim((string) $values['instructions']) !== '' ? (string) $values['instructions'] : null;
        }
    }

    public function toTradeSnapshot(): array
    {
        $method  = $this->relationLoaded('paymentMethod') ? $this->paymentMethod : $this->paymentMethod()->first();
        $details = $this->renderedDetails($method);
        $values  = $this->normalizedFieldValues($method);

        return [
            'id'                  => (int) $this->id,
            'payment_method_id'   => (int) $this->payment_method_id,
            'payment_method_name' => (string) ($method?->name ?? ''),
            'payment_method_logo' => ! empty($method?->logo) ? asset('storage/'.ltrim((string) $method->logo, '/')) : null,
            'account_label'       => (string) ($this->effective_label ?? ''),
            'display_name'        => (string) ($this->display_name ?? $this->account_name ?? ''),
            'display_value'       => (string) ($this->display_value ?? $this->account_number ?? ''),
            'account_name'        => (string) ($values['account_name'] ?? $this->account_name ?? ''),
            'account_number'      => (string) ($values['account_number'] ?? $this->account_number ?? ''),
            'instructions'        => (string) ($values['instructions'] ?? $this->instructions ?? ''),
            'details'             => $details,
            'field_values'        => $values,
            'method_instructions' => (string) ($method?->instructions ?? ''),
        ];
    }
}
