@extends('layouts.front')

@push('css')
    <style>
        .js-cookie-consent{
            position: fixed;
            bottom: 0px;
            padding: 14px;
            color  : #fff;
            text-align: center;
            width: 100%;
            z-index: 10000000000;
            background-color: #8e44ad;
            border-color: #8e44ad;
        }
        .full-width{
            width: 90%;
        }
        

            
            
        @media (min-width: 1200px) {
            .container {
                max-width: 1300px;
            }
        }
        @media (max-width: 768px){
            .full-width{
                width: 100%;
            }
        }
        .full-width .sub-full-width{
            padding-left: 0px;
            padding-right: 0px;
        }
    </style>
    

@endpush

@section('contents')

<!-- Top Header Area Start -->
<section class="top-header">
    <div class="container-fluid full-width" >
        <div class="row">
            <div class="col-lg-12">
                <div class="content">
                    <div class="left-content">
                        <div class="heading">
                            <span>{{__('Trending Now!')}} </span>
                        </div>
                        <div class='marquee'>
                            <!--<marquee onMouseOver="this.stop()" onMouseOut="this.start()" scrollamount="5">{!!$is_breaking!!}</marquee>-->
                            <marquee onMouseOver="this.stop()" onMouseOut="this.start()" scrollamount="5">
                                {!! $is_breaking !!}
                            </marquee>
                        </div>
                    </div>
                    <div class="right-content">
                        <span class="date-now">{{\Carbon\Carbon::now()->toFormattedDateString()}}<span class="time-now"></span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Top Header Area End -->

<!-- Hero Area Start -->
<section class="hero-area">
    <div class="container-fluid full-width" >
        <div class="row py">
            <div class="col-lg-6 r-p">
                <div class="intro-carousel">
                    @foreach ($sliders as $slider)
                        <a href="{{ route('frontend.productDetails2',[$slider->category->slug??'cat',$slider->id])}}" class="single-news big">
                            
                            <div class="content-wrapper">
                                @if ($slider->image_big || $slider->rss_image)
                                    <div class="tag" style="background:{{$slider->category->color}}">
                                        {{$slider->category->title}}
                                    </div>
                                @endif
                                @if ($slider->image_big || $slider->rss_image)
                                    @if ($slider->image_big)
                                        <img src="{{asset('assets/images/post/'.$slider->image_big)}}"  alt="{{$slider->alt_tag}}">
                                    @endif

                                    @if ($slider->rss_image)
                                        <img src="{{$slider->rss_image}}" alt="{{$slider->alt_tag}}">
                                    @endif

                                    @if ($slider->post_type == 'audio')
                                        <span  class="vid-aud">
                                            <i class="fas fa-volume-up"></i>
                                        </span>
                                    @endif
                                    @if ($slider->post_type == 'video')
                                        <span  class="vid-aud">
                                            <i class="fas fa-video"></i>
                                        </span>
                                    @endif
                                @else
                                    <img src="{{asset('assets/images/nopic.png')}}" alt="{{$slider->alt_tag}}">
                                @endif
                                <div class="inner-content">
                                    <span>
                                        <h4 class="title">
                                            {{strlen($slider->title)>40 ? mb_substr($slider->title,0,40,"utf-8") : $slider->title}}
                                        </h4>
                                    </span>
                                    <ul class="post-meta">
                                        <li>
                                            <span>
                                                {{ $slider->date ? \Carbon\Carbon::parse($slider->date)->format('M d, Y') : $slider->created_at->format('M d, Y') }}
                                            </span>

                                        </li>
                                        <!--<li>-->
                                        <!--    <span>|</span>-->
                                        <!--</li>-->
                                        <!--<li>-->
                                        <!--    <span>-->
                                        <!--        @if ($slider->admin_id == 0 && $slider->user_id != 0)-->
                                        <!--            {{ $slider->user->name }}-->
                                        <!--        @else-->
                                        <!--            {{$slider->admin->name}}-->
                                        <!--        @endif-->
                                        <!--    </span>-->
                                        <!--</li>-->
                                    </ul>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-3 r-p mycol">
                @foreach ($slider_rights_firsts as $slider_rights_first)
                    <a href="{{ route('frontend.productDetails2',[$slider_rights_first->category->slug??'cat',$slider_rights_first->id])}}" class="single-news animation">
                        <div class="content-wrapper">
                            @if ($slider_rights_first->image_big || $slider_rights_first->rss_image)
                                <div class="tag" style="background:{{$slider_rights_first->category->color}}">
                                    {{$slider_rights_first->category->title}}
                                </div>
                            @endif
                            @if ($slider_rights_first->image_big || $slider_rights_first->rss_image)
                                @if ($slider_rights_first->image_big)
                                    <img data-src="{{asset('assets/images/post/'.$slider_rights_first->image_big)}}" alt="{{$slider_rights_first->alt_tag}}" class="lazy">
                                @endif
                                @if ($slider_rights_first->rss_image)
                                    <img data-src="{{$slider_rights_first->rss_image}}" alt="{{$slider_rights_first->alt_tag}}" class="lazy">
                                @endif
                                @if ($slider_rights_first->post_type == 'audio')
                                    <span  class="vid-aud">
                                        <i class="fas fa-volume-up"></i>
                                    </span>
                                @endif
                                @if ($slider_rights_first->post_type == 'video')
                                    <span  class="vid-aud">
                                        <i class="fas fa-video"></i>
                                    </span>
                                @endif
                            @else
                                    <img loading="lazy" decoding="async" src="{{asset('assets/images/nopic.png')}}" alt="{{$slider_rights_first->alt_tag}}">
                            @endif
                            <div class="inner-content">
                                <span>
                                    <h4 class="title">
                                        {{strlen($slider_rights_first->title)>40 ? mb_substr($slider_rights_first->title,0,40,"utf-8") : $slider_rights_first->title}}
                                    </h4>
                                </span>
                                <ul class="post-meta">
                                    <li>
                                        <span>
                                            {{$slider_rights_first->date ? \Carbon\Carbon::parse($slider_rights_first->date)->format('M d, Y') : $slider_rights_first->created_at->format('M d, Y')}}
                                        </span>
                                    </li>
                                    <!--<li>-->
                                    <!--    <span>|</span>-->
                                    <!--</li>-->
                                    <!--<li>-->
                                    <!--    <span>-->
                                    <!--        @if ($slider_rights_first->admin_id == 0 && $slider_rights_first->user_id != 0)-->
                                    <!--            {{ $slider_rights_first->user->name }}-->
                                    <!--        @else-->
                                    <!--            {{$slider_rights_first->admin->name}}-->
                                    <!--        @endif-->
                                    <!--    </span>-->
                                    <!--</li>-->
                                </ul>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="col-lg-3 r-p mycol">
                @foreach ($slider_rights_seconds as $slider_rights_second)
                    <a href="{{ route('frontend.productDetails2',[$slider_rights_second->category->slug??'cat',$slider_rights_second->id])}}" class="single-news animation">
                        <div class="content-wrapper">
                            @if ($slider_rights_second->image_big || $slider_rights_second->rss_image)
                                <div class="tag" style="background:{{$slider_rights_second->category->color}}">
                                    {{$slider_rights_second->category->title}}
                                </div>
                            @endif
                            @if ($slider_rights_second->image_big || $slider_rights_second->rss_image)
                                @if ($slider_rights_second->post_type == 'audio')
                                <span  class="vid-aud">
                                    <i class="fas fa-volume-up"></i>
                                </span>
                                @endif
                                @if ($slider_rights_second->post_type == 'video')
                                    <span  class="vid-aud">
                                        <i class="fas fa-video"></i>
                                    </span>
                                @endif
                                @if ($slider_rights_second->image_big)
                                    <img data-src="{{asset('assets/images/post/'.$slider_rights_second->image_big)}}" alt="{{$slider_rights_second->alt_tag}}" class="lazy">
                                @endif
                                @if ($slider_rights_second->rss_image)
                                    <img data-src="{{$slider_rights_second->rss_image}}" alt="{{$slider_rights_second->alt_tag}}" class="lazy">
                                @endif
                            @else
                                    <img loading="lazy" decoding="async" src="{{asset('assets/images/nopic.png')}}" alt="">
                            @endif
                            <div class="inner-content">
                                <span>
                                    <h4 class="title">
                                        {{strlen($slider_rights_second->title)>40 ? mb_substr($slider_rights_second->title,0,40,"utf-8") : $slider_rights_first->title}}
                                    </h4>
                                </span>
                                <ul class="post-meta">
                                    <li>
                                        <span>
                                            {{$slider_rights_second->date ? \Carbon\Carbon::parse($slider_rights_second->date)->format('M d, Y') : $slider_rights_second->created_at->format('M d, Y')}}
                                        </span>
                                    </li>
                                    <!--<li>-->
                                    <!--    <span>|</span>-->
                                    <!--</li>-->
                                    <!--<li>-->
                                    <!--    <span>-->
                                    <!--        @if ($slider_rights_second->admin_id == 0 && $slider_rights_second->user_id != 0)-->
                                    <!--            {{ $slider_rights_second->user->name }}-->
                                    <!--        @else-->
                                    <!--            {{$slider_rights_second->admin->name}}-->
                                    <!--        @endif-->
                                    <!--    </span>-->
                                    <!--</li>-->
                                </ul>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
