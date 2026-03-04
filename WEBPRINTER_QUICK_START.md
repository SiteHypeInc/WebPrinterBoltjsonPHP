# WebPrinter Engine - Quick Start

Generate a complete 7-page contractor website with one function call.

## One Function. One Array. Complete Website.

```php
<?php
require_once 'webprinter-engine.php';

$contractor_data = [
    'company_name' => 'Pyramid Heating & Cooling',
    'trade' => 'hvac',
    'phone' => '(503) 555-0142',
    'email' => 'info@pyramidheating.com',
    'address' => '123 Industrial Pkwy',
    'city' => 'Portland',
    'state' => 'OR',
    'zip' => '97210',
    'services' => [
        ['name' => 'Furnace Repair'],
        ['name' => 'AC Installation']
    ]
];

$results = print_contractor_site($contractor_data);
```

## What You Get

### 7 Elementor Templates
1. **Header** - Global navigation with logo, phone, email
2. **Footer** - Contact info, social media links
3. **Home** - Hero section, company info
4. **About** - Company story, team, certifications
5. **Services** - Service catalog with pricing
6. **Estimate** - InstaBid integration, contact form
7. **Contact** - Contact form, map, business hours

### SEO Markup
- Complete Schema.org JSON-LD (LocalBusiness, trade-specific)
- Title tags (Company | City | Trade)
- Meta descriptions with ratings
- Open Graph tags (Facebook, LinkedIn)
- Twitter Card tags
- Geographic meta tags

## Three Usage Modes

### 1. Template Generation Only
```php
$results = generate_contractor_templates($contractor_data);
// Returns JSON templates for manual use
```

### 2. Quick Start (Minimal Data)
```php
$results = quick_print_site([
    'company_name' => 'Quick Fix Plumbing',
    'trade' => 'plumbing',
    'phone' => '555-1234',
    'email' => 'info@quickfix.com',
    'city' => 'Seattle',
    'state' => 'WA'
]);
// Auto-fills defaults for missing fields
```

### 3. Full WordPress Integration
```php
$results = print_contractor_site($contractor_data, [
    'create_pages' => true,
    'set_theme_builder' => true,
    'wp_site_url' => 'https://yoursite.com',
    'wp_username' => 'your_username',
    'wp_app_password' => 'your_app_password'
]);
// Creates pages in WordPress automatically
```

## Required Fields

```php
[
    'company_name' => '',  // Business name
    'trade' => '',         // hvac, plumbing, electrical, etc.
    'phone' => '',         // Phone number
    'email' => '',         // Email address
    'address' => '',       // Street address
    'city' => '',          // City
    'state' => '',         // State (2-letter code)
    'zip' => ''            // ZIP code
]
```

## Optional Fields (Recommended)

```php
[
    'lat' => 45.5231,
    'lng' => -122.6765,
    'rating' => 4.7,
    'review_count' => 143,
    'website' => 'https://company.com',
    'logo_url' => 'https://company.com/logo.png',
    'services' => [
        ['name' => 'Service Name', 'desc' => 'Description', 'price' => 'From $99']
    ],
    'service_areas' => ['City 1', 'City 2'],
    'hours' => 'Mo-Fr 08:00-17:00',
    'social_media' => [
        'facebook' => 'https://facebook.com/...',
        'twitter' => 'https://twitter.com/...'
    ]
]
```

## Supported Trades

- `hvac` → HVACBusiness
- `plumbing` → Plumber
- `electrical` → Electrician
- `roofing` → RoofingContractor
- `locksmith` → Locksmith
- `general_contractor` → GeneralContractor
- `painting`, `landscaping`, `carpentry` → ProfessionalService

## Export Templates

```php
$results = generate_contractor_templates($contractor_data);
export_templates_to_files($results['templates'], './output');
```

## Validation

```php
$validation = validate_webprinter_data($contractor_data);
if (!$validation['valid']) {
    foreach ($validation['errors'] as $error) {
        echo "Error: $error\n";
    }
}
```

## WordPress Integration

When using WordPress integration:

1. Install Elementor Pro on your WordPress site
2. Generate application password in WordPress (Users → Profile)
3. Call with WordPress options:

```php
$results = print_contractor_site($contractor_data, [
    'create_pages' => true,
    'set_theme_builder' => true,
    'wp_site_url' => 'https://yoursite.com',
    'wp_username' => 'admin',
    'wp_app_password' => 'xxxx xxxx xxxx xxxx'
]);

echo "Site URL: " . $results['site_url'] . "\n";
foreach ($results['page_urls'] as $page => $url) {
    echo "$page: $url\n";
}
```

## Testing

```bash
# Run WebPrinter tests
php test-webprinter.php

# Run all system tests
php test-schema-generator.php
php test-contact-injection.php
php test-estimate-injection.php
```

## Examples

See `example-webprinter-usage.php` for 10 complete examples including:
- Full website generation
- Minimal data quick start
- Template export
- WordPress integration
- Custom InstaBid integration
- Custom FAQs and trust signals
- Schema markup preview

## Output Structure

```php
[
    'success' => true/false,
    'site_url' => 'https://yoursite.com',
    'page_urls' => [
        'home' => 'https://yoursite.com/home',
        'about' => 'https://yoursite.com/about-us',
        'services' => 'https://yoursite.com/services',
        'estimate' => 'https://yoursite.com/get-estimate',
        'contact' => 'https://yoursite.com/contact-us'
    ],
    'templates' => [
        'header' => '...JSON...',
        'footer' => '...JSON...',
        'home' => '...JSON...',
        'about' => '...JSON...',
        'services' => '...JSON...',
        'estimate' => '...JSON...',
        'contact' => '...JSON...'
    ],
    'schema' => '<script type="application/ld+json">...</script>',
    'meta_tags' => '<title>...</title><meta...>',
    'errors' => []
]
```

## Performance

- Generation time: < 1 second
- Template size: ~90KB total (all 7 templates)
- No external API calls
- Pure PHP processing

## Next Steps

1. Review `example-webprinter-usage.php` for working examples
2. Run `test-webprinter.php` to verify installation
3. Generate your first site with minimal data
4. Customize with optional fields
5. Deploy to WordPress or export templates

For complete documentation, see `webprinter-engine.php` inline documentation.
