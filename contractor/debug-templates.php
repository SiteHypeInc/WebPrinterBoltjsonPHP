<?php
require_once 'webprinter-engine.php';

$data = [
    'company_name' => 'Pyramid Heating & Cooling',
    'trade' => 'hvac',
    'phone' => '(503) 555-0142',
    'email' => 'info@pyramidheating.com',
    'address' => '123 Industrial Pkwy',
    'city' => 'Portland',
    'state' => 'OR',
    'zip' => '97210',
    'services' => [
        ['name' => 'Furnace Repair']
    ],
    'social_media' => [
        'facebook' => 'https://facebook.com/pyramidheating',
        'twitter' => 'https://twitter.com/pyramidheating'
    ]
];

$results = generate_contractor_templates($data);

echo "=== FOOTER CONTENT CHECK ===\n";
echo "Footer contains 'Portland': " . (strpos($results['templates']['footer'], 'Portland') !== false ? 'YES' : 'NO') . "\n";
echo "Footer contains 'facebook.com/pyramidheating': " . (strpos($results['templates']['footer'], 'facebook.com/pyramidheating') !== false ? 'YES' : 'NO') . "\n";
echo "Footer contains 'twitter.com/pyramidheating': " . (strpos($results['templates']['footer'], 'twitter.com/pyramidheating') !== false ? 'YES' : 'NO') . "\n";

echo "\n=== CONTACT CONTENT CHECK ===\n";
echo "Contact contains 'info@pyramidheating.com': " . (strpos($results['templates']['contact'], 'info@pyramidheating.com') !== false ? 'YES' : 'NO') . "\n";
echo "Contact contains '97210': " . (strpos($results['templates']['contact'], '97210') !== false ? 'YES' : 'NO') . "\n";
echo "Contact contains '(503) 555-0142': " . (strpos($results['templates']['contact'], '(503) 555-0142') !== false ? 'YES' : 'NO') . "\n";
echo "Contact contains '123 Industrial Pkwy': " . (strpos($results['templates']['contact'], '123 Industrial Pkwy') !== false ? 'YES' : 'NO') . "\n";
echo "Contact contains 'Portland': " . (strpos($results['templates']['contact'], 'Portland') !== false ? 'YES' : 'NO') . "\n";

// Sample footer content
echo "\n=== FOOTER SAMPLE (first 500 chars) ===\n";
echo substr($results['templates']['footer'], 0, 500) . "...\n";

echo "\n=== CONTACT SAMPLE (first 500 chars) ===\n";
echo substr($results['templates']['contact'], 0, 500) . "...\n";
