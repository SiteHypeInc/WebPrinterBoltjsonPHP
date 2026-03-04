<?php
/**
 * Example Usage: Elementor Contact Page Template
 *
 * This example demonstrates how to create professional contact pages
 * for various contractor businesses with different configurations.
 */

require_once 'elementor-contact-injector.php';

// Example 1: HVAC Contractor with Emergency Service
function create_hvac_contact_page() {
    // Load template
    $template = json_decode(
        file_get_contents('elementor-contact-template.json'),
        true
    );

    // Prepare data
    $data = [
        'name' => 'Pyramid Heating & Cooling',
        'phone' => '(503) 555-0142',
        'email' => 'info@pyramidheating.com',
        'address' => 'Portland, OR 97201',
        'hours' => 'Mon-Fri 7am-6pm, Sat 8am-4pm',
        'emergency' => true,
        'emergency_phone' => '(503) 555-0143',
        'map_embed' => generate_google_maps_embed('Portland, OR 97201', '100%', 450),
        'hours_note' => 'Closed Sundays and major holidays'
    ];

    // Inject data and create page
    $populated = inject_contact_data($template, $data);
    $page_id = create_contact_page($populated, 'Contact Us');

    echo "✓ HVAC contact page created (ID: {$page_id})\n";
    echo "  - Emergency service: Enabled\n";
    echo "  - Map: Google Maps embed\n";
    return $page_id;
}

// Example 2: Plumbing Company without Emergency Service
function create_plumbing_contact_page() {
    $template = json_decode(
        file_get_contents('elementor-contact-template.json'),
        true
    );

    // No emergency service, business hours only
    $data = [
        'name' => 'Premier Plumbing Solutions',
        'phone' => '(503) 555-7890',
        'email' => 'contact@premierplumbing.com',
        'address' => 'Beaverton, OR 97005',
        'hours' => 'Monday-Friday: 8am-5pm',
        'emergency' => false,  // No emergency service
        'map_embed' => generate_google_maps_embed('Beaverton, OR 97005'),
        'hours_note' => 'Closed weekends and holidays'
    ];

    $populated = inject_contact_data($template, $data);
    $page_id = create_contact_page($populated, 'Contact Premier Plumbing');

    echo "✓ Plumbing contact page created (ID: {$page_id})\n";
    echo "  - Emergency service: Disabled\n";
    echo "  - Standard hours messaging used\n";
    return $page_id;
}

// Example 3: Electrical Contractor with Custom Map
function create_electrical_contact_page() {
    $template = json_decode(
        file_get_contents('elementor-contact-template.json'),
        true
    );

    // Custom map embed and detailed hours
    $data = [
        'name' => 'Bright Spark Electric',
        'phone' => '(503) 555-2468',
        'email' => 'service@brightspark.com',
        'address' => '123 Industrial Way, Portland, OR 97210',
        'hours' => 'Mon-Thu: 7am-7pm | Fri: 7am-5pm | Sat: 8am-2pm',
        'emergency' => true,
        'emergency_phone' => '(503) 555-2469',
        'map_embed' => '<iframe src="https://maps.google.com/maps?q=123+Industrial+Way+Portland+OR+97210&t=&z=15&ie=UTF8&iwloc=&output=embed" width="100%" height="450" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy"></iframe>',
        'hours_note' => 'Closed Sundays • Emergency service available 24/7'
    ];

    $populated = inject_contact_data($template, $data);
    $page_id = create_contact_page($populated, 'Contact Bright Spark');

    echo "✓ Electrical contact page created (ID: {$page_id})\n";
    echo "  - Full street address included\n";
    echo "  - Custom hours format\n";
    return $page_id;
}

// Example 4: Roofing Company with Service Area Focus
function create_roofing_contact_page() {
    $template = json_decode(
        file_get_contents('elementor-contact-template.json'),
        true
    );

    // Focus on service area rather than office location
    $data = [
        'name' => 'Summit Roofing & Repair',
        'phone' => '(503) 555-3691',
        'email' => 'quotes@summitroofing.com',
        'address' => 'Serving Portland Metro Area',
        'hours' => 'Mon-Sat: 7am-6pm',
        'emergency' => true,
        'emergency_phone' => '(503) 555-3692',
        'hours_note' => 'Sunday appointments available by request'
    ];
    // Omitting map_embed to use default placeholder

    $populated = inject_contact_data($template, $data);
    $page_id = create_contact_page($populated, 'Contact Summit Roofing');

    echo "✓ Roofing contact page created (ID: {$page_id})\n";
    echo "  - Service area instead of address\n";
    echo "  - Map placeholder (add custom map later)\n";
    return $page_id;
}

