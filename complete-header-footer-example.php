<?php
/**
 * Complete Example: Create Header & Footer for Multiple Contractors
 *
 * This script demonstrates the complete workflow:
 * 1. Load header and footer templates
 * 2. Inject contractor data for multiple businesses
 * 3. Save to WordPress database
 * 4. Assign templates to specific sites (multisite) or pages
 */

require_once 'elementor-header-footer-injector.php';

// =============================================================================
// CONFIGURATION
// =============================================================================

$db_config = [
    'host' => 'localhost',
    'dbname' => 'wordpress_db',
    'username' => 'wp_user',
    'password' => 'wp_password'
];

// Multiple contractors
$contractors = [
    [
        'name' => 'Pyramid Heating & Cooling',
        'phone' => '(503) 555-0142',
        'address' => 'Portland, OR',
        'license' => 'CCB #123456',
        'tagline' => 'Portland\'s Heating & Cooling Experts',
        'nav' => ['Home', 'Services', 'About', 'Contact'],
        'cta' => 'Get Free Estimate',
        'logo_url' => 'https://example.com/pyramid-logo.png'
    ],
    [
        'name' => 'Summit Plumbing Pro',
        'phone' => '(503) 555-0287',
        'address' => 'Beaverton, OR',
        'license' => 'CCB #789012',
        'tagline' => 'Expert Plumbing Solutions',
        'nav' => ['Home', 'Services', 'About', 'Contact'],
        'cta' => 'Call Now',
        'logo_url' => 'https://example.com/summit-logo.png'
    ],
    [
        'name' => 'Elite Roofing Services',
        'phone' => '(503) 555-0398',
        'address' => 'Lake Oswego, OR',
        'license' => 'CCB #345678',
        'tagline' => 'Quality Roofing Since 2015',
        'nav' => ['Home', 'Services', 'Gallery', 'Contact'],
        'cta' => 'Free Inspection',
        'logo_url' => 'https://example.com/elite-logo.png'
    ]
];

// =============================================================================
// MAIN WORKFLOW
// =============================================================================

echo "================================================================================\n";
echo "BATCH HEADER & FOOTER CREATION FOR MULTIPLE CONTRACTORS\n";
echo "================================================================================\n\n";

try {
    // Step 1: Connect to database
    echo "Step 1: Connecting to WordPress database...\n";
    $analyzer = new ElementorAnalyzer();

    if (!$analyzer->connectDatabase(
        $db_config['host'],
        $db_config['dbname'],
        $db_config['username'],
        $db_config['password']
    )) {
        die("Failed to connect to database.\n");
    }

    $db = $analyzer->getDb();
    echo "✓ Connected successfully\n\n";

    // Step 2: Load templates
    echo "Step 2: Loading header and footer templates...\n";
    $header_template = file_get_contents('elementor-header-template.json');
    $footer_template = file_get_contents('elementor-footer-template.json');

    if (!$header_template || !$footer_template) {
        die("Template files not found.\n");
    }

    echo "✓ Templates loaded\n\n";

    // Step 3: Process each contractor
    echo "Step 3: Creating headers and footers for each contractor...\n";
    echo str_repeat("-", 80) . "\n";

    $db->beginTransaction();

    foreach ($contractors as $index => $contractor) {
        echo "\n[" . ($index + 1) . "/" . count($contractors) . "] Processing: {$contractor['name']}\n";

        // Inject data
        echo "  → Injecting data into header template...\n";
        $populated_header = inject_header_data($header_template, $contractor);

        echo "  → Injecting data into footer template...\n";
        $populated_footer = inject_footer_data($footer_template, $contractor);

        // Save to database
        echo "  → Saving header template to database...\n";
        $header_id = save_header_template(
            $db,
            $populated_header,
            $contractor['name'] . ' - Header'
        );

        echo "  → Saving footer template to database...\n";
        $footer_id = save_footer_template(
            $db,
            $populated_footer,
            $contractor['name'] . ' - Footer'
        );

        echo "  ✓ Header saved (ID: $header_id)\n";
        echo "  ✓ Footer saved (ID: $footer_id)\n";

        // Optional: Store IDs for later use
        $contractors[$index]['header_id'] = $header_id;
        $contractors[$index]['footer_id'] = $footer_id;
    }

    $db->commit();

    echo "\n" . str_repeat("-", 80) . "\n";
    echo "\n✓ All templates created successfully!\n\n";

    // Summary
    echo "Summary:\n";
    echo "  Total contractors processed: " . count($contractors) . "\n";
    echo "  Headers created: " . count($contractors) . "\n";
    echo "  Footers created: " . count($contractors) . "\n\n";

    echo "Template IDs:\n";
    foreach ($contractors as $contractor) {
        echo "  • {$contractor['name']}:\n";
        echo "    - Header ID: {$contractor['header_id']}\n";
        echo "    - Footer ID: {$contractor['footer_id']}\n";
    }
    echo "\n";

    // Next steps
    echo "Next steps:\n";
    echo "  1. Log into WordPress admin\n";
    echo "  2. Go to Templates → Theme Builder\n";
    echo "  3. Find your header/footer templates\n";
    echo "  4. Set display conditions for each template\n";
    echo "  5. Publish the templates\n\n";

    echo "Advanced: Auto-assign templates to pages\n";
    echo "  - Use Elementor's API or direct database updates\n";
    echo "  - Set template conditions based on page/post type\n";
    echo "  - For multisite: assign templates to specific subsites\n\n";

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

// =============================================================================
// STANDALONE USAGE (NO DATABASE)
// =============================================================================

echo "================================================================================\n";
echo "STANDALONE USAGE (Generate JSON files without database)\n";
echo "================================================================================\n\n";

// For testing or external use
foreach ($contractors as $contractor) {
    $header_json = inject_header_data($header_template, $contractor);
    $footer_json = inject_footer_data($footer_template, $contractor);

    // Save to files
    $safe_name = preg_replace('/[^a-z0-9]+/i', '-', strtolower($contractor['name']));

    file_put_contents("output-{$safe_name}-header.json", $header_json);
    file_put_contents("output-{$safe_name}-footer.json", $footer_json);

    echo "✓ Generated: output-{$safe_name}-header.json\n";
    echo "✓ Generated: output-{$safe_name}-footer.json\n";
}

echo "\nJSON files can be imported manually into Elementor.\n\n";
