@forelse ($posts as $post)
    @php
        $isRss   = $post->post_type == 'rss';
        $img     = $isRss
            ? ($post->rss_image ?: url('assets/images/nopic.png'))
            : ($post->image_big ? url('assets/images/post/' . $post->image_big) : url('assets/images/nopic.png'));
        $author  = $post->admin_id != 0
            ? $post->admin->name
            : ($post->user_id != 0 ? $post->user->name : __('User Deleted'));
        $pending = $post->is_pending != 0;
        $flags   = array_filter([
            'Slider'   => $post->is_slider,
            'Breaking' => $post->is_trending,
            'Feature'  => $post->is_feature,
            'Right'    => $post->slider_right,
        ]);
    @endphp
    <tr data-id="{{ $post->id }}">
        <td class="pp-col-check">
            <label class="pp-check"><input type="checkbox" class="postCheck" value="{{ $post->id }}"><span></span></label>
        </td>
        <td>
            <div class="pp-post">
                <img class="pp-thumb" src="{{ $img }}" alt="" loading="lazy">
                <div class="pp-post-body">
                    <a class="pp-title" href="{{ route('frontend.postBySubcategory.details', [$post->category->slug, $post->slug]) }}" target="_blank" title="{{ $post->title }}">{{ $post->title }}</a>
                    <div class="pp-meta">
                        @if ($post->category_id)<span class="pp-tag pp-tag-cat">{{ $post->category->title }}</span>@endif
                        @if ($post->language_id)<span class="pp-tag pp-tag-lang">{{ $post->language->language }}</span>@endif
                        @if ($post->post_type)<span class="pp-tag pp-tag-type">{{ $post->post_type }}</span>@endif
                        @foreach ($flags as $label => $on)<span class="pp-tag pp-tag-flag">{{ $label }}</span>@endforeach
                    </div>
                </div>
            </div>
        </td>
        <td class="pp-col-author">
            <div class="pp-author">
                <span class="pp-avatar">{{ mb_strtoupper(mb_substr($author, 0, 1)) }}</span>
                <span class="pp-author-name">{{ $author }}</span>
            </div>
        </td>
        <td class="pp-col-status">
            <span class="pp-pill {{ $pending ? 'pp-pill-warn' : 'pp-pill-ok' }}">{{ $pending ? __('Pending') : __('Approved') }}</span>
        </td>
        <td class="pp-col-date">
            <div class="pp-date">{{ $post->created_at->format('M d, Y') }}</div>
            <div class="pp-time">{{ $post->created_at->format('h:i A') }}</div>
        </td>
        <td class="pp-col-actions">
            <div class="pp-actions">
                <a class="pp-icon-btn" href="{{ route('frontend.postBySubcategory.details', [$post->category->slug, $post->slug]) }}" target="_blank" title="{{ __('View on Frontend') }}"><i class="fa fa-external-link-alt"></i></a>
                @unless ($isRss)
                    <a class="pp-icon-btn" href="{{ route('post.edit', $post->id) }}" title="{{ __('Edit') }}"><i class="fas fa-edit"></i></a>
                @endunless
                <div class="pp-more">
                    <button type="button" class="pp-icon-btn pp-more-toggle" title="{{ __('More') }}"><i class="fas fa-ellipsis-v"></i></button>
                    <div class="pp-menu">
                        <a href="#" class="pp-act" data-url="{{ route('post.sliderChange', $post->id) }}"><i class="fa fa-{{ $post->is_slider ? 'minus' : 'plus' }}"></i> {{ $post->is_slider ? __('Remove From Slider') : __('Add Into Slider') }}</a>
                        <a href="#" class="pp-act" data-url="{{ route('post.trendingChange', $post->id) }}"><i class="fa fa-{{ $post->is_trending ? 'minus' : 'plus' }}"></i> {{ $post->is_trending ? __('Remove Breaking') : __('Add Into Breaking') }}</a>
                        <a href="#" class="pp-act" data-url="{{ route('post.feature', $post->id) }}"><i class="fa fa-{{ $post->is_feature ? 'minus' : 'plus' }}"></i> {{ $post->is_feature ? __('Remove Feature') : __('Add Into Feature') }}</a>
                        <a href="#" class="pp-act" data-url="{{ route('post.sliderright', $post->id) }}"><i class="fa fa-{{ $post->slider_right ? 'minus' : 'plus' }}"></i> {{ $post->slider_right ? __('Remove Slider Right') : __('Add Into Slider Right') }}</a>
                        <a href="#" class="pp-act" data-url="{{ route('post.pendingChange', $post->id) }}"><i class="fa fa-file"></i> {{ $pending ? __('Make Post Approve') : __('Make Post Pending') }}</a>
                        <div class="pp-menu-sep"></div>
                        <a href="#" class="pp-del pp-danger" data-url="{{ route('post.delete', $post->id) }}" data-title="{{ $post->title }}"><i class="fas fa-trash-alt"></i> {{ __('Delete') }}</a>
                    </div>
                </div>
            </div>
        </td>
    </tr>
@empty
    <tr class="pp-empty-row">
        <td colspan="6">
            <div class="pp-empty">
                <div class="pp-empty-icon"><i class="far fa-newspaper"></i></div>
                <h5>{{ __('No posts found') }}</h5>
                <p>{{ __('Try changing the search or filters.') }}</p>
            </div>
        </td>
    </tr>
@endforelse
