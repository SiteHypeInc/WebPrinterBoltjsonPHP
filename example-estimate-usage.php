<?php
/**
 * Example Usage: Elementor Get Estimate Page Template
 *
 * This example demonstrates how to create a conversion-optimized
 * estimate request page with InstaBid form integration.
 */

require_once 'elementor-estimate-injector.php';

// Example 1: HVAC Contractor with InstaBid
function create_hvac_estimate_page() {
    // Load template
    $template = json_decode(
        file_get_contents('elementor-estimate-template.json'),
        true
    );

    // Prepare data
    $data = [
        'name' => 'Pyramid Heating & Cooling',
        'phone' => '(503) 555-0142',
        'instabid_embed' => '<iframe src="https://instabid.app/embed/pyramid-hvac" width="100%" height="800" frameborder="0"></iframe>',
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
                'a' => 'Yes! We provide completely free, no-obligation estimates for all HVAC services. You\'ll receive a detailed breakdown of costs with no hidden fees or surprise charges.'
            ],
            [
                'q' => 'What areas do you serve?',
                'a' => 'We proudly serve Portland and surrounding areas including Beaverton, Gresham, Lake Oswego, and Tigard. Contact us to confirm service availability in your specific location.'
            ]
        ],
        'steps' => [
            'Fill Out Form',
            'Get Free Quote',
            'Schedule Service'
        ]
    ];

    // Inject data and create page
    $populated = inject_estimate_data($template, $data);
    $page_id = create_estimate_page($populated, 'Get Your Free HVAC Estimate');

    echo "✓ HVAC estimate page created (ID: {$page_id})\n";
    return $page_id;
}

// Example 2: Plumbing Company with Placeholder
function create_plumbing_estimate_page() {
    $template = json_decode(
        file_get_contents('elementor-estimate-template.json'),
        true
    );

    // Using placeholder until InstaBid is configured
    $data = [
        'name' => 'Premier Plumbing Solutions',
        'phone' => '(503) 555-7890',
        'instabid_embed' => '<!-- INSTABID_EMBED_CODE_HERE -->',
        'response_time' => 'Within 4 hours',
        'trust_signals' => [
            'Always Free Quotes',
            'No Hidden Fees',
            '24/7 Emergency Service',
            'Fully Licensed'
        ],
        'faqs' => [
            [
                'q' => 'Do you offer emergency estimates?',
                'a' => 'Yes! We provide emergency estimates 24/7 for urgent plumbing issues. Our team is always on call to assess your situation and provide immediate pricing.'
            ],
            [
                'q' => 'What information do I need to provide?',
                'a' => 'Simply describe your plumbing issue, provide some photos if possible, and include your address. We\'ll use this information to prepare an accurate estimate for your project.'
            ],
            [
                'q' => 'How accurate are online estimates?',
                'a' => 'Our online estimates are highly accurate for standard jobs. For complex plumbing projects, we may recommend a brief in-home visit to ensure pricing accuracy.'
            ]
        ],
        'steps' => [
            'Describe Your Issue',
            'Review Your Quote',
            'Book Your Plumber'
        ]
    ];

    $populated = inject_estimate_data($template, $data);
    $page_id = create_estimate_page($populated, 'Get Your Plumbing Estimate');

    echo "✓ Plumbing estimate page created (ID: {$page_id})\n";
    echo "  Note: Replace InstaBid placeholder in Elementor editor\n";
    return $page_id;
}

// Example 3: Electrical Contractor with Fast Response
function create_electrical_estimate_page() {
    $template = json_decode(
        file_get_contents('elementor-estimate-template.json'),
        true
    );

    $data = [
        'name' => 'Bright Spark Electric',
        'phone' => '(503) 555-2468',
        'instabid_embed' => '<iframe src="https://instabid.app/embed/bright-spark" width="100%" height="800" frameborder="0"></iframe>',
        'response_time' => 'Within 1 hour',
        'trust_signals' => [
            'Same-Day Quotes',
            'Free Estimates',
            'Licensed Electricians',
            'Satisfaction Guaranteed'
        ],
        'faqs' => [
            [
                'q' => 'What electrical work do you estimate?',
                'a' => 'We provide estimates for all electrical services including panel upgrades, rewiring, lighting installation, outlet additions, EV charger installation, and more.'
            ],
            [
                'q' => 'Do estimates include permits?',
                'a' => 'Yes! Our estimates include all necessary permits and inspections required for your electrical project. We handle all paperwork and coordination with local authorities.'
            ],
            [
                'q' => 'Can I get multiple estimates?',
                'a' => 'You can submit as many estimate requests as you need. We\'re happy to provide separate quotes for different projects or options for the same project.'
            ]
        ],
        'steps' => [
            'Submit Details',
            'Get Instant Quote',
            'Approve & Start'
        ]
    ];

    $populated = inject_estimate_data($template, $data);
    $page_id = create_estimate_page($populated, 'Free Electrical Estimate');

    echo "✓ Electrical estimate page created (ID: {$page_id})\n";
    return $page_id;
}

// Example 4: Roofing Company with Urgency
function create_roofing_estimate_page() {
    $template = json_decode(
        file_get_contents('elementor-estimate-template.json'),
        true
    );

    $data = [
        'name' => 'Summit Roofing & Repair',
        'phone' => '(503) 555-3691',
        'instabid_embed' => '<iframe src="https://instabid.app/embed/summit-roofing" width="100%" height="800" frameborder="0"></iframe>',
        'response_time' => 'Same day',
        'trust_signals' => [
            'Free Roof Inspection',
            'Insurance Claims Help',
            '30-Year Warranty',
            'BBB A+ Rated'
        ],
        'faqs' => [
            [
                'q' => 'Do I need to be home for the estimate?',
                'a' => 'For basic roof estimates, we can often provide an accurate quote using aerial imagery and photos you submit. For complex issues, we may schedule a brief inspection.'
            ],
            [
                'q' => 'Does the estimate include removal of old roofing?',
                'a' => 'Yes! Our comprehensive estimates include removal and disposal of existing roofing materials, new material installation, and complete cleanup of your property.'
            ],
            [
                'q' => 'How long is my estimate valid?',
                'a' => 'Estimates are valid for 30 days from the date provided. Material prices can fluctuate, so we recommend scheduling your project within this timeframe to lock in pricing.'
            ]
        ],
        'steps' => [
            'Request Inspection',
            'Receive Detailed Quote',
            'Schedule Your Roof'
        ]
    ];

    $populated = inject_estimate_data($template, $data);
    $page_id = create_estimate_page($populated, 'Get Your Free Roof Estimate');

    echo "✓ Roofing estimate page created (ID: {$page_id})\n";
    return $page_id;
}

// Run examples (uncomment to use)
// create_hvac_estimate_page();
// create_plumbing_estimate_page();
// create_electrical_estimate_page();
// create_roofing_estimate_page();

echo "\n=== Estimate Page Template Examples ===\n";
echo "Uncomment the function calls at the bottom to create pages.\n";
echo "\nAvailable examples:\n";
echo "  1. HVAC with InstaBid integration\n";
echo "  2. Plumbing with placeholder (add InstaBid later)\n";
echo "  3. Electrical with 1-hour response time\n";
echo "  4. Roofing with insurance claims messaging\n";
echo "\nEach example demonstrates:\n";
echo "  - Industry-specific trust signals\n";
echo "  - Trade-appropriate FAQs\n";
echo "  - Custom process steps\n";
echo "  - Response time messaging\n";
echo "\n";
