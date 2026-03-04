# Elementor Quick Reference Guide

## JSON Path Cheat Sheet

### Common Widget Paths

#### Heading Widget
```
$[0]['elements'][0]['elements'][0]['settings']['title']
```

#### Text Editor Widget
```
$[0]['elements'][0]['elements'][1]['settings']['editor']
```

#### Button Widget
```
$[0]['elements'][0]['elements'][2]['settings']['text']
$[0]['elements'][0]['elements'][2]['settings']['link']['url']
```

#### Image Widget
```
$[0]['elements'][0]['elements'][3]['settings']['image']['url']
$[0]['elements'][0]['elements'][3]['settings']['caption']
```

#### Icon Box Widget
```
$[0]['elements'][0]['elements'][4]['settings']['title_text']
$[0]['elements'][0]['elements'][4]['settings']['description_text']
```

---

## Typical Page Structures

### Single Section Hero

```
Section 0
  └── Column 0
      ├── Widget 0: Heading (Hero Headline)
      ├── Widget 1: Text Editor (Subheadline)
      └── Widget 2: Button (CTA)
```

**JSON Paths:**
- Headline: `$[0]['elements'][0]['elements'][0]['settings']['title']`
- Subhead: `$[0]['elements'][0]['elements'][1]['settings']['editor']`
- CTA Text: `$[0]['elements'][0]['elements'][2]['settings']['text']`
- CTA Link: `$[0]['elements'][0]['elements'][2]['settings']['link']['url']`

---

### Two-Column About Section

```
Section 1
  ├── Column 0 (Text)
  │   ├── Widget 0: Heading (About Title)
  │   └── Widget 1: Text Editor (About Content)
  └── Column 1 (Image)
      └── Widget 0: Image (About Photo)
```

**JSON Paths:**
- Title: `$[1]['elements'][0]['elements'][0]['settings']['title']`
- Content: `$[1]['elements'][0]['elements'][1]['settings']['editor']`
- Image: `$[1]['elements'][1]['elements'][0]['settings']['image']['url']`

---

### Three-Column Services Section

```
Section 2
  ├── Column 0
  │   ├── Widget 0: Icon Box (Service 1)
  ├── Column 1
  │   ├── Widget 0: Icon Box (Service 2)
  └── Column 2
      └── Widget 0: Icon Box (Service 3)
```

**JSON Paths:**
- Service 1 Title: `$[2]['elements'][0]['elements'][0]['settings']['title_text']`
- Service 1 Desc: `$[2]['elements'][0]['elements'][0]['settings']['description_text']`
- Service 2 Title: `$[2]['elements'][1]['elements'][0]['settings']['title_text']`
- Service 2 Desc: `$[2]['elements'][1]['elements'][0]['settings']['description_text']`
- Service 3 Title: `$[2]['elements'][2]['elements'][0]['settings']['title_text']`
- Service 3 Desc: `$[2]['elements'][2]['elements'][0]['settings']['description_text']`

---

### Contact Section

```
Section 3
  └── Column 0
      ├── Widget 0: Heading (Contact Title)
      ├── Widget 1: Icon Box (Phone)
      ├── Widget 2: Icon Box (Email)
      └── Widget 3: Icon Box (Address)
```

**JSON Paths:**
- Title: `$[3]['elements'][0]['elements'][0]['settings']['title']`
- Phone: `$[3]['elements'][0]['elements'][1]['settings']['description_text']`
- Email: `$[3]['elements'][0]['elements'][2]['settings']['description_text']`
- Address: `$[3]['elements'][0]['elements'][3]['settings']['description_text']`

---

## Field Name Reference

### Fields that Accept Text

| Widget Type | Field Name | Content Type |
|------------|------------|--------------|
| heading | `title` | Plain text or HTML |
| text-editor | `editor` | Rich HTML content |
| button | `text` | Plain text |
| image | `caption` | Plain text |
| icon-box | `title_text` | Plain text |
| icon-box | `description_text` | Plain text or HTML |
| form | `form_fields[].field_label` | Plain text |
| form | `form_fields[].placeholder` | Plain text |
| testimonial | `testimonial_content` | Plain text |
| testimonial | `testimonial_name` | Plain text |
| testimonial | `testimonial_job` | Plain text |
| html | `html` | Raw HTML |

### Fields that Accept URLs

| Widget Type | Field Name | Purpose |
|------------|------------|---------|
| button | `link.url` | Button destination |
| image | `image.url` | Image source |
| image | `link.url` | Image link destination |
| icon-box | `link.url` | Icon box link |
| heading | `link.url` | Heading link |

### Fields that Accept Colors

| Widget Type | Field Name | Purpose |
|------------|------------|---------|
| heading | `title_color` | Text color |
| text-editor | `text_color` | Text color |
| button | `button_background_color` | Background |
| button | `button_text_color` | Text color |
| icon-box | `icon_color` | Icon color |
| icon-box | `title_color` | Title color |
| icon-box | `description_color` | Description color |

---

## Common Injection Patterns

### Pattern 1: Simple Text Replacement

```php
// Find this in template
"title": "{{company_name}}"

// Becomes this after injection
"title": "Cascade Roofing Solutions"
```

### Pattern 2: Array to Comma-Separated List

