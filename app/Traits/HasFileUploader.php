<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasFileUploader
{
    public function uploadPdfFile($file, $model, $key = 'file')
    {
        $filename = $model->$key;

        if ($file) {
            $url = preg_replace('/^data:application\/pdf\/\w+;base64,/', '', $file);
            $type = explode(';', $file)[0];
            $type = explode('/', $type)[1];

            $dir = $model->uuid;
            $filename = Str::slug($model->title) . ".{$type}";

            $dirName = "app/{$key}s/{$dir}";
            Storage::disk($dirName)->put($filename, base64_decode($url));

            if ($model->$key) {
                $path = storage_path($dirName) . '/' . $model->$key;

                if (File::exists($path)) {
                    File::delete($path);
                }
            }
        }

        return $filename;
    }

    public function getAttachmentFileType($file)
    {
        $extension = $file->getClientOriginalExtension();

        $file_name = $file->getClientOriginalName();

        $valid_image_extensions = array("jpeg","jpg","png", "gift", "svg");

        $name_arr = explode(".", $file_name);
        if(count($name_arr) > 2) {
            $file_real_name = $name_arr[0];
            for ($i = 1; $i < count($name_arr) - 1; $i++)
            {
                $file_real_name = $file_real_name . '.' . $name_arr[$i];
            }

        } else {
            $file_real_name = $name_arr[0];
        }

        if(in_array(strtolower($extension), $valid_image_extensions)){
            $type = 'image';
        } else if(strtolower($extension) == 'pdf')
            $type = 'pdf';
        else {
            $type = 'file';
        }

        return [
            'file_name' => $file_real_name,
            'file_type' => $type
        ];
    }
}
