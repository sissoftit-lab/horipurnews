
<style>
    .address p{
        color: #fff;
        font-size: 14px;
        padding: 3px;
        margin: 0px;   
    }
    .full-width{
            width: 90%;
        }
    @media (max-width: 768px){
        .full-width{
            width: 100%;
        }
    }
</style>
<footer class="footer" id="footer">
    <div class="container-fluid full-width" >
        <div class="row">

        </div>
        <div class="row">
            <div class="col-md-6 col-lg-3">
                <div class="footer-widget about-widget">
                    <div class="footer-logo">
                        <a href="{{route('frontend.index')}}">
                            @php
								$lid = front_language()->id;
							@endphp
							
                            @php
                                $header_footer_logo = d_logo($lid);
                            @endphp
                            
                            @if (empty($gs->footer_logo) && empty($gs->logo) && !empty($header_footer_logo[0]->footer_logo))
                                <img src="{{$header_footer_logo[0]->footer_logo ? asset('assets/images/logo/'.$header_footer_logo[0]->footer_logo) : asset('assets/front/images/logo.png')}}" 
                                width="300" alt="{{ $gs->site_name ?: $gs->title }}"  >
                            @else
                                <img src="{{asset('assets/images/logo/'.($gs->footer_logo ?: $gs->logo))}}" width="300" alt="{{ $gs->site_name ?: $gs->title }}">
                            @endif
                        </a>
                    </div>
                    <div class="address">
                        @if ($gs->contact_address)<p>{{ $gs->contact_address }}</p>@endif
                        @if ($gs->contact_email)<p>{{ __('Mail') }}: {{ $gs->contact_email }}</p>@endif
                        @if ($gs->contact_phone)<p>{{ __('Phone') }}: {{ $gs->contact_phone }}</p>@endif
                    </div>
                    <div class="social-links">
                        <h4 class="title">
                            {{__('Follow Us')}}:
                        </h4>
                    </div>
                </div>
                <div class="fotter-social-links">
                    <ul>
                        @foreach ($social_links as $social_link)
                            <li>
                                <a href="{{ $social_link->link}}" class="{{$social_link->name}} mb-2">
                                    <i class="{{$social_link->icon}}"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="footer-widget info-link-widget ilw1">
                    <h4 class="title">
                        {{__('CATEGORIES')}}
                    </h4>
                    <ul class="link-list">
                        @foreach ($default_language->categories()->inRandomOrder()->get() as $category)  
                            <li>
                                <a href="{{ route('frontend.category',$category->slug)}}">
                                    <span>
                                        {{ $category->title}}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="footer-widget blog-widget">
                    <h4 class="title">
                        {{__('Popular Post')}}
                    </h4>
                    
                    <ul class="post-list">
                        @foreach (top_view_posts($top_views) as $post)
                            @php
                            @endphp
                          @if ($post) 
                            <li>
                                <div class="post">
                                    <div class="post-img">
                                        @if ($post->image_big)
                                            <div class="img">
                                                <img src="{{asset('assets/images/post/'.$post->image_big)}}" alt="">
                                            </div>
                                        @else 
                                            <div class="img">
                                                <img src="{{$post->rss_image}}" alt="">
                                            </div>
                                        @endif
                                    </div>
                                    <div class="post-details">
                                        <a href="{{ route('frontend.productDetails',[$post->id])}}">
                                            <h4 class="post-title">
                                                {{ strlen($post->title)>30 ? mb_substr($post->title,0,30,'utf-8').'...' : $post->title}}
                                            </h4>
                                        </a>
                                    </div>
                                </div>
                            </li>
                          @endif
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="footer-widget tags-widget">
                    <h4 class="title">
                        {{__('TAGS')}}
                    </h4>
                    <ul class="tag-list">
                        @foreach ($tags as $tag)
                            <li>
                                <a href="{{ route('tag.search',$tag)}}">
                                    {{$tag}}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="copy-bg">
        <div class="container-fluid full-width">
            <div class="row">
                <div class="col-lg-8">
                    <div class="content">
                        <div class="content">
                            <p><a href="{{route('frontend.index')}}">{{$gs->copyright_text}}</a>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="content">
                        <div class="content footerMenu">
                            <ul class="nav">
                                @php
                                    $footer_menus = \App\Models\Page::where('placement','footer')->where('status',1)->get();
                                @endphp
                                @foreach ($footer_menus as $menu)
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('dynamic.page',$menu->slug)}}">{{ $menu->title }}</a>
                                    </li>
                                @endforeach
                              </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

