<?php
/**
 * Example: Complete workflow for automated Elementor site generation
 *
 * This demonstrates how to:
 * 1. Connect to a WordPress database
 * 2. Fetch an Elementor template
 * 3. Inject contractor data
 * 4. Create a new page or update existing one
 */

require_once 'elementor-analyzer.php';

// =============================================================================
// CONFIGURATION
// =============================================================================

// Database configuration (update with your credentials)
$db_config = [
    'host' => 'localhost',
    'dbname' => 'wordpress_db',
    'username' => 'wp_user',
    'password' => 'wp_password'
];

// Template page ID (the Elementor page you want to use as template)
$template_post_id = 100;

// Target page IDs (where you want to create new contractor sites)
$target_post_ids = [200, 201, 202];

// Multiple contractors data
$contractors = [
    [
        'name' => 'Cascade Roofing Solutions',
        'phone' => '(503) 555-0187',
        'email' => 'info@cascaderoofing.com',
        'address' => '123 Main St, Portland OR 97201',
        'hero_headline' => 'Portland\'s Most Trusted Roofer',
        'hero_sub' => 'Fast estimates. Fair prices. Quality work.',
        'about' => 'Family owned since 2019, we bring 15 years of combined roofing experience to every job. Our team specializes in residential and commercial roofing with a focus on quality materials and exceptional customer service.',
        'services' => ['Roof Repairs', 'New Installations', 'Gutter Systems', 'Emergency Services'],
        'service_area' => 'Portland Metro Area'
    ],
    [
        'name' => 'Summit Plumbing & Heating',
        'phone' => '(503) 555-0234',
        'email' => 'contact@summitplumbing.com',
        'address' => '456 Oak Ave, Beaverton OR 97005',
        'hero_headline' => 'Expert Plumbing When You Need It',
        'hero_sub' => '24/7 emergency service available.',
        'about' => 'Summit Plumbing has been serving Oregon families since 2015. We pride ourselves on transparent pricing, fast response times, and work that lasts.',
        'services' => ['Plumbing Repairs', 'Water Heaters', 'Drain Cleaning', 'Remodeling'],
        'service_area' => 'Portland & Beaverton'
    ],
    [
        'name' => 'Elite HVAC Solutions',
        'phone' => '(971) 555-0899',
        'email' => 'service@elitehvac.com',
        'address' => '789 Cedar St, Lake Oswego OR 97034',
        'hero_headline' => 'Stay Comfortable Year-Round',
        'hero_sub' => 'Professional HVAC installation and maintenance.',
        'about' => 'Elite HVAC Solutions provides top-tier heating and cooling services throughout the Portland area. Our certified technicians ensure your home stays comfortable in every season.',
        'services' => ['AC Installation', 'Furnace Repair', 'Maintenance Plans', 'Air Quality'],
        'service_area' => 'Greater Portland Area'
    ]
];

// =============================================================================
// WORKFLOW
// =============================================================================

echo "================================================================================\n";
echo "AUTOMATED ELEMENTOR SITE GENERATION\n";
echo "================================================================================\n\n";

try {
    // Step 1: Initialize analyzer and connect to database
    echo "Step 1: Connecting to WordPress database...\n";
    $analyzer = new ElementorAnalyzer();

    if (!$analyzer->connectDatabase(
        $db_config['host'],
        $db_config['dbname'],
        $db_config['username'],
        $db_config['password']
    )) {
        die("Failed to connect to database. Check your credentials.\n");
    }
    echo "✓ Connected successfully\n\n";

    // Step 2: Fetch template
    echo "Step 2: Fetching Elementor template (post_id: $template_post_id)...\n";
    $template_pages = $analyzer->fetchElementorData($template_post_id, 1);

    if (empty($template_pages)) {
        die("Template page not found or doesn't have Elementor data.\n");
    }

    $template_json = $template_pages[0]['meta_value'];
    echo "✓ Template fetched (" . strlen($template_json) . " bytes)\n\n";

    // Step 3: Optional - Analyze template structure
    echo "Step 3: Analyzing template structure...\n";
    $analysis = $analyzer->analyzeStructure($template_json);
    echo "✓ Found " . count($analysis['structure_map']) . " element types\n";
    echo "✓ Found " . count($analysis['editable_paths']) . " editable fields\n\n";

    // Optional: Display full analysis
    // $analyzer->generateReport();

    // Step 4: Process each contractor
    echo "Step 4: Processing contractors...\n";
    echo str_repeat("-", 80) . "\n";

    foreach ($contractors as $index => $contractor) {
        $target_post_id = $target_post_ids[$index] ?? null;

        if (!$target_post_id) {
            echo "⚠ Skipping contractor '{$contractor['name']}' - no target post ID\n";
            continue;
        }

        echo "\nContractor: {$contractor['name']}\n";
        echo "Target Post ID: $target_post_id\n";

        // Inject contractor data
        echo "  → Injecting data into template...\n";
        $populated_json = inject_contractor_data($template_json, $contractor);

        // Update database
        echo "  → Updating database...\n";
        $db = $analyzer->getDb();

        // Check if _elementor_data already exists for this post
        $check_stmt = $db->prepare('
            SELECT COUNT(*) as count
            FROM wp_postmeta
            WHERE post_id = :id AND meta_key = "_elementor_data"
        ');
        $check_stmt->execute(['id' => $target_post_id]);
        $exists = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;

        if ($exists) {
            // Update existing
            $stmt = $db->prepare('
                UPDATE wp_postmeta
                SET meta_value = :value
                WHERE post_id = :id AND meta_key = "_elementor_data"
            ');
        } else {
            // Insert new
            $stmt = $db->prepare('
                INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
                VALUES (:id, "_elementor_data", :value)
            ');
        }

        $stmt->execute(['value' => $populated_json, 'id' => $target_post_id]);

        echo "  ✓ Successfully " . ($exists ? 'updated' : 'created') . " page\n";

        // Also update the page title
        $title_stmt = $db->prepare('
            UPDATE wp_posts
            SET post_title = :title
            WHERE ID = :id
        ');
        $title_stmt->execute([
            'title' => $contractor['name'] . ' - Home',
            'id' => $target_post_id
        ]);

        echo "  ✓ Updated page title\n";
    }

    echo "\n" . str_repeat("-", 80) . "\n";
    echo "\n✓ All contractors processed successfully!\n\n";

    echo "Summary:\n";
    echo "  - Template used: Post ID $template_post_id\n";
    echo "  - Contractors processed: " . count($contractors) . "\n";
    echo "  - Pages updated: " . count($target_post_ids) . "\n\n";

    echo "Next steps:\n";
    echo "  1. Log into WordPress admin\n";
    echo "  2. Navigate to Pages\n";
    echo "  3. View your newly created/updated contractor pages\n";
    echo "  4. Publish when ready\n\n";

} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

// =============================================================================
// HELPER FUNCTION: Add getDb() method to ElementorAnalyzer class
// =============================================================================

// Note: You'll need to add this method to the ElementorAnalyzer class:
/*
public function getDb() {
    return $this->db;
}
*/
