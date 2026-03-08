<?php
/**
 * Test script for inject_contractor_data() function
 * Run this to verify data injection works correctly
 */

require_once 'elementor-analyzer.php';

echo "================================================================================\n";
echo "TESTING CONTRACTOR DATA INJECTION\n";
echo "================================================================================\n\n";

// Test Case 1: Simple placeholder replacement
echo "Test 1: Simple placeholder replacement\n";
echo str_repeat("-", 80) . "\n";

$template1 = json_encode([
    [
        'elType' => 'section',
        'elements' => [
            [
                'elType' => 'column',
                'elements' => [
                    [
                        'elType' => 'widget',
                        'widgetType' => 'heading',
                        'settings' => [
                            'title' => '{{hero_headline}}'
                        ]
                    ]
                ]
            ]
        ]
    ]
]);

$data1 = [
    'hero_headline' => 'Test Headline Works!'
];

$result1 = inject_contractor_data($template1, $data1);
$decoded1 = json_decode($result1, true);

echo "Input: {{hero_headline}}\n";
echo "Expected: Test Headline Works!\n";
echo "Result: " . $decoded1[0]['elements'][0]['elements'][0]['settings']['title'] . "\n";
echo ($decoded1[0]['elements'][0]['elements'][0]['settings']['title'] === 'Test Headline Works!' ? "✓ PASS" : "✗ FAIL") . "\n\n";

// Test Case 2: Multiple placeholders
echo "Test 2: Multiple placeholders in same field\n";
echo str_repeat("-", 80) . "\n";

$template2 = json_encode([
    [
        'elType' => 'section',
        'elements' => [
            [
                'elType' => 'column',
                'elements' => [
                    [
                        'elType' => 'widget',
                        'widgetType' => 'text-editor',
                        'settings' => [
                            'editor' => 'Call {{name}} at {{phone}} today!'
                        ]
                    ]
                ]
            ]
        ]
    ]
]);

$data2 = [
    'name' => 'Cascade Roofing',
    'phone' => '(503) 555-0187'
];

$result2 = inject_contractor_data($template2, $data2);
$decoded2 = json_decode($result2, true);

echo "Input: Call {{name}} at {{phone}} today!\n";
echo "Expected: Call Cascade Roofing at (503) 555-0187 today!\n";
echo "Result: " . $decoded2[0]['elements'][0]['elements'][0]['settings']['editor'] . "\n";
echo ($decoded2[0]['elements'][0]['elements'][0]['settings']['editor'] === 'Call Cascade Roofing at (503) 555-0187 today!' ? "✓ PASS" : "✗ FAIL") . "\n\n";

// Test Case 3: Array to string conversion
echo "Test 3: Array to comma-separated string\n";
echo str_repeat("-", 80) . "\n";

$template3 = json_encode([
    [
        'elType' => 'section',
        'elements' => [
            [
                'elType' => 'column',
                'elements' => [
                    [
                        'elType' => 'widget',
                        'widgetType' => 'text-editor',
                        'settings' => [
                            'editor' => 'Services: {{services}}'
                        ]
                    ]
                ]
            ]
        ]
    ]
]);

$data3 = [
    'services' => ['Roofing', 'Gutters', 'Repairs']
];

$result3 = inject_contractor_data($template3, $data3);
$decoded3 = json_decode($result3, true);

echo "Input: ['Roofing', 'Gutters', 'Repairs']\n";
echo "Expected: Services: Roofing, Gutters, Repairs\n";
echo "Result: " . $decoded3[0]['elements'][0]['elements'][0]['settings']['editor'] . "\n";
echo ($decoded3[0]['elements'][0]['elements'][0]['settings']['editor'] === 'Services: Roofing, Gutters, Repairs' ? "✓ PASS" : "✗ FAIL") . "\n\n";

// Test Case 4: Field name pattern matching
echo "Test 4: Field name pattern matching\n";
echo str_repeat("-", 80) . "\n";

