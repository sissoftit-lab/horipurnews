(function($) {
    "use strict";

    // Show the bulk-action bar only while at least one row is ticked.
    function toggleBulkBar() {
        $('.selectPost').toggle($('.postCheck:checked').length > 0);
    }

    // Reload the table with the given option's data-href.
    function reloadTable($option) {
        table.ajax.url($option.data('href')).load();
    }

    // Fill the category dropdown for the chosen language.
    function loadCategories(langId) {
        return $.get(mainurl + 'admin/category-filter/language/' + langId, function(data) {
            $('#category_id').html(data);
        });
    }

    $(document).ready(function() {
        // Table already loads with the default language (see index view); only fetch categories here.
        loadCategories($('#filter_lang').val());
    });

    $(document).on('change', '#filter_lang', function() {
        reloadTable($('#filter_lang option:selected'));
        loadCategories($(this).val());
    });

    $(document).on('change', '#category_id', function() {
        reloadTable($('#category_id option:selected'));
    });

    $(document).on('change', '#filter_user', function() {
        reloadTable($('#filter_user option:selected'));
    });

    $(document).on('change', '#headercheck', function() {
        $('.postCheck').prop('checked', this.checked);
        toggleBulkBar();
    });

    $(document).on('change', '.postCheck', function() {
        $('#headercheck').prop('checked', $('.postCheck').length === $('.postCheck:checked').length);
        toggleBulkBar();
    });

})(jQuery);
