<?php

namespace App\Traits;

use App\CustomNotification\DatabaseNotificationModel;
use App\Models\CustomJob;

trait HasDeletesJobsAndNotification
{
    protected function deleteRelatedNotification($related, $notificationType)
    {
        CustomJob::query()
            ->whereHasMorph('related', get_class($related))
            ->where('related_id', $related->id)
            ->where('action', $notificationType)
            ->delete();
        DatabaseNotificationModel::query()
            ->where('type', $notificationType)
            ->whereHasMorph('related', get_class($related))
            ->where('related_id', $related->id)
            ->delete();
    }
}