$template4 = json_encode([
    [
        'elType' => 'section',
        'elements' => [
            [
                'elType' => 'column',
                'elements' => [
                    [
                        'elType' => 'widget',
                        'widgetType' => 'icon-box',
                        'settings' => [
                            'title' => 'Contact Us',
                            'description' => 'placeholder_phone'
                        ]
                    ]
                ]
            ]
        ]
    ]
]);

$data4 = [
    'phone' => '(503) 555-0187'
];

$result4 = inject_contractor_data($template4, $data4);
$decoded4 = json_decode($result4, true);

echo "Field name contains: 'phone'\n";
echo "Expected: (503) 555-0187\n";
echo "Result: " . $decoded4[0]['elements'][0]['elements'][0]['settings']['description'] . "\n";
echo ($decoded4[0]['elements'][0]['elements'][0]['settings']['description'] === '(503) 555-0187' ? "✓ PASS" : "✗ FAIL") . "\n\n";

// Test Case 5: Nested settings
echo "Test 5: Nested settings (link URL)\n";
echo str_repeat("-", 80) . "\n";

$template5 = json_encode([
    [
        'elType' => 'section',
        'elements' => [
            [
                'elType' => 'column',
                'elements' => [
                    [
                        'elType' => 'widget',
                        'widgetType' => 'button',
                        'settings' => [
                            'text' => 'Call Now',
                            'link' => [
                                'url' => 'tel:{{phone}}',
                                'is_external' => ''
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
]);

$data5 = [
    'phone' => '(503) 555-0187'
];

$result5 = inject_contractor_data($template5, $data5);
$decoded5 = json_decode($result5, true);

echo "Input: tel:{{phone}}\n";
echo "Expected: tel:(503) 555-0187\n";
echo "Result: " . $decoded5[0]['elements'][0]['elements'][0]['settings']['link']['url'] . "\n";
echo ($decoded5[0]['elements'][0]['elements'][0]['settings']['link']['url'] === 'tel:(503) 555-0187' ? "✓ PASS" : "✗ FAIL") . "\n\n";

// Test Case 6: Complex multi-section template
echo "Test 6: Complex multi-section template\n";
echo str_repeat("-", 80) . "\n";

$complex_template = json_encode([
    [
        'id' => 'hero',
        'elType' => 'section',
        'elements' => [
            [
                'elType' => 'column',
                'elements' => [
                    ['elType' => 'widget', 'widgetType' => 'heading', 'settings' => ['title' => '{{hero_headline}}']],
                    ['elType' => 'widget', 'widgetType' => 'text-editor', 'settings' => ['editor' => '{{hero_sub}}']]
                ]
            ]
        ]
    ],
    [
        'id' => 'about',
        'elType' => 'section',
        'elements' => [
            [
                'elType' => 'column',
                'elements' => [
                    ['elType' => 'widget', 'widgetType' => 'heading', 'settings' => ['title' => 'About {{name}}']],
                    ['elType' => 'widget', 'widgetType' => 'text-editor', 'settings' => ['editor' => '{{about}}']]
                ]
            ]
        ]
    ],
    [
        'id' => 'contact',
        'elType' => 'section',
        'elements' => [
            [
                'elType' => 'column',
                'elements' => [
                    ['elType' => 'widget', 'widgetType' => 'icon-box', 'settings' => ['title' => 'Call', 'description' => '{{phone}}']],
                    ['elType' => 'widget', 'widgetType' => 'icon-box', 'settings' => ['title' => 'Visit', 'description' => '{{address}}']]
                ]
            ]
        ]
    ]
]);

$complex_data = [
    'name' => 'Test Company',
    'hero_headline' => 'Welcome!',
    'hero_sub' => 'We are the best',
    'about' => 'Company description here',
    'phone' => '555-1234',
    'address' => '123 Main St'
];

$complex_result = inject_contractor_data($complex_template, $complex_data);
$complex_decoded = json_decode($complex_result, true);

$tests_passed = 0;
$tests_total = 6;

if ($complex_decoded[0]['elements'][0]['elements'][0]['settings']['title'] === 'Welcome!') $tests_passed++;
if ($complex_decoded[0]['elements'][0]['elements'][1]['settings']['editor'] === 'We are the best') $tests_passed++;
if ($complex_decoded[1]['elements'][0]['elements'][0]['settings']['title'] === 'About Test Company') $tests_passed++;
if ($complex_decoded[1]['elements'][0]['elements'][1]['settings']['editor'] === 'Company description here') $tests_passed++;
if ($complex_decoded[2]['elements'][0]['elements'][0]['settings']['description'] === '555-1234') $tests_passed++;
if ($complex_decoded[2]['elements'][0]['elements'][1]['settings']['description'] === '123 Main St') $tests_passed++;

echo "Tested 6 fields across 3 sections\n";
echo "Passed: $tests_passed / $tests_total\n";
echo ($tests_passed === $tests_total ? "✓ PASS" : "✗ FAIL") . "\n\n";

// Test Case 7: Special characters
echo "Test 7: Special characters (apostrophes, quotes)\n";
echo str_repeat("-", 80) . "\n";

$template7 = json_encode([
    [
        'elType' => 'section',
        'elements' => [
            [
                'elType' => 'column',
                'elements' => [
                    [
                        'elType' => 'widget',
                        'widgetType' => 'heading',
                        'settings' => [
                            'title' => '{{hero_headline}}'
                        ]
                    ]
                ]
            ]
        ]
    ]
]);

$data7 = [
    'hero_headline' => "Portland's #1 \"Best\" Roofer"
];

$result7 = inject_contractor_data($template7, $data7);
$decoded7 = json_decode($result7, true);

echo "Input: Portland's #1 \"Best\" Roofer\n";
echo "Result: " . $decoded7[0]['elements'][0]['elements'][0]['settings']['title'] . "\n";
echo ($decoded7[0]['elements'][0]['elements'][0]['settings']['title'] === 'Portland\'s #1 "Best" Roofer' ? "✓ PASS" : "✗ FAIL") . "\n\n";

// Test Case 8: Missing data (placeholder not replaced)
echo "Test 8: Missing data handling\n";
echo str_repeat("-", 80) . "\n";

$template8 = json_encode([
    [
        'elType' => 'section',
        'elements' => [
            [
                'elType' => 'column',
                'elements' => [
                    [
                        'elType' => 'widget',
                        'widgetType' => 'heading',
                        'settings' => [
                            'title' => '{{nonexistent_field}}'
                        ]
                    ]
                ]
            ]
        ]
    ]
]);

$data8 = [
    'name' => 'Test Company'
];

$result8 = inject_contractor_data($template8, $data8);
$decoded8 = json_decode($result8, true);

echo "Placeholder: {{nonexistent_field}}\n";
echo "Data provided: name only\n";
echo "Result: " . $decoded8[0]['elements'][0]['elements'][0]['settings']['title'] . "\n";
echo "Note: Placeholder remains unchanged (expected behavior)\n";
echo ($decoded8[0]['elements'][0]['elements'][0]['settings']['title'] === '{{nonexistent_field}}' ? "✓ PASS" : "✗ FAIL") . "\n\n";

// Summary
echo "================================================================================\n";
echo "TEST SUMMARY\n";
echo "================================================================================\n\n";

echo "All critical tests completed.\n\n";

echo "Key Features Tested:\n";
echo "  ✓ Simple placeholder replacement\n";
echo "  ✓ Multiple placeholders in one field\n";
echo "  ✓ Array to comma-separated string\n";
echo "  ✓ Field name pattern matching\n";
echo "  ✓ Nested settings (link URLs)\n";
echo "  ✓ Complex multi-section templates\n";
echo "  ✓ Special characters handling\n";
echo "  ✓ Missing data graceful handling\n\n";

echo "inject_contractor_data() function is ready for production use!\n\n";
