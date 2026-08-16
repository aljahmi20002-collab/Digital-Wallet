<?php

namespace App\Http\Requests\Backend;

use App\Services\BackgroundTaskRegistry;
use Illuminate\Foundation\Http\FormRequest;

class RunBackgroundTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $taskKey  = $this->input('task_key');
        $registry = app(BackgroundTaskRegistry::class);
        $task     = $registry->get($taskKey ?? '');

        $rules = [
            'task_key' => ['required', 'string', function ($attribute, $value, $fail) use ($registry) {
                if (! $registry->exists($value)) {
                    $fail(__('Invalid task key.'));
                }
            }],
        ];

        if ($task && isset($task['options'])) {
            foreach ($task['options'] as $name => $option) {
                $rules[$name] = match ($option['type'] ?? null) {
                    'integer' => [
                        'sometimes',
                        'integer',
                        "min:{$option['min']}",
                        "max:{$option['max']}",
                    ],
                    'boolean' => ['sometimes', 'boolean'],
                    default   => ['sometimes'],
                };
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'task_key.required' => __('A task must be selected.'),
            'limit.integer'     => __('Limit must be a whole number.'),
            'limit.min'         => __('Limit must be at least :min.'),
            'limit.max'         => __('Limit must not exceed :max.'),
            'renewals.boolean'  => __('Auto-renewals must be enabled or disabled.'),
        ];
    }
}
