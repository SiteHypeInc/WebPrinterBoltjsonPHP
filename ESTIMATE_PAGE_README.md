# Elementor Get Estimate Page Template

A conversion-optimized Elementor page template designed for contractor websites to capture leads through InstaBid estimate forms.

## Purpose

This template is specifically designed to:
- Embed InstaBid estimate forms seamlessly
- Build trust with visitors through social proof
- Answer common questions about the estimate process
- Provide alternative contact methods
- Maximize form completion rates

## Template Sections

### 1. Hero Section
- **Headline**: "Get Your Free Estimate"
- **Subtext**: Shows response time + "100% Free • No Obligation"
- **Design**: Blue gradient background with centered text
- **Purpose**: Clear value proposition and urgency

### 2. InstaBid Embed Section
- **Title**: "Tell Us About Your Project"
- **HTML Widget**: Placeholder for InstaBid iframe embed code
- **Placeholder**: `<!-- INSTABID_EMBED_CODE_HERE -->`
- **Purpose**: Primary conversion point for lead capture

### 3. Trust Bar
- 4 icon-based trust signals in a row
- Icons: Clock, Dollar, Check Circle, Shield
- **Purpose**: Reduce friction and build credibility

### 4. How It Works
- 3-step process explanation
- Numbered steps with descriptions
- **Purpose**: Set expectations and reduce anxiety

### 5. FAQ Section
- Accordion widget with 3 questions
- Focused on estimate process
- **Purpose**: Address objections preemptively

### 6. Contact Alternative
- "Prefer to Call?" heading
- Large phone number display
- Response time reminder
- **Purpose**: Provide phone option for those who prefer it

## Data Structure

```php
$data = [
    'name' => 'Company Name',              // Required: Company name
    'phone' => '(503) 555-0142',          // Required: Formatted phone number
    'instabid_embed' => '<iframe...',     // Required: Full iframe code or placeholder
    'response_time' => 'Within 2 hours',   // Required: Expected response time
    'trust_signals' => [                   // Required: Exactly 4 signals
        '100% Free',
        'No Obligation',
        'Same Day Response',
        'Licensed & Insured'
    ],
    'faqs' => [                            // Required: Exactly 3 FAQs
        [
            'q' => 'Question text',
            'a' => 'Answer text'
        ],
        // ... 2 more
    ],
    'steps' => [                           // Optional: defaults provided
        'Fill Out Form',
        'Receive Estimate',
        'Approve Quote'
    ]
];
```

## Usage

### Basic Implementation

```php
<?php
// Load the template
$template = json_decode(
    file_get_contents('elementor-estimate-template.json'),
    true
);

// Prepare your data
$data = [
    'name' => 'Pyramid Heating & Cooling',
    'phone' => '(503) 555-0142',
    'instabid_embed' => '<iframe src="https://instabid.app/embed/your-company" width="100%" height="800"></iframe>',
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
            'a' => 'Yes! We provide completely free, no-obligation estimates for all services. You\'ll receive a detailed breakdown of costs with no hidden fees or surprise charges.'
        ],
        [
            'q' => 'What areas do you serve?',
            'a' => 'We proudly serve Portland and surrounding areas including Beaverton, Gresham, Lake Oswego, and Tigard. Contact us to confirm service availability in your specific location.'
        ]
    ]
];

// Inject the data
require_once 'elementor-estimate-injector.php';
$populated = inject_estimate_data($template, $data);

// Create the page in WordPress
$page_id = create_estimate_page($populated, 'Get Your Free Estimate');
echo "Estimate page created with ID: {$page_id}";
```

### Using Placeholder for InstaBid Setup

If you haven't configured InstaBid yet, use the placeholder:

```php
$data['instabid_embed'] = '<!-- INSTABID_EMBED_CODE_HERE -->';
```

This preserves the comment in the HTML widget, making it easy to find and replace later through the Elementor editor.

### Custom Steps

Override the default process steps:

```php
$data['steps'] = [
    'Tell Us Your Needs',
    'Get Instant Pricing',
    'Book Your Service'
];
```

## Template Placeholders

The template uses these placeholders (automatically replaced):

| Placeholder | Purpose | Example |
|------------|---------|---------|
| `{{phone}}` | Contact phone number | (503) 555-0142 |
| `{{instabid_embed}}` | InstaBid iframe code | `<iframe src="...">` |
| `{{response_time}}` | Response time promise | Within 2 hours |
| `{{trust_signal_1}}` through `{{trust_signal_4}}` | Trust indicators | 100% Free |
| `{{step_1}}` through `{{step_3}}` | Process steps | Fill Out Form |
| `{{faq_1_q}}` through `{{faq_3_q}}` | FAQ questions | How long... |
| `{{faq_1_a}}` through `{{faq_3_a}}` | FAQ answers | Most estimates... |

## Validation

The injector validates:

1. All required fields are present
2. Trust signals array has exactly 4 items
3. FAQs array has exactly 3 items
4. Each FAQ has both 'q' and 'a' keys
5. Template structure is valid JSON

Throws `InvalidArgumentException` if validation fails.

## Functions

### `inject_estimate_data($template, $data)`

Main injection function.

