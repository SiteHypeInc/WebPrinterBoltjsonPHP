# Elementor Header & Footer Template System

Complete system for creating and injecting contractor data into Elementor header and footer templates.

## Files

| File | Purpose |
|------|---------|
| `elementor-header-template.json` | Header template with placeholders |
| `elementor-footer-template.json` | Footer template with placeholders |
| `elementor-header-footer-injector.php` | Injection functions + demo |
| `complete-header-footer-example.php` | Complete batch workflow example |

## Quick Start

### 1. Test the System

```bash
php elementor-header-footer-injector.php
```

This runs a demo showing:
- Template loading
- Data injection
- Verification tests
- Sample output

### 2. Single Contractor Usage

```php
<?php
require_once 'elementor-header-footer-injector.php';

// Contractor data
$contractor = [
    'name' => 'Pyramid Heating & Cooling',
    'phone' => '(503) 555-0142',
    'address' => 'Portland, OR',
    'license' => 'CCB #123456',
    'tagline' => 'Portland\'s Heating & Cooling Experts',
    'nav' => ['Home', 'Services', 'About', 'Contact'],
    'cta' => 'Get Free Estimate',
    'logo_url' => 'https://example.com/logo.png' // optional
];

// Load templates
$header_template = file_get_contents('elementor-header-template.json');
$footer_template = file_get_contents('elementor-footer-template.json');

// Inject data
$populated_header = inject_header_data($header_template, $contractor);
$populated_footer = inject_footer_data($footer_template, $contractor);

// Save to files or database
file_put_contents('my-header.json', $populated_header);
file_put_contents('my-footer.json', $populated_footer);
```

### 3. Batch Processing Multiple Contractors

```php
<?php
require_once 'elementor-header-footer-injector.php';

$contractors = [
    ['name' => 'Business 1', 'phone' => '555-0001', ...],
    ['name' => 'Business 2', 'phone' => '555-0002', ...],
    ['name' => 'Business 3', 'phone' => '555-0003', ...]
];

$header_template = file_get_contents('elementor-header-template.json');
$footer_template = file_get_contents('elementor-footer-template.json');

foreach ($contractors as $contractor) {
    $header = inject_header_data($header_template, $contractor);
    $footer = inject_footer_data($footer_template, $contractor);

    // Process each...
}
```

### 4. Save to WordPress Database

```php
<?php
require_once 'elementor-header-footer-injector.php';

// Connect to database
$analyzer = new ElementorAnalyzer();
$analyzer->connectDatabase('localhost', 'wp_db', 'user', 'pass');
$db = $analyzer->getDb();

// Inject and save
$header = inject_header_data($header_template, $contractor);
$footer = inject_footer_data($footer_template, $contractor);

$header_id = save_header_template($db, $header, 'My Header');
$footer_id = save_footer_template($db, $footer, 'My Footer');

echo "Header ID: $header_id\n";
echo "Footer ID: $footer_id\n";
```

## Template Structure

### Header Template

```
┌─────────────────────────────────────────────────────────┐
│  [Logo + Name]    [Navigation Menu]    [Phone] [CTA]    │
│  {{logo_url}}     Home | Services      {{phone}}        │
│  {{name}}         About | Contact      {{cta}}          │
└─────────────────────────────────────────────────────────┘
```

**Layout:**
- 3 columns (30% / 45% / 25%)
- Full-width section with boxed content
- White background with subtle shadow
- Responsive design

**Widgets Used:**
- Image (logo)
- Heading (business name)
- Nav Menu (navigation links)
- Icon Box (phone number with icon)
- Button (CTA)

### Footer Template

```
┌─────────────────────────────────────────────────────────┐
│  {{name}}           Contact Us        Quick Links       │
│  {{tagline}}        Phone: {{phone}}   → Home            │
│  {{license}}        Location:          → Services        │
│                     {{address}}        → About           │
│                                       → Contact          │
├─────────────────────────────────────────────────────────┤
│         © {{current_year}} {{name}}. All rights reserved│
└─────────────────────────────────────────────────────────┘
```

