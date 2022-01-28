<?php

namespace App\Http\Livewire\Informations;

use App\Http\Livewire\Component;
use App\Models\Information;
use App\Notifications\InformationAddedNotification;
use App\Traits\HasDeletesJobsAndNotification;
use App\Traits\HasDownloadMethods;
use Illuminate\Support\Facades\Storage;

class Index extends Component
{
    use HasDownloadMethods, HasDeletesJobsAndNotification;

    protected $listeners = [
        'notifications.information.marked-as-read' => '$refresh',
    ];

    public function paginationView()
    {
        return 'vendor.livewire.pagination-links';
    }

    public function download($infoUuid)
    {
        return $this->informationDownload($infoUuid);
    }

    public function deleteInformation($uuid)
    {
        $information = Information::findByUUIDOrFail($uuid);

        $this->authorize('delete', $information);

        $this->deleteRelatedNotification($information, InformationAddedNotification::class);

        if (Storage::disk('pdfs')->exists($information->uuid)) {
            Storage::disk('pdfs')->deleteDirectory($information->uuid);
        }

        $information->delete();

        $this->emitMessage('success', trans('informations.delete_success'));
    }

    public function render()
    {
        $informations = Information::query()
            ->forUser()
            ->withLastNotification()
            ->latest('created_at')
            ->paginate(config('setting.perPage'));

        return view('livewire.informations.index', compact('informations'));
    }
}
