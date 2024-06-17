jQuery(document).ready(function($) {
    $('#course-registration-form').on('submit', function(event) {
        event.preventDefault();

        var formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: coudar_ajax.ajax_url,
            data: {
                action: 'coudar_register_course',
                data: formData
            },
            success: function(response) {
                if (response.success) {
                    alert('Thank you for your submission!');
                } else {
                    alert('There was an error with your submission. Please try again.');
                }
            },
            error: function() {
                alert('There was an error with your submission. Please try again.');
            }
        });
    });
});
