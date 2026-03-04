# Elementor Data Structure Analyzer & Contractor Data Injector

This PHP script reverse-engineers Elementor's data structure and enables automated site generation by injecting contractor data into Elementor page templates.

## Features

1. **Database Connection** - Optionally connect to WordPress MySQL database to fetch real Elementor data
2. **Structure Analysis** - Maps element hierarchy (sections → columns → widgets)
3. **Field Mapping** - Identifies all editable fields with JSON paths
4. **Data Injection** - Populates templates with contractor data automatically

## Quick Start

### Run the demo (no database required)
```bash
php elementor-analyzer.php
```

This will:
- Analyze a sample Elementor JSON structure
- Map all elements and widgets
- Inject contractor data
- Show before/after comparison

## Usage Scenarios

### Scenario 1: Analyze Sample JSON

```php
<?php
require_once 'elementor-analyzer.php';

$analyzer = new ElementorAnalyzer();

// Analyze any Elementor JSON
$json = file_get_contents('sample-template.json');
$analysis = $analyzer->analyzeStructure($json);
$analyzer->generateReport();
```

### Scenario 2: Fetch from WordPress Database

```php
<?php
require_once 'elementor-analyzer.php';

$analyzer = new ElementorAnalyzer();

// Connect to database
$analyzer->connectDatabase(
    'localhost',
    'wordpress_db',
    'wp_user',
    'password'
);

// Fetch specific page
$pages = $analyzer->fetchElementorData(123); // post_id = 123

// Or fetch multiple pages
$pages = $analyzer->fetchElementorData(null, 10); // first 10 pages

// Analyze each
foreach ($pages as $page) {
    echo "Analyzing post ID: {$page['post_id']}\n";
    $analyzer->analyzeStructure($page['meta_value']);
    $analyzer->generateReport();
}
```

### Scenario 3: Inject Contractor Data

```php
<?php
require_once 'elementor-analyzer.php';

// Your template JSON
$template_json = file_get_contents('elementor-template.json');

// Contractor data
$contractor_data = [
    'name' => 'Cascade Roofing Solutions',
    'phone' => '(503) 555-0187',
    'address' => '123 Main St, Portland OR',
    'hero_headline' => 'Portland\'s Most Trusted Roofer',
    'hero_sub' => 'Fast estimates. Fair prices.',
    'about' => 'Family owned since 2019...',
    'services' => ['Roofing', 'Gutters', 'Repairs'],
    'service_area' => 'Portland Metro'
];

// Inject data
$populated_json = inject_contractor_data($template_json, $contractor_data);

// Save or use the populated JSON
file_put_contents('populated-page.json', $populated_json);
```

### Scenario 4: Complete Workflow (Fetch → Inject → Update)

```php
<?php
require_once 'elementor-analyzer.php';

$analyzer = new ElementorAnalyzer();
$analyzer->connectDatabase('localhost', 'wordpress_db', 'user', 'pass');

// Fetch template page
$template_pages = $analyzer->fetchElementorData(100); // template page ID
$template_json = $template_pages[0]['meta_value'];

// Define contractor data
$contractor_data = [
    'name' => 'Cascade Roofing Solutions',
    'phone' => '(503) 555-0187',
    'address' => '123 Main St, Portland OR',
    'hero_headline' => 'Portland\'s Most Trusted Roofer',
    'hero_sub' => 'Fast estimates. Fair prices.',
    'about' => 'Family owned since 2019...',
    'services' => ['Roofing', 'Gutters', 'Repairs'],
    'service_area' => 'Portland Metro'
];

// Inject data
$populated = inject_contractor_data($template_json, $contractor_data);

// Update database (create new page or update existing)
$new_post_id = 200; // target page ID
$db = $analyzer->getDb();
$stmt = $db->prepare('
    UPDATE wp_postmeta
    SET meta_value = :value
    WHERE post_id = :id AND meta_key = "_elementor_data"
');
$stmt->execute(['value' => $populated, 'id' => $new_post_id]);

echo "Page updated successfully!\n";
```

## Data Structure

### Elementor Hierarchy

```
Section (elType: section)
└── Column (elType: column)
    └── Widget (elType: widget)
        ├── widgetType: heading
        ├── widgetType: text-editor
        ├── widgetType: icon-box
        ├── widgetType: button
        ├── widgetType: image
        └── settings: { ... editable fields ... }
```

### Contractor Data Schema

```json
{
  "name": "Company Name",
  "phone": "(555) 123-4567",
  "email": "contact@example.com",
  "address": "123 Main St, City, State ZIP",
  "hero_headline": "Main headline text",
  "hero_sub": "Subheading or tagline",
  "about": "Company description or about text",
  "services": ["Service 1", "Service 2", "Service 3"],
  "service_area": "Geographic service area"
}
```

### Template Placeholders

Use `{{placeholder}}` syntax in your Elementor templates:

```json
{
  "widgetType": "heading",
  "settings": {
    "title": "{{hero_headline}}"
  }
}
```

The injector will automatically replace:
- `{{name}}` → contractor name
- `{{phone}}` → phone number
- `{{hero_headline}}` → main headline
- etc.

## Analysis Output

The analyzer generates a detailed report showing:

1. **Element Types Found**
   - Section, Column, Widget counts
   - Structure hierarchy

2. **Widget Types Found**
   - heading, text-editor, icon-box, button, image, etc.
   - Settings available for each

3. **Editable Fields**
   - JSON path to each field
   - Field name
   - Sample value

## Field Mapping Rules

The injector uses smart pattern matching:

| Contractor Data | Matches Widget Fields |
|----------------|----------------------|
| hero_headline  | heading, title, main_heading |
| hero_sub       | description, subtitle, tagline |
| phone          | phone, telephone, call |
| name           | company_name, business_name, name |
| address        | address, location, street_address |
| about          | about, about_us, description |
| services       | services, service_list, offerings |

## Advanced Usage

### Custom Mapping Rules

```php
// Modify mapping rules in inject_contractor_data()
$mapping_rules = [
    'custom_field' => ['field_pattern_1', 'field_pattern_2'],
    // ... add your custom mappings
];
```

### Batch Processing

```php
$contractors = [
    ['name' => 'Contractor 1', ...],
    ['name' => 'Contractor 2', ...],
    ['name' => 'Contractor 3', ...]
];

$template = file_get_contents('template.json');

foreach ($contractors as $contractor) {
    $populated = inject_contractor_data($template, $contractor);
    // Save or process each populated template
}
```

## Requirements

- PHP 7.4+
- PDO extension (for database connections)
- MySQL/MariaDB (if connecting to WordPress database)

## Notes

- Works standalone without WordPress installation
- Database connection is optional
- Template placeholders use `{{key}}` syntax
- Services array automatically converted to comma-separated list
- Safe to run on production databases (read-only by default)
