<?php
/**
 * Local Business JSON-LD Schema Generator for Contractors
 *
 * Generates SEO-optimized schema markup and meta tags for contractor websites.
 * Supports all major trade types with proper Schema.org typing.
 *
 * @package ContractorSchemaGenerator
 * @version 1.0.0
 */

/**
 * Map trade types to Schema.org types
 *
 * @param string $trade Trade identifier
 * @return array Array with primary type and fallback
 */
function get_schema_type_for_trade($trade) {
    $types = [
        'hvac' => ['HVACBusiness', 'LocalBusiness'],
        'plumbing' => ['Plumber', 'LocalBusiness'],
        'plumber' => ['Plumber', 'LocalBusiness'],
        'electrical' => ['Electrician', 'LocalBusiness'],
        'electrician' => ['Electrician', 'LocalBusiness'],
        'painting' => ['ProfessionalService', 'LocalBusiness'],
        'painter' => ['ProfessionalService', 'LocalBusiness'],
        'roofing' => ['RoofingContractor', 'LocalBusiness'],
        'roofer' => ['RoofingContractor', 'LocalBusiness'],
        'landscaping' => ['ProfessionalService', 'LocalBusiness'],
        'landscaper' => ['ProfessionalService', 'LocalBusiness'],
        'carpentry' => ['ProfessionalService', 'LocalBusiness'],
        'carpenter' => ['ProfessionalService', 'LocalBusiness'],
        'general_contractor' => ['GeneralContractor', 'LocalBusiness'],
        'locksmith' => ['Locksmith', 'LocalBusiness'],
        'cleaning' => ['ProfessionalService', 'LocalBusiness'],
        'pest_control' => ['ProfessionalService', 'LocalBusiness'],
        'appliance_repair' => ['ProfessionalService', 'LocalBusiness'],
        'garage_door' => ['ProfessionalService', 'LocalBusiness'],
        'handyman' => ['ProfessionalService', 'LocalBusiness'],
        'flooring' => ['ProfessionalService', 'LocalBusiness'],
        'concrete' => ['ProfessionalService', 'LocalBusiness'],
        'masonry' => ['ProfessionalService', 'LocalBusiness'],
        'tree_service' => ['ProfessionalService', 'LocalBusiness'],
        'window_cleaning' => ['ProfessionalService', 'LocalBusiness'],
        'gutter' => ['ProfessionalService', 'LocalBusiness']
    ];

    $trade_lower = strtolower(trim($trade));

    if (isset($types[$trade_lower])) {
        return $types[$trade_lower];
    }

    return ['LocalBusiness', 'Organization'];
}

/**
 * Format opening hours for Schema.org
 *
 * @param string|array $hours Hours in various formats
 * @return array Array of opening hours specifications
 */
function format_opening_hours($hours) {
    if (is_array($hours)) {
        return $hours;
    }

    if (empty($hours)) {
        return [];
    }

    // Handle 24/7
    if (preg_match('/24\/?7/i', $hours)) {
        return ['Monday-Sunday 00:00-23:59'];
    }

    // Already formatted (Mo-Fr 07:00-18:00)
    if (preg_match('/^[A-Z][a-z]-[A-Z][a-z]\s+\d{2}:\d{2}-\d{2}:\d{2}$/i', $hours)) {
        return [$hours];
    }

    return [$hours];
}

/**
 * Parse address string or array into PostalAddress structure
 *
 * @param string|array $address Address data
 * @return array PostalAddress schema
 */
function parse_address($address) {
    if (is_array($address) && isset($address['streetAddress'])) {
        return array_merge([
            '@type' => 'PostalAddress'
        ], $address);
    }

    $postal_address = [
        '@type' => 'PostalAddress'
    ];

    if (is_string($address)) {
        // Try to parse common formats
        $parts = array_map('trim', explode(',', $address));

        if (count($parts) >= 2) {
            $state_zip = end($parts);
            $city = $parts[count($parts) - 2];

            if (count($parts) > 2) {
                $postal_address['streetAddress'] = implode(', ', array_slice($parts, 0, -2));
            }

            $postal_address['addressLocality'] = $city;

            // Parse state and ZIP
            if (preg_match('/([A-Z]{2})\s*(\d{5})?/', $state_zip, $matches)) {
                $postal_address['addressRegion'] = $matches[1];
                if (isset($matches[2])) {
                    $postal_address['postalCode'] = $matches[2];
                }
            } else {
                $postal_address['addressRegion'] = $state_zip;
            }

            $postal_address['addressCountry'] = 'US';
        } else {
            $postal_address['addressLocality'] = $address;
            $postal_address['addressCountry'] = 'US';
        }
    }

    return $postal_address;
}

