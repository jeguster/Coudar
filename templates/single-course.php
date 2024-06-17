<?php
/*
Template Name: Course Template
*/

get_header(); ?>

<div id="primary" class="content-area" style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <main id="main" class="site-main" role="main">
        <?php
        while (have_posts()) : the_post(); 
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header" style="text-align: center;">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header>

                <div class="entry-content" style="text-align: center;">
                    <?php the_content(); ?>
                    <p><strong>Date:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), 'course_date', true)); ?></p>
                    <p><strong>Time:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), 'course_time', true)); ?></p>
                    <p><strong>Price:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), 'course_price', true)); ?></p>
                </div>

                <?php if (has_post_thumbnail()): ?>
                    <div class="course-thumbnail" style="text-align: center;">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>

                <h2 style="text-align: center;">Register for this Course</h2>
                <form id="course-registration-form" style="max-width: 800px; margin: 0 auto; padding: 20px;">
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
                    <button type="submit" class="button">Submit</button>
                </form>
            </article>
        <?php endwhile; ?>
    </main><!-- .site-main -->
</div><!-- .content-area -->

<?php get_footer(); ?>
