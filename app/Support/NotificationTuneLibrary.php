<?php

namespace App\Support;

class NotificationTuneLibrary
{
    public const CUSTOM_KEY = 'custom';

    public const DEFAULT_KEY = 'pulse';

    public const PUBLIC_DIRECTORY = 'general/tune';

    /**
     * @return array<string, array{label: string, frequency: float}>
     */
    public static function noteOptions(): array
    {
        return [
            'c5'  => ['label' => 'C5', 'frequency' => 523.25],
            'd5'  => ['label' => 'D5', 'frequency' => 587.33],
            'e5'  => ['label' => 'E5', 'frequency' => 659.25],
            'f5'  => ['label' => 'F5', 'frequency' => 698.46],
            'g5'  => ['label' => 'G5', 'frequency' => 783.99],
            'a5'  => ['label' => 'A5', 'frequency' => 880.00],
            'b5'  => ['label' => 'B5', 'frequency' => 987.77],
            'c6'  => ['label' => 'C6', 'frequency' => 1046.50],
            'd6'  => ['label' => 'D6', 'frequency' => 1174.66],
            'e6'  => ['label' => 'E6', 'frequency' => 1318.51],
            'g6'  => ['label' => 'G6', 'frequency' => 1567.98],
            'sil' => ['label' => 'Rest', 'frequency' => 0.0],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function tunes(): array
    {
        return self::withPublicFiles([
            'pulse' => [
                'key'         => 'pulse',
                'label'       => 'Pulse',
                'description' => 'Short two-note alert.',
                'steps'       => [
                    self::step(880, 110),
                    self::step(1175, 130, 'sine', 0.18, 35),
                ],
            ],
            'chime' => [
                'key'         => 'chime',
                'label'       => 'Chime',
                'description' => 'Soft rising notification bell.',
                'steps'       => [
                    self::step(659, 95, 'sine', 0.16, 20),
                    self::step(880, 105, 'sine', 0.17, 20),
                    self::step(1319, 145, 'sine', 0.14, 45),
                ],
            ],
            'coin' => [
                'key'         => 'coin',
                'label'       => 'Coin',
                'description' => 'Bright wallet credit sound.',
                'steps'       => [
                    self::step(1047, 80, 'triangle', 0.16, 20),
                    self::step(1568, 95, 'triangle', 0.16, 35),
                    self::step(2093, 110, 'triangle', 0.12, 40),
                ],
            ],
            'ripple' => [
                'key'         => 'ripple',
                'label'       => 'Ripple',
                'description' => 'Gentle four-step signal.',
                'steps'       => [
                    self::step(587, 85, 'sine', 0.13, 25),
                    self::step(740, 85, 'sine', 0.14, 25),
                    self::step(932, 95, 'sine', 0.15, 25),
                    self::step(1175, 120, 'sine', 0.13, 35),
                ],
            ],
            'prism' => [
                'key'         => 'prism',
                'label'       => 'Prism',
                'description' => 'Clean layered status tone.',
                'steps'       => [
                    self::step(784, 100, 'triangle', 0.15, 20),
                    self::step(988, 100, 'triangle', 0.15, 20),
                    self::step(1319, 140, 'triangle', 0.13, 45),
                ],
            ],
            'arcade' => [
                'key'         => 'arcade',
                'label'       => 'Arcade',
                'description' => 'Playful high-energy beep.',
                'steps'       => [
                    self::step(988, 70, 'square', 0.11, 20),
                    self::step(1319, 70, 'square', 0.10, 20),
                    self::step(1760, 90, 'square', 0.09, 45),
                ],
            ],
            'soft-bell' => [
                'key'         => 'soft-bell',
                'label'       => 'Soft Bell',
                'description' => 'Calm low-volume bell.',
                'steps'       => [
                    self::step(523, 130, 'sine', 0.12, 35),
                    self::step(784, 170, 'sine', 0.11, 55),
                ],
            ],
            'quick-pop' => [
                'key'         => 'quick-pop',
                'label'       => 'Quick Pop',
                'description' => 'Very short attention cue.',
                'steps'       => [
                    self::step(1047, 65, 'triangle', 0.16, 18),
                    self::step(784, 75, 'triangle', 0.13, 30),
                ],
            ],
            'mellow' => [
                'key'         => 'mellow',
                'label'       => 'Mellow',
                'description' => 'Warm rounded alert.',
                'steps'       => [
                    self::step(440, 120, 'sine', 0.14, 35),
                    self::step(554, 120, 'sine', 0.14, 35),
                    self::step(659, 150, 'sine', 0.12, 45),
                ],
            ],
            'signal' => [
                'key'         => 'signal',
                'label'       => 'Signal',
                'description' => 'Clear operational ping.',
                'steps'       => [
                    self::step(740, 90, 'sine', 0.15, 25),
                    self::step(740, 90, 'sine', 0.15, 25),
                    self::step(988, 110, 'sine', 0.14, 40),
                ],
            ],
            'success' => [
                'key'         => 'success',
                'label'       => 'Success',
                'description' => 'Positive completion tone.',
                'steps'       => [
                    self::step(659, 80, 'triangle', 0.14, 20),
                    self::step(880, 90, 'triangle', 0.15, 20),
                    self::step(1175, 130, 'triangle', 0.13, 35),
                ],
            ],
            'alert' => [
                'key'         => 'alert',
                'label'       => 'Alert',
                'description' => 'Sharper warning-style tone.',
                'steps'       => [
                    self::step(880, 95, 'square', 0.10, 25),
                    self::step(660, 95, 'square', 0.10, 25),
                    self::step(880, 115, 'square', 0.10, 45),
                ],
            ],
            'glass' => [
                'key'         => 'glass',
                'label'       => 'Glass',
                'description' => 'Light crystal tap.',
                'steps'       => [
                    self::step(1175, 75, 'sine', 0.12, 18),
                    self::step(1760, 95, 'sine', 0.11, 30),
                    self::step(2349, 115, 'sine', 0.09, 45),
                ],
            ],
            'glow' => [
                'key'         => 'glow',
                'label'       => 'Glow',
                'description' => 'Soft warm confirmation cue.',
                'steps'       => [
                    self::step(554, 110, 'sine', 0.13, 30),
                    self::step(740, 140, 'sine', 0.12, 45),
                ],
            ],
            'orbit' => [
                'key'         => 'orbit',
                'label'       => 'Orbit',
                'description' => 'Rounded three-step motion tone.',
                'steps'       => [
                    self::step(660, 80, 'triangle', 0.13, 22),
                    self::step(880, 90, 'triangle', 0.13, 22),
                    self::step(660, 115, 'triangle', 0.12, 42),
                ],
            ],
            'sparkle' => [
                'key'         => 'sparkle',
                'label'       => 'Sparkle',
                'description' => 'Bright high-note sparkle.',
                'steps'       => [
                    self::step(1319, 65, 'sine', 0.11, 18),
                    self::step(1976, 85, 'sine', 0.10, 28),
                    self::step(1568, 100, 'sine', 0.10, 38),
                ],
            ],
            'sonar' => [
                'key'         => 'sonar',
                'label'       => 'Sonar',
                'description' => 'Measured double ping.',
                'steps'       => [
                    self::step(784, 120, 'sine', 0.14, 85),
                    self::step(784, 140, 'sine', 0.12, 45),
                ],
            ],
            'cascade' => [
                'key'         => 'cascade',
                'label'       => 'Cascade',
                'description' => 'Descending notification cascade.',
                'steps'       => [
                    self::step(1568, 75, 'triangle', 0.12, 20),
                    self::step(1175, 85, 'triangle', 0.12, 20),
                    self::step(880, 105, 'triangle', 0.12, 35),
                    self::step(659, 125, 'triangle', 0.11, 45),
                ],
            ],
            'comet' => [
                'key'         => 'comet',
                'label'       => 'Comet',
                'description' => 'Fast rising action tone.',
                'steps'       => [
                    self::step(740, 65, 'square', 0.09, 18),
                    self::step(988, 65, 'square', 0.09, 18),
                    self::step(1319, 95, 'square', 0.08, 38),
                ],
            ],
            'zen' => [
                'key'         => 'zen',
                'label'       => 'Zen',
                'description' => 'Quiet low-distraction sound.',
                'steps'       => [
                    self::step(392, 150, 'sine', 0.10, 45),
                    self::step(523, 180, 'sine', 0.09, 55),
                ],
            ],
            'vault' => [
                'key'         => 'vault',
                'label'       => 'Vault',
                'description' => 'Solid secure wallet tone.',
                'steps'       => [
                    self::step(494, 100, 'triangle', 0.14, 25),
                    self::step(659, 120, 'triangle', 0.13, 25),
                    self::step(988, 145, 'triangle', 0.11, 45),
                ],
            ],
            'breeze' => [
                'key'         => 'breeze',
                'label'       => 'Breeze',
                'description' => 'Light airy notification cue.',
                'steps'       => [
                    self::step(698, 95, 'sine', 0.11, 30),
                    self::step(1047, 135, 'sine', 0.10, 55),
                ],
            ],
            'notice' => [
                'key'         => 'notice',
                'label'       => 'Notice',
                'description' => 'Simple neutral notice sound.',
                'steps'       => [
                    self::step(784, 105, 'sine', 0.13, 30),
                    self::step(988, 120, 'sine', 0.12, 40),
                ],
            ],
            'matrix' => [
                'key'         => 'matrix',
                'label'       => 'Matrix',
                'description' => 'Crisp digital sequence.',
                'steps'       => [
                    self::step(988, 60, 'square', 0.08, 18),
                    self::step(784, 60, 'square', 0.08, 18),
                    self::step(1175, 70, 'square', 0.08, 18),
                    self::step(1568, 90, 'square', 0.07, 40),
                ],
            ],
            'tempo' => [
                'key'         => 'tempo',
                'label'       => 'Tempo',
                'description' => 'Rhythmic operational alert.',
                'steps'       => [
                    self::step(659, 70, 'triangle', 0.12, 28),
                    self::step(659, 70, 'triangle', 0.12, 28),
                    self::step(880, 120, 'triangle', 0.12, 42),
                ],
            ],
            'twinkle' => [
                'key'         => 'twinkle',
                'label'       => 'Twinkle',
                'description' => 'Friendly high chime.',
                'steps'       => [
                    self::step(1047, 70, 'sine', 0.11, 18),
                    self::step(1319, 80, 'sine', 0.10, 18),
                    self::step(2093, 105, 'sine', 0.08, 38),
                ],
            ],
            'ascend' => [
                'key'         => 'ascend',
                'label'       => 'Ascend',
                'description' => 'Confident upward tone.',
                'steps'       => [
                    self::step(523, 80, 'triangle', 0.13, 20),
                    self::step(659, 90, 'triangle', 0.13, 20),
                    self::step(784, 100, 'triangle', 0.13, 20),
                    self::step(1047, 125, 'triangle', 0.12, 45),
                ],
            ],
            'calm-drop' => [
                'key'         => 'calm-drop',
                'label'       => 'Calm Drop',
                'description' => 'Soft descending status cue.',
                'steps'       => [
                    self::step(880, 110, 'sine', 0.11, 35),
                    self::step(659, 150, 'sine', 0.10, 50),
                ],
            ],
            'echo' => [
                'key'         => 'echo',
                'label'       => 'Echo',
                'description' => 'Subtle repeated ping.',
                'steps'       => [
                    self::step(988, 80, 'sine', 0.13, 55),
                    self::step(988, 90, 'sine', 0.10, 55),
                    self::step(988, 105, 'sine', 0.08, 45),
                ],
            ],
            'priority' => [
                'key'         => 'priority',
                'label'       => 'Priority',
                'description' => 'Strong attention sound.',
                'steps'       => [
                    self::step(988, 90, 'square', 0.09, 24),
                    self::step(1175, 90, 'square', 0.09, 24),
                    self::step(988, 130, 'square', 0.09, 45),
                ],
            ],
        ]);
    }

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_keys(self::tunes());
    }

    public static function defaultKey(): string
    {
        return self::normalizeKey(setting('notification_tune_default', self::DEFAULT_KEY));
    }

    public static function normalizeKey(?string $key): string
    {
        if ($key === self::CUSTOM_KEY) {
            return self::CUSTOM_KEY;
        }

        return array_key_exists((string) $key, self::tunes()) ? (string) $key : self::DEFAULT_KEY;
    }

    /**
     * @return array<string, mixed>
     */
    public static function resolve(?string $key = null, ?array $customTune = null): array
    {
        if ($key === self::CUSTOM_KEY && is_array($customTune) && self::isValidCustomTune($customTune)) {
            return array_merge([
                'key'         => self::CUSTOM_KEY,
                'label'       => 'Custom Tune',
                'description' => 'Personal tune.',
                'custom'      => true,
            ], $customTune);
        }

        $normalizedKey = self::normalizeKey($key ?: self::defaultKey());

        if ($normalizedKey === self::CUSTOM_KEY) {
            $normalizedKey = self::defaultKey();
        }

        return self::tunes()[$normalizedKey] ?? self::tunes()[self::DEFAULT_KEY];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultCustomTune(): array
    {
        return self::customTuneFromInput([
            'label'  => 'My Tune',
            'note_1' => 'c5',
            'note_2' => 'e5',
            'note_3' => 'g5',
            'note_4' => 'c6',
            'speed'  => 150,
        ]);
    }

    /**
     * @param  array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function customTuneFromInput(array $input): array
    {
        $notes = self::noteOptions();
        $speed = max(80, min(360, (int) ($input['speed'] ?? 150)));
        $label = trim((string) ($input['label'] ?? 'Custom Tune')) ?: 'Custom Tune';

        $steps = collect(['note_1', 'note_2', 'note_3', 'note_4'])
            ->map(fn (string $key): string => (string) ($input[$key] ?? 'sil'))
            ->filter(fn (string $note): bool => isset($notes[$note]))
            ->map(function (string $note) use ($notes, $speed): array {
                return self::step($notes[$note]['frequency'], $speed, 'sine', 0.16, 35);
            })
            ->values()
            ->all();

        return [
            'label'       => $label,
            'description' => 'Personal tune.',
            'steps'       => $steps ?: self::defaultCustomTune()['steps'],
            'notes'       => [
                'note_1' => (string) ($input['note_1'] ?? 'c5'),
                'note_2' => (string) ($input['note_2'] ?? 'e5'),
                'note_3' => (string) ($input['note_3'] ?? 'g5'),
                'note_4' => (string) ($input['note_4'] ?? 'c6'),
                'speed'  => $speed,
            ],
        ];
    }

    public static function publicFile(string $key): string
    {
        return self::PUBLIC_DIRECTORY.'/'.self::normalizeKey($key).'.wav';
    }

    /**
     * @param  array<string, array<string, mixed>> $tunes
     * @return array<string, array<string, mixed>>
     */
    private static function withPublicFiles(array $tunes): array
    {
        return collect($tunes)
            ->map(function (array $tune, string $key): array {
                return array_merge($tune, [
                    'file' => self::PUBLIC_DIRECTORY.'/'.$key.'.wav',
                ]);
            })
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private static function step(float $frequency, int $milliseconds, string $type = 'sine', float $volume = 0.15, int $gap = 30): array
    {
        return [
            'frequency' => $frequency,
            'duration'  => round($milliseconds / 1000, 3),
            'type'      => $type,
            'volume'    => $volume,
            'gap'       => round($gap / 1000, 3),
        ];
    }

    /**
     * @param array<string, mixed> $customTune
     */
    private static function isValidCustomTune(array $customTune): bool
    {
        return ! empty($customTune['steps']) && is_array($customTune['steps']);
    }
}
