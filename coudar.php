<?php
/*
Plugin Name: Coudar
Description: A simple course registration plugin.
Version: 1.0.0
Author: Your Name
*/

// Register Custom Post Type
function coudar_register_post_type() {
    $labels = array(
        'name' => __('Coudar', 'coudar'),
        'singular_name' => __('Course', 'coudar'),
        'add_new' => __('Add New Course', 'coudar'),
        'add_new_item' => __('Add New Course', 'coudar'),
        'edit_item' => __('Edit Course', 'coudar'),
        'new_item' => __('New Course', 'coudar'),
        'view_item' => __('View Course', 'coudar'),
        'search_items' => __('Search Courses', 'coudar'),
        'not_found' => __('No courses found', 'coudar'),
        'not_found_in_trash' => __('No courses found in Trash', 'coudar'),
        'all_items' => __('All Courses', 'coudar'),
        'menu_name' => __('Coudar', 'coudar'),
        'name_admin_bar' => __('Course', 'coudar'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'course'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-calendar-alt', // Add calendar icon
    );

    register_post_type('course', $args);
}
add_action('init', 'coudar_register_post_type');

// Register settings
function coudar_register_settings() {
    register_setting('coudar_settings_group', 'coudar_admin_email_subject');
    register_setting('coudar_settings_group', 'coudar_admin_email_body');
    register_setting('coudar_settings_group', 'coudar_user_email_subject');
    register_setting('coudar_settings_group', 'coudar_user_email_body');
}
add_action('admin_init', 'coudar_register_settings');

// Add settings page
function coudar_settings_page() {
    add_menu_page(
        'Coudar Settings',
        'Coudar Settings',
        'manage_options',
        'coudar-settings',
        'coudar_settings_page_html'
    );
}
add_action('admin_menu', 'coudar_settings_page');

// Settings page HTML
function coudar_settings_page_html() {
    ?>
    <div class="wrap">
        <h1>Coudar Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('coudar_settings_group'); ?>
            <?php do_settings_sections('coudar_settings_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Admin Email Subject</th>
                    <td><input type="text" name="coudar_admin_email_subject" value="<?php echo esc_attr(get_option('coudar_admin_email_subject', 'New Course Registration')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Admin Email Body</th>
                    <td><textarea name="coudar_admin_email_body" rows="10" cols="50"><?php echo esc_textarea(get_option('coudar_admin_email_body', 'You have received a new course registration.')); ?></textarea></td>
                </tr>
                <tr valign="top">
                    <th scope="row">User Email Subject</th>
                    <td><input type="text" name="coudar_user_email_subject" value="<?php echo esc_attr(get_option('coudar_user_email_subject', 'Course Registration Confirmation')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">User Email Body</th>
                    <td><textarea name="coudar_user_email_body" rows="10" cols="50"><?php echo esc_textarea(get_option('coudar_user_email_body', 'Thank you for your registration!')); ?></textarea></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Enqueue custom scripts
function coudar_enqueue_scripts() {
    wp_enqueue_script('coudar-scripts', plugins_url('scripts.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('coudar-scripts', 'coudar_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('coudar_register_course')
    ));
}
add_action('wp_enqueue_scripts', 'coudar_enqueue_scripts');

// Enqueue frontend styles
function coudar_enqueue_styles() {
    wp_enqueue_style('coudar-styles', plugins_url('styles.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'coudar_enqueue_styles');

// Add Metaboxes
function coudar_add_meta_boxes() {
    add_meta_box(
        'coudar_course_details',
        'Course Details',
        'coudar_render_meta_box',
        'course',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'coudar_add_meta_boxes');

function coudar_render_meta_box($post) {
    // Retrieve current data based on post ID
    $course_date = get_post_meta($post->ID, 'course_date', true);
    $course_time = get_post_meta($post->ID, 'course_time', true);
    $course_price = get_post_meta($post->ID, 'course_price', true);
    
    // Add a nonce field so we can check for it later.
    wp_nonce_field('coudar_save_meta_box_data', 'coudar_meta_box_nonce');
    ?>
    <label for="course_date">Course Date:</label>
    <input type="date" id="course_date" name="course_date" value="<?php echo esc_attr($course_date); ?>" />
    <br />
    <label for="course_time">Course Time:</label>
    <input type="time" id="course_time" name="course_time" value="<?php echo esc_attr($course_time); ?>" />
    <br />
    <label for="course_price">Course Price:</label>
    <input type="text" id="course_price" name="course_price" value="<?php echo esc_attr($course_price); ?>" />
    <?php
}

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
    $course_date = sanitize_text_field($_POST['course_date']);
    $course_time = sanitize_text_field($_POST['course_time']);
    $course_price = sanitize_text_field($_POST['course_price']);
    // Update the meta field in the database.
    update_post_meta($post_id, 'course_date', $course_date);
    update_post_meta($post_id, 'course_time', $course_time);
    update_post_meta($post_id, 'course_price', $course_price);
}
add_action('save_post', 'coudar_save_meta_box_data');

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
    if (!wp_mail($admin_email, $subject, $message)) {
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
    if (!wp_mail($user_email, $user_subject, $user_message)) {
        error_log('User confirmation email failed to send.');
    }

    // Return success response
    wp_send_json_success();
}

// Register AJAX handlers
add_action('wp_ajax_coudar_register_course', 'coudar_register_course');
add_action('wp_ajax_nopriv_coudar_register_course', 'coudar_register_course');

// Shortcode to display courses
function coudar_courses_shortcode($atts) {
    ob_start();

    $query = new WP_Query(array(
        'post_type' => 'course',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'ASC'
    ));

    if ($query->have_posts()) {
        echo '<div class="coudar-courses">';
        while ($query->have_posts()) {
            $query->the_post();
            $course_date = get_post_meta(get_the_ID(), 'course_date', true);
            $course_time = get_post_meta(get_the_ID(), 'course_time', true);
            $course_price = get_post_meta(get_the_ID(), 'course_price', true);
            ?>
            <div class="coudar-course">
                <h2><?php the_title(); ?></h2>
                <p>Date: <?php echo esc_html($course_date); ?></p>
                <p>Time: <?php echo esc_html($course_time); ?></p>
                <p>Price: <?php echo esc_html($course_price); ?></p>
                <?php if (has_post_thumbnail()): ?>
                    <div class="coudar-course-thumbnail">
                        <?php the_post_thumbnail('thumbnail'); ?>
                    </div>
                <?php endif; ?>
                <a href="<?php the_permalink(); ?>">View Details</a>
            </div>
            <?php
        }
        echo '</div>';
    } else {
        echo '<p>No courses found.</p>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('coudar_courses', 'coudar_courses_shortcode');

// Display course details on single course page
function coudar_display_course_details($content) {
    if (is_singular('course')) {
        global $post;
        $course_date = get_post_meta($post->ID, 'course_date', true);
        $course_time = get_post_meta($post->ID, 'course_time', true);
        $course_price = get_post_meta($post->ID, 'course_price', true);
        $course_content = '';

        $course_content .= '<h1>' . get_the_title() . '</h1>';
        $course_content .= '<p>' . get_the_content() . '</p>';
        if (has_post_thumbnail()) {
            $course_content .= '<div class="coudar-course-thumbnail">' . get_the_post_thumbnail($post->ID, 'large') . '</div>';
        }
        $course_content .= '<p><strong>Date:</strong> ' . esc_html($course_date) . '</p>';
        $course_content .= '<p><strong>Time:</strong> ' . esc_html($course_time) . '</p>';
        $course_content .= '<p><strong>Price:</strong> ' . esc_html($course_price) . '</p>';

        // Add registration form
        $course_content .= '<h2>Register for this Course</h2>';
        $course_content .= '<form id="coudar-registration-form">';
        $course_content .= '<label for="participant_name">Name:</label>';
        $course_content .= '<input type="text" id="participant_name" name="participant_name" required><br>';
        $course_content .= '<label for="participant_email">Email:</label>';
        $course_content .= '<input type="email" id="participant_email" name="participant_email" required><br>';
        $course_content .= '<label for="participant_phone">Phone (optional):</label>';
        $course_content .= '<input type="text" id="participant_phone" name="participant_phone"><br>';
        $course_content .= '<label for="participant_count">Number of Participants:</label>';
        $course_content .= '<input type="number" id="participant_count" name="participant_count" required><br>';
        $course_content .= '<label for="participant_message">Message:</label>';
        $course_content .= '<textarea id="participant_message" name="participant_message"></textarea><br>';
        $course_content .= '<button type="submit">Submit</button>';
        $course_content .= '</form>';

        return $course_content;
    }
    return $content;
}
add_filter('the_content', 'coudar_display_course_details');