/**
 * Generate Local Business JSON-LD schema
 *
 * @param array $contractor_data {
 *     Contractor business data
 *
 *     @type string   $name              Required: Business name
 *     @type string   $trade             Required: Trade type (hvac, plumbing, etc.)
 *     @type string   $phone             Required: Phone number
 *     @type string   $address           Required: Address string or array
 *     @type float    $lat               Optional: Latitude
 *     @type float    $lng               Optional: Longitude
 *     @type float    $rating            Optional: Average rating (0-5)
 *     @type int      $review_count      Optional: Number of reviews
 *     @type array    $services          Optional: Array of service names
 *     @type array    $service_areas     Optional: Array of cities/regions served
 *     @type string   $hours             Optional: Opening hours
 *     @type string   $email             Optional: Contact email
 *     @type string   $website           Optional: Website URL
 *     @type string   $logo              Optional: Logo URL
 *     @type string   $image             Optional: Primary image URL
 *     @type string   $description       Optional: Business description
 *     @type string   $price_range       Optional: Price range ($$, $$$, etc.)
 *     @type array    $payment_accepted  Optional: Payment methods
 *     @type array    $social_urls       Optional: Social media URLs
 *     @type int      $founded_year      Optional: Year founded
 * }
 * @return string JSON-LD script tag
 */
function generate_contractor_schema($contractor_data) {
    // Validate required fields
    $required = ['name', 'trade', 'phone', 'address'];
    foreach ($required as $field) {
        if (!isset($contractor_data[$field]) || empty($contractor_data[$field])) {
            throw new InvalidArgumentException("Missing required field: {$field}");
        }
    }

    $data = $contractor_data;
    $schema_types = get_schema_type_for_trade($data['trade']);

    // Base schema structure
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => $schema_types,
        'name' => $data['name'],
        'telephone' => $data['phone'],
        'address' => parse_address($data['address'])
    ];

    // Add email if provided
    if (isset($data['email']) && !empty($data['email'])) {
        $schema['email'] = $data['email'];
    }

    // Add website URL
    if (isset($data['website']) && !empty($data['website'])) {
        $schema['url'] = $data['website'];
    }

    // Add geo coordinates
    if (isset($data['lat']) && isset($data['lng'])) {
        $schema['geo'] = [
            '@type' => 'GeoCoordinates',
            'latitude' => (float) $data['lat'],
            'longitude' => (float) $data['lng']
        ];
    }

    // Add opening hours
    if (isset($data['hours']) && !empty($data['hours'])) {
        $schema['openingHours'] = format_opening_hours($data['hours']);
    }

    // Add price range
    if (isset($data['price_range']) && !empty($data['price_range'])) {
        $schema['priceRange'] = $data['price_range'];
    } else {
        $schema['priceRange'] = '$$';
    }

    // Add logo
    if (isset($data['logo']) && !empty($data['logo'])) {
        $schema['logo'] = $data['logo'];
    }

    // Add image
    if (isset($data['image']) && !empty($data['image'])) {
        $schema['image'] = $data['image'];
    }

    // Add description
    if (isset($data['description']) && !empty($data['description'])) {
        $schema['description'] = $data['description'];
    }

    // Add founding year
    if (isset($data['founded_year']) && !empty($data['founded_year'])) {
        $schema['foundingDate'] = (string) $data['founded_year'];
    }

    // Add payment methods
    if (isset($data['payment_accepted']) && is_array($data['payment_accepted'])) {
        $schema['paymentAccepted'] = implode(', ', $data['payment_accepted']);
    }

    // Add services as offer catalog
    if (isset($data['services']) && is_array($data['services']) && !empty($data['services'])) {
        $offers = [];
        foreach ($data['services'] as $service) {
            $offers[] = [
                '@type' => 'Offer',
                'itemOffered' => [
                    '@type' => 'Service',
                    'name' => $service
                ]
            ];
        }

        $schema['hasOfferCatalog'] = [
            '@type' => 'OfferCatalog',
            'name' => 'Services',
            'itemListElement' => $offers
        ];
    }

    // Add aggregate rating
    if (isset($data['rating']) && isset($data['review_count'])) {
        $schema['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => (float) $data['rating'],
            'reviewCount' => (int) $data['review_count'],
            'bestRating' => 5,
            'worstRating' => 1
        ];
    }

    // Add service areas
    if (isset($data['service_areas']) && is_array($data['service_areas']) && !empty($data['service_areas'])) {
        $areas = [];
        foreach ($data['service_areas'] as $area) {
            $areas[] = [
                '@type' => 'City',
                'name' => $area
            ];
        }
        $schema['areaServed'] = $areas;
    }

    // Add social media URLs
    if (isset($data['social_urls']) && is_array($data['social_urls']) && !empty($data['social_urls'])) {
        $schema['sameAs'] = array_values($data['social_urls']);
    }

    // Generate script tag
    $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

    return '<script type="application/ld+json">' . "\n" . $json . "\n" . '</script>';
}

