<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Products') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                @if (auth()->user()->is_admin)
                    <a href="{{ route('product.create') }}"
                        class="text-white float-right py-2 bg-gray-600 hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5">
                        Add New Poduct
                    </a>
                @endif

                <table class="table-auto w-full text-gray-900 dark:text-gray-100">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-start">{{ __('Product Name') }}</th>
                            <th class="px-4 py-2 text-start">{{ __('Product Price') }}</th>
                            <th class="px-4 py-2 text-start">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $product->name }}</td>
                                <td class="px-4 py-2">{{ number_format($product->price) }} IQD</td>
                                @if (auth()->user()->is_admin)
                                    <td class="px-4 py-2">
                                        <a href="{{ route('product.edit', $product) }}"
                                            class="inline-flex text-white rounded p-1 items-center bg-gray-600 hover:bg-gray-800">
                                            Edit
                                        </a>
                                        <form action="{{ route('product.destroy', $product) }}" method="POST"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button onclick="return confirm('Are you sure?')"
                                                class="bg-red-600 text-white hover:bg-red-800 rounded p-1 items-center">Delete
                                            </button>
                                        </form>

                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-2" colspan="2">{{ __('No products found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
