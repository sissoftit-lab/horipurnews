<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ImageAlbum;

class GalleryController extends Controller
{
    public function view($id)
{
    $data['gallery'] = ImageAlbum::findOrFail($id);

    // dd($data);
    return view('frontend.gallery_view', $data);
}

}
