<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HostelRule;

class HostelRuleController extends Controller
{
    public function index()
    {
        return view('backend.hostel_rule.index');
    }

    public function getRules(Request $request)
    {
        $perPage = (int) ($request->get('per_page', 10));
        $search  = trim($request->get('search', ''));

        $q = HostelRule::query();

        if ($search !== '') {
            $q->where('description', 'like', '%' . $search . '%')
              ->orWhere('icon', 'like', '%' . $search . '%');
        }

        $rows = $q->orderBy('id', 'asc')->paginate($perPage);

        return response()->json($rows);
    }

    public function store(Request $request)
    {
        $request->validate([
            'icon'        => 'nullable|string|max:100',
            'description' => 'required|string',
        ]);

        $rule = HostelRule::create([
            'icon'        => $request->icon ?: 'ti ti-file-text',
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rule created successfully',
            'data'    => $rule,
        ]);
    }

    public function update(Request $request, $id)
    {
        $rule = HostelRule::findOrFail($id);

        $request->validate([
            'icon'        => 'nullable|string|max:100',
            'description' => 'required|string',
        ]);

        $rule->icon        = $request->icon ?: 'ti ti-file-text';
        $rule->description = $request->description;
        $rule->save();

        return response()->json([
            'success' => true,
            'message' => 'Rule updated successfully',
            'data'    => $rule,
        ]);
    }

    public function destroy($id)
    {
        $rule = HostelRule::findOrFail($id);
        $rule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rule deleted successfully',
        ]);
    }
}
