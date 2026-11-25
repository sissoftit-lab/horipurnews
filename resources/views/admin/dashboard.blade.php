@extends('layouts.admin')

@section('content')
@php
    $admin   = Auth::guard('admin')->user();

    // key = field name in the AJAX JSON response (see DashboardController@data)
    $stats = [
        ['key' => 'post',     'label' => __('Post'),          'icon' => 'fas fa-newspaper',      'tone' => 'indigo',  'route' => 'post.index'],
        ['key' => 'pending',  'label' => __('Pending Post'),  'icon' => 'fas fa-hourglass-half', 'tone' => 'amber',   'route' => 'pending.index'],
        ['key' => 'draft',    'label' => __('Draft'),         'icon' => 'fas fa-pen-nib',        'tone' => 'slate',   'route' => 'draft.index'],
        ['key' => 'schedule', 'label' => __('Schedule Post'), 'icon' => 'fas fa-clock',          'tone' => 'sky',     'route' => 'schedule.index'],
        ['key' => 'rss',      'label' => __('Rss Feeds'),     'icon' => 'fas fa-rss',            'tone' => 'rose',    'route' => 'rss.index'],
        ['key' => 'polls',    'label' => __('Polls'),         'icon' => 'fas fa-poll',           'tone' => 'emerald', 'route' => 'addPolls.index'],
    ];

    $mini = [
        ['key' => 'new_users',   'label' => __('New Users'),         'sub' => __('Last 30 Days'), 'icon' => 'fas fa-user-plus', 'tone' => 'indigo'],
        ['key' => 'total_users', 'label' => __('Total Users'),       'sub' => __('All Time'),     'icon' => 'fas fa-users',     'tone' => 'sky'],
        ['key' => 'total_subs',  'label' => __('Total Subscribers'), 'sub' => __('All Time'),     'icon' => 'fas fa-envelope',  'tone' => 'emerald'],
        ['key' => 'total_fonts', 'label' => __('Total Fonts'),       'sub' => __('All Time'),     'icon' => 'fas fa-font',      'tone' => 'amber'],
    ];
@endphp

<div class="content-area dash">

    {{-- Greeting --}}
    <div class="dash-hero">
        <div>
            <h4 class="dash-hero-title">{{ __('Welcome back') }}, {{ $admin->name ?? __('Admin') }}</h4>
            <p class="dash-hero-sub">{{ __("Here's what's happening with your newsroom today.") }}</p>
        </div>
        <div class="dash-hero-date"><i class="far fa-calendar-alt"></i> {{ now()->format('D, d M Y') }}</div>
    </div>

    <div id="dash-error" class="alert alert-danger" style="display:none">
        {{ __('Could not load dashboard data.') }}
        <a href="#" id="dash-retry">{{ __('Retry') }}</a>
    </div>

    {{-- Primary stats --}}
    <div class="dash-grid dash-grid-6">
        @foreach ($stats as $s)
            <a href="{{ route($s['route']) }}" class="dash-stat tone-{{ $s['tone'] }}">
                <span class="dash-stat-icon"><i class="{{ $s['icon'] }}"></i></span>
                <span class="dash-stat-body">
                    <span class="dash-stat-label">{{ $s['label'] }}</span>
                    <span class="dash-stat-value" data-stat="{{ $s['key'] }}">…</span>
                </span>
                <i class="fas fa-arrow-right dash-stat-go"></i>
            </a>
        @endforeach
    </div>

    {{-- Owner-only sections: hidden until the AJAX response says this admin is the owner --}}
    <div id="dash-owner" style="display:none">

        {{-- Secondary stats --}}
        <div class="dash-grid dash-grid-4">
            @foreach ($mini as $m)
                <div class="dash-mini tone-{{ $m['tone'] }}">
                    <span class="dash-mini-icon"><i class="{{ $m['icon'] }}"></i></span>
                    <div class="dash-mini-body">
                        <span class="dash-mini-label">{{ $m['label'] }}</span>
                        <span class="dash-mini-sub">{{ $m['sub'] }}</span>
                    </div>
                    <span class="dash-mini-value" data-stat="{{ $m['key'] }}">…</span>
                </div>
            @endforeach
        </div>

        {{-- Tables --}}
        <div class="dash-grid dash-grid-2">
            <div class="dash-panel">
                <div class="dash-panel-head">
                    <h5><i class="fas fa-user-clock"></i> {{ __('Recent User(s)') }}</h5>
                </div>
                <div class="dash-panel-body">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>{{ __('Email') }}</th>
                                <th class="text-right">{{ __('Joined') }}</th>
                            </tr>
                        </thead>
                        <tbody id="dash-users">
                            <tr><td colspan="2" class="dash-empty">{{ __('Loading...') }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dash-panel">
                <div class="dash-panel-head">
                    <h5><i class="fas fa-envelope-open-text"></i> {{ __('Subscribers') }}</h5>
                </div>
                <div class="dash-panel-body">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th style="width:60px">{{ __('#Sl') }}</th>
                                <th>{{ __('Email') }}</th>
                            </tr>
                        </thead>
                        <tbody id="dash-subs">
                            <tr><td colspan="2" class="dash-empty">{{ __('Loading...') }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
(function ($) {
    var url = "{{ route('admin.dashboard.data') }}";
    var txt = {
        noUsers: @json(__('No users yet')),
        noSubs:  @json(__('No subscribers yet'))
    };

    function fmt(n) { return Number(n || 0).toLocaleString(); }

    function avatar(email) {
        return $('<span class="dash-avatar">').text(String(email).charAt(0).toUpperCase());
    }

    function emptyRow(msg) {
        return $('<tr>').append($('<td colspan="2" class="dash-empty">').text(msg));
    }

    function render(d) {
        $('[data-stat]').each(function () {
            var key = $(this).data('stat');
            if (d[key] !== undefined) { $(this).text(fmt(d[key])); }
        });

        if (!d.is_owner) { return; }
        $('#dash-owner').show();

        var $users = $('#dash-users').empty();
        if (!d.recent_users.length) { $users.append(emptyRow(txt.noUsers)); }
        $.each(d.recent_users, function (_, u) {
            $users.append($('<tr>')
                .append($('<td>').append(avatar(u.email), ' ', $('<span class="dash-email">').text(u.email)))
                .append($('<td class="text-right dash-muted">').text(u.joined || '')));
        });

        var $subs = $('#dash-subs').empty();
        if (!d.subscribers.length) { $subs.append(emptyRow(txt.noSubs)); }
        $.each(d.subscribers, function (_, s) {
            $subs.append($('<tr>')
                .append($('<td class="dash-muted">').text(s.id))
                .append($('<td>').append(avatar(s.email), ' ', $('<span class="dash-email">').text(s.email))));
        });
    }

    function load() {
        $('#dash-error').hide();
        $.ajax({ url: url, type: 'GET', dataType: 'json' })
            .done(render)
            .fail(function () {
                $('[data-stat]').text('–');
                $('#dash-error').show();
            });
    }

    $('#dash-retry').on('click', function (e) { e.preventDefault(); load(); });
    $(load);
})(jQuery);
</script>
@endsection
