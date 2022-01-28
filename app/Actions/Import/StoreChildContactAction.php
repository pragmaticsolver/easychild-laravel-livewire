<?php

namespace App\Actions\Import;

use App\Models\Contact;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsObject;

class StoreChildContactAction
{
    use AsObject;

    public function handle($contact = [])
    {
        Log::info($contact);

        Contact::create($contact);
    }
}
