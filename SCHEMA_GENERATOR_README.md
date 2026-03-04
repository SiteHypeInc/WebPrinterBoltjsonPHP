# Contractor Schema Generator

A comprehensive PHP library for generating SEO-optimized Schema.org JSON-LD markup and meta tags for contractor websites.

## Purpose

This library helps contractor websites:
- **Improve search engine visibility** with structured data
- **Enhance local SEO** with proper LocalBusiness schema
- **Increase click-through rates** with rich search results
- **Display ratings and reviews** in search results
- **Show service areas and hours** in Google Knowledge Panels
- **Generate complete meta tags** for social sharing and SEO

## Features

### Schema.org JSON-LD Generation
- ✅ Trade-specific business types (HVAC, Plumber, Electrician, etc.)
- ✅ Complete contact information (phone, email, address)
- ✅ Geographic coordinates for local search
- ✅ Opening hours in Schema.org format
- ✅ Service catalog with offer listings
- ✅ Aggregate ratings and review counts
- ✅ Service area definitions
- ✅ Social media profile linking
- ✅ Payment methods accepted
- ✅ Price range indicators

### Meta Tag Generation
- ✅ SEO title tags with local optimization
- ✅ Meta descriptions with ratings and CTAs
- ✅ Open Graph tags for Facebook/LinkedIn
- ✅ Twitter Card tags
- ✅ Geographic meta tags
- ✅ Business contact data tags

### Trade Type Support

The library automatically maps trade types to appropriate Schema.org types:

| Trade | Schema.org Type |
|-------|----------------|
| hvac | HVACBusiness |
| plumbing, plumber | Plumber |
| electrical, electrician | Electrician |
| roofing, roofer | RoofingContractor |
| locksmith | Locksmith |
| general_contractor | GeneralContractor |
| painting, landscaping, carpentry, etc. | ProfessionalService |

All types also include `LocalBusiness` as a fallback type.

## Installation

Simply include the generator file in your PHP project:

```php
require_once 'contractor-schema-generator.php';
```

## Basic Usage

### Generate Complete Schema Markup

```php
<?php
require_once 'contractor-schema-generator.php';

$contractor_data = [
    'name' => 'Pyramid Heating & Cooling',
    'trade' => 'hvac',
    'phone' => '(503) 555-0142',
    'address' => 'Portland, OR',
    'lat' => 45.5231,
    'lng' => -122.6765,
    'rating' => 4.7,
    'review_count' => 143,
    'services' => ['Furnace Repair', 'AC Install'],
    'service_areas' => ['Portland', 'Beaverton'],
    'hours' => 'Mo-Fr 07:00-18:00'
];

// Generate JSON-LD schema
$schema = generate_contractor_schema($contractor_data);
echo $schema;

// Generate meta tags
$meta_tags = generate_meta_tags($contractor_data);
echo $meta_tags;

// Generate complete head section (meta tags + schema)
$head_section = generate_head_section($contractor_data);
echo $head_section;
```

### Output Example

```html
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": ["HVACBusiness", "LocalBusiness"],
    "name": "Pyramid Heating & Cooling",
    "telephone": "(503) 555-0142",
    "address": {
        "@type": "PostalAddress",
        "addressLocality": "Portland",
        "addressRegion": "OR",
        "addressCountry": "US"
    },
    "geo": {
        "@type": "GeoCoordinates",
        "latitude": 45.5231,
        "longitude": -122.6765
    },
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": 4.7,
        "reviewCount": 143
    }
    ...
}
</script>
```

## Data Structure

### Required Fields

```php
[
    'name' => 'Business Name',           // Company/DBA name
    'trade' => 'hvac',                   // Trade identifier
    'phone' => '(503) 555-0142',        // Primary phone
    'address' => 'Portland, OR'          // Address (string or array)
]
```

### Optional Fields

```php
[
    'email' => 'info@company.com',                    // Contact email
    'website' => 'https://company.com',               // Website URL
    'lat' => 45.5231,                                 // Latitude (requires lng)
    'lng' => -122.6765,                               // Longitude (requires lat)
    'rating' => 4.7,                                  // Average rating (0-5, requires review_count)
    'review_count' => 143,                            // Number of reviews (required with rating)
    'services' => ['Service 1', 'Service 2'],        // Service list
    'service_areas' => ['City 1', 'City 2'],         // Areas served
    'hours' => 'Mo-Fr 07:00-18:00',                  // Opening hours
    'description' => 'Business description',          // Company description
    'price_range' => '$$',                            // Price indicator ($-$$$$)
    'logo' => 'https://company.com/logo.png',        // Logo URL
    'image' => 'https://company.com/image.jpg',      // Primary image URL
    'payment_accepted' => ['Cash', 'Credit Card'],   // Payment methods
    'social_urls' => ['https://facebook.com/...'],   // Social media URLs
    'founded_year' => 1995                            // Year established
]
```

