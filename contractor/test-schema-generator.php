<?php
/**
 * Test Suite for Contractor Schema Generator
 */

require_once 'contractor-schema-generator.php';

class SchemaGeneratorTest {
    private $passed = 0;
    private $failed = 0;
    private $tests = [];

    public function assert($condition, $message) {
        if ($condition) {
            $this->passed++;
            $this->tests[] = "✓ {$message}";
        } else {
            $this->failed++;
            $this->tests[] = "✗ {$message}";
        }
    }

    public function assertEquals($expected, $actual, $message) {
        $this->assert($expected === $actual, $message);
    }

    public function assertContains($needle, $haystack, $message) {
        $this->assert(strpos($haystack, $needle) !== false, $message);
    }

    public function assertNotContains($needle, $haystack, $message) {
        $this->assert(strpos($haystack, $needle) === false, $message);
    }

    public function assertTrue($condition, $message) {
        $this->assert($condition === true, $message);
    }

    public function assertFalse($condition, $message) {
        $this->assert($condition === false, $message);
    }

    public function assertArrayHasKey($key, $array, $message) {
        $this->assert(array_key_exists($key, $array), $message);
    }

    public function report() {
        echo "\n=== SCHEMA GENERATOR TEST RESULTS ===\n\n";
        foreach ($this->tests as $test) {
            echo $test . "\n";
        }
        echo "\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        echo "\n";

        if ($this->failed === 0) {
            echo "🎉 ALL TESTS PASSED!\n\n";
        } else {
            echo "❌ SOME TESTS FAILED\n\n";
        }

        return $this->failed === 0;
    }
}

$test = new SchemaGeneratorTest();

// Test data
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

// Test 1: Basic schema generation
$schema = generate_contractor_schema($data);
$test->assertContains('<script type="application/ld+json">', $schema, 'Schema includes script tag');
$test->assertContains('"@context": "https://schema.org"', $schema, 'Schema has @context');

// Test 2: Schema type mapping
$test->assertContains('"HVACBusiness"', $schema, 'HVAC trade mapped to HVACBusiness');
$test->assertContains('"LocalBusiness"', $schema, 'LocalBusiness included in @type');

// Test 3: Required fields present
$test->assertContains('"name": "Pyramid Heating & Cooling"', $schema, 'Name included in schema');
$test->assertContains('"telephone": "(503) 555-0142"', $schema, 'Phone included in schema');

// Test 4: Address parsing
$test->assertContains('"@type": "PostalAddress"', $schema, 'PostalAddress type included');
$test->assertContains('"addressLocality": "Portland"', $schema, 'City parsed from address');
$test->assertContains('"addressRegion": "OR"', $schema, 'State parsed from address');

// Test 5: Geo coordinates
$test->assertContains('"@type": "GeoCoordinates"', $schema, 'GeoCoordinates included');
$test->assertContains('"latitude": 45.5231', $schema, 'Latitude included');
$test->assertContains('"longitude": -122.6765', $schema, 'Longitude included');

// Test 6: Opening hours
$test->assertContains('"openingHours"', $schema, 'Opening hours included');
$test->assertContains('Mo-Fr 07:00-18:00', $schema, 'Hours format preserved');

// Test 7: Aggregate rating
$test->assertContains('"@type": "AggregateRating"', $schema, 'AggregateRating included');
$test->assertContains('"ratingValue": 4.7', $schema, 'Rating value included');
$test->assertContains('"reviewCount": 143', $schema, 'Review count included');

// Test 8: Services as offer catalog
$test->assertContains('"hasOfferCatalog"', $schema, 'Offer catalog included');
$test->assertContains('Furnace Repair', $schema, 'Service 1 included');
$test->assertContains('AC Install', $schema, 'Service 2 included');

// Test 9: Service areas
$test->assertContains('"areaServed"', $schema, 'Area served included');
$test->assertContains('Portland', $schema, 'Service area 1 included');
$test->assertContains('Beaverton', $schema, 'Service area 2 included');

