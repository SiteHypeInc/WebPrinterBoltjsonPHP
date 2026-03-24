<?php
/**
 * Plugin Name: WebPrinter Engine
 * Description: Template-agnostic REST endpoint to deploy contractor demo sites from n8n.
 * Version:     4.0
 * Author:      Team Platypus
 *
 * REQUIRES in wp-config.php:
 *   define( 'WP_TEMPLATE_BASE', 'https://raw.githubusercontent.com/SiteHypeInc/WebPrinterBoltjsonPHP/main' );
 *
 * n8n payload must include:
 *   "template": "bold-v2"   (matches folder name in repo)
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
        //    Repo folder: {WP_TEMPLATE_BASE}/{template}/home.json etc.
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
        // 4. SIDELOAD ALL IMAGES INTO MEDIA LIBRARY
        //    Builds $images map: slot => [ 'url' => local_url, 'id' => attachment_id ]
        //    Templates reference slots via "_wp_img": "hero|about|service|logo"
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

        $replacements = [
            '[COMPANY NAME]'          => $company,
            '[TAGLINE]'               => sanitize_text_field( $params['tagline']        ?? "Professional {$trade} Services in {$city_state}" ),
            '[HERO HEADLINE]'         => sanitize_text_field( $params['hero_headline']  ?? $company ),
            '[HERO SUB]'              => sanitize_text_field( $params['hero_sub']       ?? "Serving {$city_state}" ),
            '[TRADE]'                 => strtoupper( $trade ),
            '[CITY, STATE]'           => $city_state,
            '[CITY]'                  => sanitize_text_field( $params['city'] ),
            '[STATE]'                 => sanitize_text_field( $params['state'] ),
            '[PHONE]'                 => sanitize_text_field( $params['phone']          ?? '' ),
            '[EMAIL]'                 => sanitize_email(      $params['email']          ?? '' ),
            '[ADDRESS]'               => sanitize_text_field( $params['address']        ?? '' ),
            '[ABOUT]'                 => wp_kses_post(        $params['about']          ?? '' ),
            '[SERVICE 1 NAME]'        => sanitize_text_field( $params['service_1_name'] ?? '' ),
            '[SERVICE 1 description]' => wp_kses_post(        $params['service_1_desc'] ?? '' ),
            '[SERVICE 2 NAME]'        => sanitize_text_field( $params['service_2_name'] ?? '' ),
            '[SERVICE 2 description]' => wp_kses_post(        $params['service_2_desc'] ?? '' ),
            '[SERVICE 3 NAME]'        => sanitize_text_field( $params['service_3_name'] ?? '' ),
            '[SERVICE 3 description]' => wp_kses_post(        $params['service_3_desc'] ?? '' ),
            '[SERVICE 4 NAME]'        => sanitize_text_field( $params['service_4_name'] ?? '' ),
            '[SERVICE 4 description]' => wp_kses_post(        $params['service_4_desc'] ?? '' ),
        ];

        // ---------------------------------------------------------------
        // 6. DEPLOY ALL PAGES
        //    Plugin fetches fresh JSON from GitHub on every deploy.
        //    File names are standardized — all templates use same names.
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
        // 7. DEPLOY HEADER + FOOTER (Elementor Library templates)
        //    WP slug must match — bold-header / bold-footer on subsite
        // ---------------------------------------------------------------
        $library_templates = [
            'header' => [ 'file' => 'header.json', 'slug' => 'bold-header' ],
            'footer' => [ 'file' => 'footer.json', 'slug' => 'bold-footer' ],
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

        if ( is_multisite() ) restore_current_blog();

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

    /**
     * Fetch template JSON from GitHub, inject tokens and images, write to WP page.
     * Bypasses Elementor's import sanitization entirely — no esc_url() stripping.
     *
     * @return string|WP_Error  Permalink on success.
     */
    private function deploy_page( string $slug, string $json_url, array $replacements, array $images ) {
        $body = $this->fetch_template( $json_url );
        if ( is_wp_error( $body ) ) return $body;

        $elementor_data = $this->build_elementor_data( $body, $json_url, $replacements, $images );
        if ( is_wp_error( $elementor_data ) ) return $elementor_data;

        // Find page by slug
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

    /**
     * Fetch template JSON from GitHub, inject tokens and images, write to Elementor Library template.
     *
     * @return bool|WP_Error
     */
    private function deploy_library_template( string $template_slug, string $json_url, array $replacements, array $images ) {
        $body = $this->fetch_template( $json_url );
        if ( is_wp_error( $body ) ) return $body;

        $elementor_data = $this->build_elementor_data( $body, $json_url, $replacements, $images );
        if ( is_wp_error( $elementor_data ) ) return $elementor_data;

        $templates = get_posts([
            'name'           => $template_slug,
            'post_type'      => 'elementor_library',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
        ]);
        if ( empty( $templates ) ) return new WP_Error( 'not_found', "Library template '{$template_slug}' not found." );

        $post_id = $templates[0]->ID;
        update_post_meta( $post_id, '_elementor_data', wp_slash( $elementor_data ) );
        update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
        $this->clear_elementor_cache( $post_id );

        return true;
    }

    /**
     * Fetch raw JSON from a URL. Returns body string or WP_Error.
     */
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

    /**
     * Parse template JSON, inject text tokens, apply image overrides.
     * Returns final Elementor JSON string ready for _elementor_data.
     */
    private function build_elementor_data( string $body, string $source_url, array $replacements, array $images ) {
        $template_data = json_decode( $body, true );
        if ( ! isset( $template_data['content'] ) ) {
            return new WP_Error( 'invalid_template', "Template has no 'content' key: {$source_url}" );
        }

        // Text token replacement on raw JSON string
        $elements_json = wp_json_encode( $template_data['content'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        $elements_json = $this->inject_tokens( $elements_json, $replacements );

        // Image override via _wp_img markers (template-agnostic)
        $elements = json_decode( $elements_json, true );
        $elements = $this->apply_image_overrides( $elements, $images );

        return wp_json_encode( $elements, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    }

    /**
     * str_replace token injection on raw JSON string.
     * Works for all text tokens. Image URLs handled separately via _wp_img.
     */
    private function inject_tokens( string $json, array $replacements ): string {
        return str_replace(
            array_keys( $replacements ),
            array_map( 'strval', array_values( $replacements ) ),
            $json
        );
    }

    /**
     * Recursively walk Elementor elements array.
     * Any element with "_wp_img": "hero|about|service|logo" in its settings
     * gets its background_image replaced with the sideloaded image data.
     *
     * This is TEMPLATE-AGNOSTIC — no hardcoded element IDs.
     * Just add "_wp_img": "hero" to any container in any template to drive images.
     */
    private function apply_image_overrides( array $elements, array $images ): array {
        foreach ( $elements as &$element ) {
            if ( ! is_array( $element ) ) continue;

            if ( isset( $element['settings']['_wp_img'] ) ) {
                $slot = $element['settings']['_wp_img'];
                if ( isset( $images[$slot] ) && ! empty( $images[$slot]['url'] ) ) {
                    $element['settings']['background_image']      = $images[$slot];
                    $element['settings']['background_background'] = 'classic';
                }
            }

            if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
                $element['elements'] = $this->apply_image_overrides( $element['elements'], $images );
            }
        }
        return $elements;
    }

    /**
     * Sideload external image into WP media library.
     * Returns attachment ID or 0 on failure.
     * Skips re-download if _source_url meta already exists.
     */
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

    /**
     * Clear Elementor postmeta cache AND regenerate filesystem CSS file.
     * Both layers must be cleared for changes to appear immediately.
     */
    private function clear_elementor_cache( int $post_id ) {
        delete_post_meta( $post_id, '_elementor_css' );
        delete_post_meta( $post_id, '_elementor_page_settings' );
        clean_post_cache( $post_id );
        wp_cache_delete( $post_id, 'posts' );
        wp_cache_delete( $post_id, 'post_meta' );

        // Regenerate Elementor filesystem CSS from updated _elementor_data
        if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
            \Elementor\Core\Files\CSS\Post::create( $post_id )->update();
        }

        // Nuke global Elementor file cache
        if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
    }

}

new WebPrinter_Engine();
