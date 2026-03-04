<?php
require_once 'webprinter-engine.php';

$data = [
    'company_name' => 'Test',
    'trade' => 'hvac',
    'phone' => '555-1234',
    'email' => 'test@test.com',
    'address' => '123 Main',
    'city' => 'Portland',
    'state' => 'OR',
    'zip' => '97210',
    'services' => [['name' => 'S1']],
    'social_media' => [
        'facebook' => 'https://facebook.com/test',
        'twitter' => 'https://twitter.com/test'
    ]
];

$results = generate_contractor_templates($data);
$footer = $results['templates']['footer'];

echo "Footer contains {{facebook}}: " . (strpos($footer, '{{facebook}}') !== false ? 'YES' : 'NO') . "\n";
echo "Footer contains facebook.com: " . (strpos($footer, 'facebook.com') !== false ? 'YES' : 'NO') . "\n";
echo "Footer contains https://facebook.com/test: " . (strpos($footer, 'https://facebook.com/test') !== false ? 'YES' : 'NO') . "\n";

// Find the tagline section
$tagline_pos = strpos($footer, 'tagline');
if ($tagline_pos !== false) {
    echo "\nTagline section found at position $tagline_pos\n";
    echo "Context: " . substr($footer, max(0, $tagline_pos - 100), 300) . "\n";
}