// Test 10: Meta tags generation
$meta = generate_meta_tags($data);
$test->assertContains('<title>', $meta, 'Title tag generated');
$test->assertContains('Pyramid Heating & Cooling', $meta, 'Company name in title');
$test->assertContains('Portland', $meta, 'City in title');
$test->assertContains('<meta name="description"', $meta, 'Meta description generated');

// Test 11: Open Graph tags
$test->assertContains('<meta property="og:type" content="business.business">', $meta, 'OG type tag included');
$test->assertContains('<meta property="og:title"', $meta, 'OG title tag included');
$test->assertContains('<meta property="og:description"', $meta, 'OG description tag included');

// Test 12: Twitter Card tags
$test->assertContains('<meta name="twitter:card" content="summary">', $meta, 'Twitter card tag included');
$test->assertContains('<meta name="twitter:title"', $meta, 'Twitter title tag included');

// Test 13: Geo meta tags
$test->assertContains('<meta name="geo.placename"', $meta, 'Geo placename tag included');
$test->assertContains('<meta name="geo.position"', $meta, 'Geo position tag included');

// Test 14: Different trade types
$plumber_data = $data;
$plumber_data['trade'] = 'plumbing';
$plumber_schema = generate_contractor_schema($plumber_data);
$test->assertContains('"Plumber"', $plumber_schema, 'Plumbing trade mapped correctly');

$electrician_data = $data;
$electrician_data['trade'] = 'electrical';
$electrician_schema = generate_contractor_schema($electrician_data);
$test->assertContains('"Electrician"', $electrician_schema, 'Electrical trade mapped correctly');

$roofer_data = $data;
$roofer_data['trade'] = 'roofing';
$roofer_schema = generate_contractor_schema($roofer_data);
$test->assertContains('"RoofingContractor"', $roofer_schema, 'Roofing trade mapped correctly');

// Test 15: Missing required field
try {
    $incomplete_data = $data;
    unset($incomplete_data['phone']);
    generate_contractor_schema($incomplete_data);
    $test->assert(false, 'Missing required field throws exception');
} catch (InvalidArgumentException $e) {
    $test->assert(true, 'Missing required field throws exception');
}

// Test 16: Email validation
$email_data = $data;
$email_data['email'] = 'info@pyramidheating.com';
$email_schema = generate_contractor_schema($email_data);
$test->assertContains('"email": "info@pyramidheating.com"', $email_schema, 'Email included when provided');

try {
    $bad_email = $data;
    $bad_email['email'] = 'not-an-email';
    validate_contractor_data($bad_email);
    $test->assert(false, 'Invalid email throws exception');
} catch (InvalidArgumentException $e) {
    $test->assert(true, 'Invalid email throws exception');
}

// Test 17: Rating validation
try {
    $bad_rating = $data;
    $bad_rating['rating'] = 6.0;
    validate_contractor_data($bad_rating);
    $test->assert(false, 'Rating > 5 throws exception');
} catch (InvalidArgumentException $e) {
    $test->assert(true, 'Rating > 5 throws exception');
}

// Test 18: Coordinates validation
try {
    $bad_coords = $data;
    $bad_coords['lat'] = 200;
    validate_contractor_data($bad_coords);
    $test->assert(false, 'Invalid latitude throws exception');
} catch (InvalidArgumentException $e) {
    $test->assert(true, 'Invalid latitude throws exception');
}

// Test 19: Website URL
$website_data = $data;
$website_data['website'] = 'https://pyramidheating.com';
$website_schema = generate_contractor_schema($website_data);
$test->assertContains('"url": "https://pyramidheating.com"', $website_schema, 'Website URL included');

// Test 20: Social media URLs
$social_data = $data;
$social_data['social_urls'] = [
    'https://facebook.com/pyramidheating',
    'https://twitter.com/pyramidheating'
];
$social_schema = generate_contractor_schema($social_data);
$test->assertContains('"sameAs"', $social_schema, 'SameAs property included');
$test->assertContains('facebook.com', $social_schema, 'Facebook URL included');
$test->assertContains('twitter.com', $social_schema, 'Twitter URL included');

// Test 21: Price range
$price_data = $data;
$price_data['price_range'] = '$$$';
$price_schema = generate_contractor_schema($price_data);
$test->assertContains('"priceRange": "$$$"', $price_schema, 'Custom price range included');