- **Parameters**: Template array, data array
- **Returns**: Populated template array
- **Throws**: `InvalidArgumentException` on validation failure

### `validate_estimate_template($template)`

Checks that template has all required placeholders.

- **Parameters**: Template array
- **Returns**: `true` if valid
- **Throws**: `InvalidArgumentException` if invalid

### `find_remaining_placeholders($template)`

Finds any unreplaced `{{placeholders}}` in template.

- **Parameters**: Template array
- **Returns**: Array of placeholder strings (empty if none)

### `create_estimate_page($template_data, $page_title)`

WordPress integration function.

- **Parameters**: Populated template, page title
- **Returns**: WordPress page ID
- **Throws**: `RuntimeException` on page creation failure

## Testing

Run the comprehensive test suite:

```bash
php test-estimate-injection.php
```

Tests cover:
- Data injection correctness
- Placeholder replacement
- Validation rules
- Error handling
- Special character handling
- Default values
- Template structure integrity

## Design Features

### Visual Hierarchy
- Large, clear headlines
- Progressive information disclosure
- White space for readability
- Consistent typography

### Trust Building
- Social proof icons above the fold
- FAQ section addresses objections
- Multiple contact methods
- Professional gradient designs

### Conversion Optimization
- Minimal navigation (canvas template)
- Single primary CTA (the form)
- Secondary CTA (phone number)
- Clear process expectations

### Responsive Design
- Mobile-friendly layouts
- Stackable columns
- Touch-friendly accordion
- Readable font sizes

## InstaBid Integration

### Getting Your Embed Code

1. Log in to your InstaBid account
2. Navigate to Settings → Embed Codes
3. Copy your unique iframe embed code
4. It will look like:
   ```html
   <iframe src="https://instabid.app/embed/your-company-id"
           width="100%"
           height="800"
           frameborder="0"></iframe>
   ```

### Testing the Integration

1. Create the page with placeholder comment
2. Preview the page in Elementor
3. Replace the HTML widget content with your actual embed code
4. Test form submission
5. Verify leads are captured in InstaBid dashboard

## Customization

### Changing Colors

Modify these settings in the template JSON:

- Hero background: `#1e40af` and `#3b82f6` (blue gradient)
- Trust icon color: `#3b82f6` (blue)
- CTA background: `#3b82f6` and `#1e40af` (blue gradient)

### Adding More FAQs

The template is designed for 3 FAQs to avoid overwhelming visitors. To add more:

1. Duplicate an FAQ item in the accordion settings
2. Add corresponding placeholders (`{{faq_4_q}}`, etc.)
3. Update the injector validation to accept more items
4. Update the replacement map

### Changing Trust Signals

The 4 trust signals use these icons by default:
- Clock (fast response)
- Dollar (free/pricing)
- Check Circle (no obligation)
- Shield (licensed/insured)

You can modify icons in the template JSON by changing the `icon.value` field.

## Best Practices

### Copy Writing

**Hero Section**:
- Keep urgency subtle ("Within 2 hours" vs "ACT NOW!")
- Emphasize value (Free, No Obligation)

**Trust Signals**:
- Be specific ("24-Hour Response" vs "Fast")
- Use numbers ("100% Free" vs "Free")
- Include credentials ("Licensed & Insured")

**FAQs**:
- Answer the most common objections
- Keep answers concise but complete
- Use natural, conversational language

### Form Optimization

- Keep InstaBid form fields minimal
- Test on mobile devices
- Ensure fast loading time
- Remove navigation distractions (canvas template)

### A/B Testing Ideas

Test variations of:
- Response time promises
- Trust signal wording
- FAQ questions
- CTA button text
- Phone number prominence

## Troubleshooting

### Page Shows Placeholders

**Issue**: `{{placeholder}}` text visible on page
**Solution**: Data injection didn't run. Ensure you called `inject_estimate_data()` before creating the page.

### InstaBid Form Not Loading

**Issue**: Form iframe is empty or shows error
**Solution**: Verify your InstaBid embed code is correct. Check browser console for CORS errors.

### Template Won't Import

**Issue**: Error when creating page
**Solution**: Ensure Elementor plugin is active and template JSON is valid.

### Special Characters Breaking

**Issue**: Apostrophes or quotes cause issues
**Solution**: The injector properly escapes characters. Use single quotes in PHP and let the function handle escaping.

## File Structure

```
elementor-estimate-template.json     # Main template file (7 sections)
elementor-estimate-injector.php      # Data injection functions
test-estimate-injection.php          # Test suite (34 tests)
ESTIMATE_PAGE_README.md             # This documentation
```

## Version History

### Version 1.0.0
- Initial release
- 7 sections: Hero, Embed, Trust, How It Works, Steps, FAQ, Contact Alt
- InstaBid iframe integration
- 4 trust signals
- 3 FAQ accordion
- Comprehensive validation
- Full test coverage (34 tests)

## Support

For issues or questions:
1. Run the test suite to verify setup
2. Check validation error messages
3. Review this documentation
4. Verify InstaBid account is active

## Related Templates

- **Header/Footer Template**: Site-wide navigation
- **About Page Template**: Company story and team
- **Services Page Template**: Service catalog with cards

All templates use the same injection pattern for consistency.
