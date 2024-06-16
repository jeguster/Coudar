<?php
get_header();
if (have_posts()) :
    while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="max-width: 800px; margin: 0 auto;">
            <h1 class="entry-title"><?php the_title(); ?></h1>
            <div class="entry-content">
                <?php the_content(); ?>
                <p><strong>Date:</strong> <?php echo date_i18n(get_option('coudar_date_format', 'd/m/Y'), strtotime(get_post_meta(get_the_ID(), 'course_date', true))); ?></p>
                <p><strong>Time:</strong> <?php echo date_i18n(get_option('coudar_time_format', 'H:i'), strtotime(get_post_meta(get_the_ID(), 'course_time', true))); ?></p>
                <p><strong>Price:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), 'course_price', true)); ?></p>
                <div>
                    <?php if (has_post_thumbnail()) {
                        the_post_thumbnail('medium'); // Change 'medium' to the desired image size
                    } ?>
                </div>
                <h2>Register for this Course</h2>
                <form id="course-registration-form">
                    <label for="participant_name">Name:</label>
                    <input type="text" id="participant_name" name="participant_name" required>

                    <label for="participant_email">Email:</label>
                    <input type="email" id="participant_email" name="participant_email" required>

                    <label for="participant_phone">Phone (optional):</label>
                    <input type="tel" id="participant_phone" name="participant_phone">

                    <label for="participant_count">Number of Participants:</label>
                    <input type="number" id="participant_count" name="participant_count" required min="1">

                    <label for="participant_message">Message:</label>
                    <textarea id="participant_message" name="participant_message"></textarea>

                    <button type="submit" id="submit_button" disabled>Submit</button>
                </form>
                <div id="form-message"></div>
            </div>
        </article>
    <?php endwhile;
else :
    echo '<p>No content found</p>';
endif;
get_footer();
?>
