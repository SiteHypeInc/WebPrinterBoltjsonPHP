# Elementor Contact Page Template

A professional contact page template for contractor websites with comprehensive contact information, forms, and emergency service options.

## Purpose

This template is designed to:
- Provide multiple ways for customers to contact the business
- Display business hours and service area information
- Highlight emergency service availability (if applicable)
- Include a functional contact form
- Show service area via map integration
- Build trust through comprehensive contact information

## Template Sections

### 1. Hero Section
- **Headline**: "Contact Us"
- **Subtext**: Welcoming message to encourage contact
- **Design**: Green gradient background with centered text
- **Purpose**: Welcoming and inviting introduction

### 2. Contact Info Section
Three-column layout with clickable contact methods:

**Column 1 - Phone**
- Phone icon
- "Phone" label
- Clickable phone number (tel: link)
- "Click to call" CTA

**Column 2 - Email**
- Email icon
- "Email" label
- Clickable email address (mailto: link)
- "Send us a message" CTA

**Column 3 - Address**
- Location icon
- "Address" label
- Physical address or service area
- "Visit our office" text

### 3. Contact Form Section
- **Title**: "Send Us a Message"
- **Form Fields**:
  - Name (required)
  - Phone (required)
  - Email (required)
  - Message (required textarea)
  - Submit button
- **Design**: Styled HTML form with validation
- **Purpose**: Primary lead capture method

### 4. Hours of Operation Section
- Clock icon
- "Hours of Operation" heading
- Business hours text
- Optional note (e.g., "Closed on major holidays")
- **Purpose**: Set customer expectations

### 5. Service Area Map Section
- "Service Area" heading
- Service area description
- Map embed placeholder or Google Maps iframe
- **Purpose**: Show geographic coverage

### 6. Emergency CTA Section
Conditional section that adapts based on emergency service availability:

**When emergency = true:**
- Red gradient background
- Warning icon
- "Emergency Service Available 24/7" heading
- Large emergency phone number (clickable)
- "Call now for immediate assistance" text

**When emergency = false:**
- "Standard Service Hours Apply" heading
- Regular phone number displayed
- "Contact us during business hours" text

## Data Structure

```php
$data = [
    'name' => 'Company Name',                    // Required: Company name
    'phone' => '(503) 555-0142',                // Required: Formatted phone
    'email' => 'info@company.com',              // Required: Valid email
    'address' => 'Portland, OR',                // Required: Address/service area
    'hours' => 'Mon-Fri 7am-6pm, Sat 8am-4pm', // Required: Hours text
    'emergency' => true,                         // Required: Emergency service bool
    'emergency_phone' => '(503) 555-0143',      // Required if emergency = true
    'map_embed' => '<iframe src="...">',        // Optional: Map embed code
    'hours_note' => 'Closed Sundays'            // Optional: Additional hours info
];
```

### Required Fields

All data arrays must include:
- `name` - Company name (string)
- `phone` - Phone number with formatting (string)
- `email` - Valid email address (validated)
- `address` - Physical address or service area (string)
- `hours` - Hours of operation (string)
- `emergency` - Emergency service availability (boolean)

### Conditional Requirements

- If `emergency` is `true`, `emergency_phone` is **required**
- If `emergency` is `false`, `emergency_phone` is **not used**

### Optional Fields

- `map_embed` - Custom map iframe code (defaults to placeholder if not provided)
- `hours_note` - Additional hours information (defaults to "Closed on major holidays")

## Usage

### Basic Implementation

