<?php
/**
 * Elementor About Page Template Injector
 *
 * Injects contractor data into Elementor about page template
 */

require_once 'elementor-analyzer.php';

/**
 * Inject contractor data into about page template
 */
function inject_about_data($about_template_json, $contractor_data) {
    $data = is_string($about_template_json)
        ? json_decode($about_template_json, true)
        : $about_template_json;

    if (!$data) {
        throw new Exception("Invalid about template JSON provided");
    }

    // Calculate years in business
    if (isset($contractor_data['founded'])) {
        $current_year = date('Y');
        $years = $current_year - intval($contractor_data['founded']);
        $contractor_data['years_in_business'] = $years;
    }

    // Prepare trust signals array items
    if (isset($contractor_data['trust_signals']) && is_array($contractor_data['trust_signals'])) {
        foreach ($contractor_data['trust_signals'] as $index => $signal) {
            $contractor_data["trust_$index"] = $signal;
        }
    }

    // Prepare values array items
    if (isset($contractor_data['values']) && is_array($contractor_data['values'])) {
        foreach ($contractor_data['values'] as $index => $value) {
            $contractor_data["value_$index"] = $value;
        }
    }

    // Standard injection
    $modified_data = inject_about_recursive($data, $contractor_data);

    return json_encode($modified_data);
}

/**
 * Recursive injection helper for about page
 */
function inject_about_recursive($data, $contractor_data) {
    if (!is_array($data)) {
        return $data;
    }

    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $data[$key] = inject_about_recursive($value, $contractor_data);
        } else if (is_string($value)) {
            $data[$key] = replace_about_placeholders($value, $contractor_data);
        }
    }

    return $data;
}

/**
 * Replace all placeholders in a string
 */
function replace_about_placeholders($value, $contractor_data) {
    foreach ($contractor_data as $key => $data_value) {
        $placeholder = '{{' . $key . '}}';

        if (stripos($value, $placeholder) !== false) {
            $replacement = is_array($data_value)
                ? implode(', ', $data_value)
                : $data_value;
            $value = str_replace($placeholder, $replacement, $value);
        }
    }

    return $value;
}

/**
 * Save about page to WordPress database
 */
