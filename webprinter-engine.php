<?php
/**
 * WebPrinter Engine - Master Contractor Website Generator
 *
 * One function call creates a complete 7-page contractor website with:
 * - Header/Footer (global)
 * - Home page
 * - About page
 * - Services page
 * - Estimate page (InstaBid integration)
 * - Contact page
 * - SEO Schema markup
 *
 * @package WebPrinter
 * @version 1.0.0
 */

require_once 'elementor-header-footer-injector.php';
require_once 'elementor-about-injector.php';
require_once 'elementor-services-injector.php';
require_once 'elementor-estimate-injector.php';
require_once 'elementor-contact-injector.php';
require_once 'contractor-schema-generator.php';

/**
 * WordPress REST API Client
 */
class WordPressAPI {
    private $site_url;
    private $username;
    private $app_password;

    public function __construct($site_url, $username, $app_password) {
        $this->site_url = rtrim($site_url, '/');
        $this->username = $username;
        $this->app_password = $app_password;
    }

    /**
     * Make authenticated API request
     */
    private function request($endpoint, $method = 'GET', $data = null) {
        $url = $this->site_url . '/wp-json/wp/v2/' . ltrim($endpoint, '/');

        $headers = [
            'Authorization: Basic ' . base64_encode($this->username . ':' . $this->app_password),
            'Content-Type: application/json'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code >= 400) {
            throw new Exception("WordPress API error: HTTP $http_code - $response");
        }

        return json_decode($response, true);
    }

    /**
     * Create or update a page
     */
    public function createPage($title, $content, $template = 'elementor_canvas', $status = 'publish') {
        // Check if page exists
        $existing = $this->request('pages?search=' . urlencode($title));

        $page_data = [
            'title' => $title,
            'content' => $content,
            'status' => $status,
            'template' => $template,
            'meta' => [
                '_elementor_edit_mode' => 'builder',
                '_elementor_template_type' => 'wp-page',
                '_elementor_version' => '3.0.0'
            ]
        ];

        if (!empty($existing)) {
            // Update existing page
            $page_id = $existing[0]['id'];
            return $this->request("pages/$page_id", 'PUT', $page_data);
        } else {
            // Create new page
            return $this->request('pages', 'POST', $page_data);
        }
    }

    /**
     * Set Elementor data for a page
     */
    public function setElementorData($page_id, $elementor_json) {
        return $this->request("pages/$page_id", 'PUT', [
            'meta' => [
                '_elementor_data' => $elementor_json
            ]
        ]);
    }

    /**
     * Create Elementor template
     */
    public function createElementorTemplate($title, $type, $elementor_json) {
        $template_data = [
            'title' => $title,
            'type' => 'elementor_library',
            'status' => 'publish',
            'meta' => [
                '_elementor_data' => $elementor_json,
                '_elementor_template_type' => $type,
                '_elementor_edit_mode' => 'builder'
            ]
        ];

        // Check for existing template
        $existing = $this->request('elementor_library?search=' . urlencode($title));

        if (!empty($existing)) {
            $template_id = $existing[0]['id'];
            return $this->request("elementor_library/$template_id", 'PUT', $template_data);
        } else {
            return $this->request('elementor_library', 'POST', $template_data);
        }
    }

    /**
     * Set theme builder conditions
     */
    public function setThemeBuilderConditions($template_id, $conditions) {
        return $this->request("elementor_library/$template_id", 'PUT', [
            'meta' => [
                '_elementor_conditions' => json_encode($conditions)
            ]
        ]);
    }
}

