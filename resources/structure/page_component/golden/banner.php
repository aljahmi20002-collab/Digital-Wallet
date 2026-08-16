<?php

/**
 * Golden Banner — hero with vault-card mockup.
 * Italic-gold accent words are wrapped in __word__ in the heading.
 */
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

        'primary_button_text' => [
            'translatable' => true,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'nullable|string|max:100',
        ],
        'primary_button_url' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'nullable|string|max:255',
        ],

        'secondary_button_text' => [
            'translatable' => true,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'nullable|string|max:100',
        ],
        'secondary_button_url' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'nullable|string|max:255',
        ],

        'vault_brand' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'nullable|string|max:60',
        ],
        'vault_tier' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-6',
            'validation'   => 'nullable|string|max:60',
        ],
        'vault_monogram' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-4',
            'validation'   => 'nullable|string|max:4',
        ],
        'vault_number' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-4',
            'validation'   => 'nullable|string|max:40',
        ],
        'vault_holder' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-4',
            'validation'   => 'nullable|string|max:60',
        ],
        'vault_expires' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-4',
            'validation'   => 'nullable|string|max:20',
        ],
        'vault_balance' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-4',
            'validation'   => 'nullable|string|max:40',
        ],
        'vault_yield' => [
            'translatable' => false,
            'type'         => 'text',
            'class'        => 'col-md-4',
            'validation'   => 'nullable|string|max:20',
        ],
    ],

];