/**
 * Generate SEO meta tags for contractor
 *
 * @param array $contractor_data Same structure as generate_contractor_schema()
 * @return string HTML meta tags
 */
function generate_meta_tags($contractor_data) {
    $data = $contractor_data;
    $tags = [];

    // Extract city from address for localization
    $city = '';
    if (is_string($data['address'])) {
        $parts = array_map('trim', explode(',', $data['address']));
        if (count($parts) >= 2) {
            $city = $parts[count($parts) - 2];
        }
    } elseif (is_array($data['address']) && isset($data['address']['addressLocality'])) {
        $city = $data['address']['addressLocality'];
    }

    // Generate title
    $title = $data['name'];
    if ($city) {
        $title .= " | {$city}";
    }
    $trade_name = ucwords(str_replace('_', ' ', $data['trade']));
    $title .= " | {$trade_name} Services";

    $tags[] = "<title>{$title}</title>";

    // Generate meta description
    $description = isset($data['description']) && !empty($data['description'])
        ? $data['description']
        : "Professional {$trade_name} services from {$data['name']}";

    if ($city) {
        $description .= " serving {$city}";
    }

    if (isset($data['service_areas']) && is_array($data['service_areas']) && count($data['service_areas']) > 1) {
        $description .= " and surrounding areas";
    }

    if (isset($data['rating'])) {
        $description .= ". Rated {$data['rating']}/5";
    }

    if (isset($data['review_count'])) {
        $description .= " from {$data['review_count']} reviews";
    }

    $description .= ". Call {$data['phone']} today!";

    $tags[] = '<meta name="description" content="' . htmlspecialchars($description) . '">';

    // Open Graph tags
    $tags[] = '<meta property="og:type" content="business.business">';
    $tags[] = '<meta property="og:title" content="' . htmlspecialchars($data['name']) . '">';
    $tags[] = '<meta property="og:description" content="' . htmlspecialchars($description) . '">';

    if (isset($data['website'])) {
        $tags[] = '<meta property="og:url" content="' . htmlspecialchars($data['website']) . '">';
    }

    if (isset($data['image'])) {
        $tags[] = '<meta property="og:image" content="' . htmlspecialchars($data['image']) . '">';
    }

    $tags[] = '<meta property="business:contact_data:street_address" content="' . htmlspecialchars($data['address']) . '">';
    $tags[] = '<meta property="business:contact_data:phone_number" content="' . htmlspecialchars($data['phone']) . '">';

    if (isset($data['email'])) {
        $tags[] = '<meta property="business:contact_data:email" content="' . htmlspecialchars($data['email']) . '">';
    }

    // Twitter Card tags
    $tags[] = '<meta name="twitter:card" content="summary">';
    $tags[] = '<meta name="twitter:title" content="' . htmlspecialchars($data['name']) . '">';
    $tags[] = '<meta name="twitter:description" content="' . htmlspecialchars($description) . '">';

    if (isset($data['image'])) {
        $tags[] = '<meta name="twitter:image" content="' . htmlspecialchars($data['image']) . '">';
    }

    // Additional SEO meta tags
    if ($city) {
        $tags[] = '<meta name="geo.region" content="US">';
        $tags[] = '<meta name="geo.placename" content="' . htmlspecialchars($city) . '">';

        if (isset($data['lat']) && isset($data['lng'])) {
            $tags[] = '<meta name="geo.position" content="' . $data['lat'] . ';' . $data['lng'] . '">';
            $tags[] = '<meta name="ICBM" content="' . $data['lat'] . ', ' . $data['lng'] . '">';
        }
    }

    return implode("\n", $tags);
}

