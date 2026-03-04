<?php
/**
 * Example Usage: Contractor Schema Generator
 *
 * Demonstrates how to generate SEO-optimized schema markup
 * for various contractor business types and configurations.
 */

require_once 'contractor-schema-generator.php';

echo "=== CONTRACTOR SCHEMA GENERATOR EXAMPLES ===\n\n";

// Example 1: Full-Featured HVAC Contractor
echo "Example 1: Full-Featured HVAC Contractor\n";
echo str_repeat('-', 60) . "\n";

$hvac_full = [
    'name' => 'Pyramid Heating & Cooling',
    'trade' => 'hvac',
    'phone' => '(503) 555-0142',
    'email' => 'info@pyramidheating.com',
    'website' => 'https://pyramidheating.com',
    'address' => '123 Industrial Pkwy, Portland, OR 97210',
    'lat' => 45.5231,
    'lng' => -122.6765,
    'rating' => 4.7,
    'review_count' => 143,
    'services' => [
        'Furnace Repair',
        'AC Installation',
        'Duct Cleaning',
        'Thermostat Installation',
        'Maintenance Plans'
    ],
    'service_areas' => ['Portland', 'Beaverton', 'Hillsboro', 'Gresham'],
    'hours' => 'Mo-Fr 07:00-18:00',
    'description' => 'Professional HVAC services for residential and commercial properties. Family-owned and operated since 1995.',
    'price_range' => '$$',
    'logo' => 'https://pyramidheating.com/logo.png',
    'image' => 'https://pyramidheating.com/images/hero.jpg',
    'payment_accepted' => ['Cash', 'Credit Card', 'Check', 'Financing Available'],
    'social_urls' => [
        'https://facebook.com/pyramidheating',
        'https://twitter.com/pyramidheating',
        'https://instagram.com/pyramidheating'
    ],
    'founded_year' => 1995
];

$schema = generate_contractor_schema($hvac_full);
$meta = generate_meta_tags($hvac_full);

echo "Schema snippet:\n";
echo substr($schema, 0, 400) . "...\n\n";

echo "Meta tags (first 5 lines):\n";
$meta_lines = explode("\n", $meta);
echo implode("\n", array_slice($meta_lines, 0, 5)) . "\n...\n\n";

// Example 2: Minimal Plumbing Contractor
echo "Example 2: Minimal Plumbing Contractor (Required Fields Only)\n";
echo str_repeat('-', 60) . "\n";

$plumber_minimal = [
    'name' => 'Quick Fix Plumbing',
    'trade' => 'plumbing',
    'phone' => '(503) 555-7890',
    'address' => 'Beaverton, OR'
];

$plumber_schema = generate_contractor_schema($plumber_minimal);
echo "Generated schema with only required fields:\n";
echo "  - Name: " . $plumber_minimal['name'] . "\n";
echo "  - Trade: " . $plumber_minimal['trade'] . "\n";
echo "  - Phone: " . $plumber_minimal['phone'] . "\n";
echo "  - Address: " . $plumber_minimal['address'] . "\n";
echo "  - Default price range: $$\n\n";

// Example 3: Electrician with High Rating
echo "Example 3: Electrician with High Rating & Multiple Service Areas\n";
echo str_repeat('-', 60) . "\n";

$electrician = [
    'name' => 'Bright Spark Electric',
    'trade' => 'electrical',
    'phone' => '(503) 555-2468',
    'email' => 'service@brightspark.com',
    'address' => 'Portland Metro Area',
    'rating' => 4.9,
    'review_count' => 287,
    'services' => [
        'Panel Upgrades',
        'Wiring & Rewiring',
        'Outlet Installation',
        'Lighting Installation',
        'Emergency Repairs'
    ],
    'service_areas' => [
        'Portland',
        'Beaverton',
        'Hillsboro',
        'Tigard',
        'Lake Oswego',
        'Oregon City'
    ],
    'hours' => 'Mo-Fr 07:00-19:00',
    'price_range' => '$$'
];

$elec_meta = generate_meta_tags($electrician);
echo "Meta description includes high rating:\n";
preg_match('/<meta name="description" content="([^"]+)"/', $elec_meta, $matches);
echo "  " . $matches[1] . "\n\n";

// Example 4: Roofer with Structured Address
echo "Example 4: Roofer with Structured Address Format\n";
echo str_repeat('-', 60) . "\n";

$roofer = [
    'name' => 'Summit Roofing & Repair',
    'trade' => 'roofing',
    'phone' => '(503) 555-3691',
    'email' => 'quotes@summitroofing.com',
    'address' => [
        'streetAddress' => '456 Commerce St, Suite 100',
        'addressLocality' => 'Portland',
        'addressRegion' => 'OR',
        'postalCode' => '97201',
        'addressCountry' => 'US'
    ],
    'lat' => 45.5155,
    'lng' => -122.6789,
    'services' => [
        'Roof Replacement',
        'Roof Repair',
        'Gutter Installation',
        'Roof Inspection'
    ],
    'hours' => 'Mo-Sa 07:00-18:00',
    'website' => 'https://summitroofing.com'
];