```php
<?php
// Load the template
$template = json_decode(
    file_get_contents('elementor-contact-template.json'),
    true
);

// Prepare your data
$data = [
    'name' => 'Pyramid Heating & Cooling',
    'phone' => '(503) 555-0142',
    'email' => 'info@pyramidheating.com',
    'address' => 'Portland, OR',
    'hours' => 'Mon-Fri 7am-6pm, Sat 8am-4pm',
    'emergency' => true,
    'emergency_phone' => '(503) 555-0143',
    'map_embed' => '<iframe src="https://maps.google.com/maps?q=Portland+OR" width="100%" height="450"></iframe>',
    'hours_note' => 'Closed Sundays and major holidays'
];

// Inject the data
require_once 'elementor-contact-injector.php';
$populated = inject_contact_data($template, $data);

// Create the page in WordPress
$page_id = create_contact_page($populated, 'Contact Us');
echo "Contact page created with ID: {$page_id}";
```

### Without Emergency Service

```php
$data = [
    'name' => 'Standard Plumbing',
    'phone' => '(503) 555-9999',
    'email' => 'contact@standardplumbing.com',
    'address' => 'Beaverton, OR',
    'hours' => 'Mon-Fri 8am-5pm',
    'emergency' => false  // No emergency phone needed
];

$populated = inject_contact_data($template, $data);
```

### Using Google Maps Generator

```php
// Generate Google Maps embed automatically
$map_code = generate_google_maps_embed('123 Main St, Portland, OR 97201');

$data = [
    // ... other fields
    'map_embed' => $map_code
];
```

### Using Map Placeholder

If you don't have a map embed code ready:

```php
$data = [
    // ... other required fields
    // Omit map_embed to use default placeholder
];

// The injector will use: <!-- MAP_EMBED_CODE_HERE -->
// Replace later in Elementor editor
```

## Template Placeholders

| Placeholder | Purpose | Example |
|------------|---------|---------|
| `{{name}}` | Company name | Pyramid Heating & Cooling |
| `{{phone}}` | Display phone number | (503) 555-0142 |
| `{{phone_raw}}` | Phone for tel: links | 5035550142 |
| `{{email}}` | Email address | info@company.com |
| `{{address}}` | Physical address | Portland, OR |
| `{{hours}}` | Business hours | Mon-Fri 7am-6pm |
| `{{hours_note}}` | Hours additional info | Closed major holidays |
| `{{map_embed}}` | Map iframe code | `<iframe...>` |
| `{{emergency_title}}` | Emergency section title | Emergency Service 24/7 |
| `{{emergency_phone_raw}}` | Emergency tel: link | 5035550143 |
| `{{emergency_phone_display}}` | Emergency phone display | (503) 555-0143 |
| `{{emergency_subtext}}` | Emergency section text | Call now for assistance |

## Validation

The injector validates:

1. **Required fields** - All required fields must be present
2. **Email format** - Email must be valid format
3. **Emergency phone** - Required when `emergency = true`
4. **Template structure** - All placeholders must exist

Throws `InvalidArgumentException` if validation fails.

## Functions

### `inject_contact_data($template, $data)`

Main injection function.

- **Parameters**: Template array, data array
- **Returns**: Populated template array
- **Throws**: `InvalidArgumentException` on validation failure

### `validate_contact_template($template)`

Checks that template has all required placeholders.

- **Parameters**: Template array
- **Returns**: `true` if valid
- **Throws**: `InvalidArgumentException` if invalid

### `find_remaining_contact_placeholders($template)`

Finds any unreplaced `{{placeholders}}` in template.

- **Parameters**: Template array
- **Returns**: Array of placeholder strings (empty if none)

### `create_contact_page($template_data, $page_title)`

WordPress integration function.

- **Parameters**: Populated template, page title
- **Returns**: WordPress page ID
- **Throws**: `RuntimeException` on page creation failure

### `generate_google_maps_embed($address, $width, $height)`

Generates Google Maps iframe embed code.

- **Parameters**:
  - `$address` - Full address or place name (string)
  - `$width` - Map width, e.g., '100%' or 800 (default: '100%')
  - `$height` - Map height in pixels (default: 450)
- **Returns**: iframe embed code (string)

## Testing

Run the comprehensive test suite:

```bash
php test-contact-injection.php
```

