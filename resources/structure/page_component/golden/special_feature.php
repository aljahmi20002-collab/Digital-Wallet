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

        'phone_greeting' => [
            'translatable' => true,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'nullable|string|max:60',
        ],
        'phone_name' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'nullable|string|max:60',
        ],
        'phone_balance' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-12',
            'validation'   => 'nullable|string|max:60',
        ],
    ],

    'repeated_content' => [
        'spfeat_icon_class' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-12',
            'validation'   => 'required|string|max:80',
        ],
        'spfeat_title' => [
            'translatable' => true,
            'type'         => 'text',
            'class'        => 'col-md-12',
            'validation'   => 'required|string|max:120',
        ],
        'spfeat_text' => [
            'translatable' => true,
            'type'         => 'textarea',
            'class'        => 'col-md-12',
            'validation'   => 'required|string|max:500',
        ],
    ],

    'repeated_content_limit' => 6,
];
