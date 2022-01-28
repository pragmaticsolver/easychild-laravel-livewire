<?php

namespace App\Http\Livewire\Notifications;

use App\CustomNotification\DatabaseNotificationModel;
use App\Http\Livewire\Component;
use App\Models\CalendarEvent;
use App\Models\Conversation;
use App\Models\Information;
use App\Models\Note;
use App\Models\Schedule;
use App\Notifications\AttendanceNotification;
use App\Traits\HasDownloadMethods;
use Illuminate\Support\Arr;

class Index extends Component
{
    use HasDownloadMethods;

    protected $listeners = [
        'refreshNotificationBox' => '$refresh',
    ];

    public $filters = [
        'attendances' => true,
        'messages' => false,
        'informations' => true,
        'notes' => false,
        'events' => false,
    ];

    protected $filterTypes = [
        'attendances' => Schedule::class,
        'messages' => Conversation::class,
        'informations' => Information::class,
        'notes' => Note::class,
        'events' => CalendarEvent::class,
    ];

    public function getAvailableFiltersProperty()
    {
        $disabledFilters = [];

        if (auth()->user()->isUser()) {
            $disabledFilters[] = 'notes';
        }

        return collect($this->filterTypes)->keys()
            ->reject(function ($value) use ($disabledFilters) {
                return in_array($value, $disabledFilters);
            })->all();
    }

    private function getEnabledTypes()
    {
        $types = [];

        $user = auth()->user();
        $settings = $user->settings;
        if (isset($settings['notification_filters'])) {
            $this->filters = $settings['notification_filters'];
        }
        foreach ($this->availableFilters as $filter) {
            if (Arr::has($this->filters, $filter) && $this->filters[$filter]) {
                array_push($types, $this->filterTypes[$filter]);
            }
        }

        return $types;
    }

    public function markAsRead(DatabaseNotificationModel $notification, $showNotification = true)
    {
        $usersList = auth()->user()->getMyNotifiableIdList();

        if (in_array($notification->notifiable_id, $usersList) && is_null($notification->read_at)) {
            $notification->markAsRead();

            if ($notification->related_type == Information::class) {
                $this->emit('notifications.information.marked-as-read');
            }

            $showNotification && $this->emitMessage('success', trans('notifications.mark-read-msg'));

            return;
        }

        $showNotification && $this->emitMessage('error', trans('notifications.mark-read-msg-error'));
    }

    public function navigateToRelatedResource(DatabaseNotificationModel $notification)
    {
        $this->markAsRead($notification, false);

        $related = $notification->related;

        if (! $related) {
            return;
        }

        $relatedRouteMap = [
            Note::class => 'routeToRelatedNote',
            Information::class => 'routeToRelatedInformation',
            Conversation::class => 'routeToRelatedConversation',
            CalendarEvent::class => 'routeToRelatedEvent',
        ][get_class($related)];

        $this->$relatedRouteMap($related);
    }

    private function routeToRelatedEvent(CalendarEvent $event)
    {
        return redirect()->route('calendar', [
            'date' => $event->from->format('Y-m-d'),
        ]);
    }

    private function routeToRelatedNote(Note $note)
    {
        return redirect()->route('users.edit', [
            'user' => $note->user,
            'type' => 'notes',
        ]);
    }

    private function routeToRelatedConversation(Conversation $conversation)
    {
        return redirect()->route('messages.index', [
            'conversation' => encrypt($conversation->id),
        ]);
    }

    private function routeToRelatedInformation(Information $information)
    {
        return redirect()->route('informations.index');
    }

    public function approveSchedule($scheduleUuid)
    {
        $schedule = Schedule::findByUUIDOrFail($scheduleUuid);
        $this->authorize('update', $schedule);

        $schedule->sendDealtNotificationToUser('approved');

        $this->markRelatedNotificationAsRead($schedule);
    }

    public function rejectSchedule($scheduleUuid)
    {
        $schedule = Schedule::findByUUIDOrFail($scheduleUuid);
        $this->authorize('update', $schedule);

        $schedule->sendDealtNotificationToUser('declined');

        $this->markRelatedNotificationAsRead($schedule);
    }

    private function markRelatedNotificationAsRead($schedule)
    {
        DatabaseNotificationModel::query()
            ->where('type', AttendanceNotification::class)
            ->whereHasMorph('related', Schedule::class)
            ->where('related_id', $schedule->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);
    }

    public function markAllNotificationsAsRead()
    {
        DatabaseNotificationModel::query()
            ->userNotification()
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        $this->emitMessage('success', trans('notifications.mark-all-read'));
    }

    public function removeReadNotifications()
    {
        DatabaseNotificationModel::query()
            ->userNotification()
            ->whereNotNull('read_at')
            ->delete();

        $this->emitMessage('success', trans('notifications.clear-read'));
    }

    public function updatedFilters()
    {
        $user = auth()->user();
        $settings = $user->settings;
        $settings['notification_filters'] = $this->filters;
        $user->update([
            'settings' => $settings,
        ]);
    }

    public function render()
    {
        $notifications = DatabaseNotificationModel::query()
            ->relatedTypes($this->getEnabledTypes())
            ->userNotification()
            ->where(function ($query) {
                $query->where('updated_at', '>=', now()->subDays(7)->format('Y-m-d H:i:s'))
                    ->orWhereNull('read_at');
            })
            ->get();

        // $notifications = auth()->user()->notifications()
        //     ->relatedTypes($this->getEnabledTypes())
        //     ->where(function ($query) {
        //         $query->where('updated_at', '>=', now()->subDays(7)->format('Y-m-d H:i:s'))
        //             ->orWhereNull('read_at');
        //     })
        //     ->get();

        // $unreadNotificationsCount = auth()->user()->notifications()->unread()->count();
        $unreadNotificationsCount = DatabaseNotificationModel::query()
            ->userNotification()
            // ->where(function ($query) {
            //     $query->where('updated_at', '>=', now()->subDays(7)->format('Y-m-d H:i:s'))
            //         ->orWhereNull('read_at');
            // })
            ->unread()->count();

        $this->dispatchBrowserEvent('update-unread-notifications-count', $unreadNotificationsCount);

        $notifications->load('related');

        return view('livewire.notifications.index', compact('notifications', 'unreadNotificationsCount'));
    }
}
