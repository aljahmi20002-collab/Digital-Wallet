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
        'service_icon_class' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-12',
            'validation'   => 'required|string|max:80',
        ],
        'service_title' => [
            'translatable' => true,
            'type'         => 'text',
            'class'        => 'col-md-12',
            'validation'   => 'required|string|max:120',
        ],
        'service_text' => [
            'translatable' => true,
            'type'         => 'textarea',
            'class'        => 'col-md-12',
            'validation'   => 'required|string|max:600',
        ],
        'service_link' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-12',
            'validation'   => 'nullable|string|max:255',
        ],
    ],

    'repeated_content_limit' => 12,
];
