<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Frontend\Gallery;
use Illuminate\Support\Facades\File;

class GalleryController extends Controller
{
    public function index()
    {
        return view('backend.gallery.gallery');
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,webp,gif,svg,jfif,heic,heif,avif,bmp|max:9096',
        ]);
        $path = public_path('gallery_image');
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $file = $request->file('image');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($path, $filename);
        $gallery = Gallery::create([
            'image' => $filename,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Created successfully',
        ]);
    }

    public function getluxuryroom(Request $request)
    {
        $perPage = (int) ($request->get('per_page', 10));
        $search  = trim($request->get('search', ''));
        $q = Gallery::query();
        if ($search !== '') {
            $q->where('id', 'like', '%' . $search . '%');
        }
        $rows = $q->orderBy('id', 'desc')->paginate($perPage);
        $rows->getCollection()->transform(function ($item) {
            $item->image_url = asset('gallery_image/' . $item->image);
            return $item;
        });
        return response()->json($rows);
    }

    public function update(Request $request, $id)
    {
        $row = Gallery::findOrFail($id);
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,webp,gif,svg,jfif,heic,heif,avif,bmp|max:9096',
        ]);
        $path = public_path('gallery_image');
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
        if ($request->hasFile('image')) {
            if (!empty($row->image)) {
                $old = $path . DIRECTORY_SEPARATOR . $row->image;
                if (File::exists($old)) {
                    File::delete($old);
                }
            }
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($path, $filename);
            $row->image = $filename;
        }
        $row->save();
        return response()->json([
            'success' => true,
            'message' => 'Updated successfully',
            'data' => [
                'id'        => $row->id,
                'image'     => $row->image,
                'image_url' => asset('gallery_image/' . $row->image),
            ],
        ], 200);
    }

    public function destroy($id)
    {
        $row = Gallery::findOrFail($id);
        $path = public_path('gallery_image');
        $old = $path . DIRECTORY_SEPARATOR . $row->image;
        if (File::exists($old)) File::delete($old);
        $row->delete();
        return response()->json([
            'success' => true,
            'message' => 'Deleted successfully',
        ]);
    }
}
