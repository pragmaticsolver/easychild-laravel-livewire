<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

trait HasAvatarFileUploader
{
    public function uploadImage($image, $model, $key = 'avatar')
    {
        $filename = $model->$key;

        if ($image) {
            $img = preg_replace('/^data:image\/\w+;base64,/', '', $image);
            $type = explode(';', $image)[0];

            $newExplode = explode('/', $type);

            if (count($newExplode) == 2) {
                $type = explode('/', $type)[1];

                $filename = Uuid::uuid4()->toString().".{$type}";

                while (Storage::disk('avatars')->exists($filename)) {
                    $filename = Uuid::uuid4()->toString().".{$type}";
                }

                Storage::disk('avatars')->put($filename, base64_decode($img));

                if ($model->$key) {
                    $path = storage_path('app/avatars').'/'.$model->$key;

                    if (File::exists($path)) {
                        File::delete($path);
                    }
                }
            } else {
                $this->dispatchBrowserEvent('croppie-reset-event-fired');
            }
        }

        return $filename;
    }
}
