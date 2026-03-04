<?php
/**
 * Elementor Get Estimate Page Template Data Injector
 *
 * Injects contractor-specific data into the estimate page template.
 * Handles InstaBid embed code, trust signals, FAQs, and contact info.
 *
 * @package ElementorEstimateInjector
 * @version 1.0.0
 */

/**
 * Recursive array replacement helper
 *
 * @param array $array Array to process
 * @param array $replacements Map of placeholders to values
 * @return array Modified array
 */
function replace_in_estimate_array($array, $replacements) {
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = replace_in_estimate_array($value, $replacements);
        } elseif (is_string($value)) {
            $array[$key] = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $value
            );
        }
    }
    return $array;
}

/**
 * Inject estimate page data into template
 *
 * @param array $template The Elementor template structure
 * @param array $data {
 *     Contractor and estimate data
 *
 *     @type string   $name             Company name
 *     @type string   $phone            Phone number with formatting
 *     @type string   $instabid_embed   InstaBid iframe embed code or placeholder
 *     @type string   $response_time    Expected response time (e.g., "Within 2 hours")
 *     @type array    $trust_signals    Array of 4 trust signal texts
 *     @type array    $faqs             Array of FAQ objects with 'q' and 'a' keys
 * }
 * @return array Modified template with injected data
 */
function inject_estimate_data($template, $data) {
    // Validate required fields
    $required = ['name', 'phone', 'instabid_embed', 'response_time', 'trust_signals', 'faqs'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            throw new InvalidArgumentException("Missing required field: {$field}");
        }
    }

    // Validate trust signals (must be exactly 4)
    if (!is_array($data['trust_signals']) || count($data['trust_signals']) !== 4) {
        throw new InvalidArgumentException("trust_signals must be an array of exactly 4 items");
    }

    // Validate FAQs (must be exactly 3)
    if (!is_array($data['faqs']) || count($data['faqs']) !== 3) {
        throw new InvalidArgumentException("faqs must be an array of exactly 3 items");
    }

    foreach ($data['faqs'] as $index => $faq) {
        if (!isset($faq['q']) || !isset($faq['a'])) {
            throw new InvalidArgumentException("FAQ at index {$index} must have 'q' and 'a' keys");
        }
    }

    // Extract steps if provided, otherwise use defaults
    $steps = isset($data['steps']) && is_array($data['steps']) && count($data['steps']) === 3
        ? $data['steps']
        : ['Submit Details', 'Get Estimate', 'Receive Quote'];

    // Build replacement map
    $replacements = [
        '{{phone}}' => $data['phone'],
        '{{instabid_embed}}' => $data['instabid_embed'],
        '{{response_time}}' => $data['response_time'],
        '{{trust_signal_1}}' => $data['trust_signals'][0],
        '{{trust_signal_2}}' => $data['trust_signals'][1],
        '{{trust_signal_3}}' => $data['trust_signals'][2],
        '{{trust_signal_4}}' => $data['trust_signals'][3],
        '{{step_1}}' => $steps[0],
        '{{step_2}}' => $steps[1],
        '{{step_3}}' => $steps[2],
        '{{faq_1_q}}' => $data['faqs'][0]['q'],
        '{{faq_1_a}}' => $data['faqs'][0]['a'],
        '{{faq_2_q}}' => $data['faqs'][1]['q'],
        '{{faq_2_a}}' => $data['faqs'][1]['a'],
        '{{faq_3_q}}' => $data['faqs'][2]['q'],
        '{{faq_3_a}}' => $data['faqs'][2]['a']
    ];

    return replace_in_estimate_array($template, $replacements);
}

/**
 * Validate that template has all required placeholders
 *
 * @param array $template The template to validate
 * @return bool True if valid
 * @throws InvalidArgumentException if template is invalid
 */
function validate_estimate_template($template) {
    $required_placeholders = [
        '{{phone}}',
        '{{instabid_embed}}',
        '{{response_time}}',
        '{{trust_signal_1}}',
        '{{trust_signal_2}}',
        '{{trust_signal_3}}',
        '{{trust_signal_4}}',
        '{{step_1}}',
        '{{step_2}}',
        '{{step_3}}',
        '{{faq_1_q}}',
        '{{faq_1_a}}',
        '{{faq_2_q}}',
        '{{faq_2_a}}',
        '{{faq_3_q}}',
        '{{faq_3_a}}'
    ];

    $json = json_encode($template);

    foreach ($required_placeholders as $placeholder) {
        if (strpos($json, $placeholder) === false) {
            throw new InvalidArgumentException("Template missing required placeholder: {$placeholder}");
        }
    }

    return true;
}

/**
 * Check if template still contains any unreplaced placeholders
 *
 * @param array $template The template to check
 * @return array Array of remaining placeholders (empty if none)
 */
function find_remaining_placeholders($template) {
    $json = json_encode($template);
    preg_match_all('/\{\{([^}]+)\}\}/', $json, $matches);

    return array_unique($matches[0]);
}

/**
 * WordPress integration: Save populated template as Elementor page
 *
 * @param array  $template_data Populated template
 * @param string $page_title    Title for the new page
 * @return int Page ID
 */
function create_estimate_page($template_data, $page_title = 'Get Estimate') {
    // Create the page
    $page_id = wp_insert_post([
        'post_title'   => $page_title,
        'post_status'  => 'publish',
        'post_type'    => 'page'
    ]);

    if (is_wp_error($page_id)) {
        throw new RuntimeException("Failed to create page: " . $page_id->get_error_message());
    }

    // Enable Elementor
    update_post_meta($page_id, '_elementor_edit_mode', 'builder');
    update_post_meta($page_id, '_elementor_template_type', 'wp-page');
    update_post_meta($page_id, '_elementor_version', '3.16.0');

    // Set page template
    update_post_meta($page_id, '_wp_page_template', 'elementor_canvas');

    // Save Elementor data
    update_post_meta($page_id, '_elementor_data', wp_slash(json_encode($template_data['content'])));
    update_post_meta($page_id, '_elementor_page_settings', wp_slash(json_encode($template_data['page_settings'])));

    // Clear Elementor cache
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }

    return $page_id;
}

// Example usage (commented out)
/*
// Load template
$template = json_decode(file_get_contents('elementor-estimate-template.json'), true);

// Prepare data
$data = [
    'name' => 'Pyramid Heating & Cooling',
    'phone' => '(503) 555-0142',
    'instabid_embed' => '<!-- INSTABID_EMBED_CODE_HERE -->',
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
        'Submit Details',
        'Get Estimate',
        'Approve & Schedule'
    ]
];

// Inject data
$populated = inject_estimate_data($template, $data);

// Create page in WordPress
$page_id = create_estimate_page($populated, 'Get Your Free Estimate');
echo "Estimate page created with ID: {$page_id}";
*/
