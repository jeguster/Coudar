jQuery(document).ready(function($) {
    function validateForm() {
        var name = $('#participant_name').val();
        var email = $('#participant_email').val();
        var count = $('#participant_count').val();
        if (name && email && count > 0) {
            $('#submit_button').prop('disabled', false);
        } else {
            $('#submit_button').prop('disabled', true);
        }
    }

    $('#participant_name, #participant_email, #participant_count').on('input', validateForm);

    $('#course-registration-form').on('submit', function(event) {
        event.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: coudar_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'coudar_register_course',
                data: formData
            },
            success: function(response) {
                $('#form-message').html('<p>Thank you for your submission!</p>');
                $('#course-registration-form')[0].reset();
                $('#submit_button').prop('disabled', true);
            }
        });
    });
});
