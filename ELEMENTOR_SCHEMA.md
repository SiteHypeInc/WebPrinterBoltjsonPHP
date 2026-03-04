# Elementor Data Structure Schema

## Complete JSON Hierarchy

```
[
  {
    "id": "unique-id",
    "elType": "section",
    "settings": {
      // Section-level settings
      "layout": "boxed|full_width",
      "content_width": "boxed|full_width",
      "gap": "default|narrow|extended|wide|wider",
      "height": "default|min-height|full",
      "structure": "10|20|30|etc",
      "background_background": "classic|gradient|video",
      "background_color": "#hexcolor",
      "background_image": { "url": "..." },
      "padding": { "unit": "px", "top": "", "right": "", ... }
    },
    "elements": [
      {
        "id": "unique-id",
        "elType": "column",
        "settings": {
          // Column-level settings
          "_column_size": 50,
          "_inline_size": 50,
          "background_background": "classic|gradient",
          "padding": { ... }
        },
        "elements": [
          {
            "id": "unique-id",
            "elType": "widget",
            "widgetType": "heading|text-editor|image|button|...",
            "settings": {
              // Widget-specific settings (see below)
            }
          }
        ]
      }
    ]
  }
]
```

## Widget Types & Their Settings

### 1. Heading Widget

```json
{
  "elType": "widget",
  "widgetType": "heading",
  "settings": {
    "title": "Text content",
    "header_size": "h1|h2|h3|h4|h5|h6",
    "align": "left|center|right|justify",
    "title_color": "#hexcolor",
    "typography_typography": "custom",
    "typography_font_size": { "unit": "px", "size": 36 },
    "blend_mode": "normal",
    "link": { "url": "", "is_external": "", "nofollow": "" }
  }
}
```

**Editable Paths:**
- `settings.title` - Main heading text

---

### 2. Text Editor Widget

```json
{
  "elType": "widget",
  "widgetType": "text-editor",
  "settings": {
    "editor": "HTML content with <p>, <strong>, etc.",
    "text_color": "#hexcolor",
    "typography_typography": "custom",
    "align": "left|center|right|justify"
  }
}
```

**Editable Paths:**
- `settings.editor` - Rich text content

---

### 3. Image Widget

```json
{
  "elType": "widget",
  "widgetType": "image",
  "settings": {
    "image": {
      "url": "https://...",
      "id": 123
    },
    "image_size": "thumbnail|medium|large|full",
    "caption": "Image caption text",
    "link_to": "none|file|custom",
    "link": { "url": "...", "is_external": "", "nofollow": "" },
    "align": "left|center|right",
    "hover_animation": "grow|shrink|pulse|..."
  }
}
```

**Editable Paths:**
- `settings.image.url` - Image URL
- `settings.caption` - Caption text
- `settings.link.url` - Link destination

---

### 4. Button Widget

```json
{
  "elType": "widget",
  "widgetType": "button",
  "settings": {
    "text": "Button text",
    "link": {
      "url": "https://...",
      "is_external": "",
      "nofollow": "",
      "custom_attributes": ""
    },
    "align": "left|center|right|justify",
    "size": "xs|sm|md|lg|xl",
    "button_type": "primary|secondary|success|info|warning|danger",
    "icon": "fa fa-...",
    "icon_align": "left|right",
    "button_background_color": "#hexcolor",
    "button_text_color": "#hexcolor",
    "hover_animation": "grow|shrink|pulse|..."
  }
}
```

**Editable Paths:**
- `settings.text` - Button label
- `settings.link.url` - Button destination

---

### 5. Icon Box Widget

```json
{
  "elType": "widget",
  "widgetType": "icon-box",
  "settings": {
    "icon": "fa fa-...",
    "title_text": "Title",
    "description_text": "Description content",
    "link": { "url": "...", "is_external": "", "nofollow": "" },
    "position": "top|left|right",
    "title_size": "h1|h2|h3|h4|h5|h6",
    "icon_color": "#hexcolor",
    "title_color": "#hexcolor",
    "description_color": "#hexcolor"
  }
}
```

**Editable Paths:**
- `settings.title_text` - Title
- `settings.description_text` - Description

---

### 6. Form Widget (Elementor Pro)

```json
{
  "elType": "widget",
  "widgetType": "form",
  "settings": {
    "form_name": "Contact Form",
    "form_fields": [
      {
        "field_type": "text|email|tel|number|url|password|...",
        "field_label": "Label",
        "placeholder": "Placeholder text",
        "required": "yes|no",
        "field_value": "",
        "_id": "field-id"
      }
    ],
    "button_text": "Submit",
    "submit_actions": ["email", "redirect", "..."]
  }
}
```

**Editable Paths:**
- `settings.form_fields[].field_label` - Field labels
- `settings.form_fields[].placeholder` - Placeholders
- `settings.button_text` - Submit button text

---

### 7. Testimonial Widget (Elementor Pro)

```json
{
  "elType": "widget",
  "widgetType": "testimonial",
  "settings": {
    "testimonial_content": "Quote text",
    "testimonial_name": "Author name",
    "testimonial_job": "Author title/job",
    "testimonial_image": {
      "url": "https://...",
      "id": 123
    },
    "alignment": "left|center|right"
  }
}
```

**Editable Paths:**
- `settings.testimonial_content` - Quote
- `settings.testimonial_name` - Author name
- `settings.testimonial_job` - Author title
- `settings.testimonial_image.url` - Author photo

