<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UploadStorage;

class UploadStorageController extends Controller
{
    public function index()
    {
        return UploadStorage::latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|mimes:jpg,jpeg,png,webp,mp4,mov,avi|max:20480',
        ]);

        $media = [];
        foreach ($request->file('files') as $file) {
            $mimeType = $file->getMimeType();
            if (str_starts_with($mimeType, 'image/')) {
                $type = 'image';
            } elseif (str_starts_with($mimeType, 'video/')) {
                $type = 'video';
            } else {
                $type = 'unknown';
            }

            $path = $file->store('uploads', 'public');

            $media[] = UploadStorage::create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $mimeType,
                'type' => $type,
                'product_id' => null,
            ]);
        }

        return response()->json([
            'message' => 'file berhasil di upload',
            'data' => $media,
        ]);
    }

    public function assignToProduct(Request $request, $mediaId)
    {
        $request->validate([
            'product_id' => 'nullable | exist: products,id'
        ]);

        $media = UploadStorage::findOrFail($mediaId);
        $media->product_id = $request->product_id;
        $media->save();
        return response()->json([
            'message' => 'Media Berhasil ditambahkan',
            'data' => $media
        ]);
    }


    public function destroy($id)
    {
        $media = UploadStorage::findOrFail($id);
        $media->delete();

        return response()->json([
            'message' => 'Media berhasil dihapus'
        ]);
    }
}
