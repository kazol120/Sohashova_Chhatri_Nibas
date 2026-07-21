<?php
namespace App\Services;

use App\Models\Backend\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingService{
    public function getSettings($slug)
    {
        // Generate the dynamic JSON file path based on slug
        $jsonPath = "json/{$slug}.json";
        // Check if the file exists
        if (Storage::exists($jsonPath)) {
            // Read and decode the JSON file
            $jsonContent = Storage::get($jsonPath);
            $fields = json_decode($jsonContent, true); // Decoded as an associative array
        } else {
            $fields = []; // Fallback if file doesn't exist
        }
        return $fields;

    }
    public function getSettingBySlug($slug)
    {
        return Setting::where('slug',$slug)->first();
    }
    public function getSlugContents($slug)
    {
        $existSlug = $this->getSettingBySlug($slug);
        if ($existSlug){
            return json_decode($existSlug->contents);
        }
    }
    
    public function createSetting(Request $request)
    {
        try{
            $existSlug = $this->getSettingBySlug($request->slug);
            $contents = $request->except('_token','slug');

            // If the setting already exists, decode the existing contents
            $existingContents = $existSlug ? json_decode($existSlug->contents, true) : [];

            // Handle file uploads
            foreach ($request->allFiles() as $key => $file) {
                if ($file->isValid()) {
                    // Remove the existing file if it exists
                    if (isset($existingContents[$key]) && file_exists(public_path($existingContents[$key]))) {
                        unlink(public_path($existingContents[$key]));
                    }

                    // Generate a unique filename
                    $fileName = $key.'.png';
                    // Store the new file in the public directory
                    $file->move(public_path('/'), $fileName);
                    // Update the contents array with the new file path
                    $contents[$key] = $fileName;
                }
            }

            // Merge existing contents with the updated contents
            if($existingContents)
            {
                $contents = array_merge($existingContents, $contents);
            }

            if($existSlug){
                // Encode the contents into JSON format
                $in['contents'] = json_encode($contents);
                $existSlug->update($in);
            }else{
                $in['title'] = str_replace("_", " ", $request->slug);
                $in['slug'] = $request->slug;
                // Encode the contents into JSON format
                $in['contents'] = json_encode($contents);
                Setting::create($in);
            }
            return $in;
        }catch (\Exception $e){
            return $e->getMessage();
        }

    }
}