/**
 * Main WebPrinter function - Creates complete contractor website
 *
 * @param array $contractor_data {
 *     Complete contractor business data
 *
 *     Required fields:
 *     @type string $company_name        Business name
 *     @type string $trade               Trade type (hvac, plumbing, etc.)
 *     @type string $phone               Phone number
 *     @type string $email               Email address
 *     @type string $address             Physical address
 *     @type string $city                City
 *     @type string $state               State
 *     @type string $zip                 ZIP code
 *
 *     Optional fields:
 *     @type float  $lat                 Latitude
 *     @type float  $lng                 Longitude
 *     @type float  $rating              Average rating (0-5)
 *     @type int    $review_count        Number of reviews
 *     @type array  $services            Service list
 *     @type array  $service_areas       Cities/areas served
 *     @type string $hours               Business hours
 *     @type string $website             Website URL
 *     @type string $logo_url            Logo URL
 *     @type array  $social_media        Social media URLs
 *     @type string $about_story         Company story
 *     @type array  $team_members        Team member data
 *     @type string $instabid_key        InstaBid API key
 * }
 * @param array $options {
 *     Optional configuration
 *
 *     @type string $template            Template style (default, modern, professional)
 *     @type string $wp_site_url         WordPress site URL
 *     @type string $wp_username         WordPress username
 *     @type string $wp_app_password     WordPress application password
 *     @type bool   $create_pages        Whether to create WordPress pages (default: false)
 *     @type bool   $set_theme_builder   Whether to set Elementor theme builder (default: false)
 *     @type string $color_scheme        Color scheme (blue, green, orange, custom)
 *     @type array  $custom_colors       Custom color palette if color_scheme is 'custom'
 * }
 * @return array {
 *     Website generation results
 *
 *     @type string $site_url            WordPress site URL
 *     @type array  $page_urls           Array of created page URLs
 *     @type array  $templates           Generated Elementor templates
 *     @type string $schema              Generated schema markup
 *     @type array  $meta_tags           Generated meta tags
 *     @type bool   $success             Whether generation succeeded
 *     @type array  $errors              Any errors encountered
 * }
 */
