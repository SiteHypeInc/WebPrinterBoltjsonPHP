<?php
/**
 * Elementor Data Structure Analyzer & Contractor Data Injector
 *
 * Usage:
 * 1. Optionally connect to WordPress DB to fetch real Elementor data
 * 2. Analyze Elementor JSON structure and map editable fields
 * 3. Inject contractor data into Elementor templates
 */

class ElementorAnalyzer {
    private $db = null;
    private $structure_map = [];
    private $editable_paths = [];

    /**
     * Connect to WordPress database (optional)
     */
    public function connectDatabase($host, $dbname, $username, $password) {
        try {
            $this->db = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            return true;
        } catch (PDOException $e) {
            echo "Database connection failed: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Get database connection
     */
    public function getDb() {
        return $this->db;
    }

    /**
     * Fetch Elementor data from WordPress database
     */
    public function fetchElementorData($post_id = null, $limit = 1) {
        if (!$this->db) {
            throw new Exception("Database not connected. Call connectDatabase() first.");
        }

        $sql = "SELECT post_id, meta_value
                FROM wp_postmeta
                WHERE meta_key = '_elementor_data'";

        if ($post_id) {
            $sql .= " AND post_id = :post_id";
        }

        $sql .= " LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        if ($post_id) {
            $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Analyze Elementor JSON structure
     */
    public function analyzeStructure($elementor_json_string) {
        $data = json_decode($elementor_json_string, true);

        if (!$data) {
            throw new Exception("Invalid JSON provided");
        }

        $this->structure_map = [];
        $this->editable_paths = [];

        // Walk through the structure
        $this->walkStructure($data, []);

        return [
            'structure_map' => $this->structure_map,
            'editable_paths' => $this->editable_paths
        ];
    }

    /**
     * Recursively walk through Elementor structure
     */
    private function walkStructure($data, $path) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $current_path = array_merge($path, [$key]);

                // Record element type information
                if ($key === 'elType') {
                    $this->recordElement($path, $data);
                }

                // Record widget type information
                if ($key === 'widgetType') {
                    $this->recordWidget($path, $data);
                }

                // Record editable fields
                if ($this->isEditableField($key, $value)) {
                    $this->recordEditableField($current_path, $key, $value);
                }

                // Recurse
                if (is_array($value)) {
                    $this->walkStructure($value, $current_path);
                }
            }
        }
    }

    /**
     * Record element information
     */
    private function recordElement($path, $data) {
        $element_type = $data['elType'] ?? 'unknown';

        if (!isset($this->structure_map[$element_type])) {
            $this->structure_map[$element_type] = [
                'count' => 0,
                'samples' => []
            ];
        }

        $this->structure_map[$element_type]['count']++;

        // Store first sample of each type
        if (count($this->structure_map[$element_type]['samples']) < 2) {
            $this->structure_map[$element_type]['samples'][] = [
                'path' => implode(' > ', $path),
                'structure' => $this->getElementStructure($data)
            ];
        }
    }

    /**
     * Record widget information
     */
    private function recordWidget($path, $data) {
        $widget_type = $data['widgetType'] ?? 'unknown';
        $key = "widget_$widget_type";

        if (!isset($this->structure_map[$key])) {
            $this->structure_map[$key] = [
                'count' => 0,
                'samples' => []
            ];
        }

        $this->structure_map[$key]['count']++;

        if (count($this->structure_map[$key]['samples']) < 2) {
            $this->structure_map[$key]['samples'][] = [
                'path' => implode(' > ', $path),
                'settings' => $data['settings'] ?? []
            ];
        }
    }

