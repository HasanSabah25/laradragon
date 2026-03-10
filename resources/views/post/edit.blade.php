<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Post') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('post.update', $post) }}">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title"
                                :value="old('title', $post->title)" />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>


                        <div>
                            <x-input-label for="body" :value="__('Body')" />
                            <x-text-input id="body" class="block mt-1 w-full" type="text" name="body"
                                :value="old('body', $post->body)" />
                            <x-input-error :messages="$errors->get('body')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <x-text-input id="status" class="block mt-1 w-full" type="text" name="status"
                                :value="old('status', (string) $post->status->value)" />
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>



                        <div class="flex items-center justify-end mt-4">

                            <x-primary-button class="ms-4">
                                {{ __('Edit') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
