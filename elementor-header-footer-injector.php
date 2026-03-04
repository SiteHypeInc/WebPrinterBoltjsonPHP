<?php
/**
 * Elementor Header & Footer Template Injector
 *
 * Injects contractor data into Elementor header and footer templates
 * Works with the same pattern as page templates
 */

require_once 'elementor-analyzer.php';

/**
 * Inject contractor data into header template
 */
function inject_header_data($header_template_json, $contractor_data) {
    $data = is_string($header_template_json)
        ? json_decode($header_template_json, true)
        : $header_template_json;

    if (!$data) {
        throw new Exception("Invalid header template JSON provided");
    }

    // Prepare navigation items if array provided
    if (isset($contractor_data['nav']) && is_array($contractor_data['nav'])) {
        foreach ($contractor_data['nav'] as $index => $nav_item) {
            $contractor_data["nav_$index"] = $nav_item;
        }
    }

    // Add default logo if not provided
    if (!isset($contractor_data['logo_url']) || empty($contractor_data['logo_url'])) {
        $contractor_data['logo_url'] = 'https://via.placeholder.com/180x60?text=' .
                                        urlencode($contractor_data['name'] ?? 'Logo');
    }

    // Standard injection
    $modified_data = inject_template_recursive($data, $contractor_data);

    return json_encode($modified_data, JSON_UNESCAPED_SLASHES);
}

/**
 * Inject contractor data into footer template
 */
function inject_footer_data($footer_template_json, $contractor_data) {
    $data = is_string($footer_template_json)
        ? json_decode($footer_template_json, true)
        : $footer_template_json;

    if (!$data) {
        throw new Exception("Invalid footer template JSON provided");
    }

    // Prepare navigation items if array provided
    if (isset($contractor_data['nav']) && is_array($contractor_data['nav'])) {
        foreach ($contractor_data['nav'] as $index => $nav_item) {
            $contractor_data["nav_$index"] = $nav_item;
        }
    }

    // Add current year for copyright
    $contractor_data['current_year'] = date('Y');

    // Standard injection
    $modified_data = inject_template_recursive($data, $contractor_data);

    return json_encode($modified_data, JSON_UNESCAPED_SLASHES);
}

/**
 * Recursive template injection helper
 */
function inject_template_recursive($data, $contractor_data) {
    if (!is_array($data)) {
        return $data;
    }

    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $data[$key] = inject_template_recursive($value, $contractor_data);
        } else if (is_string($value)) {
            $data[$key] = replace_placeholders($value, $contractor_data);
        }
    }

    return $data;
}

/**
 * Replace all placeholders in a string
 */
