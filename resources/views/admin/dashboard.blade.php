@extends('layouts.admin')

@section('content')
@php
    $admin   = Auth::guard('admin')->user();
    $isStaff = !in_array($admin->role->name, ['admin', 'moderator']);
    $isOwner = $admin->whereId(1)->exists();

    $stats = [
        ['label' => __('Post'),          'value' => $isStaff ? $author_post : $total_post,         'icon' => 'fas fa-newspaper',      'tone' => 'indigo', 'route' => 'post.index'],
        ['label' => __('Pending Post'),  'value' => $isStaff ? $author_pending : $pending_posts,   'icon' => 'fas fa-hourglass-half', 'tone' => 'amber',  'route' => 'pending.index'],
        ['label' => __('Draft'),         'value' => $drafts,                                       'icon' => 'fas fa-pen-nib',        'tone' => 'slate',  'route' => 'draft.index'],
        ['label' => __('Schedule Post'), 'value' => $schedules,                                    'icon' => 'fas fa-clock',          'tone' => 'sky',    'route' => 'schedule.index'],
        ['label' => __('Rss Feeds'),     'value' => $rss,                                          'icon' => 'fas fa-rss',            'tone' => 'rose',   'route' => 'rss.index'],
        ['label' => __('Polls'),         'value' => $polls,                                        'icon' => 'fas fa-poll',           'tone' => 'emerald','route' => 'addPolls.index'],
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

    {{-- Primary stats --}}
    <div class="dash-grid dash-grid-6">
        @foreach ($stats as $s)
            <a href="{{ route($s['route']) }}" class="dash-stat tone-{{ $s['tone'] }}">
                <span class="dash-stat-icon"><i class="{{ $s['icon'] }}"></i></span>
                <span class="dash-stat-body">
                    <span class="dash-stat-label">{{ $s['label'] }}</span>
                    <span class="dash-stat-value">{{ number_format($s['value']) }}</span>
                </span>
                <i class="fas fa-arrow-right dash-stat-go"></i>
            </a>
        @endforeach
    </div>

    @if ($isOwner)
        @php
            $newUsers   = App\Models\Admin::where('id', '!=', 1)->where('created_at', '>', Carbon\Carbon::now()->subDays(30))->count();
            $totalUsers = App\Models\Admin::where('id', '!=', 1)->count();
            $totalSubs  = App\Models\Subscriber::count();
            $totalFonts = App\Models\Font::count();
            $mini = [
                ['label' => __('New Users'),         'sub' => __('Last 30 Days'), 'value' => $newUsers,   'icon' => 'fas fa-user-plus',  'tone' => 'indigo'],
                ['label' => __('Total Users'),       'sub' => __('All Time'),     'value' => $totalUsers, 'icon' => 'fas fa-users',      'tone' => 'sky'],
                ['label' => __('Total Subscribers'), 'sub' => __('All Time'),     'value' => $totalSubs,  'icon' => 'fas fa-envelope',   'tone' => 'emerald'],
                ['label' => __('Total Fonts'),       'sub' => __('All Time'),     'value' => $totalFonts, 'icon' => 'fas fa-font',       'tone' => 'amber'],
            ];
        @endphp

        {{-- Secondary stats --}}
        <div class="dash-grid dash-grid-4">
            @foreach ($mini as $m)
                <div class="dash-mini tone-{{ $m['tone'] }}">
                    <span class="dash-mini-icon"><i class="{{ $m['icon'] }}"></i></span>
                    <div class="dash-mini-body">
                        <span class="dash-mini-label">{{ $m['label'] }}</span>
                        <span class="dash-mini-sub">{{ $m['sub'] }}</span>
                    </div>
                    <span class="dash-mini-value">{{ number_format($m['value']) }}</span>
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
                        <tbody>
                            @php $hasUsers = false; @endphp
                            @foreach ($userRole as $role)
                                @foreach ($role->users()->orderBy('id', 'desc')->take(10)->get() as $data)
                                    @php $hasUsers = true; @endphp
                                    <tr>
                                        <td>
                                            <span class="dash-avatar">{{ strtoupper(substr($data->email, 0, 1)) }}</span>
                                            <span class="dash-email">{{ $data->email }}</span>
                                        </td>
                                        <td class="text-right dash-muted">{{ optional($data->created_at)->format('d M Y') }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                            @unless ($hasUsers)
                                <tr><td colspan="2" class="dash-empty">{{ __('No users yet') }}</td></tr>
                            @endunless
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
                        <tbody>
                            @forelse ($subscribers as $data)
                                <tr>
                                    <td class="dash-muted">{{ $data->id }}</td>
                                    <td>
                                        <span class="dash-avatar">{{ strtoupper(substr($data->email, 0, 1)) }}</span>
                                        <span class="dash-email">{{ $data->email }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="dash-empty">{{ __('No subscribers yet') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection

@section('scripts')

@endsection
