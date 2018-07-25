window.$ = window.jQuery = require('jquery');

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).on('keydown', 'input[type=text]', function(e) {
    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
        (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
        (e.keyCode >= 35 && e.keyCode <= 40)) {
             return;
    }

    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
    }
});

$(function() {
    $('form').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData($(this)[0]);

        $('#calculate-button').addClass('active');
        $('.warning').hide().empty();
        $('.payment-table').hide().empty();

        $.ajax({
            method: 'POST',
            url: '/calculate',
            data: formData,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false
        }).done(function(result) {
            $('#calculate-button').removeClass('active');

            if (result.result) {
                $('.payment-table').append(result.schedule).show();
            }
            else {
                $.each(result.errors, function(index, value) {
                    $('.warning').append('<div>' + value + '</div>');
                });

                $('.warning').show();
            }
        }).fail(function(jqXHR) {
            console.log(jqXHR.responseText);
        });
    });
});