## Functions

### `generate_contractor_schema($contractor_data)`

Generates complete JSON-LD schema markup.

**Parameters:**
- `$contractor_data` (array) - Contractor business data

**Returns:** (string) Complete `<script type="application/ld+json">` tag with schema

**Throws:** `InvalidArgumentException` if required fields missing or invalid

**Example:**
```php
$schema = generate_contractor_schema([
    'name' => 'ABC Plumbing',
    'trade' => 'plumbing',
    'phone' => '(555) 123-4567',
    'address' => 'Seattle, WA'
]);
```

### `generate_meta_tags($contractor_data)`

Generates SEO and social media meta tags.

**Parameters:**
- `$contractor_data` (array) - Same structure as schema function

**Returns:** (string) HTML meta tags (title, description, OG, Twitter)

**Example:**
```php
$meta = generate_meta_tags($contractor_data);
// Returns: <title>...</title>\n<meta name="description"...>...
```

### `generate_head_section($contractor_data)`

Generates complete HTML head content (meta tags + schema).

**Parameters:**
- `$contractor_data` (array) - Contractor business data

**Returns:** (string) Complete head section content

**Example:**
```php
$head = generate_head_section($contractor_data);
// Use in <head> tag
```

### `validate_contractor_data($data)`

Validates contractor data structure.

**Parameters:**
- `$data` (array) - Data to validate

**Returns:** (bool) True if valid

**Throws:** `InvalidArgumentException` with specific error message

**Example:**
```php
try {
    validate_contractor_data($contractor_data);
} catch (InvalidArgumentException $e) {
    echo "Validation error: " . $e->getMessage();
}
```

### `get_schema_type_for_trade($trade)`

Maps trade identifier to Schema.org types.

**Parameters:**
- `$trade` (string) - Trade identifier

**Returns:** (array) `[PrimaryType, FallbackType]`

**Example:**
```php
$types = get_schema_type_for_trade('hvac');
// Returns: ['HVACBusiness', 'LocalBusiness']
```

### `format_opening_hours($hours)`

Formats opening hours for Schema.org.

**Parameters:**
- `$hours` (string|array) - Hours in various formats

**Returns:** (array) Schema.org formatted hours

**Example:**
```php
$formatted = format_opening_hours('Mo-Fr 07:00-18:00');
// Returns: ['Mo-Fr 07:00-18:00']

$formatted = format_opening_hours('24/7');
// Returns: ['Monday-Sunday 00:00-23:59']
```

### `parse_address($address)`

Parses address into PostalAddress schema structure.

**Parameters:**
- `$address` (string|array) - Address to parse

**Returns:** (array) PostalAddress schema structure

**Example:**
```php
$parsed = parse_address('123 Main St, Portland, OR 97201');
// Returns structured address with street, city, state, ZIP
```

### `get_trade_keywords($trade)`

Gets SEO keywords for specific trade.

**Parameters:**
- `$trade` (string) - Trade identifier

**Returns:** (array) Related keywords

**Example:**
```php
$keywords = get_trade_keywords('hvac');
// Returns: ['heating', 'cooling', 'furnace', 'air conditioning', ...]
```

## Address Formats

### String Format

Simple address parsing:

```php
'address' => 'Portland, OR'
// Parses to: city=Portland, state=OR

'address' => 'Portland, OR 97201'
// Parses to: city=Portland, state=OR, zip=97201

'address' => '123 Main St, Portland, OR 97201'
// Parses to: street=123 Main St, city=Portland, state=OR, zip=97201
```

### Array Format

Structured address for precise control:

```php
'address' => [
    'streetAddress' => '123 Main St',
    'addressLocality' => 'Portland',
    'addressRegion' => 'OR',
    'postalCode' => '97201',
    'addressCountry' => 'US'
]
```

## Hours Formats

### Schema.org Format (Recommended)

```php
'hours' => 'Mo-Fr 07:00-18:00'
'hours' => 'Mo-Fr 09:00-17:00'
'hours' => 'Mo-Sa 08:00-20:00'
```

### 24/7 Format

```php
'hours' => '24/7'
// Auto-converts to: 'Monday-Sunday 00:00-23:59'
```

### Array Format

```php
'hours' => [
    'Mo-Fr 07:00-18:00',
    'Sa 08:00-16:00'
]
```

### Human-Readable Format

```php
'hours' => 'Mon-Fri: 7am-6pm, Sat: 8am-4pm'
// Passed through as-is
```

## Validation Rules

### Required Field Validation

All data must include:
- `name` - Non-empty string
- `trade` - Non-empty string
- `phone` - Non-empty string
- `address` - Non-empty string or array