// Test 22: Default price range
$test->assertContains('"priceRange": "$$"', $schema, 'Default price range is $$');

// Test 23: Description
$desc_data = $data;
$desc_data['description'] = 'Professional HVAC services';
$desc_schema = generate_contractor_schema($desc_data);
$test->assertContains('"description": "Professional HVAC services"', $desc_schema, 'Description included');

// Test 24: Logo and image
$img_data = $data;
$img_data['logo'] = 'https://example.com/logo.png';
$img_data['image'] = 'https://example.com/image.jpg';
$img_schema = generate_contractor_schema($img_data);
$test->assertContains('"logo": "https://example.com/logo.png"', $img_schema, 'Logo URL included');
$test->assertContains('"image": "https://example.com/image.jpg"', $img_schema, 'Image URL included');

// Test 25: Payment methods
$payment_data = $data;
$payment_data['payment_accepted'] = ['Cash', 'Credit Card', 'Check'];
$payment_schema = generate_contractor_schema($payment_data);
$test->assertContains('"paymentAccepted": "Cash, Credit Card, Check"', $payment_schema, 'Payment methods included');

// Test 26: 24/7 hours format
$full_time_data = $data;
$full_time_data['hours'] = '24/7';
$full_time_schema = generate_contractor_schema($full_time_data);
$test->assertContains('Monday-Sunday 00:00-23:59', $full_time_schema, '24/7 hours formatted correctly');

// Test 27: Full address parsing
$full_address_data = $data;
$full_address_data['address'] = '123 Main St, Portland, OR 97201';
$full_address_schema = generate_contractor_schema($full_address_data);
$test->assertContains('"streetAddress": "123 Main St"', $full_address_schema, 'Street address parsed');
$test->assertContains('"postalCode": "97201"', $full_address_schema, 'ZIP code parsed');

// Test 28: Array address format
$array_address_data = $data;
$array_address_data['address'] = [
    'streetAddress' => '456 Oak Ave',
    'addressLocality' => 'Beaverton',
    'addressRegion' => 'OR',
    'postalCode' => '97005',
    'addressCountry' => 'US'
];
$array_address_schema = generate_contractor_schema($array_address_data);
$test->assertContains('"streetAddress": "456 Oak Ave"', $array_address_schema, 'Array address format supported');
$test->assertContains('"addressLocality": "Beaverton"', $array_address_schema, 'City from array address');

// Test 29: Founded year
$founded_data = $data;
$founded_data['founded_year'] = 1995;
$founded_schema = generate_contractor_schema($founded_data);
$test->assertContains('"foundingDate": "1995"', $founded_schema, 'Founding year included');

// Test 30: Multiple services
$multi_service_data = $data;
$multi_service_data['services'] = [
    'Furnace Repair',
    'AC Installation',
    'Duct Cleaning',
    'Thermostat Installation',
    'Maintenance Plans'
];
$multi_service_schema = generate_contractor_schema($multi_service_data);
foreach ($multi_service_data['services'] as $service) {
    $test->assertContains($service, $multi_service_schema, "Service '{$service}' included");
}

// Test 31: Rating without review count
try {
    $no_reviews = $data;
    $no_reviews['rating'] = 4.5;
    unset($no_reviews['review_count']);
    validate_contractor_data($no_reviews);
    $test->assert(false, 'Rating without review_count throws exception');
} catch (InvalidArgumentException $e) {
    $test->assert(true, 'Rating without review_count throws exception');
}

// Test 32: Lat without lng
try {
    $partial_coords = $data;
    unset($partial_coords['lng']);
    validate_contractor_data($partial_coords);
    $test->assert(false, 'Lat without lng throws exception');
} catch (InvalidArgumentException $e) {
    $test->assert(true, 'Lat without lng throws exception');
}

// Test 33: Complete head section
$head = generate_head_section($data);
$test->assertContains('<title>', $head, 'Head section includes title');
$test->assertContains('<meta', $head, 'Head section includes meta tags');
$test->assertContains('<script type="application/ld+json">', $head, 'Head section includes schema');

