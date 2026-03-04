<?php
/**
 * Test Suite for WebPrinter Engine
 */

require_once 'webprinter-engine.php';

class WebPrinterTest {
    private $passed = 0;
    private $failed = 0;
    private $tests = [];

    public function assert($condition, $message) {
        if ($condition) {
            $this->passed++;
            $this->tests[] = "✓ {$message}";
        } else {
            $this->failed++;
            $this->tests[] = "✗ {$message}";
        }
    }

    public function assertContains($needle, $haystack, $message) {
        $this->assert(strpos($haystack, $needle) !== false, $message);
    }

    public function assertArrayHasKey($key, $array, $message) {
        $this->assert(array_key_exists($key, $array), $message);
    }

    public function assertTrue($condition, $message) {
        $this->assert($condition === true, $message);
    }

    public function assertFalse($condition, $message) {
        $this->assert($condition === false, $message);
    }

    public function assertNotEmpty($value, $message) {
        $this->assert(!empty($value), $message);
    }

    public function report() {
        echo "\n=== WEBPRINTER ENGINE TEST RESULTS ===\n\n";
        foreach ($this->tests as $test) {
            echo $test . "\n";
        }
        echo "\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        echo "\n";

        if ($this->failed === 0) {
            echo "🎉 ALL TESTS PASSED!\n\n";
        } else {
            echo "❌ SOME TESTS FAILED\n\n";
        }

        return $this->failed === 0;
    }
}

$test = new WebPrinterTest();

// Test data
$contractor_data = [
    'company_name' => 'Pyramid Heating & Cooling',
    'trade' => 'hvac',
    'phone' => '(503) 555-0142',
    'email' => 'info@pyramidheating.com',
    'address' => '123 Industrial Pkwy',
    'city' => 'Portland',
    'state' => 'OR',
    'zip' => '97210',
    'lat' => 45.5231,
    'lng' => -122.6765,
    'rating' => 4.7,
    'review_count' => 143,
    'website' => 'https://pyramidheating.com',
    'logo_url' => 'https://pyramidheating.com/logo.png',
    'services' => [
        ['name' => 'Furnace Repair', 'description' => 'Expert furnace repair service'],
        ['name' => 'AC Installation', 'description' => 'Professional AC installation'],
        ['name' => 'Duct Cleaning', 'description' => 'Thorough duct cleaning']
    ],
    'service_areas' => ['Portland', 'Beaverton', 'Hillsboro'],
    'hours' => 'Mo-Fr 07:00-18:00',
    'years_in_business' => 25,
    'about_story' => 'Family-owned HVAC company serving Portland since 1998.',
    'certifications' => ['NATE Certified', 'EPA Certified'],
    'team_members' => [
        ['name' => 'John Smith', 'title' => 'Master Technician', 'image' => ''],
        ['name' => 'Jane Doe', 'title' => 'Service Manager', 'image' => '']
    ],
    'social_media' => [
        'facebook' => 'https://facebook.com/pyramidheating',
        'twitter' => 'https://twitter.com/pyramidheating'
    ]
];

echo "=== WEBPRINTER ENGINE TESTS ===\n\n";

// Test 1: Validation function
echo "Test 1: Data Validation\n";
echo str_repeat('-', 60) . "\n";

$validation = validate_webprinter_data($contractor_data);
$test->assertTrue($validation['valid'], 'Valid data passes validation');
$test->assertTrue(empty($validation['errors']), 'No validation errors for valid data');

// Test with missing required field
$incomplete = $contractor_data;
unset($incomplete['phone']);
$validation = validate_webprinter_data($incomplete);
$test->assertFalse($validation['valid'], 'Invalid data fails validation');
$test->assertNotEmpty($validation['errors'], 'Validation errors returned for invalid data');

// Test with invalid email
$bad_email = $contractor_data;
$bad_email['email'] = 'not-an-email';
$validation = validate_webprinter_data($bad_email);
$test->assertFalse($validation['valid'], 'Invalid email fails validation');

echo "\n";

// Test 2: Template Generation (without WordPress)
echo "Test 2: Template Generation\n";
echo str_repeat('-', 60) . "\n";

$results = generate_contractor_templates($contractor_data);

$test->assertArrayHasKey('success', $results, 'Results contain success status');
$test->assertTrue($results['success'], 'Template generation succeeds');
$test->assertArrayHasKey('templates', $results, 'Results contain templates');
$test->assertArrayHasKey('schema', $results, 'Results contain schema');
$test->assertArrayHasKey('meta_tags', $results, 'Results contain meta tags');

echo "\n";

// Test 3: Template Content Validation
echo "Test 3: Template Content\n";
echo str_repeat('-', 60) . "\n";

