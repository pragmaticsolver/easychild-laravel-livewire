<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class MessageController extends Controller
{
    public function __invoke(Request $request)
    {
        $conversation = null;

        if ($request->has('conversation') && $request->conversation) {
            $conversation = decrypt($request->conversation);
        }

        if (auth()->user()->isAdmin()) {
            return redirect()->route('dashboard');
        }

        $title = trans('messages.title');

        return view('pages.messages', [
            'title' => $title,
            'conversation' => $conversation,
        ]);
    }

    public function attachmentURL($uuid)
    {
        $message = Message::findByUuid($uuid);
        $path = storage_path('app/attachments/' . $message['conversation_id'] . '/'.$message['uuid'].'/'. $message['attachment']);

        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);

        $type = File::mimeType($path);


        $response = Response::make($file, 200);

        $response->header("Content-Type", $type);

        return $response;
    }
}
