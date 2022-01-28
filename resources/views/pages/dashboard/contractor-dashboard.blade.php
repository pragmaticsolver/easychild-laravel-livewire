@extends('layouts.page')

@section('pageTitle')
    <x-h1title :page-title="trans('dashboard.title')"></x-h1title>
@endsection

@section('content')
    <div class="flex flex-wrap items-start -mx-2">
        {{-- Organization detail --}}
        @foreach($organizations as $organization)
            <div class="mb-8 px-2 w-full md:w-1/2 lg:w-1/3">
                <div class="bg-white overflow-hidden shadow-md rounded-lg">
                    <div class="px-4 py-5 sm:p-6 flex flex-col items-center justify-center" style="min-height: 212px;">
                        <div class="w-full flex items-center">
                            <dl class="flex-grow pr-4">
                                <dt class="text-xl leading-8 font-semibold text-gray-900">
                                    {{ $organization->name }}
                                </dt>
                            </dl>

                            <div
                                class="flex-shrink-0"
                                x-data="window.userAvatarFunc('{{ auth()->user()->organization_avatar }}', 'org-avatar')"
                                x-init="onInit()"
                            >
                                <img width="90" :src="visibleAvatar" alt="{{ auth()->user()->organization ? auth()->user()->organization->name : 'Org Avatar' }}">
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-200 px-4 py-4 sm:px-6">
                        <div class="text-sm leading-5">
                            <a
                                href="#" class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150"
                                x-on:click.prevent="$dispatch('impersonation-impersonate-organization', '{{ $organization->uuid }}')"
                            >
                                {{ trans('impersonations.org_impersonate') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
