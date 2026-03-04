# Complete Workflow Guide

## Visual Process Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    STEP 1: TEMPLATE CREATION                     │
│                                                                  │
│  WordPress Site                                                  │
│  └── Create page in Elementor                                   │
│      ├── Add sections, columns, widgets                         │
│      ├── Use {{placeholders}} for dynamic content               │
│      └── Export or note the post_id                             │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                 STEP 2: EXTRACT & ANALYZE                        │
│                                                                  │
│  $ php elementor-analyzer.php                                   │
│                                                                  │
│  ElementorAnalyzer                                              │
│  ├── Connect to WordPress DB                                    │
│  ├── Fetch _elementor_data from wp_postmeta                     │
│  ├── Parse JSON structure                                       │
│  └── Generate mapping report                                    │
│      ├── Element types (section, column, widget)                │
│      ├── Widget types (heading, text-editor, etc.)              │
│      └── Editable fields with JSON paths                        │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                   STEP 3: PREPARE DATA                           │
│                                                                  │
│  contractor_data = [                                            │
│    'name' => 'Cascade Roofing',                                 │
│    'phone' => '(503) 555-0187',                                 │
│    'hero_headline' => 'Best Roofer',                            │
│    ...                                                          │
│  ]                                                              │
│                                                                  │
│  Sources:                                                       │
│  ├── CSV file                                                   │
│  ├── Database                                                   │
│  ├── API                                                        │
│  └── Manual array                                               │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                    STEP 4: INJECT DATA                           │
│                                                                  │
│  populated = inject_contractor_data(template, data)             │
│                                                                  │
│  Process:                                                       │
│  1. Parse template JSON                                         │
│  2. Walk through structure recursively                          │
│  3. Replace {{placeholders}}                                    │
│  4. Match field name patterns                                   │
│  5. Convert arrays to strings                                   │
│  6. Return populated JSON                                       │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                 STEP 5: SAVE TO DATABASE                         │
│                                                                  │
│  UPDATE wp_postmeta                                             │
│  SET meta_value = populated_json                                │
│  WHERE post_id = target_id                                      │
│    AND meta_key = '_elementor_data'                             │
│                                                                  │
│  Also update:                                                   │
│  ├── Post title (wp_posts)                                      │
│  ├── Post status (draft/publish)                                │
│  └── Clear _elementor_css cache                                 │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                      STEP 6: VERIFY                              │
│                                                                  │
│  1. Login to WordPress admin                                    │
│  2. Navigate to Pages                                           │
│  3. Edit page with Elementor                                    │
│  4. Verify all data populated correctly                         │
│  5. Preview on frontend                                         │
│  6. Publish when ready                                          │
└─────────────────────────────────────────────────────────────────┘
```

---

## Detailed Step-by-Step Instructions

### STEP 1: Create Template in WordPress

#### 1.1 Design Your Template Page

1. Login to WordPress admin
2. Go to Pages → Add New
3. Click "Edit with Elementor"
4. Design your page structure:
   - Add sections for hero, about, services, contact, etc.
   - Add columns within sections
   - Add widgets (headings, text, buttons, icons, etc.)

#### 1.2 Add Placeholders

Replace dynamic content with placeholders:

**Before:**
```
Heading Widget: "Portland's Most Trusted Roofer"
```

**After:**
```
Heading Widget: "{{hero_headline}}"
```

**Common Placeholders:**
- `{{name}}` - Company name
- `{{phone}}` - Phone number
- `{{email}}` - Email address
- `{{address}}` - Street address
- `{{hero_headline}}` - Main hero text
- `{{hero_sub}}` - Subheading
- `{{about}}` - About section
- `{{services}}` - Services list
- `{{service_area}}` - Coverage area

#### 1.3 Note the Post ID

After saving, note the post_id from URL:
```
https://yoursite.com/wp-admin/post.php?post=100&action=edit
                                           ^^^
                                      This is post_id