function print_contractor_site($contractor_data, $options = []) {
    $results = [
        'success' => false,
        'site_url' => '',
        'page_urls' => [],
        'templates' => [],
        'schema' => '',
        'meta_tags' => '',
        'errors' => []
    ];

    try {
        // Set defaults
        $options = array_merge([
            'template' => 'default',
            'create_pages' => false,
            'set_theme_builder' => false,
            'color_scheme' => 'blue',
            'wp_site_url' => '',
            'wp_username' => '',
            'wp_app_password' => ''
        ], $options);

        // Validate required data
        $required = ['company_name', 'trade', 'phone', 'email', 'address', 'city', 'state', 'zip'];
        foreach ($required as $field) {
            if (empty($contractor_data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Initialize WordPress API if credentials provided
        $wp_api = null;
        if ($options['create_pages'] && !empty($options['wp_site_url'])) {
            if (empty($options['wp_username']) || empty($options['wp_app_password'])) {
                throw new Exception("WordPress credentials required when create_pages is true");
            }

            $wp_api = new WordPressAPI(
                $options['wp_site_url'],
                $options['wp_username'],
                $options['wp_app_password']
            );

            $results['site_url'] = $options['wp_site_url'];
        }

        // Step 1: Generate Header Template
        echo "Generating header template...\n";
        $header_json = inject_header_data(
            file_get_contents('elementor-header-template.json'),
            [
                'name' => $contractor_data['company_name'],
                'phone' => $contractor_data['phone'],
                'email' => $contractor_data['email'],
                'logo_url' => $contractor_data['logo_url'] ?? '',
                'tagline' => $contractor_data['tagline'] ?? "Professional {$contractor_data['trade']} Services"
            ]
        );
        $results['templates']['header'] = $header_json;

        // Step 2: Generate Footer Template
        echo "Generating footer template...\n";
        $footer_json = inject_footer_data(
            file_get_contents('elementor-footer-template.json'),
            [
                'name' => $contractor_data['company_name'],
                'phone' => $contractor_data['phone'],
                'email' => $contractor_data['email'],
                'address' => $contractor_data['address'],
                'city' => $contractor_data['city'],
                'state' => $contractor_data['state'],
                'zip' => $contractor_data['zip'],
                'facebook' => $contractor_data['social_media']['facebook'] ?? '',
                'twitter' => $contractor_data['social_media']['twitter'] ?? '',
                'instagram' => $contractor_data['social_media']['instagram'] ?? '',
                'linkedin' => $contractor_data['social_media']['linkedin'] ?? ''
            ]
        );
        $results['templates']['footer'] = $footer_json;

        // Step 3: Generate Home Page (using about template as base)
        echo "Generating home page...\n";
        $home_json = inject_about_data(
            file_get_contents('elementor-about-template.json'),
            [
                'name' => $contractor_data['company_name'],
                'hero_headline' => $contractor_data['home_headline'] ?? "Your Trusted {$contractor_data['trade']} Experts",
                'hero_sub' => $contractor_data['home_subheadline'] ?? "Serving {$contractor_data['city']} and surrounding areas",
                'about' => $contractor_data['home_story'] ?? "Quality service you can trust.",
                'years_in_business' => $contractor_data['years_in_business'] ?? 10,
                'phone' => $contractor_data['phone'],
                'address' => "{$contractor_data['city']}, {$contractor_data['state']}"
            ]
        );
        $results['templates']['home'] = $home_json;

        // Step 4: Generate About Page
        echo "Generating about page...\n";
        $about_json = inject_about_data(
            file_get_contents('elementor-about-template.json'),
            [
                'name' => $contractor_data['company_name'],
                'hero_headline' => $contractor_data['about_headline'] ?? "About {$contractor_data['company_name']}",
                'hero_sub' => $contractor_data['about_subheadline'] ?? "Experience. Quality. Trust.",
                'about' => $contractor_data['about_story'] ?? "Our story of excellence.",
                'years_in_business' => $contractor_data['years_in_business'] ?? 10,
                'phone' => $contractor_data['phone'],
                'address' => "{$contractor_data['city']}, {$contractor_data['state']}"
            ]
        );
        $results['templates']['about'] = $about_json;

        // Step 5: Generate Services Page
        echo "Generating services page...\n";
        $services_json = inject_services_data(
            file_get_contents('elementor-services-template.json'),
            [
                'name' => $contractor_data['company_name'],
                'trade' => ucfirst($contractor_data['trade']),
                'tagline' => $contractor_data['tagline'] ?? "Professional {$contractor_data['trade']} Services",
                'services' => $contractor_data['services'] ?? [],
                'service_areas' => $contractor_data['service_areas'] ?? [$contractor_data['city']],
                'steps' => $contractor_data['steps'] ?? [
                    'Contact us for a free consultation',
                    'We provide a detailed estimate',
                    'Expert service completion'
                ]
            ]
        );
        $results['templates']['services'] = $services_json;

        // Step 6: Generate Estimate Page
        echo "Generating estimate page...\n";
        $estimate_json = inject_estimate_data(
            file_get_contents('elementor-estimate-template.json'),
            [
                'name' => $contractor_data['company_name'],
                'phone' => $contractor_data['phone'],
                'instabid_embed' => $contractor_data['instabid_embed'] ?? '<!-- INSTABID_EMBED_CODE_HERE -->',
                'response_time' => $contractor_data['response_time'] ?? '24 hours',
                'trust_signals' => $contractor_data['trust_signals'] ?? ['Licensed & Insured', 'Free Estimates', 'Fast Response', '100% Satisfaction Guaranteed'],
                'faqs' => $contractor_data['faqs'] ?? [
                    ['q' => 'How quickly can you provide an estimate?', 'a' => 'We typically provide estimates within 24 hours.'],
                    ['q' => 'Is the estimate free?', 'a' => 'Yes, all our estimates are completely free with no obligation.'],
                    ['q' => 'What areas do you serve?', 'a' => 'We proudly serve the entire local area and surrounding communities.']
                ]
            ]
        );
        $results['templates']['estimate'] = $estimate_json;

        // Step 7: Generate Contact Page
        echo "Generating contact page...\n";
        $contact_json = inject_contact_data(
            file_get_contents('elementor-contact-template.json'),
            [
                'name' => $contractor_data['company_name'],
                'phone' => $contractor_data['phone'],
                'email' => $contractor_data['email'],
                'address' => $contractor_data['address'],
                'hours' => $contractor_data['hours'] ?? 'Mon-Fri: 8am-5pm',
                'emergency' => $contractor_data['emergency'] ?? false,
                'emergency_phone' => $contractor_data['emergency_phone'] ?? $contractor_data['phone']
            ]
        );
        $results['templates']['contact'] = $contact_json;

        // Step 8: Generate Schema Markup
        echo "Generating schema markup...\n";
        $schema_data = [
            'name' => $contractor_data['company_name'],
            'trade' => $contractor_data['trade'],
            'phone' => $contractor_data['phone'],
            'email' => $contractor_data['email'],
            'address' => "{$contractor_data['address']}, {$contractor_data['city']}, {$contractor_data['state']} {$contractor_data['zip']}",
            'website' => $contractor_data['website'] ?? $options['wp_site_url']
        ];

        // Add optional schema fields
        if (isset($contractor_data['lat'])) $schema_data['lat'] = $contractor_data['lat'];
        if (isset($contractor_data['lng'])) $schema_data['lng'] = $contractor_data['lng'];
        if (isset($contractor_data['rating'])) $schema_data['rating'] = $contractor_data['rating'];
        if (isset($contractor_data['review_count'])) $schema_data['review_count'] = $contractor_data['review_count'];
        if (isset($contractor_data['services'])) {
            $schema_data['services'] = array_column($contractor_data['services'], 'name');
        }
        if (isset($contractor_data['service_areas'])) $schema_data['service_areas'] = $contractor_data['service_areas'];
        if (isset($contractor_data['hours'])) $schema_data['hours'] = $contractor_data['hours'];
        if (isset($contractor_data['logo_url'])) $schema_data['logo'] = $contractor_data['logo_url'];
        if (isset($contractor_data['price_range'])) $schema_data['price_range'] = $contractor_data['price_range'];
        if (isset($contractor_data['social_media'])) $schema_data['social_urls'] = array_values($contractor_data['social_media']);

        $results['schema'] = generate_contractor_schema($schema_data);
        $results['meta_tags'] = generate_meta_tags($schema_data);

        // Step 9: Create WordPress Pages (if enabled)
        if ($options['create_pages'] && $wp_api !== null) {
            echo "Creating WordPress pages...\n";

            $pages = [
                'home' => ['title' => 'Home', 'json' => $home_json],
                'about' => ['title' => 'About Us', 'json' => $about_json],
                'services' => ['title' => 'Services', 'json' => $services_json],
                'estimate' => ['title' => 'Get Estimate', 'json' => $estimate_json],
                'contact' => ['title' => 'Contact Us', 'json' => $contact_json]
            ];

            foreach ($pages as $key => $page_data) {
                try {
                    $page = $wp_api->createPage(
                        $page_data['title'],
                        '',
                        'elementor_canvas',
                        'publish'
                    );

                    // Set Elementor data
                    $wp_api->setElementorData($page['id'], $page_data['json']);

                    $results['page_urls'][$key] = $page['link'];
                    echo "  Created: {$page_data['title']} - {$page['link']}\n";
                } catch (Exception $e) {
                    $results['errors'][] = "Failed to create {$page_data['title']}: " . $e->getMessage();
                }
            }
        }

        // Step 10: Set Elementor Theme Builder (if enabled)
        if ($options['set_theme_builder'] && $wp_api !== null) {
            echo "Setting up Elementor theme builder...\n";

            try {
                // Create header template
                $header_template = $wp_api->createElementorTemplate(
                    $contractor_data['company_name'] . ' - Header',
                    'header',
                    $header_json
                );

                // Set header to display on all pages
                $wp_api->setThemeBuilderConditions($header_template['id'], [
                    ['type' => 'include', 'name' => 'general']
                ]);

                echo "  Header template created\n";

                // Create footer template
                $footer_template = $wp_api->createElementorTemplate(
                    $contractor_data['company_name'] . ' - Footer',
                    'footer',
                    $footer_json
                );

                // Set footer to display on all pages
                $wp_api->setThemeBuilderConditions($footer_template['id'], [
                    ['type' => 'include', 'name' => 'general']
                ]);

                echo "  Footer template created\n";
            } catch (Exception $e) {
                $results['errors'][] = "Failed to set theme builder: " . $e->getMessage();
            }
        }

        $results['success'] = true;
        echo "\n✅ Website generation complete!\n";

    } catch (Exception $e) {
        $results['errors'][] = $e->getMessage();
        echo "\n❌ Error: " . $e->getMessage() . "\n";
    }

    return $results;
}

/**
 * Generate complete website without WordPress integration
 * Returns all JSON templates and markup for manual installation
 *
 * @param array $contractor_data Contractor business data
 * @param array $options Optional configuration
 * @return array Generated templates and markup
 */
function generate_contractor_templates($contractor_data, $options = []) {
    $options['create_pages'] = false;
    $options['set_theme_builder'] = false;

    return print_contractor_site($contractor_data, $options);
}

/**
 * Quick site generator with minimal data
 * Provides sensible defaults for missing fields
 *
 * @param array $minimal_data Minimal contractor data (name, trade, phone, email, city, state)
 * @return array Generated templates
 */
function quick_print_site($minimal_data) {
    $trade_defaults = [
        'hvac' => [
            'tagline' => 'Keeping Your Home Comfortable Year-Round',
            'services' => [
                ['name' => 'Furnace Repair', 'description' => 'Expert furnace repair and maintenance'],
                ['name' => 'AC Installation', 'description' => 'Professional air conditioning installation'],
                ['name' => 'Duct Cleaning', 'description' => 'Thorough duct cleaning services']
            ]
        ],
        'plumbing' => [
            'tagline' => 'Your Trusted Plumbing Professionals',
            'services' => [
                ['name' => 'Drain Cleaning', 'description' => 'Fast drain cleaning service'],
                ['name' => 'Pipe Repair', 'description' => 'Expert pipe repair and replacement'],
                ['name' => 'Water Heater Service', 'description' => 'Water heater installation and repair']
            ]
        ],
        'electrical' => [
            'tagline' => 'Professional Electrical Services',
            'services' => [
                ['name' => 'Panel Upgrades', 'description' => 'Electrical panel upgrades'],
                ['name' => 'Wiring', 'description' => 'Complete wiring services'],
                ['name' => 'Lighting Installation', 'description' => 'Professional lighting installation']
            ]
        ]
    ];

    $trade = strtolower($minimal_data['trade']);
    $defaults = $trade_defaults[$trade] ?? $trade_defaults['hvac'];

    $full_data = array_merge([
        'zip' => '00000',
        'address' => '123 Main St',
        'tagline' => $defaults['tagline'],
        'services' => $defaults['services'],
        'service_areas' => [$minimal_data['city']],
        'hours' => 'Mon-Fri: 8:00am-5:00pm',
        'years_in_business' => 10
    ], $minimal_data);

    return generate_contractor_templates($full_data);
}

/**
 * Export templates to files
 *
 * @param array $templates Templates from print_contractor_site()
 * @param string $output_dir Output directory path
 * @return bool Success status
 */
function export_templates_to_files($templates, $output_dir = './output') {
    if (!is_dir($output_dir)) {
        mkdir($output_dir, 0755, true);
    }

    foreach ($templates as $name => $json) {
        $filename = $output_dir . '/' . $name . '-template.json';
        file_put_contents($filename, $json);
        echo "Exported: $filename\n";
    }

    return true;
}

/**
 * Validate contractor data structure
 *
 * @param array $data Contractor data to validate
 * @return array Validation results with errors array
 */
function validate_webprinter_data($data) {
    $errors = [];
    $required = ['company_name', 'trade', 'phone', 'email', 'address', 'city', 'state', 'zip'];

    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = "Missing required field: $field";
        }
    }

    // Validate email
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Validate trade
    $valid_trades = ['hvac', 'plumbing', 'electrical', 'roofing', 'painting', 'landscaping',
                     'carpentry', 'locksmith', 'general_contractor', 'pest_control'];
    if (!empty($data['trade']) && !in_array(strtolower($data['trade']), $valid_trades)) {
        $errors[] = "Invalid trade type. Use: " . implode(', ', $valid_trades);
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
