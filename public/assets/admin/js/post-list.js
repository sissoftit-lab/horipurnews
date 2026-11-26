(function ($) {
    "use strict";

    var S = { page: 1, per_page: 25, q: '', lang: $('#ppLang').val() || '', category: '', type: '', state: '', from: '', to: '' };
    var reqId = 0, searchTimer = null, pendingDelete = null;
    var $rows = $('#ppRows');

    /* ---------- helpers ---------- */

    function toast(msg, isErr) {
        var $t = $('<div class="pp-toast' + (isErr ? ' err' : '') + '"><i class="fas fa-' + (isErr ? 'exclamation-circle' : 'check-circle') + '"></i></div>')
            .append($('<span>').text(msg));
        $('#ppToasts').append($t);
        setTimeout(function () { $t.fadeOut(250, function () { $t.remove(); }); }, 3200);
    }

    function selectedIds() {
        return $('.postCheck:checked').map(function () { return this.value; }).get();
    }

    function syncSelection() {
        var ids = selectedIds(), total = $('.postCheck').length;
        $('#ppSelCount').text(ids.length);
        $('#ppBulk').toggleClass('show', ids.length > 0);
        $('#headercheck').prop('checked', total > 0 && ids.length === total);
        $('.postCheck').each(function () { $(this).closest('tr').toggleClass('is-selected', this.checked); });
    }

    // IDs travel in the query string, so send large selections in batches
    // to stay under the web server's URL length limit.
    function sendInBatches(url, ids) {
        var size = 200;
        return $.ajax({ url: url, data: ids ? { ids: ids.slice(0, size).join(',') } : {}, dataType: 'json' })
            .then(function (r) {
                return ids && ids.length > size ? sendInBatches(url, ids.slice(size)) : r;
            });
    }

    function hasFilters() {
        return !!(S.q || S.category || S.type || S.state || S.from);
    }

    /* ---------- list loading ---------- */

    function load() {
        var id = ++reqId;
        $('#ppLoading').addClass('show');

        $.ajax({ url: PP.listUrl, data: S, dataType: 'json' })
            .done(function (r) {
                if (id !== reqId) { return; } // a newer request superseded this one
                if (r.page > r.last_page && r.last_page > 0) { S.page = r.last_page; return load(); }

                $rows.html(r.html);
                $('#cntAll').text(r.counts.all);
                $('#cntApproved').text(r.counts.approved);
                $('#cntPending').text(r.counts.pending);
                $('#ppInfo').text(r.total ? 'Showing ' + r.from + '–' + r.to + ' of ' + r.total + ' posts' : '0 posts');
                renderPager(r.page, r.last_page);
                $('#ppReset').toggleClass('show', hasFilters());
                syncSelection();
            })
            .fail(function (xhr) {
                if (id !== reqId) { return; }
                toast(xhr.status === 401 || xhr.status === 419 ? 'Session expired, please reload.' : 'Could not load posts.', true);
            })
            .always(function () {
                if (id === reqId) { $('#ppLoading').removeClass('show'); }
            });
    }

    function renderPager(page, last) {
        var $p = $('#ppPager').empty();
        if (last <= 1) { return; }

        function btn(label, p, opts) {
            opts = opts || {};
            return $('<button type="button" class="pp-page">').html(label).data('page', p)
                .toggleClass('active', !!opts.active).prop('disabled', !!opts.disabled);
        }

        $p.append(btn('<i class="fas fa-chevron-left"></i>', page - 1, { disabled: page === 1 }));

        var pages = [1], i;
        for (i = Math.max(2, page - 1); i <= Math.min(last - 1, page + 1); i++) { pages.push(i); }
        pages.push(last);
        var prev = 0;
        $.each(pages.filter(function (v, idx, a) { return a.indexOf(v) === idx; }), function (_, n) {
            if (n - prev > 1) { $p.append('<span class="pp-gap">…</span>'); }
            $p.append(btn(n, n, { active: n === page }));
            prev = n;
        });

        $p.append(btn('<i class="fas fa-chevron-right"></i>', page + 1, { disabled: page === last }));
    }

    function reload() { S.page = 1; load(); }

    /* ---------- categories ---------- */

    function loadCategories() {
        S.category = '';
        var $c = $('#ppCategory').html('<option value="">All Categories</option>');
        if (!S.lang) { return $.Deferred().resolve().promise(); }
        return $.getJSON(PP.catUrl + '/' + S.lang, function (list) {
            $.each(list, function (_, c) { $c.append($('<option>').val(c.id).text(c.title)); });
        });
    }

    /* ---------- filter events ---------- */

    $('#ppSearch').on('input', function () {
        var v = $.trim(this.value);
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { if (v !== S.q) { S.q = v; reload(); } }, 350);
    });

    $('#ppLang').on('change', function () {
        S.lang = this.value;
        loadCategories().always(reload);
    });
    $('#ppCategory').on('change', function () { S.category = this.value; reload(); });
    $('#ppType').on('change', function () { S.type = this.value; reload(); });
    $('#ppPerPage').on('change', function () { S.per_page = +this.value; reload(); });

    $('#ppTabs').on('click', '.pp-tab', function () {
        $('#ppTabs .pp-tab').removeClass('active');
        $(this).addClass('active');
        S.state = $(this).data('state') || '';
        reload();
    });

    $('#ppPager').on('click', '.pp-page:not(:disabled)', function () {
        S.page = $(this).data('page');
        load();
        $('html, body').animate({ scrollTop: $('.pp-card').offset().top - 80 }, 200);
    });

    $('#ppDate').daterangepicker({
        autoUpdateInput: false,
        opens: 'left',
        maxDate: moment(),
        showDropdowns: true,
        locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' },
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'This Year': [moment().startOf('year'), moment()],
            'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
        }
    }).on('apply.daterangepicker', function (e, picker) {
        S.from = picker.startDate.format('YYYY-MM-DD');
        S.to = picker.endDate.format('YYYY-MM-DD');
        $(this).val(S.from + '  →  ' + S.to);
        reload();
    }).on('cancel.daterangepicker', function () {
        S.from = S.to = '';
        $(this).val('');
        reload();
    });

    $('#ppReset').on('click', function () {
        S.q = S.type = S.state = S.from = S.to = '';
        $('#ppSearch').val('');
        $('#ppType').val('');
        $('#ppDate').val('');
        $('#ppTabs .pp-tab').removeClass('active').first().addClass('active');
        $('#ppCategory').val('');
        S.category = '';
        reload();
    });

    /* ---------- selection ---------- */

    $(document).on('change', '#headercheck', function () {
        $('.postCheck').prop('checked', this.checked);
        syncSelection();
    });
    $(document).on('change', '.postCheck', syncSelection);

    /* ---------- row actions ---------- */

    $(document).on('click', '.pp-more-toggle', function (e) {
        e.stopPropagation();
        var $m = $(this).closest('.pp-more'), wasOpen = $m.hasClass('open');
        $('.pp-more').removeClass('open up');
        if (!wasOpen) {
            $m.addClass('open');
            var menu = $m.find('.pp-menu')[0].getBoundingClientRect();
            if (menu.bottom > window.innerHeight - 10) { $m.addClass('up'); }
        }
    });
    $(document).on('click', function () { $('.pp-more').removeClass('open up'); });

    // single toggle (slider / breaking / feature / right / pending)
    $(document).on('click', '.pp-act', function (e) {
        e.preventDefault();
        $('.pp-more').removeClass('open up');
        $.ajax({ url: $(this).data('url'), dataType: 'json' })
            .done(function (r) { toast(r.message || 'Updated'); load(); })
            .fail(function () { toast('Action failed.', true); });
    });

    // single delete
    $(document).on('click', '.pp-del', function (e) {
        e.preventDefault();
        $('.pp-more').removeClass('open up');
        pendingDelete = { url: $(this).data('url') };
        $('#ppDeleteText').text('"' + $(this).data('title') + '" and its related data will be permanently removed.');
        $('#ppDeleteModal').modal('show');
    });

    // bulk delete
    $('#ppBulkDelete').on('click', function () {
        var ids = selectedIds();
        if (!ids.length) { return; }
        pendingDelete = { url: PP.bulkDeleteUrl, ids: ids };
        $('#ppDeleteText').text(ids.length + ' selected post' + (ids.length > 1 ? 's' : '') + ' and related data will be permanently removed.');
        $('#ppDeleteModal').modal('show');
    });

    $('#ppDeleteConfirm').on('click', function () {
        if (!pendingDelete) { return; }
        var job = pendingDelete, $b = $(this).prop('disabled', true);
        pendingDelete = null;
        sendInBatches(job.url, job.ids)
            .done(function (r) { toast(typeof r === 'string' ? r : (r.message || 'Deleted')); load(); })
            .fail(function () { toast('Delete failed.', true); load(); })
            .always(function () { $b.prop('disabled', false); $('#ppDeleteModal').modal('hide'); });
    });

    // bulk flag changes
    $('#ppBulk').on('click', '[data-bulk]', function () {
        var ids = selectedIds();
        if (!ids.length) { return; }
        sendInBatches(PP.bulkUrl + '/' + $(this).data('bulk'), ids)
            .done(function (r) { toast(r.message || 'Updated'); load(); })
            .fail(function () { toast('Bulk action failed.', true); load(); });
    });

    /* ---------- boot ---------- */

    loadCategories();
    load();

})(jQuery);