```

---

### STEP 2: Extract and Analyze Template

#### 2.1 Configure Database Connection

Edit `example-usage.php` or create your own script:

```php
$db_config = [
    'host' => 'localhost',        // Database host
    'dbname' => 'wordpress_db',   // Database name
    'username' => 'wp_user',      // Database user
    'password' => 'wp_password'   // Database password
];
```

#### 2.2 Fetch Template Data

```php
$analyzer = new ElementorAnalyzer();
$analyzer->connectDatabase(
    $db_config['host'],
    $db_config['dbname'],
    $db_config['username'],
    $db_config['password']
);

// Fetch your template (use post_id from Step 1.3)
$pages = $analyzer->fetchElementorData(100);
$template_json = $pages[0]['meta_value'];
```

#### 2.3 Analyze Structure

```php
$analysis = $analyzer->analyzeStructure($template_json);
$analyzer->generateReport();
```

This generates a report showing:
- All element types found
- All widget types and their settings
- All editable fields with JSON paths

**Review the report** to understand your template structure.

---

### STEP 3: Prepare Contractor Data

#### Option A: Manual Array

```php
$contractor_data = [
    'name' => 'Cascade Roofing Solutions',
    'phone' => '(503) 555-0187',
    'email' => 'info@cascaderoofing.com',
    'address' => '123 Main St, Portland OR 97201',
    'hero_headline' => 'Portland\'s Most Trusted Roofer',
    'hero_sub' => 'Fast estimates. Fair prices.',
    'about' => 'Family owned since 2019...',
    'services' => ['Roofing', 'Gutters', 'Repairs'],
    'service_area' => 'Portland Metro'
];
```

#### Option B: Load from CSV

```php
function load_contractors_from_csv($file) {
    $contractors = [];
    $handle = fopen($file, 'r');
    $headers = fgetcsv($handle); // First row as keys

    while ($row = fgetcsv($handle)) {
        $contractor = array_combine($headers, $row);

        // Convert services string to array
        if (isset($contractor['services'])) {
            $contractor['services'] = explode(',', $contractor['services']);
        }

        $contractors[] = $contractor;
    }

    fclose($handle);
    return $contractors;
}

$contractors = load_contractors_from_csv('contractors.csv');
```

**CSV Format:**
```csv
name,phone,address,hero_headline,hero_sub,about,services,service_area
Cascade Roofing,(503) 555-0187,123 Main St,"Portland's Roofer","Fast estimates","Family owned","Roofing,Gutters,Repairs","Portland Metro"
```

#### Option C: Load from Database

```php
$db = new PDO('mysql:host=localhost;dbname=crm', 'user', 'pass');
$stmt = $db->query('SELECT * FROM contractors');
$contractors = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($contractors as &$contractor) {
    // Transform database fields to match template placeholders
    $contractor['hero_headline'] = $contractor['tagline'];
    $contractor['services'] = json_decode($contractor['services_json']);
}
```

---

### STEP 4: Inject Data into Template

```php
// Single contractor
$populated_json = inject_contractor_data($template_json, $contractor_data);

// Multiple contractors
$results = [];
foreach ($contractors as $contractor) {
    $results[] = inject_contractor_data($template_json, $contractor);
}
```

**What happens during injection:**

1. Template parsed from JSON string to PHP array
2. Recursive walk through entire structure
3. Find all `{{placeholder}}` patterns and replace with data
4. Find settings fields matching contractor data keys
5. Convert arrays to comma-separated strings where needed
6. Return as JSON string ready for database

---

### STEP 5: Save to WordPress Database

#### 5.1 Update Existing Page

```php
$target_post_id = 200; // The page you want to update

