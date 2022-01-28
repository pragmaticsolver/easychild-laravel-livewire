<?php

namespace App\Traits;

use App\Actions\Authorizations\InformationFileDownloadAccess;
use App\Models\Information;
use App\Notifications\InformationAddedNotification;

trait HasDownloadMethods
{
    public function informationDownload($infoUuid)
    {
        if (! $information = InformationFileDownloadAccess::run($infoUuid)) {
            return $this->emitMessage('error', trans('informations.download_error'));
        }

        $user = auth()->user();

        if ($information->file) {
            $user->notifications()
                ->where('related_type', Information::class)
                ->where('related_id', $information->id)
                ->where('type', InformationAddedNotification::class)
                ->whereNull('read_at')
                ->get()
                ->markAsRead();

            $fileName = 'app/files/' . $information->uuid . '/' . $information->file;

            $this->emit('refreshNotificationBox');

            return response()->download(storage_path($fileName));
        }

        return $this->emitMessage('error', trans('informations.download_no_file'));
    }

    private function bkDownloadTest()
    {
        $user = auth()->user();
        $information = Information::findByUuid($infoUuid);

        if (! $information) {
            return $this->emitMessage('error', trans('informations.download_error'));
        }

        $rolesAllowToDownload = false;

        if ($user->role == 'Manager') {
            $rolesAllowToDownload = true;
        }

        if (in_array($user->role, ['Principal', 'User', 'Vendor'])) {
            if (in_array($user->role, $information->roles)) {
                $rolesAllowToDownload = true;
            }
        }

        if ($user->role == 'Parent') {
            if (in_array('User', $information->roles)) {
                $rolesAllowToDownload = true;
            }
        }

        if (! $rolesAllowToDownload) {
            return $this->emitMessage('error', trans('informations.download_error'));
        }

        $orgIds = [$user->organization_id];

        if ($user->isParent()) {
            $orgIds = Organization::query()
                ->whereIn('organizations.id', function ($query) {
                    $query->select('users.organization_id')
                        ->from('users')
                        ->where('users.role', 'User')
                        ->whereIn('users.id', function ($query) {
                            $query->select('parent_child.child_id')
                                ->from('parent_child')
                                ->where('parent_child.parent_id', auth()->id());
                        });
                })->pluck('organizations.id')->all();
        }

        if (! in_array($information->organization_id, $orgIds)) {
            return $this->emitMessage('error', trans('informations.download_error'));
        }
    }
}
