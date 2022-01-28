<?php

namespace App\Console\Commands;

use App\CustomNotification\DatabaseNotificationModel;
use App\Models\Organization;
use App\Models\ParentLink;
use App\Models\Schedule;
use App\Models\User;
use App\Notifications\NewParentSignupNotification;
use App\Notifications\UserAttendanceDealedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class UpgradeAppData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upgrade app database settings.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $this->upgradeParentChildData();

        // $this->addParentLoginFromChild();

        // $this->upgradeNotificationData();

        // $this->setChildLoginTokenForParent();

        // $this->updateParentsSettingsForDefaultMail();

        // $this->upgradeEatsOnsiteData();

        return 0;
    }

    private function updateParentsSettingsForDefaultMail()
    {
        $parents = User::query()
            ->where('role', 'Parent')
            ->get();

        foreach ($parents as $parent) {
            $settings = $parent->settings;

            if (! $settings) {
                $settings = ['mail' => true];
            }

            if (! Arr::has($settings, 'mail')) {
                $settings['mail'] = true;
            }

            $parent->update([
                'settings' => $settings,
            ]);
        }
    }

    private function upgradeNotificationData()
    {
        $notifications = DatabaseNotificationModel::query()
            ->whereIn('type', [
                UserAttendanceDealedNotification::class,
            ])
            ->whereHasMorph('related', Schedule::class)
            ->get();

        foreach ($notifications as $notification) {
            $schedule = $notification->related;
            $child = $schedule->user;

            $data = $notification->data;
            $data['target_child'] = $child->given_names;

            $notification->update([
                'data' => $data,
            ]);
        }
    }

    private function setChildLoginTokenForParent()
    {
        $users = User::query()
            ->where('role', 'User')
            ->get();

        foreach ($users as $user) {
            $firstParent = $user->parents()->first();

            if ($firstParent) {
                $token = $user->token;

                if ($token) {
                    $user->update([
                        'token' => null,
                    ]);

                    $firstParent->update([
                        'token' => $token,
                    ]);
                }
            }
        }
    }

    private function upgradeParentChildData()
    {
        $parents = User::query()
            ->where('role', 'Parent')
            ->get();

        foreach ($parents as $parent) {
            $parent->update([
                'organization_id' => null,
            ]);

            foreach ($parent->childrens as $child) {
                ParentLink::updateOrCreate([
                    'email' => $parent->email,
                    'child_id' => $child->id,
                ], [
                    'token' => Str::random(),
                    'linked' => true,
                    'created_at' => $parent->created_at,
                    'updated_at' => $parent->created_at,
                ]);
            }
        }
    }

    private function addParentLoginFromChild()
    {
        $users = User::query()
            ->where('role', 'User')
            ->get();

        foreach ($users as $user) {
            $parentsCount = $user->parents()->count();

            if (! $parentsCount) {
                $parentLink = ParentLink::firstOrCreate([
                    'email' => $user->email,
                    'child_id' => $user->id,
                ], [
                    'token' => Str::random(),
                ]);

                $user->update(['email' => null]);

                $parent = User::firstOrCreate(
                    [
                        'email' => $parentLink->email,
                    ],
                    [
                        'password' => $user->password,
                        'role' => 'Parent',
                        'token' => Str::random(100),
                    ]
                );

                $parent->childrens()->syncWithoutDetaching([$parentLink->child_id]);

                // $parent->notify(new NewParentSignupNotification($parent, $parentLink));
            }
        }
    }

    private function upgradeEatsOnsiteData()
    {
        Organization::chunk(5, function ($orgs) {
            foreach ($orgs as $org) {
                User::query()
                    ->where('organization_id', $org->id)
                    ->where('role', 'User')
                    ->chunk(10, function ($users) use ($org) {
                        foreach ($users as $user) {
                            $userSettings = $user->settings;

                            if (! $userSettings) {
                                $userSettings = [];
                            }

                            if (! Arr::has($userSettings, 'eats_onsite')) {
                                $userSettings['eats_onsite'] = $org->settings['eats_onsite'];
                            }

                            $options = ['breakfast', 'lunch', 'dinner'];
                            foreach ($options as $option) {
                                if (Arr::has($userSettings['eats_onsite'], $option) && $userSettings['eats_onsite'][$option]) {
                                    $userSettings['eats_onsite'][$option] = null;
                                }
                            }

                            $user->update([
                                'settings' => $userSettings,
                            ]);
                        }
                    });
            }
        });
    }
}
