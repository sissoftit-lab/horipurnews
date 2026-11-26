<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Language;
use App\Models\Advertisement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /** Columns the listing needs (skips the heavy `description` column). */
    private const LIST_COLUMNS = [
        'id', 'title', 'slug', 'category_id', 'language_id', 'post_type', 'image_big', 'rss_image',
        'admin_id', 'user_id', 'is_slider', 'is_trending', 'is_feature', 'slider_right', 'is_pending', 'created_at',
    ];

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /* ------------------------------------------------------------------
     |  Listing
     * ------------------------------------------------------------------ */

    public function index(Request $request)
    {
        $postTypes = ['article', 'video', 'audio', 'Trivia Quiz', 'Personality Quiz', 'Sorted List', 'rss'];

        return view('admin.post.index', compact('postTypes'));
    }

    /**
     * AJAX list: returns the rendered rows (admin.post._rows) plus paging info and status counts.
     * Filters: q, lang, category, type, state (approved|pending), from, to, per_page, page.
     */
    public function list(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $base = Post::query()
            ->where('status', 'true')
            ->where('schedule_post', 0)
            ->whereNull('schedule_post_date');

        if (!in_array($admin->role->name, ['admin', 'moderator'])) {
            $base->where('admin_id', $admin->id);
        }
        if ($request->filled('category')) {
            $base->where('category_id', $request->input('category'));
        } elseif ($request->filled('lang')) {
            $base->where('language_id', $request->input('lang'));
        }
        if ($request->filled('type')) {
            $base->where('post_type', $request->input('type'));
        }
        if ($request->filled('q')) {
            $term = addcslashes(trim($request->input('q')), '%_' . chr(92));
            $base->where('title', 'like', '%' . $term . '%');
        }
        if ($from = $this->validDate($request->input('from'))) {
            $base->where('created_at', '>=', $from . ' 00:00:00');
        }
        if ($to = $this->validDate($request->input('to'))) {
            $base->where('created_at', '<=', $to . ' 23:59:59');
        }

        // Tab counts follow every filter except the status tab itself.
        $counts = (clone $base)->toBase()
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(is_pending = 0),0) as approved, COALESCE(SUM(is_pending <> 0),0) as pending')
            ->first();

        if ($request->input('state') === 'pending') {
            $base->where('is_pending', '<>', 0);
        } elseif ($request->input('state') === 'approved') {
            $base->where('is_pending', 0);
        }

        $perPage = (int) $request->input('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 25;

        $posts = $base
            ->select(self::LIST_COLUMNS)
            ->with(['category:id,title,slug', 'language:id,language', 'admin:id,name', 'user:id,name'])
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'html'      => view('admin.post._rows', compact('posts'))->render(),
            'total'     => $posts->total(),
            'from'      => $posts->firstItem() ?? 0,
            'to'        => $posts->lastItem() ?? 0,
            'page'      => $posts->currentPage(),
            'last_page' => $posts->lastPage(),
            'counts'    => [
                'all'      => (int) $counts->total,
                'approved' => (int) $counts->approved,
                'pending'  => (int) $counts->pending,
            ],
        ]);
    }

    /** Top-level categories of a language, as JSON for the filter dropdown. */
    public function categories($id)
    {
        return response()->json(
            Language::findOrFail($id)->categories()->whereNull('parent_id')->get(['id', 'title'])
        );
    }

    /** Legacy HTML <option> list (still used by the user-side post page). */
    public function categoryFilter($id)
    {
        $datas = Language::findOrFail($id)->categories()->whereNull('parent_id')->get(['id', 'title']);
        $base = route('post.list');

        $output = '<option data-href="' . $base . '?category=&lang=' . (int) $id . '">All</option>';
        foreach ($datas as $data) {
            $output .= '<option data-href="' . $base . '?category=' . $data->id . '">' . e($data->title) . '</option>';
        }
        return $output;
    }

    public function edit($id)
    {
        $data = Post::findOrFail($id);

        $categories = Category::whereNull('parent_id')->get();
        $languages = Language::orderBy('id', 'desc')->get();

        switch ($data->post_type) {
            case 'article':
                $individual_posts = Advertisement::where('add_placement', 'individual_post')->where('status', 1)->get();
                return view('admin.article.edit', compact('categories', 'languages', 'data', 'individual_posts'));
            case 'video':
                return view('admin.video.edit', compact('categories', 'languages', 'data'));
            case 'Sorted List':
                return view('admin.shortlist.edit', compact('categories', 'languages', 'data'));
            case 'Trivia Quiz':
                return view('admin.quiz.edit', compact('categories', 'languages', 'data'));
            case 'Personality Quiz':
                return view('admin.pquiz.edit', compact('categories', 'languages', 'data'));
            default:
                return view('admin.audio.edit', compact('categories', 'languages', 'data'));
        }
    }

    public function view($id)
    {
        $data = Post::findOrFail($id);
        abort_unless($data->post_type == 'article', 404);

        return view('admin.article.view', compact('data'));
    }

    /* ------------------------------------------------------------------
     |  Single toggles
     * ------------------------------------------------------------------ */

    public function sliderChange($id)
    {
        return $this->toggle($id, 'is_slider', 'Slider Status Updated successfully!');
    }

    public function trendingChange($id)
    {
        return $this->toggle($id, 'is_trending', 'Breaking News Status Updated successfully!');
    }

    public function featureChange($id)
    {
        return $this->toggle($id, 'is_feature', 'Data Updated successfully!');
    }

    public function sliderright($id)
    {
        return $this->toggle($id, 'slider_right', 'Data Updated successfully!');
    }

    public function pendingChange($id)
    {
        return $this->toggle($id, 'is_pending', 'Pending Status Updated successfully!');
    }

    /* ------------------------------------------------------------------
     |  Bulk flag changes (one UPDATE query each)
     * ------------------------------------------------------------------ */

    public function sliderBulk(Request $request)         { return $this->bulkFlag($request, 'is_slider', 1); }
    public function removesliderBulk(Request $request)   { return $this->bulkFlag($request, 'is_slider', 0); }
    public function breakingBulk(Request $request)       { return $this->bulkFlag($request, 'is_trending', 1); }
    public function removebreakingBulk(Request $request) { return $this->bulkFlag($request, 'is_trending', 0); }
    public function featureBulk(Request $request)        { return $this->bulkFlag($request, 'is_feature', 1); }
    public function removefeatureBulk(Request $request)  { return $this->bulkFlag($request, 'is_feature', 0); }
    public function rightBulk(Request $request)          { return $this->bulkFlag($request, 'slider_right', 1); }
    public function removerightBulk(Request $request)    { return $this->bulkFlag($request, 'slider_right', 0); }

    /* ------------------------------------------------------------------
     |  Delete
     * ------------------------------------------------------------------ */

    public function delete($id)
    {
        $posts = Post::whereKey($id)->get();
        abort_if($posts->isEmpty(), 404);
        $this->destroyPosts($posts);

        return response()->json('Data Successfully Deleted');
    }

    public function bulkdelete(Request $request)
    {
        $ids = $this->parseIds($request);
        if ($ids) {
            // chunk to keep memory flat when many posts are selected
            foreach (array_chunk($ids, 50) as $chunk) {
                $this->destroyPosts(Post::whereIn('id', $chunk)->get());
            }
        }

        return $this->respond('Data Deleted successfully!');
    }

    /* ------------------------------------------------------------------
     |  Helpers
     * ------------------------------------------------------------------ */

    private function validDate($value): ?string
    {
        $d = is_string($value) ? \DateTime::createFromFormat('Y-m-d', $value) : false;

        return $d && $d->format('Y-m-d') === $value ? $value : null;
    }

    /** JSON for AJAX callers (the post list page), classic redirect-back for everything else. */
    private function respond(string $message)
    {
        return request()->expectsJson()
            ? response()->json(['message' => $message])
            : back()->with('success', $message);
    }

    private function toggle($id, string $field, string $message)
    {
        $post = Post::findOrFail($id);
        $post->update([$field => $post->$field == 1 ? 0 : 1]);

        return $this->respond($message);
    }

    private function parseIds(Request $request): array
    {
        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? $ids : explode(',', (string) $ids);

        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    private function bulkFlag(Request $request, string $field, int $value)
    {
        $ids = $this->parseIds($request);
        if ($ids) {
            Post::whereIn('id', $ids)->where($field, '!=', $value)->update([$field => $value]);
            // mass updates fire no model events, so invalidate explicitly
            news_cache()->postsChanged(Post::whereIn('id', $ids)->get(['id', 'category_id', 'subcategories_id', 'admin_id']));
        }

        return $this->respond('Data Updated successfully!');
    }

    /**
     * Delete posts together with their related rows and files.
     * Rows are removed inside a transaction; files are unlinked only after it commits.
     */
    private function destroyPosts($posts): void
    {
        $posts->load(['galleries', 'tquizs.answers', 'tresults', 'pquizs.answers', 'presults', 'sorts']);

        $files = [];
        $add = function (string $dir, $name) use (&$files) {
            if ($name) {
                $files[] = public_path($dir . '/' . $name);
            }
        };

        DB::transaction(function () use ($posts, $add) {
            foreach ($posts as $post) {
                $post->views()->delete();

                switch ($post->post_type) {
                    case 'audio':
                        $add('assets/audios', $post->audio);
                        break;
                    case 'video':
                        $add('assets/videos', $post->video);
                        break;
                    case 'Trivia Quiz':
                        foreach ($post->tquizs as $quiz) {
                            foreach ($quiz->answers as $answer) {
                                $add('assets/images/quizanswer', $answer->answer_photo);
                                $answer->delete();
                            }
                            $add('assets/images/quiz', $quiz->question_photo);
                            $quiz->delete();
                        }
                        foreach ($post->tresults as $result) {
                            $add('assets/images/quizresult', $result->result_photo);
                            $result->delete();
                        }
                        break;
                    case 'Personality Quiz':
                        foreach ($post->pquizs as $quiz) {
                            foreach ($quiz->answers as $answer) {
                                $add('assets/images/panswer', $answer->answer_photo);
                                $answer->delete();
                            }
                            $add('assets/images/pquiz', $quiz->question_photo);
                            $quiz->delete();
                        }
                        foreach ($post->presults as $result) {
                            $add('assets/images/presult', $result->result_photo);
                            $result->delete();
                        }
                        break;
                    case 'Sorted List':
                        foreach ($post->sorts as $sort) {
                            $add('assets/images/sort', $sort->item_photo);
                            $sort->delete();
                        }
                        break;
                }

                foreach ($post->galleries as $gallery) {
                    $add('assets/images/galleries', $gallery->photo);
                    $gallery->delete();
                }

                $add('assets/images/post', $post->image_big);
                $post->delete();
            }
        });

        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}
