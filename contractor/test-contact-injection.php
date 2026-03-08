<?php
/**
 * Test Suite for Contact Page Template Injection
 */

require_once 'elementor-contact-injector.php';

class ContactInjectionTest {
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

    public function assertEquals($expected, $actual, $message) {
        $this->assert($expected === $actual, $message);
    }

    public function assertContains($needle, $haystack, $message) {
        $this->assert(strpos($haystack, $needle) !== false, $message);
    }

    public function assertNotContains($needle, $haystack, $message) {
        $this->assert(strpos($haystack, $needle) === false, $message);
    }

    public function assertArrayHasKey($key, $array, $message) {
        $this->assert(array_key_exists($key, $array), $message);
    }

    public function report() {
        echo "\n=== CONTACT PAGE INJECTION TEST RESULTS ===\n\n";
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

// Run tests
$test = new ContactInjectionTest();

// Load template
$template = json_decode(file_get_contents('elementor-contact-template.json'), true);
$test->assert($template !== null, 'Template loads successfully');

// Test data with emergency service
$data = [
    'name' => 'Pyramid Heating & Cooling',
    'phone' => '(503) 555-0142',
    'email' => 'info@pyramidheating.com',
    'address' => 'Portland, OR',
    'hours' => 'Mon-Fri 7am-6pm, Sat 8am-4pm',
    'emergency' => true,
    'emergency_phone' => '(503) 555-0143',
    'map_embed' => '<iframe src="https://maps.google.com/maps?q=Portland+OR"></iframe>',
    'hours_note' => 'Closed Sundays and major holidays'
];

// Test 1: Basic injection
$populated = inject_contact_data($template, $data);
$test->assert($populated !== null, 'Data injection completes without error');

$json = json_encode($populated);

// Test 2: Phone number injection
$test->assertContains('(503) 555-0142', $json, 'Phone number injected correctly');
$test->assertContains('tel:5035550142', $json, 'Phone tel: link generated correctly');

// Test 3: Email injection
$test->assertContains('info@pyramidheating.com', $json, 'Email injected correctly');
$test->assertContains('mailto:info@pyramidheating.com', $json, 'Email mailto: link generated correctly');

// Test 4: Address injection
$test->assertContains('Portland, OR', $json, 'Address injected correctly');

// Test 5: Hours injection
$test->assertContains('Mon-Fri 7am-6pm, Sat 8am-4pm', $json, 'Hours injected correctly');
$test->assertContains('Closed Sundays and major holidays', $json, 'Hours note injected correctly');

// Test 6: Map embed injection
$test->assertContains('maps.google.com\/maps?q=Portland+OR', $json, 'Map embed code injected');

// Test 7: Emergency section (enabled)
$test->assertContains('Emergency Service Available 24\/7', $json, 'Emergency title shows when emergency is true');
$test->assertContains('(503) 555-0143', $json, 'Emergency phone injected');
$test->assertContains('tel:5035550143', $json, 'Emergency phone tel: link generated');
$test->assertContains('Call now for immediate assistance', $json, 'Emergency subtext shows');

// Test 8: No remaining placeholders
$remaining = find_remaining_contact_placeholders($populated);
$test->assertEquals(0, count($remaining), 'No placeholders remain after injection');

// Test 9: Template validation
try {
    validate_contact_template($template);
    $test->assert(true, 'Template validation passes');
} catch (Exception $e) {
    $test->assert(false, 'Template validation passes');
}

// Test 10: Missing required field
try {
    $incomplete_data = $data;
    unset($incomplete_data['email']);
    inject_contact_data($template, $incomplete_data);
    $test->assert(false, 'Missing required field throws exception');
} catch (InvalidArgumentException $e) {
    $test->assert(true, 'Missing required field throws exception');
}

// Test 11: Invalid email format
try {
    $bad_data = $data;
    $bad_data['email'] = 'not-an-email';
    inject_contact_data($template, $bad_data);
    $test->assert(false, 'Invalid email throws exception');
} catch (InvalidArgumentException $e) {
    $test->assert(true, 'Invalid email throws exception');
}

// Test 12: Emergency true without emergency_phone
try {
    $bad_data = $data;
    $bad_data['emergency'] = true;
    unset($bad_data['emergency_phone']);
    inject_contact_data($template, $bad_data);
    $test->assert(false, 'Emergency true without emergency_phone throws exception');
} catch (InvalidArgumentException $e) {
    $test->assert(true, 'Emergency true without emergency_phone throws exception');
}

// Test 13: Emergency false (no emergency service)
$no_emergency_data = [
    'name' => 'Standard Plumbing',
    'phone' => '(503) 555-9999',
    'email' => 'contact@standardplumbing.com',
    'address' => 'Beaverton, OR',
    'hours' => 'Mon-Fri 8am-5pm',
    'emergency' => false
];

$populated_no_emergency = inject_contact_data($template, $no_emergency_data);
$json_no_emergency = json_encode($populated_no_emergency);

$test->assertContains('Standard Service Hours Apply', $json_no_emergency, 'Non-emergency title shows when emergency is false');
$test->assertContains('Contact us during business hours', $json_no_emergency, 'Non-emergency subtext shows');
$test->assertContains('(503) 555-9999', $json_no_emergency, 'Regular phone used for emergency section when emergency is false');

// Test 14: Default map placeholder when not provided
$no_map_data = [
    'name' => 'Test Company',
    'phone' => '(555) 123-4567',
    'email' => 'test@example.com',
    'address' => 'Test City',
    'hours' => 'Mon-Fri 9am-5pm',
    'emergency' => false
];

$populated_no_map = inject_contact_data($template, $no_map_data);
$json_no_map = json_encode($populated_no_map);
$test->assertContains('MAP_EMBED_CODE_HERE', $json_no_map, 'Default map placeholder used when not provided');

// Test 15: Default hours note when not provided
$test->assertContains('Closed on major holidays', $json_no_map, 'Default hours note used when not provided');

// Test 16: Template structure integrity
$test->assertArrayHasKey('content', $populated, 'Template has content key');
$test->assertArrayHasKey('page_settings', $populated, 'Template has page_settings key');
$test->assert(is_array($populated['content']), 'Content is an array');

// Test 17: Section count
$test->assertEquals(6, count($populated['content']), 'Template has 6 sections (hero, info, form, hours, map, emergency)');

// Test 18: Google Maps embed generator
$map_code = generate_google_maps_embed('123 Main St, Portland, OR', '100%', 450);
$test->assertContains('<iframe', $map_code, 'Google Maps generator creates iframe');
$test->assertContains('maps.google.com', $map_code, 'Google Maps generator uses correct domain');
$test->assertContains(urlencode('123 Main St, Portland, OR'), $map_code, 'Google Maps generator encodes address');

// Test 19: Special characters in address
$special_data = $data;
$special_data['address'] = '123 O\'Reilly Street, Portland, OR';
$populated_special = inject_contact_data($template, $special_data);
$json_special = json_encode($populated_special);
$test->assertContains("O'Reilly", $json_special, 'Special characters handled in address');

// Test 20: Complex hours format
$complex_data = $data;
$complex_data['hours'] = 'Mon-Fri: 7am-6pm | Sat: 8am-4pm | Sun: Closed';
$populated_complex = inject_contact_data($template, $complex_data);
$json_complex = json_encode($populated_complex);
$test->assertContains('Mon-Fri: 7am-6pm', $json_complex, 'Complex hours format preserved');

// Test 21: Long email address
$long_email_data = $data;
$long_email_data['email'] = 'customer.service.department@pyramidheatingandcooling.com';
$populated_long = inject_contact_data($template, $long_email_data);
$json_long = json_encode($populated_long);
$test->assertContains('customer.service.department@pyramidheatingandcooling.com', $json_long, 'Long email address handled');

// Test 22: Phone number with extension
$ext_data = $data;
$ext_data['phone'] = '(503) 555-0142 ext. 123';
$populated_ext = inject_contact_data($template, $ext_data);
$json_ext = json_encode($populated_ext);
$test->assertContains('(503) 555-0142 ext. 123', $json_ext, 'Phone with extension preserved');

// Test 23: Multiple-line address support
$multiline_data = $data;
$multiline_data['address'] = 'Portland, OR 97201';
$populated_multiline = inject_contact_data($template, $multiline_data);
$json_multiline = json_encode($populated_multiline);
$test->assertContains('Portland, OR 97201', $json_multiline, 'Address with ZIP code handled');

// Generate summary
echo "\n=== SAMPLE OUTPUT ===\n";
echo "Company: {$data['name']}\n";
echo "Phone: {$data['phone']}\n";
echo "Email: {$data['email']}\n";
echo "Address: {$data['address']}\n";
echo "Hours: {$data['hours']}\n";
echo "Emergency Service: " . ($data['emergency'] ? 'Yes' : 'No') . "\n";
if ($data['emergency']) {
    echo "Emergency Phone: {$data['emergency_phone']}\n";
}
echo "Map Embed: " . (strpos($json, 'maps.google.com') !== false ? 'Present' : 'Placeholder') . "\n";

$success = $test->report();
exit($success ? 0 : 1);