<!-- Hero Area End -->

<!-- Home Front Area Start -->
<section class="home-front-area">
    <div class="container-fluid full-width" >
        <div class="row">
            <div class="col-lg-8">
                @php
                    if (!isset($sponsorIndex)) {
                        $sponsorIndex = 0;
                    }
                    $shownPosts = [];
                    $remainingPosts = [];
                @endphp
                
                @foreach ($home_page_posts as $home_page_post)
                
               
                    @if (count($home_page_post->child)>0)
                        @if (cat_post_count($home_page_post->id) > 0)
                            <!-- News Tabs start -->
                            <div class="main-content tab-view">
                                <div class="row">
                                    <div class="col-lg-12 mycol">
                                        <div class="header-area">
                                            <h3 class="title">
                                                {{$home_page_post->title}}
                                            </h3>
                                            <ul class="nav" role="tablist">
                                                @foreach ($home_page_post->child as $child)
                                                <li class="nav-item">
                                                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" id="pills-home-tab" data-toggle="pill" href="#pills-{{$child->slug}}" role="tab" aria-controls="pills-{{$child->slug}}" aria-selected="true">{{$child->title}}</a>
                                                </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="tab-content" id="pills-tabContent">
                                            @foreach ($home_page_post->child as $child)
                                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pills-{{$child->slug}}" role="tabpanel" aria-labelledby="pills-{{$child->slug}}-tab">
                                                    <div class="row">
                                                        <div class="col-md-6 mycol">
                                                            @if (subcat_recent_posts($child->id,6)->count()>0)
                                                            @foreach (subcat_recent_posts($child->id,6)->take(1) as $post)
                                                                <div class="single-news landScape-normal">
                                                                    <a href="{{ route('frontend.productDetails2',[$post->category->slug??'cat',$post->id])}}">
                                                                        <div class="content-wrapper">
                                                                            <div class="img">
                                                                                @if ($post->image_big || $post->rss_image)
                                                                                    <div class="tag" style="background:{{$home_page_post->color}}">
                                                                                        {{$post->category->title}}
                                                                                    </div>
                                                                                @endif
                                                                                @if ($post->image_big || $post->rss_image)
                                                                                    @if ($post->image_big)
                                                                                        <img data-src="{{asset('assets/images/post/'.$post->image_big)}}" alt="{{$post->alt_tag}}" class="lazy">
                                                                                    @endif
                                                                                    @if ($post->rss_image)
                                                                                        <img data-src="{{$post->rss_image}}" alt="{{$post->alt_tag}}" class="lazy">
                                                                                    @endif
                                                                                    @if ($post->post_type == 'audio')
                                                                                        <span  class="vid-aud">
                                                                                            <i class="fas fa-volume-up"></i>
                                                                                        </span>
                                                                                    @endif
                                                                                    @if ($post->post_type == 'video')
                                                                                        <span  class="vid-aud">
                                                                                            <i class="fas fa-video"></i>
                                                                                        </span>
                                                                                    @endif
                                                                                @else
                                                                                    <img loading="lazy" decoding="async" src="{{asset('assets/images/nopic.png')}}" alt="">
                                                                                @endif
                                                                            </div>
                                                                            <div class="inner-content">
                                                                                <a href="{{ route('frontend.productDetails2',[$post->category->slug??'cat',$post->id])}}">
                                                                                    <h4 class="title">
                                                                                        {{strlen($post->title)>170 ? mb_substr($post->title,0,170,'utf-8').'...' : $post->title}}
                                                                                    </h4>
                                                                                    <p class="text">
                                                                                        {{ \Illuminate\Support\Str::limit(html_entity_decode(strip_tags(convertUtf8($post->description)), ENT_QUOTES | ENT_HTML5, 'UTF-8'), 180) }}
                                                                                    </p>
                                                                                    <ul class="post-meta">
                                                                                        <li>
                                                                                        <a href="{{ route('frontend.postByDate').'?date='.$post->created_at->format('Y-m-d') }}">
                                                                                                {{$post->date ? \Carbon\Carbon::parse($post->date)->format('M d, Y') : $post->created_at->format('M d, Y')}}
                                                                                            </a>
                                                                                        </li>

                                                                                        <!--<li>-->
                                                                                        <!--    <span>|</span>-->
                                                                                        <!--</li>-->
                                                                                        <!--<li>-->
                                                                                        <!--    <a href="{{ route('front.authorProfile',$post->admin->name)}}">-->
                                                                                        <!--        @if ($post->admin_id == 0 && $post->user_id != 0)-->
                                                                                        <!--            {{ $post->user->name }}-->
                                                                                        <!--        @else-->
                                                                                        <!--            {{$post->admin->name}}-->
                                                                                        <!--        @endif-->
                                                                                        <!--    </a>-->
                                                                                        <!--</li>-->
                                                                                    </ul>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </a>
                                                                </div>
                                                            @endforeach
                                                            @endif
                                                        </div>
                                                        <div class="col-md-6 mycol">
                                                            @if (subcat_recent_posts($child->id,6)->count()>0)
                                                            @foreach (subcat_recent_posts($child->id,6)->slice(1,5) as $post)
                                                                <a href="{{ route('frontend.productDetails2',[$post->category->slug??'cat',$post->id])}}">
                                                                    <div class="single-box landScape-small-with-meta">
                                                                        <div class="img">
                                                                            @if ($post->image_big || $post->rss_image)
                                                                                @if ($post->image_big)
                                                                                    <img data-src="{{asset('assets/images/post/'.$post->image_big)}}" alt="{{$post->alt_tag}}" class="lazy">
                                                                                @endif
                                                                                @if ($post->rss_image)
                                                                                    <img data-src="{{$post->rss_image}}" alt="{{$post->alt_tag}}" class="lazy">
                                                                                @endif
                                                                                @if ($post->post_type == 'audio')
                                                                                        <span  class="vid-aud">
                                                                                            <i class="fas fa-volume-up"></i>
                                                                                        </span>
                                                                                @endif
                                                                                @if ($post->post_type == 'video')
                                                                                    <span  class="vid-aud">
                                                                                        <i class="fas fa-video"></i>
                                                                                    </span>
                                                                                @endif
                                                                            @else
                                                                                <img loading="lazy" decoding="async" src="{{asset('assets/images/nopic.png')}}" alt="">
                                                                            @endif
                                                                        </div>
                                                                        <div class="content">
                                                                            <a href="{{ route('frontend.productDetails2',[$post->category->slug??'cat',$post->id])}}">
                                                                                <h4 class="title">
                                                                                    {{ strlen($post->title)>130 ? mb_substr($post->title,0,130,'utf-8').'...' : $post->title}}
                                                                                </h4>
                                                                            </a>
                                                                            <ul class="post-meta">
                                                                                <li>
                                                                                    <a href="{{ route('frontend.postByDate').'?date='.$post->created_at->format('Y-m-d') }}">
                                                                                        {{$post->date? \Carbon\Carbon::parse($post->date)->format('M d, Y') : $post->created_at->format('M d, Y')}}
                                                                                    </a>
                                                                                </li>
                                                                                <!--<li>-->
                                                                                <!--    <span>|</span>-->
                                                                                <!--</li>-->
                                                                                <!--<li>-->
                                                                                <!--    <a href="{{ route('front.authorProfile',$post->admin->name)}}">-->
                                                                                <!--        @if ($post->admin_id == 0 && $post->user_id != 0)-->
                                                                                <!--            {{ $post->user->name }}-->
                                                                                <!--        @else-->
                                                                                <!--            {{$post->admin->name}}-->
                                                                                <!--        @endif-->
                                                                                <!--    </a>-->
                                                                                <!--</li>-->
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- News Tabs End -->
                            <div class="row mb-5">
                                <div class="col-lg-12 mycol">
                                    @php
                                        $index_bottom = advertisement(); 
                                    @endphp

                                    @if ($index_bottom)
                                        @if ($index_bottom->banner_type == 'image')
                                            <div class="add-long">
                                                <a href="{{$index_bottom->link}}" target="_blank" data-addid="{{$index_bottom->id}}" id="headerAdd">
                                                    <img data-src="{{asset('assets/images/addBanner/'.$index_bottom->photo)}}" alt="{{$index_bottom->alt_tag}}" class="lazy">
                                                </a>
                                            </div>
                                        @else
                                            {!! $header_ad->banner_code !!}
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endif
                    @else
                        @if (cat_post_count($home_page_post->id) > 0 && count($shownPosts) < 6)
                        @php $shownPosts[] = $home_page_post; @endphp
                            <div class="main-content">
                                
                                <div class="row">
                                    <div class="col-12" style="padding-left:0px; padding-right:0px">
                                        <div class="cat-head" style="--cat-color: {{ $home_page_post->color ?: '#b42024' }}">
                                            <h3><a href="{{ route('frontend.category', $home_page_post->slug) }}">{{ $home_page_post->title }}</a></h3>
                                            <a href="{{ route('frontend.category', $home_page_post->slug) }}" class="cat-more">
                                                {{ __('View all') }} <i class="fas fa-angle-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mycol">
                                        @foreach (cat_recent_posts($home_page_post->id)->take(1) as $post)
                      
                                            <div class="single-news landScape-normal">
                                                <a href="{{ route('frontend.productDetails2', [$post->category->slug??'cat',$post->id]) }}">
                                                    <div class="content-wrapper">
                                                        <div class="img">
                                                            @if ($post->image_big || $post->rss_image)
                                                                <div class="tag" style="background:{{ $home_page_post->color }}">
                                                                    {{ $post->category->title }}
                                                                </div>
                                                            @endif
                    
                                                            @if ($post->image_big)
                                                                <img data-src="{{ asset('assets/images/post/' . $post->image_big) }}" alt="{{$post->alt_tag}}" class="lazy">
                                                            @elseif ($post->rss_image)
                                                                <img data-src="{{ $post->rss_image }}" alt="{{$post->alt_tag}}" class="lazy">
                                                            @else
                                                                <img loading="lazy" decoding="async" src="{{ asset('assets/images/nopic.png') }}" alt="{{$post->alt_tag}}">
                                                            @endif
                    
                                                            @if ($post->post_type == 'audio')
                                                                <span class="vid-aud"><i class="fas fa-volume-up"></i></span>
                                                            @elseif ($post->post_type == 'video')
                                                                <span class="vid-aud"><i class="fas fa-video"></i></span>
                                                            @endif
                                                        </div>
                    
                                                        <div class="inner-content">
                                                            <h4 class="title">
                                                                {{ strlen($post->title) > 170 ? mb_substr($post->title, 0, 170, 'utf-8') . '...' : $post->title }}
                                                            </h4>
                                                            <p class="text">
                                                                {{ \Illuminate\Support\Str::limit(html_entity_decode(strip_tags(convertUtf8($post->description)), ENT_QUOTES | ENT_HTML5, 'UTF-8'), 180) }}
                                                            </p>
                                                            <ul class="post-meta">
                                                                <li><a href="{{ route('frontend.postByDate') . '?date=' . $post->created_at->format('Y-m-d') }}">{{ $post->date ? \Carbon\Carbon::parse($post->date)->format('M d, Y') : $post->created_at->format('M d, Y') }}</a></li>
                                                                <!--<li><span>|</span></li>-->
                                                                <!--<li><a href="{{ route('front.authorProfile', $post->admin->name) }}">-->
                                                                <!--    @if ($post->admin_id == 0 && $post->user_id != 0)-->
                                                                <!--        {{ $post->user->name }}-->
                                                                <!--    @else-->
                                                                <!--        {{ $post->admin->name }}-->
                                                                <!--    @endif-->
                                                                <!--</a></li>-->
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                
                                    <div class="col-md-6 mycol d-none d-md-block">
                                        @foreach (cat_recent_posts($home_page_post->id)->slice(1,5) as $post)
                                            <a href="{{ route('frontend.productDetails2',[$post->category->slug??'cat',$post->id])}}">
                                                <div class="single-box landScape-small-with-meta">
                                                    <div class="img">
                                                        @if ($post->image_big)
                                                            <img data-src="{{asset('assets/images/post/'.$post->image_big)}}" alt="{{$post->alt_tag}}" class="lazy">
                                                        @elseif ($post->rss_image)
                                                            <img data-src="{{$post->rss_image}}" alt="{{$post->alt_tag}}" class="lazy">
                                                        @else
                                                            <img loading="lazy" decoding="async" src="{{asset('assets/images/nopic.png')}}" alt="">
                                                        @endif
                                
                                                        @if ($post->post_type == 'audio')
                                                            <span class="vid-aud"><i class="fas fa-volume-up"></i></span>
                                                        @elseif ($post->post_type == 'video')
                                                            <span class="vid-aud"><i class="fas fa-video"></i></span>
                                                        @endif
                                                    </div>
                                
                                                    <div class="content">
                                                        <h4 class="title">
                                                            {{ strlen($post->title) > 130 ? mb_substr($post->title, 0, 130, 'utf-8') . '...' : $post->title }}
                                                        </h4>
                                                        <ul class="post-meta">
                                                            <li><a href="{{ route('frontend.postByDate').'?date='.$post->created_at->format('Y-m-d') }}">{{$post->date ? \Carbon\Carbon::parse($post->date)->format('M d, Y') : $post->created_at->format('M d, Y')}}</a></li>
                                                            <!--<li><span>|</span></li>-->
                                                            <!--<li><a href="{{ route('front.authorProfile',$post->admin->name)}}">-->
                                                            <!--    @if ($post->admin_id == 0 && $post->user_id != 0)-->
                                                            <!--        {{ $post->user->name }}-->
                                                            <!--    @else-->
                                                            <!--        {{$post->admin->name}}-->
                                                            <!--    @endif-->
                                                            <!--</a></li>-->
                                                        </ul>
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                    <div class="col-12 d-block d-md-none px-0">
                                        @foreach (cat_recent_posts($home_page_post->id)->slice(1,5) as $post)
                                            <div class="news-item" >
                                                <a href="{{ route('frontend.productDetails2', [$post->category->slug??'cat',$post->id]) }}">
                                                    <div class="d-flex align-items-start mt-2 ps-0">
                                                        <div class="me-2 flex-shrink-0" style="width: 100px;">
                                                            <img loading="lazy" decoding="async" class="img-fluid" src="{{ $post->image_big ? asset('assets/images/post/'.$post->image_big) : ($post->rss_image ?? asset('assets/images/nopic.png')) }}" alt="{{$post->alt_tag}}">
                                                        </div>
                                                        <div class="text-content mx-2">
                                                            <p class="mb-1" style="font-size: 12px; font-weight: 600;">
                                                                {{ strlen($post->title) > 50 ? mb_substr($post->title, 0, 50, 'utf-8').'...' : $post->title }}
                                                            </p>
                                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                                {{$post->date ? \Carbon\Carbon::parse($post->date)->format('M d, Y') : $post->created_at->format('M d, Y')}}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>

                                
                                    @if(isset($sponsor_index[$sponsorIndex]))
                                        <div class="col-md-12 mycol my-3">
                                            <a href="{{ $sponsor_index[$sponsorIndex]->link ?? '#' }}" target="_blank">
                                                <img loading="lazy" decoding="async" src="{{ asset('assets/images/addBanner/' . $sponsor_index[$sponsorIndex]->photo) }}" alt="Sponsor Banner" class="img-fluid lazy" style="width: 100%; height: auto;">
                                            </a>
                                        </div>
                                        
                                    @endif
                                    @php
                                        $sponsorIndex++; 
                                    @endphp
                                </div>

                            </div>
                        @else
                            @php 
                                $remainingPosts[] = $home_page_post; 
                            @endphp
                        @endif
                    @endif
                @endforeach
            </div>
            
            
            
            <div class="col-lg-4 aside">
                @if ($ws->feature_inhome == 1)
                    <div class="aside-tab mt-4">
                        <ul class="nav" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="pills-tab1-tab" data-toggle="pill" href="#pills-tab1" role="tab" aria-controls="pills-tab1" aria-selected="true">{{__('FEATURED')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-tab2-tab" data-toggle="pill" href="#pills-tab2" role="tab" aria-controls="pills-tab2" aria-selected="false">{{__('RECENT')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-tab3-tab" data-toggle="pill" href="#pills-tab3" role="tab" aria-controls="pills-tab3" aria-selected="false">{{__('TOP VIEWS')}}</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-tab1" role="tabpanel" aria-labelledby="pills-tab1-tab">
                                <div class="row">
                                    @foreach ($is_features as $feature)
                                    <div class="col-lg-12">
                                        <a href="{{ route('frontend.productDetails2',[$feature->category->slug??'cat',$feature->id])}}">
                                            <div class="single-box landScape-small-with-meta">
                                                <div class="img">
                                                    @if ($feature->image_big || $feature->rss_image)
                                                        @if ($feature->image_big)
                                                            <img data-src="{{asset('assets/images/post/'.$feature->image_big)}}" alt="{{$feature->alt_tag}}" class="lazy">
                                                        @endif

                                                        @if ($feature->rss_image)
                                                            <img data-src="{{$feature->rss_image}}" alt="{{$feature->alt_tag}}" class="lazy">
                                                        @endif

                                                        @if ($feature->post_type == 'audio')
                                                                <span  class="vid-aud">
                                                                    <i class="fas fa-volume-up"></i>
                                                                </span>
                                                            @endif
                                                            @if ($feature->post_type == 'video')
                                                                <span  class="vid-aud">
                                                                    <i class="fas fa-video"></i>
                                                                </span>
                                                        @endif

                                                    @else
                                                        <img loading="lazy" decoding="async" src="{{asset('assets/images/nopic.png')}}" alt="">
                                                    @endif
                                                </div>
                                                <div class="content">
                                                    <a href="{{ route('frontend.productDetails2',[$feature->category->slug??'cat',$feature->id])}}">
                                                        <h4 class="title">
                                                        {{strlen($feature->title)>40 ? mb_substr($feature->title,0,40,"utf-8") : $feature->title}}
                                                        </h4>
                                                    </a>
                                                    <ul class="post-meta">
                                                        <li>
                                                            <a href="{{ route('frontend.postByDate').'?date='.$feature->created_at->format('Y-m-d') }}">
                                                                {{$feature->date ? \Carbon\Carbon::parse($feature->date)->format('M d, Y') : $feature->created_at->format('M d, Y')}}
                                                            </a>
                                                        </li>
                                                        <!--<li>-->
                                                        <!--    <span>|</span>-->
                                                        <!--</li>-->
                                                        <!--<li>-->
                                                        <!--    <a href="#">-->
                                                        <!--        <a href="{{ route('front.authorProfile',$feature->admin->name)}}">-->
                                                        <!--            @if ($feature->admin_id == 0 && $feature->user_id != 0)-->
                                                        <!--                {{ $feature->user->name }}-->
                                                        <!--            @else-->
                                                        <!--                {{$feature->admin->name}}-->
                                                        <!--            @endif-->
                                                        <!--        </a>-->
                                                        <!--    </a>-->
                                                        <!--</li>-->
                                                    </ul>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pills-tab2" role="tabpanel" aria-labelledby="pills-tab2-tab">
                                <div class="row">
                                    @foreach ($is_recents as $is_recent)
                                    <div class="col-lg-12">
                                        <a href="{{ route('frontend.productDetails2',[$is_recent->category->slug??'cat',$is_recent->id])}}">
                                            <div class="single-box landScape-small-with-meta">
                                                <div class="img">
                                                    @if ($is_recent->image_big || $is_recent->rss_image)
                                                        @if ($is_recent->image_big)
                                                            <img data-src="{{asset('assets/images/post/'.$is_recent->image_big)}}" alt="{{$is_recent->alt_tag}}" class="lazy">
                                                        @endif

                                                        @if ($is_recent->rss_image)
                                                            <img data-src="{{ $is_recent->rss_image}}" alt="{{$is_recent->alt_tag}}" class="lazy">
                                                        @endif

                                                        @if ($is_recent->post_type == 'audio')
                                                            <span  class="vid-aud">
                                                                <i class="fas fa-volume-up"></i>
                                                            </span>
                                                        @endif
                                                        @if ($is_recent->post_type == 'video')
                                                            <span  class="vid-aud">
                                                                <i class="fas fa-video"></i>
                                                            </span>
                                                        @endif

                                                    @else
                                                        <img loading="lazy" decoding="async" src="{{asset('assets/images/nopic.png')}}" alt="">
                                                    @endif
                                                </div>
                                                <div class="content">
                                                    <a href="{{ route('frontend.productDetails2',[$is_recent->category->slug??'cat',$is_recent->id])}}">
                                                        <h4 class="title">
                                                            {{strlen($is_recent->title)>40 ? mb_substr($is_recent->title,0,40,"utf-8") : $is_recent->title}}
                                                        </h4>
                                                    </a>
                                                    <ul class="post-meta">
                                                        <li>
                                                            <a href="{{ route('frontend.postByDate').'?date='.$is_recent->created_at->format('Y-m-d') }}">
                                                                {{$is_recent->date ? \Carbon\Carbon::parse($is_recent->date)->format('M d, Y') : $is_recent->created_at->format('M d, Y')}}
                                                            </a>
                                                        </li>
                                                        <!--<li>-->
                                                        <!--    <span>|</span>-->
                                                        <!--</li>-->
                                                        <!--<li>-->
                                                        <!--    <a href="{{ route('front.authorProfile',$is_recent->admin->name)}}">-->
                                                        <!--        @if ($is_recent->admin_id == 0 && $is_recent->user_id != 0)-->
                                                        <!--            {{ $is_recent->user->name }}-->
                                                        <!--        @else-->
                                                        <!--            {{$is_recent->admin->name}}-->
                                                        <!--        @endif-->
                                                        <!--    </a>-->
                                                        <!--</li>-->
                                                    </ul>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pills-tab3" role="tabpanel" aria-labelledby="pills-tab3-tab">
                                <div class="row">
                                    @foreach (top_view_posts($top_views) as $post)
                                    @php
                                    @endphp
                                        @if ($post)
                                            <div class="col-lg-12">
                                                <a href="{{ route('frontend.productDetails2',[$post->category->slug??'cat',$post->id])}}">
                                                    <div class="single-box landScape-small-with-meta">
                                                        @if ($post->image_big || $post->rss_image)
                                                            <div class="img">
                                                                @if ($post->image_big)
                                                                    <img data-src="{{asset('assets/images/post/'.$post->image_big)}}" alt="{{$post->alt_tag}}" class="lazy">
                                                                @endif
                                                                @if ($post->rss_image)
                                                                    <img data-src="{{$post->rss_image}}" alt="{{$post->alt_tag}}" class="lazy">
                                                                @endif
                                                                @if ($post->post_type == 'audio')
                                                                    <span  class="vid-aud">
                                                                        <i class="fas fa-volume-up"></i>
                                                                    </span>
                                                                @endif
                                                                @if ($post->post_type == 'video')
                                                                    <span  class="vid-aud">
                                                                        <i class="fas fa-video"></i>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="img">
                                                                <img loading="lazy" decoding="async" src="{{asset('assets/images/nopic.png')}}" alt="">
                                                            </div>
                                                        @endif
                                                        <div class="content">
                                                            <a href="{{ route('frontend.productDetails2',[$post->category->slug??'cat',$post->id])}}">
                                                                <h4 class="title">
                                                                {{ $post->title }}
                                                                </h4>
                                                            </a>
                                                            <ul class="post-meta">
                                                                <li>
                                                                    <a href="{{ route('frontend.postByDate').'?date='.$post->created_at->format('Y-m-d') }}">
                                                                        {{$post->date ? \Carbon\Carbon::parse($post->date)->format('M d, Y') : $post->created_at->format('M d, Y')}}
                                                                    </a>
                                                                </li>
                                                                <!--<li>-->
                                                                <!--    <span>|</span>-->
                                                                <!--</li>-->
                                                                <!--<li>-->
                                                                <!--    <a href="{{ route('front.authorProfile',$post->admin->name)}}">-->
                                                                <!--        @if ($post->admin_id == 0 && $post->user_id != 0)-->
                                                                <!--            {{ $post->user->name }}-->
                                                                <!--        @else-->
                                                                <!--            {{$post->admin->name}}-->
                                                                <!--        @endif-->
                                                                <!--    </a>-->
                                                                <!--</li>-->
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if ($ws->category_inhome == 1)
                    <div class="categori-widget-area mt-4">
                        <div class="header-area">
                            <h4 class="title">
                                {{__('CATEGORIES')}}
                            </h4>
                        </div>
                        <ul class="categori-list">
                            @foreach ($home_page_posts as $category)
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
                @endif
                @if ($ws->follow_inhome == 1)
                    <div class="social-follow-area">
                        <div class="header-area">
                            <h4 class="title">
                               {{__('Follow Us')}}
                            </h4>
                        </div>
                        <ul class="social-links">
                            @foreach ($social_links as $social_link)
                            <li>
                                <a href="{{ $social_link->link}}" class="{{$social_link->name}}">
                                    <i class="{{$social_link->icon}}"></i>{{$social_link->name}}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if ($ws->tag_inhome == 1)
                    <div class="tags-widget">
                        <div class="header-area">
                            <h3 class="title">
                                {{__('TAGS')}}
                            </h3>
                        </div>
                        <ul class="tag-list">
                            @foreach ($tags as $tag)
                                <li>
                                    <a href="{{ route('tag.search',$tag)}}">{{$tag}}</a>
                                </li>
                            @endforeach

                        </ul>
                    </div>
                @endif
                <div class="add mt-4">
                    @php
                    $sidebar_banner = sidebar_banner(); //method from helper.php class
                    @endphp
                    @if ($sidebar_banner)
                        @if ($sidebar_banner->banner_type == 'image')
                            <a href="{{$sidebar_banner->link}}">
                                <img data-src="{{asset('assets/images/addBanner/'.$sidebar_banner->photo)}}" alt="{{$sidebar_banner->alt_tag}}" class="lazy">
                            </a>
                        @else
                            {!! $sidebar_banner->banner_code !!}
                        @endif
                    @endif
                </div>
                @if ($ws->poll_inhome == 1)
                    <div class="poll-area mt-4">
                        <div class="header-area">
                            <h4 class="title">
                               {{__('Poll')}}
                            </h4>
                            <h5 class="title"><a href="{{ route('front.allPoll') }}" style="color: :#ff0000;">{{__('Previous Result')}}</a></h5>
                        </div>

                        @foreach ($polls as $poll)
                        <div class="poll-box">
                            <h4 class="title">
                                <i class="far fa-question-circle"></i> {{$poll->question}}
                            </h4>
                            <form id="voteform{{$poll->id}}" action="{{ route('front.poll.vote')}}" method="POST" class="voteform">
                                @csrf
                                <input type="hidden" name="poll_question_id" value="{{ $poll->id }}" id="poll_question_id">
                                <input type="hidden" name="ip_address">
                                @foreach ($poll->child as $answer)
                                <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input" id="customControlValidation{{$answer->id}}" name="poll_answer_id" value="{{$answer->id}}">
                                    <label class="custom-control-label" for="customControlValidation{{$answer->id}}">{{$answer->poll_option}}</label>
                                </div>
                                @endforeach
                                @php
                                    $ip = request()->ip();
                                    $isVote = App\Models\PollResult::where('poll_question_id',$poll->id)->where('ip_address',$ip)->first();
                                @endphp
                                @if (!$isVote)
                                    <button data-id="{{$poll->id}}" type="submit" class="mybtn1 vote" id="vote_success-{{$poll->id}}">{{__('Vote')}}</button>
                                @else
                                    <button data-id="{{$poll->id}}" type="submit" class="mybtn1 result view_result" id="vote_view-{{$poll->id}}">{{__('View Result')}}</button>
                                @endif
                                <div class="viewVoteResult"></div>
                                <p id="errMsg"></p>
                                <div class="voteresult"></div>
                            </form>
                        </div>
                        @endforeach
                    </div>
                @endif
                @if ($ws->calendar_inhome == 1)
                    <div class="celander-widget-area mt-4">
                        <div id="datecalender"></div>
                    </div>
                @endif
                @if ($ws->newsletter_inhome == 1)
                    <div class="aside-newsletter-widget mt-4 subarea">
                        <div class="header-area">
                            <h4 class="title">
                            {{__('Newsletter')}}
                            </h4>
                        </div>
                        <p class="text">{{__('Subscribe to our newsletter to stay.')}}</p>
                        <form action="{{ route('front.subscribers.store') }}" class="subscribe-form" method="POST" id="subForm">
                            @csrf
                            <input type="text" placeholder="{{__('Enter Your Email Address')}}" name="email" class="subEmail">
                            <button type="submit" class="submit subBtn">{{__('Subscribe')}}</button>
                        </form>
                    </div>
                @endif
                <div class=" aside">
                <div class="add">
                    <div class="header-area">
                        <h4 class="title">
                        {{__('Sponsor Ad')}}
                        </h4>
                    </div>
                    @foreach ($sponsor_banners as $sponsor_banner)
                        @if ($sponsor_banner)
                            @if ($sponsor_banner->banner_type == 'image')
                                <div class="add-banner mt-2">
                                    <a href="{{$sponsor_banner->link}}">
                                        <img data-src="{{asset('assets/images/addBanner/'.$sponsor_banner->photo)}}" alt="" class="lazy">
                                    </a>
                                </div>
                            @else
                                {!! $sponsor_banner->banner_code !!}
                            @endif
                        @endif
                    @endforeach
                </div>

                <div class="widget-slider">
                    @foreach ($widgets as $widget)
                    <div class="aside-newsletter-widget mt-4">
                        <div class="header-area">
                            <h4 class="title">
                            {{ $widget->title }}
                            </h4>
                        </div>
                        <p class="text"> {!! (strlen(convertUtf8(strip_tags($widget->description))) > 350) ? convertUtf8(substr(strip_tags($widget->description), 0, 350)) . '...' : convertUtf8(strip_tags($widget->description)) !!}</p>
                    </div>
                @endforeach
                </div>

            </div>
            </div>
        </div>
        
        <div clall= "container-fluid full-width">
            @if (count($remainingPosts) > 0)
            <div class="main-content mt-5 sub-full-width">
        
                @foreach ($remainingPosts as $home_page_post)
                    @php
                        $posts = cat_recent_posts($home_page_post->id)->take(4);
                    @endphp
        
                    @if ($posts->count() > 0)
        
        
                        <!-- Mobile Layout -->
                        <div class="d-block d-md-none px-0">
                            <div class="row">
                                <div class="col-12" style="">
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                        <h3 class="mb-0" style="font-size: 18px; color: red; text-decoration: underline;font-weight: bolder;">
                                            {{ $home_page_post->title }}
                                        </h3>
                                        <a href="{{ route('frontend.category', $home_page_post->slug) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            {{ __('View all') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
        
                            @foreach ($posts as $index => $post)
                                @if ($index == 0)
                                    <!-- First post full width image -->
                                    <a href="{{ route('frontend.productDetails2', [$post->category->slug??'cat',$post->id]) }}" class="d-block mb-3  text-decoration-none text-dark">
                                        @if ($post->image_big)
                                            <img loading="lazy" decoding="async" src="{{ asset('assets/images/post/' . $post->image_big) }}" alt="{{$post->alt_tag}}" class="img-fluid rounded w-100">
                                        @elseif ($post->rss_image)
                                            <img loading="lazy" decoding="async" src="{{ $post->rss_image }}" alt="{{$post->alt_tag}}" class="img-fluid rounded w-100">
                                        @else
                                            <img loading="lazy" decoding="async" src="{{ asset('assets/images/nopic.png') }}" alt="" class="img-fluid rounded w-100">
                                        @endif
        
                                        <p class="mt-2 mb-1" style="font-size: 12px; font-weight: 600;">
                                            {{ strlen($post->title) > 50 ? mb_substr($post->title, 0, 50, 'utf-8') . '...' : $post->title }}
                                        </p>
                                        <p class="text-muted mb-0" style="font-size: 0.8rem;">
                                            {{$post->date ? \Carbon\Carbon::parse($post->date)->format('M d, Y') : $post->created_at->format('M d, Y')}}
                                        </p>
                                    </a>
                                @else
                                    <!-- Other posts image left, text right -->
                                    <a href="{{ route('frontend.productDetails2', [$post->category->slug??'cat',$post->id]) }}" class="d-flex align-items-start mb-3  text-decoration-none text-dark">
                                        <div style="width: 100px; flex-shrink: 0;">
                                            @if ($post->image_big)
                                                <img loading="lazy" decoding="async" src="{{ asset('assets/images/post/' . $post->image_big) }}" alt="{{$post->alt_tag}}" class="img-fluid rounded">
                                            @elseif ($post->rss_image)
                                                <img loading="lazy" decoding="async" src="{{ $post->rss_image }}" alt="{{$post->alt_tag}}" class="img-fluid rounded">
                                            @else
                                                <img loading="lazy" decoding="async" src="{{ asset('assets/images/nopic.png') }}" alt="" class="img-fluid rounded">
                                            @endif
                                        </div>
                                        <div class="ps-3 flex-grow-1 mx-2">
                                            <p class="mb-1" style="font-size: 12px; font-weight: 600;">
                                                {{ strlen($post->title) > 60 ? mb_substr($post->title, 0, 60, 'utf-8') . '...' : $post->title }}
                                            </p>
                                            <p class="text-muted mb-0" style="font-size: 0.8rem;">
                                                {{ $post->created_at->format('M d, Y') }} 
                                        </div>
                                    </a>
                                @endif
                            @endforeach
                        </div>
        
                    @endif
                @endforeach
            </div>
        @endif
        
        @if (count($remainingPosts) > 0)
            <div class="main-content mt-5">
        
                @foreach ($remainingPosts as $home_page_post)
                    @php
                        $posts = cat_recent_posts($home_page_post->id)->take(4);
                    @endphp
        
                    @if ($posts->count() > 0)
        
                        <!-- Desktop Layout (Grid) -->
                        <div class="row mb-4 d-none d-md-flex">
                            
                            <div class="col-12" style="padding-left:0px; padding-right:0px">
                                        <div class="cat-head" style="--cat-color: {{ $home_page_post->color ?: '#b42024' }}">
                                            <h3><a href="{{ route('frontend.category', $home_page_post->slug) }}">{{ $home_page_post->title }}</a></h3>
                                            <a href="{{ route('frontend.category', $home_page_post->slug) }}" class="cat-more">
                                                {{ __('View all') }} <i class="fas fa-angle-right"></i>
                                            </a>
                                        </div>
                                    </div>
                            
                            
        
                            @foreach ($posts as $post)
                                <div class="col-md-6 col-lg-3 mycol mb-3">
                                    <div class="single-news landScape-normal h-100">
                                        <a href="{{ route('frontend.productDetails2', [$post->category->slug??'cat',$post->id]) }}">
                                            <div class="content-wrapper">
                                                <div class="img">
                                                    @if ($post->image_big || $post->rss_image)
                                                        <div class="tag" style="background:{{ $home_page_post->color }}">
                                                            {{ $post->category->title }}
                                                        </div>
                                                    @endif
        
                                                    @if ($post->image_big)
                                                        <img data-src="{{ asset('assets/images/post/' . $post->image_big) }}" alt="{{$post->alt_tag}}" class="lazy">
                                                    @elseif ($post->rss_image)
                                                        <img data-src="{{ $post->rss_image }}" alt="{{$post->alt_tag}}" class="lazy">
                                                    @else
                                                        <img loading="lazy" decoding="async" src="{{ asset('assets/images/nopic.png') }}" alt="">
                                                    @endif
        
                                                    @if ($post->post_type == 'audio')
                                                        <span class="vid-aud"><i class="fas fa-volume-up"></i></span>
                                                    @elseif ($post->post_type == 'video')
                                                        <span class="vid-aud"><i class="fas fa-video"></i></span>
                                                    @endif
                                                </div>
        
                                                <div class="inner-content">
                                                    <h4 class="title">
                                                        {{ strlen($post->title) > 170 ? mb_substr($post->title, 0, 170, 'utf-8') . '...' : $post->title }}
                                                    </h4>
                                                    <p class="text">
                                                        {!! (strlen(convertUtf8(strip_tags($post->description))) > 200)
                                                            ? convertUtf8(substr(strip_tags($post->description), 0, 200)) . '...'
                                                            : convertUtf8(strip_tags($post->description)) !!}
                                                    </p>
                                                    <ul class="post-meta">
                                                        <li>
                                                            <a href="{{ route('frontend.postByDate') . '?date=' . $post->created_at->format('Y-m-d') }}">
                                                                {{$post->date ? \Carbon\Carbon::parse($post->date)->format('M d, Y') : $post->created_at->format('M d, Y')}}
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
        
                        
        
                    @endif
                @endforeach
            </div>
        @endif
        </div>
        <!--<div class="row">-->
        <!--    <div class="col-lg-12">-->
        <!--        <div class="video-gallery-box">-->
        <!--            <div class="one-item-slider">-->

                         
        <!--            </div>-->
        <!--                <ul class="all-item-slider">-->
        <!--                    @foreach ($video_smalls as $video_small)-->
        <!--                        <li>-->
        <!--                            <a href="{{asset('assets/videos/'.$video_small->video)}}" data-embed="{{ $video_small->embed_video }}" class="">-->
        <!--                                <div class="left">-->
        <!--                                    <img loading="lazy" decoding="async" src="{{asset('assets/images/post/'.$video_small->image_big)}}" alt="">-->
        <!--                                </div>-->
        <!--                                <div class="right">-->
        <!--                                    <h4 class="title">-->
        <!--                                        {{strlen($video_small->title)>40 ? mb_substr($video_small->title,0,40,"utf-8").'...' : $video_small->title}}-->
        <!--                                    </h4>-->
        <!--                                    <p class="date">-->
        <!--                                        {{$video_small->createdAt()}}-->
        <!--                                    </p>-->
        <!--                                </div>-->
        <!--                            </a>-->
        <!--                        </li>-->
        <!--                    @endforeach-->
        <!--                </ul>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--</div>-->
        <style>
            .gallery-images .item {
                display: block;
                position: relative;
                overflow: hidden;
                border-radius: 8px;
                margin-bottom: 20px;
            }
            
            .gallery-images .item img {
                width: 100%;
                height: auto;
                transition: transform 0.4s ease;
            }
            
            .gallery-images .item:hover img {
                transform: scale(1.05);
            }
            
            .gallery-images .title {
                margin-top: 10px;
                font-size: 18px;
                text-align: center;
            }
            </style>
            <style>
                .single-news {
                    overflow: hidden;
                    border-radius: 8px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                    transition: transform 0.4s ease;
                }
                
                .single-news img {
                    width: 100%;
                    height: auto;
                    transition: transform 0.4s ease;
                    display: block;
                    border-bottom: 1px solid #ddd;
                }
                
                .single-news:hover img {
                    transform: scale(1.05);
                }
                
                .single-news .tag {
                    position: absolute;
                    top: 15px;
                    left: 15px;
                    background: #333;
                    color: #fff;
                    padding: 4px 10px;
                    font-size: 12px;
                    border-radius: 3px;
                    z-index: 2;
                }
                
                .inner-content {
                    background: #fff;
                    padding: 15px;
                }
                
                .inner-content .title {
                    font-size: 18px;
                    font-weight: 600;
                    margin-bottom: 10px;
                }
                
                .inner-content .post-meta {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                    font-size: 14px;
                    color: #999;
                }
                
                .inner-content .post-meta li {
                    display: inline-block;
                    margin-right: 10px;
                }
                </style>


        <div class="row">
            <div class="col-lg-12">
                @if ($is_features->count()>0)
                <!-- Featured News Area Start -->
                <div class="featured-news">
                    <div class="row">
                        <div class="col-lg-12 mycol">
                            <div class="header-area">
                                <h3 class="title">
                                    {{ __('TODAYS FEATURED NEWS') }}
                                </h3>
                            </div>
                        </div>
                    </div>
                
                    <div class="row">
                        @foreach ($is_features as $post)
                        <div class="col-md-6 mb-4">
                            <div class="single-news animation zoom-hover">
                                <a href="{{ route('frontend.productDetails2', [$post->category->slug??'cat',$post->id]) }}" class="item">
                                    <div class="content-wrapper">
                                        @if ($post->image_big || $post->rss_image)
                                        <div class="tag" style="background:{{ $post->category->color }}">
                                            {{ $post->category->title }}
                                        </div>
                                        @endif
                
                                        @if ($post->image_big || $post->rss_image)
                                            @if ($post->image_big)
                                                <img loading="lazy" decoding="async" src="{{ asset('assets/images/post/'.$post->image_big) }}" alt="{{$post->alt_tag}}" class="img-fluid">
                                            @endif
                                            @if ($post->rss_image)
                                                <img loading="lazy" decoding="async" src="{{ $post->rss_image }}" alt="{{$post->alt_tag}}" class="img-fluid">
                                            @endif
                                        @else
                                            <img loading="lazy" decoding="async" src="{{ asset('assets/images/nopic.png') }}" alt="" class="img-fluid">
                                        @endif
                
                                        <div class="inner-content p-3">
                                            <h4 class="title">
                                                {{ strlen($post->title) > 30 ? mb_substr($post->title, 0, 30, 'utf-8').'...' : $post->title }}
                                            </h4>
                                            <ul class="post-meta">
                                                <li>
                                                    <a href="{{ route('frontend.postByDate').'?date='.$post->created_at->format('Y-m-d') }}">
                                                        {{$post->date ? \Carbon\Carbon::parse($post->date)->format('M d, Y') : $post->created_at->format('M d, Y')}}
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <!-- Featured News Area End -->
                @endif



                @if ($image_albums->count()>0)
                <!-- Photo Gallery Area Start -->
                <div class="featured-news photos">
                        <div class="row">
                            <div class="col-lg-12 mycol">
                                <div class="header-area">
                                    <h3 class="title">
                                        {{__('Image Gallery')}}
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="gallery-images">
                                    <div class="row">
                                        
                                        @foreach ($image_albums as $image_album)
                                            @if (count($image_album->categories) > 0)
                                                <div class="col-md-6 mycol">
                                                    <a href="{{ route('gallery.view',$image_album->id)}}" class="item">
                                                        <div class="single-news animation">
                                                            <div class="content-wrapper">
                                                                <img data-src="{{asset('assets/images/image-album/'.$image_album->photo)}}" alt="{{$image_album->alt_tag}}" class="lazy">
                                                                <div class="inner-content">
                                                                    <h4 class="title">
                                                                        {{$image_album->album_name}}
                                                                    </h4>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endif
                                        @endforeach
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <!-- Photo Gallery Area End -->
                @endif

                <!-- More News Area Start -->
                @if ($more_news->count() > 0)
                <div class="more-news">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="more-area">
                                <div class="header-area">
                                    <h4 class="title">{{ __('More news') }}</h4>
                                </div>
                            </div>
                            <div class="row show-more-news">
                                @foreach ($more_news as $index => $more_new)
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-4 ">
                                    <a href="{{ route('frontend.productDetails2', [$more_new->category->slug??'cat',$more_new->id]) }}">
                                        <div class="single-news land-scap-medium">
                                            <div class="img">
                                                @if ($more_new->image_big || $more_new->rss_image)
                                                    <div class="tag" style="background:{{ $more_new->category->color }}">
                                                        {{ $more_new->category->title }}
                                                    </div>
                                                @endif
                
                                                @if ($more_new->image_big)
                                                    <img src="{{ asset('assets/images/post/' . $more_new->image_big) }}" alt="{{ $more_new->title }}" loading="lazy" />
                                                @elseif ($more_new->rss_image)
                                                    <img src="{{ $more_new->rss_image }}" alt="{{ $more_new->title }}" loading="lazy" />
                                                @else
                                                    <img src="{{ asset('assets/images/nopic.png') }}" alt="No Image" loading="lazy" />
                                                @endif
                
                                                @if ($more_new->post_type == 'audio')
                                                    <span class="vid-aud"><i class="fas fa-volume-up"></i></span>
                                                @endif
                                                @if ($more_new->post_type == 'video')
                                                    <span class="vid-aud"><i class="fas fa-video"></i></span>
                                                @endif
                                            </div>
                                            <div class="content">
                                                <h4 class="title">
                                                    {{ strlen($more_new->title) > 30 ? mb_substr($more_new->title, 0, 30, 'utf-8') . '...' : $more_new->title }}
                                                </h4>
                                                <p class="text">
                                                    {!! (strlen(strip_tags($more_new->description)) > 100) ? substr(strip_tags($more_new->description), 0, 100) . '...' : strip_tags($more_new->description) !!}
                                                </p>
                                                <ul class="post-meta">
                                                    <li><a href="{{ route('frontend.postByDate') . '?date=' . $more_new->created_at->format('Y-m-d') }}">
                                                        {{$more_new->date ? \Carbon\Carbon::parse($more_new->date)->format('M d, Y') : $more_new->created_at->format('M d, Y')}}
                                                    </a></li>
                                                    <!--<li><span>|</span></li>-->
                                                    <!--<li><a href="{{ route('front.authorProfile', $more_new->admin->name) }}">-->
                                                    <!--    @if ($more_new->admin_id == 0 && $more_new->user_id != 0)-->
                                                    <!--        {{ $more_new->user->name }}-->
                                                    <!--    @else-->
                                                    <!--        {{ $more_new->admin->name }}-->
                                                    <!--    @endif-->
                                                    <!--</a></li>-->
                                                </ul>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                @php
                                    $last_id = $more_new->id;
                                @endphp
                                @endforeach
                            </div>
                            <div class="row">
                                <div class="col-lg-12 text-center mt-4">
                                    <button type="button" class="mybtn1" id="loadMore" data-id="{{ $last_id }}">{{ __('Load More') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- More News Area End -->
            </div>
        </div>
        <div class="row pt-5">
            <div class="col-lg-12 text-center">
                @if($sponsor_footer)
                    <a href="{{ $sponsor_footer->link ?? '#' }}" target="_blank">
                        <img loading="lazy" decoding="async" src="{{ asset('assets/images/addBanner/' . $sponsor_footer->photo) }}" alt="Advertisement" class="img-fluid lazy" style="width: 100%; height: auto;">
                    </a>
                @endif
            </div>
        </div>

    </div>
</section>

<style>
    .more-news .single-news {
        border: 1px solid #ddd;
        border-radius: 6px;
        overflow: hidden;
        background: #fff;
        height: 100%; 
        display: flex;
        flex-direction: column;
    }
    
    .more-news .single-news .img {
        width: 100%;
        height: 180px; 
        background-color: #f0f0f0; 
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    
    .more-news .single-news .img img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        display: block;
    }
    
    
    
    .more-news .single-news:hover .img img {
        transform: scale(1.05);
    }
    
    .more-news .single-news .tag {
        position: absolute;
        top: 8px;
        left: 8px;
        padding: 3px 8px;
        font-size: 12px;
        color: #fff;
        border-radius: 3px;
        text-transform: uppercase;
        font-weight: 600;
    }
    
    .more-news .single-news .content {
        padding: 15px;
        flex-grow: 1; 
        display: flex;
        flex-direction: column;
    }
    
    .more-news .single-news .content .title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 8px;
        color: #333;
    }
    
    .more-news .single-news .content .text {
        flex-grow: 1;
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 10px;
    }
    
    .more-news .single-news .content ul.post-meta {
        padding-left: 0;
        list-style: none;
        font-size: 0.8rem;
        color: #999;
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
    }
    
    .more-news .single-news .content ul.post-meta li span {
        color: #ccc;
    }
    
    .more-news .single-news .vid-aud {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(0,0,0,0.5);
        color: #fff;
        padding: 5px 7px;
        border-radius: 3px;
        font-size: 14px;
    }
    
    .more-news .row {
        --bs-gutter-x: 1rem; 
        --bs-gutter-y: 1rem;
    }

</style>
<!-- Home Front Area End -->

{{-- @include('cookieConsent::index') --}}

@endsection

@push('js')
    <script>
        $('#loadMore').on('click', function () {
            let button = $(this);
            let last_id = button.data('id');
            button.text('Loading...');
        
            $.ajax({
                url: '{{ route("frontend.loadMore") }}', 
                type: 'GET',
                data: { last_news: last_id },
                success: function (response) {
                    $('.show-more-news').append(response.output); 
                    button.data('id', response.id); 
        
                    if (!response.has_more) {
                        button.remove(); 
                    } else {
                        button.text('{{ __("Load More") }}');
                    }
                }
            });
        });

    </script>
    <script>
        "use strict";

        var lang = {
            'load_more': '{{__('Load More')}}',
            'hide_resultt': '{{__('Hide Result')}}',
            'view_resultt': '{{__('View Result')}}',
            'loading': '{{__('Loading......')}}',
        }
    </script>
    <script src="{{asset('assets/front/js/notify.min.js')}}"></script>
    <script id="dsq-count-scr" src="//{{ $gs->disqus}}.disqus.com/count.js" async></script>
    <script src="{{asset('assets/front/js/front.js')}}"></script>

    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.9/jquery.lazy.min.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.9/jquery.lazy.plugins.min.js"></script>
    <script>
        "use strict";
        $(function() {
            $('.lazy').Lazy();
        });
    </script>

@endpush

