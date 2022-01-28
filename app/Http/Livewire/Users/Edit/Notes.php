<?php

namespace App\Http\Livewire\Users\Edit;

use App\Http\Livewire\Component;
use App\Models\Note;
use App\Models\User;
use App\Notifications\NoteNotification;
use App\Traits\HasDeletesJobsAndNotification;
use Livewire\WithPagination;

class Notes extends Component
{
    use WithPagination, HasDeletesJobsAndNotification;

    public Note $note;
    public User $user;
    public $showModal;

    public function paginationView()
    {
        return 'vendor.livewire.pagination-links';
    }

    public function mount(User $user)
    {
        $this->user = $user;
        $this->resetNoteItem();

        $this->showModal = false;
    }

    public function rules()
    {
        $priorities = join(',', $this->priorities->keys()->all());

        return [
            'note.title' => 'required',
            'note.text' => 'nullable',
            'note.priority' => 'required|in:' . $priorities,
        ];
    }

    public function edit(Note $note)
    {
        $this->authorize('update', $note);

        $this->note = $note;
        $this->showModal = true;
    }

    public function getPrioritiesProperty()
    {
        return collect([
            'low' => trans('notes.priority.low'),
            'normal' => trans('notes.priority.normal'),
            'urgent' => trans('notes.priority.urgent'),
        ]);
    }

    public function submit()
    {
        $msg = trans('notes.create_success_msg');
        $isNewItem = true;

        if ($this->note->getKey()) {
            $isNewItem = false;
            $msg = trans('notes.update_success_msg');
        }

        $this->note->user_id = $this->user->id;

        if ($isNewItem || count($this->note->getDirty())) {
            $this->note->save();

            $msg = 'no update needed.';

            $this->sendNotification($isNewItem);
        }

        $this->resetNoteItem();
        $this->showModal = false;

        $this->emitMessage('success', $msg);
    }

    private function sendNotification($isNewItem)
    {
        $users = User::query()
            ->where('organization_id', $this->user->organization_id)
            ->where('id', '!=', auth()->id())
            // ->whereIn('role', ['Principal'])
            ->where('role', 'Principal')
            ->whereIn('id', function ($query) {
                $query->select('user_id')
                    ->from('group_user')
                    ->whereIn('group_id', function ($query) {
                        $query->select('group_id')
                            ->from('group_user')
                            ->where('user_id', $this->user->id);
                    });
            })
            ->pluck('id')
            ->all();

        if ($users && count($users)) {
            if ($isNewItem) {
                auth()->user()->jobs()->updateOrCreate([
                    'related_type' => Note::class,
                    'related_id' => $this->note->id,
                    'action' => NoteNotification::class,
                ], [
                    'user_ids' => $users,
                    'due_at' => now()->addMinutes(2),
                    'data' => [
                        'type' => 'create',
                    ],
                ]);
            } else {
                auth()->user()->jobs()->updateOrCreate([
                    'related_type' => Note::class,
                    'related_id' => $this->note->id,
                    'action' => NoteNotification::class,
                ], [
                    'user_ids' => $users,
                    'due_at' => now()->addMinutes(2),
                    'data' => [
                        'type' => 'update',
                    ],
                ]);
            }
        }
    }

    public function createNew()
    {
        $this->resetNoteItem(true);

        $this->showModal = true;
    }

    private function resetNoteItem($creatingNew = false)
    {
        if (($creatingNew && $this->note->getKey()) || (! $creatingNew)) {
            $this->note = Note::make([
                'priority' => 'normal',
                'user_id' => $this->user->id,
            ]);
        }
    }

    public function deleteNote(Note $note)
    {
        $this->authorize('delete', $note);

        $this->deleteRelatedNotification($note, NoteNotification::class);

        $note->delete();

        $this->resetNoteItem();

        $this->emitMessage('success', trans('contacts.delete_success_msg'));
    }

    public function render()
    {
        $notes = $this->user->notes()
            ->orderByDesc('priority')
            ->paginate(config('setting.perPage'));

        return view('livewire.users.edit.notes', compact('notes'));
    }
}
