<?php

/**
 * DISI-Solves issue taxonomy: category => label + subcategories (slug => label).
 * Single source of truth for validation, filters and the forms (shared to the frontend by Inertia).
 */
return [
    'categories' => [
        'hardware' => [
            'label' => 'Hardware',
            'subcategories' => [
                'ibml' => 'IBML Scanners',
                'other-scanners' => 'Other Scanners',
                'printers' => 'Printers',
                'computers' => 'Computers & Laptops',
                'network' => 'Network & Peripherals',
                'other-hardware' => 'Other Hardware',
            ],
        ],
        'software' => [
            'label' => 'Software',
            'subcategories' => [
                'softtrac' => 'SoftTrac',
                'omniscan' => 'OmniScan',
                'operating-system' => 'Operating System',
                'accounts-access' => 'Accounts & Access',
                'other-software' => 'Other Software',
            ],
        ],
    ],
];
