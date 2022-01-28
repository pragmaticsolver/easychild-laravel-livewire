<?php

namespace App\Providers;

use App\Models\CalendarEvent;
use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use App\Observers\CalendarEventObserver;
use App\Observers\GroupObserver;
use App\Observers\OrganizationObserver;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Organization::observe(OrganizationObserver::class);
        Group::observe(GroupObserver::class);
        User::observe(UserObserver::class);
        CalendarEvent::observe(CalendarEventObserver::class);

        Builder::macro('likeWhere', function ($field, $string) {
            if ($string) {
                $this->whereRaw("LOWER(`{$field}`) LIKE ?", [$string]);
            }

            return $this;
        });

        Builder::macro('orLikeWhere', function ($field, $string) {
            if ($string) {
                $this->orWhereRaw("LOWER(`{$field}`) LIKE ?", [$string]);
            }

            return $this;
        });

        Str::macro('bytesToHuman', function ($value) {
            $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];

            for ($i = 0; $value > 1024; $i++) {
                $value /= 1024;
            }

            return number_format($value, 2).' '.$units[$i];
        });
    }
}
