<?php

return [
    'component_fields' => [
        'eyebrow' => [
            'translatable' => true,
            'type'         => 'text',
            'class'        => 'col-md-12',
            'validation'   => 'nullable|string|max:120',
        ],
        'heading' => [
            'translatable' => true,
            'type'         => 'text',
            'class'        => 'col-md-12',
            'validation'   => 'required|string|max:255',
        ],
        'button_text' => [
            'translatable' => true,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'nullable|string|max:60',
        ],
        'button_url' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'nullable|string|max:255',
        ],
    ],
];
