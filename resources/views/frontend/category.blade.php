@extends('layouts.front')

@push('css')
    <style>
        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
        }
        
        .pagination .page-item {
            margin: 0 5px;
        }
        
        .pagination .page-link {
            padding: 8px 12px;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }
        
        .pagination .page-item.disabled .page-link {
            color: #aaa;
            pointer-events: none;
            background-color: #f5f5f5;
        }
    </style>
    <style>
        @media (max-width: 576px) {
            .pagination .page-link {
                padding: 0.4rem 0.5rem;
                font-size: 14px;
            }
        }
        
    </style>
    <style>
        .custom-tab {
            position: relative;
            list-style: none;
            margin: 0;
            padding: 0;
            width: 100%;
        }
    
        .tab-link {
            display: inline-block;
            background: #196cad;
            color: white;
            font-size: 20px !important;
            padding: 8px 20px;
            position: relative;
            font-family: 'SolaimanLipi', 'Siyam Rupali', sans-serif;
            text-decoration: none;
            margin-left: 0;
        }
    
        .tab-link::after {
            content: "";
            position: absolute;
            top: 0;
            right: -25px;
            width: 0;
            height: 0;
            border-top: 35px solid transparent;
            border-left: 25px solid {{ $data->color ?? '#0b9d4c' }};
        }
    
        .custom-tab::after {
            content: "";
            display: block;
            height: 3px;
            background-color: red;
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
        }
    </style>
    <style>
    .full-width{
        width: 90%;
    }
        @media (max-width: 767px) {
            .mobile-post {
                display: flex;
                gap: 10px;
                margin-bottom: 15px;
                align-items: flex-start;
            }
    
            .mobile-post img {
                width: 40%;
                height: auto;
                object-fit: cover;
            }
    
            .mobile-post .text-content {
                width: 60%;
            }
    
            .mobile-post h5 {
                font-size: 15px;
                font-weight: bold;
                margin-bottom: 5px;
            }
    
            .mobile-post .meta {
                font-size: 12px;
                color: #666;
            }
    
            .mobile-full-post img {
                width: 100%;
                height: auto;
                object-fit: cover;
                margin-bottom: 10px;
            }
    
            .mobile-full-post h4 {
                font-size: 18px;
                font-weight: bold;
                margin-bottom: 5px;
            }
    
            .mobile-full-post .meta {
                font-size: 13px;
                color: #888;
            }
            .responsive-title {
                font-size: 16px; /* or whatever you prefer */
            }
            .full-width{
                width: 100%;
            }
        }
        .main-image {
            padding-left: 0 !important;
        }
        .feature-news-slider {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .feature-news-slider .item {
            flex: 1 1 calc(50% - 20px); 
        }
    </style>
    
    

    
@endpush

@section('contents')
    	<!-- Breadcrumb Area Start -->
	<div class="breadcrumb-area">
        <div class="container-fluid full-width" >
            <div class="row">
                <div class="col-lg-12">
                    <ul class="pages">
                        <!--<li>-->
                        <!--    <a href="#">-->
                        <!--        {{__('Home')}}-->
                        <!--    </a>-->
                        <!--</li>-->
                        <!--<li>-->
                        <!--    <a href="#">-->
                        <!--        {{__('News')}}-->
                        <!--    </a>-->
                        <!--</li>-->
                        <!--<li class="active " >-->
                        <!--    <a href="#" style="color :{{$data->color}}; ">-->
                        <!--        {{$data->title}}-->
                        <!--    </a>-->
                        <!--</li>-->
                        <li class="custom-tab">
                            <a href="#" class="tab-link">
                                {{ $data->title }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
</div>
<!-- Breadcrumb Area Start -->

<!-- Categori Page Start -->
<section class="categori-page-news">
    <div class="container-fluid full-width" >
        <div class="row">
            <div class="col-lg-8">
                <div class="categoti-content-area">
                    <!--<div class="row">-->
                    <!--    @if ($posts->count() > 0)-->
                    <!--        @foreach ($posts as $index => $post)-->
                    <!--            <div class="col-md-6 mycol {{ $index > 0 ? 'mobile-horizontal' : '' }}">-->
                    <!--                <div class="single-news animation">-->
                    <!--                    <a href="{{ route('frontend.productDetails', [$post->id]) }}">-->
                    <!--                        <div class="content-wrapper">-->
                    <!--                            <div class="image-wrapper">-->
                    <!--                                @if ($post->image_big || $post->rss_image)-->
                    <!--                                    @if ($post->image_big)-->
                    <!--                                        <img src="{{ asset('assets/images/post/' . $post->image_big) }}" alt="">-->
                    <!--                                    @else-->
                    <!--                                        <img src="{{ $post->rss_image }}" alt="">-->
                    <!--                                    @endif-->
                    <!--                                @else-->
                    <!--                                    <img src="{{ asset('assets/images/nopic.png') }}" alt="">-->
                    <!--                                @endif-->
                    <!--                            </div>-->
                    
                    <!--                            <div class="inner-content">-->
                    <!--                                <h4 class="title">-->
                    <!--                                    {{ strlen($post->title) > 50 ? mb_substr($post->title, 0, 50, 'utf-8') . '...' : $post->title }}-->
                    <!--                                </h4>-->
                    <!--                                <ul class="post-meta">-->
                    <!--                                    <li>-->
                    <!--                                        <a href="{{ route('frontend.postByDate') . '?date=' . $post->created_at->format('Y-m-d') }}">-->
                    <!--                                            {{ $post->createdAt() }}-->
                    <!--                                        </a>-->
                    <!--                                    </li>-->
                    <!--                                    <li><span>|</span></li>-->
                    <!--                                    <li>-->
                    <!--                                        <a href="#">-->
                    <!--                                            @if ($post->admin_id == 0 && $post->user_id != 0)-->
                    <!--                                                {{ $post->user->name }}-->
                    <!--                                            @else-->
                    <!--                                                {{ $post->admin->name }}-->
                    <!--                                            @endif-->
                    <!--                                        </a>-->
                    <!--                                    </li>-->
                    <!--                                </ul>-->
                    <!--                            </div>-->
                    <!--                        </div>-->
                    <!--                    </a>-->
                    <!--                </div>-->
                    <!--            </div>-->
                    <!--        @endforeach-->
                    <!--    @else-->
                    <!--        <div class="col-lg-12">-->
                    <!--            <div class="card">-->
                    <!--                <div class="card-body">-->
                    <!--                    <p class="text-danger text-center">{{ __('This Category has no news.') }}</p>-->
                    <!--                </div>-->
                    <!--            </div>-->
                    <!--        </div>-->
                    <!--    @endif-->
                    <!--</div>-->
                    
                    
                        <div class="row " >
                            @if ($posts->count() > 0)
                                @foreach ($posts as $key => $post)
                                    @if ($key == 0)
                                        
                                        <div class="col-md-12 main-image" >
                                            <div class="single-news">
                                                <a href="{{ route('frontend.productDetails2',[$post->category->slug??'cat',$post->id])}}">
                                                    
                                                    <img src="{{ $post->image_big ? asset('assets/images/post/'.$post->image_big) : ($post->rss_image ?? asset('assets/images/nopic.png')) }}" alt="{{$post->alt_tag}}"
                                                    style="width: 100%; height: auto;">
                                                    <p class="mt-2 responsive-title" style="font-weight: 700 !important; font-size: 18px !important;">
                                                        {{ $post->title }}
                                                    </p>
                                                    <p>
                                                        {{$post->date ? \Carbon\Carbon::parse($post->date)->format('M d, Y') : $post->created_at->format('M d, Y')}}
                                                    </p>
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        
                                        <div class="col-md-6 main-image">
                                            <!-- Desktop layout (normal) -->
                                            <div class="news-item d-none d-md-block">
                                                <a href="{{ route('frontend.productDetails2', [$post->category->slug??'cat',$post->id]) }}">
                                                    <img src="{{ $post->image_big ? asset('assets/images/post/'.$post->image_big) : ($post->rss_image ?? asset('assets/images/nopic.png')) }}" alt="{{$post->alt_tag}}">
                                                    <div class="text-content pt-2">
                                                        <p style="font-weight: 700 !important; font-size: 16px !important;">
                                                            {{ strlen($post->title) > 60 ? mb_substr($post->title, 0, 60, 'utf-8').'...' : $post->title }}
                                                        </p>

                                                        <p class="text-muted" style="font-size: 13px;">
                                                            {{$post->date ? \Carbon\Carbon::parse($post->date)->format('M d, Y') : $post->created_at->format('M d, Y')}}
                                                        </p>
                                                    </div>

                                                </a>
                                            </div>

                                            <!-- Mobile layout (image left, text right) -->
                                            <div class="news-item d-block d-md-none">
                                                <a href="{{ route('frontend.productDetails2', [$post->category->slug??'cat',$post->id]) }}">
                                                    <div class="d-flex align-items-start mt-2">
                                                        <div class="me-2 flex-shrink-0" style="width: 100px;">
                                                            <img class="img-fluid" src="{{ $post->image_big ? asset('assets/images/post/'.$post->image_big) : ($post->rss_image ?? asset('assets/images/nopic.png')) }}" alt="{{$post->alt_tag}}">
                                                        </div>
                                                        <div class="text-content mx-2">
                                                            <p class="mb-1" style="font-size: 15px;">
                                                                {{ strlen($post->title) > 50 ? mb_substr($post->title, 0, 50, 'utf-8').'...' : $post->title }}
                                                            </p>
                                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                                {{$post->date ? \Carbon\Carbon::parse($post->date)->format('M d, Y') : $post->created_at->format('M d, Y')}}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>

                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="col-12">
                                    <div class="alert alert-warning text-center">
                                        {{ __('This Category has no news.') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    
                    <!--<div class="mt-3">{{ $posts->links() }}</div>-->
                    
                    <div class="row">
                        @if ($posts->hasPages())
                            @php
                                $maxPage = 6;
                                $lastPage = $posts->lastPage();
                                $currentPage = $posts->currentPage();
                                $pageRange = $posts->getUrlRange(1, $lastPage > $maxPage ? $maxPage : $lastPage);
                            @endphp
                        
                            <div class="col-lg-12 mt-4 text-center">
                                <ul class="pagination justify-content-center">
                        
                                    {{-- Previous Page Link --}}
                                    @if ($posts->onFirstPage())
                                        <li class="page-item disabled"><span class="page-link">«</span></li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $posts->previousPageUrl() }}" rel="prev">«</a>
                                        </li>
                                    @endif
                        
                                    {{-- Page Links: 1 to 6 --}}
                                    @foreach ($pageRange as $page => $url)
                                        @if ($page == $currentPage)
                                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                        @else
                                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                        @endif
                                    @endforeach
                        
                                    {{-- Show ellipsis and last page if needed --}}
                                    @if ($lastPage > $maxPage)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $posts->url($lastPage) }}">{{ $lastPage }}</a>
                                        </li>
                                    @endif
                        
                                    {{-- Next Page Link --}}
                                    @if ($posts->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $posts->nextPageUrl() }}" rel="next">»</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled"><span class="page-link">»</span></li>
                                    @endif
                        
                                </ul>
                            </div>
                        @endif

                    </div>


                    
   
                </div>
            </div>
            <div class="col-lg-4 aside">
                <div class="categori-widget-area">
                    <div class="header-area">
                        <h4 class="title">
                            {{__('CATEGORIES')}}
                        </h4>
                    </div>
                    <ul class="categori-list">
                        @foreach ($categories as $category)
                            <li>
                                <a href="{{ route('frontend.category',$category->slug)}}">
                                    <span>
                                        {{$category->title}}
                                    </span>
                                    <span>
                                        ({{cat_post_count($category->id)}})
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="aside-newsletter-widget mt-3 subarea">
                    <h4 class="title">{{__('Newsletter')}}</h4>
                    <p class="text">{{__('Subscribe to our newsletter to stay.')}}</p>
                    <form action="{{ route('front.subscribers.store') }}" class="subscribe-form" method="POST" id="subForm">
                        @csrf
                        <input type="text" placeholder="{{__('Enter Your Email Address')}}" name="email" class="subEmail">
                        <button type="submit" class="submit subBtn">{{__('Subscribe')}}</button>
                    </form>
                </div>
                <div class="celander-widget-area mt-4">
                        <div id="datecalender">
                        </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- More News Area End -->
@endsection

@push('js')
<script src="{{asset('assets/front/js/notify.min.js')}}"></script>
<script src="{{asset('assets/front/js/rfront.js')}}"></script>

@endpush