---

### 8. Divider Widget

```json
{
  "elType": "widget",
  "widgetType": "divider",
  "settings": {
    "style": "solid|double|dotted|dashed",
    "weight": { "unit": "px", "size": 1 },
    "width": { "unit": "%", "size": 100 },
    "color": "#hexcolor",
    "gap": { "unit": "px", "size": 15 },
    "align": "left|center|right"
  }
}
```

---

### 9. Spacer Widget

```json
{
  "elType": "widget",
  "widgetType": "spacer",
  "settings": {
    "space": { "unit": "px", "size": 50 }
  }
}
```

---

### 10. HTML Widget

```json
{
  "elType": "widget",
  "widgetType": "html",
  "settings": {
    "html": "<div>Custom HTML code</div>"
  }
}
```

**Editable Paths:**
- `settings.html` - HTML content

---

## Common Field Types Across Widgets

### Link Object
```json
{
  "url": "https://example.com",
  "is_external": "on|",
  "nofollow": "on|",
  "custom_attributes": "data-attr=value"
}
```

### Image Object
```json
{
  "url": "https://example.com/image.jpg",
  "id": 123
}
```

### Dimension Object (Padding, Margin, etc.)
```json
{
  "unit": "px|%|em|rem|vw|vh",
  "top": "10",
  "right": "20",
  "bottom": "10",
  "left": "20",
  "isLinked": true
}
```

### Typography Object
```json
{
  "typography": "custom",
  "font_family": "Arial",
  "font_size": { "unit": "px", "size": 16 },
  "font_weight": "400|700|...",
  "line_height": { "unit": "em", "size": 1.5 },
  "letter_spacing": { "unit": "px", "size": 0 }
}
```

---

## Field Name Patterns for Data Injection

The `inject_contractor_data()` function uses pattern matching on field names. Here's what it looks for:

| Pattern | Matches | Example Keys |
|---------|---------|--------------|
| `hero_headline` | heading, title, main_heading | `settings.title`, `settings.heading` |
| `hero_sub` | description, subtitle, sub_heading, tagline | `settings.description`, `settings.subtitle` |
| `phone` | phone, telephone, call, contact_number | `settings.phone`, `settings.description` (if contains phone) |
| `name` | company_name, business_name, name, title | `settings.title`, `settings.name` |
| `address` | address, location, street_address | `settings.address`, `settings.description` |
| `email` | email, contact_email | `settings.email` |
| `about` | about, about_us, description | `settings.editor`, `settings.description` |
| `services` | services, service_list, offerings | `settings.editor`, `settings.description` |
| `service_area` | service_area, area, coverage | `settings.description` |

---

## Template Placeholder Syntax

You can use placeholders directly in your Elementor templates:

```json
{
  "widgetType": "heading",
  "settings": {
    "title": "{{hero_headline}}"
  }
}
```

The injection function will replace `{{hero_headline}}` with actual data.

---

## Complete Data Flow Example

### 1. Original Template
```json
[{
  "elType": "section",
  "elements": [{
    "elType": "column",
    "elements": [{
      "elType": "widget",
      "widgetType": "heading",
      "settings": {
        "title": "{{hero_headline}}"
      }
    }]
  }]
}]
```

### 2. Contractor Data
```php
$contractor_data = [
    'hero_headline' => 'Portland\'s Most Trusted Roofer'
];
```

### 3. After Injection
```json
[{
  "elType": "section",
  "elements": [{
    "elType": "column",
    "elements": [{
      "elType": "widget",
      "widgetType": "heading",
      "settings": {
        "title": "Portland's Most Trusted Roofer"
      }
    }]
  }]
}]
```

---

## Database Storage

Elementor stores page data in `wp_postmeta` table:

| Column | Value |
|--------|-------|
| `meta_id` | Auto-increment ID |
| `post_id` | The WordPress post/page ID |
| `meta_key` | `_elementor_data` |
| `meta_value` | JSON string (entire structure above) |

**Other related meta keys:**
- `_elementor_edit_mode` - "builder" when using Elementor
- `_elementor_version` - Elementor version used
- `_elementor_template_type` - "wp-post", "wp-page", etc.
- `_elementor_pro_version` - Elementor Pro version (if applicable)

---

## Query Examples

### Fetch all Elementor pages
```sql
SELECT p.ID, p.post_title, pm.meta_value
FROM wp_posts p
INNER JOIN wp_postmeta pm ON p.ID = pm.post_id
WHERE pm.meta_key = '_elementor_data'
AND p.post_status = 'publish';
```

### Find pages using specific widget
```sql
SELECT post_id
FROM wp_postmeta
WHERE meta_key = '_elementor_data'
AND meta_value LIKE '%"widgetType":"heading"%';
```

### Count widgets by type
```sql
SELECT
  SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, '"widgetType":"', -1), '"', 1) as widget,
  COUNT(*) as count
FROM wp_postmeta
WHERE meta_key = '_elementor_data'
GROUP BY widget;
```

---

## Tips for Automation

1. **Always backup** before bulk updates
2. **Test on staging** environment first
3. **Clear Elementor cache** after programmatic updates:
   ```php
   delete_post_meta($post_id, '_elementor_css');
   ```
4. **Regenerate CSS** by visiting the page in Elementor editor
5. **Update page modification time**:
   ```php
   wp_update_post(['ID' => $post_id, 'post_modified' => current_time('mysql')]);
   ```
