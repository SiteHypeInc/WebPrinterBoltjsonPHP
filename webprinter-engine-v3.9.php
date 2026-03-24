<?php
/**
 * Plugin Name: WebPrinter Engine
 * Description: REST API endpoint to deploy Bold template sites from n8n.
 * Version:     3.9
 * Author:      Team Platypus
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// GitHub raw URL for template JSONs — update path when repo is populated
define( 'WP_TEMPLATE_BASE', 'https://raw.githubusercontent.com/SiteHypeInc/WebPrinterBoltjsonPHP/main/' );

class WebPrinter_Engine {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'webprinter/v1', '/deploy', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'handle_deploy' ],
            'permission_callback' => [ $this, 'check_auth' ],
        ] );

        // Diagnostic — remove before production
        register_rest_route( 'webprinter/v1', '/diagnostic/(?P<blog_id>\d+)/(?P<slug>[a-z0-9-]+)', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'handle_diagnostic' ],
            'permission_callback' => '__return_true',
        ] );
    }

    public function check_auth( WP_REST_Request $request ) {
        $key = defined( 'WP_WEBPRINTER_KEY' ) ? WP_WEBPRINTER_KEY : '';
        if ( empty( $key ) ) return true;
        return $request->get_header( 'X-Webprinter-Key' ) === $key;
    }

    public function handle_diagnostic( WP_REST_Request $request ) {
        $blog_id = intval( $request['blog_id'] );
        $slug    = sanitize_text_field( $request['slug'] );

        if ( is_multisite() ) switch_to_blog( $blog_id );

        $page = get_page_by_path( $slug, OBJECT, 'page' );
        if ( ! $page ) {
            if ( is_multisite() ) restore_current_blog();
            return new WP_REST_Response( [ 'error' => "Page '{$slug}' not found" ], 404 );
        }

        $raw  = get_post_meta( $page->ID, '_elementor_data', true );
        $data = json_decode( $raw, true );

        $bg_images = [];
        foreach ( (array) $data as $section ) {
            $id     = $section['id'] ?? 'unknown';
            $url    = $section['settings']['background_image']['url'] ?? null;
            $img_id = $section['settings']['background_image']['id']  ?? null;
            if ( $url !== null ) {
                $bg_images[ $id ] = [ 'url' => $url, 'id' => $img_id ];
            }
        }

        if ( is_multisite() ) restore_current_blog();

        return new WP_REST_Response( [
            'post_id'     => $page->ID,
            'slug'        => $slug,
            'bg_images'   => $bg_images,
            'data_length' => strlen( $raw ),
        ], 200 );
    }

    public function handle_deploy( WP_REST_Request $request ) {
        $params = $request->get_json_params();
        if ( empty( $params ) ) {
            $params = $request->get_params();
        }

        // ---------------------------------------------------------------
        // 1. VALIDATE
        // ---------------------------------------------------------------
        $required = [ 'blog_id', 'company_name', 'trade', 'city', 'state' ];
        foreach ( $required as $field ) {
            if ( empty( $params[ $field ] ) ) {
                return new WP_REST_Response( [ 'success' => false, 'error' => "Missing: {$field}" ], 400 );
            }
        }

        $blog_id = intval( $params['blog_id'] );

        // ---------------------------------------------------------------
        // 2. SWITCH TO SUBSITE
        // ---------------------------------------------------------------
        if ( is_multisite() ) {
            if ( ! get_blog_details( $blog_id ) ) {
                return new WP_REST_Response( [ 'success' => false, 'error' => "Blog {$blog_id} not found" ], 404 );
            }
            switch_to_blog( $blog_id );
        }

        // ---------------------------------------------------------------
        // 3. SIDELOAD IMAGES — get local URLs and IDs
        // ---------------------------------------------------------------
        $hero_img_id    = $this->sideload_image( $params['hero_image_url']    ?? '' );
        $about_img_id   = $this->sideload_image( $params['about_image_url']   ?? '' );
        $service_img_id = $this->sideload_image( $params['service_image_url'] ?? '' );

        $hero_local_url    = $hero_img_id    ? wp_get_attachment_url( $hero_img_id )    : '';
        $about_local_url   = $about_img_id   ? wp_get_attachment_url( $about_img_id )   : '';
        $service_local_url = $service_img_id ? wp_get_attachment_url( $service_img_id ) : '';

        // ---------------------------------------------------------------
        // 4. BUILD REPLACEMENTS
        // ---------------------------------------------------------------
        $city_state = sanitize_text_field( $params['city'] ) . ', ' . sanitize_text_field( $params['state'] );
        $company    = sanitize_text_field( $params['company_name'] );
        $trade      = sanitize_text_field( $params['trade'] );

        $replacements = [
            '[COMPANY NAME]'          => $company,
            '[TAGLINE]'               => sanitize_text_field( $params['tagline']       ?? "Professional {$trade} Services in {$city_state}" ),
            '[HERO HEADLINE]'         => sanitize_text_field( $params['hero_headline'] ?? $company ),
            '[HERO SUB]'              => sanitize_text_field( $params['hero_sub']      ?? "Serving {$city_state}" ),
            '[TRADE]'                 => strtoupper( $trade ),
            '[CITY, STATE]'           => $city_state,
            '[CITY]'                  => sanitize_text_field( $params['city'] ),
            '[STATE]'                 => sanitize_text_field( $params['state'] ),
            '[PHONE]'                 => sanitize_text_field( $params['phone']         ?? '' ),
            '[EMAIL]'                 => sanitize_email(      $params['email']         ?? '' ),
            '[ADDRESS]'               => sanitize_text_field( $params['address']       ?? '' ),
            '[ABOUT]'                 => wp_kses_post(        $params['about']         ?? '' ),
            '[LOGO IMAGE URL]'        => esc_url(             $params['logo_url']      ?? '' ),
            '[SERVICE 1 NAME]'        => sanitize_text_field( $params['service_1_name'] ?? '' ),
            '[SERVICE 1 description]' => wp_kses_post(        $params['service_1_desc'] ?? '' ),
            '[SERVICE 2 NAME]'        => sanitize_text_field( $params['service_2_name'] ?? '' ),
            '[SERVICE 2 description]' => wp_kses_post(        $params['service_2_desc'] ?? '' ),
            '[SERVICE 3 NAME]'        => sanitize_text_field( $params['service_3_name'] ?? '' ),
            '[SERVICE 3 description]' => wp_kses_post(        $params['service_3_desc'] ?? '' ),
            '[SERVICE 4 NAME]'        => sanitize_text_field( $params['service_4_name'] ?? '' ),
            '[SERVICE 4 description]' => wp_kses_post(        $params['service_4_desc'] ?? '' ),
        ];

        // Background image overrides keyed by element ID as defined in OUR template JSON.
        // Since we write the JSON directly (no Elementor import), IDs are never reassigned.
        $background_overrides = [
            'hero-section' => [ 'url' => $hero_local_url, 'id' => $hero_img_id ],
        ];

        // ---------------------------------------------------------------
        // 5. DEPLOY EACH PAGE FROM FRESH TEMPLATE JSON
        // ---------------------------------------------------------------
        $page_templates = [
            'home'     => 'elementor-bold-home-v3.json',
            'about'    => 'elementor-bold-about.json',
            'services' => 'elementor-bold-services.json',
            'quote'    => 'elementor-bold-quote.json',
            'contact'  => 'elementor-bold-contact.json',
        ];

        $results = [];
        $errors  = [];

        foreach ( $page_templates as $slug => $template_file ) {
            $result = $this->deploy_page( $slug, $template_file, $replacements, $background_overrides );
            if ( is_wp_error( $result ) ) {
                $errors[ $slug ] = $result->get_error_message();
            } else {
                $results[ $slug ] = $result;
            }
        }

        if ( is_multisite() ) restore_current_blog();

        $success = empty( $errors );

        return new WP_REST_Response( [
            'success'   => $success,
            'blog_id'   => $blog_id,
            'company'   => $company,
            'updated'   => $results,
            'errors'    => $errors,
            'image_ids' => [
                'hero'    => $hero_img_id,
                'about'   => $about_img_id,
                'service' => $service_img_id,
            ],
        ], $success ? 200 : 207 );
    }

    /**
     * Fetch template JSON from GitHub, apply replacements, write directly to page.
     * No Elementor import — element IDs stay exactly as defined in the template.
     */
    private function deploy_page( string $slug, string $template_file, array $replacements, array $background_overrides = [] ) {
        // Find the target page
        $page = get_page_by_path( $slug, OBJECT, 'page' );
        if ( ! $page ) {
            $posts = get_posts( [ 'name' => $slug, 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1 ] );
            $page  = $posts[0] ?? null;
        }
        if ( ! $page ) {
            return new WP_Error( 'not_found', "Page '{$slug}' not found." );
        }

        // Fetch template JSON from GitHub
        $template_url = WP_TEMPLATE_BASE . $template_file;
        $response     = wp_remote_get( $template_url, [ 'timeout' => 15, 'sslverify' => true ] );

        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'template_fetch_failed', "Could not fetch template '{$template_file}': " . $response->get_error_message() );
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        if ( $status_code !== 200 ) {
            return new WP_Error( 'template_not_found', "Template '{$template_file}' returned HTTP {$status_code}." );
        }

        $template_json = wp_remote_retrieve_body( $response );

        // Decode to verify it's valid JSON
        $template_data = json_decode( $template_json, true );
        if ( ! is_array( $template_data ) ) {
            return new WP_Error( 'template_invalid', "Template '{$template_file}' is not valid JSON." );
        }

        // Elementor stores the 'content' array as _elementor_data, not the full wrapper
        $elements = $template_data['content'] ?? $template_data;

        // Step 1: Token replacement on raw JSON string
        $elements_json = wp_json_encode( $elements, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        $search        = array_keys( $replacements );
        $replace       = array_map( 'strval', array_values( $replacements ) );
        $elements_json = str_replace( $search, $replace, $elements_json );

        // Step 2: Decode, apply background image overrides by element ID, re-encode
        $elements_array = json_decode( $elements_json, true );
        $elements_array = $this->apply_background_overrides( $elements_array, $background_overrides );
        $elements_json  = wp_json_encode( $elements_array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

        // Step 3: Write directly to _elementor_data
        $post_id = $page->ID;
        update_post_meta( $post_id, '_elementor_data', wp_slash( $elements_json ) );
        update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
        delete_post_meta( $post_id, '_elementor_css' );

        // Step 4: Clear Elementor file cache
        if ( class_exists( '\Elementor\Plugin' ) ) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }

        // Step 5: Loopback request to warm CSS — forces Elementor to regenerate CSS file
        $permalink = get_permalink( $post_id );
        wp_remote_get( $permalink, [ 'timeout' => 15, 'sslverify' => false, 'blocking' => true ] );

        return $permalink;
    }

    /**
     * Recursively walk elements array and apply background_image by element ID.
     * Since we own the JSON (no Elementor import), IDs are exactly as defined.
     */
    private function apply_background_overrides( array $elements, array $overrides ): array {
        foreach ( $elements as &$element ) {
            if ( ! is_array( $element ) ) continue;

            $elem_id = $element['id'] ?? '';

            if ( $elem_id && isset( $overrides[ $elem_id ] ) ) {
                $o = $overrides[ $elem_id ];
                if ( ! empty( $o['url'] ) && ! empty( $o['id'] ) ) {
                    $element['settings']['background_image']      = [ 'url' => $o['url'], 'id' => intval( $o['id'] ) ];
                    $element['settings']['background_background'] = 'classic';
                }
            }

            if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
                $element['elements'] = $this->apply_background_overrides( $element['elements'], $overrides );
            }
        }
        return $elements;
    }

    /**
     * Sideload external image into WP media library. Deduplicates by _source_url.
     */
    private function sideload_image( string $url ): int {
        if ( empty( $url ) ) return 0;

        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $existing = get_posts( [ 'post_type' => 'attachment', 'meta_key' => '_source_url', 'meta_value' => $url, 'posts_per_page' => 1 ] );
        if ( ! empty( $existing ) ) return $existing[0]->ID;

        $attachment_id = media_sideload_image( $url, 0, '', 'id' );
        if ( is_wp_error( $attachment_id ) ) return 0;

        update_post_meta( $attachment_id, '_source_url', $url );
        return intval( $attachment_id );
    }

} // end class WebPrinter_Engine

new WebPrinter_Engine();
