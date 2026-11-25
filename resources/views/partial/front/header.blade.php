
		@if ($gs->is_loader)
			<div class="preloader" id="preloader" style="background: url({{asset('assets/images/'.$gs->loader)}})">

			</div>
		@endif
    <style>
        .full-width{
            width: 90%;
        }
        @media (max-width: 768px){
            .full-width{
                width: 100%;
            }
        }
    </style>
    

		<section class="main-top-header">
			<div class="container-fluid full-width" >
				<div class="row">
					<div class="col-lg-12 align-self-center">
						<div class="top-header-content">
							<div class="left-content">
								<ul class="list">
									@foreach ($social_links as $social_link)
									<li>
										<a href="{{ $social_link->link}}" class="{{$social_link->name}}">
											<i class="{{$social_link->icon}}"></i>
										</a>
									</li>
									@endforeach
								</ul>
							</div>
							<div class="right-content">
								<div class="list">
									@if (!auth()->user())
										<li class="log-reg">
										<a href="{{ route('front.LogReg') }}">{{__('Login')}}</a>
											<span>
												/
											</span>
											<a href="{{ route('front.LogReg') }}">{{__('Register')}}</a>
										</li>
									@endif

									@if (auth()->user())
									     @php
											 $data = auth()->user();
										 @endphp
										<li class="user-profile">
											<div class="nav-item dropdown">
												<a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
													<img src="{{ $data->photo ? asset('assets/images/admin/'.$data->photo):asset('assets/images/noimage.png')}}" alt="Live News">
												</a>
												<ul class="dropdown-menu">
													<li><a class="dropdown-item" href="{{ route('user.dashboard') }}"><i class="far fa-user-circle"></i>{{__('Dashboard')}}</a></li>
													<li><a class="dropdown-item" href="{{ route('front.logout') }}"><i class="fas fa-sign-out-alt"></i>{{__('Logout')}}</a></li>
												</ul>
											</div>
										</li>
									@endif
									<li>
										<select id="languageChange">
											@foreach ($languages as $language)
													<option value="{{$language->id}}" {{ Session::has('language') ? ( Session::get('language') == $language->id ? 'selected' : '' ) : (front_language()->id == $language->id ? 'selected' : '') }}>{{$language->language}}</option>
												@endforeach
										</select>
									</li>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

    <style>
        .logo-img {
            width: 250px !important;
            height: 40px;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            text-align: left;
        }
        
        @media (max-width: 992px) {
            .logo-img {
                margin-left: 0 !important;
                width: 100% !important;
                
            }
        
        }

    </style>
		<section class="logo-header">
			<div class="container-fluid full-width" >
				<div class="row">
					<div class="col-12 col-md-8 d-flex justify-content-md-start align-items-center">
                        <a class="navbar-brand" href="{{ route('frontend.index') }}">
                            @php
                                $lid = front_language()->id;
                                $header_footer_logo = d_logo($lid);
                            @endphp
                    
                            @if (empty($gs->logo) && !empty($header_footer_logo[0]->header_logo))
                                <img src="{{ $header_footer_logo[0]->header_logo ? asset('assets/images/logo/' . $header_footer_logo[0]->header_logo) : asset('assets/front/images/logo.png') }}" alt="{{ $gs->site_name ?: $gs->title }}" class="logo-img">
                            @else
                                <img src="{{ $gs->logo ? asset('assets/images/logo/' . $gs->logo) : asset('assets/front/images/logo.png') }}" alt="{{ $gs->site_name ?: $gs->title }}" class="logo-img">
                            @endif
                        </a>
                    </div>


					<div class="col-lg-4 col-md-4 align-self-center">
						@php
							$header_ad = header_ads();
						@endphp
						@if($header_ad)
							@if ($header_ad->banner_type == 'image')
								<div class="add-banner">
									<a href="{{$header_ad->link}}" target="_blank" data-addid="{{$header_ad->id}}" id="headerAdd"><img src="{{asset('assets/images/addBanner/'.$header_ad->photo)}}" alt=""></a>
								</div>
							@else
								<div class="add-banner">
									{!! $header_ad->banner_code !!}
								</div>
							@endif
						@endif
					</div>
				</div>
			</div>
		</section>
		<style>
		    
            hr.only-pt {
                display: none;
                border: none; 
                border-top: 2px solid #ccc;
                padding-right: 15px;
                padding-left: 15px;
                margin-bottom: 0;
            }
            
            hr.only-pt2 {
                display: none;
                border: none; 
                border-top: 2px solid #ccc;
                padding-right: 15px;
                padding-left: 15px;
                margin-top: 0;
            }
            
            @media (max-width: 767.98px) {
                hr.only-pt2 {
                    display: block;
                }
                hr.only-pt {
                    display: block;
                }
            }


		</style>
        
        <div class="container-fluid full-width" >
    	    <hr class="only-pt">
    	</div>

		<div class="mainmenu-area">
			<div class="container-fluid full-width" >
				<div class="row">
					<div class="col-lg-12">
						<!--<div class="navsm">-->
						<!--	<div class="toogle-icon">-->
						<!--		<i class="fas fa-bars"></i>-->
						<!--	</div>-->

						<!--	<div class="serch-icon web-serch">-->
						<!--		<i class="fas fa-search"></i>-->
						<!--	</div>-->
						<!--	<div class="search-form-area">-->
						<!--		<form class="search-form" action="#">-->
						<!--			<input type="text" name="search" placeholder="Search what you want">-->
						<!--		</form>-->
						<!--	</div>-->
						<!--</div>-->
						<nav class="navbar navbar-expand-lg navbar-light menulg">
							<!--<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main_menu"-->
							<!--	aria-controls="main_menu" aria-expanded="false" aria-label="Toggle navigation">-->
							<!--	<span class="navbar-toggler-icon"></span>-->
							<!--</button>-->
							<style>
								.go-tab-c {
									display: none;
								}
								.go-tab-c.active {
									display: block !important;
								}
							</style>
							<div class="collapse navbar-collapse fixed-height d-none d-md-block" id="main_menu">
								<ul class="navbar-nav mr-auto" style="flex-direction: row;">
									@foreach ($categories as $category)
										@if ($loop->first)
											<li class="nav-item">
												<a class="nav-link " href="{{ route('frontend.category',$category->slug)}}">
													{{$category->title}}</a>
											</li>
										@endif

										@if ($category->child->count() > 0)
											<li class="nav-item dropdown mega-menu">
													<a class="nav-link dropdown-toggle" href="{{ route('frontend.category',$category->slug)}}">
														{{$category->title}}
												</a>
												<div class="dropdown-menu">
													<div class="row m-0 p-0">
														<div class="col-lg-2 p-0">
															<div class="nav flex-column">
																@foreach ($category->child->where('show_on_menu',1) as $child)
																	<a class="nav-link tab-link"  href="{{ route('frontend.postBySubcategory.details',[$category->slug,$child->slug])}}" data-tab="
																	#{{$child->id}}" >{{$child->title}} </a>
																@endforeach
															</div>
														</div>
													<div class="col-lg-10">
														<div class="tab-content">
															@foreach ($category->child->where('show_on_menu',1) as $child)
																<div id="{{$child->id}}" class="go-tab-c {{$loop->first ? 'active' : ''}}" >
															@php $menuPosts = subcat_recent_posts($child->id, 4); @endphp
															@if ($menuPosts->count() > 0)
																	<div class="row">
																@foreach ($menuPosts as $post)
																			<div class="col-lg-3 col-md-4 col-sm-6 pr-0">
																				<div class="single-news-menu">
																					<a href="{{ route('frontend.postBySubcategory.details',[$post->category->slug,$post->slug])}}">
																						<div class="content-wrapper">
																							<div class="img">
																								@if ($post->image_big || $post->rss_image)
																									<div class="tag" style="background:{{$child->color}}">
																										{{$child->title}}
																									</div>
																								@endif
																								@if ($post->image_big || $post->rss_image)
																									@if ($post->image_big)
																										<img src="{{asset('assets/images/post/'.$post->image_big)}}" alt="">
																									@endif
																									@if ($post->rss_image)
																										<img src="{{$post->rss_image}}" alt="">
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
																									<img src="{{asset('assets/images/nopic.png')}}" alt="">
																								@endif
																							</div>
																							<div class="inner-content">
																								<a href="{{ route('frontend.postBySubcategory.details',[$post->category->slug,$post->slug])}}">
																									<h4 class="title">
																										{{ strlen($post->title)>40 ? mb_substr($post->title,0,40,'utf-8').'...' : $post->title}}
																									</h4>
																									<ul class="post-meta">
																										<li>
																											<span href="#">
																												{{$post->createdAt()}}
																											</span>
																										</li>
																										<li>
																											<span>|</span>
																										</li>
																										<li>
																											<span href="#">
																												{{$post->admin->name}}
																											</span>
																										</li>
																									</ul>
																								</a>
																							</div>
																						</div>
																					</a>
																				</div>
																			</div>
																		@endforeach
																		</div>
																	@endif
																</div>
															@endforeach
														</div>
													</div>
													</div>
												</div>
											</li>
										@else
											@if ($loop->first)
											@else
												<li class="nav-item">
													<a class="nav-link" href="{{ route('frontend.category',$category->slug)}}">{{$category->title}}</a>
												</li>
											@endif
										@endif
									@endforeach

								</ul>
							</div>
							<div class="serch-icon-area">
								<p class="web-serch">
									<i class="fas fa-search"></i>
								</p>
							</div>
							<div class="search-form-area">
								<form class="search-form" action="{{ route('front.news_search') }}" method="get" data-track-submit="Search">
									<input type="text" name="search" placeholder="{{__('Search what you want')}}">
								</form>
							</div>
						</nav>
					</div>
				</div>
			</div>
		</div>

	
		<style>
            #main_menu ul.navbar-nav li {
                margin-right: 18px;
            }
        
            #main_menu ul.navbar-nav .nav-link {
                font-weight: bold;
            }
            
        </style>

		<div class="container-fluid full-width" >
		    <div class="collapse navbar-collapse fixed-height d-block d-md-none" id="main_menu">
                <ul class="navbar-nav flex-row flex-nowrap overflow-auto" style="white-space: nowrap; flex-direction:row">
                    @foreach ($categories as $category)
                        @if ($loop->first)
                            <li class="nav-item me-3">
                                <a class="nav-link active" href="{{ route('frontend.index') }}">
                                    {{ $category->title }}
                                </a>
                            </li>
                        @endif
            
                        @if ($category->child->count() > 0)
                            <li class="nav-item dropdown mega-menu me-3">
                                <a class="nav-link dropdown-toggle" href="{{ route('frontend.category', $category->slug) }}">
                                    {{ $category->title }}
                                </a>
                                <div class="dropdown-menu">
                                    <div class="row m-0 p-0">
                                        <div class="col-lg-2 p-0">
                                            <div class="nav flex-column">
                                                @foreach ($category->child->where('show_on_menu', 1) as $child)
                                                    <a class="nav-link tab-link"
                                                       href="{{ route('frontend.postBySubcategory.details', [$category->slug, $child->slug]) }}"
                                                       data-tab="#{{ $child->id }}">
                                                        {{ $child->title }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="tab-content">
                                                @foreach ($category->child->where('show_on_menu', 1) as $child)
                                                    <div id="{{ $child->id }}" class="go-tab-c {{ $loop->first ? 'active' : '' }}">
                                                        @php $menuPosts = subcat_recent_posts($child->id, 4); @endphp
                                                        @if ($menuPosts->count() > 0)
                                                            <div class="row">
                                                                @foreach ($menuPosts as $post)
                                                                    <div class="col-lg-3 col-md-4 col-sm-6 pr-0">
                                                                        <div class="single-news-menu">
                                                                            <a href="{{ route('frontend.postBySubcategory.details', [$post->category->slug, $post->slug]) }}">
                                                                                <div class="content-wrapper">
                                                                                    <div class="img">
                                                                                        @if ($post->image_big || $post->rss_image)
                                                                                            <div class="tag" style="background: {{ $child->color }}">
                                                                                                {{ $child->title }}
                                                                                            </div>
                                                                                        @endif
            
                                                                                        @if ($post->image_big)
                                                                                            <img src="{{ asset('assets/images/post/' . $post->image_big) }}" alt="">
                                                                                        @elseif ($post->rss_image)
                                                                                            <img src="{{ $post->rss_image }}" alt="">
                                                                                        @else
                                                                                            <img src="{{ asset('assets/images/nopic.png') }}" alt="">
                                                                                        @endif
            
                                                                                        @if ($post->post_type == 'audio')
                                                                                            <span class="vid-aud"><i class="fas fa-volume-up"></i></span>
                                                                                        @elseif ($post->post_type == 'video')
                                                                                            <span class="vid-aud"><i class="fas fa-video"></i></span>
                                                                                        @endif
                                                                                    </div>
                                                                                    <div class="inner-content">
                                                                                        <a href="{{ route('frontend.postBySubcategory.details', [$post->category->slug, $post->slug]) }}">
                                                                                            <h4 class="title">
                                                                                                {{ mb_strlen($post->title) > 40 ? mb_substr($post->title, 0, 40, 'utf-8') . '...' : $post->title }}
                                                                                            </h4>
                                                                                            <ul class="post-meta">
                                                                                                <li><span>{{ $post->createdAt() }}</span></li>
                                                                                                <li><span>|</span></li>
                                                                                                <li><span>{{ $post->admin->name }}</span></li>
                                                                                            </ul>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @elseif (!$loop->first)
                            <li class="nav-item me-3">
                                <a class="nav-link" href="{{ route('frontend.category', $category->slug) }}">
                                    {{ $category->title }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
		</div>

	<div class="container-fluid full-width" >
	    <hr class="only-pt2">
	</div>
		
		
		
		
		
		


