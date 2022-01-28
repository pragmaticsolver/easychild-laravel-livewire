@php
    $visibleItem = 'breakfast';

    $lunchTime = now()->startOfDay()->addHours(11);
    $dinnerTime = now()->startOfDay()->addHours(16);

    if (now() > $lunchTime) {
        $visibleItem = 'lunch';
    }

    if (now() > $dinnerTime) {
        $visibleItem = 'dinner';
    }
@endphp

<div
    class="relative"
    x-data="{
        visibleItem: '{{ $visibleItem }}',
        availableMeals: ['breakfast', 'lunch', 'dinner'],
        moveNext() {
            let currentIndex = this.availableMeals.indexOf(this.visibleItem)
            currentIndex++

            if (currentIndex >= this.availableMeals.length) {
                currentIndex = 0
            }

            this.visibleItem = this.availableMeals[currentIndex]
        },
        movePrev() {
            let currentIndex = this.availableMeals.indexOf(this.visibleItem)
            currentIndex--

            if (currentIndex < 0) {
                currentIndex = this.availableMeals.length - 1
            }

            this.visibleItem = this.availableMeals[currentIndex]
        }
    }"
>
    <a class="z-10 text-indigo-500 absolute top-4 left-2" href="#" x-on:click.prevent="movePrev()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
    </a>

    <a class="z-10 text-indigo-500 absolute top-4 right-2" href="#" x-on:click.prevent="moveNext()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
    </a>

    {{ $slot }}
</div>