Tests cover:
- Contact information injection
- Email validation
- Emergency service handling
- Phone number formatting (tel: links)
- Map embed code handling
- Default value behavior
- Special character handling
- Template structure integrity

## Design Features

### Visual Hierarchy
- Clear section separation
- Icon-based contact methods
- Large, readable phone numbers
- Prominent CTA buttons

### Accessibility
- Clickable phone numbers (tel: links)
- Clickable email addresses (mailto: links)
- Required form fields marked
- Clear labels and instructions

### Mobile Optimization
- Responsive three-column grid
- Touch-friendly click targets
- Mobile-optimized form fields
- Stackable layout on small screens

### Trust Building
- Multiple contact methods
- Transparent hours display
- Physical location/service area
- Emergency availability (if applicable)

### Emergency Services Design
- High-contrast red gradient
- Warning icon for urgency
- Large, prominent phone number
- Clear 24/7 availability message

## Contact Form

### Form Fields

The template includes a standard HTML form with:

1. **Name** - Text input, required
2. **Phone** - Tel input, required
3. **Email** - Email input with validation, required
4. **Message** - Textarea, required, resizable
5. **Submit Button** - Green styled button

### Form Styling

- Clean, modern design
- 1px borders with rounded corners
- Proper spacing and padding
- Focus states on inputs
- Responsive width (max 600px)

### Form Integration

The form uses standard HTML. To integrate with your backend:

1. Add `action` attribute to form tag
2. Set up server-side form handler
3. Add spam protection (reCAPTCHA, honeypot)
4. Configure email notifications
5. Add success/error message handling

**Alternative**: Replace HTML form with Elementor Form widget or Contact Form 7 shortcode.

## Google Maps Integration

### Using the Generator

```php
// Basic usage
$map = generate_google_maps_embed('Portland, OR');

// With custom dimensions
$map = generate_google_maps_embed(
    '123 Main St, Portland, OR 97201',
    '100%',  // width
    600      // height in pixels
);

// Full address with business name
$map = generate_google_maps_embed('Pyramid Heating & Cooling, Portland, OR');
```

### Custom Map Embed

Use any map service:

```php
// Google Maps (full embed)
$data['map_embed'] = '<iframe src="https://www.google.com/maps/embed?pb=..." width="100%" height="450"></iframe>';

// Mapbox
$data['map_embed'] = '<iframe src="https://api.mapbox.com/..." width="100%" height="450"></iframe>';

// OpenStreetMap
$data['map_embed'] = '<iframe src="https://www.openstreetmap.org/export/embed.html?..." width="100%" height="450"></iframe>';
```

### Map Placeholder

If map embed is not provided, shows:
```
<!-- MAP_EMBED_CODE_HERE -->
[Gray placeholder box with text]
```

Easy to find and replace in Elementor editor.

## Emergency Service Configuration

### Scenario 1: 24/7 Emergency Service

```php
$data = [
    // ... other fields
    'emergency' => true,
    'emergency_phone' => '(503) 555-0143'  // Dedicated emergency line
];
```

**Result**: Red alert section with emergency phone and 24/7 messaging.

### Scenario 2: Same Number for Everything

```php
$data = [
    // ... other fields
    'emergency' => true,
    'emergency_phone' => '(503) 555-0142'  // Same as main phone
];
```

**Result**: Red alert section, same phone displayed prominently.

### Scenario 3: No Emergency Service

```php
$data = [
    // ... other fields
    'emergency' => false
    // emergency_phone not required
];
```

**Result**: Neutral section with standard hours messaging, main phone displayed.

## Customization

### Changing Colors

**Primary Color** (green by default):
- Hero background: `#059669` and `#10b981`
- Icons: `#059669`
- Form button: `#059669`
- Links: `#059669`

**Emergency Color** (red):
- Emergency section: `#dc2626` and `#ef4444`

Modify in template JSON or via Elementor editor.

