<?php

namespace App\Actions\User;

use App\Models\CustomJob;
use App\Models\ParentLink;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsObject;

class RemoveParentFromChild
{
    use AsObject;

    public function handle(ParentLink $parentLink)
    {
        $parent = User::query()
            ->where('email', $parentLink->email)
            ->first();

        if ($parent) {
            $this->clearLinkAndAttachedItems($parentLink, $parent);
        }

        CustomJob::query()
            ->where('related_type', ParentLink::class)
            ->where('related_id', $parentLink->id)
            ->delete();

        $parentLink->delete();
    }

    private function clearLinkAndAttachedItems(ParentLink $parentLink, User $parent)
    {
        $child = $parentLink->child;

        if (! $child) {
            return;
        }

        // clear parent cache for current child if it match
        $cacheKey = $parent->getCacheKey();
        $currentChildId = cache()->get($cacheKey);

        if ($currentChildId == $child->id) {
            Cache::forget($cacheKey);
        }

        $parent->childrens()->detach($child->id);
    }
}