```php
// Input
$contractor_data['services'] = ['Roofing', 'Gutters', 'Repairs'];

// Template
"editor": "We offer: {{services}}"

// Output
"editor": "We offer: Roofing, Gutters, Repairs"
```

### Pattern 3: Nested Field Replacement

```php
// Template
"link": {
  "url": "tel:{{phone}}",
  "is_external": ""
}

// Output
"link": {
  "url": "tel:(503) 555-0187",
  "is_external": ""
}
```

### Pattern 4: Conditional Content

```php
// Template
"editor": "Serving {{service_area}} since 2019"

// If service_area exists
"editor": "Serving Portland Metro since 2019"

// If service_area is empty
"editor": "Serving  since 2019" // ⚠️ leaves gap
```

**Better approach:**
```php
"editor": "{{about}}"
// Build complete sentence in data
$contractor_data['about'] = "Serving Portland Metro since 2019...";
```

---

## Database Update Scripts

### Update Single Page

```php
<?php
$db = new PDO(...);
$populated_json = inject_contractor_data($template, $data);

$stmt = $db->prepare('
    UPDATE wp_postmeta
    SET meta_value = :value
    WHERE post_id = :id
    AND meta_key = "_elementor_data"
');

$stmt->execute([
    'value' => $populated_json,
    'id' => 123
]);
```

### Batch Update Multiple Pages

```php
<?php
$contractors = [...]; // Array of contractor data
$template = file_get_contents('template.json');

foreach ($contractors as $index => $contractor) {
    $post_id = 200 + $index;
    $populated = inject_contractor_data($template, $contractor);

    $stmt->execute([
        'value' => $populated,
        'id' => $post_id
    ]);
}
```

### Insert New Page with Elementor Data

```php
<?php
// 1. Create the post
$post_id = wp_insert_post([
    'post_title' => $contractor_data['name'],
    'post_type' => 'page',
    'post_status' => 'draft',
]);

// 2. Add Elementor data
$populated = inject_contractor_data($template, $contractor_data);
update_post_meta($post_id, '_elementor_data', $populated);
update_post_meta($post_id, '_elementor_edit_mode', 'builder');
```

---

## Debugging Tips

### View Raw JSON

```php
<?php
$pages = $analyzer->fetchElementorData(123);
echo json_encode(
    json_decode($pages[0]['meta_value']),
    JSON_PRETTY_PRINT
);
```

### Find Widget by Type

```php
<?php
function findWidgets($data, $widget_type) {
    $found = [];
    array_walk_recursive($data, function($value, $key) use ($widget_type, &$found) {
        if ($key === 'widgetType' && $value === $widget_type) {
            $found[] = $value;
        }
    });
    return $found;
}

$data = json_decode($json, true);
$headings = findWidgets($data, 'heading');
```

### Validate JSON After Injection

```php
<?php
$populated = inject_contractor_data($template, $data);
$decoded = json_decode($populated);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Error: " . json_last_error_msg();
} else {
    echo "Valid JSON!";
}
```

### Compare Before/After

```php
<?php
$before = json_decode($template, true);
$after = json_decode($populated, true);

// Count widgets
$before_count = count_widgets($before);
$after_count = count_widgets($after);

echo "Widgets before: $before_count\n";
echo "Widgets after: $after_count\n";
```

---

## Common Gotchas

### 1. Special Characters
```php
// ❌ Wrong - will break JSON
$data['hero_headline'] = "Portland's Best";

// ✓ Correct - escape or use json_encode
$data['hero_headline'] = "Portland\'s Best";
// or
$data['hero_headline'] = 'Portland\'s Best';
```

### 2. Empty Arrays vs Empty Objects
```php
// Arrays become [] in JSON
$settings = [];

// Objects should be (object)[] or new stdClass
$settings = (object)[];
```

### 3. Numeric Keys
```php
// Elements array MUST have numeric keys
$elements = [
    0 => [...],
    1 => [...],
    2 => [...]
];

// NOT
$elements = [
    'first' => [...],
    'second' => [...]
];
```

### 4. Cache Issues

After updating Elementor data programmatically, clear cache:

```php
delete_post_meta($post_id, '_elementor_css');
delete_transient('elementor_global_css');
```

---

## Testing Checklist

- [ ] Verify JSON is valid after injection
- [ ] Check all placeholders were replaced
- [ ] Confirm no empty fields left
- [ ] Test on staging site first
- [ ] Clear Elementor cache
- [ ] View page in Elementor editor
- [ ] Preview on frontend
- [ ] Test on mobile devices
- [ ] Check console for JavaScript errors
- [ ] Verify links work correctly

---

## Performance Tips

1. **Use transactions for batch updates**
   ```php
   $db->beginTransaction();
   foreach ($contractors as $contractor) {
       // updates...
   }
   $db->commit();
   ```

2. **Prepare statements outside loops**
   ```php
   $stmt = $db->prepare('UPDATE...');
   foreach ($contractors as $contractor) {
       $stmt->execute([...]);
   }
   ```

3. **Cache template**
   ```php
   $template = file_get_contents('template.json');
   // Use same template for all contractors
   foreach ($contractors as $contractor) {
       $populated = inject_contractor_data($template, $contractor);
   }
   ```

4. **Limit data fetching**
   ```sql
   -- Only fetch needed columns
   SELECT post_id, meta_value FROM wp_postmeta
   WHERE meta_key = '_elementor_data'
   LIMIT 100;
   ```
