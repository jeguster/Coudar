<?php
/*
Plugin Name: Coudar
Description: A simple course registration plugin.
Version: 1.0.0
Author: Your Name
*/

// Register custom post type
function coudar_register_post_type() {
    $labels = array(
        'name' => __('Courses', 'coudar'),
        'singular_name' => __('Course', 'coudar'),
        'add_new' => __('Add New Course', 'coudar'),
        'add_new_item' => __('Add New Course', 'coudar'),
        'edit_item' => __('Edit Course', 'coudar'),
        'new_item' => __('New Course', 'coudar'),
        'view_item' => __('View Course', 'coudar'),
        'search_items' => __('Search Courses', 'coudar'),
        'not_found' => __('No courses found', 'coudar'),
        'not_found_in_trash' => __('No courses found in Trash', 'coudar'),
        'parent_item_colon' => __('Parent Course:', 'coudar'),
        'all_items' => __('All Courses', 'coudar'),
        'archives' => __('Course Archives', 'coudar'),
        'insert_into_item' => __('Insert into course', 'coudar'),
        'uploaded_to_this_item' => __('Uploaded to this course', 'coudar'),
        'featured_image' => __('Featured image', 'coudar'),
        'set_featured_image' => __('Set featured image', 'coudar'),
        'remove_featured_image' => __('Remove featured image', 'coudar'),
        'use_featured_image' => __('Use as featured image', 'coudar'),
        'menu_name' => __('Courses', 'coudar'),
        'filter_items_list' => __('Filter courses list', 'coudar'),
        'items_list_navigation' => __('Courses list navigation', 'coudar'),
        'items_list' => __('Courses list', 'coudar'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_icon' => 'dashicons-calendar',
        'rewrite' => array('slug' => 'courses'),
    );

    register_post_type('course', $args);
}
add_action('init', 'coudar_register_post_type');

// Shortcode to display courses
function coudar_courses_shortcode() {
    $args = array(
        'post_type' => 'course',
        'posts_per_page' => -1,
    );

    $query = new WP_Query($args);

    $output = '<div class="course-thumbnails">';
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $output .= '<div class="course-thumbnail">';
            if (has_post_thumbnail()) {
                $output .= get_the_post_thumbnail(get_the_ID(), 'medium');
            }
            $output .= '<h3>' . get_the_title() . '</h3>';
            $output .= '<p>' . get_the_date() . '</p>';
            $output .= '<p>' . get_the_excerpt() . '</p>';
            $output .= '<a href="' . get_permalink() . '" class="button">View Course</a>';
            $output .= '</div>';
        }
        wp_reset_postdata();
    } else {
        $output .= '<p>No courses found</p>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode('coudar_courses', 'coudar_courses_shortcode');

// Enqueue custom scripts and styles
function coudar_enqueue_scripts() {
    wp_enqueue_script('coudar-scripts', plugins_url('scripts.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('coudar-scripts', 'coudar_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}

add_action('wp_enqueue_scripts', 'coudar_enqueue_scripts');

function coudar_enqueue_styles() {
    wp_enqueue_style('coudar-styles', plugins_url('styles.css', __FILE__));
}

add_action('wp_enqueue_scripts', 'coudar_enqueue_styles');

// Handle AJAX form submission
function coudar_register_course() {
    // Ensure that WordPress core functions are available
    if (!function_exists('wp_mail')) {
        require_once ABSPATH . WPINC . '/pluggable.php';
    }

    parse_str($_POST['data'], $form_data);

    // Log form data for debugging
    error_log(print_r($form_data, true));

    // Check nonce for security
    if (!isset($form_data['coudar_nonce']) || !wp_verify_nonce($form_data['coudar_nonce'], 'coudar_register_course')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Prepare email details
    $admin_email = get_option('admin_email');
    $subject = 'New Course Registration';
    $message = 'Name: ' . sanitize_text_field($form_data['participant_name']) . "\n";
    $message .= 'Email: ' . sanitize_email($form_data['participant_email']) . "\n";
    $message .= 'Phone: ' . sanitize_text_field($form_data['participant_phone']) . "\n";
    $message .= 'Number of Participants: ' . intval($form_data['participant_count']) . "\n";
    $message .= 'Message: ' . sanitize_textarea_field($form_data['participant_message']);

    // Send email to admin
    $headers = array('Content-Type: text/plain; charset=UTF-8');
    if (!wp_mail($admin_email, $subject, $message, $headers)) {
        error_log('Admin email failed to send.');
    }

    // Prepare confirmation email for user
    $user_email = sanitize_email($form_data['participant_email']);
    $user_subject = 'Course Registration Confirmation';
    $user_message = 'Thank you for your submission! Here are the details of your registration:' . "\n\n";
    $user_message .= 'Name: ' . sanitize_text_field($form_data['participant_name']) . "\n";
    $user_message .= 'Email: ' . sanitize_email($form_data['participant_email']) . "\n";
    $user_message .= 'Phone: ' . sanitize_text_field($form_data['participant_phone']) . "\n";
    $user_message .= 'Number of Participants: ' . intval($form_data['participant_count']) . "\n";
    $user_message .= 'Message: ' . sanitize_textarea_field($form_data['participant_message']) . "\n\n";
    $user_message .= 'We look forward to seeing you at the course!';

    // Send confirmation email to user
    if (!wp_mail($user_email, $user_subject, $user_message, $headers)) {
        error_log('User confirmation email failed to send.');
    }

    // Return success response
    wp_send_json_success();
}

// Register AJAX handlers
add_action('wp_ajax_coudar_register_course', 'coudar_register_course');
add_action('wp_ajax_nopriv_coudar_register_course', 'coudar_register_course');

// Add meta boxes
function coudar_add_meta_boxes() {
    add_meta_box(
        'course_details',
        __('Course Details', 'coudar'),
        'coudar_render_meta_box',
        'course',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'coudar_add_meta_boxes');

// Render meta box content
function coudar_render_meta_box($post) {
    // Add a nonce field so we can check for it later.
    wp_nonce_field('coudar_save_meta_box_data', 'coudar_meta_box_nonce');

    $date = get_post_meta($post->ID, '_course_date', true);
    $time = get_post_meta($post->ID, '_course_time', true);
    $price = get_post_meta($post->ID, '_course_price', true);

    echo '<label for="course_date">';
    _e('Date', 'coudar');
    echo '</label> ';
    echo '<input type="date" id="course_date" name="course_date" value="' . esc_attr($date) . '" size="25" />';

    echo '<label for="course_time">';
    _e('Time', 'coudar');
    echo '</label> ';
    echo '<input type="time" id="course_time" name="course_time" value="' . esc_attr($time) . '" size="25" />';

    echo '<label for="course_price">';
    _e('Price', 'coudar');
    echo '</label> ';
    echo '<input type="text" id="course_price" name="course_price" value="' . esc_attr($price) . '" size="25" />';
}

// Save meta box content
function coudar_save_meta_box_data($post_id) {
    // Check if our nonce is set.
    if (!isset($_POST['coudar_meta_box_nonce'])) {
        return;
    }

    // Verify that the nonce is valid.
    if (!wp_verify_nonce($_POST['coudar_meta_box_nonce'], 'coudar_save_meta_box_data')) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions.
    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    } else {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    // Sanitize user input.
    $date = sanitize_text_field($_POST['course_date']);
    $time = sanitize_text_field($_POST['course_time']);
    $price = sanitize_text_field($_POST['course_price']);

    // Update the meta field in the database.
    update_post_meta($post_id, '_course_date', $date);
    update_post_meta($post_id, '_course_time', $time);
    update_post_meta($post_id, '_course_price', $price);
}
add_action('save_post', 'coudar_save_meta_box_data');

// Add custom template for single course
function coudar_add_template($template) {
    if (is_singular('course')) {
        $template = plugin_dir_path(__FILE__) . 'templates/single-course.php';
    }
    return $template;
}
add_filter('single_template', 'coudar_add_template');
