<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;

class GalleryController extends BaseController
{
    /**
     * Danh sách album của CLB
     */
    public function index($clubId)
    {
        $albums = GalleryAlbum::where('club_id', $clubId)
            ->withCount('images')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'albums' => $albums
        ]);
    }

    /**
     * Chi tiết 1 album + ảnh
     */
    public function show($albumId)
    {
        $album = GalleryAlbum::with('images')->findOrFail($albumId);

        return response()->json($album);
    }

    /**
     * Thêm ảnh vào album
     */
    public function store($clubId, Request $request)
    {
        $this->validate($request, [
            'album_id' => 'required|exists:gallery_albums,id',
            'title' => 'required',
            'image_url' => 'required',
        ]);

        $img = GalleryImage::create([
            'club_id' => $clubId,
            'album_id' => $request->album_id,
            'title' => $request->title,
            'description' => $request->description,
            'image_url' => $request->image_url,
        ]);

        return response()->json($img);
    }

    /**
     * Xóa ảnh
     */
    public function destroy($id)
    {
        $img = GalleryImage::find($id);

        if (!$img) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $img->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
