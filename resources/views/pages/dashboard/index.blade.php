@extends('layouts.page')

@section('pageTitle')
    <x-h1title :page-title="trans('dashboard.title')"></x-h1title>
@endsection

@section('content')
    <div class="flex flex-wrap items-start -mx-2">
        <div class="mb-8 px-2 w-full md:w-1/2 xl:w-1/3">
            <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
                {{ trans('organizations.title') }}
            </h2>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6 flex items-center justify-start" style="min-height: 212px;">
                    <dl>
                        <dt class="text-sm leading-5 font-medium text-gray-500">
                            {{ trans('dashboard.total_org') }}
                        </dt>
                        <dd class="mt-1 text-3xl leading-9 font-semibold text-gray-900">
                            {{ $organizations }}
                        </dd>
                    </dl>
                </div>

                <div class="bg-gray-200 px-4 py-4 sm:px-6">
                    <div class="text-sm leading-5">
                        <a href="{{ route('organizations.index') }}"
                            class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                            {{ trans('dashboard.view_all') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- users list --}}
        @include('pages.dashboard.partials.userslist')
    </div>
@endsection
