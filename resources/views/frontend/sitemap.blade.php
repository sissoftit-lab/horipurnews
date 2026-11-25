<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
    <url>
        <loc>https://livenews.asia/</loc>
        <lastmod>{{ date('Y-m-d')}}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.00</priority>
    </url>

    @foreach ($blog_post as $post)
        <url>
            <loc>{{ route('frontend.productDetails', [$post->id]) }}</loc>
            <lastmod>{{ gmdate('Y-m-d\TH:i:s\Z', strtotime($post->updated_at)) }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.80</priority>
        </url>
    @endforeach

</urlset>