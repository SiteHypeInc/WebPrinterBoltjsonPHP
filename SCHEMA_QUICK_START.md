# Schema Generator Quick Start

Generate SEO-optimized Schema.org markup for contractor websites in 3 steps.

## Quick Usage

```php
<?php
require_once 'contractor-schema-generator.php';

// 1. Prepare your data
$data = [
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

// 2. Generate schema + meta tags
echo generate_head_section($data);
```

## Required Fields Only

```php
$data = [
    'name' => 'Your Company Name',
    'trade' => 'hvac',              // or: plumbing, electrical, roofing, etc.
    'phone' => '(555) 123-4567',
    'address' => 'City, State'
];
```

## Supported Trades

- `hvac` → HVACBusiness
- `plumbing` → Plumber
- `electrical` → Electrician
- `roofing` → RoofingContractor
- `locksmith` → Locksmith
- `general_contractor` → GeneralContractor
- `painting`, `landscaping`, `carpentry`, etc. → ProfessionalService

## Output

Generates:
- ✅ JSON-LD Schema markup
- ✅ SEO title tag
- ✅ Meta description
- ✅ Open Graph tags
- ✅ Twitter Card tags
- ✅ Geographic tags

## Functions

| Function | Purpose |
|----------|---------|
| `generate_contractor_schema($data)` | JSON-LD schema only |
| `generate_meta_tags($data)` | Meta tags only |
| `generate_head_section($data)` | Both schema + meta |

## Testing

```bash
php test-schema-generator.php
```

## Full Documentation

See `SCHEMA_GENERATOR_README.md` for complete details.
