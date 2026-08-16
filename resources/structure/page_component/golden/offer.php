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
        'description' => [
            'translatable' => true,
            'type'         => 'textarea',
            'class'        => 'col-md-12',
            'validation'   => 'nullable|string|max:1000',
        ],
        'button_text' => [
            'translatable' => true,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'nullable|string|max:100',
        ],
        'button_url' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'nullable|string|max:255',
        ],
    ],

    'repeated_content' => [
        'counter_prefix' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-3',
            'validation'   => 'nullable|string|max:6',
        ],
        'counter_number' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-3',
            'validation'   => 'required|string|max:20',
        ],
        'counter_suffix' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-3',
            'validation'   => 'nullable|string|max:6',
        ],
        'counter_decimals' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-3',
            'validation'   => 'nullable|integer|min:0|max:3',
        ],
        'counter_title' => [
            'translatable' => true,
            'type'         => 'text',
            'class'        => 'col-md-12',
            'validation'   => 'required|string|max:120',
        ],
    ],

    'repeated_content_limit' => 4,
];
