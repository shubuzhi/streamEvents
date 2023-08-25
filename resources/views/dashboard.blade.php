<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex">
                        <a href="#" class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Total Revenue in past 30 days</h5>
                            <p class="font-normal text-gray-700 dark:text-gray-400">{{ $data['totalRevenue'] }}</p>
                        </a>
                        <a href="#" class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Total followers gained in past 30 days</h5>
                            <p class="font-normal text-gray-700 dark:text-gray-400">{{ $data['followers'] }}</p>
                        </a>
                        <a href="#" class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Top 3 sales in past 30 days</h5>
                            @foreach($data['topThree'] as $item)
                                <p class="font-normal text-gray-700 dark:text-gray-400">{{ $item['item_name'] }}</p>
                            @endforeach
                        </a>
                    </div>
                    <ul class="max-w-md space-y-1 text-gray-500 list-inside dark:text-gray-400">
                        @foreach ($data['list'] as $item)
                            <li class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-2 @if($item['read']) text-green-500 dark:text-green-40 @else text-gray-500 dark:text-gray-400 @endif flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                                </svg>
                                {{ $item['name'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
