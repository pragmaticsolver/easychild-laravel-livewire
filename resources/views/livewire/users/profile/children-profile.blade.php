<div class="space-y-4 sm:mx-auto sm:w-full sm:max-w-md">
   @foreach($children as $child)
        @livewire('users.profile.child-setting', [
            'child' => $child
        ], key($child->uuid))
    @endforeach
</div>