### Adding More Contact Methods

To add social media, live chat, or other contact methods:

1. Duplicate a column in the contact info section
2. Change the icon
3. Update the label and link
4. Add corresponding placeholders and data

### Form Customization

**Option 1**: Edit HTML in template
- Modify field types
- Add/remove fields
- Change button text
- Update styling

**Option 2**: Replace with Elementor Form widget
- Use Elementor's drag-drop form builder
- Built-in spam protection
- Email notifications included
- Database storage options

**Option 3**: Use plugin shortcode
- Contact Form 7
- WPForms
- Gravity Forms
- Any WordPress form plugin

## Best Practices

### Contact Information

**Phone Numbers**:
- Use consistent formatting: `(XXX) XXX-XXXX`
- Include area code
- Add extension if applicable: `(503) 555-0142 ext. 123`
- Use tel: links for click-to-call

**Email Addresses**:
- Use professional domain email
- Keep it short and memorable
- Use department emails if applicable: `service@`, `sales@`
- Always validate format

**Hours Format**:
- Be specific: "Mon-Fri 7am-6pm, Sat 8am-4pm"
- Include timezone if serving wide area
- Note holiday closures
- Update seasonally if needed

### Emergency Services

**When to Enable**:
- HVAC (heating/cooling emergencies)
- Plumbing (burst pipes, flooding)
- Electrical (power outages, hazards)
- Locksmith services
- 24/7 security services

**When to Disable**:
- Strictly business hours operations
- Appointment-only services
- Non-urgent service businesses

### Map Selection

**Use full address when**:
- You have a physical office/showroom
- Customers visit your location
- You want to show exact location

**Use city/region when**:
- Service-area business (no storefront)
- Privacy concerns
- Multiple service locations

## Troubleshooting

### Page Shows Placeholders

**Issue**: `{{placeholder}}` text visible on page
**Solution**: Data injection didn't run. Ensure you called `inject_contact_data()` before creating the page.

### Invalid Email Error

**Issue**: "Invalid email format" exception
**Solution**: Verify email address is valid format (includes @ and domain).

### Emergency Phone Missing Error

**Issue**: Exception when emergency = true
**Solution**: Add `emergency_phone` field to your data array.

### Map Not Showing

**Issue**: Map placeholder visible or broken iframe
**Solution**:
- Verify iframe code is complete and valid
- Use `generate_google_maps_embed()` function
- Check browser console for CORS errors

### Form Not Submitting

**Issue**: Form doesn't send data
**Solution**:
- Add `action` attribute to form tag
- Set up server-side handler
- Or replace with Elementor Form widget

### Phone Links Not Working on Desktop

**Issue**: tel: links don't work on desktop
**Solution**: This is expected. tel: links work on mobile devices. Desktop users can copy the number.

## File Structure

```
elementor-contact-template.json       # Main template (6 sections)
elementor-contact-injector.php        # Data injection functions
test-contact-injection.php            # Test suite (36 tests)
CONTACT_PAGE_README.md               # This documentation
```

## Version History

### Version 1.0.0
- Initial release
- 6 sections: Hero, Contact Info, Form, Hours, Map, Emergency
- Emergency service conditional logic
- Google Maps embed generator
- Phone/email clickable links
- HTML contact form included
- Comprehensive validation
- Full test coverage (36 tests)

## Related Templates

- **Header/Footer Template**: Site-wide navigation
- **About Page Template**: Company story and team
- **Services Page Template**: Service catalog
- **Get Estimate Template**: InstaBid integration

All templates use consistent injection patterns.

## Support

For issues or questions:
1. Run the test suite to verify setup
2. Check validation error messages
3. Review this documentation
4. Verify all required fields are provided

## Example Implementations

See `example-contact-usage.php` for complete examples:
- HVAC contractor with emergency service
- Plumbing company without emergency service
- Electrical contractor with custom hours
- Roofing company with map integration
