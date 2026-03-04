# Elementor Services Page Template System

Complete system for generating contractor services detail pages with dynamic data injection.

## Overview

This system creates professional services pages with 6 service cards, process steps, and service area listings. Designed for contractor websites (HVAC, plumbing, electrical, etc.).

## Files

- `elementor-services-template.json` - Base Elementor template (6 sections)
- `elementor-services-injector.php` - Data injection and WordPress integration
- `SERVICES_PAGE_README.md` - This documentation

## Template Structure

### 1. Hero Section
- Blue gradient background
- "Our {{trade}} Services" heading
- Company tagline subheading

### 2. Services Grid (2 rows × 3 columns = 6 services)
- Each service card includes:
  - Icon box with service name and description
  - Price/estimate display in green
  - "Learn More" button
- Light gray background (#f9fafb)
- Color-coded icons (blue, red, gold, green, purple, pink)

### 3. How It Works (3-step process)
- Three icon boxes showing service process
- Phone icon (blue) → Calendar icon (green) → Check icon (green)
- Customizable step text

### 4. Service Area
- "Areas We Serve" heading
- Auto-formatted city list with bullet separators

### 5. Call-to-Action
- Green gradient background
- "Get Your Free Estimate Today" heading
- Company name and trade in CTA text
- White button with shadow

## Data Structure

```php
$contractor = [
    'name' => 'Pyramid Heating & Cooling',      // Company name
    'trade' => 'HVAC',                          // Trade/industry
    'tagline' => "Portland's Heating & Cooling Experts",
    'services' => [                             // Up to 6 services
        [
            'name' => 'Furnace Repair',
            'desc' => 'Fast, reliable furnace repair...',
            'price' => 'From $89'               // Or "Free Estimate"
        ],
        [
            'name' => 'AC Installation',
            'desc' => 'Professional air conditioning...',
            'price' => 'Free Estimate'
        ],
        // ... up to 6 total
    ],
    'service_areas' => [                        // Cities served
        'Portland', 'Beaverton', 'Gresham',
        'Lake Oswego', 'Tigard', 'Hillsboro'
    ],
    'steps' => [                                // Must be exactly 3
        'Call Us',
        'We Schedule',
        'Job Done'
    ]
];
```

## Quick Start

```php
require_once 'elementor-services-injector.php';

// 1. Load your data
$data = [
    'name' => 'Smith Plumbing',
    'trade' => 'Plumbing',
    'tagline' => 'Your Trusted Local Plumbers',
    'services' => [
        ['name' => 'Leak Repair', 'desc' => '...', 'price' => 'From $99'],
        ['name' => 'Drain Cleaning', 'desc' => '...', 'price' => 'From $129'],
        // ... more services
    ],
    'service_areas' => ['Portland', 'Beaverton'],
    'steps' => ['Contact', 'Quote', 'Complete']
];

// 2. Load and inject
$template = file_get_contents('elementor-services-template.json');
$populated = inject_services_data($template, $data);

// 3. Save to WordPress
$page_id = save_services_page($populated, 'Our Services');
echo "Created page ID: $page_id";
```

## Placeholder Reference

### Company Info
- `{{name}}` - Company name
- `{{trade}}` - Trade/industry type
- `{{tagline}}` - Hero tagline

### Services (1-6)
- `{{service_1_name}}` through `{{service_6_name}}`
- `{{service_1_desc}}` through `{{service_6_desc}}`
- `{{service_1_price}}` through `{{service_6_price}}`

### Process Steps
- `{{step_1}}` - Step 1 title
- `{{step_2}}` - Step 2 title
- `{{step_3}}` - Step 3 title

### Service Area
- `{{service_areas}}` - Auto-formatted city list with bullets

## Service Card Colors

The template uses 6 distinct icon colors for visual variety:

1. Blue (#3b82f6) - Wrench icon
2. Red (#ef4444) - Cog icon
3. Gold (#f59e0b) - Tools icon
4. Green (#10b981) - Hammer icon
5. Purple (#8b5cf6) - Hard hat icon
6. Pink (#ec4899) - Clipboard icon

## Features

### Automatic Formatting
- Service areas joined with bullet separators (•)
- Empty service slots filled with generic defaults
- Missing service data handled gracefully

### Validation
- Requires all core fields (name, trade, tagline, services, service_areas, steps)
- Services must be non-empty array
- Service areas must be non-empty array
- Steps must have exactly 3 items

### Responsive Design
- Mobile-friendly column layouts
- Proper spacing and padding
- Touch-friendly button sizes

## WordPress Integration

```php
// Create page with custom title
$page_id = save_services_page($populated, 'HVAC Services');

// The function automatically:
// - Creates a new page
// - Sets Elementor canvas template
// - Enables Elementor editor
// - Publishes the page
// - Returns the page ID
```

## Testing

Run the demo script to test injection:

```bash
php elementor-services-injector.php
```

Expected output:
```
✓ Hero heading contains trade
✓ Service 1 name injected
✓ All 6 services populated
✓ Steps formatted correctly
✓ Service areas formatted
✓ No remaining placeholders

TEST SUMMARY: 18 / 18 tests passed
```

## Common Use Cases

### HVAC Company
```php
$data = [
    'trade' => 'HVAC',
    'services' => [
        ['name' => 'Furnace Repair', ...],
        ['name' => 'AC Installation', ...],
        ['name' => 'Heat Pump Service', ...],
    ],
];
```

### Plumbing Company
```php
$data = [
    'trade' => 'Plumbing',
    'services' => [
        ['name' => 'Leak Detection', ...],
        ['name' => 'Water Heater', ...],
        ['name' => 'Drain Cleaning', ...],
    ],
];
```

### Electrical Company
```php
$data = [
    'trade' => 'Electrical',
    'services' => [
        ['name' => 'Panel Upgrades', ...],
        ['name' => 'Outlet Installation', ...],
        ['name' => 'Lighting Design', ...],
    ],
];
```

## Error Handling

The system validates all inputs and throws exceptions for:

- Missing required fields
- Empty services array
- Empty service areas array
- Wrong number of steps (must be 3)
- WordPress function unavailability

## Best Practices

1. **Service Descriptions**: Keep to 1-2 sentences for card readability
2. **Pricing**: Use "Free Estimate" for complex jobs, specific prices for simple tasks
3. **Service Areas**: List 4-8 cities for best visual balance
4. **Steps**: Keep step titles to 2-3 words each
5. **Services**: Provide all 6 for best grid layout (can use fewer, but 6 looks best)

## File Output

### WordPress Database
```php
save_services_page($populated);
```

### JSON File
```php
file_put_contents('my-services-page.json', $populated);
```

### Direct String
```php
echo $populated;  // Full Elementor JSON
```

## Support

For issues or questions:
1. Check that all required fields are present in your data array
2. Verify services array has at least one service
3. Confirm steps array has exactly 3 items
4. Run the test script to identify specific issues

## Version

Template Version: 1.0
Last Updated: 2026-03-04
