<footer class="footer" id="footer">
    <div class="container-fluid footer-container">
        <div class="row footer-grid">
            <div class="col-md-6 col-lg-3">
                <div class="footer-widget about-widget">
                    <div class="footer-logo">
                        <a href="{{route('frontend.index')}}">
                            @php
                                $header_footer_logo = d_logo(front_language()->id);
                            @endphp

                            @if (empty($gs->footer_logo) && empty($gs->logo) && !empty($header_footer_logo[0]->footer_logo))
                                <img src="{{asset('assets/images/logo/'.$header_footer_logo[0]->footer_logo)}}" class="logo-img" alt="{{ $gs->site_name ?: $gs->title }}">
                            @else
                                <img src="{{asset('assets/images/logo/'.($gs->footer_logo ?: $gs->logo))}}" class="logo-img" alt="{{ $gs->site_name ?: $gs->title }}">
                            @endif
                        </a>
                    </div>
                    <div class="address">
                        @if ($gs->contact_address)<p><i class="fas fa-map-marker-alt"></i><span>{{ $gs->contact_address }}</span></p>@endif
                        @if ($gs->contact_email)<p><i class="fas fa-envelope"></i><a href="mailto:{{ $gs->contact_email }}">{{ $gs->contact_email }}</a></p>@endif
                        @if ($gs->contact_phone)<p><i class="fas fa-phone"></i><a href="tel:{{ $gs->contact_phone }}">{{ $gs->contact_phone }}</a></p>@endif
                    </div>
                    <div class="fotter-social-links">
                        <ul aria-label="{{__('Follow Us')}}">
                            @foreach ($social_links as $social_link)
                                <li>
                                    <a href="{{ $social_link->link}}" class="{{$social_link->name}}" title="{{$social_link->name}}" aria-label="{{$social_link->name}}">
                                        <i class="{{$social_link->icon}}"></i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="footer-widget info-link-widget">
                    <h4 class="title">
                        {{__('CATEGORIES')}}
                    </h4>
                    <ul class="link-list">
                        @foreach (footer_categories($default_language->id)->shuffle() as $category)
                            <li>
                                <a href="{{ route('frontend.category',$category->slug)}}">{{ $category->title}}</a>
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
                        @foreach (top_view_posts($top_views)->take(4) as $post)
                            <li>
                                <a class="post" href="{{ route('frontend.productDetails',[$post->id])}}">
                                    <span class="post-img">
                                        <img src="{{ $post->image_big ? asset('assets/images/post/'.$post->image_big) : $post->rss_image }}" alt="" loading="lazy">
                                    </span>
                                    <span class="post-details">
                                        <span class="post-title">{{ $post->title }}</span>
                                    </span>
                                </a>
                            </li>
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
                        @foreach (array_filter(array_map('trim', $tags)) as $tag)
                            <li>
                                <a href="{{ route('tag.search',$tag)}}">{{$tag}}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="copy-bg">
        <div class="container-fluid footer-container">
            <div class="copy-row">
                <div class="content">
                    <div class="content">
                        <p><a href="{{route('frontend.index')}}">{{$gs->copyright_text}}</a></p>
                    </div>
                </div>
                <div class="content footerMenu">
                    <ul class="nav">
                        @foreach (footer_pages() as $menu)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dynamic.page',$menu->slug)}}">{{ $menu->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
