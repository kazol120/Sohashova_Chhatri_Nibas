<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Frontend\Notice;

class NoticeController extends Controller
{
    public function index()
    {
        return view('backend.notice.create');
    }

    public function get(Request $request)
    {
        $perPage = (int) ($request->get('per_page', 10));
        $search  = trim($request->get('search', ''));
        $q = Notice::query();
        
        if ($search !== '') {
            $q->where('notice', 'like', '%' . $search . '%')
              ->orWhere('id', 'like', '%' . $search . '%');
        }
        
        $rows = $q->orderBy('id', 'desc')->paginate($perPage);
        return response()->json($rows);
    }

    public function store(Request $request)
    {
        $request->validate([
            'notice' => 'required|string|max:5000',
        ]);

        $notice = Notice::create([
            'notice' => $request->notice,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notice created successfully',
        ]);
    }

    public function update(Request $request, $id)
    {
        $row = Notice::findOrFail($id);
        
        $request->validate([
            'notice' => 'required|string|max:5000',
        ]);

        $row->notice = $request->notice;
        $row->save();

        return response()->json([
            'success' => true,
            'message' => 'Notice updated successfully',
            'data' => $row,
        ], 200);
    }

    public function destroy($id)
    {
        $row = Notice::findOrFail($id);
        $row->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notice deleted successfully',
        ]);
    }
}
