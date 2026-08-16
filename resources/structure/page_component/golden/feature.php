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
        'feature_number' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-4',
            'validation'   => 'nullable|string|max:6',
        ],
        'feature_icon_class' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-8',
            'validation'   => 'required|string|max:80',
        ],
        'feature_title' => [
            'translatable' => true,
            'type'         => 'text',
            'class'        => 'col-md-12',
            'validation'   => 'required|string|max:120',
        ],
        'feature_text' => [
            'translatable' => true,
            'type'         => 'textarea',
            'class'        => 'col-md-12',
            'validation'   => 'required|string|max:600',
        ],
    ],

    'repeated_content_limit' => 9,
];
