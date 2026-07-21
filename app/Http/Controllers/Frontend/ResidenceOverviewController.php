<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Frontend\ResidenceOverview;

class ResidenceOverviewController extends Controller
{
    public function index()
    {
        return view('backend.residenceoverview.residenceoverview');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'img_back'    => 'required|file|mimes:jpg,jpeg,png,webp,gif,svg,jfif,heic,heif,avif,bmp|max:4096',
            'img_front'   => 'required|file|mimes:jpg,jpeg,png,webp,gif,svg,jfif,heic,heif,avif,bmp|max:4096',
        ], [
            'title.required'     => 'Title is required.',
            'img_back.required'  => 'Back image is required.',
            'img_front.required' => 'Front image is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $imgBackName = null;
        $imgFrontName = null;

        // Back Image Path
        $backPath = public_path('residence_back_image');
        if (!file_exists($backPath)) {
            mkdir($backPath, 0777, true);
        }

        // Front Image Path
        $frontPath = public_path('residence_front_image');
        if (!file_exists($frontPath)) {
            mkdir($frontPath, 0777, true);
        }

        // Upload Back Image
        if ($request->hasFile('img_back')) {
            $file = $request->file('img_back');
            $imgBackName = time().'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
            $file->move($backPath, $imgBackName);
        }

        // Upload Front Image
        if ($request->hasFile('img_front')) {
            $file = $request->file('img_front');
            $imgFrontName = time().'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
            $file->move($frontPath, $imgFrontName);
        }

        $overview = ResidenceOverview::create([
            'title'       => $request->title,
            'description' => $request->description,
            'img_back'    => $imgBackName,
            'img_front'   => $imgFrontName,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Residence overview created successfully.',
            'data'    => $overview,
        ]);
    }

    public function getrelaxplace(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $search = trim($request->get('search', ''));

        $query = ResidenceOverview::query();

        if ($search !== '') {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $data = $query->latest()->paginate($perPage);

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $overview = ResidenceOverview::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'img_back'    => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,svg,jfif,heic,heif,avif,bmp|max:4096',
            'img_front'   => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,svg,jfif,heic,heif,avif,bmp|max:4096',
        ], [
            'title.required' => 'Title is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $backPath = public_path('residence_back_image');
        $frontPath = public_path('residence_front_image');

        if (!file_exists($backPath)) {
            mkdir($backPath, 0777, true);
        }

        if (!file_exists($frontPath)) {
            mkdir($frontPath, 0777, true);
        }

        if ($request->hasFile('img_back')) {
            if ($overview->img_back && file_exists($backPath . '/' . $overview->img_back)) {
                @unlink($backPath . '/' . $overview->img_back);
            }

            $file = $request->file('img_back');
            $imgBackName = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($backPath, $imgBackName);
            $overview->img_back = $imgBackName;
        }

        if ($request->hasFile('img_front')) {
            if ($overview->img_front && file_exists($frontPath . '/' . $overview->img_front)) {
                @unlink($frontPath . '/' . $overview->img_front);
            }

            $file = $request->file('img_front');
            $imgFrontName = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($frontPath, $imgFrontName);
            $overview->img_front = $imgFrontName;
        }

        $overview->title = $request->title;
        $overview->description = $request->description;
        $overview->save();

        return response()->json([
            'status'  => true,
            'message' => 'Residence overview updated successfully.',
            'data'    => $overview,
        ]);
    }

    public function destroy($id)
    {
        $overview = ResidenceOverview::findOrFail($id);

        $backPath = public_path('residence_back_image');
        $frontPath = public_path('residence_front_image');

        if ($overview->img_back && file_exists($backPath . '/' . $overview->img_back)) {
            @unlink($backPath . '/' . $overview->img_back);
        }

        if ($overview->img_front && file_exists($frontPath . '/' . $overview->img_front)) {
            @unlink($frontPath . '/' . $overview->img_front);
        }

        $overview->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Residence overview deleted successfully.',
        ]);
    }
}
