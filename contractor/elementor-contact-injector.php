<?php
/**
 * Elementor Contact Page Template Data Injector
 *
 * Injects contractor-specific data into the contact page template.
 * Handles contact information, hours, emergency services, and map embeds.
 *
 * @package ElementorContactInjector
 * @version 1.0.0
 */

/**
 * Recursive array replacement helper
 *
 * @param array $array Array to process
 * @param array $replacements Map of placeholders to values
 * @return array Modified array
 */
function replace_in_contact_array($array, $replacements) {
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = replace_in_contact_array($value, $replacements);
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
 * Inject contact page data into template
 *
 * @param array $template The Elementor template structure
 * @param array $data {
 *     Contractor contact data
 *
 *     @type string   $name              Company name
 *     @type string   $phone             Phone number with formatting
 *     @type string   $email             Contact email address
 *     @type string   $address           Physical address or service area
 *     @type string   $hours             Hours of operation text
 *     @type bool     $emergency         Whether emergency service is available
 *     @type string   $emergency_phone   Emergency phone number (if emergency is true)
 *     @type string   $map_embed         Optional: Map iframe embed code
 *     @type string   $hours_note        Optional: Additional hours note
 * }
 * @return array Modified template with injected data
 */
function inject_contact_data($template, $data) {
    // Validate required fields
    $required = ['name', 'phone', 'email', 'address', 'hours', 'emergency'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            throw new InvalidArgumentException("Missing required field: {$field}");
        }
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException("Invalid email format: {$data['email']}");
    }

    // Validate emergency phone if emergency service is enabled
    if ($data['emergency'] === true && !isset($data['emergency_phone'])) {
        throw new InvalidArgumentException("emergency_phone is required when emergency is true");
    }

    // Strip phone number for tel: links
    $phone_raw = preg_replace('/[^0-9]/', '', $data['phone']);

    // Handle emergency phone
    $emergency_phone_raw = '';
    $emergency_phone_display = '';
    $emergency_title = '';
    $emergency_subtext = '';

    if ($data['emergency'] === true) {
        $emergency_phone_raw = preg_replace('/[^0-9]/', '', $data['emergency_phone']);
        $emergency_phone_display = $data['emergency_phone'];
        $emergency_title = 'Emergency Service Available 24/7';
        $emergency_subtext = 'Call now for immediate assistance';
    } else {
        $emergency_title = 'Standard Service Hours Apply';
        $emergency_phone_display = $data['phone'];
        $emergency_phone_raw = $phone_raw;
        $emergency_subtext = 'Contact us during business hours';
    }

    // Default map placeholder if not provided
    $map_embed = isset($data['map_embed']) && !empty($data['map_embed'])
        ? $data['map_embed']
        : '<!-- MAP_EMBED_CODE_HERE --><div style="background: #e5e7eb; padding: 120px 20px; text-align: center; border-radius: 8px; color: #6b7280; font-size: 18px;">Map embed placeholder - Add your map code here</div>';

    // Default hours note if not provided
    $hours_note = isset($data['hours_note']) && !empty($data['hours_note'])
        ? $data['hours_note']
        : 'Closed on major holidays';

    // Build replacement map
    $replacements = [
        '{{name}}' => $data['name'],
        '{{phone}}' => $data['phone'],
        '{{phone_raw}}' => $phone_raw,
        '{{email}}' => $data['email'],
        '{{address}}' => $data['address'],
        '{{hours}}' => $data['hours'],
        '{{hours_note}}' => $hours_note,
        '{{map_embed}}' => $map_embed,
        '{{emergency_title}}' => $emergency_title,
        '{{emergency_phone_raw}}' => $emergency_phone_raw,
        '{{emergency_phone_display}}' => $emergency_phone_display,
        '{{emergency_subtext}}' => $emergency_subtext
    ];

    return replace_in_contact_array($template, $replacements);
}

/**
 * Validate that template has all required placeholders
 *
 * @param array $template The template to validate
 * @return bool True if valid
 * @throws InvalidArgumentException if template is invalid
 */
function validate_contact_template($template) {
    $required_placeholders = [
        '{{phone}}',
        '{{phone_raw}}',
        '{{email}}',
        '{{address}}',
        '{{hours}}',
        '{{hours_note}}',
        '{{map_embed}}',
        '{{emergency_title}}',
        '{{emergency_phone_raw}}',
        '{{emergency_phone_display}}',
        '{{emergency_subtext}}'
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
function find_remaining_contact_placeholders($template) {
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
function create_contact_page($template_data, $page_title = 'Contact Us') {
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

/**
 * Generate Google Maps embed code
 *
 * @param string $address Full address or place name
 * @param int    $width   Map width (default 100%)
 * @param int    $height  Map height in pixels (default 450)
 * @return string iframe embed code
 */
function generate_google_maps_embed($address, $width = '100%', $height = 450) {
    $encoded_address = urlencode($address);
    $width_attr = is_numeric($width) ? "{$width}px" : $width;

    return sprintf(
        '<iframe src="https://maps.google.com/maps?q=%s&t=&z=13&ie=UTF8&iwloc=&output=embed" width="%s" height="%d" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy"></iframe>',
        $encoded_address,
        $width_attr,
        $height
    );
}

// Example usage (commented out)
/*
// Load template
$template = json_decode(file_get_contents('elementor-contact-template.json'), true);

// Prepare data
$data = [
    'name' => 'Pyramid Heating & Cooling',
    'phone' => '(503) 555-0142',
    'email' => 'info@pyramidheating.com',
    'address' => 'Portland, OR',
    'hours' => 'Mon-Fri 7am-6pm, Sat 8am-4pm',
    'emergency' => true,
    'emergency_phone' => '(503) 555-0143',
    'map_embed' => generate_google_maps_embed('Portland, OR'),
    'hours_note' => 'Closed Sundays and major holidays'
];

// Inject data
$populated = inject_contact_data($template, $data);

// Create page in WordPress
$page_id = create_contact_page($populated, 'Contact Us');
echo "Contact page created with ID: {$page_id}";
*/
