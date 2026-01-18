<?php

namespace App\Http\Controllers\Api;

use App\Models\Content;
use App\Models\ContentMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Responses\BaseResponse;

class ContentController extends Controller
{
    private $currentUser;

    function __construct()
    {
        $this->currentUser = auth('api')->user();
    }

    public function createPost(Request $request)
    {
        $request->validate([
            'activity_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov'
        ]);

        DB::beginTransaction();

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverImage = $request->file('cover_image');
            $coverPath = uploadImage($coverImage, 'activities_cover_image', null);
        }

        // Create content
        $content = Content::create([
            'user_id' => $this->currentUser->id,
            'activity_id' => $request->activity_id,
            'description' => $request->description,
            'type' => 'post',
            'cover_photo' => $coverPath,
            'is_published' => 1
        ]);

        // Store media files
        if ($request->hasFile('media')) {
            $media = $request->file('media');
            foreach ($media as $index => $media) {
                $path = uploadImage($media, 'content_media', null);
                ContentMedia::create([
                    'content_id' => $content->id,
                    'media_path' => $path
                ]);
            }
        }

        DB::commit();
        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Content created successfully');
    }

    public function createReel(Request $request)
    {
        $request->validate([
            'activity_id' => 'required|integer',
            'description' => 'nullable|string',
            'reel_media' => 'required|file',
        ]);

        DB::beginTransaction();
        $reelMedia = null;
        if ($request->hasFile('reel_media')) {
            $reel = $request->file('reel_media');
            $reelMedia = uploadImage($reel, 'reel_media', null);
        }

        // Create content
        $content = Content::create([
            'user_id' => $this->currentUser->id,
            'activity_id' => $request->activity_id,
            'description' => $request->description,
            'type' => 'reel',
            'reel_media' => $reelMedia,
            'is_published' => 1
        ]);

        // Attach categories
        if ($request->has('category_id')) {
            $content->categories()->attach($request->category_id);
        }

        DB::commit();
        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Reel created successfully');
    }
}