**Layout:**
- Main section: 3 columns (33% / 33% / 33%)
- Copyright section: 1 column (100%)
- Dark theme (#2c3e50 background)
- White text with colored accents

**Widgets Used:**
- Heading (business name, section titles)
- Text Editor (tagline, license, copyright)
- Icon Box (phone, address)
- Icon List (navigation links)

## Contractor Data Schema

```php
[
    // Required fields
    'name' => 'Business Name',
    'phone' => '(555) 123-4567',
    'address' => 'City, State',
    'license' => 'License #',
    'tagline' => 'Business tagline',
    'nav' => ['Link1', 'Link2', 'Link3', 'Link4'],
    'cta' => 'Button text',

    // Optional fields
    'logo_url' => 'https://example.com/logo.png',

    // Auto-generated
    'current_year' => 2026  // Added automatically for copyright
]
```

### Navigation Array

The `nav` array is automatically converted to individual placeholders:

```php
'nav' => ['Home', 'Services', 'About', 'Contact']

// Becomes:
'nav_0' => 'Home'
'nav_1' => 'Services'
'nav_2' => 'About'
'nav_3' => 'Contact'
```

### Logo URL

If `logo_url` is not provided, a placeholder image is automatically generated:

```
https://via.placeholder.com/180x60?text=Business+Name
```

## Functions Reference

### `inject_header_data($template, $data)`

Injects contractor data into header template.

**Parameters:**
- `$template` (string|array) - JSON template or PHP array
- `$data` (array) - Contractor data

**Returns:**
- (string) JSON string ready for database

**Example:**
```php
$header = inject_header_data($template, [
    'name' => 'ABC Plumbing',
    'phone' => '555-1234',
    'nav' => ['Home', 'Services', 'Contact'],
    'cta' => 'Call Now'
]);
```

### `inject_footer_data($template, $data)`

Injects contractor data into footer template.

**Parameters:**
- `$template` (string|array) - JSON template or PHP array
- `$data` (array) - Contractor data

**Returns:**
- (string) JSON string ready for database

**Example:**
```php
$footer = inject_footer_data($template, [
    'name' => 'ABC Plumbing',
    'phone' => '555-1234',
    'address' => 'Portland, OR',
    'license' => 'CCB #12345',
    'tagline' => 'Expert Plumbing',
    'nav' => ['Home', 'Services', 'Contact']
]);
```

### `save_header_template($db, $json, $name)`

Saves header template to WordPress database.

**Parameters:**
- `$db` (PDO) - Database connection
- `$json` (string) - Populated JSON
- `$name` (string) - Template name

**Returns:**
- (int) Template post ID

### `save_footer_template($db, $json, $name)`

Saves footer template to WordPress database.

**Parameters:**
- `$db` (PDO) - Database connection
- `$json` (string) - Populated JSON
- `$name` (string) - Template name

**Returns:**
- (int) Template post ID

## Placeholder Reference

### Header Placeholders

| Placeholder | Field | Widget Type | Example |
|------------|-------|-------------|---------|
| `{{logo_url}}` | image.url | Image | https://example.com/logo.png |
| `{{name}}` | title | Heading | Pyramid Heating |
| `{{phone}}` | description_text | Icon Box | (503) 555-0142 |
| `{{cta}}` | text | Button | Get Free Estimate |
| `{{nav_0}}` | text | Nav Menu | Home |
| `{{nav_1}}` | text | Nav Menu | Services |
| `{{nav_2}}` | text | Nav Menu | About |
| `{{nav_3}}` | text | Nav Menu | Contact |

### Footer Placeholders

| Placeholder | Field | Widget Type | Example |
|------------|-------|-------------|---------|
| `{{name}}` | title | Heading | Pyramid Heating |
| `{{tagline}}` | editor | Text Editor | Portland's Experts |
| `{{license}}` | editor | Text Editor | CCB #123456 |
| `{{phone}}` | description_text | Icon Box | (503) 555-0142 |
| `{{address}}` | description_text | Icon Box | Portland, OR |
| `{{nav_0}}` to `{{nav_3}}` | text | Icon List | Links |
| `{{current_year}}` | editor | Text Editor | 2026 |

## Customization

### Change Colors

Edit the template JSON files:

**Header Background:**
```json
"background_color": "#ffffff"  // White
```

**Footer Background:**
```json
"background_color": "#2c3e50"  // Dark blue-gray
```

**CTA Button:**
```json
"button_background_color": "#ff6600",  // Orange
"button_text_color": "#ffffff"         // White
```

### Change Layout

**Header Column Sizes:**
```json
"_column_size": 30  // Logo column (30%)
"_column_size": 45  // Navigation (45%)
"_column_size": 25  // Contact/CTA (25%)
```

**Footer Column Sizes:**
```json
"_column_size": 33  // Each footer column (33%)
```

### Add More Navigation Items

In header template, add to menu_items array:
```json
{
  "text": "{{nav_4}}",
  "url": "/pricing",
  "_id": "nav-item-4"
}
```

In footer template, add to icon_list array:
```json
{
  "text": "{{nav_4}}",
  "link": {"url": "/pricing"},
  "icon": "fa fa-angle-right",
  "_id": "footer-link-4"
}
```

Then update contractor data:
```php
'nav' => ['Home', 'Services', 'About', 'Contact', 'Pricing']
```

## WordPress Integration

### Save to Elementor Library

Templates are saved as `elementor_library` post type with:

**Header:**
- `_elementor_template_type` = "header"

**Footer:**
- `_elementor_template_type` = "footer"

### Activate Templates

After saving to database:

1. Go to WordPress admin
2. Navigate to **Templates → Theme Builder**
3. Find your header/footer templates
4. Click **Set Conditions**
5. Choose where to display:
   - Entire Site
   - Specific Pages
   - Archive Pages
   - etc.
6. Click **Publish**

### Programmatic Activation

```php
// Set header to display on entire site
update_post_meta($header_id, '_elementor_conditions', [
    'include/general/all'
]);

// Set footer to display on entire site
update_post_meta($footer_id, '_elementor_conditions', [
    'include/general/all'
]);
```

## Multisite Usage

For WordPress multisite networks:

```php
// Switch to specific site
switch_to_blog($site_id);

// Save templates
$header_id = save_header_template($db, $header, 'Site Header');
$footer_id = save_footer_template($db, $footer, 'Site Footer');

// Activate
update_post_meta($header_id, '_elementor_conditions', ['include/general/all']);
update_post_meta($footer_id, '_elementor_conditions', ['include/general/all']);

// Restore original site
restore_current_blog();
```

## Troubleshooting

### Templates not appearing in Theme Builder

Check:
- Post type is `elementor_library`
- Meta key `_elementor_template_type` is set to "header" or "footer"
- Post status is "publish"

### Styling issues

- Clear Elementor CSS cache: `delete_post_meta($id, '_elementor_css')`
- Regenerate CSS in Elementor editor
- Check for theme conflicts

### Placeholders not replaced

- Verify contractor data array has correct keys
- Check placeholder syntax: `{{key}}` not `{{ key }}`
- Ensure template JSON is valid

## Performance Tips

1. Cache templates in memory for batch processing
2. Use transactions for multiple database inserts
3. Process contractors in batches of 50-100
4. Disable auto-regenerate CSS during batch processing

## Best Practices

1. Test with one contractor before batch processing
2. Backup database before bulk operations
3. Use staging environment first
4. Keep template styling simple for easier customization
5. Version control your template JSON files
6. Document any custom modifications

## Next Steps

1. Customize template colors/fonts to match brand
2. Add social media icons to footer
3. Create mobile-specific layouts
4. Add schema markup for SEO
5. Integrate with page builder for full sites
