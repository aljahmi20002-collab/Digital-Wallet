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
        'client_image' => [
            'translatable'     => false,
            'type'             => 'img',
            'class'            => 'col-md-12',
            'validation'       => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'recommended_size' => '200x200',
        ],
        'client_name' => [
            'translatable' => true,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'required|string|max:120',
        ],
        'client_position' => [
            'translatable' => true,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'required|string|max:120',
        ],
        'rating' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-12',
            'validation'   => 'nullable|integer|min:1|max:5',
        ],
        'comment_text' => [
            'translatable' => true,
            'type'         => 'textarea',
            'class'        => 'col-md-12',
            'validation'   => 'required|string|max:800',
        ],
    ],
];
