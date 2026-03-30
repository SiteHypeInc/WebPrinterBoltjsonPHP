<?php
/**
 * Plugin Name: WebPrinter Engine
 * Description: Template-agnostic REST endpoint to deploy contractor demo sites from n8n.
 * Version:     4.6
 * Author:      Team Platypus
 *
 * REQUIRES in wp-config.php:
 *   define( 'WP_TEMPLATE_BASE', 'https://raw.githubusercontent.com/SiteHypeInc/WebPrinterBoltjsonPHP/main' );
 *
 * n8n payload must include:
 *   "template": "bold-v2"
 *   "blog_id": 2
 *   ... all other fields
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class WebPrinter_Engine {

    /**
     * Image slot keys -> n8n param names.
     * Add new slots here to support additional image fields across templates.
     */
    const IMAGE_SLOTS = [
        'hero'    => 'hero_image_url',
        'about'   => 'about_image_url',
        'service' => 'service_image_url',
        'logo'    => 'logo_url',
    ];

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'webprinter/v1', '/deploy', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'handle_deploy' ],
            'permission_callback' => [ $this, 'check_auth' ],
        ] );
        register_rest_route( 'webprinter/v1', '/setup-breeze', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'handle_setup_breeze' ],
            'permission_callback' => [ $this, 'check_auth' ],
        ] );
    }

    /**
     * Set Breeze cache exclusion URLs for all demo slot blogs.
     * Call once after initial deploy to prevent Breeze from re-caching
     * demo sites without Elementor CSS after TTL expiry.
     *
     * POST /wp-json/webprinter/v1/setup-breeze
     * Body: { "blog_ids": [4, 7, 10], "paths": ["/slot-hvac-1/", ...] }
     */
    public function handle_setup_breeze( WP_REST_Request $request ) {
        if ( ! is_multisite() ) {
            return new WP_REST_Response( [ 'success' => false, 'error' => 'Not multisite' ], 400 );
        }

        $params   = $request->get_json_params() ?: $request->get_params();
        $blog_ids = array_map( 'intval', $params['blog_ids'] ?? [] );
        $paths    = array_map( 'sanitize_text_field', $params['paths'] ?? [] );

        if ( empty( $blog_ids ) || empty( $paths ) ) {
            return new WP_REST_Response( [ 'success' => false, 'error' => 'blog_ids and paths required' ], 400 );
        }

        $results = [];
        foreach ( $blog_ids as $blog_id ) {
            switch_to_blog( $blog_id );

            $settings = get_option( 'breeze_basic_settings', [] );
            if ( ! is_array( $settings ) ) $settings = [];

            // Merge new paths into existing exclusions, deduplicated
            $existing   = isset( $settings['breeze-exclude-urls'] ) ? (array) $settings['breeze-exclude-urls'] : [];
            $merged     = array_values( array_unique( array_merge( $existing, $paths ) ) );
            $settings['breeze-exclude-urls'] = $merged;
            update_option( 'breeze_basic_settings', $settings );

            // Also flush any existing Breeze cache for this blog
            do_action( 'breeze_clear_all_cache' );
            if ( class_exists( 'Breeze_PurgeCache' ) ) {
                Breeze_PurgeCache::breeze_cache_flush();
            }

            restore_current_blog();
            $results[ $blog_id ] = [ 'excluded_paths' => $merged ];
        }

        return new WP_REST_Response( [ 'success' => true, 'results' => $results ], 200 );
    }

    public function check_auth( WP_REST_Request $request ) {
        $key = defined( 'WP_WEBPRINTER_KEY' ) ? WP_WEBPRINTER_KEY : '';
        if ( empty( $key ) ) return true;
        return $request->get_header( 'X-Webprinter-Key' ) === $key;
    }

    public function handle_deploy( WP_REST_Request $request ) {
        $params = $request->get_json_params();
        if ( empty( $params ) ) $params = $request->get_params();

        // ---------------------------------------------------------------
        // 1. VALIDATE REQUIRED FIELDS
        // ---------------------------------------------------------------
        $required = [ 'blog_id', 'company_name', 'trade', 'city', 'state' ];
        foreach ( $required as $field ) {
            if ( empty( $params[$field] ) ) {
                return new WP_REST_Response([
                    'success' => false,
                    'error'   => "Missing required field: {$field}",
                ], 400 );
            }
        }

        $blog_id  = intval( $params['blog_id'] );
        $template = sanitize_text_field( $params['template'] ?? 'bold-v2' );

        // ---------------------------------------------------------------
        // 2. BUILD TEMPLATE BASE URL
        // ---------------------------------------------------------------
        if ( ! defined( 'WP_TEMPLATE_BASE' ) ) {
            return new WP_REST_Response([
                'success' => false,
                'error'   => 'WP_TEMPLATE_BASE is not defined in wp-config.php',
            ], 500 );
        }
        $template_base = rtrim( WP_TEMPLATE_BASE, '/' ) . '/' . $template . '/';

        // ---------------------------------------------------------------
        // 3. SWITCH TO TARGET SUBSITE
        // ---------------------------------------------------------------
        if ( is_multisite() ) {
            if ( ! get_blog_details( $blog_id ) ) {
                return new WP_REST_Response([
                    'success' => false,
                    'error'   => "Blog ID {$blog_id} not found on this network.",
                ], 404 );
            }
            switch_to_blog( $blog_id );
        }

        // ---------------------------------------------------------------
        // 3b. SET ELEMENTOR KIT BRAND COLORS (template-aware)
        // ---------------------------------------------------------------
        $template_colors = [
            'authority-v2' => '#C9A84C', // HVAC gold
            'green-v2'     => '#2E7D32', // Roofing green
            'premium-v2'   => '#1A3A5C', // Plumbing navy
        ];
        $accent_color = $template_colors[ strtolower( $template ) ] ?? '#C9A84C';
        $this->set_elementor_kit_accent_color( $accent_color );

        // ---------------------------------------------------------------
        // 3c. CLEAR ALL ELEMENTOR CSS ONCE (before page deployments)
        // Each page deploy regenerates its own CSS via Post::update().
        // Clearing once here prevents stale CSS; clearing per-page would
        // nuke every previously-regenerated file on each loop iteration.
        // ---------------------------------------------------------------
        if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }

        // ---------------------------------------------------------------
        // 4. SIDELOAD ALL IMAGES INTO MEDIA LIBRARY
        // ---------------------------------------------------------------
        $images = [];
        foreach ( self::IMAGE_SLOTS as $slot => $param_key ) {
            $src_url = $params[$param_key] ?? '';
            if ( ! empty( $src_url ) ) {
                $id        = $this->sideload_image( $src_url );
                $local_url = $id ? wp_get_attachment_url( $id ) : esc_url( $src_url );
                $images[$slot] = [ 'url' => $local_url, 'id' => $id ];
            } else {
                $images[$slot] = [ 'url' => '', 'id' => 0 ];
            }
        }

        // ---------------------------------------------------------------
        // 5. BUILD TEXT TOKEN MAP
        // ---------------------------------------------------------------
        $city_state = sanitize_text_field( $params['city'] ) . ', ' . sanitize_text_field( $params['state'] );
        $company    = sanitize_text_field( $params['company_name'] );
        $trade      = sanitize_text_field( $params['trade'] );
        $phone      = sanitize_text_field( $params['phone']   ?? '' );
        $email      = sanitize_email(      $params['email']   ?? '' );

        $years_in_business = sanitize_text_field( $params['years_in_business'] ?? '' );

        $replacements = [
            '[COMPANY NAME]'                => $company,
            '[TAGLINE]'                     => sanitize_text_field( $params['tagline']        ?? "Professional {$trade} Services in {$city_state}" ),
            '[HERO HEADLINE]'               => sanitize_text_field( $params['hero_headline']  ?? $company ),
            '[HERO SUB]'                    => sanitize_text_field( $params['hero_sub']       ?? "Serving {$city_state}" ),
            '[TRADE]'                       => strtoupper( $trade ),
            '[CITY, STATE]'                 => $city_state,
            '[CITY]'                        => sanitize_text_field( $params['city'] ),
            '[STATE]'                       => sanitize_text_field( $params['state'] ),
            '[PHONE]'                       => $phone,
            '[PHONE NUMBER]'                => $phone,
            '[EMAIL]'                       => $email,
            '[EMAIL ADDRESS]'               => $email,
            '[ADDRESS]'                     => sanitize_text_field( $params['address']        ?? '' ),
            '[ABOUT]'                       => wp_kses_post(        $params['about']          ?? '' ),
            '[YEARS IN BUSINESS]'           => $years_in_business ?: '10',
            '[INSTABID EMBED PLACEHOLDER]'  => '',
            '[SERVICE 1 NAME]'              => sanitize_text_field( $params['service_1_name'] ?? '' ),
            '[SERVICE 1 description]'       => wp_kses_post(        $params['service_1_desc'] ?? '' ),
            '[SERVICE 2 NAME]'              => sanitize_text_field( $params['service_2_name'] ?? '' ),
            '[SERVICE 2 description]'       => wp_kses_post(        $params['service_2_desc'] ?? '' ),
            '[SERVICE 3 NAME]'              => sanitize_text_field( $params['service_3_name'] ?? '' ),
            '[SERVICE 3 description]'       => wp_kses_post(        $params['service_3_desc'] ?? '' ),
            '[SERVICE 4 NAME]'              => sanitize_text_field( $params['service_4_name'] ?? '' ),
            '[SERVICE 4 description]'       => wp_kses_post(        $params['service_4_desc'] ?? '' ),
            '[PROCESS 1 TITLE]'             => sanitize_text_field( $params['process_1_title'] ?? 'Free Consultation' ),
            '[PROCESS 1 DESC]'              => wp_kses_post(        $params['process_1_desc']  ?? 'We start with a thorough assessment and provide a free estimate.' ),
            '[PROCESS 2 TITLE]'             => sanitize_text_field( $params['process_2_title'] ?? 'Custom Plan' ),
            '[PROCESS 2 DESC]'              => wp_kses_post(        $params['process_2_desc']  ?? 'We create a plan tailored to your needs and timeline.' ),
            '[PROCESS 3 TITLE]'             => sanitize_text_field( $params['process_3_title'] ?? 'Expert Execution' ),
            '[PROCESS 3 DESC]'              => wp_kses_post(        $params['process_3_desc']  ?? 'Our certified team executes with precision and professionalism.' ),
            '[PROCESS 4 TITLE]'             => sanitize_text_field( $params['process_4_title'] ?? 'Quality Guarantee' ),
            '[PROCESS 4 DESC]'              => wp_kses_post(        $params['process_4_desc']  ?? 'We stand behind every job with a satisfaction guarantee.' ),
            '[TESTIMONIAL 1 TEXT]'          => wp_kses_post(        $params['testimonial_1_text'] ?? 'Outstanding service from a truly professional team.' ),
            '[TESTIMONIAL 1 NAME]'          => sanitize_text_field( $params['testimonial_1_name'] ?? 'Satisfied Customer' ),
            '[TESTIMONIAL 1 TITLE]'         => sanitize_text_field( $params['testimonial_1_title'] ?? 'Homeowner' ),
            '[TESTIMONIAL 2 TEXT]'          => wp_kses_post(        $params['testimonial_2_text'] ?? 'Best contractor in the area. Fair pricing and great quality.' ),
            '[TESTIMONIAL 2 NAME]'          => sanitize_text_field( $params['testimonial_2_name'] ?? 'Happy Client' ),
            '[TESTIMONIAL 2 TITLE]'         => sanitize_text_field( $params['testimonial_2_title'] ?? 'Local Business Owner' ),
        ];

        // ---------------------------------------------------------------
        // 6. DEPLOY ALL PAGES
        // ---------------------------------------------------------------
        $pages = [
            'home'     => 'home.json',
            'about'    => 'about.json',
            'services' => 'services.json',
            'quote'    => 'quote.json',
            'contact'  => 'contact.json',
        ];

        $results = [];
        $errors  = [];

        foreach ( $pages as $slug => $file ) {
            $result = $this->deploy_page( $slug, $template_base . $file, $replacements, $images );
            if ( is_wp_error( $result ) ) {
                $errors[$slug] = $result->get_error_message();
            } else {
                $results[$slug] = $result;
            }
        }

        // ---------------------------------------------------------------
        // 7. DEPLOY HEADER + FOOTER
        // ---------------------------------------------------------------
        $library_templates = [
            'header' => [ 'file' => 'header.json', 'slug' => 'header' ],
            'footer' => [ 'file' => 'footer.json', 'slug' => 'footer' ],
        ];

        foreach ( $library_templates as $label => $cfg ) {
            $result = $this->deploy_library_template(
                $cfg['slug'],
                $template_base . $cfg['file'],
                $replacements,
                $images
            );
            if ( ! is_wp_error( $result ) ) {
                $results[$label] = 'updated';
            } else {
                $errors[$label] = $result->get_error_message();
            }
        }

        // ---------------------------------------------------------------
        // 8. FLUSH BREEZE PAGE CACHE + LOOPBACK WARM REQUEST
        // ---------------------------------------------------------------
        do_action( 'breeze_clear_all_cache' );
        if ( class_exists( 'Breeze_PurgeCache' ) ) {
            Breeze_PurgeCache::breeze_cache_flush();
        }

        // Capture site URL while still in switched-blog context before restoring
        $deployed_site_url = is_multisite() ? get_blog_option( $blog_id, 'siteurl' ) : get_option( 'siteurl' );

        if ( is_multisite() ) restore_current_blog();

        // Loopback warm request: forces Breeze to regenerate a fresh page after
        // cache flush, so the next visitor (and QA) sees correct Elementor CSS.
        // This works outside switch_to_blog() context, so Breeze scoping is correct.
        if ( ! empty( $deployed_site_url ) ) {
            wp_remote_get( trailingslashit( $deployed_site_url ), [
                'timeout'   => 15,
                'headers'   => [ 'Cache-Control' => 'no-cache, no-store, must-revalidate' ],
                'sslverify' => false,
                'blocking'  => false, // fire-and-forget; don't hold up the response
            ] );
        }

        $success = empty( $errors );

        return new WP_REST_Response([
            'success'   => $success,
            'blog_id'   => $blog_id,
            'template'  => $template,
            'company'   => $company,
            'updated'   => $results,
            'errors'    => $errors,
            'image_ids' => array_map( fn( $img ) => $img['id'], $images ),
        ], $success ? 200 : 207 );
    }

    // ===================================================================
    // PRIVATE METHODS
    // ===================================================================

    private function deploy_page( string $slug, string $json_url, array $replacements, array $images ) {
        $body = $this->fetch_template( $json_url );
        if ( is_wp_error( $body ) ) return $body;

        $elementor_data = $this->build_elementor_data( $body, $json_url, $replacements, $images );
        if ( is_wp_error( $elementor_data ) ) return $elementor_data;

        $page = get_page_by_path( $slug, OBJECT, 'page' );
        if ( ! $page ) {
            $posts = get_posts([
                'name'           => $slug,
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
            ]);
            $page = $posts[0] ?? null;
        }
        if ( ! $page ) return new WP_Error( 'not_found', "Page '{$slug}' not found on this blog." );

        $post_id = $page->ID;
        update_post_meta( $post_id, '_elementor_data', wp_slash( $elementor_data ) );
        update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
        $this->clear_elementor_cache( $post_id );

        return get_permalink( $post_id );
    }

    private function deploy_library_template( string $template_slug, string $json_url, array $replacements, array $images ) {
        $body = $this->fetch_template( $json_url );
        if ( is_wp_error( $body ) ) return $body;

        $elementor_data = $this->build_elementor_data( $body, $json_url, $replacements, $images );
        if ( is_wp_error( $elementor_data ) ) return $elementor_data;

        // HFE uses elementor-hf post type with ehf_template_type meta
        $hfe_type = ( $template_slug === 'header' ) ? 'type_header' : 'type_footer';
        $templates = get_posts([
            'post_type'      => 'elementor-hf',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'meta_key'       => 'ehf_template_type',
            'meta_value'     => $hfe_type,
        ]);

        // Fallback: create the template if it doesn't exist
        if ( empty( $templates ) ) {
            $new_id = wp_insert_post([
                'post_title'  => ucfirst( $template_slug ),
                'post_status' => 'publish',
                'post_type'   => 'elementor-hf',
            ]);
            if ( ! $new_id || is_wp_error( $new_id ) ) {
                return new WP_Error( 'create_failed', "Could not create HFE {$template_slug} template." );
            }
            update_post_meta( $new_id, 'ehf_template_type', $hfe_type );
            update_post_meta( $new_id, '_elementor_edit_mode', 'builder' );
            update_post_meta( $new_id, 'ehf_target_include_locations', [ 'rule' => [ 'basic-global' ], 'specific' => [] ] );
            $post_id = $new_id;
        } else {
            $post_id = $templates[0]->ID;
        }

        // Also update the elementor_library post (for plugin compatibility)
        $lib_templates = get_posts([
            'name'           => $template_slug,
            'post_type'      => 'elementor_library',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
        ]);
        if ( ! empty( $lib_templates ) ) {
            update_post_meta( $lib_templates[0]->ID, '_elementor_data', wp_slash( $elementor_data ) );
            $this->clear_elementor_cache( $lib_templates[0]->ID );
        }

        update_post_meta( $post_id, '_elementor_data', wp_slash( $elementor_data ) );
        update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
        $this->clear_elementor_cache( $post_id );

        // Ensure page template allows HFE to render (not canvas)
        $this->set_page_template_for_hfe();

        return true;
    }

    private function set_page_template_for_hfe() {
        // Switch all pages from elementor_canvas to elementor_header_footer
        // so HFE header/footer renders on every page.
        $pages = get_posts([
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_key'       => '_wp_page_template',
            'meta_value'     => 'elementor_canvas',
        ]);
        foreach ( $pages as $page ) {
            update_post_meta( $page->ID, '_wp_page_template', 'elementor_header_footer' );
        }
        // Also ensure theme is Hello Elementor (HFE-compatible)
        $active_template = get_option( 'template' );
        if ( $active_template !== 'hello-elementor' ) {
            switch_theme( 'hello-elementor' );
        }
    }

    private function fetch_template( string $url ) {
        $response = wp_remote_get( $url, [ 'timeout' => 15 ] );
        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'fetch_failed', "Could not fetch template: {$url}" );
        }
        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 200 ) {
            return new WP_Error( 'fetch_failed', "Template returned HTTP {$code}: {$url}" );
        }
        $body = wp_remote_retrieve_body( $response );
        if ( empty( $body ) ) {
            return new WP_Error( 'empty_template', "Empty template body: {$url}" );
        }
        return $body;
    }

    private function build_elementor_data( string $body, string $source_url, array $replacements, array $images ) {
        $template_data = json_decode( $body, true );
        if ( ! isset( $template_data['content'] ) ) {
            return new WP_Error( 'invalid_template', "Template has no 'content' key: {$source_url}" );
        }

        $elements_json = wp_json_encode( $template_data['content'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        $elements_json = $this->inject_tokens( $elements_json, $replacements );

        $elements = json_decode( $elements_json, true );
        $elements = $this->apply_image_overrides( $elements, $images );

        return wp_json_encode( $elements, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    }

    private function inject_tokens( string $json, array $replacements ): string {
        return str_replace(
            array_keys( $replacements ),
            array_map( 'strval', array_values( $replacements ) ),
            $json
        );
    }

    /**
     * Recursively walk Elementor elements.
     *
     * Any element with "_wp_img": "slot" in settings gets its image injected.
     *
     * - On IMAGE WIDGETS (elType: widget, widgetType: image):
     *   sets settings.image = { url, id }
     *
     * - On CONTAINERS / SECTIONS (everything else):
     *   sets settings.background_image = { url, id }
     *   sets settings.background_background = 'classic'
     *
     * Template-agnostic. No hardcoded element IDs.
     */
    private function apply_image_overrides( array $elements, array $images ): array {
        foreach ( $elements as &$element ) {
            if ( ! is_array( $element ) ) continue;

            if ( isset( $element['settings']['_wp_img'] ) ) {
                $slot = $element['settings']['_wp_img'];

                if ( isset( $images[$slot] ) && ! empty( $images[$slot]['url'] ) ) {
                    $img = $images[$slot];

                    $is_image_widget = ( ( $element['elType'] ?? '' ) === 'widget' &&
                                         ( $element['widgetType'] ?? '' ) === 'image' );

                    if ( $is_image_widget ) {
                        // Logo, team photos, etc.
                        $element['settings']['image'] = $img;
                    } else {
                        // Hero, about, service background containers
                        $element['settings']['background_image']      = $img;
                        $element['settings']['background_background'] = 'classic';
                    }
                }
            }

            if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
                $element['elements'] = $this->apply_image_overrides( $element['elements'], $images );
            }
        }
        return $elements;
    }

    private function sideload_image( string $url ): int {
        if ( empty( $url ) ) return 0;

        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $existing = get_posts([
            'post_type'      => 'attachment',
            'meta_key'       => '_source_url',
            'meta_value'     => $url,
            'posts_per_page' => 1,
        ]);
        if ( ! empty( $existing ) ) return $existing[0]->ID;

        $id = media_sideload_image( $url, 0, '', 'id' );
        if ( is_wp_error( $id ) ) return 0;

        update_post_meta( $id, '_source_url', $url );
        return intval( $id );
    }

    private function set_elementor_kit_accent_color( string $color ) {
        $kit_id = (int) get_option( 'elementor_active_kit' );
        if ( ! $kit_id ) return;

        $settings = get_post_meta( $kit_id, '_elementor_page_settings', true );
        if ( ! is_array( $settings ) ) $settings = [];

        if ( empty( $settings['system_colors'] ) ) {
            $settings['system_colors'] = [
                [ '_id' => 'primary',   'title' => 'Primary',   'color' => '#6EC1E4' ],
                [ '_id' => 'secondary', 'title' => 'Secondary', 'color' => '#54595F' ],
                [ '_id' => 'text',      'title' => 'Text',      'color' => '#7A7A7A' ],
                [ '_id' => 'accent',    'title' => 'Accent',    'color' => $color ],
            ];
        } else {
            foreach ( $settings['system_colors'] as &$c ) {
                if ( ( $c['_id'] ?? '' ) === 'accent' ) {
                    $c['color'] = $color;
                }
            }
            unset( $c );
        }

        update_post_meta( $kit_id, '_elementor_page_settings', $settings );

        // Regenerate kit CSS only — do NOT call files_manager->clear_cache() here;
        // that wipes all CSS files and is called once centrally in handle_deploy().
        delete_post_meta( $kit_id, '_elementor_css' );
        if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
            \Elementor\Core\Files\CSS\Post::create( $kit_id )->update();
        }
    }

    private function clear_elementor_cache( int $post_id ) {
        delete_post_meta( $post_id, '_elementor_css' );
        clean_post_cache( $post_id );
        wp_cache_delete( $post_id, 'posts' );
        wp_cache_delete( $post_id, 'post_meta' );

        // Regenerate this post's CSS only.
        // Do NOT call files_manager->clear_cache() here — it nukes ALL Elementor
        // CSS files and is invoked once centrally in handle_deploy() before the loop.
        if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
            \Elementor\Core\Files\CSS\Post::create( $post_id )->update();
        }
    }

}

new WebPrinter_Engine();
