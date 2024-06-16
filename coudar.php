<?php

/*
Plugin Name: Coudar - Course Calendar
Plugin URI: http://your-plugin-url.com/
Description: Easy management for course schedules with Coudar.
Version: 1.0.0
Author: Pate
Author URI: http://your-author-url.com/
Text Domain: coudar
Domain Path: /lang
*/

define( 'COUDAR_FILE', __FILE__ );
define( 'COUDAR_PREFIX', '_coudar' );
define( 'COUDAR_PATH', untrailingslashit( plugin_dir_path( COUDAR_FILE ) ) );
define( 'COUDAR_WOO', in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ? true : false );
define( 'COUDAR_GMT_OFFSET', current_time('timestamp') - time() );
define( 'COUDAR_VERSION', '1.0.0' );

global $coudar_courses;
$coudar_courses = [];

register_activation_hook( __FILE__, 'coudar_plugin_activation' );
register_deactivation_hook( __FILE__, 'coudar_plugin_deactivation' );

add_action( 'plugins_loaded', 'coudar_load_domain' );

function coudar_load_domain() {
  load_plugin_textdomain( 'coudar', false, basename( dirname( __FILE__ ) ) . '/lang/' );
}

function coudar_plugin_activation() {
    // Activation code here...
}

function coudar_plugin_deactivation() {
    // Deactivation code here...
}

// Register a custom post type for Courses.
function coudar_register_course_post_type() {
    $labels = array(
        'name' => __('Courses'),
        'singular_name' => __('Course'),
        'add_new' => __('Add New Course'),
        'add_new_item' => __('Add New Course'),
        'edit_item' => __('Edit Course'),
        'new_item' => __('New Course'),
        'view_item' => __('View Course'),
        'search_items' => __('Search Courses'),
        'not_found' => __('No courses found'),
        'not_found_in_trash' => __('No courses found in Trash'),
        'all_items' => __('All Courses'),
        'menu_name' => __('Courses'),
        'name_admin_bar' => __('Course'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'course'),
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'excerpt', 'elementor'), // Add 'elementor'
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-calendar-alt',
    );

    register_post_type('course', $args);
}

add_action('init', 'coudar_register_course_post_type');

// Remove default meta boxes for Courses
function coudar_remove_default_meta_boxes() {
    remove_meta_box('formatdiv', 'course', 'normal'); // Post format
    remove_meta_box('postexcerpt', 'course', 'normal'); // Excerpt
    remove_meta_box('authordiv', 'course', 'normal'); // Author
    remove_meta_box('revisionsdiv', 'course', 'normal'); // Revisions
    remove_meta_box('postcustom', 'course', 'normal'); // Custom fields
    remove_meta_box('slugdiv', 'course', 'normal'); // Slug
    remove_meta_box('submitdiv', 'course', 'side'); // Publish
    remove_meta_box('trackbacksdiv', 'course', 'normal'); // Trackbacks
    remove_meta_box('commentsdiv', 'course', 'normal'); // Comments
    remove_meta_box('commentstatusdiv', 'course', 'normal'); // Comment status
}

add_action('do_meta_boxes', 'coudar_remove_default_meta_boxes');

// Add meta boxes for course details
function coudar_add_meta_boxes() {
    add_meta_box(
        'coudar_course_details',
        __('Course Details'),
        'coudar_render_meta_box',
        'course',
        'normal',
        'default'
    );
}

add_action('add_meta_boxes', 'coudar_add_meta_boxes');

function coudar_render_meta_box($post) {
    // Add nonce for security and authentication.
    wp_nonce_field('coudar_nonce_action', 'coudar_nonce');

    $course_date = get_post_meta($post->ID, 'course_date', true);
    $course_time = get_post_meta($post->ID, 'course_time', true);
    $course_price = get_post_meta($post->ID, 'course_price', true);
    $course_message = get_post_meta($post->ID, 'course_message', true);

    echo '<p><label for="course_date">' . __('Course Date', 'coudar') . '</label><br>';
    echo '<input type="date" id="course_date" name="course_date" value="' . esc_attr($course_date) . '" class="course-details-input"></p>';

    echo '<p><label for="course_time">' . __('Course Time', 'coudar') . '</label><br>';
    echo '<input type="time" id="course_time" name="course_time" value="' . esc_attr($course_time) . '" class="course-details-input"></p>';

    echo '<p><label for="course_price">' . __('Course Price', 'coudar') . '</label><br>';
    echo '<input type="text" id="course_price" name="course_price" value="' . esc_attr($course_price) . '" class="course-details-input"></p>';

    echo '<p><label for="course_message">' . __('Email Message', 'coudar') . '</label><br>';
    echo '<textarea id="course_message" name="course_message" class="course-details-input" style="height: 100px;">' . esc_textarea($course_message) . '</textarea></p>';
}