// Test 34: Trade type validation
$locksmith_data = $data;
$locksmith_data['trade'] = 'locksmith';
$locksmith_schema = generate_contractor_schema($locksmith_data);
$test->assertContains('"Locksmith"', $locksmith_schema, 'Locksmith trade mapped correctly');

// Test 35: Unknown trade type fallback
$unknown_trade_data = $data;
$unknown_trade_data['trade'] = 'unknown_service';
$unknown_schema = generate_contractor_schema($unknown_trade_data);
$test->assertContains('"LocalBusiness"', $unknown_schema, 'Unknown trade falls back to LocalBusiness');

// Test 36: Special characters in name
$special_name_data = $data;
$special_name_data['name'] = "O'Reilly's HVAC & Cooling";
$special_name_schema = generate_contractor_schema($special_name_data);
$test->assertContains("O'Reilly's HVAC & Cooling", $special_name_schema, 'Special characters in name handled');

// Test 37: Meta description includes rating
$meta_with_rating = generate_meta_tags($data);
$test->assertContains('4.7/5', $meta_with_rating, 'Meta description includes rating');
$test->assertContains('143 reviews', $meta_with_rating, 'Meta description includes review count');

// Test 38: Meta description without rating
$no_rating_data = $data;
unset($no_rating_data['rating']);
unset($no_rating_data['review_count']);
$meta_no_rating = generate_meta_tags($no_rating_data);
$test->assertNotContains('/5', $meta_no_rating, 'Meta description excludes rating when not provided');

// Test 39: Trade keywords function
$hvac_keywords = get_trade_keywords('hvac');
$test->assertTrue(in_array('heating', $hvac_keywords), 'HVAC keywords include heating');
$test->assertTrue(in_array('cooling', $hvac_keywords), 'HVAC keywords include cooling');

$plumbing_keywords = get_trade_keywords('plumbing');
$test->assertTrue(in_array('plumber', $plumbing_keywords), 'Plumbing keywords include plumber');

// Test 40: JSON validity
$test->assertTrue(json_decode(str_replace(['<script type="application/ld+json">', '</script>'], '', $schema)) !== null, 'Generated JSON is valid');

// Generate sample output
echo "\n=== SAMPLE SCHEMA OUTPUT ===\n\n";
echo substr($schema, 0, 500) . "...\n\n";

echo "=== SAMPLE META TAGS ===\n\n";
$meta_lines = explode("\n", $meta);
echo implode("\n", array_slice($meta_lines, 0, 8)) . "\n...\n\n";

echo "=== SAMPLE COMPLETE HEAD ===\n\n";
$complete_data = [
    'name' => 'Pyramid Heating & Cooling',
    'trade' => 'hvac',
    'phone' => '(503) 555-0142',
    'email' => 'info@pyramidheating.com',
    'website' => 'https://pyramidheating.com',
    'address' => '123 Industrial Pkwy, Portland, OR 97210',
    'lat' => 45.5231,
    'lng' => -122.6765,
    'rating' => 4.7,
    'review_count' => 143,
    'services' => ['Furnace Repair', 'AC Installation', 'Duct Cleaning'],
    'service_areas' => ['Portland', 'Beaverton', 'Hillsboro'],
    'hours' => 'Mo-Fr 07:00-18:00',
    'description' => 'Professional HVAC services for residential and commercial properties',
    'price_range' => '$$',
    'image' => 'https://pyramidheating.com/images/hero.jpg',
    'logo' => 'https://pyramidheating.com/logo.png',
    'social_urls' => [
        'https://facebook.com/pyramidheating',
        'https://twitter.com/pyramidheating'
    ],
    'payment_accepted' => ['Cash', 'Credit Card', 'Check', 'Financing'],
    'founded_year' => 1995
];

$complete_head = generate_head_section($complete_data);
$head_lines = explode("\n", $complete_head);
echo "Total lines: " . count($head_lines) . "\n";
echo "First 10 lines:\n" . implode("\n", array_slice($head_lines, 0, 10)) . "\n\n";

$success = $test->report();
exit($success ? 0 : 1);
