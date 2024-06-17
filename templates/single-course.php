<?php get_header(); ?>

<div class="course-details">
    <?php while ( have_posts() ) : the_post(); ?>
        <h1><?php the_title(); ?></h1>
        <div class="course-meta">
            <p><?php echo get_post_meta(get_the_ID(), '_course_date', true); ?></p>
            <p><?php echo get_post_meta(get_the_ID(), '_course_time', true); ?></p>
            <p><?php echo get_post_meta(get_the_ID(), '_course_price', true); ?></p>
        </div>
        <div class="course-content">
            <?php the_content(); ?>
        </div>
        <?php if ( has_post_thumbnail() ) {
            the_post_thumbnail('medium');
        } ?>
    <?php endwhile; ?>
</div>

<form id="course-registration-form">
    <label for="participant_name">Name:</label>
    <input type="text" id="participant_name" name="participant_name" required><br>
    <label for="participant_email">Email:</label>
    <input type="email" id="participant_email" name="participant_email" required><br>
    <label for="participant_phone">Phone (optional):</label>
    <input type="text" id="participant_phone" name="participant_phone"><br>
    <label for="participant_count">Number of Participants:</label>
    <input type="number" id="participant_count" name="participant_count" required><br>
    <label for="participant_message">Message:</label>
    <textarea id="participant_message" name="participant_message"></textarea><br>
    <?php wp_nonce_field('coudar_register_course', 'coudar_nonce'); ?>
    <button type="submit" class="button">Submit</button>
</form>

<?php get_footer(); ?>
