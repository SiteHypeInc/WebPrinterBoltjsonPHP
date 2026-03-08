<?php

function inject_services_data($template_json, $data) {
    $required_fields = ['name', 'trade', 'tagline', 'services', 'service_areas', 'steps'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    if (!is_array($data['services']) || count($data['services']) === 0) {
        throw new Exception("Services must be a non-empty array");
    }

    if (!is_array($data['service_areas']) || count($data['service_areas']) === 0) {
        throw new Exception("Service areas must be a non-empty array");
    }

    if (!is_array($data['steps']) || count($data['steps']) !== 3) {
        throw new Exception("Steps must be an array with exactly 3 items");
    }

    $replacements = [
        '{{name}}' => $data['name'],
        '{{trade}}' => $data['trade'],
        '{{tagline}}' => $data['tagline'],
        '{{step_1}}' => $data['steps'][0],
        '{{step_2}}' => $data['steps'][1],
        '{{step_3}}' => $data['steps'][2],
        '{{service_areas}}' => implode(' • ', $data['service_areas'])
    ];

    for ($i = 0; $i < 6; $i++) {
        $index = $i + 1;
        if (isset($data['services'][$i])) {
            $service = $data['services'][$i];
            $replacements["{{service_{$index}_name}}"] = $service['name'] ?? "Service $index";
            $replacements["{{service_{$index}_desc}}"] = $service['desc'] ?? '';
            $replacements["{{service_{$index}_price}}"] = $service['price'] ?? 'Contact for Pricing';
        } else {
            $replacements["{{service_{$index}_name}}"] = "Additional Service";
            $replacements["{{service_{$index}_desc}}"] = "Contact us to learn more about our services.";
            $replacements["{{service_{$index}_price}}"] = "Contact for Pricing";
        }
    }

    $populated = str_replace(
        array_keys($replacements),
        array_values($replacements),
        $template_json
    );

    return $populated;
}

function save_services_page($populated_json, $page_title = 'Our Services') {
    if (!function_exists('wp_insert_post')) {
        throw new Exception('WordPress functions not available');
    }

    $post_data = [
        'post_title'   => $page_title,
        'post_content' => '',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'meta_input'   => [
            '_elementor_data' => $populated_json,
            '_elementor_edit_mode' => 'builder',
            '_elementor_template_type' => 'wp-page',
            '_wp_page_template' => 'elementor_canvas'
        ]
    ];

    $page_id = wp_insert_post($post_data);

    if (is_wp_error($page_id)) {
        throw new Exception('Failed to create page: ' . $page_id->get_error_message());
    }

    return $page_id;
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    echo "=== ELEMENTOR SERVICES PAGE INJECTION DEMO ===\n\n";

    $contractor_data = [
        'name' => 'Pyramid Heating & Cooling',
        'trade' => 'HVAC',
        'tagline' => "Portland's Heating & Cooling Experts",
        'services' => [
            [
                'name' => 'Furnace Repair',
                'desc' => 'Fast, reliable furnace repair to keep your home warm all winter long.',
                'price' => 'From $89'
            ],
            [
                'name' => 'AC Installation',
                'desc' => 'Professional air conditioning installation with energy-efficient systems.',
                'price' => 'Free Estimate'
            ],
            [
                'name' => 'Heat Pump Service',
                'desc' => 'Complete heat pump maintenance, repair, and replacement services.',
                'price' => 'From $129'
            ],
            [
                'name' => 'Duct Cleaning',
                'desc' => 'Improve indoor air quality with professional duct cleaning services.',
                'price' => 'From $299'
            ],
            [
                'name' => 'Emergency Service',
                'desc' => '24/7 emergency HVAC repair when you need it most.',
                'price' => 'Same Day Service'
            ],
            [
                'name' => 'Maintenance Plans',
                'desc' => 'Year-round protection with our comprehensive maintenance plans.',
                'price' => 'From $199/year'
            ]
        ],
        'service_areas' => ['Portland', 'Beaverton', 'Gresham', 'Lake Oswego', 'Tigard', 'Hillsboro'],
        'steps' => ['Call Us', 'We Schedule', 'Job Done']
    ];

    echo "Loading template...\n";
    $template = file_get_contents(__DIR__ . '/elementor-services-template.json');

    if ($template === false) {
        die("ERROR: Could not load template file\n");
    }

    echo "Template loaded: " . strlen($template) . " bytes\n\n";

    echo "Injecting data...\n";
    try {
        $populated = inject_services_data($template, $contractor_data);
        echo "Injection successful!\n\n";

        echo "=== RUNNING TESTS ===\n\n";

        $tests_passed = 0;
        $tests_failed = 0;

        $tests = [
            ['name' => 'Hero heading contains trade', 'check' => 'Our HVAC Services'],
            ['name' => 'Hero tagline present', 'check' => "Portland's Heating & Cooling Experts"],
            ['name' => 'Service 1 name injected', 'check' => 'Furnace Repair'],
            ['name' => 'Service 1 description injected', 'check' => 'Fast, reliable furnace repair'],
            ['name' => 'Service 1 price injected', 'check' => 'From $89'],
            ['name' => 'Service 2 name injected', 'check' => 'AC Installation'],
            ['name' => 'Service 2 price injected', 'check' => 'Free Estimate'],
            ['name' => 'Service 3 name injected', 'check' => 'Heat Pump Service'],
            ['name' => 'Service 4 name injected', 'check' => 'Duct Cleaning'],
            ['name' => 'Service 5 name injected', 'check' => 'Emergency Service'],
            ['name' => 'Service 6 name injected', 'check' => 'Maintenance Plans'],
            ['name' => 'Step 1 injected', 'check' => '1. Call Us'],
            ['name' => 'Step 2 injected', 'check' => '2. We Schedule'],
            ['name' => 'Step 3 injected', 'check' => '3. Job Done'],
            ['name' => 'Service areas formatted correctly', 'check' => 'Portland • Beaverton • Gresham'],
            ['name' => 'CTA contains company name', 'check' => 'Contact Pyramid Heating & Cooling'],
            ['name' => 'CTA contains trade', 'check' => 'professional HVAC services'],
            ['name' => 'No remaining placeholders', 'check' => '{{', 'inverse' => true]
        ];

        foreach ($tests as $test) {
            $found = strpos($populated, $test['check']) !== false;
            $inverse = isset($test['inverse']) && $test['inverse'];

            if (($found && !$inverse) || (!$found && $inverse)) {
                echo "✓ {$test['name']}\n";
                $tests_passed++;
            } else {
                echo "✗ {$test['name']}\n";
                $tests_failed++;
            }
        }

        echo "\n";
        echo "TEST SUMMARY: $tests_passed / " . count($tests) . " tests passed\n";

        if ($tests_failed === 0) {
            echo "✓ ALL TESTS PASSED - Services page injection working perfectly!\n";
        } else {
            echo "✗ $tests_failed test(s) failed\n";
        }

        echo "\n=== SAMPLE OUTPUT (first 500 chars) ===\n";
        echo substr($populated, 0, 500) . "...\n\n";

        echo "Ready for WordPress:\n";
        echo "- Use save_services_page(\$populated) to create the page\n";
        echo "- Or save to file: file_put_contents('output.json', \$populated)\n";

    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        exit(1);
    }
}