    /**
     * Check if field is editable
     */
    private function isEditableField($key, $value) {
        // Common editable field names in Elementor
        $editable_keys = [
            'title', 'text', 'description', 'content', 'heading',
            'caption', 'url', 'link', 'image', 'button_text',
            'name', 'phone', 'email', 'address', 'label',
            'placeholder', 'value', 'prefix', 'suffix',
            'before_text', 'after_text', 'inner_text'
        ];

        if (in_array($key, $editable_keys) && is_string($value)) {
            return true;
        }

        // Check for text-like values
        if (is_string($value) && strlen($value) > 0 && strlen($value) < 1000) {
            // Exclude system values
            $system_patterns = ['/^_/', '/^elementor-/', '/^icon-/', '/^fa-/', '/^#[0-9a-f]{6}$/i'];
            foreach ($system_patterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Record editable field
     */
    private function recordEditableField($path, $key, $value) {
        $this->editable_paths[] = [
            'path' => $path,
            'field' => $key,
            'sample_value' => $value,
            'json_path' => $this->pathToJsonPath($path)
        ];
    }

    /**
     * Convert array path to JSON path notation
     */
    private function pathToJsonPath($path) {
        $json_path = '$';
        foreach ($path as $segment) {
            if (is_numeric($segment)) {
                $json_path .= "[$segment]";
            } else {
                $json_path .= "['$segment']";
            }
        }
        return $json_path;
    }

    /**
     * Get simplified element structure
     */
    private function getElementStructure($data) {
        return [
            'elType' => $data['elType'] ?? null,
            'widgetType' => $data['widgetType'] ?? null,
            'has_settings' => isset($data['settings']),
            'has_elements' => isset($data['elements']),
            'settings_keys' => isset($data['settings']) ? array_keys($data['settings']) : []
        ];
    }

    /**
     * Generate human-readable report
     */
    public function generateReport() {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "ELEMENTOR STRUCTURE ANALYSIS REPORT\n";
        echo str_repeat("=", 80) . "\n\n";

        echo "ELEMENT HIERARCHY:\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($this->structure_map as $type => $info) {
            echo "• $type (found {$info['count']} instances)\n";
            foreach ($info['samples'] as $sample) {
                echo "  Path: {$sample['path']}\n";
                if (isset($sample['structure'])) {
                    echo "  Structure: " . json_encode($sample['structure'], JSON_PRETTY_PRINT) . "\n";
                }
                if (isset($sample['settings'])) {
                    echo "  Settings: " . json_encode($sample['settings'], JSON_PRETTY_PRINT) . "\n";
                }
            }
            echo "\n";
        }

        echo "\nEDITABLE FIELDS:\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($this->editable_paths as $field) {
            echo "• {$field['json_path']}\n";
            echo "  Field: {$field['field']}\n";
            echo "  Sample: " . substr($field['sample_value'], 0, 100) . "\n\n";
        }
    }
}

/**
 * Inject contractor data into Elementor JSON template
 */
function inject_contractor_data($elementor_json, $contractor_data) {
    $data = is_string($elementor_json) ? json_decode($elementor_json, true) : $elementor_json;

    if (!$data) {
        throw new Exception("Invalid Elementor JSON provided");
    }

    // Mapping rules: field patterns to contractor data keys
    $mapping_rules = [
        // Hero section
        'hero_headline' => ['heading', 'title', 'main_heading'],
        'hero_sub' => ['description', 'subtitle', 'sub_heading', 'tagline'],

        // Contact information
        'phone' => ['phone', 'telephone', 'call', 'contact_number'],
        'name' => ['company_name', 'business_name', 'name', 'title'],
        'address' => ['address', 'location', 'street_address'],
        'email' => ['email', 'contact_email'],

        // Content
        'about' => ['about', 'about_us', 'description', 'company_description'],
        'service_area' => ['service_area', 'area', 'coverage'],

        // Services
        'services' => ['services', 'service_list', 'offerings']
    ];

    // Walk and replace
    $modified_data = inject_recursive($data, $contractor_data, $mapping_rules);

    return json_encode($modified_data);
}

/**
 * Recursive injection helper
 */
function inject_recursive($data, $contractor_data, $mapping_rules) {
    if (!is_array($data)) {
        return $data;
    }

    foreach ($data as $key => $value) {
        // Check if this is a widget with settings
        if ($key === 'settings' && is_array($value)) {
            $data[$key] = inject_widget_settings($value, $contractor_data, $mapping_rules);
        }
        // Recurse into arrays
        else if (is_array($value)) {
            $data[$key] = inject_recursive($value, $contractor_data, $mapping_rules);
        }
        // Replace string values
        else if (is_string($value)) {
            $data[$key] = replace_template_value($value, $contractor_data);
        }
    }

    return $data;
}

/**
 * Inject data into widget settings
 */
function inject_widget_settings($settings, $contractor_data, $mapping_rules) {
    foreach ($settings as $setting_key => $setting_value) {
        // Check each mapping rule
        foreach ($mapping_rules as $data_key => $field_patterns) {
            if (!isset($contractor_data[$data_key])) {
                continue;
            }

            // If setting key matches any pattern
            foreach ($field_patterns as $pattern) {
                if (stripos($setting_key, $pattern) !== false) {
                    // Handle arrays (like services)
                    if (is_array($contractor_data[$data_key])) {
                        $settings[$setting_key] = implode(', ', $contractor_data[$data_key]);
                    } else {
                        $settings[$setting_key] = $contractor_data[$data_key];
                    }
                    break 2; // Found match, move to next setting
                }
            }
        }

        // Handle nested arrays
        if (is_array($setting_value)) {
            $settings[$setting_key] = inject_widget_settings($setting_value, $contractor_data, $mapping_rules);
        }
        // Handle template placeholders
        else if (is_string($setting_value)) {
            $settings[$setting_key] = replace_template_value($setting_value, $contractor_data);
        }
    }

    return $settings;
}

/**
 * Replace template placeholders in string values
 */
function replace_template_value($value, $contractor_data) {
    // Handle {{placeholder}} syntax
    foreach ($contractor_data as $key => $data_value) {
        $placeholder = '{{' . $key . '}}';
        if (stripos($value, $placeholder) !== false) {
            $replacement = is_array($data_value) ? implode(', ', $data_value) : $data_value;
            $value = str_replace($placeholder, $replacement, $value);
        }
    }

    return $value;
}

// ============================================================================
// EXAMPLE USAGE
// ============================================================================

// Sample Elementor JSON structure (typical structure)
$sample_elementor_json = json_encode([
    [
        'id' => '1a2b3c4d',
        'elType' => 'section',
        'settings' => [
            'layout' => 'boxed'
        ],
        'elements' => [
            [
                'id' => '5e6f7g8h',
                'elType' => 'column',
                'settings' => [
                    '_column_size' => 100
                ],
                'elements' => [
                    [
                        'id' => '9i0j1k2l',
                        'elType' => 'widget',
                        'widgetType' => 'heading',
                        'settings' => [
                            'title' => '{{hero_headline}}',
                            'header_size' => 'h1'
                        ]
                    ],
                    [
                        'id' => '3m4n5o6p',
                        'elType' => 'widget',
                        'widgetType' => 'text-editor',
                        'settings' => [
                            'editor' => '{{hero_sub}}'
                        ]
                    ]
                ]
            ]
        ]
    ],
    [
        'id' => '7q8r9s0t',
        'elType' => 'section',
        'settings' => [],
        'elements' => [
            [
                'id' => '1u2v3w4x',
                'elType' => 'column',
                'settings' => [],
                'elements' => [
                    [
                        'id' => '5y6z7a8b',
                        'elType' => 'widget',
                        'widgetType' => 'heading',
                        'settings' => [
                            'title' => 'About {{name}}'
                        ]
                    ],
                    [
                        'id' => '9c0d1e2f',
                        'elType' => 'widget',
                        'widgetType' => 'text-editor',
                        'settings' => [
                            'editor' => '{{about}}'
                        ]
                    ]
                ]
            ],
            [
                'id' => '3g4h5i6j',
                'elType' => 'column',
                'settings' => [],
                'elements' => [
                    [
                        'id' => '7k8l9m0n',
                        'elType' => 'widget',
                        'widgetType' => 'icon-box',
                        'settings' => [
                            'title' => 'Call Us',
                            'description' => '{{phone}}'
                        ]
                    ],
                    [
                        'id' => '1o2p3q4r',
                        'elType' => 'widget',
                        'widgetType' => 'icon-box',
                        'settings' => [
                            'title' => 'Visit Us',
                            'description' => '{{address}}'
                        ]
                    ]
                ]
            ]
        ]
    ],
    [
        'id' => '5s6t7u8v',
        'elType' => 'section',
        'settings' => [],
        'elements' => [
            [
                'id' => '9w0x1y2z',
                'elType' => 'column',
                'settings' => [],
                'elements' => [
                    [
                        'id' => '3a4b5c6d',
                        'elType' => 'widget',
                        'widgetType' => 'heading',
                        'settings' => [
                            'title' => 'Our Services'
                        ]
                    ],
                    [
                        'id' => '7e8f9g0h',
                        'elType' => 'widget',
                        'widgetType' => 'text-editor',
                        'settings' => [
                            'editor' => 'We offer: {{services}}'
                        ]
                    ]
                ]
            ]
        ]
    ]
]);

// Run the demonstration
echo "\n" . str_repeat("=", 80) . "\n";
echo "ELEMENTOR ANALYZER - DEMONSTRATION\n";
echo str_repeat("=", 80) . "\n\n";

// 1. Analyze structure
echo "Step 1: Analyzing Elementor structure...\n\n";
$analyzer = new ElementorAnalyzer();
$analysis = $analyzer->analyzeStructure($sample_elementor_json);
$analyzer->generateReport();

// 2. Inject contractor data
echo "\n" . str_repeat("=", 80) . "\n";
echo "Step 2: Injecting contractor data...\n";
echo str_repeat("=", 80) . "\n\n";

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

$populated_json = inject_contractor_data($sample_elementor_json, $contractor_data);

echo "ORIGINAL TEMPLATE (excerpt):\n";
echo substr($sample_elementor_json, 0, 500) . "...\n\n";

echo "POPULATED JSON (excerpt):\n";
echo substr($populated_json, 0, 500) . "...\n\n";

echo "FULL POPULATED JSON:\n";
echo json_encode(json_decode($populated_json), JSON_PRETTY_PRINT) . "\n\n";

echo "\n" . str_repeat("=", 80) . "\n";
echo "USAGE WITH DATABASE:\n";
echo str_repeat("=", 80) . "\n";
echo "
// Connect to WordPress database
\$analyzer->connectDatabase('localhost', 'wordpress_db', 'user', 'password');

// Fetch real Elementor data
\$pages = \$analyzer->fetchElementorData(123); // specific post_id
// or
\$pages = \$analyzer->fetchElementorData(null, 5); // first 5 pages

// Analyze real data
foreach (\$pages as \$page) {
    \$analysis = \$analyzer->analyzeStructure(\$page['meta_value']);
    \$analyzer->generateReport();
}

// Inject data and update database
\$original_json = \$pages[0]['meta_value'];
\$populated = inject_contractor_data(\$original_json, \$contractor_data);

// Update back to database (example)
\$stmt = \$db->prepare('UPDATE wp_postmeta SET meta_value = :value WHERE post_id = :id AND meta_key = \"_elementor_data\"');
\$stmt->execute(['value' => \$populated, 'id' => 123]);
";

echo "\n✓ Script complete. You can now:\n";
echo "  1. Use this script standalone to analyze sample JSON\n";
echo "  2. Connect to a WordPress DB to analyze real Elementor pages\n";
echo "  3. Use inject_contractor_data() to populate templates\n";
echo "  4. Integrate into your automation workflow\n\n";
