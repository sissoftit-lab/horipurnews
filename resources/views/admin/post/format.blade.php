@extends('layouts.admin')

@section('styles')
<style>
.pc{--pc-ink:#1b2140;--pc-mute:#7a829e;--pc-line:#e6e9f3;--pc-brand:#5b5bf0;--pc-brand-2:#8b5cf6;padding:14px;color:var(--pc-ink)}
.pc-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:16px}
.pc-head h2{font-size:21px;font-weight:700;letter-spacing:-.5px;margin:0}
.pc-head p{margin:2px 0 0;color:var(--pc-mute);font-size:13px}
.pc-back{display:inline-flex;align-items:center;gap:6px;padding:7px 13px;border-radius:9px;border:1px solid var(--pc-line);background:#fff;color:var(--pc-ink)!important;font-weight:600;font-size:12.5px;text-decoration:none!important;transition:all .15s}
.pc-back:hover{border-color:var(--pc-brand);color:var(--pc-brand)!important}
.pc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:12px}
.pc-card{--c:#5b5bf0;--c2:#8b5cf6;position:relative;display:flex;align-items:center;gap:12px;flex-direction:row;padding:14px 16px;background:#fff;border:1px solid var(--pc-line);border-radius:12px;text-decoration:none!important;color:var(--pc-ink)!important;overflow:hidden;
  box-shadow:0 1px 2px rgba(27,33,64,.04),0 14px 34px -20px rgba(27,33,64,.18);transition:transform .2s,box-shadow .2s,border-color .2s}
.pc-card:before{content:"";position:absolute;right:-50px;top:-50px;width:150px;height:150px;border-radius:50%;background:linear-gradient(135deg,var(--c),var(--c2));opacity:.09;transition:transform .3s,opacity .2s}
.pc-card:hover{transform:translateY(-2px);border-color:transparent;box-shadow:0 22px 44px -18px color-mix(in srgb,var(--c) 55%,transparent)}
.pc-card:hover:before{transform:scale(1.6);opacity:.14}
.pc-icon{width:42px;height:42px;flex:none;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:17px;color:#fff;background:linear-gradient(135deg,var(--c),var(--c2));box-shadow:0 12px 22px -10px var(--c);position:relative}
.pc-card h6{font-size:14.5px;font-weight:700;margin:0 0 2px;position:relative}
.pc-card p{margin:0;color:var(--pc-mute);font-size:12px;line-height:1.4;position:relative}
.pc-go{display:none}.pc-go-x{display:inline-flex;align-items:center;gap:8px;font-weight:600;font-size:13px;color:var(--c);position:relative}
.pc-go i{transition:transform .2s}
.pc-card:hover .pc-go i{transform:translateX(5px)}
@media(max-width:640px){.pc-head h2{font-size:19px}}
</style>
@endsection

@section('content')
<div class="content-area">
<div class="pc">

    <div class="pc-head">
        <div>
            <h2>{{ __('Create a new post') }}</h2>
            <p>{{ __('Pick the format that fits your story.') }}</p>
        </div>
        <a href="{{ route('post.index') }}" class="pc-back"><i class="fas fa-list"></i> {{ __('All Posts') }}</a>
    </div>

    @php
        $formats = [
            ['route' => route('article.create'),         'icon' => 'far fa-file-alt',   'title' => __('Article'),          'desc' => __('An article with images'),                 'c' => '#5b5bf0', 'c2' => '#8b5cf6'],
            ['route' => route('image.gallery.create'),   'icon' => 'far fa-images',     'title' => __('Gallery'),          'desc' => __('A collection of images'),                 'c' => '#0ea5e9', 'c2' => '#38bdf8'],
            ['route' => route('shortlist.create'),       'icon' => 'fas fa-list-ul',    'title' => __('Sorted List'),      'desc' => __('A list based article'),                   'c' => '#f59e0b', 'c2' => '#fb923c'],
            ['route' => route('tquiz.create'),           'icon' => 'fas fa-list-ol',    'title' => __('Trivia Quiz'),      'desc' => __('Quizzes with right and wrong answers'),   'c' => '#10b981', 'c2' => '#34d399'],
            ['route' => route('pquiz.create'),           'icon' => 'fas fa-horse-head', 'title' => __('Personality Quiz'), 'desc' => __('Quizzes with custom results'),             'c' => '#ec4899', 'c2' => '#f472b6'],
            ['route' => route('video.create'),           'icon' => 'fas fa-film',       'title' => __('Video'),            'desc' => __('Upload or embed videos'),                 'c' => '#ef4444', 'c2' => '#f97316'],
            ['route' => route('audio.create'),           'icon' => 'fas fa-volume-up',  'title' => __('Audio'),            'desc' => __('Upload audios'),                          'c' => '#8b5cf6', 'c2' => '#6366f1'],
        ];
    @endphp

    <div class="pc-grid">
        @foreach ($formats as $f)
            <a href="{{ $f['route'] }}" class="pc-card" style="--c:{{ $f['c'] }};--c2:{{ $f['c2'] }}">
                <div class="pc-icon"><i class="{{ $f['icon'] }}"></i></div>
                <div style="min-width:0;position:relative"><h6>{{ $f["title"] }}</h6>
                <p>{{ $f["desc"] }}</p></div>
            </a>
        @endforeach
    </div>
</div>
</div>
@endsection
