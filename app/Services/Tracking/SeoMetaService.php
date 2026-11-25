<?php

namespace App\Services\Tracking;

use Illuminate\Support\Str;

/**
 * Builds the SEO / social meta data for a page. Rendered by partial.front.seo-meta.
 */
class SeoMetaService
{
    public function post($post, $gs, $seo = null): array
    {
        $meta = $this->home($gs, $seo);

        $desc = $post->meta_tag
            ?: Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $post->description))), 200);
        $image = $post->image_big
            ? asset('assets/images/post/'.$post->image_big)
            : ($post->rss_image ?: $meta['image']);
        $url = route('frontend.productDetails', $post->id);

        $meta = array_merge($meta, [
            'title'       => $post->title,
            'description' => $desc ?: $meta['description'],
            'keywords'    => $post->tags ?: $meta['keywords'],
            'canonical'   => $url,
            'image'       => $image,
            'type'        => 'article',
        ]);

        $meta['json_ld'] = [[
            '@context'         => 'https://schema.org',
            '@type'            => 'NewsArticle',
            'headline'         => Str::limit($post->title, 110, ''),
            'description'      => $meta['description'],
            'image'            => [$image],
            'mainEntityOfPage' => $url,
            'datePublished'    => optional($post->created_at)->toAtomString(),
            'dateModified'     => optional($post->updated_at ?? $post->created_at)->toAtomString(),
            'author'           => ['@type' => 'Person', 'name' => optional($post->admin)->name ?: $meta['site_name']],
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => $meta['site_name'],
                'logo'  => ['@type' => 'ImageObject', 'url' => asset('assets/images/logo/'.$gs->logo)],
            ],
        ]];

        return $meta;
    }

    public function home($gs, $seo = null): array
    {
        $cfg   = config('tracking.seo');
        $site  = $gs->site_name ?: $gs->title;
        $title = $cfg['home_title'] ?: $gs->title;
        $desc  = $cfg['home_description']
            ?: Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $gs->footer_text))), 300);
        $image = asset('assets/images/logo/'.$gs->logo);
        $url   = url('/');

        return [
            'title'        => $title,
            'description'  => $desc,
            'keywords'     => $cfg['home_keywords'] ?: ($seo->meta_keys ?? null),
            'author'       => $gs->meta_author ?: $site,
            'canonical'    => $url,
            'robots'       => 'index, follow, max-image-preview:large, max-snippet:-1',
            'site_name'    => $site,
            'image'        => $image,
            'locale'       => $cfg['locale'],
            'twitter_site' => $cfg['twitter_site'],
            'type'         => 'website',
            'json_ld'      => [
                [
                    '@context' => 'https://schema.org',
                    '@type'    => 'NewsMediaOrganization',
                    'name'     => $site,
                    'url'      => $url,
                    'logo'     => $image,
                ],
                [
                    '@context' => 'https://schema.org',
                    '@type'    => 'WebSite',
                    'name'     => $site,
                    'url'      => $url,
                ],
            ],
        ];
    }
}
