<?php

declare(strict_types=1);

namespace App\Models\P2P;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'p2p_payment_methods';

    protected $fillable = [
        'logo',
        'name',
        'country',
        'instructions',
        'fields',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'fields'     => 'array',
        'sort_order' => 'integer',
        'status'     => 'boolean',
    ];

    public function paymentAccounts(): HasMany
    {
        return $this->hasMany(PaymentAccount::class, 'payment_method_id');
    }

    public function normalizedFields(): array
    {
        $fields = self::normalizeFieldDefinitions((array) ($this->fields ?? []));

        return $fields !== [] ? $fields : self::defaultFieldDefinitions();
    }

    public static function normalizeFieldDefinitions(array $fields): array
    {
        $normalized = [];
        $usedKeys   = [];

        foreach (array_values($fields) as $index => $field) {
            if (! is_array($field)) {
                continue;
            }

            $label = trim((string) ($field['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $type = strtolower(trim((string) ($field['type'] ?? 'text')));
            if (! in_array($type, ['text', 'number', 'textarea', 'select', 'file'], true)) {
                $type = 'text';
            }

            $rawKey = trim((string) ($field['key'] ?? ''));
            $key    = Str::snake($rawKey !== '' ? $rawKey : $label);
            $key    = preg_replace('/[^a-z0-9_]/', '', $key ?? '') ?: 'field_'.($index + 1);

            if (in_array($key, $usedKeys, true)) {
                $key .= '_'.($index + 1);
            }

            $usedKeys[] = $key;

            $rawOptions = $field['options'] ?? [];
            if (is_string($rawOptions)) {
                $rawOptions = preg_split('/[\r\n,]+/', $rawOptions) ?: [];
            }

            $options = collect((array) $rawOptions)
                ->map(fn ($option) => trim((string) $option))
                ->filter(fn (string $option) => $option !== '')
                ->values()
                ->all();

            $normalized[] = [
                'key'        => $key,
                'label'      => $label,
                'type'       => $type,
                'required'   => (bool) ($field['required'] ?? false),
                'options'    => $type === 'select' ? $options : [],
                'sort_order' => isset($field['sort_order']) ? (int) $field['sort_order'] : ($index + 1),
            ];
        }

        usort($normalized, fn (array $a, array $b) => ((int) $a['sort_order']) <=> ((int) $b['sort_order']));

        return array_values($normalized);
    }

    public static function defaultFieldDefinitions(): array
    {
        return [
            [
                'key'        => 'account_name',
                'label'      => 'Account Name',
                'type'       => 'text',
                'required'   => true,
                'options'    => [],
                'sort_order' => 1,
            ],
            [
                'key'        => 'account_number',
                'label'      => 'Account Number',
                'type'       => 'text',
                'required'   => true,
                'options'    => [],
                'sort_order' => 2,
            ],
            [
                'key'        => 'instructions',
                'label'      => 'Instructions',
                'type'       => 'text',
                'required'   => false,
                'options'    => [],
                'sort_order' => 3,
            ],
        ];
    }

    public function fieldByKey(string $key): ?array
    {
        foreach ($this->normalizedFields() as $field) {
            if ((string) ($field['key'] ?? '') === $key) {
                return $field;
            }
        }

        return null;
    }

    public function getLogoAltAttribute(): string
    {
        return $this->logo ?? '';
    }
}