// Save meta box content.
function coudar_save_meta_box($post_id) {
    // Verify nonce.
    if (!isset($_POST['coudar_nonce']) || !wp_verify_nonce($_POST['coudar_nonce'], 'coudar_nonce_action')) {
        return;
    }

    // Check autosave.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions.
    if (isset($_POST['post_type']) && 'page' === $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    } elseif (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Sanitize user input.
    $course_date = sanitize_text_field($_POST['course_date']);
    $course_time = sanitize_text_field($_POST['course_time']);
    $course_price = sanitize_text_field($_POST['course_price']);
    $course_message = sanitize_textarea_field($_POST['course_message']);

    // Update the meta field in the database.
    update_post_meta($post_id, 'course_date', $course_date);
    update_post_meta($post_id, 'course_time', $course_time);
    update_post_meta($post_id, 'course_price', $course_price);
    update_post_meta($post_id, 'course_message', $course_message);
}

add_action('save_post', 'coudar_save_meta_box');

// Enqueue Dashicons and Custom Styles
function coudar_enqueue_assets() {
    wp_enqueue_style('dashicons');
    wp_enqueue_style('coudar-styles', plugins_url('style.css', __FILE__));
}

add_action('wp_enqueue_scripts', 'coudar_enqueue_assets');

// Enqueue custom admin styles
function coudar_enqueue_admin_styles() {
    wp_enqueue_style('coudar-admin-styles', plugins_url('admin-style.css', __FILE__));
}

add_action('admin_enqueue_scripts', 'coudar_enqueue_admin_styles');

// Load the custom template for single course posts
function coudar_load_single_course_template($template) {
    global $post;

    if ($post->post_type == 'course') {
        // Look in plugin folder first
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/single-course.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }

    return $template;
}

add_filter('single_template', 'coudar_load_single_course_template');

// Shortcode to display the course calendar
function coudar_course_calendar_shortcode() {
    $args = array(
        'post_type' => 'course',
        'posts_per_page' => -1,
        'order' => 'ASC',
        'orderby' => 'meta_value',
        'meta_key' => 'course_date'
    );

    $query = new WP_Query($args);
    if ($query->have_posts()) {
        $output = '<div class="course-calendar">';
        while ($query->have_posts()) {
            $query->the_post();
            $course_date = get_post_meta(get_the_ID(), 'course_date', true);
            $course_time = get_post_meta(get_the_ID(), 'course_time', true);
            $course_price = get_post_meta(get_the_ID(), 'course_price', true);
            $output .= '<div class="course-item">';
            $output .= '<a href="' . get_permalink() . '">';
            $output .= '<h2>' . get_the_title() . '</h2>';
            $output .= '<div>' . get_the_post_thumbnail(get_the_ID(), 'thumbnail') . '</div>';
            $output .= '<p>' . $course_date . ' at ' . $course_time . '</p>';
            $output .= '<p>Price: ' . $course_price . '</p>';
            $output .= '</a>';
            $output .= '</div>';
        }
        $output .= '</div>';
        wp_reset_postdata();
    } else {
        $output = '<p>No courses found</p>';
    }

    return $output;
}

add_shortcode('coudar_course_calendar', 'coudar_course_calendar_shortcode');

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'coudar_flush_rewrite_rules');
register_deactivation_hook(__FILE__, 'coudar_flush_rewrite_rules');

function coudar_flush_rewrite_rules() {
    coudar_register_course_post_type();
    flush_rewrite_rules();
}

// Add settings page
function coudar_add_settings_page() {
    add_submenu_page(
        'edit.php?post_type=course', // Parent slug
        __('Coudar Settings', 'coudar'), // Page title
        __('Settings', 'coudar'), // Menu title
        'manage_options', // Capability
        'coudar-settings', // Menu slug
        'coudar_render_settings_page' // Callback function
    );
}

add_action('admin_menu', 'coudar_add_settings_page');

function coudar_render_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Coudar Settings', 'coudar'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('coudar_settings_group');
            do_settings_sections('coudar-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
function coudar_register_settings() {
    register_setting('coudar_settings_group', 'coudar_date_format');
    register_setting('coudar_settings_group', 'coudar_time_format');
    register_setting('coudar_settings_group', 'coudar_currency_format');

    add_settings_section(
        'coudar_settings_section',
        __('Course Settings', 'coudar'),
        'coudar_settings_section_callback', // Non-null callback function
        'coudar-settings'
    );

    add_settings_field(
        'coudar_date_format',
        __('Date Format', 'coudar'),
        'coudar_date_format_callback',
        'coudar-settings',
        'coudar_settings_section'
    );

    add_settings_field(
        'coudar_time_format',
        __('Time Format', 'coudar'),
        'coudar_time_format_callback',
        'coudar-settings',
        'coudar_settings_section'
    );

    add_settings_field(
        'coudar_currency_format',
        __('Currency Format', 'coudar'),
        'coudar_currency_format_callback',
        'coudar-settings',
        'coudar_settings_section'
    );
}

add_action('admin_init', 'coudar_register_settings');

function coudar_settings_section_callback() {
    // This callback function is intentionally left blank.
}

function coudar_date_format_callback() {
    $date_format = get_option('coudar_date_format', 'd/m/Y');
    echo '<input type="text" name="coudar_date_format" value="' . esc_attr($date_format) . '" class="regular-text">';
    echo '<p class="description">' . __('Enter the date format. Default: d/m/Y', 'coudar') . '</p>';
}

function coudar_time_format_callback() {
    $time_format = get_option('coudar_time_format', 'H:i');
    echo '<input type="text" name="coudar_time_format" value="' . esc_attr($time_format) . '" class="regular-text">';
    echo '<p class="description">' . __('Enter the time format. Default: H:i', 'coudar') . '</p>';
}

function coudar_currency_format_callback() {
    $currency_format = get_option('coudar_currency_format', '$');
    echo '<input type="text" name="coudar_currency_format" value="' . esc_attr($currency_format) . '" class="regular-text">';
    echo '<p class="description">' . __('Enter the currency format. Default: $', 'coudar') . '</p>';
}
