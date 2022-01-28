<?php

namespace App\Http\Livewire\Messages;

use App\Http\Livewire\Component;
use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\InstantMessagePushNotification;
use App\Notifications\MessageNotification;
use App\Traits\HasConversationParticipants;
use Illuminate\Support\Facades\Notification;
use App\Traits\HasFileUploader;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class Send extends Component
{
    use HasConversationParticipants, HasFileUploader, WithFileUploads;

    public $conversationId;
    public $message;
    public $file;

    protected $listeners = [
        'userChangedTheThread',
        'change-file'=> 'changeEvent'
    ];

    public function userChangedTheThread($id)
    {
        $this->conversationId = $id;
    }

    public function changeEvent($value)
    {
       $this->message = $value;
    }

    public function sendMessage()
    {
        $this->validate([
            'file' => 'nullable|max:2048',
        ]);

        if (! $this->message || gettype($this->message) !== 'string') {
            return $this->emitMessage('error', trans('messages.message_send.cannot_be_empty'));
        }

        $user = auth()->user();
        $conversation = Conversation::find($this->conversationId);

        $canSendMessage = $user->canTalkToConversation($conversation);
        $messageSent = false;

        if ($canSendMessage) {
            if($this->file)
            {
                $file_info = $this->getAttachmentFileType($this->file);
                $fileName = $this->file->getClientOriginalName();

                $message = Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $user->id,
                    'body' => $this->message,
                    'type' => $file_info['file_type'],
                    'attachment' => $fileName
                ]);


                $dirName = "attachments/". $conversation->id . '/' . $message->uuid;
                $this->file->storeAs($dirName, $fileName);

                Storage::deleteDirectory('/livewire-tmp');
            } else {
                $message = Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $user->id,
                    'body' => $this->message,
                ]);
            }

            $messageSent = true;

            $this->normalMessageSent($conversation, $message, $user);
        } else {
            if (! $conversation->private && $user->isParent()) {
                $currentChild = $user->parent_current_child;
                $userGroup = $currentChild->userGroup();

                if (! $userGroup) {
                    $this->message = null;

                    return $this->emitMessage('error', trans('messages.message_send.no_group_assigned'));
                }

                $messageSent = true;

                $conversationToSendMsg = Conversation::firstOrCreate([
                    'title' => 'messages.roles.principals',
                    'alt_title' => $currentChild->full_name,
                    'organization_id' => $currentChild->organization_id,
                    'group_id' => null,
                    'creator_id' => $currentChild->id,
                    'private' => true,
                    'chat_type' => 'single-group-user',
                    'participation_id' => $userGroup->id,
                ]);

                if($this->file)
                {
                    $file_info = $this->getAttachmentFileType($this->file);
                    $fileName = $this->file->getClientOriginalName();

                    $message = Message::create([
                        'conversation_id' => $conversationToSendMsg->id,
                        'sender_id' => $user->id,
                        'body' => $this->message,
                        'type' => $file_info['file_type'],
                        'attachment' => $fileName
                    ]);

                    $dirName = "attachments/". $conversationToSendMsg->id . '/' . $message->uuid;


                    $this->file->storeAs($dirName, $fileName);

                    Storage::deleteDirectory('/livewire-tmp');
                } else {
                    $message = Message::create([
                        'conversation_id' => $conversationToSendMsg->id,
                        'sender_id' => $user->id,
                        'body' => $this->message,
                    ]);
                }

                $this->privateMessageSent($conversationToSendMsg, $message, $user);
            }

            if (! $conversation->private && $user->isPrincipal()) {
                $currentGroup = $user->principal_current_group;

                if ($currentGroup) {
                    $availableConversation = Conversation::query()
                        ->where('organization_id', $user->organization_id)
                        ->where('chat_type', 'users')
                        ->where('group_id', $currentGroup->id)
                        ->first();

                    if ($availableConversation) {
                        $messageSent = true;
                        if($this->file)
                        {
                            $file_info = $this->getAttachmentFileType($this->file);
                            $fileName = $this->file->getClientOriginalName();

                            $message = Message::create([
                                'conversation_id' => $availableConversation->id,
                                'sender_id' => $user->id,
                                'body' => $this->message,
                                'type' => $file_info['file_type'],
                                'attachment' => $fileName
                            ]);

                            $dirName = "attachments/". $availableConversation->id . '/' . $message->uuid;

                            $this->file->storeAs($dirName, $fileName);

                            Storage::deleteDirectory('/livewire-tmp');
                        } else {
                            $message = Message::create([
                                'conversation_id' => $availableConversation->id,
                                'sender_id' => $user->id,
                                'body' => $this->message,
                            ]);
                        }

                        $this->groupUsersMessageSent($availableConversation, $message, $user);
                    }
                }
            }
        }

        if (! $messageSent && ! $canSendMessage) {
            $this->emitMessage('error', trans('messages.message_send.cannot_send_to_thread'));
        }
    }

    private function privateMessageSent($conversation, $message, $user)
    {
        $this->message = null;
        $this->emitMessage('success', trans('messages.message_send.sent_success_private'));
        $this->emit('refreshChatSidebarThreads');

        $this->sendPushNotification($conversation, $message, $user);
    }

    private function groupUsersMessageSent($conversation, $message, $user)
    {
        $this->message = null;
        $this->emitMessage('success', trans('messages.message_send.sent_success_group_users'));
        $this->emit('refreshChatSidebarThreads');

        $this->sendPushNotification($conversation, $message, $user);
    }

    private function normalMessageSent($conversation, $message, $user)
    {
        // if ($user->isParent()) {
        //     $currentChild = $user->parent_current_child;
        //     if ($currentChild->active_thread && $currentChild->active_thread != $conversation->id) {
        //         $this->emit('refreshChatSidebarThreads');
        //     }
        // } else {
        if ($user->active_thread && $user->active_thread != $conversation->id) {
            $this->emit('refreshChatSidebarThreads');
        }
        // }

        $this->emitMessage('success', trans('messages.message_send.sent_success'));
        $this->emit('addNewMessageToThread', $message->id, $conversation->id);

        $this->message = null;
        $this->sendPushNotification($conversation, $message, $user);
    }

    private function sendPushNotification($conversation, $message, $excludeUser)
    {
        $users = $this->getConversationParticipants($conversation)
            ->where('users.id', '!=', $excludeUser->id)
            ->get();

        if ($users && $users->count()) {
            $userIds = $users->pluck('id')->all();
            $customJob = auth()->user()->jobs()->firstOrCreate([
                'related_type' => Conversation::class,
                'related_id' => $conversation->id,
                'action' => MessageNotification::class,
            ], [
                'user_ids' => $userIds,
                'due_at' => now()->addMinutes(1),
                'data' => [
                    'sender_id' => $excludeUser->id,
                    'messages' => [],
                    'messages_ids' => [],
                ],
            ]);

            $jobData = $customJob->data;
            $jobData['messages'][] = $message->body;
            $jobData['messages_ids'][] = $message->id;

            $customJob->update([
                'user_ids' => $userIds,
                'due_at' => now()->addMinutes(1),
                'data' => $jobData,
            ]);

            $title = trans('messages.notifications.message_received');
            $body = [
                'model_id' => $conversation->id,
                'message_id' => $message->id,
                'text1' => trans('messages.notifications.received_msg_from', ['name' => $excludeUser->full_name]),
                'text2' => $message->body,
            ];

            Notification::send($users, new InstantMessagePushNotification($title, $body, route('messages.index'), 'conversation-message'));
        }
    }

    public function render()
    {
        return view('livewire.messages.send');
    }
}
