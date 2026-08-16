<?php

namespace App\Http\Requests\Frontend;

use App\Support\NotificationTuneLibrary;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationPreferenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $allowedTuneKeys = array_merge(['default'], NotificationTuneLibrary::keys(), [NotificationTuneLibrary::CUSTOM_KEY]);
        $allowedNotes    = array_keys(NotificationTuneLibrary::noteOptions());

        return [
            'notifications_enabled' => ['required', 'boolean'],
            'tune_enabled'          => ['required', 'boolean'],
            'tune_key'              => ['required', 'string', Rule::in($allowedTuneKeys)],
            'custom_tune'           => ['nullable', 'array'],
            'custom_tune.label'     => ['nullable', 'required_if:tune_key,custom', 'string', 'max:40'],
            'custom_tune.note_1'    => ['nullable', 'required_if:tune_key,custom', 'string', Rule::in($allowedNotes)],
            'custom_tune.note_2'    => ['nullable', 'required_if:tune_key,custom', 'string', Rule::in($allowedNotes)],
            'custom_tune.note_3'    => ['nullable', 'required_if:tune_key,custom', 'string', Rule::in($allowedNotes)],
            'custom_tune.note_4'    => ['nullable', 'required_if:tune_key,custom', 'string', Rule::in($allowedNotes)],
            'custom_tune.speed'     => ['nullable', 'required_if:tune_key,custom', 'integer', 'min:80', 'max:360'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'notifications_enabled.required' => __('Choose whether in-app notifications are enabled.'),
            'notifications_enabled.boolean'  => __('The in-app notification setting must be enabled or disabled.'),
            'tune_enabled.required'          => __('Choose whether notification tunes are enabled.'),
            'tune_enabled.boolean'           => __('The notification tune setting must be enabled or disabled.'),
            'tune_key.required'              => __('Select a notification tune.'),
            'tune_key.in'                    => __('Select a valid notification tune.'),
            'custom_tune.label.required_if'  => __('Enter a name for your custom tune.'),
            'custom_tune.label.max'          => __('The custom tune name may not be greater than :max characters.'),
            'custom_tune.note_1.required_if' => __('Select the first custom tune note.'),
            'custom_tune.note_1.in'          => __('Select a valid first custom tune note.'),
            'custom_tune.note_2.required_if' => __('Select the second custom tune note.'),
            'custom_tune.note_2.in'          => __('Select a valid second custom tune note.'),
            'custom_tune.note_3.required_if' => __('Select the third custom tune note.'),
            'custom_tune.note_3.in'          => __('Select a valid third custom tune note.'),
            'custom_tune.note_4.required_if' => __('Select the fourth custom tune note.'),
            'custom_tune.note_4.in'          => __('Select a valid fourth custom tune note.'),
            'custom_tune.speed.required_if'  => __('Enter a note length for your custom tune.'),
            'custom_tune.speed.integer'      => __('The custom tune note length must be a number.'),
            'custom_tune.speed.min'          => __('The custom tune note length must be at least :min ms.'),
            'custom_tune.speed.max'          => __('The custom tune note length may not be greater than :max ms.'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function preferenceData(): array
    {
        $validated = $this->validated();
        $tuneKey   = (string) $validated['tune_key'];

        return [
            'notifications_enabled' => (bool) $validated['notifications_enabled'],
            'tune_enabled'          => (bool) $validated['tune_enabled'],
            'tune_key'              => $tuneKey === 'default' ? null : $tuneKey,
            'custom_tune'           => $tuneKey === NotificationTuneLibrary::CUSTOM_KEY
                ? NotificationTuneLibrary::customTuneFromInput($validated['custom_tune'] ?? [])
                : null,
        ];
    }
}
