@extends('layouts.admin')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.min.css">
<style>
.pp,.pp-modal,.pp-toast-box{--pp-bg:#f4f6fb;--pp-card:#fff;--pp-ink:#1b2140;--pp-mute:#7a829e;--pp-line:#e8ebf4;--pp-brand:#5b5bf0;--pp-brand-2:#8b5cf6;--pp-brand-soft:#eeeeff;--pp-ok:#12a76b;--pp-ok-soft:#e3f8ef;--pp-warn:#d98a06;--pp-warn-soft:#fff3d9;--pp-danger:#e5484d;--pp-danger-soft:#fdecec;--pp-radius:12px}
.pp{padding:14px;color:var(--pp-ink)}
.pp *{box-sizing:border-box}
.pp-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px}
.pp-head h2{font-size:19px;font-weight:700;margin:0;letter-spacing:-.4px}
.pp-btn-primary{display:inline-flex;align-items:center;gap:8px;padding:7px 14px;border:0;border-radius:9px;color:#fff!important;font-weight:600;font-size:13px;background:linear-gradient(135deg,var(--pp-brand),var(--pp-brand-2));box-shadow:0 8px 20px -6px rgba(91,91,240,.55);transition:transform .15s,box-shadow .15s;text-decoration:none!important}
.pp-btn-primary:hover{transform:translateY(-1px);box-shadow:0 12px 24px -6px rgba(91,91,240,.6)}
.pp-card{background:var(--pp-card);border:1px solid var(--pp-line);border-radius:var(--pp-radius);box-shadow:0 1px 2px rgba(27,33,64,.04),0 12px 32px -16px rgba(27,33,64,.12)}
.pp-toolbar{padding:10px 12px;border-bottom:1px solid var(--pp-line)}
.pp-toolbar-top{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:8px}
.pp-tabs{display:flex;gap:3px;flex-wrap:wrap;padding:3px;background:var(--pp-bg);border-radius:9px;width:max-content;max-width:100%}
.pp-tab{border:0;background:transparent;padding:4px 11px;border-radius:7px;font-size:12.5px;font-weight:600;color:var(--pp-mute);cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .15s}
.pp-tab b{font-size:11px;padding:1px 6px;border-radius:20px;background:#e3e6f2;color:var(--pp-mute);font-weight:700}
.pp-tab.active{background:#fff;color:var(--pp-ink);box-shadow:0 2px 8px rgba(27,33,64,.1)}
.pp-tab.active b{background:var(--pp-brand);color:#fff}
.pp-filters{display:grid;grid-template-columns:minmax(220px,2fr) repeat(3,minmax(120px,1fr)) minmax(200px,1.3fr);gap:8px}
.pp-field{position:relative}
.pp-field>i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--pp-mute);font-size:13px;pointer-events:none}
.pp-input,.pp-select{width:100%;height:34px;border:1px solid var(--pp-line);border-radius:8px;background:#fff;padding:0 11px;font-size:13px;color:var(--pp-ink);outline:0;transition:border-color .15s,box-shadow .15s;-webkit-appearance:none;appearance:none;display:block}
.pp-input.has-icon{padding-left:32px}
.pp-filters .pp-field{min-width:0;height:34px}
.pp-filters input.pp-input,.pp-filters select.pp-select{height:34px!important;min-height:34px!important;border:1px solid var(--pp-line)!important;border-radius:8px!important;background-color:#fff!important;padding-top:0!important;padding-bottom:0!important;box-sizing:border-box!important;margin:0!important;line-height:normal!important}
.pp-filters input.pp-input.has-icon{padding-left:32px!important}
.pp-filters .pp-field>i{top:50%!important;bottom:auto!important;left:11px!important;transform:translateY(-50%)!important;line-height:1!important;margin:0!important}
.pp-select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='none' stroke='%237a829e' stroke-width='1.8' d='M1 1.5l5 5 5-5'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 11px center;padding-right:28px}
.pp-input:focus,.pp-select:focus{border-color:var(--pp-brand);box-shadow:0 0 0 4px rgba(91,91,240,.12)}
.pp-clear{display:none;margin-left:auto;border:0;background:none;color:var(--pp-brand);font-weight:600;font-size:12.5px;cursor:pointer;padding:0}
.pp-clear.show{display:inline-block}
.pp-bulk{display:none;align-items:center;gap:6px;flex-wrap:wrap;padding:7px 12px;background:linear-gradient(135deg,#1b2140,#2d3470);color:#fff;border-bottom:1px solid var(--pp-line);position:sticky;top:0;z-index:5}
.pp-bulk.show{display:flex}
.pp-bulk-count{font-weight:700;font-size:13px;margin-right:6px}
.pp-bulk button{border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.1);color:#fff;border-radius:7px;padding:4px 9px;font-size:12px;font-weight:600;cursor:pointer;transition:background .15s}
.pp-bulk button:hover{background:rgba(255,255,255,.2)}
.pp-bulk .pp-bulk-del{background:var(--pp-danger);border-color:var(--pp-danger);margin-left:auto}
.pp-bulk .pp-bulk-del:hover{background:#cf3a3f}
.pp-table-wrap{overflow-x:auto;position:relative;min-height:200px}
.pp-table{width:100%;border-collapse:separate;border-spacing:0;margin:0}
.pp-table th{padding:8px 12px;font-size:11px;text-transform:uppercase;letter-spacing:.7px;font-weight:700;color:var(--pp-mute);background:#fafbfe;border-bottom:1px solid var(--pp-line);white-space:nowrap;text-align:left}
.pp-table td{padding:7px 12px;border-bottom:1px solid var(--pp-line);vertical-align:middle;font-size:12.5px;background:#fff}
.pp-table tbody tr{transition:background .12s}
.pp-table tbody tr:hover td{background:#fafbff}
.pp-table tbody tr.is-selected td{background:var(--pp-brand-soft)}
.pp-table tbody tr:last-child td{border-bottom:0}
.pp-col-check{width:40px}
.pp-check{position:relative;display:inline-block;width:17px;height:17px;margin:0;cursor:pointer;vertical-align:middle}
.pp-check input{position:absolute;opacity:0;inset:0;margin:0;cursor:pointer}
.pp-check span{position:absolute;inset:0;border:2px solid #cbd0e3;border-radius:5px;background:#fff;transition:all .15s}
.pp-check input:checked+span{background:var(--pp-brand);border-color:var(--pp-brand)}
.pp-check input:checked+span:after{content:"";position:absolute;left:4px;top:1px;width:5px;height:9px;border:solid #fff;border-width:0 2px 2px 0;transform:rotate(45deg)}
.pp-check input:focus-visible+span{box-shadow:0 0 0 4px rgba(91,91,240,.2)}
.pp-post{display:flex;align-items:center;gap:10px;min-width:300px}
.pp-thumb{width:52px;height:36px;object-fit:cover;border-radius:6px;background:var(--pp-bg);flex:none;border:1px solid var(--pp-line)}
.pp-post-body{min-width:0}
.pp-title{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;font-weight:600;font-size:13px;line-height:1.35;color:var(--pp-ink)!important;text-decoration:none!important;max-width:560px}
.pp-title:hover{color:var(--pp-brand)!important}
.pp-meta{display:flex;flex-wrap:wrap;gap:4px;margin-top:3px}
.pp-tag{font-size:10.5px;line-height:1.5;font-weight:600;padding:0 7px;border-radius:20px;background:var(--pp-bg);color:var(--pp-mute)}
.pp-tag-cat{background:var(--pp-brand-soft);color:var(--pp-brand)}
.pp-tag-lang{background:#e6f5fb;color:#1583a8}
.pp-tag-flag{background:#fff0e5;color:#d9650a}
.pp-author{display:flex;align-items:center;gap:7px;white-space:nowrap}
.pp-avatar{width:24px;height:24px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;background:linear-gradient(135deg,var(--pp-brand),var(--pp-brand-2))}
.pp-author-name{font-weight:500}
.pp-pill{display:inline-flex;align-items:center;gap:5px;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700}
.pp-pill:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
.pp-pill-ok{background:var(--pp-ok-soft);color:var(--pp-ok)}
.pp-pill-warn{background:var(--pp-warn-soft);color:var(--pp-warn)}
.pp-date{font-weight:600}.pp-time{color:var(--pp-mute);font-size:11px}
.pp-col-date{white-space:nowrap}
.pp-col-actions{width:110px}
.pp-actions{display:flex;align-items:center;justify-content:flex-end;gap:4px}
.pp-icon-btn{width:28px;height:28px;border-radius:7px;border:1px solid var(--pp-line);background:#fff;color:var(--pp-mute)!important;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .15s;text-decoration:none!important;font-size:12px;padding:0}
.pp-icon-btn:hover{background:var(--pp-brand-soft);border-color:var(--pp-brand-soft);color:var(--pp-brand)!important}
.pp-more{position:relative}
.pp-menu{display:none;position:absolute;right:0;top:32px;min-width:210px;background:#fff;border:1px solid var(--pp-line);border-radius:13px;box-shadow:0 18px 40px -12px rgba(27,33,64,.3);padding:6px;z-index:20}
.pp-more.open .pp-menu{display:block}
.pp-more.up .pp-menu{top:auto;bottom:32px}
.pp-menu a{display:flex;align-items:center;gap:9px;padding:6px 10px;border-radius:8px;font-size:12.5px;font-weight:500;color:var(--pp-ink)!important;text-decoration:none!important;white-space:nowrap}
.pp-menu a i{width:16px;text-align:center;color:var(--pp-mute);font-size:12px}
.pp-menu a:hover{background:var(--pp-bg)}
.pp-menu a.pp-danger,.pp-menu a.pp-danger i{color:var(--pp-danger)!important}
.pp-menu a.pp-danger:hover{background:var(--pp-danger-soft)}
.pp-menu-sep{height:1px;background:var(--pp-line);margin:5px 4px}
.pp-footer{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;padding:8px 12px;border-top:1px solid var(--pp-line)}
.pp-info{color:var(--pp-mute);font-size:12.5px}
.pp-pager{display:flex;gap:5px;flex-wrap:wrap}
.pp-page{min-width:29px;height:29px;padding:0 8px;border-radius:7px;border:1px solid var(--pp-line);background:#fff;color:var(--pp-ink);font-weight:600;font-size:12px;cursor:pointer;transition:all .15s}
.pp-page:hover:not(:disabled):not(.active){background:var(--pp-bg)}
.pp-page.active{background:linear-gradient(135deg,var(--pp-brand),var(--pp-brand-2));border-color:transparent;color:#fff;box-shadow:0 6px 14px -4px rgba(91,91,240,.5)}
.pp-page:disabled{opacity:.4;cursor:not-allowed}
.pp-gap{align-self:center;color:var(--pp-mute);padding:0 4px}
.pp-perpage{display:flex;align-items:center;gap:8px;color:var(--pp-mute);font-size:12.5px}
.pp-perpage .pp-select{width:80px;height:29px}
.pp-loading{position:absolute;inset:0;background:rgba(255,255,255,.7);backdrop-filter:blur(1px);display:none;align-items:flex-start;justify-content:center;padding-top:80px;z-index:3}
.pp-loading.show{display:flex}
.pp-spin{width:34px;height:34px;border:3px solid var(--pp-brand-soft);border-top-color:var(--pp-brand);border-radius:50%;animation:ppspin .7s linear infinite}
@keyframes ppspin{to{transform:rotate(360deg)}}
.pp-skel td{padding:14px 12px}
.pp-skel i{display:block;height:12px;border-radius:6px;background:linear-gradient(90deg,#eef0f7 25%,#f7f8fc 50%,#eef0f7 75%);background-size:200% 100%;animation:ppsh 1.2s infinite}
@keyframes ppsh{to{background-position:-200% 0}}
.pp-empty{text-align:center;padding:40px 20px}
.pp-empty-icon{width:68px;height:68px;margin:0 auto 14px;border-radius:50%;background:var(--pp-brand-soft);color:var(--pp-brand);display:flex;align-items:center;justify-content:center;font-size:26px}
.pp-empty h5{font-weight:700;margin:0 0 4px}.pp-empty p{color:var(--pp-mute);margin:0}
.pp-toast-box{position:fixed;right:22px;bottom:22px;z-index:99999;display:flex;flex-direction:column;gap:10px}
.pp-toast{background:#1b2140;color:#fff;padding:12px 18px;border-radius:12px;font-size:13.5px;font-weight:500;box-shadow:0 14px 30px -8px rgba(0,0,0,.4);display:flex;align-items:center;gap:10px;animation:ppin .25s ease}
.pp-toast.err{background:var(--pp-danger)}
@keyframes ppin{from{transform:translateY(12px);opacity:0}}
.pp-modal .modal-content{border:0;border-radius:18px;overflow:hidden;box-shadow:0 30px 60px -20px rgba(27,33,64,.5)}
.pp-modal-body{padding:34px 30px 8px;text-align:center}
.pp-modal-icon{width:64px;height:64px;border-radius:50%;margin:0 auto 16px;background:var(--pp-danger-soft);color:var(--pp-danger);display:flex;align-items:center;justify-content:center;font-size:24px}
.pp-modal-body h4{font-weight:700;margin:0 0 8px;color:var(--pp-ink)}
.pp-modal-body p{color:var(--pp-mute);margin:0;word-break:break-word}
.pp-modal-foot{display:flex;gap:10px;justify-content:center;padding:22px 30px 30px}
.pp-modal-foot button{border:0;border-radius:11px;padding:10px 24px;font-weight:600;font-size:14px;cursor:pointer}
.pp-modal-foot .cancel{background:var(--pp-bg);color:var(--pp-ink)}
.pp-modal-foot .confirm{background:var(--pp-danger);color:#fff}
.daterangepicker{border-radius:14px;border:1px solid #e8ebf4;box-shadow:0 18px 40px -12px rgba(27,33,64,.3)}
.daterangepicker td.active,.daterangepicker td.active:hover{background-color:#5b5bf0}
.daterangepicker .ranges li.active{background-color:#5b5bf0}
.daterangepicker .drp-buttons .btn-primary{background:#5b5bf0;border-color:#5b5bf0}
@media(max-width:1100px){.pp-filters{grid-template-columns:repeat(3,1fr)}.pp-filters .pp-field:first-child{grid-column:1/-1}}
@media(max-width:640px){.pp{padding:10px}.pp-filters{grid-template-columns:1fr 1fr}.pp-col-author{display:none}.pp-footer{justify-content:center}}
</style>
@endsection

@section('content')
<div class="content-area">
<div class="pp">

    <div class="pp-head">
        <div>
            <h2>{{ __('All Posts') }}</h2>
        </div>
        <a href="{{ route('admin.post.format') }}" class="pp-btn-primary"><i class="fas fa-plus"></i> {{ __('Add New Post') }}</a>
    </div>

    @include('includes.admin.form-success')
    @include('includes.admin.flash-message')

    <div class="pp-card">

        <div class="pp-toolbar">
            <div class="pp-toolbar-top">
                <div class="pp-tabs" id="ppTabs">
                    <button type="button" class="pp-tab active" data-state="">{{ __('All') }} <b id="cntAll">0</b></button>
                    <button type="button" class="pp-tab" data-state="approved">{{ __('Approved') }} <b id="cntApproved">0</b></button>
                    <button type="button" class="pp-tab" data-state="pending">{{ __('Pending') }} <b id="cntPending">0</b></button>
                </div>
                <button type="button" class="pp-clear" id="ppReset"><i class="fas fa-times"></i> {{ __('Clear filters') }}</button>
            </div>

            <div class="pp-filters">
                <div class="pp-field">
                    <i class="fas fa-search"></i>
                    <input type="text" id="ppSearch" class="pp-input has-icon" placeholder="{{ __('Search by title...') }}" autocomplete="off">
                </div>
                <div class="pp-field">
                    <select id="ppLang" class="pp-select" aria-label="{{ __('Language') }}">
                        @foreach ($languages as $language)
                            <option value="{{ $language->id }}" {{ $language->is_default == 1 ? 'selected' : '' }}>{{ $language->language }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pp-field">
                    <select id="ppCategory" class="pp-select" aria-label="{{ __('Category') }}">
                        <option value="">{{ __('All Categories') }}</option>
                    </select>
                </div>
                <div class="pp-field">
                    <select id="ppType" class="pp-select" aria-label="{{ __('Post Type') }}">
                        <option value="">{{ __('All Types') }}</option>
                        @foreach ($postTypes as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pp-field">
                    <i class="far fa-calendar-alt"></i>
                    <input type="text" id="ppDate" class="pp-input has-icon" placeholder="{{ __('All dates') }}" autocomplete="off" readonly>
                </div>
            </div>
        </div>

        {{-- bulk action bar (ids kept for compatibility) --}}
        <div class="pp-bulk selectPost" id="ppBulk">
            <span class="pp-bulk-count"><span id="ppSelCount">0</span> {{ __('selected') }}</span>
            <button type="button" data-bulk="sliderBulk"><i class="fa fa-plus"></i> {{ __('Slider') }}</button>
            <button type="button" data-bulk="breakingBulk"><i class="fa fa-plus"></i> {{ __('Breaking') }}</button>
            <button type="button" data-bulk="featureBulk"><i class="fa fa-plus"></i> {{ __('Feature') }}</button>
            <button type="button" data-bulk="rightBulk"><i class="fa fa-plus"></i> {{ __('Slider Right') }}</button>
            <button type="button" data-bulk="remove/sliderBulk"><i class="fa fa-minus"></i> {{ __('Slider') }}</button>
            <button type="button" data-bulk="remove/breakingBulk"><i class="fa fa-minus"></i> {{ __('Breaking') }}</button>
            <button type="button" data-bulk="remove/featureBulk"><i class="fa fa-minus"></i> {{ __('Feature') }}</button>
            <button type="button" data-bulk="remove/rightBulk"><i class="fa fa-minus"></i> {{ __('Slider Right') }}</button>
            <button type="button" class="pp-bulk-del" id="ppBulkDelete"><i class="fas fa-trash-alt"></i> {{ __('Delete') }}</button>
        </div>

        <div class="pp-table-wrap">
            <div class="pp-loading" id="ppLoading"><div class="pp-spin"></div></div>
            <table class="pp-table">
                <thead>
                    <tr>
                        <th class="pp-col-check"><label class="pp-check"><input type="checkbox" id="headercheck"><span></span></label></th>
                        <th>{{ __('Post') }}</th>
                        <th class="pp-col-author">{{ __('Author') }}</th>
                        <th class="pp-col-status">{{ __('Status') }}</th>
                        <th class="pp-col-date">{{ __('Created') }}</th>
                        <th style="text-align:right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody id="ppRows">
                    @for ($i = 0; $i < 6; $i++)
                        <tr class="pp-skel"><td><i style="width:18px"></i></td><td><i style="width:70%"></i></td><td class="pp-col-author"><i style="width:60%"></i></td><td><i style="width:60px"></i></td><td><i style="width:80px"></i></td><td><i style="width:70px;margin-left:auto"></i></td></tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <div class="pp-footer">
            <div class="pp-info" id="ppInfo"></div>
            <div class="pp-pager" id="ppPager"></div>
            <div class="pp-perpage">
                {{ __('Rows') }}
                <select id="ppPerPage" class="pp-select">
                    <option>10</option><option selected>25</option><option>50</option><option>100</option><option>200</option><option>500</option><option>1000</option>
                </select>
            </div>
        </div>
    </div>
</div>
</div>

{{-- DELETE CONFIRM --}}
<div class="modal fade pp-modal" id="ppDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:420px">
        <div class="modal-content">
            <div class="pp-modal-body">
                <div class="pp-modal-icon"><i class="fas fa-trash-alt"></i></div>
                <h4>{{ __('Delete post?') }}</h4>
                <p id="ppDeleteText"></p>
            </div>
            <div class="pp-modal-foot">
                <button type="button" class="cancel" data-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="confirm" id="ppDeleteConfirm">{{ __('Yes, delete') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="pp-toast-box" id="ppToasts"></div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.min.js"></script>
<script>
window.PP = {
    listUrl: @json(route('post.list')),
    catUrl: @json(url('admin/post/categories')),
    bulkUrl: @json(url('admin/post')),
    bulkDeleteUrl: @json(route('post.bulkdelete'))
};
</script>
<script src="{{ asset('assets/admin/js/post-list.js') }}?v={{ filemtime(public_path('assets/admin/js/post-list.js')) }}"></script>
@endsection