$test->assertArrayHasKey('header', $results['templates'], 'Header template generated');
$test->assertArrayHasKey('footer', $results['templates'], 'Footer template generated');
$test->assertArrayHasKey('home', $results['templates'], 'Home template generated');
$test->assertArrayHasKey('about', $results['templates'], 'About template generated');
$test->assertArrayHasKey('services', $results['templates'], 'Services template generated');
$test->assertArrayHasKey('estimate', $results['templates'], 'Estimate template generated');
$test->assertArrayHasKey('contact', $results['templates'], 'Contact template generated');

// Check header contains company data
$header = $results['templates']['header'];
$test->assertContains('Pyramid Heating & Cooling', $header, 'Header contains company name');
$test->assertContains('(503) 555-0142', $header, 'Header contains phone number');

// Check footer contains company data
$footer = $results['templates']['footer'];
$test->assertContains('Pyramid Heating & Cooling', $footer, 'Footer contains company name');
$test->assertContains('Portland', $footer, 'Footer contains city');

// Check services template
$services = $results['templates']['services'];
$test->assertContains('Furnace Repair', $services, 'Services contains service 1');
$test->assertContains('AC Installation', $services, 'Services contains service 2');
$test->assertContains('Duct Cleaning', $services, 'Services contains service 3');

// Check contact template
$contact = $results['templates']['contact'];
$test->assertContains('info@pyramidheating.com', $contact, 'Contact contains email');
$test->assertContains('97210', $contact, 'Contact contains ZIP code');

echo "\n";

// Test 4: Schema Generation
echo "Test 4: Schema Generation\n";
echo str_repeat('-', 60) . "\n";

$schema = $results['schema'];
$test->assertContains('<script type="application/ld+json">', $schema, 'Schema contains script tag');
$test->assertContains('"@context": "https://schema.org"', $schema, 'Schema has @context');
$test->assertContains('HVACBusiness', $schema, 'Schema has correct business type');
$test->assertContains('Pyramid Heating & Cooling', $schema, 'Schema contains company name');
$test->assertContains('"ratingValue": 4.7', $schema, 'Schema contains rating');
$test->assertContains('Furnace Repair', $schema, 'Schema contains services');

// Validate JSON
$json_content = str_replace(['<script type="application/ld+json">', '</script>'], '', $schema);
$decoded = json_decode(trim($json_content), true);
$test->assertTrue($decoded !== null, 'Schema JSON is valid');

echo "\n";

// Test 5: Meta Tags Generation
echo "Test 5: Meta Tags\n";
echo str_repeat('-', 60) . "\n";

$meta = $results['meta_tags'];
$test->assertContains('<title>', $meta, 'Meta tags include title');
$test->assertContains('Pyramid Heating & Cooling', $meta, 'Title contains company name');
$test->assertContains('Portland', $meta, 'Title contains city');
$test->assertContains('<meta name="description"', $meta, 'Meta description included');
$test->assertContains('<meta property="og:type"', $meta, 'OG type tag included');
$test->assertContains('<meta name="twitter:card"', $meta, 'Twitter card included');
$test->assertContains('4.7/5', $meta, 'Meta description includes rating');

echo "\n";

// Test 6: Quick Print Function
echo "Test 6: Quick Print Function\n";
echo str_repeat('-', 60) . "\n";

$minimal_data = [
    'company_name' => 'Quick Plumbing',
    'trade' => 'plumbing',
    'phone' => '(555) 123-4567',
    'email' => 'info@quickplumbing.com',
    'city' => 'Seattle',
    'state' => 'WA'
];

$quick_results = quick_print_site($minimal_data);
$test->assertTrue($quick_results['success'], 'Quick print succeeds with minimal data');
$test->assertArrayHasKey('templates', $quick_results, 'Quick print generates templates');

$quick_services = $quick_results['templates']['services'];
$test->assertContains('Drain Cleaning', $quick_services, 'Quick print adds default plumbing services');

echo "\n";

// Test 7: Template Export
echo "Test 7: Template Export\n";
echo str_repeat('-', 60) . "\n";

$output_dir = sys_get_temp_dir() . '/webprinter-test';
if (is_dir($output_dir)) {
    array_map('unlink', glob("$output_dir/*"));
    rmdir($output_dir);
}

$export_success = export_templates_to_files($results['templates'], $output_dir);
$test->assertTrue($export_success, 'Template export succeeds');
$test->assertTrue(file_exists($output_dir . '/header-template.json'), 'Header template file created');
$test->assertTrue(file_exists($output_dir . '/footer-template.json'), 'Footer template file created');
$test->assertTrue(file_exists($output_dir . '/home-template.json'), 'Home template file created');
$test->assertTrue(file_exists($output_dir . '/about-template.json'), 'About template file created');
$test->assertTrue(file_exists($output_dir . '/services-template.json'), 'Services template file created');
$test->assertTrue(file_exists($output_dir . '/contact-template.json'), 'Contact template file created');

// Cleanup
array_map('unlink', glob("$output_dir/*"));
rmdir($output_dir);

echo "\n";

// Test 8: Different Trade Types
echo "Test 8: Different Trade Types\n";
echo str_repeat('-', 60) . "\n";

