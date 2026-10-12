<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Frontend\Slider;
use Illuminate\Support\Facades\File;

class SliderController extends Controller
{
    public function index()
    {
        return view('backend.slider.slider');
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,webp,gif,svg,jfif,heic,heif,avif,bmp|max:9096',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        $path = public_path('slider_image');
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $file = $request->file('image');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($path, $filename);
        $slider = Slider::create([
            'image' => $filename,
            'title' => $request->title,
            'description' => $request->description,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Slider image added successfully',
        ]);
    }

    public function getSliders(Request $request)
    {
        $perPage = (int) ($request->get('per_page', 10));
        $search  = trim($request->get('search', ''));
        $q = Slider::query();
        if ($search !== '') {
            $q->where('title', 'like', '%' . $search . '%')
              ->orWhere('id', 'like', '%' . $search . '%');
        }
        $rows = $q->orderBy('id', 'desc')->paginate($perPage);
        $rows->getCollection()->transform(function ($item) {
            $item->image_url = asset('slider_image/' . $item->image);
            return $item;
        });
        return response()->json($rows);
    }

    public function update(Request $request, $id)
    {
        $row = Slider::findOrFail($id);
        $request->validate([
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,svg,jfif,heic,heif,avif,bmp|max:9096',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        $path = public_path('slider_image');
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
        $row->title = $request->title;
        $row->description = $request->description;
        $row->save();
        return response()->json([
            'success' => true,
            'message' => 'Slider updated successfully',
            'data' => [
                'id'        => $row->id,
                'image'     => $row->image,
                'image_url' => asset('slider_image/' . $row->image),
                'title'     => $row->title,
                'description'=> $row->description,
            ],
        ], 200);
    }

    public function destroy($id)
    {
        $row = Slider::findOrFail($id);
        $path = public_path('slider_image');
        $old = $path . DIRECTORY_SEPARATOR . $row->image;
        if (File::exists($old)) File::delete($old);
        $row->delete();
        return response()->json([
            'success' => true,
            'message' => 'Slider deleted successfully',
        ]);
    }
}
