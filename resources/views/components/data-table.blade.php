@props([
    'title',
    'totalCount',
    'searchPlaceholder' => 'Search...',
    'searchName' => 'search',
    'action',
    'tableId',
    'searchInputId',
    'totalCountId',
    'clearBtnId' => null,
    'clearBtnWrapperId' => null,
    'refreshBtn' => false,
    'items', // paginator collection
    'headers', // array of ['label' => '...', 'key' => '...', 'sortable' => true/false, 'sort_field' => '...', 'align' => 'left/right/center']
    'filterKeys' => [], // array of query param keys for clearing filters
])

<div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
    <!-- Top Indeterminate Progress Bar -->
    <div id="{{ $tableId }}_progress" class="absolute top-0 left-0 right-0 h-[3px] bg-blue-500/10 overflow-hidden hidden z-30">
        <div class="h-full bg-blue-600 dark:bg-blue-400 w-full origin-left animate-[loadingBar_1.5s_infinite_ease-in-out]"></div>
    </div>

    <!-- Table Loading Overlay (Blurred backdrop + spinner) -->
    <div id="{{ $tableId }}_overlay" class="absolute inset-0 bg-white/60 dark:bg-slate-950/65 backdrop-blur-[0.5px] flex items-center justify-center hidden z-15 transition-all duration-300">
        <div class="flex items-center gap-2.5 px-4.5 py-3 rounded-xl bg-white dark:bg-slate-800 shadow-2xl border border-slate-200/80 dark:border-slate-700/90">
            <svg class="animate-spin h-4 w-4 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-100 tracking-wide">Loading data...</span>
        </div>
    </div>

    <style>
        @keyframes loadingBar {
            0% { transform: translateX(-100%) scaleX(0.2); }
            50% { transform: translateX(0%) scaleX(0.6); }
            100% { transform: translateX(100%) scaleX(0.2); }
        }
    </style>
    <!-- Card Header -->
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
        <div class="flex items-center gap-2">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $title }}</h2>
            <span id="{{ $totalCountId }}" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-300/35 dark:border-slate-700/50">
                {{ $totalCount }}
            </span>
        </div>

        <form action="{{ $action }}" method="GET" class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <!-- Search Box -->
            <div class="relative flex items-center w-full sm:w-60">
                <i class="fa-solid fa-magnifying-glass absolute left-3 text-slate-400 text-xs"></i>
                <input type="text" id="{{ $searchInputId }}" name="{{ $searchName }}" value="{{ request($searchName) }}"
                    class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 pl-8 pr-8 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                    placeholder="{{ $searchPlaceholder }}">
                <button type="button" id="clearSearchInputBtn" class="absolute right-2.5 flex items-center justify-center w-5 h-5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700/50 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 border-none bg-transparent cursor-pointer transition-colors {{ request($searchName) ? '' : 'hidden' }}" title="Clear Search">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <!-- Custom Filters Slot (e.g. category, status dropdowns) -->
            @if(isset($filters))
                {{ $filters }}
            @endif

            @if($clearBtnId && $clearBtnWrapperId)
                <div id="{{ $clearBtnWrapperId }}">
                    @if(request()->anyFilled(array_merge([$searchName], $filterKeys)))
                        <a href="{{ $action }}" id="{{ $clearBtnId }}" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 dark:bg-rose-950/20 dark:border-rose-900/30 dark:text-rose-400 text-xs font-semibold transition-colors text-center no-underline flex items-center justify-center animate-fadeIn">
                            Clear
                        </a>
                    @endif
                </div>
            @endif

            @if($refreshBtn)
                <button type="button" id="refreshTableBtn" class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 text-xs font-semibold transition-colors flex items-center gap-1.5 cursor-pointer bg-white dark:bg-slate-800" title="Refresh Table">
                    <i class="fa-solid fa-arrows-rotate"></i> Refresh
                </button>
            @endif
        </form>
    </div>

    <!-- Table content wrapper -->
    <div id="{{ $tableId }}">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50">
                        @foreach($headers as $header)
                            @php
                                $isSortable = $header['sortable'] ?? false;
                                $align = $header['align'] ?? 'left';
                                $alignClass = $align === 'right' ? 'text-right' : ($align === 'center' ? 'text-center' : 'text-left');
                            @endphp
                            <th class="px-5 py-3 {{ $alignClass }} text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                @if($isSortable)
                                    @php
                                        $sortField = $header['sort_field'] ?? $header['key'];
                                        $currentSort = request('sort_by');
                                        $currentDir = request('sort_dir') ?? request('sort_order') ?? 'asc';
                                        $nextDir = ($currentSort === $sortField && $currentDir === 'asc') ? 'desc' : 'asc';
                                        $urlParams = array_merge(request()->query(), [
                                            'sort_by' => $sortField,
                                            'sort_dir' => $nextDir,
                                            'sort_order' => $nextDir
                                        ]);
                                        $sortUrl = url()->current() . '?' . http_build_query($urlParams);
                                    @endphp
                                    <a href="{{ $sortUrl }}" class="group inline-flex items-center gap-1.5 hover:text-slate-850 dark:hover:text-slate-100 no-underline transition-colors">
                                        <span class="{{ $currentSort === $sortField ? 'text-blue-600 dark:text-blue-450 font-bold' : '' }}">{{ $header['label'] }}</span>
                                        <span class="inline-flex items-center text-[10px]">
                                            @if($currentSort === $sortField)
                                                @if($currentDir === 'asc')
                                                    <i class="fa-solid fa-sort-up text-blue-600 dark:text-blue-450 font-black -mb-1"></i>
                                                @else
                                                    <i class="fa-solid fa-sort-down text-blue-600 dark:text-blue-450 font-black -mt-1"></i>
                                                @endif
                                            @else
                                                <i class="fa-solid fa-sort text-slate-300 dark:text-slate-700 group-hover:text-slate-500 dark:group-hover:text-slate-400 transition-colors"></i>
                                            @endif
                                        </span>
                                    </a>
                                @else
                                    {{ $header['label'] }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    {{ $slot }}
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        @if($items && method_exists($items, 'hasPages') && $items->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>

<x-table-ajax-handler
    :tableId="$tableId"
    :searchInputId="$searchInputId"
    :totalCountId="$totalCountId"
    :clearBtnWrapperId="$clearBtnWrapperId"
    :clearBtnId="$clearBtnId"
    :refreshBtnId="$refreshBtn ? 'refreshTableBtn' : null"
/>