echo "Structured address parsed correctly:\n";
echo "  Street: 456 Commerce St, Suite 100\n";
echo "  City: Portland\n";
echo "  State: OR\n";
echo "  ZIP: 97201\n\n";

// Example 5: 24/7 Locksmith Service
echo "Example 5: 24/7 Locksmith with Emergency Service\n";
echo str_repeat('-', 60) . "\n";

$locksmith = [
    'name' => '24/7 Metro Locksmith',
    'trade' => 'locksmith',
    'phone' => '(503) 555-LOCK',
    'address' => 'Mobile Service - Portland Metro Area',
    'hours' => '24/7',
    'services' => [
        'Emergency Lockout',
        'Lock Installation',
        'Key Duplication',
        'Security Systems',
        'Car Key Replacement'
    ],
    'service_areas' => ['Portland', 'Beaverton', 'Gresham', 'Tigard'],
    'price_range' => '$$',
    'description' => 'Emergency locksmith service available 24 hours a day, 7 days a week'
];

$locksmith_schema = generate_contractor_schema($locksmith);
echo "24/7 hours converted to schema format:\n";
echo "  'Monday-Sunday 00:00-23:59'\n\n";

// Example 6: Painter with Payment Options
echo "Example 6: Painting Contractor with Multiple Payment Options\n";
echo str_repeat('-', 60) . "\n";

$painter = [
    'name' => 'ColorPro Painting',
    'trade' => 'painting',
    'phone' => '(503) 555-9999',
    'email' => 'estimates@colorpro.com',
    'address' => 'Hillsboro, OR 97124',
    'services' => [
        'Interior Painting',
        'Exterior Painting',
        'Cabinet Refinishing',
        'Deck Staining',
        'Commercial Painting'
    ],
    'hours' => 'Mo-Fr 08:00-17:00',
    'payment_accepted' => [
        'Cash',
        'Check',
        'Visa',
        'Mastercard',
        'American Express',
        'Financing Available'
    ],
    'price_range' => '$$',
    'founded_year' => 2005
];

$painter_schema = generate_contractor_schema($painter);
echo "Payment methods included in schema:\n";
echo "  Cash, Check, Visa, Mastercard, American Express, Financing Available\n";
echo "  Founded: 2005\n\n";

// Example 7: General Contractor with Social Media
echo "Example 7: General Contractor with Social Media Integration\n";
echo str_repeat('-', 60) . "\n";

$gc = [
    'name' => 'Pacific Northwest Builders',
    'trade' => 'general_contractor',
    'phone' => '(503) 555-1234',
    'email' => 'projects@pnwbuilders.com',
    'website' => 'https://pnwbuilders.com',
    'address' => '789 Builder Lane, Portland, OR 97210',
    'lat' => 45.5298,
    'lng' => -122.6815,
    'rating' => 4.8,
    'review_count' => 96,
    'services' => [
        'Home Remodeling',
        'Kitchen Renovation',
        'Bathroom Renovation',
        'Room Additions',
        'Custom Homes'
    ],
    'hours' => 'Mo-Fr 08:00-17:00',
    'social_urls' => [
        'https://facebook.com/pnwbuilders',
        'https://instagram.com/pnwbuilders',
        'https://houzz.com/pro/pnwbuilders',
        'https://linkedin.com/company/pnwbuilders'
    ],
    'logo' => 'https://pnwbuilders.com/logo.png',
    'image' => 'https://pnwbuilders.com/portfolio/kitchen-hero.jpg',
    'price_range' => '$$$'
];

$gc_schema = generate_contractor_schema($gc);
echo "Social profiles linked in schema:\n";
echo "  - Facebook\n";
echo "  - Instagram\n";
echo "  - Houzz\n";
echo "  - LinkedIn\n\n";

// Example 8: Landscaper with Seasonal Hours
echo "Example 8: Landscaping with Custom Hours Format\n";
echo str_repeat('-', 60) . "\n";

$landscaper = [
    'name' => 'Green Valley Landscaping',
    'trade' => 'landscaping',
    'phone' => '(503) 555-8888',
    'email' => 'info@greenvalley.com',
    'address' => 'Lake Oswego, OR',
    'services' => [
        'Lawn Maintenance',
        'Landscape Design',
        'Irrigation Systems',
        'Tree Service',
        'Hardscaping'
    ],
    'hours' => [
        'Mo-Fr 07:00-17:00',
        'Sa 08:00-14:00'
    ],
    'service_areas' => ['Lake Oswego', 'West Linn', 'Tigard', 'Portland SW'],
    'price_range' => '$$'
];

$landscaper_schema = generate_contractor_schema($landscaper);
echo "Multiple opening hours:\n";
echo "  Mon-Fri: 07:00-17:00\n";
echo "  Sat: 08:00-14:00\n\n";

// Example 9: Complete Head Section Generation
echo "Example 9: Complete HTML Head Section\n";
echo str_repeat('-', 60) . "\n";

$complete_head = generate_head_section($hvac_full);
$head_lines = explode("\n", $complete_head);