### Email Validation

If provided, email must be valid format:
```php
'email' => 'info@company.com'  // ✅ Valid
'email' => 'not-an-email'       // ❌ Throws exception
```

### Rating Validation

- Must be between 0 and 5
- Requires `review_count` to be set
```php
'rating' => 4.7,         // ✅ Valid
'review_count' => 143    // ✅ Required with rating

'rating' => 6.0          // ❌ Throws exception (> 5)
'rating' => 4.5          // ❌ Throws exception (no review_count)
```

### Coordinate Validation

- Latitude: -90 to 90
- Longitude: -180 to 180
- Both must be provided together
```php
'lat' => 45.5231,   // ✅ Valid
'lng' => -122.6765  // ✅ Required with lat

'lat' => 200        // ❌ Throws exception (> 90)
'lat' => 45.5231    // ❌ Throws exception (no lng)
```

## Schema Components

### LocalBusiness Base

Every schema includes:
- `@context`: https://schema.org
- `@type`: Trade-specific + LocalBusiness
- `name`: Business name
- `telephone`: Phone number
- `address`: PostalAddress structure

### Geographic Data

When coordinates provided:
```json
{
    "geo": {
        "@type": "GeoCoordinates",
        "latitude": 45.5231,
        "longitude": -122.6765
    }
}
```

### Service Catalog

When services provided:
```json
{
    "hasOfferCatalog": {
        "@type": "OfferCatalog",
        "name": "Services",
        "itemListElement": [
            {
                "@type": "Offer",
                "itemOffered": {
                    "@type": "Service",
                    "name": "Furnace Repair"
                }
            }
        ]
    }
}
```

### Aggregate Rating

When rating and review_count provided:
```json
{
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": 4.7,
        "reviewCount": 143,
        "bestRating": 5,
        "worstRating": 1
    }
}
```

### Service Areas

When service_areas provided:
```json
{
    "areaServed": [
        {
            "@type": "City",
            "name": "Portland"
        },
        {
            "@type": "City",
            "name": "Beaverton"
        }
    ]
}
```

### Social Profiles

When social_urls provided:
```json
{
    "sameAs": [
        "https://facebook.com/pyramidheating",
        "https://twitter.com/pyramidheating"
    ]
}
```

## Meta Tag Output

### Title Tag

Format: `{Company Name} | {City} | {Trade} Services`

```html
<title>Pyramid Heating & Cooling | Portland | HVAC Services</title>
```

### Meta Description

Includes: Description/trade mention, location, rating, review count, phone, CTA

```html
<meta name="description" content="Professional HVAC services from Pyramid Heating & Cooling serving Portland and surrounding areas. Rated 4.7/5 from 143 reviews. Call (503) 555-0142 today!">
```

### Open Graph Tags

```html
<meta property="og:type" content="business.business">
<meta property="og:title" content="Pyramid Heating & Cooling">
<meta property="og:description" content="Professional HVAC services...">
<meta property="og:url" content="https://pyramidheating.com">
<meta property="og:image" content="https://pyramidheating.com/image.jpg">
<meta property="business:contact_data:street_address" content="Portland, OR">
<meta property="business:contact_data:phone_number" content="(503) 555-0142">
<meta property="business:contact_data:email" content="info@pyramidheating.com">
```

### Twitter Card Tags

```html
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="Pyramid Heating & Cooling">
<meta name="twitter:description" content="Professional HVAC services...">
<meta name="twitter:image" content="https://pyramidheating.com/image.jpg">
```

### Geographic Tags

```html
<meta name="geo.region" content="US">
<meta name="geo.placename" content="Portland">
<meta name="geo.position" content="45.5231;-122.6765">
<meta name="ICBM" content="45.5231, -122.6765">
```

## SEO Benefits

### Rich Search Results

Proper schema enables:
- ⭐ Star ratings in search results
- 📞 Click-to-call phone numbers
- 🕒 Business hours display
- 📍 Location and map integration
- 💰 Price range indicators
- ✅ Service listings

### Local SEO

Enhanced local search with:
- Service area definitions
- Geographic coordinates
- Local business categorization
- Opening hours
- Contact information
- Customer reviews

### Social Sharing

Optimized social cards for:
- Facebook posts and shares
- Twitter cards
- LinkedIn previews
- Pinterest pins
- All Open Graph platforms

## WordPress Integration

### In Theme Header

```php
<?php
// In header.php or functions.php
require_once get_template_directory() . '/contractor-schema-generator.php';

$contractor_data = [
    'name' => get_bloginfo('name'),
    'trade' => get_option('contractor_trade'),
    'phone' => get_option('company_phone'),
    'address' => get_option('company_address'),
    'email' => get_option('admin_email'),
    'website' => home_url(),
    // ... additional fields
];

function add_contractor_schema() {
    global $contractor_data;
    echo generate_head_section($contractor_data);
}
add_action('wp_head', 'add_contractor_schema');
```