function replace_placeholders($value, $contractor_data) {
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
 * Save header template to WordPress database
 */
function save_header_template($db, $populated_json, $template_name = 'Contractor Header') {
    // Create header template post
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
            "publish",
            "elementor_library",
            1
        )
    ');

    $stmt->execute(['title' => $template_name]);
    $template_id = $db->lastInsertId();

    // Add Elementor data
    $stmt = $db->prepare('
        INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
        VALUES
            (:id, "_elementor_data", :data),
            (:id, "_elementor_edit_mode", "builder"),
            (:id, "_elementor_template_type", "header")
    ');

    $stmt->execute([
        'id' => $template_id,
        'data' => $populated_json
    ]);

    return $template_id;
}

/**
 * Save footer template to WordPress database
 */
function save_footer_template($db, $populated_json, $template_name = 'Contractor Footer') {
    // Create footer template post
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
            "publish",
            "elementor_library",
            1
        )
    ');

    $stmt->execute(['title' => $template_name]);
    $template_id = $db->lastInsertId();

    // Add Elementor data
    $stmt = $db->prepare('
        INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
        VALUES
            (:id, "_elementor_data", :data),
            (:id, "_elementor_edit_mode", "builder"),
            (:id, "_elementor_template_type", "footer")
    ');

    $stmt->execute([
        'id' => $template_id,
        'data' => $populated_json
    ]);

    return $template_id;
}

// =============================================================================
// DEMONSTRATION
// =============================================================================

echo "\n" . str_repeat("=", 80) . "\n";
echo "ELEMENTOR HEADER & FOOTER INJECTOR - DEMONSTRATION\n";
echo str_repeat("=", 80) . "\n\n";

// Sample contractor data
$contractor_data = [
    'name' => 'Pyramid Heating & Cooling',
    'phone' => '(503) 555-0142',
    'address' => 'Portland, OR',
    'license' => 'CCB #123456',
    'tagline' => 'Portland\'s Heating & Cooling Experts',
    'nav' => ['Home', 'Services', 'About', 'Contact'],
    'cta' => 'Get Free Estimate'
];

// Load templates
echo "Step 1: Loading header and footer templates...\n\n";

$header_template = file_get_contents('elementor-header-template.json');
$footer_template = file_get_contents('elementor-footer-template.json');

if (!$header_template || !$footer_template) {
    die("Error: Template files not found.\n");
}

echo "✓ Header template loaded (" . strlen($header_template) . " bytes)\n";
echo "✓ Footer template loaded (" . strlen($footer_template) . " bytes)\n\n";

// Inject data into header
echo "Step 2: Injecting contractor data into header...\n\n";

$populated_header = inject_header_data($header_template, $contractor_data);
$header_decoded = json_decode($populated_header, true);

echo "Header fields populated:\n";
echo "  • Business name: " . $contractor_data['name'] . "\n";
echo "  • Phone: " . $contractor_data['phone'] . "\n";
echo "  • CTA button: " . $contractor_data['cta'] . "\n";
echo "  • Navigation: " . implode(', ', $contractor_data['nav']) . "\n\n";

// Verify header injection
$header_business_name = $header_decoded[0]['elements'][0]['elements'][1]['settings']['title'] ?? '';
$header_phone = $header_decoded[0]['elements'][2]['elements'][0]['settings']['description_text'] ?? '';
$header_cta = $header_decoded[0]['elements'][2]['elements'][1]['settings']['text'] ?? '';

echo "Verification:\n";
echo "  • Business name in header: " . ($header_business_name === $contractor_data['name'] ? "✓ PASS" : "✗ FAIL") . "\n";
echo "  • Phone in header: " . ($header_phone === $contractor_data['phone'] ? "✓ PASS" : "✗ FAIL") . "\n";
echo "  • CTA text: " . ($header_cta === $contractor_data['cta'] ? "✓ PASS" : "✗ FAIL") . "\n\n";

// Inject data into footer
echo "Step 3: Injecting contractor data into footer...\n\n";

$populated_footer = inject_footer_data($footer_template, $contractor_data);
$footer_decoded = json_decode($populated_footer, true);

echo "Footer fields populated:\n";
echo "  • Business name: " . $contractor_data['name'] . "\n";
echo "  • Tagline: " . $contractor_data['tagline'] . "\n";
echo "  • Phone: " . $contractor_data['phone'] . "\n";
echo "  • Address: " . $contractor_data['address'] . "\n";
echo "  • License: " . $contractor_data['license'] . "\n";
echo "  • Copyright year: " . date('Y') . "\n\n";

// Verify footer injection
$footer_business_name = $footer_decoded[0]['elements'][0]['elements'][0]['settings']['title'] ?? '';
$footer_tagline = $footer_decoded[0]['elements'][0]['elements'][1]['settings']['editor'] ?? '';
$footer_license = $footer_decoded[0]['elements'][0]['elements'][2]['settings']['editor'] ?? '';
$footer_phone = $footer_decoded[0]['elements'][1]['elements'][1]['settings']['description_text'] ?? '';
$footer_address = $footer_decoded[0]['elements'][1]['elements'][2]['settings']['description_text'] ?? '';

echo "Verification:\n";
echo "  • Business name in footer: " . ($footer_business_name === $contractor_data['name'] ? "✓ PASS" : "✗ FAIL") . "\n";
echo "  • Tagline: " . ($footer_tagline === $contractor_data['tagline'] ? "✓ PASS" : "✗ FAIL") . "\n";
echo "  • License: " . ($footer_license === $contractor_data['license'] ? "✓ PASS" : "✗ FAIL") . "\n";
echo "  • Phone: " . ($footer_phone === $contractor_data['phone'] ? "✓ PASS" : "✗ FAIL") . "\n";
echo "  • Address: " . ($footer_address === $contractor_data['address'] ? "✓ PASS" : "✗ FAIL") . "\n\n";

// Show sample output
echo str_repeat("=", 80) . "\n";
echo "SAMPLE OUTPUT (Header - excerpt)\n";
echo str_repeat("=", 80) . "\n";
echo substr($populated_header, 0, 400) . "...\n\n";

echo str_repeat("=", 80) . "\n";
echo "SAMPLE OUTPUT (Footer - excerpt)\n";
echo str_repeat("=", 80) . "\n";
echo substr($populated_footer, 0, 400) . "...\n\n";

// Usage example with database
echo str_repeat("=", 80) . "\n";
echo "USAGE WITH DATABASE\n";
echo str_repeat("=", 80) . "\n";
echo '
// Connect to database
$analyzer = new ElementorAnalyzer();
$analyzer->connectDatabase("localhost", "wordpress_db", "user", "password");
$db = $analyzer->getDb();

// Load templates
$header_template = file_get_contents("elementor-header-template.json");
$footer_template = file_get_contents("elementor-footer-template.json");

// Contractor data
$contractor_data = [
    "name" => "Pyramid Heating & Cooling",
    "phone" => "(503) 555-0142",
    "address" => "Portland, OR",
    "license" => "CCB #123456",
    "tagline" => "Portland\'s Heating & Cooling Experts",
    "nav" => ["Home", "Services", "About", "Contact"],
    "cta" => "Get Free Estimate",
    "logo_url" => "https://example.com/logo.png" // optional
];

// Inject data
$populated_header = inject_header_data($header_template, $contractor_data);
$populated_footer = inject_footer_data($footer_template, $contractor_data);

// Save to database
$header_id = save_header_template($db, $populated_header, "Pyramid Header");
$footer_id = save_footer_template($db, $populated_footer, "Pyramid Footer");

echo "Header template saved with ID: $header_id\n";
echo "Footer template saved with ID: $footer_id\n";

// Activate templates in Elementor Theme Builder
// (Requires manual configuration in WordPress admin or additional code)
';

echo "\n\n✓ Demonstration complete!\n\n";

echo "Files created:\n";
echo "  • elementor-header-template.json - Header template with placeholders\n";
echo "  • elementor-footer-template.json - Footer template with placeholders\n";
echo "  • elementor-header-footer-injector.php - Injection functions\n\n";

echo "Next steps:\n";
echo "  1. Review the generated JSON templates\n";
echo "  2. Customize styling/colors as needed\n";
echo "  3. Use inject_header_data() and inject_footer_data() functions\n";
echo "  4. Save to WordPress database using save_header_template() / save_footer_template()\n";
echo "  5. Activate in Elementor Theme Builder\n\n";