$trades = ['plumbing', 'electrical', 'roofing', 'painting', 'landscaping'];

foreach ($trades as $trade) {
    $trade_data = $contractor_data;
    $trade_data['trade'] = $trade;
    $trade_results = generate_contractor_templates($trade_data);

    $test->assertTrue($trade_results['success'], ucfirst($trade) . ' template generation succeeds');
}

echo "\n";

// Test 9: Error Handling
echo "Test 9: Error Handling\n";
echo str_repeat('-', 60) . "\n";

// Test with completely invalid data
$invalid_data = ['company_name' => 'Test'];
$error_results = generate_contractor_templates($invalid_data);
$test->assertFalse($error_results['success'], 'Invalid data causes failure');
$test->assertNotEmpty($error_results['errors'], 'Errors array populated for invalid data');

echo "\n";

// Test 10: Template Size Validation
echo "Test 10: Template Sizes\n";
echo str_repeat('-', 60) . "\n";

foreach ($results['templates'] as $name => $json) {
    $size = strlen($json);
    $test->assertTrue($size > 100, ucfirst($name) . " template has content (size: $size bytes)");
}

echo "\n";

// Test 11: Required Fields in Templates
echo "Test 11: Required Fields\n";
echo str_repeat('-', 60) . "\n";

// Check all templates have proper JSON structure
foreach ($results['templates'] as $name => $json) {
    $decoded = json_decode($json, true);
    $test->assertTrue($decoded !== null, ucfirst($name) . " template is valid JSON");
}

echo "\n";

// Test 12: Service Data Propagation
echo "Test 12: Service Data Propagation\n";
echo str_repeat('-', 60) . "\n";

$services_template = $results['templates']['services'];
foreach ($contractor_data['services'] as $service) {
    $test->assertContains($service['name'], $services_template, "Service '{$service['name']}' included in services template");
}

echo "\n";

// Test 13: Contact Information Propagation
echo "Test 13: Contact Information\n";
echo str_repeat('-', 60) . "\n";

$contact_template = $results['templates']['contact'];
$test->assertContains($contractor_data['phone'], $contact_template, 'Phone in contact template');
$test->assertContains($contractor_data['email'], $contact_template, 'Email in contact template');
$test->assertContains($contractor_data['address'], $contact_template, 'Address in contact template');
$test->assertContains($contractor_data['city'], $contact_template, 'City in contact template');

echo "\n";

// Test 14: Social Media Links
echo "Test 14: Social Media Integration\n";
echo str_repeat('-', 60) . "\n";

$footer_template = $results['templates']['footer'];
$test->assertContains('facebook.com/pyramidheating', $footer_template, 'Facebook link in footer');
$test->assertContains('twitter.com/pyramidheating', $footer_template, 'Twitter link in footer');

echo "\n";

// Test 15: Schema and Meta Tag Coordination
echo "Test 15: Schema and Meta Tag Coordination\n";
echo str_repeat('-', 60) . "\n";

$test->assertContains('Pyramid Heating & Cooling', $results['schema'], 'Company name in schema');
$test->assertContains('Pyramid Heating & Cooling', $results['meta_tags'], 'Company name in meta tags');
$test->assertContains('Portland', $results['schema'], 'City in schema');
$test->assertContains('Portland', $results['meta_tags'], 'City in meta tags');
$test->assertContains('4.7', $results['schema'], 'Rating in schema');
$test->assertContains('4.7', $results['meta_tags'], 'Rating in meta tags');

echo "\n";

// Generate sample output
echo "=== SAMPLE OUTPUT ===\n";
echo str_repeat('=', 60) . "\n\n";

echo "Templates Generated:\n";
foreach ($results['templates'] as $name => $json) {
    $size = strlen($json);
    echo "  ✓ " . ucfirst($name) . " template: " . number_format($size) . " bytes\n";
}

echo "\nSchema Markup:\n";
echo "  ✓ JSON-LD Schema: " . number_format(strlen($results['schema'])) . " bytes\n";

echo "\nMeta Tags:\n";
$meta_count = substr_count($results['meta_tags'], '<meta');
echo "  ✓ Meta Tags Generated: $meta_count tags\n";

echo "\nFirst 500 characters of home template:\n";
echo substr($results['templates']['home'], 0, 500) . "...\n\n";

echo "First 300 characters of schema:\n";
echo substr($results['schema'], 0, 300) . "...\n\n";

// Summary
echo "=== TEST SUMMARY ===\n";
echo "Total Components Tested:\n";
echo "  - Data validation\n";
echo "  - Template generation (7 templates)\n";
echo "  - Schema markup generation\n";
echo "  - Meta tag generation\n";
echo "  - Quick print function\n";
echo "  - Template export\n";
echo "  - Multiple trade types\n";
echo "  - Error handling\n";
echo "  - Data propagation\n";
echo "\n";

$success = $test->report();
exit($success ? 0 : 1);