### Custom Fields Integration

```php
$contractor_data = [
    'name' => get_field('business_name'),
    'trade' => get_field('trade_type'),
    'phone' => get_field('phone_number'),
    'address' => [
        'streetAddress' => get_field('street_address'),
        'addressLocality' => get_field('city'),
        'addressRegion' => get_field('state'),
        'postalCode' => get_field('zip_code'),
        'addressCountry' => 'US'
    ],
    'lat' => (float) get_field('latitude'),
    'lng' => (float) get_field('longitude'),
    'rating' => (float) get_field('average_rating'),
    'review_count' => (int) get_field('total_reviews'),
    'services' => get_field('services_list'),
    'service_areas' => get_field('service_areas'),
    'hours' => get_field('business_hours')
];
```

## Testing

Run the comprehensive test suite:

```bash
php test-schema-generator.php
```

The test suite includes:
- ✅ 78 automated tests
- Schema generation validation
- Meta tag generation
- Trade type mapping
- Address parsing
- Validation rules
- Error handling
- JSON validity
- Special character handling

## Example Implementations

See `example-schema-usage.php` for complete examples:
- HVAC contractor with full data
- Plumber with minimal data
- Electrician with custom pricing
- Roofer with service areas
- Locksmith (24/7 service)
- Multiple trade configurations

## Best Practices

### Data Quality

**DO:**
- ✅ Use accurate, up-to-date information
- ✅ Include coordinates for better local SEO
- ✅ Provide comprehensive service lists
- ✅ Keep ratings and reviews current
- ✅ Update hours for holidays/seasons

**DON'T:**
- ❌ Inflate ratings or review counts
- ❌ List services you don't offer
- ❌ Use fake addresses or phone numbers
- ❌ Spam service areas where you don't operate

### Schema Optimization

- Include as many optional fields as possible
- Update schema when business information changes
- Add high-quality logo and image URLs
- List all payment methods accepted
- Define specific service areas
- Include founding year for credibility

### Testing & Validation

1. **Google Rich Results Test**: https://search.google.com/test/rich-results
2. **Schema Markup Validator**: https://validator.schema.org/
3. **Test with actual data** before deploying
4. **Monitor search console** for schema errors
5. **Update regularly** to maintain accuracy

## Common Issues

### Schema Not Appearing in Search

**Issue**: Schema markup not showing in Google search results

**Solutions**:
- Verify JSON validity with validator
- Check Google Search Console for errors
- Wait 2-4 weeks for Google to process
- Ensure schema is in `<head>` section
- Verify no conflicting schema on page

### Invalid Rating Range

**Issue**: `InvalidArgumentException: Rating must be between 0 and 5`

**Solution**: Ensure rating is 0-5 scale, not percentage

```php
'rating' => 4.7,  // ✅ Correct
'rating' => 94,   // ❌ Wrong (percentage)
```

### Missing Review Count

**Issue**: `InvalidArgumentException: review_count required when rating is provided`

**Solution**: Always include review count with ratings

```php
'rating' => 4.7,
'review_count' => 143  // Required
```

### Coordinates Validation Failed

**Issue**: Latitude/longitude out of range

**Solution**: Verify coordinate accuracy

```php
'lat' => 45.5231,    // ✅ Valid latitude (-90 to 90)
'lng' => -122.6765,  // ✅ Valid longitude (-180 to 180)
```

## Performance

- **Negligible runtime overhead** - Pure PHP string generation
- **No external API calls** - All processing local
- **Cacheable output** - Store generated schema
- **Minimal memory usage** - Processes single data array

### Caching Recommendation

```php
$cache_key = 'contractor_schema_' . md5(serialize($contractor_data));
$schema = get_transient($cache_key);

if (false === $schema) {
    $schema = generate_head_section($contractor_data);
    set_transient($cache_key, $schema, DAY_IN_SECONDS);
}

echo $schema;
```

## Browser Support

Generated markup works in:
- All modern browsers
- Search engine crawlers
- Social media bots
- Screen readers
- No JavaScript required

## Version History

### Version 1.0.0
- Initial release
- 25+ trade type mappings
- Complete Schema.org LocalBusiness support
- Full meta tag generation
- Address parsing (string and array)
- Opening hours formatting
- Service catalog generation
- Aggregate ratings
- Service area definitions
- Social profile linking
- Comprehensive validation
- 78 passing tests

## License

Free to use for contractor websites.

## Support

For issues or questions:
1. Review this documentation
2. Run the test suite
3. Check validation error messages
4. Verify data structure matches examples
