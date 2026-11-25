<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Language;
use App\Models\Advertisement;
use Datatables;
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
        return view('admin.post.index');
    }

    public function datatables(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        // Single query builder: paging/search happen in SQL instead of loading every post into memory.
        $query = Post::query()
            ->select(self::LIST_COLUMNS)
            ->with(['category:id,title,slug', 'language:id,language', 'admin:id,name', 'user:id,name'])
            ->where('status', 'true')
            ->where('schedule_post', 0)
            ->whereNull('schedule_post_date')
            ->orderByDesc('id');

        if (!in_array($admin->role->name, ['admin', 'moderator'])) {
            $query->where('admin_id', $admin->id);
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        } elseif ($request->filled('lang')) {
            $query->where('language_id', $request->input('lang'));
        }

        return Datatables::of($query)
            ->addColumn('action', function (Post $data) {
                $links = [];
                $links[] = '<a href="' . route('frontend.postBySubcategory.details', [$data->category->slug, $data->slug]) . '" target="_blank"> <i class="fa fa-info-circle" aria-hidden="true"></i> View on Frontend</a>';
                if ($data->post_type != 'rss') {
                    $links[] = '<a href="' . route('post.edit', $data->id) . '"> <i class="fas fa-edit"></i> Edit</a>';
                }
                $links[] = $this->toggleLink('post.sliderChange', $data->id, $data->is_slider != 0, 'Add Into Slider', 'Remove From Slider');
                $links[] = $this->toggleLink('post.trendingChange', $data->id, $data->is_trending != 0, 'Add Into Breaking', 'Remove Breaking');
                $links[] = $this->toggleLink('post.feature', $data->id, $data->is_feature != 0, 'Add into Feature', 'Remove Feature');
                $links[] = $this->toggleLink('post.sliderright', $data->id, $data->slider_right != 0, 'Add into sliderRight', 'Remove sliderRight');
                $links[] = '<a href="' . route('post.pendingChange', $data->id) . '"><i class="fa fa-file"></i> ' . ($data->is_pending == 0 ? 'Make Post Pending' : 'Make Post Approve') . '</a>';
                $links[] = '<a href="javascript:;" data-href="' . route('post.delete', $data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i> Delete</a>';

                return '<div class="godropdown"><button class="go-dropdown-toggle"> Actions<i class="fas fa-chevron-down"></i></button><div class="action-list">' . implode('', $links) . '</div></div>';
            })
            ->addColumn('checkbox', function (Post $data) {
                return '<input type="checkbox" class="form-check-input m-0 p-0 postCheck" value="' . $data->id . '">';
            })
            ->editColumn('category_id', function (Post $data) {
                return $data->category_id ? '<span class="badge badge-primary">' . e($data->category->title) . '</span>' : '';
            })
            ->editColumn('language_id', function (Post $data) {
                return $data->language_id ? '<span class="badge badge-info">' . e($data->language->language) . '</span>' : '';
            })
            ->addColumn('is_approve', function (Post $data) {
                return $data->is_pending != 0
                    ? '<span class="badge badge-warning text-white">pending</span>'
                    : '<span class="badge badge-success">approve</span>';
            })
            ->editColumn('post_type', function (Post $data) {
                return $data->post_type ? '<span class="badge badge-secondary">' . e($data->post_type) . '</span>' : '';
            })
            ->editColumn('image_big', function (Post $data) {
                if ($data->post_type == 'rss') {
                    $src = $data->rss_image ?: url('assets/images/nopic.png');
                } else {
                    $src = $data->image_big ? url('assets/images/post/' . $data->image_big) : url('assets/images/nopic.png');
                }
                return '<img src="' . e($src) . '" alt="Image" loading="lazy">';
            })
            ->editColumn('created_at', function (Post $data) {
                return $data->created_at->toFormattedDateString();
            })
            ->editColumn('admin_id', function (Post $data) {
                if ($data->admin_id != 0) {
                    return e($data->admin->name);
                }
                return $data->user_id != 0 ? e($data->user->name) : 'User Deleted';
            })
            ->rawColumns(['checkbox', 'image_big', 'category_id', 'language_id', 'action', 'is_approve', 'post_type', 'created_at', 'admin_id'])
            ->toJson();
    }

    public function categoryFilter($id)
    {
        $datas = Language::findOrFail($id)->categories()->whereNull('parent_id')->get(['id', 'title']);
        $base = route('post.datatables');

        $output = '<option data-href="' . $base . '?category=&lang=' . (int) $id . '">All</option>';
        foreach ($datas as $data) {
            $output .= '<option data-href="' . $base . '?category=' . $data->id . '">' . e($data->title) . '</option>';
        }
        return $output;
    }

    /* ------------------------------------------------------------------
     |  Edit / view
     * ------------------------------------------------------------------ */

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

        return back()->with('success', 'Data Deleted successfully!');
    }

    /* ------------------------------------------------------------------
     |  Helpers
     * ------------------------------------------------------------------ */

    private function toggleLink(string $route, int $id, bool $active, string $addLabel, string $removeLabel): string
    {
        return '<a href="' . route($route, $id) . '"><i class="fa fa-' . ($active ? 'minus' : 'plus') . '"></i> ' . ($active ? $removeLabel : $addLabel) . '</a>';
    }

    private function toggle($id, string $field, string $message)
    {
        $post = Post::findOrFail($id);
        $post->update([$field => $post->$field == 1 ? 0 : 1]);

        return back()->with('success', $message);
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
            site_cache_bump(); // mass updates fire no model events, so bust the frontend cache manually
        }

        return back()->with('success', 'Data Updated successfully!');
    }

    /**
     * Delete posts together with their related rows and files.
     * Rows are removed inside a transaction; files are unlinked only after it commits.
     */
    private function destroyPosts($posts): void
    {
        $posts->load(['tquizs.answers', 'tresults', 'pquizs.answers', 'presults', 'sorts']);

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
