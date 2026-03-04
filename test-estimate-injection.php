<?php
/**
 * Test Suite for Estimate Page Template Injection
 */

require_once 'elementor-estimate-injector.php';

class EstimateInjectionTest {
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
        echo "\n=== ESTIMATE PAGE INJECTION TEST RESULTS ===\n\n";
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
$test = new EstimateInjectionTest();

// Load template
$template = json_decode(file_get_contents('elementor-estimate-template.json'), true);
$test->assert($template !== null, 'Template loads successfully');

// Test data
$data = [
    'name' => 'Pyramid Heating & Cooling',
    'phone' => '(503) 555-0142',
    'instabid_embed' => '<iframe src="https://instabid.app/embed/pyramid-hvac" width="100%" height="800"></iframe>',
    'response_time' => 'Within 2 hours',
    'trust_signals' => [
        '100% Free',
        'No Obligation',
        'Same Day Response',
        'Licensed & Insured'
    ],
    'faqs' => [
        [
            'q' => 'How long does it take to get an estimate?',
            'a' => 'Most estimates are delivered within 2 hours during business hours. For complex projects, we may schedule an in-home consultation to provide the most accurate quote.'
        ],
        [
            'q' => 'Is the estimate really free?',
            'a' => 'Yes! We provide completely free, no-obligation estimates for all services. You\'ll receive a detailed breakdown of costs with no hidden fees or surprise charges.'
        ],
        [
            'q' => 'What areas do you serve?',
            'a' => 'We proudly serve Portland and surrounding areas including Beaverton, Gresham, Lake Oswego, and Tigard. Contact us to confirm service availability in your specific location.'
        ]
    ],
    'steps' => [
        'Fill Out Form',
        'Receive Estimate',
        'Approve Quote'
    ]
];

// Test 1: Basic injection
$populated = inject_estimate_data($template, $data);
$test->assert($populated !== null, 'Data injection completes without error');

$json = json_encode($populated);

// Test 2: Phone number injection
$test->assertContains('(503) 555-0142', $json, 'Phone number injected correctly');

// Test 3: InstaBid embed injection
$test->assertContains('https:\/\/instabid.app\/embed\/pyramid-hvac', $json, 'InstaBid embed code injected');

// Test 4: Response time injection
$test->assertContains('Within 2 hours', $json, 'Response time injected');

// Test 5: Trust signals injection
$test->assertContains('100% Free', $json, 'Trust signal 1 injected');
$test->assertContains('No Obligation', $json, 'Trust signal 2 injected');
$test->assertContains('Same Day Response', $json, 'Trust signal 3 injected');
$test->assertContains('Licensed & Insured', $json, 'Trust signal 4 injected');

// Test 6: Steps injection
$test->assertContains('Fill Out Form', $json, 'Step 1 injected');
$test->assertContains('Receive Estimate', $json, 'Step 2 injected');
$test->assertContains('Approve Quote', $json, 'Step 3 injected');

// Test 7: FAQ questions injection
$test->assertContains('How long does it take to get an estimate?', $json, 'FAQ 1 question injected');
$test->assertContains('Is the estimate really free?', $json, 'FAQ 2 question injected');
$test->assertContains('What areas do you serve?', $json, 'FAQ 3 question injected');

// Test 8: FAQ answers injection
$test->assertContains('Most estimates are delivered within 2 hours', $json, 'FAQ 1 answer injected');
$test->assertContains('completely free, no-obligation estimates', $json, 'FAQ 2 answer injected');
$test->assertContains('Portland and surrounding areas', $json, 'FAQ 3 answer injected');

// Test 9: No remaining placeholders
$remaining = find_remaining_placeholders($populated);
$test->assertEquals(0, count($remaining), 'No placeholders remain after injection');

// Test 10: Template validation
try {
    validate_estimate_template($template);
    $test->assert(true, 'Template validation passes');
} catch (Exception $e) {
    $test->assert(false, 'Template validation passes');
}

// Test 11: Missing required field
try {
    $incomplete_data = $data;
    unset($incomplete_data['phone']);
    inject_estimate_data($template, $incomplete_data);
    $test->assert(false, 'Missing required field throws exception');
} catch (InvalidArgumentException $e) {
    $test->assert(true, 'Missing required field throws exception');
}

// Test 12: Wrong number of trust signals
try {
    $bad_data = $data;
    $bad_data['trust_signals'] = ['Signal 1', 'Signal 2']; // Only 2 instead of 4
    inject_estimate_data($template, $bad_data);
    $test->assert(false, 'Wrong number of trust signals throws exception');
} catch (InvalidArgumentException $e) {
    $test->assert(true, 'Wrong number of trust signals throws exception');
}

// Test 13: Wrong number of FAQs
try {
    $bad_data = $data;
    $bad_data['faqs'] = [
        ['q' => 'Question 1', 'a' => 'Answer 1']
    ]; // Only 1 instead of 3
    inject_estimate_data($template, $bad_data);
    $test->assert(false, 'Wrong number of FAQs throws exception');
} catch (InvalidArgumentException $e) {
    $test->assert(true, 'Wrong number of FAQs throws exception');
}

// Test 14: FAQ missing keys
try {
    $bad_data = $data;
    $bad_data['faqs'] = [
        ['question' => 'Q1', 'answer' => 'A1'],
        ['question' => 'Q2', 'answer' => 'A2'],
        ['question' => 'Q3', 'answer' => 'A3']
    ]; // Wrong keys (should be 'q' and 'a')
    inject_estimate_data($template, $bad_data);
    $test->assert(false, 'FAQ with wrong keys throws exception');
} catch (InvalidArgumentException $e) {
    $test->assert(true, 'FAQ with wrong keys throws exception');
}

// Test 15: Default steps when not provided
$data_no_steps = $data;
unset($data_no_steps['steps']);
$populated_default = inject_estimate_data($template, $data_no_steps);
$json_default = json_encode($populated_default);
$test->assertContains('Submit Details', $json_default, 'Default step 1 used when steps not provided');
$test->assertContains('Get Estimate', $json_default, 'Default step 2 used when steps not provided');
$test->assertContains('Receive Quote', $json_default, 'Default step 3 used when steps not provided');

// Test 16: Placeholder comment in embed
$placeholder_data = $data;
$placeholder_data['instabid_embed'] = '<!-- INSTABID_EMBED_CODE_HERE -->';
$populated_placeholder = inject_estimate_data($template, $placeholder_data);
$json_placeholder = json_encode($populated_placeholder);
$test->assertContains('<!-- INSTABID_EMBED_CODE_HERE -->', $json_placeholder, 'Placeholder comment preserved in HTML widget');

// Test 17: Template structure integrity
$test->assertArrayHasKey('content', $populated, 'Template has content key');
$test->assertArrayHasKey('page_settings', $populated, 'Template has page_settings key');
$test->assert(is_array($populated['content']), 'Content is an array');

// Test 18: Section count
$test->assertEquals(7, count($populated['content']), 'Template has 7 sections (hero, embed, trust, how-it-works, steps, faq, contact-alt)');

// Test 19: Accordion structure
$faq_section = null;
foreach ($populated['content'] as $section) {
    if (isset($section['id']) && $section['id'] === 'faq-section') {
        $faq_section = $section;
        break;
    }
}
$test->assert($faq_section !== null, 'FAQ section exists');

// Test 20: Special characters in FAQ answers
$special_data = $data;
$special_data['faqs'][0]['a'] = "We're the #1 choice! Questions? Call us at (503) 555-0142.";
$populated_special = inject_estimate_data($template, $special_data);
$json_special = json_encode($populated_special);
$test->assertContains("We're the #1 choice", $json_special, 'Special characters handled in FAQ answers');

// Generate summary
echo "\n=== SAMPLE OUTPUT ===\n";
echo "Phone: {$data['phone']}\n";
echo "Response Time: {$data['response_time']}\n";
echo "Trust Signals: " . implode(', ', $data['trust_signals']) . "\n";
echo "Steps: " . implode(' → ', $data['steps']) . "\n";
echo "FAQs: " . count($data['faqs']) . " questions\n";
echo "Embed Code Present: " . (strpos($json, 'instabid.app') !== false ? 'Yes' : 'Placeholder') . "\n";

$success = $test->report();
exit($success ? 0 : 1);
