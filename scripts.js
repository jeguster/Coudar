jQuery(document).ready(function($) {
    $('#coudar-registration-form').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize() + '&coudar_nonce=' + coudar_ajax.nonce;

        $.ajax({
            type: 'POST',
            url: coudar_ajax.ajax_url,
            data: {
                action: 'coudar_register_course',
                data: formData
            },
            success: function(response) {
                if (response.success) {
                    alert('Registration successful! Thank you.');
                } else {
                    alert('There was an error with your registration. Please try again.');
                }
            },
            error: function() {
                alert('There was an error with your registration. Please try again.');
            }
        });
    });
});
