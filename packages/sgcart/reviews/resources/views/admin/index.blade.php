@extends('layouts.admin')

@section('title', 'Product Reviews — SGCart Admin')

@section('content')

<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Product Reviews</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Catalog'],
            ['label' => 'Reviews']
        ]" />
    </div>
</div>

<!-- KPI Metrics Section -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-6">
    <!-- Card 1: Total Reviews -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-400 text-lg shrink-0">
            <i class="fa-solid fa-comments"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Reviews</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ number_format($totalReviews) }}</span>
        </div>
    </div>

    <!-- Card 2: Pending Reviews -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg shrink-0">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pending Approval</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ number_format($pendingReviewsCount) }}</span>
        </div>
    </div>

    <!-- Card 3: Approved Reviews -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg shrink-0">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Approved Reviews</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ number_format($approvedReviewsCount) }}</span>
        </div>
    </div>

    <!-- Card 4: Rejected Reviews -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center text-rose-600 dark:text-rose-400 text-lg shrink-0">
            <i class="fa-solid fa-ban"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Rejected Reviews</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ number_format($rejectedReviewsCount) }}</span>
        </div>
    </div>

    <!-- Card 5: Average Rating -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-lg shrink-0">
            <i class="fa-solid fa-star text-amber-500"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Average Rating</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ number_format($averageRating, 1) }} / 5.0</span>
        </div>
    </div>
</div>

<!-- Reviews Table Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
    <!-- Card Header with Filters -->
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
        <div class="flex items-center gap-2">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">All Customer Reviews</h2>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-300/35 dark:border-slate-700/50">
                {{ $reviews->total() }}
            </span>
        </div>
        
        <form action="{{ route('admin.reviews.index') }}" method="GET" class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
            <!-- Search Box -->
            <div class="relative flex items-center w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 pl-8 pr-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                    placeholder="Search by customer, product, comment…">
            </div>

            <!-- Status Filter -->
            <select name="status_id" onchange="this.form.submit()" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-slate-100 cursor-pointer">
                <option value="">All Statuses</option>
                @foreach($statuses as $st)
                    <option value="{{ $st->id }}" {{ request('status_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                @endforeach
            </select>
            
            @if(request()->anyFilled(['search', 'status_id']))
                <a href="{{ route('admin.reviews.index') }}" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 dark:bg-rose-950/20 dark:border-rose-900/30 dark:text-rose-400 text-xs font-semibold transition-colors text-center no-underline flex items-center justify-center">
                    Clear Filters
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 dark:bg-slate-900/50 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <th class="py-3 px-5">Customer</th>
                    <th class="py-3 px-5">Product</th>
                    <th class="py-3 px-5">Rating</th>
                    <th class="py-3 px-5">Comment & Image</th>
                    <th class="py-3 px-5">Status</th>
                    <th class="py-3 px-5">Approved At</th>
                    <th class="py-3 px-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                @forelse($reviews as $rv)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/50 transition-colors">
                        <!-- Customer -->
                        <td class="py-4 px-5">
                            <div class="font-bold text-slate-850 dark:text-white">{{ $rv->customer->name ?? 'Unknown Customer' }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $rv->customer->email ?? '' }}</div>
                        </td>
                        
                        <!-- Product -->
                        <td class="py-4 px-5">
                            <div class="font-bold text-slate-850 dark:text-white">{{ $rv->product->name ?? 'Unknown Product' }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">SKU: <span class="font-mono">{{ $rv->product->sku ?? 'N/A' }}</span></div>
                        </td>

                        <!-- Rating -->
                        <td class="py-4 px-5 shrink-0">
                            <div class="flex items-center gap-0.5 text-amber-400 text-sm">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $rv->rating)
                                        <i class="fa-solid fa-star"></i>
                                    @else
                                        <i class="fa-regular fa-star text-slate-200 dark:text-slate-700"></i>
                                    @endif
                                @endfor
                            </div>
                        </td>

                        <!-- Comment & Images -->
                        <td class="py-4 px-5 max-w-xs md:max-w-md">
                            <div class="text-slate-600 dark:text-slate-300 leading-relaxed">{{ $rv->comment ?? 'No comment provided.' }}</div>
                            @if($rv->images->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach($rv->images as $img)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($img->image_path) }}" 
                                             alt="Review Image" 
                                             class="w-12 h-12 object-cover rounded-lg border border-slate-200 dark:border-slate-800 cursor-zoom-in hover:brightness-90 transition-all shadow-sm"
                                             onclick="openLightbox('{{ \Illuminate\Support\Facades\Storage::url($img->image_path) }}')">
                                    @endforeach
                                </div>
                            @endif
                        </td>

                        <!-- Status Badge -->
                        <td class="py-4 px-5">
                            @php
                                $statusEnum = $rv->status ? \SGCart\Reviews\Enums\ReviewStatus::fromDb($rv->status->name) : null;
                            @endphp
                            @if($statusEnum === \SGCart\Reviews\Enums\ReviewStatus::PENDING)
                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase">
                                    Pending
                                </span>
                            @elseif($statusEnum === \SGCart\Reviews\Enums\ReviewStatus::APPROVED)
                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">
                                    Approved
                                </span>
                            @elseif($statusEnum === \SGCart\Reviews\Enums\ReviewStatus::REJECTED)
                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 uppercase">
                                    Rejected
                                </span>
                            @endif
                        </td>

                        <!-- Approved At -->
                        <td class="py-4 px-5 text-slate-400 dark:text-slate-500 font-mono text-[10px]">
                            {{ $rv->approved_at ? $rv->approved_at->format('M d, Y h:i A') : '—' }}
                        </td>

                        <!-- Actions -->
                        <td class="py-4 px-5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($statusEnum !== \SGCart\Reviews\Enums\ReviewStatus::APPROVED)
                                    <form action="{{ route('admin.reviews.approve', $rv->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-850 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-450 transition-colors cursor-pointer" title="Approve Review">
                                            <i class="fa-solid fa-check text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                @if($statusEnum !== \SGCart\Reviews\Enums\ReviewStatus::REJECTED)
                                    <form action="{{ route('admin.reviews.reject', $rv->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-850 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center text-rose-500 hover:text-rose-600 dark:text-rose-400 transition-colors cursor-pointer" title="Reject Review">
                                            <i class="fa-solid fa-ban text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 dark:text-slate-500">
                            <i class="fa-solid fa-comments text-2xl mb-2 block opacity-40"></i>
                            No reviews found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($reviews->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/20 dark:bg-slate-900/10">
            {{ $reviews->links() }}
        </div>
    @endif
</div>

<!-- Photo Lightbox Modal -->
<div id="lightbox-modal" class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/85 hidden" onclick="closeLightbox()">
    <button class="absolute top-4 right-4 text-white hover:text-slate-350 bg-transparent border-none cursor-pointer outline-none">
        <i class="fa-solid fa-xmark text-2xl"></i>
    </button>
    <img id="lightbox-image" src="" alt="Zoomed Review Image" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl" onclick="event.stopPropagation()">
</div>

<script>
    function openLightbox(src) {
        const modal = document.getElementById('lightbox-modal');
        const img = document.getElementById('lightbox-image');
        img.src = src;
        modal.classList.remove('hidden');
    }

    function closeLightbox() {
        const modal = document.getElementById('lightbox-modal');
        modal.classList.add('hidden');
    }
</script>

@endsection
