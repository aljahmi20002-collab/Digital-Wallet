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
    ],

    'repeated_content' => [
        'step_number' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-3',
            'validation'   => 'nullable|string|max:6',
        ],
        'step_icon_class' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-9',
            'validation'   => 'required|string|max:80',
        ],
        'step_title' => [
            'translatable' => true,
            'type'         => 'text',
            'class'        => 'col-md-12',
            'validation'   => 'required|string|max:120',
        ],
        'step_description' => [
            'translatable' => true,
            'type'         => 'textarea',
            'class'        => 'col-md-12',
            'validation'   => 'required|string|max:500',
        ],
    ],

    'repeated_content_limit' => 6,
];
