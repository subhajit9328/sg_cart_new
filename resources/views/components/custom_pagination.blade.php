@props([
    'paginator',
    'showInfo' => false,
    'label' => 'items',
    'size' => 'md',
    'class' => ''
])

@if($paginator->hasPages())
    @php
        $buttonClass = $size === 'sm' 
            ? 'w-8 h-8 rounded-lg text-xs' 
            : 'w-10 h-10 rounded-xl text-xs';
            
        $activeButtonClass = $size === 'sm'
            ? 'w-8 h-8 rounded-lg text-xs'
            : 'w-10 h-10 rounded-xl text-xs';

        $lastPage = $paginator->lastPage();
        $currentPage = $paginator->currentPage();
        $adjacent = 1;
        $showLeftDots = true;
        $showRightDots = true;
    @endphp

    <div class="flex flex-col sm:flex-row items-center {{ $showInfo ? 'justify-between' : 'justify-center' }} gap-4 {{ $class }}">
        @if($showInfo)
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                Showing <span class="font-bold text-slate-800 dark:text-slate-200">{{ $paginator->firstItem() }}</span> to <span class="font-bold text-slate-800 dark:text-slate-200">{{ $paginator->lastItem() }}</span> of <span class="font-bold text-slate-800 dark:text-slate-200">{{ $paginator->total() }}</span> {{ $label }}
            </p>
        @endif

        <div class="flex items-center gap-1.5">
            {{-- Previous Page Link --}}
            @if($paginator->onFirstPage())
                <span class="{{ $buttonClass }} border border-border flex items-center justify-center font-semibold text-stone bg-white opacity-40 cursor-not-allowed select-none dark:bg-[#171614] dark:border-[#2b2a27] dark:text-[#9ca3af] dark:opacity-25"><i class="fa-solid fa-chevron-left"></i></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="{{ $buttonClass }} border border-border flex items-center justify-center font-semibold text-stone hover:text-ink hover:border-stone hover:bg-silk bg-white transition-all no-underline select-none dark:bg-[#171614] dark:border-[#2b2a27] dark:text-[#9ca3af] dark:hover:border-[#6b7280] dark:hover:bg-[#2b2925] dark:hover:text-[#f3f4f6]" rel="prev"><i class="fa-solid fa-chevron-left"></i></a>
            @endif

            {{-- Pagination Elements --}}
            @foreach($paginator->getUrlRange(1, $lastPage) as $page => $url)
                @if($page == $currentPage)
                    <span class="{{ $activeButtonClass }} border border-accent bg-[#fbfaf8] flex items-center justify-center font-bold text-accent select-none dark:border-accent dark:bg-[#c8a97e]/15 dark:text-accent">{{ $page }}</span>
                @elseif($page == 1 || $page == $lastPage || abs($page - $currentPage) <= $adjacent)
                    <a href="{{ $url }}" class="{{ $buttonClass }} border border-border flex items-center justify-center font-semibold text-stone hover:text-ink hover:border-stone hover:bg-silk bg-white transition-all no-underline select-none dark:bg-[#171614] dark:border-[#2b2a27] dark:text-[#9ca3af] dark:hover:border-[#6b7280] dark:hover:bg-[#2b2925] dark:hover:text-[#f3f4f6]">{{ $page }}</a>
                @elseif($page > 1 && $page < $currentPage - $adjacent && $showLeftDots)
                    <span class="{{ $buttonClass }} flex items-center justify-center font-semibold text-stone select-none dark:text-[#9ca3af]">...</span>
                    @php $showLeftDots = false; @endphp
                @elseif($page > $currentPage + $adjacent && $page < $lastPage && $showRightDots)
                    <span class="{{ $buttonClass }} flex items-center justify-center font-semibold text-stone select-none dark:text-[#9ca3af]">...</span>
                    @php $showRightDots = false; @endphp
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="{{ $buttonClass }} border border-border flex items-center justify-center font-semibold text-stone hover:text-ink hover:border-stone hover:bg-silk bg-white transition-all no-underline select-none dark:bg-[#171614] dark:border-[#2b2a27] dark:text-[#9ca3af] dark:hover:border-[#6b7280] dark:hover:bg-[#2b2925] dark:hover:text-[#f3f4f6]" rel="next"><i class="fa-solid fa-chevron-right"></i></a>
            @else
                <span class="{{ $buttonClass }} border border-border flex items-center justify-center font-semibold text-stone bg-white opacity-40 cursor-not-allowed select-none dark:bg-[#171614] dark:border-[#2b2a27] dark:text-[#9ca3af] dark:opacity-25"><i class="fa-solid fa-chevron-right"></i></span>
            @endif
        </div>
    </div>
@endif
