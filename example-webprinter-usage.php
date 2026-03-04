<?php
/**
 * WebPrinter Engine - Usage Examples
 *
 * Demonstrates how to use the WebPrinter engine to generate complete
 * contractor websites with a single function call.
 */

require_once 'webprinter-engine.php';

echo "=== WEBPRINTER ENGINE USAGE EXAMPLES ===\n\n";

// Example 1: Complete 7-Page Website Generation
echo "Example 1: Complete Website Generation\n";
echo str_repeat('-', 70) . "\n";

$contractor_data = [
    'company_name' => 'Pyramid Heating & Cooling',
    'trade' => 'hvac',
    'phone' => '(503) 555-0142',
    'email' => 'info@pyramidheating.com',
    'address' => '123 Industrial Pkwy',
    'city' => 'Portland',
    'state' => 'OR',
    'zip' => '97210',

    // Optional: Enhanced data
    'lat' => 45.5231,
    'lng' => -122.6765,
    'rating' => 4.7,
    'review_count' => 143,
    'website' => 'https://pyramidheating.com',
    'logo_url' => 'https://pyramidheating.com/logo.png',
    'years_in_business' => 25,

    'services' => [
        ['name' => 'Furnace Repair', 'desc' => 'Expert furnace repair', 'price' => 'From $89'],
        ['name' => 'AC Installation', 'desc' => 'Professional AC install', 'price' => 'From $2,999'],
        ['name' => 'Duct Cleaning', 'desc' => 'Thorough duct cleaning', 'price' => 'From $299']
    ],

    'service_areas' => ['Portland', 'Beaverton', 'Hillsboro', 'Gresham'],
    'hours' => 'Mo-Fr 07:00-18:00',

    'social_media' => [
        'facebook' => 'https://facebook.com/pyramidheating',
        'twitter' => 'https://twitter.com/pyramidheating',
        'instagram' => 'https://instagram.com/pyramidheating'
    ]
];

// Generate all templates (no WordPress integration)
$results = generate_contractor_templates($contractor_data);

echo "Generation Results:\n";
echo "  Success: " . ($results['success'] ? 'YES' : 'NO') . "\n";
echo "  Templates created: " . count($results['templates']) . "\n";
echo "    - " . implode("\n    - ", array_keys($results['templates'])) . "\n";
echo "  Schema markup: " . (strlen($results['schema']) > 0 ? 'Generated' : 'Not generated') . "\n";
echo "  Meta tags: " . (strlen($results['meta_tags']) > 0 ? 'Generated' : 'Not generated') . "\n\n";

// Example 2: Minimal Data (Quick Start)
echo "Example 2: Quick Start with Minimal Data\n";
echo str_repeat('-', 70) . "\n";

$minimal_data = [
    'company_name' => 'Quick Fix Plumbing',
    'trade' => 'plumbing',
    'phone' => '(555) 123-4567',
    'email' => 'info@quickfix.com',
    'city' => 'Seattle',
    'state' => 'WA'
];

$quick_results = quick_print_site($minimal_data);

echo "Quick Generation Results:\n";
echo "  Templates: " . count($quick_results['templates']) . "\n";
echo "  Auto-added services: ";
if (isset($quick_results['templates']['services'])) {
    echo "Yes (plumbing defaults)\n";
} else {
    echo "No\n";
}
echo "\n";

// Example 3: Export Templates to Files
echo "Example 3: Export Templates to Files\n";
echo str_repeat('-', 70) . "\n";

$output_dir = './webprinter-output';
if (!is_dir($output_dir)) {
    mkdir($output_dir, 0755, true);
}

export_templates_to_files($results['templates'], $output_dir);
echo "Templates exported to: $output_dir/\n\n";

// Example 4: Generate with WordPress Integration (requires credentials)
echo "Example 4: WordPress Integration (Requires Credentials)\n";
echo str_repeat('-', 70) . "\n";

echo "To create pages directly in WordPress:\n\n";
echo "```php\n";
echo "\$wp_results = print_contractor_site(\$contractor_data, [\n";
echo "    'create_pages' => true,\n";
echo "    'set_theme_builder' => true,\n";
echo "    'wp_site_url' => 'https://yoursite.com',\n";
echo "    'wp_username' => 'your_username',\n";
echo "    'wp_app_password' => 'your_app_password'\n";
echo "]);\n";
echo "```\n\n";

echo "This will:\n";
echo "  1. Create 7 Elementor templates (header, footer, 5 pages)\n";
echo "  2. Generate Schema.org markup\n";
echo "  3. Create WordPress pages\n";
echo "  4. Set header/footer as global theme builder elements\n";
echo "  5. Return URLs for all created pages\n\n";

// Example 5: Different Trade Types
echo "Example 5: Different Trade Types\n";
echo str_repeat('-', 70) . "\n";

$trades = [
    'hvac' => 'HVAC Business',
    'plumbing' => 'Plumber',
    'electrical' => 'Electrician',
    'roofing' => 'Roofing Contractor',
    'painting' => 'Professional Service',
    'landscaping' => 'Professional Service',
    'locksmith' => 'Locksmith'
];