// Example 5: Landscaping Company with Seasonal Hours
function create_landscaping_contact_page() {
    $template = json_decode(
        file_get_contents('elementor-contact-template.json'),
        true
    );

    $data = [
        'name' => 'Green Valley Landscaping',
        'phone' => '(503) 555-8888',
        'email' => 'info@greenvalley.com',
        'address' => 'Lake Oswego, OR',
        'hours' => 'March-October: Mon-Sat 7am-5pm | November-February: Mon-Fri 8am-4pm',
        'emergency' => false,
        'map_embed' => generate_google_maps_embed('Lake Oswego, OR'),
        'hours_note' => 'Seasonal hours • Winter storm cleanup available'
    ];

    $populated = inject_contact_data($template, $data);
    $page_id = create_contact_page($populated, 'Contact Green Valley');

    echo "✓ Landscaping contact page created (ID: {$page_id})\n";
    echo "  - Seasonal hours included\n";
    return $page_id;
}

// Example 6: Locksmith with Same Phone for Everything
function create_locksmith_contact_page() {
    $template = json_decode(
        file_get_contents('elementor-contact-template.json'),
        true
    );

    // Same number for regular and emergency
    $data = [
        'name' => '24/7 Metro Locksmith',
        'phone' => '(503) 555-LOCK',
        'email' => 'help@metrolocksmith.com',
        'address' => 'Mobile Service - Portland Area',
        'hours' => 'Available 24 Hours, 7 Days a Week',
        'emergency' => true,
        'emergency_phone' => '(503) 555-LOCK',  // Same as main phone
        'hours_note' => 'Always open, including holidays'
    ];

    $populated = inject_contact_data($template, $data);
    $page_id = create_contact_page($populated, 'Contact 24/7 Locksmith');

    echo "✓ Locksmith contact page created (ID: {$page_id})\n";
    echo "  - 24/7 availability highlighted\n";
    echo "  - Same number for all contact\n";
    return $page_id;
}

// Example 7: General Contractor with Office Location
function create_general_contractor_contact_page() {
    $template = json_decode(
        file_get_contents('elementor-contact-template.json'),
        true
    );

    $data = [
        'name' => 'Pacific Northwest Builders',
        'phone' => '(503) 555-1234',
        'email' => 'projects@pnwbuilders.com',
        'address' => '456 Commerce St, Suite 200, Portland, OR 97204',
        'hours' => 'Office Hours: Mon-Fri 8am-5pm',
        'emergency' => false,
        'map_embed' => generate_google_maps_embed('456 Commerce St, Portland, OR 97204', '100%', 500),
        'hours_note' => 'Site visits by appointment only'
    ];

    $populated = inject_contact_data($template, $data);
    $page_id = create_contact_page($populated, 'Contact PNW Builders');

    echo "✓ General contractor contact page created (ID: {$page_id})\n";
    echo "  - Full office address with suite number\n";
    echo "  - Appointment-only note\n";
    return $page_id;
}

// Example 8: Appliance Repair with Email Focus
function create_appliance_repair_contact_page() {
    $template = json_decode(
        file_get_contents('elementor-contact-template.json'),
        true
    );

    $data = [
        'name' => 'Expert Appliance Repair',
        'phone' => '(503) 555-9876',
        'email' => 'service@expertappliance.com',
        'address' => 'Gresham, OR and surrounding areas',
        'hours' => 'Mon-Fri: 9am-6pm, Sat: 10am-3pm',
        'emergency' => false,
        'hours_note' => 'Same-day service available for most requests'
    ];

    $populated = inject_contact_data($template, $data);
    $page_id = create_contact_page($populated, 'Contact Expert Appliance');

    echo "✓ Appliance repair contact page created (ID: {$page_id})\n";
    echo "  - Service area focus\n";
    echo "  - Same-day note added\n";
    return $page_id;
}

// Run examples (uncomment to use)
// create_hvac_contact_page();
// create_plumbing_contact_page();
// create_electrical_contact_page();
// create_roofing_contact_page();
// create_landscaping_contact_page();
// create_locksmith_contact_page();
// create_general_contractor_contact_page();
// create_appliance_repair_contact_page();

echo "\n=== Contact Page Template Examples ===\n";
echo "Uncomment the function calls at the bottom to create pages.\n";
echo "\nAvailable examples:\n";
echo "  1. HVAC with 24/7 emergency service\n";
echo "  2. Plumbing without emergency service\n";
echo "  3. Electrical with custom map embed\n";
echo "  4. Roofing with service area focus\n";
echo "  5. Landscaping with seasonal hours\n";
echo "  6. Locksmith (24/7, same number for all)\n";
echo "  7. General contractor with office location\n";
echo "  8. Appliance repair with email focus\n";
echo "\nEach example demonstrates:\n";
echo "  - Different emergency service configurations\n";
echo "  - Various hours formats\n";
echo "  - Address vs service area approaches\n";
echo "  - Map integration options\n";
echo "\n";
