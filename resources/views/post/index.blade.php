<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Posts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                @forelse ($posts as $post)
                    <div class="card p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="card-title"> {{ $post->title }}</h3>
                        <p class="mt-3 card-body"> {{ $post->body }}</p>
                    </div>
                @empty
                    <div class="card p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="card-title"> {{ _('no posts found') }}</h3>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
