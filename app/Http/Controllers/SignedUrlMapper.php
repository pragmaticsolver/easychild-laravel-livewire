<?php

namespace App\Http\Controllers;

use App\Actions\Authorizations\CalendarEventsFileDownloadAccess;
use App\Actions\Authorizations\InformationFileDownloadAccess;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SignedUrlMapper extends Controller
{
    public function signed(Request $request)
    {
        if (! $this->hasFileAccess($request->model, $request->uuid)) {
            return response()->noContent(Response::HTTP_FORBIDDEN);
        }

        return response()->file($request->path);
    }

    private function hasFileAccess($modelName, $uuid)
    {
        $modelMethod = [
            'information' => InformationFileDownloadAccess::class,
            'events' => CalendarEventsFileDownloadAccess::class,
        ][$modelName];

        return $modelMethod::run($uuid);
    }
}
