<?php

namespace App\CustomNotification;

use App\Models\CalendarEvent;
use App\Models\Conversation;
use App\Models\Information;
use App\Models\Note;
use App\Models\Schedule;
use App\Notifications\AttendanceNotification;
use App\Notifications\CalendarEventNotification;
use App\Notifications\InformationAddedNotification;
use App\Notifications\MessageNotification;
use App\Notifications\NoteNotification;
use App\Notifications\UserAttendanceDealedNotification;
use Illuminate\Notifications\Notification;
use RuntimeException;

class DatabaseChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function send($notifiable, Notification $notification)
    {
        return $notifiable->routeNotificationFor('database', $notification)->create(
            $this->buildPayload($notifiable, $notification)
        );
    }

    /**
     * Get the data for the notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array
     *
     * @throws \RuntimeException
     */
    protected function getData($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toDatabase')) {
            return is_array($data = $notification->toDatabase($notifiable))
                                ? $data : $data->data;
        }

        if (method_exists($notification, 'toArray')) {
            return $notification->toArray($notifiable);
        }

        throw new RuntimeException('Notification is missing toDatabase / toArray method.');
    }

    /**
     * Build an array payload for the DatabaseNotification Model.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array
     */
    protected function buildPayload($notifiable, Notification $notification)
    {
        $payload = [
            'id' => $notification->id,
            'data' => $this->getData($notifiable, $notification),
            'read_at' => null,
        ];

        $extraPayload = $this->getRelatedModelDetail($notification);

        foreach ($extraPayload as $key => $value) {
            $payload[$key] = $value;
        }

        return $payload;
    }

    protected function getRelatedModelDetail(Notification $notification)
    {
        $class = get_class($notification);

        $typeMaps = [
            InformationAddedNotification::class => Information::class,
            NoteNotification::class => Note::class,
            MessageNotification::class => Conversation::class,
            AttendanceNotification::class => Schedule::class,
            UserAttendanceDealedNotification::class => Schedule::class,
            CalendarEventNotification::class => CalendarEvent::class,
        ];

        $possiblePropertyName = [
            InformationAddedNotification::class => 'information',
            NoteNotification::class => 'note',
            MessageNotification::class => 'conversation',
            AttendanceNotification::class => 'schedule',
            UserAttendanceDealedNotification::class => 'schedule',
            CalendarEventNotification::class => 'event',
        ][$class];

        return [
            'type' => $class,
            'related_type' => $typeMaps[$class],
            'related_id' => $notification->$possiblePropertyName->id,
        ];
    }
}
