<div class="mb-8 px-2 w-full md:w-1/2 lg:w-1/3">
    <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
        {{ trans('dashboard.manager_users_title') }}
    </h2>

    <div class="bg-white overflow-hidden shadow-md rounded-lg">
        <div class="px-4 py-5 sm:p-6" style="min-height: 212px;">
            <div class="flex justify-start">
                @foreach($users as $user)
                    <div class="w-1/4 text-center p-2">
                        <div class="rounded-lg w-full max-w-16 h-16 mx-auto">
                            <img width="80" src="{{ asset('img/types/' . Str::lower($user->role) . '.svg') }}" alt="">
                        </div>

                        <div class="text-2xl">{{ $user->total }}</div>
                        <div class="break-words text-sm xl:text-base">
                            @if($user->role == 'Manager')
                                {{ trans('dashboard.role_manager') }}
                            @elseif($user->role == 'Parent')
                                {{ trans('dashboard.role_parent') }}
                            @elseif($user->role == 'Principal')
                                {{ trans('dashboard.role_principal') }}
                            @elseif($user->role == 'User')
                                {{ trans('dashboard.role_user') }}
                            @elseif($user->role == 'Vendor')
                                {{ trans('dashboard.role_vendor') }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-gray-200 px-4 py-4 sm:px-6">
            <div class="text-sm leading-5">
                <a href="{{ route('users.index') }}"
                    class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                    {{ trans('dashboard.manager_users_link') }}
                </a>
            </div>
        </div>
    </div>
</div>