echo "Supported trade types and their Schema.org mappings:\n";
foreach ($trades as $trade => $schema_type) {
    echo sprintf("  %-15s -> %s\n", ucfirst($trade), $schema_type);
}
echo "\n";

// Example 6: Data Validation
echo "Example 6: Data Validation\n";
echo str_repeat('-', 70) . "\n";

$invalid_data = [
    'company_name' => 'Test Company',
    'trade' => 'hvac'
    // Missing required fields
];

$validation = validate_webprinter_data($invalid_data);
echo "Validation result:\n";
echo "  Valid: " . ($validation['valid'] ? 'YES' : 'NO') . "\n";
if (!$validation['valid']) {
    echo "  Errors:\n";
    foreach ($validation['errors'] as $error) {
        echo "    - $error\n";
    }
}
echo "\n";

// Example 7: Custom InstaBid Integration
echo "Example 7: Custom InstaBid Integration\n";
echo str_repeat('-', 70) . "\n";

$instabid_data = $contractor_data;
$instabid_data['instabid_embed'] = '<iframe src="https://instabid.com/embed/12345" width="100%" height="800"></iframe>';

$instabid_results = generate_contractor_templates($instabid_data);
echo "InstaBid embed code included in estimate page: ";
echo (strpos($instabid_results['templates']['estimate'], 'instabid.com') !== false ? 'YES' : 'NO') . "\n\n";

// Example 8: Custom FAQs and Trust Signals
echo "Example 8: Custom FAQs and Trust Signals\n";
echo str_repeat('-', 70) . "\n";

$custom_data = $contractor_data;
$custom_data['trust_signals'] = [
    'Licensed & Insured',
    'BBB Accredited',
    '24/7 Emergency Service',
    'Satisfaction Guaranteed'
];
$custom_data['faqs'] = [
    ['q' => 'Do you offer emergency service?', 'a' => 'Yes, we offer 24/7 emergency service.'],
    ['q' => 'Are you licensed and insured?', 'a' => 'Yes, we are fully licensed and insured.'],
    ['q' => 'What is your service area?', 'a' => 'We serve Portland and all surrounding areas.']
];

$custom_results = generate_contractor_templates($custom_data);
echo "Custom trust signals and FAQs added to estimate page\n";
echo "  Trust signals: 4 custom items\n";
echo "  FAQs: 3 custom questions\n\n";

// Example 9: Schema Markup Preview
echo "Example 9: Schema Markup Preview\n";
echo str_repeat('-', 70) . "\n";

echo "Generated Schema.org JSON-LD (first 500 characters):\n";
echo substr($results['schema'], 0, 500) . "...\n\n";

echo "Generated Meta Tags (first 5 tags):\n";
$meta_lines = explode("\n", $results['meta_tags']);
foreach (array_slice($meta_lines, 0, 5) as $line) {
    if (!empty(trim($line))) {
        echo "  " . trim($line) . "\n";
    }
}
echo "\n";

// Example 10: Template Size Information
echo "Example 10: Template Sizes\n";
echo str_repeat('-', 70) . "\n";

echo "Generated template sizes:\n";
foreach ($results['templates'] as $name => $json) {
    $size = strlen($json);
    $kb = round($size / 1024, 2);
    echo sprintf("  %-12s: %s bytes (%s KB)\n", ucfirst($name), number_format($size), $kb);
}
echo "\nTotal size: " . number_format(array_sum(array_map('strlen', $results['templates']))) . " bytes\n\n";

// Summary
echo "=== USAGE SUMMARY ===\n";
echo str_repeat('=', 70) . "\n";
echo "\nThree Main Functions:\n\n";

echo "1. print_contractor_site(\$data, \$options)\n";
echo "   - Full function with optional WordPress integration\n";
echo "   - Creates pages, sets theme builder, returns URLs\n";
echo "   - Use when deploying to WordPress\n\n";

echo "2. generate_contractor_templates(\$data)\n";
echo "   - Generates all templates without WordPress\n";
echo "   - Returns JSON templates for manual use\n";
echo "   - Use for development or custom integration\n\n";

echo "3. quick_print_site(\$minimal_data)\n";
echo "   - Quick generation with minimal required fields\n";
echo "   - Auto-fills missing data with defaults\n";
echo "   - Use for rapid prototyping\n\n";

echo "All functions generate:\n";
echo "  - 7 Elementor templates (header, footer, 5 pages)\n";
echo "  - Complete Schema.org LocalBusiness markup\n";
echo "  - SEO meta tags (title, description, OG, Twitter)\n";
echo "  - Service catalog, contact forms, estimate forms\n\n";

echo "For complete documentation, see WEBPRINTER_README.md\n";

// Cleanup
if (is_dir($output_dir)) {
    array_map('unlink', glob("$output_dir/*"));
    rmdir($output_dir);
}
