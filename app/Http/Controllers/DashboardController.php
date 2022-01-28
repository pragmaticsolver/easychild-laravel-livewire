<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Information;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        $organization = null;
        $users = null;
        $organizations = null;
        $openingTime = null;
        $schedule = 0;

        switch ($user->role) {
            case 'Admin':
                list($users, $organizations) = $this->getAdminData();
                break;

            case 'Manager':
                list($users, $organization, $openingTime) = $this->getManagerData($user);
                break;

            case 'Principal':
                break;

            case 'Vendor':
                break;

            case 'Contractor':
                break;

            default:
                list($organization, $schedule) = $this->getUserData($user);
        }

        if ($user->isContractor()) {
            $organizations = $user->organizations;

            return view('pages.dashboard.contractor-dashboard', compact('user', 'organizations'));
        }

        if ($user->isPrincipal()) {
            list($timeSchedules, $prescenseSchedule, $mealPlans) = $user->getPrincipalDashboardDetail();

            list($informations, $messages) = $this->getInformationAndMessages();

            // dd($mealPlans);

            return view('pages.dashboard.principal-dashboard', compact('user', 'timeSchedules', 'prescenseSchedule', 'mealPlans', 'informations', 'messages'));
            // return view('pages.dashboard.principal', compact('user'));
        }

        if ($user->isManager()) {
            list($timeSchedules, $prescenseSchedule, $mealPlans) = $user->getManagerDashboardDetail();

            list($informations, $messages) = $this->getInformationAndMessages();

            return view('pages.dashboard.manager-dashboard', compact('user', 'timeSchedules', 'prescenseSchedule', 'mealPlans', 'informations', 'messages', 'organization', 'users', 'openingTime'));
        }

        if ($user->isVendor()) {
            $title = $user->organization->name;

            return view('pages.livewire', [
                'livewire' => 'vendor.dashboard',
                'title' => $title,
            ]);
        }

        if ($user->isUser()) {
            list($informations, $messages) = $this->getInformationAndMessages();

            return view('pages.dashboard.user-dashoard', compact('user', 'users', 'organizations', 'organization', 'schedule', 'openingTime', 'informations', 'messages'));
        }

        if ($user->isParent()) {
            return view('pages.dashboard.parent-dashoard');
        }

        return view('pages.dashboard.index', compact('user', 'users', 'organizations', 'organization', 'schedule', 'openingTime'));
    }

    private function getInformationAndMessages()
    {
        $user = auth()->user();

        $informations = Information::query()
            ->where('informations.creator_id', '!=', $user->id)
            ->where('organization_id', $user->organization_id)
            ->when(in_array($user->role, ['User', 'Principal', 'Vendor']), function ($query) use ($user) {
                $query->whereJsonContains('roles', $user->role);
            })
            ->whereNotIn('informations.id', function ($query) use ($user) {
                $query->select('related_id')
                    ->from('notifications')
                    ->where('related_type', Information::class)
                    ->where('notifiable_id', $user->id)
                    ->whereNotNull('read_at');
            })
            ->latest('created_at')
            ->limit(3)
            ->get();

        $messages = Conversation::query()
            ->withThreads()
            ->withLastMessage(false)
            ->withLastReadAt()
            ->latest('updated_at')
            ->get();

        // foreach ($messages as $message) {
        //     if (! $message->last_read_at) {
        //         $message->last_read_at = now()->subMonth()->format('Y-m-d');
        //     }
        // }

        $messages->loadCount('unreadMessages');

        $messages = $messages->where('unread_messages_count', '>', 0)->take(3);
        if ($messages->count()) {
            $messages->load('lastMessage.sender');
        }

        return [$informations, $messages];
    }

    private function getAdminData()
    {
        $users = User::query()
            ->select('role', DB::raw('count(*) as total'))
            ->whereNotIn('role', ['Parent', 'Admin'])
            ->groupBy('role')
            ->get();

        $organizations = Organization::count();

        return [
            $users,
            $organizations,
        ];
    }

    private function getManagerData($user)
    {
        $users = User::query()
            ->select('role', DB::raw('count(*) as total'))
            ->where('role', '!=', 'Parent')
            ->where('organization_id', $user->organization_id)
            ->groupBy('role')
            ->get();

        $organization = $user->organization;

        $openingTime = null;
        if ($organization) {
            $openingTimes = collect($organization->settings['opening_times']);

            $openingTime = $openingTimes->where('key', now()->dayOfWeek - 1)
                ->first();

            if (! empty($openingTime)) {
                $openingTime['start'] = Carbon::parse($openingTime['start'])->format(config('setting.format.time'));
                $openingTime['end'] = Carbon::parse($openingTime['end'])->format(config('setting.format.time'));
            }
        }

        return [
            $users,
            $organization,
            $openingTime,
        ];
    }

    private function getUserData($user)
    {
        $organization = $user->organization;
        $schedule = $user->schedules()
            ->where('date', now()->format('Y-m-d'))
            ->first();

        return [
            $organization,
            $schedule,
        ];
    }
}
