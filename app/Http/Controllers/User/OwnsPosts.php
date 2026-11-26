<?php

namespace App\Http\Controllers\User;

use App\Models\Post;

trait OwnsPosts
{
    /** A post written by the logged-in user; anyone else's id gets a 404. */
    private function ownPost($id): Post
    {
        return Post::where('user_id', auth()->id())->findOrFail($id);
    }
}