/**
 * Generate complete HTML head section with schema and meta tags
 *
 * @param array $contractor_data Contractor data
 * @return string Complete HTML head content
 */
function generate_head_section($contractor_data) {
    $meta_tags = generate_meta_tags($contractor_data);
    $schema = generate_contractor_schema($contractor_data);

    return $meta_tags . "\n\n" . $schema;
}

/**
 * Validate contractor data structure
 *
 * @param array $data Contractor data to validate
 * @return bool True if valid
 * @throws InvalidArgumentException if validation fails
 */
function validate_contractor_data($data) {
    $required = ['name', 'trade', 'phone', 'address'];

    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new InvalidArgumentException("Missing required field: {$field}");
        }
    }

    // Validate email if provided
    if (isset($data['email']) && !empty($data['email'])) {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format: {$data['email']}");
        }
    }

    // Validate rating if provided
    if (isset($data['rating'])) {
        $rating = (float) $data['rating'];
        if ($rating < 0 || $rating > 5) {
            throw new InvalidArgumentException("Rating must be between 0 and 5");
        }
    }

    // Validate coordinates if provided
    if (isset($data['lat']) || isset($data['lng'])) {
        if (!isset($data['lat']) || !isset($data['lng'])) {
            throw new InvalidArgumentException("Both lat and lng must be provided together");
        }

        $lat = (float) $data['lat'];
        $lng = (float) $data['lng'];

        if ($lat < -90 || $lat > 90) {
            throw new InvalidArgumentException("Latitude must be between -90 and 90");
        }

        if ($lng < -180 || $lng > 180) {
            throw new InvalidArgumentException("Longitude must be between -180 and 180");
        }
    }

    // Validate review count if rating provided
    if (isset($data['rating']) && !isset($data['review_count'])) {
        throw new InvalidArgumentException("review_count required when rating is provided");
    }

    return true;
}

/**
 * Get trade-specific keywords for SEO
 *
 * @param string $trade Trade type
 * @return array Keywords related to trade
 */
function get_trade_keywords($trade) {
    $keywords = [
        'hvac' => ['heating', 'cooling', 'furnace', 'air conditioning', 'AC repair', 'HVAC service'],
        'plumbing' => ['plumber', 'drain cleaning', 'pipe repair', 'water heater', 'leak repair'],
        'electrical' => ['electrician', 'wiring', 'electrical repair', 'panel upgrade', 'outlet installation'],
        'roofing' => ['roof repair', 'roof replacement', 'shingle installation', 'roof inspection'],
        'painting' => ['interior painting', 'exterior painting', 'house painter', 'commercial painting'],
        'landscaping' => ['lawn care', 'landscaping design', 'irrigation', 'tree service'],
        'general_contractor' => ['home remodeling', 'construction', 'renovation', 'home improvement'],
        'locksmith' => ['lock installation', 'key duplication', 'emergency lockout', 'security systems']
    ];

    $trade_lower = strtolower(trim($trade));
    return isset($keywords[$trade_lower]) ? $keywords[$trade_lower] : [];
}

// Example usage (commented out)
/*
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
    'hours' => 'Mo-Fr 07:00-18:00',
    'email' => 'info@pyramidheating.com',
    'website' => 'https://pyramidheating.com',
    'description' => 'Professional HVAC services for residential and commercial properties',
    'price_range' => '$$',
    'social_urls' => [
        'https://facebook.com/pyramidheating',
        'https://twitter.com/pyramidheating'
    ]
];

echo generate_head_section($contractor_data);
*/
