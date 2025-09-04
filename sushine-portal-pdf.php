<?php
/**
 * Plugin Name: SunshinePortal PDF Tool
 * Plugin URI: education.ufl.edu
 * Description: A comprehensive PDF resource management tool with filtering and download tracking
 * Version: 2.0.1
 * Author: Eve
 * License: GPL v2 or later
 * Text Domain: sunshineportal-pdf
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SUNSHINEPORTAL_PDF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SUNSHINEPORTAL_PDF_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SUNSHINEPORTAL_PDF_VERSION', '1.0.0');

class SunshinePortal_PDF_Manager {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('rest_api_init', array($this, 'register_api_routes'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_data'));
        
        // Admin columns
        add_filter('manage_pdf_resource_posts_columns', array($this, 'add_admin_columns'));
        add_action('manage_pdf_resource_posts_custom_column', array($this, 'display_admin_columns'), 10, 2);
        
        // Shortcode
        add_shortcode('sunshineportal_pdf_manager', array($this, 'pdf_manager_shortcode'));
        
        // Allow uploads for anonymous users by setting proper upload permissions
        add_filter('upload_mimes', array($this, 'allow_pdf_uploads'));
        
        // Increase upload limits for PDF files
        add_filter('wp_max_upload_size', array($this, 'increase_upload_limit'));
    }
    
    public function init() {
        $this->register_post_type();
        $this->register_taxonomies();
    }
    
    public function allow_pdf_uploads($mimes) {
        $mimes['pdf'] = 'application/pdf';
        return $mimes;
    }
    
    public function increase_upload_limit($size) {
        // Set 10MB limit for uploads (only if current limit is lower)
        $new_size = 10 * 1024 * 1024; // 10MB in bytes
        return max($size, $new_size);
    }
    
    public function enqueue_frontend_assets() {
        // Only enqueue on pages with the shortcode or PDF resource pages
        if (is_singular('pdf_resource') || $this->has_shortcode()) {
            wp_enqueue_style(
                'sunshineportal-pdf-css',
                SUNSHINEPORTAL_PDF_PLUGIN_URL . 'assets/css/pdf-manager.css',
                array('dashicons'), // FIXED: Add dashicons dependency
                SUNSHINEPORTAL_PDF_VERSION
            );
            
            wp_enqueue_script(
                'sunshineportal-pdf-js',
                SUNSHINEPORTAL_PDF_PLUGIN_URL . 'assets/js/pdf-manager.js',
                array('jquery'),
                SUNSHINEPORTAL_PDF_VERSION,
                true
            );
            
            // FIXED: Enqueue media library for frontend when shortcode is present AND user is logged in
            if ($this->has_shortcode_with_admin() && is_user_logged_in()) {
                wp_enqueue_media();
            }
            
            wp_localize_script('sunshineportal-pdf-js', 'pdfManager', array(
                'apiUrl' => rest_url('sunshineportal-pdf/v1/'),
                'nonce' => wp_create_nonce('wp_rest'),
                'showAdmin' => current_user_can('upload_files'),
                'pluginUrl' => SUNSHINEPORTAL_PDF_PLUGIN_URL,
                'maxFileSize' => '10MB',
                'allowedTypes' => 'PDF files only',
                'isLoggedIn' => is_user_logged_in(),
            ));
        }
    }
    
    public function enqueue_admin_assets($hook) {
        global $post_type;
        if (($hook == 'post-new.php' || $hook == 'post.php') && $post_type == 'pdf_resource') {
            wp_enqueue_media();
            wp_enqueue_script(
                'sunshineportal-pdf-admin',
                SUNSHINEPORTAL_PDF_PLUGIN_URL . 'assets/js/pdf-admin.js',
                array('jquery'),
                SUNSHINEPORTAL_PDF_VERSION,
                true
            );
        }
    }
    
    private function has_shortcode() {
        global $post;
        return is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'sunshineportal_pdf_manager');
    }
    
    private function has_shortcode_with_admin() {
        global $post;
        if (!is_a($post, 'WP_Post')) {
            return false;
        }
        
        // Check if shortcode exists and has show_admin="true"
        $pattern = '/\[sunshineportal_pdf_manager[^\]]*show_admin=["\']true["\']/';
        return preg_match($pattern, $post->post_content);
    }
    
    public function register_post_type() {
        $args = array(
            'public' => true,
            'label' => __('PDF Resources', 'sunshineportal-pdf'),
            'labels' => array(
                'name' => __('PDF Resources', 'sunshineportal-pdf'),
                'singular_name' => __('PDF Resource', 'sunshineportal-pdf'),
                'add_new' => __('Add New PDF', 'sunshineportal-pdf'),
                'add_new_item' => __('Add New PDF Resource', 'sunshineportal-pdf'),
                'edit_item' => __('Edit PDF Resource', 'sunshineportal-pdf'),
                'all_items' => __('All PDF Resources', 'sunshineportal-pdf')
            ),
            'supports' => array('title', 'editor', 'custom-fields'),
            'menu_icon' => 'dashicons-media-document',
            'has_archive' => true,
            'rewrite' => array('slug' => 'pdf-resources'),
            'show_in_rest' => true,
            'capability_type' => 'post',
            'capabilities' => array(
                'create_posts' => 'upload_files',
            ),
            'map_meta_cap' => true,
        );
        register_post_type('pdf_resource', $args);
    }
    
    public function register_taxonomies() {
        // PDF Categories- ELC
        register_taxonomy('pdf_category', 'pdf_resource', array(
            'label' => __('ELC', 'sunshineportal-pdf'),
            'rewrite' => array('slug' => 'pdf-category'),
            'hierarchical' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
        ));
        
        // PDF Types- County
        register_taxonomy('pdf_type', 'pdf_resource', array(
            'label' => __('County', 'sunshineportal-pdf'),
            'rewrite' => array('slug' => 'pdf-type'),
            'hierarchical' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
        ));
        
        // Departments- Year
        register_taxonomy('pdf_department', 'pdf_resource', array(
            'label' => __('Years', 'sunshineportal-pdf'),
            'rewrite' => array('slug' => 'department'),
            'hierarchical' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
        ));
    }
    
    public function add_meta_boxes() {
        add_meta_box(
            'pdf_resource_details',
            __('PDF Resource Details', 'sunshineportal-pdf'),
            array($this, 'meta_box_callback'),
            'pdf_resource',
            'normal',
            'high'
        );
    }
    
    public function meta_box_callback($post) {
        wp_nonce_field('pdf_resource_meta_box', 'pdf_resource_meta_box_nonce');
        
        $pdf_file_id = get_post_meta($post->ID, '_pdf_file_id', true);
        $download_count = get_post_meta($post->ID, '_download_count', true) ?: 0;
        $file_size = get_post_meta($post->ID, '_file_size', true);
        
        include SUNSHINEPORTAL_PDF_PLUGIN_PATH . 'templates/admin-meta-box.php';
    }
    
    public function save_meta_data($post_id) {
        if (!isset($_POST['pdf_resource_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['pdf_resource_meta_box_nonce'], 'pdf_resource_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save PDF file ID
        if (isset($_POST['pdf_file_id'])) {
            $pdf_file_id = sanitize_text_field($_POST['pdf_file_id']);
            update_post_meta($post_id, '_pdf_file_id', $pdf_file_id);
            
            // Get file info and save additional meta
            if ($pdf_file_id) {
                $file_path = get_attached_file($pdf_file_id);
                if ($file_path && file_exists($file_path)) {
                    $file_size = size_format(filesize($file_path));
                    update_post_meta($post_id, '_file_size', $file_size);
                    update_post_meta($post_id, '_upload_date', current_time('mysql'));
                }
            }
        }
        
        // Initialize download count if new post
        if (!get_post_meta($post_id, '_download_count', true)) {
            update_post_meta($post_id, '_download_count', 0);
        }
    }
    
    public function register_api_routes() {
        // Get filtered PDFs
        register_rest_route('sunshineportal-pdf/v1', '/resources', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_resources_api'),
            'permission_callback' => '__return_true',
            'args' => array(
                'search' => array('type' => 'string'),
                'category' => array('type' => 'array'),
                'type' => array('type' => 'array'),
                'department' => array('type' => 'array'),
                'orderby' => array('type' => 'string', 'default' => 'date'),
                'order' => array('type' => 'string', 'default' => 'DESC'),
            ),
        ));
        
        // Download PDF endpoint
        register_rest_route('sunshineportal-pdf/v1', '/download/(?P<id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'download_pdf_api'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ),
            ),
        ));
        
        // Create new PDF resource (allow anyone since frontend admin is now public)
        register_rest_route('sunshineportal-pdf/v1', '/resources', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_resource_api'),
            'permission_callback' => '__return_true', // FIXED: Allow anyone to upload
        ));
        
        // Get taxonomies
        register_rest_route('sunshineportal-pdf/v1', '/taxonomies', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_taxonomies_api'),
            'permission_callback' => '__return_true',
        ));
        
        // Upload PDF file (for anonymous users)
        register_rest_route('sunshineportal-pdf/v1', '/upload', array(
            'methods' => 'POST',
            'callback' => array($this, 'upload_pdf_api'),
            'permission_callback' => '__return_true', // Allow anonymous uploads
        ));
    }
    
    public function get_resources_api($request) {
        $params = $request->get_params();
        
        $args = array(
            'post_type' => 'pdf_resource',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_key' => '_pdf_file_id',
            'meta_compare' => 'EXISTS',
        );
        
        // Add search
        if (!empty($params['search'])) {
            $args['s'] = sanitize_text_field($params['search']);
        }
        
        // Add taxonomy filters
        $tax_query = array();
        
        foreach (['category', 'type', 'department'] as $tax) {
            if (!empty($params[$tax])) {
                $tax_query[] = array(
                    'taxonomy' => 'pdf_' . $tax,
                    'field' => 'slug',
                    'terms' => array_map('sanitize_text_field', $params[$tax]),
                );
            }
        }
        
        if (count($tax_query) > 1) {
            $tax_query['relation'] = 'AND';
        }
        
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        
        // Add sorting
        if (!empty($params['orderby'])) {
            if ($params['orderby'] === 'downloads') {
                $args['meta_key'] = '_download_count';
                $args['orderby'] = 'meta_value_num';
            } else {
                $args['orderby'] = sanitize_text_field($params['orderby']);
            }
        }
        
        if (!empty($params['order'])) {
            $args['order'] = strtoupper(sanitize_text_field($params['order']));
        }
        
        $query = new WP_Query($args);
        $resources = array();
        
        foreach ($query->posts as $post) {
            $pdf_file_id = get_post_meta($post->ID, '_pdf_file_id', true);
            $download_count = get_post_meta($post->ID, '_download_count', true) ?: 0;
            $file_size = get_post_meta($post->ID, '_file_size', true);
            
            $categories = wp_get_post_terms($post->ID, 'pdf_category', array('fields' => 'names'));
            $types = wp_get_post_terms($post->ID, 'pdf_type', array('fields' => 'names'));
            $departments = wp_get_post_terms($post->ID, 'pdf_department', array('fields' => 'names'));
            
            $resources[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'description' => $post->post_content,
                'url' => wp_get_attachment_url($pdf_file_id),
                'download_url' => rest_url('sunshineportal-pdf/v1/download/' . $post->ID),
                'categories' => $categories,
                'types' => $types,
                'departments' => $departments,
                'download_count' => intval($download_count),
                'file_size' => $file_size,
                'date' => $post->post_date,
            );
        }
        
        return rest_ensure_response($resources);
    }
    
    public function download_pdf_api($request) {
        $post_id = $request['id'];
        
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'pdf_resource') {
            return new WP_Error('invalid_post', 'PDF resource not found', array('status' => 404));
        }
        
        $pdf_file_id = get_post_meta($post_id, '_pdf_file_id', true);
        if (!$pdf_file_id) {
            return new WP_Error('no_file', 'No PDF file attached', array('status' => 400));
        }
        
        // Increment download count
        $current_count = get_post_meta($post_id, '_download_count', true) ?: 0;
        update_post_meta($post_id, '_download_count', $current_count + 1);
        
        // Log download (optional)
        do_action('sunshineportal_pdf_downloaded', $post_id, get_current_user_id());
        
        return rest_ensure_response(array(
            'success' => true,
            'download_url' => wp_get_attachment_url($pdf_file_id),
            'new_count' => $current_count + 1,
        ));
    }
    
    public function create_resource_api($request) {
        $params = $request->get_params();
        
        // Debug logging to see what's being received
        error_log('PDF Creation params: ' . print_r($params, true));
        
        $post_data = array(
            'post_title' => sanitize_text_field($params['title']),
            'post_content' => sanitize_textarea_field($params['description']),
            'post_type' => 'pdf_resource',
            'post_status' => 'publish',
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // FIXED: Convert slugs to term IDs before setting terms
        if (!empty($params['category']) && is_array($params['category'])) {
            $term_ids = array();
            foreach ($params['category'] as $slug) {
                $term = get_term_by('slug', sanitize_text_field($slug), 'pdf_category');
                if ($term) {
                    $term_ids[] = $term->term_id;
                }
            }
            if (!empty($term_ids)) {
                $result = wp_set_post_terms($post_id, $term_ids, 'pdf_category');
                error_log('Category assignment result: ' . print_r($result, true));
            }
        }
        
        if (!empty($params['type']) && is_array($params['type'])) {
            $term_ids = array();
            foreach ($params['type'] as $slug) {
                $term = get_term_by('slug', sanitize_text_field($slug), 'pdf_type');
                if ($term) {
                    $term_ids[] = $term->term_id;
                }
            }
            if (!empty($term_ids)) {
                $result = wp_set_post_terms($post_id, $term_ids, 'pdf_type');
                error_log('Type assignment result: ' . print_r($result, true));
            }
        }
        
        if (!empty($params['department']) && is_array($params['department'])) {
            $term_ids = array();
            foreach ($params['department'] as $slug) {
                $term = get_term_by('slug', sanitize_text_field($slug), 'pdf_department');
                if ($term) {
                    $term_ids[] = $term->term_id;
                }
            }
            if (!empty($term_ids)) {
                $result = wp_set_post_terms($post_id, $term_ids, 'pdf_department');
                error_log('Department assignment result: ' . print_r($result, true));
            }
        }
        
        // Handle file upload if provided
        if (!empty($params['pdf_file_id'])) {
            update_post_meta($post_id, '_pdf_file_id', intval($params['pdf_file_id']));
            
            // Get file info and save additional meta
            $file_path = get_attached_file(intval($params['pdf_file_id']));
            if ($file_path && file_exists($file_path)) {
                $file_size = size_format(filesize($file_path));
                update_post_meta($post_id, '_file_size', $file_size);
                update_post_meta($post_id, '_upload_date', current_time('mysql'));
            }
        }
        
        // Initialize download count
        update_post_meta($post_id, '_download_count', 0);
        
        // Get the assigned terms for confirmation
        $assigned_categories = wp_get_post_terms($post_id, 'pdf_category', array('fields' => 'slugs'));
        $assigned_types = wp_get_post_terms($post_id, 'pdf_type', array('fields' => 'slugs'));
        $assigned_departments = wp_get_post_terms($post_id, 'pdf_department', array('fields' => 'slugs'));
        
        return rest_ensure_response(array(
            'success' => true,
            'post_id' => $post_id,
            'message' => 'PDF resource created successfully',
            'debug' => array(
                'received_params' => $params,
                'assigned_categories' => $assigned_categories,
                'assigned_types' => $assigned_types,
                'assigned_departments' => $assigned_departments
            )
        ));
    }
    
    public function upload_pdf_api($request) {
        // Security check: Verify nonce
        if (!wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')) {
            return new WP_Error('invalid_nonce', 'Invalid security token', array('status' => 403));
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('no_file', 'No file uploaded or upload error', array('status' => 400));
        }
        
        $file = $_FILES['pdf_file'];
        
        // Validate file type
        $file_type = wp_check_filetype($file['name']);
        if ($file_type['type'] !== 'application/pdf') {
            return new WP_Error('invalid_type', 'Only PDF files are allowed', array('status' => 400));
        }
        
        // Additional MIME type validation
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if ($detected_type !== 'application/pdf') {
            return new WP_Error('invalid_mime', 'File is not a valid PDF', array('status' => 400));
        }
        
        // Validate file size (10MB limit)
        if ($file['size'] > 10 * 1024 * 1024) {
            return new WP_Error('file_too_large', 'File size must be less than 10MB', array('status' => 400));
        }
        
        // Sanitize filename
        $filename = sanitize_file_name($file['name']);
        
        // Include WordPress file handling functions
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        // Configure upload overrides
        $upload_overrides = array(
            'test_form' => false,
            'test_type' => true,
            'mimes' => array(
                'pdf' => 'application/pdf'
            ),
            'unique_filename_callback' => function($dir, $name, $ext) {
                // Add timestamp to filename to avoid conflicts
                $timestamp = time();
                return pathinfo($name, PATHINFO_FILENAME) . '_' . $timestamp . $ext;
            }
        );
        
        // Handle the upload
        $uploaded_file = wp_handle_upload($file, $upload_overrides);
        
        if (isset($uploaded_file['error'])) {
            return new WP_Error('upload_failed', $uploaded_file['error'], array('status' => 500));
        }
        
        // Add to media library
        $attachment = array(
            'guid' => $uploaded_file['url'],
            'post_mime_type' => $uploaded_file['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_content' => '',
            'post_status' => 'inherit',
            'post_author' => get_current_user_id() ?: 0, // Anonymous uploads have author 0
        );
        
        $attachment_id = wp_insert_attachment($attachment, $uploaded_file['file']);
        
        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }
        
        // Generate attachment metadata
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        
        $attach_data = wp_generate_attachment_metadata($attachment_id, $uploaded_file['file']);
        wp_update_attachment_metadata($attachment_id, $attach_data);
        
        return rest_ensure_response(array(
            'success' => true,
            'file_id' => $attachment_id,
            'filename' => basename($uploaded_file['file']),
            'file_size' => size_format(filesize($uploaded_file['file'])),
            'url' => $uploaded_file['url'],
        ));
    }
    
    public function get_taxonomies_api($request) {
        $taxonomies = array();
        
        foreach (['pdf_category', 'pdf_type', 'pdf_department'] as $taxonomy) {
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ));
            
            $key = str_replace('pdf_', '', $taxonomy);
            $taxonomies[$key] = array_map(function($term) {
                return array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                );
            }, $terms);
        }
        
        return rest_ensure_response($taxonomies);
    }
    
    public function add_admin_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['pdf_file'] = __('PDF File', 'sunshineportal-pdf');
        $new_columns['download_count'] = __('Downloads', 'sunshineportal-pdf');
        $new_columns['file_size'] = __('File Size', 'sunshineportal-pdf');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    public function display_admin_columns($column, $post_id) {
        switch ($column) {
            case 'pdf_file':
                $pdf_file_id = get_post_meta($post_id, '_pdf_file_id', true);
                if ($pdf_file_id) {
                    $file_url = wp_get_attachment_url($pdf_file_id);
                    echo '<a href="' . esc_url($file_url) . '" target="_blank">' . __('View PDF', 'sunshineportal-pdf') . '</a>';
                } else {
                    echo __('No file', 'sunshineportal-pdf');
                }
                break;
                
            case 'download_count':
                $count = get_post_meta($post_id, '_download_count', true) ?: 0;
                echo esc_html($count);
                break;
                
            case 'file_size':
                echo esc_html(get_post_meta($post_id, '_file_size', true));
                break;
        }
    }
    
    public function pdf_manager_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_admin' => 'false',
        ), $atts);
        
        ob_start();
        include SUNSHINEPORTAL_PDF_PLUGIN_PATH . 'templates/pdf-manager-frontend.php';
        return ob_get_clean();
    }
}

// Initialize the plugin
function sunshineportal_pdf_init() {
    new SunshinePortal_PDF_Manager();
}
add_action('plugins_loaded', 'sunshineportal_pdf_init');

// Activation hook
register_activation_hook(__FILE__, 'sunshineportal_pdf_activate');
function sunshineportal_pdf_activate() {
    // Create default terms
    $default_terms = array(
        'pdf_category' => array('Documentation', 'Tutorial', 'Template', 'Report', 'Guide'),
        'pdf_type' => array('Beginner', 'Intermediate', 'Advanced', 'Reference'),
        'pdf_department' => array('Marketing', 'Development', 'Design', 'Human Resources', 'Sales')
    );
    
    foreach ($default_terms as $taxonomy => $terms) {
        foreach ($terms as $term) {
            if (!term_exists($term, $taxonomy)) {
                wp_insert_term($term, $taxonomy);
            }
        }
    }
    
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'sunshineportal_pdf_deactivate');
function sunshineportal_pdf_deactivate() {
    flush_rewrite_rules();
}