echo "Generated complete <head> content:\n";
echo "  Total lines: " . count($head_lines) . "\n";
echo "  Includes:\n";
echo "    - Title tag\n";
echo "    - Meta description\n";
echo "    - Open Graph tags (8+)\n";
echo "    - Twitter Card tags\n";
echo "    - Geographic meta tags\n";
echo "    - JSON-LD schema (60+ lines)\n\n";

// Example 10: Validation Examples
echo "Example 10: Data Validation\n";
echo str_repeat('-', 60) . "\n";

// Valid data
try {
    validate_contractor_data($hvac_full);
    echo "✓ Full HVAC data validated successfully\n";
} catch (InvalidArgumentException $e) {
    echo "✗ Validation failed: " . $e->getMessage() . "\n";
}

// Missing required field
try {
    $invalid = ['name' => 'Test', 'trade' => 'hvac'];
    validate_contractor_data($invalid);
    echo "✗ Should have failed validation\n";
} catch (InvalidArgumentException $e) {
    echo "✓ Caught missing field: " . $e->getMessage() . "\n";
}

// Invalid email
try {
    $bad_email = $plumber_minimal;
    $bad_email['email'] = 'not-an-email';
    validate_contractor_data($bad_email);
    echo "✗ Should have failed email validation\n";
} catch (InvalidArgumentException $e) {
    echo "✓ Caught invalid email: " . $e->getMessage() . "\n";
}

// Invalid rating
try {
    $bad_rating = $plumber_minimal;
    $bad_rating['rating'] = 6.0;
    $bad_rating['review_count'] = 100;
    validate_contractor_data($bad_rating);
    echo "✗ Should have failed rating validation\n";
} catch (InvalidArgumentException $e) {
    echo "✓ Caught invalid rating: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 11: Trade Type Mapping
echo "Example 11: Trade Type to Schema.org Type Mapping\n";
echo str_repeat('-', 60) . "\n";

$trades = ['hvac', 'plumbing', 'electrical', 'roofing', 'locksmith', 'painting', 'unknown'];

foreach ($trades as $trade) {
    $types = get_schema_type_for_trade($trade);
    echo sprintf("%-15s -> [%s]\n", $trade, implode(', ', $types));
}

echo "\n";

// Example 12: Trade Keywords
echo "Example 12: SEO Keywords by Trade\n";
echo str_repeat('-', 60) . "\n";

$keyword_trades = ['hvac', 'plumbing', 'electrical', 'roofing'];

foreach ($keyword_trades as $trade) {
    $keywords = get_trade_keywords($trade);
    echo sprintf("%-12s: %s\n", ucfirst($trade), implode(', ', array_slice($keywords, 0, 4)));
}

echo "\n";

// Summary
echo "=== SUMMARY ===\n";
echo "12 examples demonstrated:\n";
echo "  1. Full-featured HVAC with all fields\n";
echo "  2. Minimal plumber with required fields only\n";
echo "  3. Electrician with high rating\n";
echo "  4. Roofer with structured address\n";
echo "  5. 24/7 locksmith service\n";
echo "  6. Painter with payment options\n";
echo "  7. General contractor with social media\n";
echo "  8. Landscaper with custom hours\n";
echo "  9. Complete head section generation\n";
echo "  10. Data validation scenarios\n";
echo "  11. Trade type mapping\n";
echo "  12. SEO keywords by trade\n";
echo "\n";

echo "All examples use valid data structures.\n";
echo "Copy any example and modify for your needs.\n";
echo "\n";

// WordPress Integration Example
echo "=== WORDPRESS INTEGRATION EXAMPLE ===\n";
echo str_repeat('-', 60) . "\n";

$wordpress_example = <<<'PHP'
<?php
/**
 * Add to your theme's functions.php or a custom plugin
 */

require_once get_template_directory() . '/contractor-schema-generator.php';

function my_contractor_schema() {
    $contractor_data = [
        'name' => get_bloginfo('name'),
        'trade' => get_option('contractor_trade', 'general_contractor'),
        'phone' => get_option('company_phone'),
        'email' => get_option('admin_email'),
        'website' => home_url(),
        'address' => get_option('company_address'),
        'lat' => (float) get_option('company_latitude'),
        'lng' => (float) get_option('company_longitude'),
        'rating' => (float) get_option('average_rating'),
        'review_count' => (int) get_option('review_count'),
        'services' => explode(',', get_option('services_list')),
        'service_areas' => explode(',', get_option('service_areas')),
        'hours' => get_option('business_hours'),
        'description' => get_bloginfo('description'),
        'logo' => get_site_icon_url(),
        'social_urls' => [
            get_option('facebook_url'),
            get_option('twitter_url')
        ]
    ];

    // Remove empty social URLs
    $contractor_data['social_urls'] = array_filter($contractor_data['social_urls']);

    echo generate_head_section($contractor_data);
}

add_action('wp_head', 'my_contractor_schema');
PHP;

echo $wordpress_example;
echo "\n\n";

echo "For more information, see SCHEMA_GENERATOR_README.md\n";