function save_about_page($db, $populated_json, $contractor_data) {
    $page_title = 'About ' . ($contractor_data['name'] ?? 'Us');

    // Create page post
    $stmt = $db->prepare('
        INSERT INTO wp_posts (
            post_title,
            post_content,
            post_status,
            post_type,
            post_author
        ) VALUES (
            :title,
            "",
            "draft",
            "page",
            1
        )
    ');

    $stmt->execute(['title' => $page_title]);
    $page_id = $db->lastInsertId();

    // Add Elementor data
    $stmt = $db->prepare('
        INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
        VALUES
            (:id, "_elementor_data", :data),
            (:id, "_elementor_edit_mode", "builder"),
            (:id, "_elementor_version", "3.16.0")
    ');

    $stmt->execute([
        'id' => $page_id,
        'data' => $populated_json
    ]);

    return $page_id;
}

// =============================================================================
// DEMONSTRATION
// =============================================================================

echo "\n" . str_repeat("=", 80) . "\n";
echo "ELEMENTOR ABOUT PAGE INJECTOR - DEMONSTRATION\n";
echo str_repeat("=", 80) . "\n\n";

// Sample contractor data
$contractor_data = [
    'name' => 'Pyramid Heating & Cooling',
    'founder' => 'Don Mitchell',
    'founded' => '2006',
    'city' => 'Portland',
    'state' => 'OR',
    'story' => 'Family owned since 2006, we started with a simple mission: provide honest, reliable HVAC service to our neighbors. Today, we\'re proud to be Portland\'s most trusted heating and cooling company.',
    'team_size' => '12',
    'license' => 'CCB #123456',
    'values' => ['Quality Work', 'Fair Pricing', 'Local Service'],
    'trust_signals' => [
        'Licensed & Insured',
        'NATE Certified',
        '5-Star Rated',
        '18+ Years'
    ]
];

// Load template
echo "Step 1: Loading about page template...\n\n";

$about_template = file_get_contents('elementor-about-template.json');

if (!$about_template) {
    die("Error: Template file not found.\n");
}

echo "✓ About page template loaded (" . strlen($about_template) . " bytes)\n\n";

// Inject data
echo "Step 2: Injecting contractor data into about page...\n\n";

$populated_about = inject_about_data($about_template, $contractor_data);
$about_decoded = json_decode($populated_about, true);

echo "About page fields populated:\n";
echo "  • Business name: " . $contractor_data['name'] . "\n";
echo "  • Founder: " . $contractor_data['founder'] . "\n";
echo "  • Founded: " . $contractor_data['founded'] . "\n";
echo "  • Years in business: " . (date('Y') - intval($contractor_data['founded'])) . "\n";
echo "  • Team size: " . $contractor_data['team_size'] . "\n";
echo "  • Location: " . $contractor_data['city'] . ", " . $contractor_data['state'] . "\n";
echo "  • License: " . $contractor_data['license'] . "\n";
echo "  • Trust signals: " . count($contractor_data['trust_signals']) . " items\n";
echo "  • Values: " . count($contractor_data['values']) . " items\n\n";

// Verify injection
echo "Step 3: Verifying data injection...\n\n";

// Check hero section
$hero_title = $about_decoded[0]['elements'][0]['elements'][0]['settings']['title'] ?? '';
$hero_story = $about_decoded[0]['elements'][0]['elements'][1]['settings']['editor'] ?? '';

// Check our story section
$story_text = $about_decoded[1]['elements'][0]['elements'][1]['settings']['editor'] ?? '';

// Check trust signals
$trust_1 = $about_decoded[3]['elements'][0]['elements'][0]['settings']['title_text'] ?? '';
$trust_2 = $about_decoded[3]['elements'][1]['elements'][0]['settings']['title_text'] ?? '';
$trust_3 = $about_decoded[3]['elements'][2]['elements'][0]['settings']['title_text'] ?? '';
$trust_4 = $about_decoded[3]['elements'][3]['elements'][0]['settings']['title_text'] ?? '';

// Check values
$value_1 = $about_decoded[6]['elements'][0]['elements'][0]['settings']['title_text'] ?? '';
$value_2 = $about_decoded[6]['elements'][1]['elements'][0]['settings']['title_text'] ?? '';
$value_3 = $about_decoded[6]['elements'][2]['elements'][0]['settings']['title_text'] ?? '';

// Check CTA
$cta_heading = $about_decoded[7]['elements'][0]['elements'][0]['settings']['title'] ?? '';

echo "Verification Results:\n";
echo str_repeat("-", 80) . "\n";

// Hero Section
echo "Hero Section:\n";
echo "  • Heading: " . ($hero_title === 'About ' . $contractor_data['name'] ? "✓ PASS" : "✗ FAIL") . "\n";
echo "    Value: $hero_title\n";
echo "  • Story: " . (strpos($hero_story, $contractor_data['story']) !== false ? "✓ PASS" : "✗ FAIL") . "\n\n";

// Story Section
echo "Our Story Section:\n";
echo "  • Contains founder name: " . (strpos($story_text, $contractor_data['founder']) !== false ? "✓ PASS" : "✗ FAIL") . "\n";
echo "  • Contains founded year: " . (strpos($story_text, $contractor_data['founded']) !== false ? "✓ PASS" : "✗ FAIL") . "\n";
echo "  • Contains city: " . (strpos($story_text, $contractor_data['city']) !== false ? "✓ PASS" : "✗ FAIL") . "\n";
echo "  • Contains team size: " . (strpos($story_text, $contractor_data['team_size']) !== false ? "✓ PASS" : "✗ FAIL") . "\n\n";

// Trust Signals
echo "Trust Signals (Why Choose Us):\n";
echo "  • Signal 1: " . ($trust_1 === $contractor_data['trust_signals'][0] ? "✓ PASS" : "✗ FAIL") . " ($trust_1)\n";
echo "  • Signal 2: " . ($trust_2 === $contractor_data['trust_signals'][1] ? "✓ PASS" : "✗ FAIL") . " ($trust_2)\n";
echo "  • Signal 3: " . ($trust_3 === $contractor_data['trust_signals'][2] ? "✓ PASS" : "✗ FAIL") . " ($trust_3)\n";
echo "  • Signal 4: " . ($trust_4 === $contractor_data['trust_signals'][3] ? "✓ PASS" : "✗ FAIL") . " ($trust_4)\n\n";

// Values
echo "Values Section:\n";
echo "  • Value 1: " . ($value_1 === $contractor_data['values'][0] ? "✓ PASS" : "✗ FAIL") . " ($value_1)\n";
echo "  • Value 2: " . ($value_2 === $contractor_data['values'][1] ? "✓ PASS" : "✗ FAIL") . " ($value_2)\n";
echo "  • Value 3: " . ($value_3 === $contractor_data['values'][2] ? "✓ PASS" : "✗ FAIL") . " ($value_3)\n\n";

// CTA
echo "CTA Section:\n";
echo "  • Heading: " . ($cta_heading === 'Ready to Work Together?' ? "✓ PASS" : "✗ FAIL") . "\n";
echo "    Value: $cta_heading\n\n";

// Summary
$total_tests = 13;
$passed_tests = 0;

if ($hero_title === 'About ' . $contractor_data['name']) $passed_tests++;
if (strpos($hero_story, $contractor_data['story']) !== false) $passed_tests++;
if (strpos($story_text, $contractor_data['founder']) !== false) $passed_tests++;
if (strpos($story_text, $contractor_data['founded']) !== false) $passed_tests++;
if (strpos($story_text, $contractor_data['city']) !== false) $passed_tests++;
if (strpos($story_text, $contractor_data['team_size']) !== false) $passed_tests++;
if ($trust_1 === $contractor_data['trust_signals'][0]) $passed_tests++;
if ($trust_2 === $contractor_data['trust_signals'][1]) $passed_tests++;
if ($trust_3 === $contractor_data['trust_signals'][2]) $passed_tests++;
if ($trust_4 === $contractor_data['trust_signals'][3]) $passed_tests++;
if ($value_1 === $contractor_data['values'][0]) $passed_tests++;
if ($value_2 === $contractor_data['values'][1]) $passed_tests++;
if ($value_3 === $contractor_data['values'][2]) $passed_tests++;

echo str_repeat("=", 80) . "\n";
echo "TEST SUMMARY: $passed_tests / $total_tests tests passed\n";
echo str_repeat("=", 80) . "\n\n";

if ($passed_tests === $total_tests) {
    echo "✓ ALL TESTS PASSED - About page injection working perfectly!\n\n";
} else {
    echo "⚠ Some tests failed. Review the verification results above.\n\n";
}

// Show sample output
echo str_repeat("=", 80) . "\n";
echo "SAMPLE OUTPUT (excerpt)\n";
echo str_repeat("=", 80) . "\n";
echo substr($populated_about, 0, 500) . "...\n\n";

// Usage example with database
echo str_repeat("=", 80) . "\n";
echo "USAGE WITH DATABASE\n";
echo str_repeat("=", 80) . "\n";
echo '
// Connect to database
$analyzer = new ElementorAnalyzer();
$analyzer->connectDatabase("localhost", "wordpress_db", "user", "password");
$db = $analyzer->getDb();

// Load template
$about_template = file_get_contents("elementor-about-template.json");

// Contractor data
$contractor_data = [
    "name" => "Pyramid Heating & Cooling",
    "founder" => "Don Mitchell",
    "founded" => "2006",
    "city" => "Portland",
    "state" => "OR",
    "story" => "Your company story...",
    "team_size" => "12",
    "license" => "CCB #123456",
    "values" => ["Quality Work", "Fair Pricing", "Local Service"],
    "trust_signals" => ["Licensed & Insured", "NATE Certified", "5-Star Rated", "18+ Years"]
];

// Inject data
$populated_about = inject_about_data($about_template, $contractor_data);

// Save to database
$page_id = save_about_page($db, $populated_about, $contractor_data);

echo "About page saved with ID: $page_id\n";

// Publish the page
$stmt = $db->prepare("UPDATE wp_posts SET post_status = \"publish\" WHERE ID = :id");
$stmt->execute(["id" => $page_id]);
';

echo "\n\n✓ Demonstration complete!\n\n";

echo "Page Sections Created:\n";
echo "  1. Hero - 'About [Business Name]' headline + founding story\n";
echo "  2. Our Story - Company history with founder, years in business, team size\n";
echo "  3. Why Choose Us - 4 trust signal icon boxes\n";
echo "  4. Meet The Team - Team description and qualifications\n";
echo "  5. Our Values - 3 core values in columns\n";
echo "  6. CTA - 'Ready to Work Together?' with contact button\n\n";

echo "Next steps:\n";
echo "  1. Review the generated JSON template\n";
echo "  2. Customize colors/styling as needed\n";
echo "  3. Use inject_about_data() function for your contractors\n";
echo "  4. Save to WordPress database using save_about_page()\n";
echo "  5. Publish when ready\n\n";
