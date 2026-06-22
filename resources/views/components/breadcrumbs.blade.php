@props(['items'])

<div class="flex flex-wrap items-center gap-1.5 text-xs text-slate-400 mt-1">
    @foreach($items as $index => $item)
        @if(!$loop->first)
            <i class="fa-solid fa-angle-right text-[10px] text-slate-300 dark:text-slate-600"></i>
        @endif

        @php
            $isMono = $item['mono'] ?? false;
            $class = $isMono ? 'font-mono' : '';
        @endphp

        @if($loop->last)
            <span class="text-slate-600 dark:text-slate-300 font-medium {{ $class }}">
                {{ $item['label'] }}
            </span>
        @elseif(isset($item['url']) && $item['url'])
            <a href="{{ $item['url'] }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors no-underline text-slate-400 {{ $class }}">
                {{ $item['label'] }}
            </a>
        @else
            <span class="{{ $class }}">
                {{ $item['label'] }}
            </span>
        @endif
    @endforeach
</div>