$stmt = $db->prepare('
    UPDATE wp_postmeta
    SET meta_value = :value
    WHERE post_id = :id
    AND meta_key = "_elementor_data"
');

$stmt->execute([
    'value' => $populated_json,
    'id' => $target_post_id
]);
```

#### 5.2 Create New Page

```php
// Insert into wp_posts
$stmt = $db->prepare('
    INSERT INTO wp_posts (
        post_title,
        post_content,
        post_status,
        post_type,
        post_author
    ) VALUES (
        :title,
        "",
        "draft",
        "page",
        1
    )
');

$stmt->execute([
    'title' => $contractor_data['name'] . ' - Home'
]);

$new_post_id = $db->lastInsertId();

// Insert Elementor data
$stmt = $db->prepare('
    INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
    VALUES
        (:id, "_elementor_data", :data),
        (:id, "_elementor_edit_mode", "builder"),
        (:id, "_elementor_version", "3.16.0")
');

$stmt->execute([
    'id' => $new_post_id,
    'data' => $populated_json
]);
```

#### 5.3 Batch Process Multiple Contractors

```php
$db->beginTransaction();

try {
    foreach ($contractors as $index => $contractor) {
        $target_post_id = 200 + $index;
        $populated = inject_contractor_data($template_json, $contractor);

        $stmt->execute([
            'value' => $populated,
            'id' => $target_post_id
        ]);
    }

    $db->commit();
    echo "Successfully processed " . count($contractors) . " contractors\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
```

---

### STEP 6: Verify and Publish

#### 6.1 Check in WordPress Admin

1. Login to WordPress
2. Go to Pages
3. Find your newly created/updated pages
4. Click "Edit with Elementor"
5. Verify all content populated correctly

#### 6.2 Clear Cache

```php
// Clear Elementor CSS cache
delete_post_meta($post_id, '_elementor_css');

// Clear WordPress cache (if using cache plugin)
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
}
```

#### 6.3 Frontend Testing

1. Click "Preview" in Elementor
2. Check on desktop and mobile
3. Verify all links work (especially phone/email)
4. Test forms if included
5. Check images load correctly

#### 6.4 Publish

```php
// Update post status to publish
$stmt = $db->prepare('
    UPDATE wp_posts
    SET post_status = "publish"
    WHERE ID = :id
');

$stmt->execute(['id' => $post_id]);
```

Or manually click "Publish" in WordPress admin.

---

## Complete Example Script

Here's a complete script that does everything:

```php
<?php
require_once 'elementor-analyzer.php';

// 1. Configuration
$db_config = [
    'host' => 'localhost',
    'dbname' => 'wordpress_db',
    'username' => 'wp_user',
    'password' => 'wp_password'
];

$template_post_id = 100;
$contractors = load_contractors_from_csv('contractors.csv');

// 2. Connect and fetch template
$analyzer = new ElementorAnalyzer();
$analyzer->connectDatabase(...$db_config);
$pages = $analyzer->fetchElementorData($template_post_id);
$template = $pages[0]['meta_value'];

// 3. Process each contractor
$db = $analyzer->getDb();
$db->beginTransaction();

foreach ($contractors as $i => $contractor) {
    echo "Processing: {$contractor['name']}\n";

    // Inject data
    $populated = inject_contractor_data($template, $contractor);

    // Create new page
    $stmt = $db->prepare('INSERT INTO wp_posts ...');
    $stmt->execute([...]);
    $new_id = $db->lastInsertId();

    // Add Elementor data
    $stmt = $db->prepare('INSERT INTO wp_postmeta ...');
    $stmt->execute(['id' => $new_id, 'data' => $populated]);

    echo "  ✓ Created page ID: $new_id\n";
}

$db->commit();
echo "\nAll done! Created " . count($contractors) . " pages.\n";
```

---

## Troubleshooting Common Issues

### Issue: "Database connection failed"

Check:
- Database credentials are correct
- Database allows connections from your IP
- Database user has proper permissions

### Issue: "Template page not found"

Check:
- Post ID is correct
- Page was created with Elementor (not classic editor)
- wp_postmeta has `_elementor_data` entry for that post_id

### Issue: "Placeholders not replaced"

Check:
- Placeholder syntax is exact: `{{key}}` not `{{ key }}`
- Contractor data array has matching keys
- Keys are case-sensitive

### Issue: "Changes not visible in Elementor"

Solution:
- Clear Elementor cache
- Hard refresh browser (Ctrl+Shift+R)
- Regenerate CSS in Elementor

### Issue: "Page shows blank"

Check:
- JSON is valid (use json_decode to test)
- No syntax errors in populated JSON
- Elementor version compatibility

---

## Best Practices

1. Always test on staging first
2. Backup database before bulk operations
3. Use transactions for batch updates
4. Validate JSON after injection
5. Clear caches after updates
6. Keep template simple initially
7. Test with one contractor before batch processing
8. Monitor database size with large batches
9. Use prepared statements for security
10. Log all operations for debugging
