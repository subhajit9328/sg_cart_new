@if(!class_exists(\SGCart\Reviews\Models\Review::class) || empty($reviewsData))
    <p class="text-xs text-slate-400 dark:text-slate-500 italic">No results found</p>
@else
    @forelse($reviewsData as $rv)
        <div class="review-item border-b border-slate-100 dark:border-slate-800/60 pb-6 mb-6 last:border-0 last:pb-0 last:mb-0" data-rating="{{ $rv['rating'] }}">
            <!-- 1. Star Rating Block first -->
            <div class="flex items-center gap-1.5 text-[11px] text-emerald-600 dark:text-emerald-500 mb-2">
                <div class="flex gap-0.5">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $rv['rating'])
                            <i class="fa-solid fa-star"></i>
                        @else
                            <i class="fa-regular fa-star text-slate-200 dark:text-slate-750"></i>
                        @endif
                    @endfor
                </div>
                <span class="font-extrabold ml-1.5">{{ number_format($rv['rating'], 1) }}</span>
            </div>

            <!-- 2. Comment text -->
            <p class="text-xs text-slate-700 dark:text-slate-350 leading-relaxed font-medium mb-3">
                {{ $rv['comment'] ?? 'No comment provided.' }}
            </p>

            <!-- 3. Images (if present) -->
            @if(!empty($rv['images']))
                 <div class="mt-2.5 mb-4 flex flex-wrap gap-2">
                     @foreach($rv['images'] as $img)
                         <div class="inline-block cursor-zoom-in">
                             <img src="{{ \Illuminate\Support\Facades\Storage::url($img['image_path']) }}" 
                                  alt="User Review Attachment" 
                                  class="w-16 h-16 object-cover rounded-lg border border-slate-200 dark:border-slate-800 shadow-xs hover:opacity-90 transition-opacity"
                                  onclick="openLightbox('{{ \Illuminate\Support\Facades\Storage::url($img['image_path']) }}')">
                         </div>
                     @endforeach
                 </div>
            @endif

            <!-- 4. Customer Name and date details -->
            <div class="flex flex-wrap items-center gap-2 text-[10px] text-slate-400 dark:text-slate-500">
                <span class="font-bold text-slate-600 dark:text-slate-350">{{ $rv['customer']['name'] ?? 'Anonymous' }}</span>
                <span>•</span>
                <span>Verified Purchase</span>
                <span>•</span>
                <span>{{ \Carbon\Carbon::parse($rv['created_at'])->diffForHumans() }}</span>
            </div>
        </div>
    @empty
        <p class="text-xs text-slate-400 dark:text-slate-500 italic">No reviews have been published for this product yet.</p>
    @endforelse

    {{-- PAGINATION --}}
    @if($reviewsData instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $reviewsData->hasPages())
        <div class="flex items-center justify-center gap-2 mt-8 mb-4">
            {{-- Previous Page Link --}}
            @if($reviewsData->onFirstPage())
                <span class="w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-center text-xs font-semibold text-slate-400 bg-white opacity-40 cursor-not-allowed select-none"><i class="fa-solid fa-chevron-left"></i></span>
            @else
                <button type="button" onclick="changeReviewPage({{ $reviewsData->currentPage() - 1 }})" class="w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-center text-xs font-semibold text-slate-700 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white bg-white dark:bg-slate-900 transition-all select-none"><i class="fa-solid fa-chevron-left"></i></button>
            @endif

            {{-- Pagination Elements --}}
            @foreach($reviewsData->getUrlRange(1, $reviewsData->lastPage()) as $page => $url)
                @if($page == $reviewsData->currentPage())
                    <span class="w-10 h-10 rounded-xl border border-blue-500 bg-blue-50/50 dark:bg-blue-955/20 flex items-center justify-center text-xs font-bold text-blue-500 select-none">{{ $page }}</span>
                @else
                    <button type="button" onclick="changeReviewPage({{ $page }})" class="w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-center text-xs font-semibold text-slate-700 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white bg-white dark:bg-slate-900 transition-all select-none">{{ $page }}</button>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if($reviewsData->hasMorePages())
                <button type="button" onclick="changeReviewPage({{ $reviewsData->currentPage() + 1 }})" class="w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-center text-xs font-semibold text-slate-700 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white bg-white dark:bg-slate-900 transition-all select-none"><i class="fa-solid fa-chevron-right"></i></button>
            @else
                <span class="w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-center text-xs font-semibold text-slate-400 bg-white opacity-40 cursor-not-allowed select-none"><i class="fa-solid fa-chevron-right"></i></span>
            @endif
        </div>
    @endif
@endif
