# Elementor Reverse Engineering & Automation Suite

Complete toolkit for reverse-engineering Elementor data structures and automating contractor site generation.

## What This Does

This suite provides everything needed to:

1. Connect to WordPress databases and extract Elementor page data
2. Analyze and map Elementor's JSON structure
3. Identify all editable fields and their JSON paths
4. Inject contractor data into templates automatically
5. Create unlimited contractor sites from a single template

## Files Included

| File | Purpose |
|------|---------|
| `elementor-analyzer.php` | Main analyzer class + injection function (ready to run) |
| `example-usage.php` | Complete workflow example with database |
| `test-injection.php` | Test suite for data injection |
| `ELEMENTOR_README.md` | Full usage documentation |
| `ELEMENTOR_SCHEMA.md` | Complete Elementor structure reference |
| `QUICK_REFERENCE.md` | JSON paths cheat sheet |

## Quick Start

### 1. Run the Demo (No Database Required)

```bash
php elementor-analyzer.php
```

This will:
- Analyze a sample Elementor template
- Map all element types and editable fields
- Inject contractor data
- Show before/after comparison

### 2. Test Data Injection

```bash
php test-injection.php
```

Runs 8 comprehensive tests covering:
- Placeholder replacement
- Array handling
- Special characters
- Nested settings
- Complex templates

### 3. Use With Real WordPress Database

```php
<?php
require_once 'elementor-analyzer.php';

$analyzer = new ElementorAnalyzer();

// Connect to database
$analyzer->connectDatabase('localhost', 'wp_db', 'user', 'pass');

// Fetch template
$pages = $analyzer->fetchElementorData(100); // template post_id
$template = $pages[0]['meta_value'];

// Inject contractor data
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

$populated = inject_contractor_data($template, $contractor_data);

// Update database
$db = $analyzer->getDb();
$stmt = $db->prepare('
    UPDATE wp_postmeta
    SET meta_value = :value
    WHERE post_id = :id AND meta_key = "_elementor_data"
');
$stmt->execute(['value' => $populated, 'id' => 200]);

echo "Site created successfully!\n";
```

## How It Works

### Elementor Structure

```
Section (container)
└── Column (layout)
    └── Widget (content)
        └── Settings (editable fields)
```

### Data Injection Methods

**Method 1: Template Placeholders** (Recommended)
```json
{
  "settings": {
    "title": "{{hero_headline}}"
  }
}
```

**Method 2: Field Name Pattern Matching**
```json
{
  "settings": {
    "phone": "auto-replaced-if-contractor_data-has-phone-key"
  }
}
```

## Contractor Data Schema

```php
[
    'name' => 'Business name',
    'phone' => 'Phone number',
    'email' => 'Email address',
    'address' => 'Street address',
    'hero_headline' => 'Main hero text',
    'hero_sub' => 'Subheading/tagline',
    'about' => 'About section content',
    'services' => ['Service 1', 'Service 2', ...],
    'service_area' => 'Geographic coverage'
]
```

## Supported Widget Types

Currently supports analysis and injection for:

- `heading` - Headings (h1-h6)
- `text-editor` - Rich text content
- `image` - Images with captions
- `button` - Call-to-action buttons
- `icon-box` - Icon boxes with title/description
- `form` - Contact forms (Elementor Pro)
- `testimonial` - Testimonials (Elementor Pro)
- `divider` - Horizontal dividers
- `spacer` - Spacing elements
- `html` - Custom HTML

## Common Use Cases

### Use Case 1: Generate 100 Contractor Sites

```php
$contractors = load_contractors_from_csv('contractors.csv');
$template = file_get_contents('template.json');

foreach ($contractors as $i => $contractor) {
    $populated = inject_contractor_data($template, $contractor);
    save_to_database($populated, 200 + $i);
}
```

### Use Case 2: A/B Test Different Headlines

```php
$headlines = [
    'Portland\'s Most Trusted Roofer',
    'Expert Roofing Since 2019',
    'Fast, Fair, Professional Roofing'
];

foreach ($headlines as $i => $headline) {
    $data = $base_contractor_data;
    $data['hero_headline'] = $headline;
    $populated = inject_contractor_data($template, $data);
    // Save variant
}
```

### Use Case 3: Batch Update Existing Sites

```php
// Update all contractor pages with new phone number
$contractor_pages = get_all_contractor_pages();

foreach ($contractor_pages as $page) {
    $json = get_elementor_data($page->ID);
    $updated_data = ['phone' => '(503) NEW-NUMB'];
    $populated = inject_contractor_data($json, $updated_data);
    update_elementor_data($page->ID, $populated);
}
```

## Requirements

- PHP 7.4 or higher
- PDO extension (for database connections)
- MySQL/MariaDB (if connecting to WordPress)
- No WordPress installation required for analysis

## Production Checklist

Before deploying to production:

- [ ] Test on staging database first
- [ ] Backup production database
- [ ] Verify all placeholders are replaced
- [ ] Check JSON validity after injection
- [ ] Clear Elementor cache after updates
- [ ] Test one page in Elementor editor
- [ ] Preview on frontend
- [ ] Verify mobile responsiveness
- [ ] Check all links work
- [ ] Monitor server resources during batch operations

## Troubleshooting

### Issue: Placeholders not replaced

**Solution:** Check that placeholder syntax is exact: `{{key}}` not `{{ key }}` or `{key}`

### Issue: JSON invalid after injection

**Solution:** Check for special characters. Use proper escaping:
```php
$data['text'] = addslashes($text);
// or
$data['text'] = json_encode($text);
```

### Issue: Changes not showing in Elementor

**Solution:** Clear Elementor cache:
```php
delete_post_meta($post_id, '_elementor_css');
```

### Issue: Database connection fails

**Solution:** Verify credentials and that database allows remote connections:
```sql
GRANT ALL ON wordpress_db.* TO 'user'@'%' IDENTIFIED BY 'password';
FLUSH PRIVILEGES;
```

## Performance Tips

1. **Use prepared statements** for batch operations
2. **Cache the template** - don't re-read for each contractor
3. **Use transactions** for multiple updates
4. **Limit database queries** - fetch once, process in memory
5. **Consider async processing** for 100+ sites

## Security Considerations

1. Never commit database credentials to version control
2. Use environment variables for sensitive data
3. Validate and sanitize all contractor input
4. Use prepared statements to prevent SQL injection
5. Restrict database user permissions (SELECT, UPDATE only)
6. Consider rate limiting for public-facing implementations

## Next Steps

1. Export an Elementor template from your WordPress site
2. Save it as JSON
3. Run analyzer to understand structure
4. Create placeholders in template
5. Test with sample contractor data
6. Deploy to production

## Support

For issues or questions:
1. Check the documentation files included
2. Review test-injection.php for examples
3. Examine ELEMENTOR_SCHEMA.md for structure details

## License

This is a standalone utility script. Use freely for your automation needs.

---

**Created for automated Elementor site generation**
Version 1.0 - Complete reverse engineering toolkit
