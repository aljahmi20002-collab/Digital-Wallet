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
        'team_image' => [
            'translatable'     => false,
            'type'             => 'img',
            'class'            => 'col-md-12',
            'validation'       => 'required|image|mimes:jpg,jpeg,png,webp',
            'recommended_size' => '600x750',
        ],
        'name' => [
            'translatable' => true,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'required|string|max:120',
        ],
        'designation' => [
            'translatable' => true,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'required|string|max:120',
        ],
        'linkedin_url' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'nullable|string|max:255',
        ],
        'twitter_url' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'nullable|string|max:255',
        ],
        'facebook_url' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'nullable|string|max:255',
        ],
        'email' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'nullable|string|max:255',
        ],
    ],

    'repeated_content_limit' => 12,
];
