<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    protected $settingService;
    
    public function __construct(SettingService $settingService)
    {
        $this->middleware('auth');
        $this->middleware('permission:setting-index|setting-create|setting-edit|setting-delete');
        $this->settingService = $settingService;

    }

    public function index($slug)
    {
        $data['page_title'] = str_replace("_", " ", $slug);
        $data['fields'] = $this->settingService->getSettings($slug);
        $data['editData'] = $this->settingService->getSlugContents($slug);
        $data['slug'] = $slug;
        return view('backend.setting.edit', $data);
    }

    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'slug' => 'required',
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid)->withInput();
        }
//       return $request->allFiles();
        $this->settingService->createSetting($request);
        return redirect()->back()->with('success', 'Setting save successfully');
    }

}
