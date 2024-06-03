/*
View datatable table script for admnistration/?p=m
 */
// change of the filter will change the input component
let applyFilter = function () {

    let filter_1 = $('select[name="filter_sel[]"]').map(function () {
            return $(this).val();
        }).get(),
        filter_2 = $('select[name^="filter_operator"]').map(function () {
            return $(this).val();
        }).get(),
        filter_3 = $(':input[name^="filter_value"]').map(function () {
            return $(this).val();
        }).get();

    let url = document.location.origin + '/administration/api/?api=MJSN';

    $.post(url, {c1: filter_1, c2: filter_2, c3: filter_3}, function (xhr) {

        let datatable = $('#memberTable').dataTable();

        datatable.fnClearTable();

        if (xhr.dataset.length) {

            datatable.fnAddData(xhr.dataset);

            // datatable.draw();
        }

    }, 'json').fail(function (xhr) {


    });

};

// Hides all the input
$('.filter-input').hide();
$('.filter-val-container > .filter-input').first().show();

$(document).on('change', 'select[name="filter_sel[]"]', function (ev) {

    ev.preventDefault();

    let value = $(this).val();

    if (value) {
        let filter = $(this).closest('.filter-row').find('.filter-val-container');

        filter.find('.filter-input').hide();

        filter.find('.' + value).show();
    }

});

// Remove filter row
$(document).on('click', 'a[data-action="removefilter"]', function (e) {
    e.preventDefault();
    $(this).closest('.filter-row').remove();
    applyFilter();
});

// Resets
$(document).on('click', 'button[data-action="resetfilter"]', function (e) {

    let filter_row = $('.filter-row');

    // reset the filter values
    let sel = $('select[name="filter_sel[]"]');
    if (sel.length > 1) {
        for (let i = 1; i < filter_row.length; i++) {
            filter_row.eq(i).remove();
        }
        sel.eq(0).val('').trigger('change');
    } else {
        sel.eq(0).val('').trigger('change');
    }

    initFilters('jc1a');
    applyFilter();
});

let initFilters = function (dom) {

    let cdom = $('#' + dom);
    if (cdom.length) {

        let frows = cdom.closest('.filter-row');

        frows.find('.filter-input').hide();
        frows.find('.filter-val-container > .filter-input').first().show();
    }

}

// $(document).on('click', 'button[data-action="resendmail"]', function(e) {
//     e.preventDefault();
//     let val = $(this).val();
//     $(this).find('span').text('Sending..');
//     $('#resendmailFrm #resend_mail').val( val );
//     $('#resendmailFrm').trigger('submit');
// });

// Filters
$(document).on('click', 'a[data-action="addfilter"]', function (e) {

    e.preventDefault();

    let url = document.location.origin + '/administration/api/?api=MFAD';

    let rowcount = $('.filter-row').length;

    $.post(url, {rows: rowcount}, function (xhr) {

        $('#member-query-filter').append(xhr);

    }).fail(function (xhr) {

        alert(xhr);
    });
});


$(document).on('click', 'button[name="apply"]', function (e) {
    applyFilter();
});
