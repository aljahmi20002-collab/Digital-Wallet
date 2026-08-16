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
    ],

    // Plan cards themselves come from the WalletEarnPlan model, not from
    // repeated content. Schema intentionally limited to header copy.
];
