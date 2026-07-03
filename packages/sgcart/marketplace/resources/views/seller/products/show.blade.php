@extends('marketplace::layouts.seller')

@section('title', $product->name . ' · Product Details — Seller Portal')

@push('styles')
<style>
.pv-page * { box-sizing: border-box; }
.pv-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
}
.pv-card:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.08);
}
.dark .pv-card { 
    background: #0f172a; 
    border-color: #1e293b; 
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -2px rgba(0, 0, 0, 0.2);
}
.pv-card-header {
    display: flex; align-items: center; gap: 10px;
    padding: 16px 24px;
    border-bottom: 1px solid #f1f5f9;
    background: #f8fafc;
}
.dark .pv-card-header { border-color: #1e293b; background: rgba(30, 41, 59, 0.2); }
.pv-card-hicon {
    width: 32px; height: 32px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; background: #ede9fe; color: #7c3aed; flex-shrink: 0;
    box-shadow: inset 0 2px 4px rgba(124, 58, 237, 0.05);
}
.dark .pv-card-hicon { background: rgba(139,92,246,.15); color: #a78bfa; }
.pv-card-htitle {
    font-size: 12px; font-weight: 850; letter-spacing: .09em;
    text-transform: uppercase; color: #64748b;
}
.dark .pv-card-htitle { color: #94a3b8; }

/* Price and Payout highlights */
.payout-highlight-card {
    border-radius: 16px; 
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); 
    border: 1px solid #bbf7d0; 
    padding: 18px; 
    text-align: center;
}
.dark .payout-highlight-card {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%);
    border-color: rgba(16, 185, 129, 0.2);
}

/* Pills */
.pv-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 12px; border-radius: 999px;
    font-size: 11px; font-weight: 800; letter-spacing: .04em; line-height: 1.4;
    text-transform: uppercase;
}
.pv-pill.active   { background:#d1fae5; color:#065f46; }
.pv-pill.draft    { background:#fef3c7; color:#92400e; }
.pv-pill.inactive { background:#f1f5f9; color:#475569; }
.pv-pill.rejected { background:#fee2e2; color:#991b1b; }
.pv-pill.pending_approval { background:#e0e7ff; color:#3730a3; }
.dark .pv-pill.active   { background:rgba(16,185,129,.15); color:#34d399; }
.dark .pv-pill.draft    { background:rgba(245,158,11,.15); color:#fbbf24; }
.dark .pv-pill.inactive { background:rgba(148,163,184,.1); color:#94a3b8; }
.dark .pv-pill.rejected { background:rgba(239,68,68,.15); color:#f87171; }
.dark .pv-pill.pending_approval { background:rgba(99,102,241,.15); color:#818cf8; }

/* Gallery */
.pv-gmain {
    flex: 1; min-height: 280px; max-height: 330px;
    background: #0f172a; border-radius: 16px; overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid #e2e8f0;
    box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.2);
}
.dark .pv-gmain { border-color: #1e293b; }
.pv-gmain img {
    width: 100%; height: 100%; max-height: 330px;
    object-fit: contain; transition: opacity 0.2s ease-in-out;
}
.pv-gthumb {
    width: 52px; height: 52px; border-radius: 10px; border: 2px solid transparent;
    cursor: pointer; overflow: hidden; background: #000; flex-shrink: 0;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.pv-gthumb:hover { transform: scale(1.05); }
.pv-gthumb.active { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); }
.pv-gthumb img { width: 100%; height: 100%; object-fit: cover; }

/* Specs Table */
.spec-label { font-size:11px; font-weight:800; text-transform:uppercase; color:#94a3b8; width:140px; padding:15px 8px; letter-spacing: 0.05em; }
.spec-val   { font-size:13px; font-weight:650; color:#334155; padding:15px 8px; }
.dark .spec-val { color:#cbd5e1; }
.spec-row { border-bottom: 1px solid #f1f5f9; }
.dark .spec-row { border-color: #1e293b; }
.spec-row:last-child { border-bottom: none; }
</style>
@endpush

@section('content')
<div class="pv-page space-y-6">
    <!-- Page Header -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3.5">
            <a href="{{ route('seller.products.index') }}" class="w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-slate-100 transition-all shadow-sm">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="font-display text-xl sm:text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">Product Details</h1>
                <x-breadcrumbs :items="[
                    ['label' => 'Seller Portal', 'url' => route('seller.dashboard')],
                    ['label' => 'Catalogue'],
                    ['label' => 'My Products', 'url' => route('seller.products.index')],
                    ['label' => 'Details']
                ]" />
            </div>
        </div>
        <div class="flex gap-2.5">
            @if($product->status->value === 'active')
                <a href="{{ route('store.product', $product->slug) }}" target="_blank" class="px-4 py-2.5 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition-all flex items-center gap-1.5 shadow-sm no-underline">
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs text-slate-400"></i> Preview Product
                </a>
            @endif
            @if($product->status->value === 'rejected' || $product->status->value === 'draft')
                <form id="resubmitForm" action="{{ route('seller.products.resubmit', $product->ulid) }}" method="POST" style="display: none;">
                    @csrf
                    <input type="hidden" name="seller_note" id="sellerNoteHiddenInput">
                </form>
                <button type="button" onclick="confirmResubmit()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-500/10 active:bg-emerald-800 text-white rounded-xl text-sm font-bold transition-all flex items-center gap-1.5 shadow-sm cursor-pointer border-none">
                    <i class="fa-solid fa-paper-plane text-xs"></i> Submit for Approval
                </button>
            @endif
            <a href="{{ route('seller.products.edit', $product->id) }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/10 active:bg-indigo-800 text-white rounded-xl text-sm font-bold transition-all flex items-center gap-1.5 shadow-sm no-underline border-none">
                <i class="fa-solid fa-pen text-xs"></i> Edit Details
            </a>
        </div>
    </div>

    @if($product->status->value === 'rejected')
    <!-- Rejection Notice Banner -->
    <div class="bg-rose-50 dark:bg-rose-950/25 border border-rose-200 dark:border-rose-800/40 rounded-2xl p-5 flex items-start gap-4 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0 text-lg">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div>
            <h4 class="text-sm font-bold text-rose-800 dark:text-rose-450">Listing Rejected by Administrator</h4>
            <p class="text-xs text-rose-650 dark:text-rose-350 mt-1 leading-relaxed">
                <strong>Reason:</strong> {{ $product->rejection_reason }}
            </p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2.5">
                Please click the <strong>Edit Details</strong> button above to make the necessary corrections and resubmit this listing.
            </p>
        </div>
    </div>
    @endif

    <!-- Row 1: Gallery & Financial/Pricing Dashboard -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: Gallery -->
        <div class="lg:col-span-5">
            <div class="pv-card h-full">
                <div class="pv-card-header">
                    <div class="pv-card-hicon"><i class="fa-solid fa-images"></i></div>
                    <span class="pv-card-htitle">Product Gallery</span>
                </div>
                <div class="p-5 flex-1 flex flex-col justify-between gap-4">
                    <div class="pv-gmain">
                        @if($product->image)
                            <img id="galleryViewer" src="{{ Storage::url($product->image) }}" alt="Main Image">
                        @else
                            <div class="text-slate-500 text-xs italic">No gallery images uploaded</div>
                        @endif
                    </div>
                    @if($product->images && $product->images->count() > 1)
                        <div class="flex gap-2 overflow-x-auto pb-1">
                            @foreach($product->images as $img)
                                <div class="pv-gthumb {{ $img->is_default ? 'active' : '' }}" onclick="viewImage('{{ Storage::url($img->image_path) }}', this)">
                                    <img src="{{ Storage::url($img->image_path) }}" alt="Thumb">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Product Overview, Pricing & Payouts -->
        <div class="lg:col-span-7">
            <div class="pv-card h-full">
                <div class="pv-card-header" style="display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div class="pv-card-hicon"><i class="fa-solid fa-chart-line"></i></div>
                        <span class="pv-card-htitle">Overview &amp; Earnings</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="pv-pill {{ $product->status->value }}">{{ $product->status->value }}</span>
                    </div>
                </div>
                <div class="p-6 flex-1 flex flex-col justify-between gap-5">
                    <!-- Title Block -->
                    <div>
                        <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; display: block; margin-bottom: 2px;">Product Title</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 leading-snug">{{ $product->name }}</h2>
                        
                        <div class="flex flex-wrap gap-x-4 gap-y-2 mt-3 text-xs text-slate-500 dark:text-slate-400">
                            <span><i class="fa-solid fa-barcode mr-1"></i> SKU: <strong class="text-slate-700 dark:text-slate-200">{{ $product->sku }}</strong></span>
                            <span>&#x2022;</span>
                            <span><i class="fa-solid fa-folder mr-1"></i> Category: <strong class="text-slate-700 dark:text-slate-200">{{ $product->category->name ?? 'None' }}</strong></span>
                            @if($product->manufacturer)
                                <span>&#x2022;</span>
                                <span><i class="fa-solid fa-copyright mr-1"></i> Brand: <strong class="text-slate-700 dark:text-slate-200">{{ $product->manufacturer->name }}</strong></span>
                            @endif
                        </div>
                    </div>

                    @php
                        $price = (float) ($product->sale_price ?: $product->price);
                        $rate = (float) ($product->seller ? ($product->seller->commission_rate ?: config('marketplace.default_commission_rate', 10.00)) : config('marketplace.default_commission_rate', 10.00));
                        $platformCommission = $price * ($rate / 100);
                        $sellerPayout = $price - $platformCommission;
                        
                        $platformPct = $price > 0 ? ($platformCommission / $price) * 100 : 0;
                        $sellerPct = 100 - $platformPct;
                    @endphp

                    <!-- Pricing & Payout Highlight Card -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Selling Price Card -->
                        <div style="border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; padding:18px; display:flex; flex-direction:column; justify-content:center; align-items:center;" class="dark:bg-slate-900/50 dark:border-slate-800">
                            <span style="font-size:10px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:#64748b; margin-bottom:6px;">Selling Price</span>
                            <div class="flex items-baseline gap-1.5">
                                <span style="font-size:26px; font-weight:900; color:#1e293b;" class="dark:text-slate-100">&#x20B9;{{ number_format($price, 2) }}</span>
                            </div>
                            @if($product->sale_price)
                                <div style="font-size:11px; color:#94a3b8; font-weight:600; text-decoration:line-through; margin-top:2px;">
                                    Original: &#x20B9;{{ number_format($product->price, 2) }}
                                </div>
                            @endif
                        </div>

                        <!-- Net Payout Card -->
                        <div class="payout-highlight-card">
                            <span style="font-size:10px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:#15803d; margin-bottom:6px; display:block;">Your Earnings</span>
                            <div style="font-size:26px; font-weight:900; color:#166534;" class="dark:text-emerald-400">
                                &#x20B9;{{ number_format($sellerPayout, 2) }}
                            </div>
                            <span style="font-size:11px; color:#166534; font-weight:600; margin-top:4px; opacity:0.85; display:block;" class="dark:text-emerald-400/80">
                                ({{ number_format($sellerPct, 1) }}% payout share)
                            </span>
                        </div>
                    </div>

                    <!-- Split Progress Bar & Commission Rates -->
                    <div style="background:#f8fafc; border: 1px solid #e2e8f0; border-radius:16px; padding:16px;" class="dark:bg-slate-900/20 dark:border-slate-800">
                        <div style="height:8px; border-radius:999px; overflow:hidden; display:flex; background:#e2e8f0;" class="dark:bg-slate-800">
                            <div style="width:{{ $platformPct }}%; background:#a855f7;"></div>
                            <div style="width:{{ $sellerPct }}%; background:#10b981;"></div>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px; font-size:11px; font-weight:600; color:#64748b;">
                            <span style="display:flex; align-items:center; gap:5px;">
                                <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#a855f7;"></span>
                                Platform Fee ({{ $rate }}%): <strong class="text-slate-800 dark:text-slate-200">&#x20B9;{{ number_format($platformCommission, 2) }}</strong>
                            </span>
                            <span style="display:flex; align-items:center; gap:5px;">
                                <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#10b981;"></span>
                                Payout Rate: <strong class="text-slate-800 dark:text-slate-200">{{ $sellerPct }}%</strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Description & Specifications / Logistics -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: Description -->
        <div class="lg:col-span-7">
            <div class="pv-card h-full">
                <div class="pv-card-header">
                    <div class="pv-card-hicon"><i class="fa-solid fa-align-left"></i></div>
                    <span class="pv-card-htitle">Description</span>
                </div>
                <div class="p-6 text-sm text-slate-650 dark:text-slate-350 space-y-5 flex-1">
                    @if($product->short_description)
                        <div class="p-5 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-850">
                            <span class="text-[10px] font-extrabold uppercase text-slate-400 block mb-1.5 tracking-wider">Short Summary</span>
                            <p class="leading-relaxed text-slate-700 dark:text-slate-300 font-medium">{{ $product->short_description }}</p>
                        </div>
                    @endif
                    <div>
                        <span class="text-[10px] font-extrabold uppercase text-slate-400 block mb-2 tracking-wider">Detailed Description</span>
                        <p class="leading-relaxed whitespace-pre-line text-slate-600 dark:text-slate-400">{{ $product->description ?? 'No detailed description provided.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Specs & Logistics -->
        <div class="lg:col-span-5">
            <div class="pv-card h-full">
                <div class="pv-card-header">
                    <div class="pv-card-hicon"><i class="fa-solid fa-list-check"></i></div>
                    <span class="pv-card-htitle">Logistics &amp; Specs</span>
                </div>
                <div class="p-6 flex-1 flex flex-col justify-between">
                    <table class="w-full text-left border-collapse">
                        <tbody>
                            <tr class="spec-row">
                                <td class="spec-label">SKU Code</td>
                                <td class="spec-val font-mono text-indigo-600 dark:text-indigo-400">{{ $product->sku }}</td>
                            </tr>
                            <tr class="spec-row">
                                <td class="spec-label">Available Stock</td>
                                <td class="spec-val">
                                    <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 rounded-lg text-xs font-bold">{{ $product->stock }} units</span>
                                </td>
                            </tr>
                            <tr class="spec-row">
                                <td class="spec-label">Min Stock Alarm</td>
                                <td class="spec-val">
                                    <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 rounded-lg text-xs font-bold">{{ $product->min_stock ?? '5 (Default)' }} units</span>
                                </td>
                            </tr>
                            <tr class="spec-row">
                                <td class="spec-label">Weight</td>
                                <td class="spec-val">{{ $product->weight ?? 'Not specified' }}</td>
                            </tr>
                            <tr class="spec-row">
                                <td class="spec-label">Dimensions</td>
                                <td class="spec-val">{{ $product->dimensions ?? 'Not specified' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if(isset($product->variants) && $product->variants->count() > 0)
    <!-- Row 3: Product Variants -->
    <div class="pv-card mt-6">
        <div class="pv-card-header" style="display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:8px;">
                <div class="pv-card-hicon" style="background:#ede9fe; color:#7c3aed;"><i class="fa-solid fa-cubes"></i></div>
                <span class="pv-card-htitle">Product Variants</span>
            </div>
            <span class="pv-pill active" style="font-size:10px;">{{ $product->variants->count() }} Variants</span>
        </div>
        
        <div style="overflow-x:auto; padding: 16px;">
            <table class="w-full text-left border-collapse" style="min-width: 600px;">
                <thead>
                    <tr class="border-b border-slate-205 dark:border-slate-800 text-slate-400 font-bold text-xs uppercase bg-slate-50/40 dark:bg-slate-900/30">
                        <th style="padding:12px 16px; font-weight: 800; width: 80px;">Image</th>
                        <th style="padding:12px 16px; font-weight: 800; width: 140px;">SKU</th>
                        <th style="padding:12px 16px; font-weight: 800;">Attributes</th>
                        <th style="padding:12px 16px; font-weight: 800; width: 120px;">Price</th>
                        <th style="padding:12px 16px; font-weight: 800; width: 120px;">Sale Price</th>
                        <th style="padding:12px 16px; font-weight: 800; text-align:center; width: 100px;">Stock</th>
                        <th style="padding:12px 16px; font-weight: 800; text-align:center; width: 100px;">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-slate-700 dark:text-slate-300">
                    @foreach($product->variants as $variant)
                        <tr style="{{ !$variant->is_active ? 'opacity:.55;' : '' }}" class="hover:bg-slate-50/40 dark:hover:bg-slate-800/10 transition-colors">
                            <!-- Variant Image -->
                            <td style="padding:12px 16px;">
                                @if($variant->image)
                                    <div class="w-12 h-12 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-850 flex items-center justify-center cursor-pointer shadow-sm hover:scale-105 transition-transform"
                                         onclick="viewImage('{{ Storage::url($variant->image) }}', this)">
                                        <img src="{{ Storage::url($variant->image) }}" alt="Variant Image" class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 text-xs shadow-sm">
                                        <i class="fa-solid fa-image text-lg"></i>
                                    </div>
                                @endif
                            </td>
                            <!-- SKU -->
                            <td style="padding:12px 16px;" class="font-mono text-xs font-semibold text-slate-600 dark:text-slate-400">
                                {{ $variant->sku ?: $product->sku }}
                            </td>
                            <!-- Attributes -->
                            <td style="padding:12px 16px;">
                                <div class="flex flex-wrap gap-1.5">
                                    @if($variant->color)
                                        <div style="display:inline-flex; align-items:center; gap:6px; background:#f1f5f9; padding:4px 10px; border-radius:999px; border:1px solid #e2e8f0; font-size:11px; font-weight:700; color:#334155;" class="dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                                            @if($variant->color->hex_code)
                                                <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:{{ $variant->color->hex_code }}; border:1px solid rgba(0,0,0,0.15);"></span>
                                            @endif
                                            Color: {{ $variant->color->name }}
                                        </div>
                                    @endif
                                    @if($variant->size)
                                        <div style="display:inline-flex; align-items:center; gap:6px; background:#f1f5f9; padding:4px 10px; border-radius:999px; border:1px solid #e2e8f0; font-size:11px; font-weight:700; color:#334155;" class="dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                                            Size: {{ $variant->size->name }}
                                            @if($variant->size->code)
                                                <span style="font-size:9px; background:#64748b; color:#fff; padding:1px 4px; border-radius:4px; font-weight:800;">{{ $variant->size->code }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    @if(!$variant->color && !$variant->size)
                                        <span style="color:#94a3b8; font-size:12px;">Standard Variant</span>
                                    @endif
                                </div>
                            </td>
                            <!-- Price -->
                            <td style="padding:12px 16px;" class="font-bold text-xs">
                                @if($variant->price)
                                    &#x20B9;{{ number_format($variant->price, 2) }}
                                @else
                                    <span class="text-slate-450 dark:text-slate-500 font-normal italic">Inherited</span>
                                @endif
                            </td>
                            <!-- Sale Price -->
                            <td style="padding:12px 16px;" class="font-bold text-xs text-emerald-600 dark:text-emerald-400">
                                @if($variant->sale_price)
                                    &#x20B9;{{ number_format($variant->sale_price, 2) }}
                                @elseif($product->sale_price)
                                    <span class="text-slate-450 dark:text-slate-500 font-normal italic">Inherited</span>
                                @else
                                    <span class="text-slate-400 font-normal">—</span>
                                @endif
                            </td>
                            <!-- Stock -->
                            <td style="padding:12px 16px; text-align:center;">
                                <span class="pv-pill {{ $variant->stock > 0 ? 'active' : 'inactive' }}" style="font-size: 10px;">{{ $variant->stock }} qty</span>
                            </td>
                            <!-- Status -->
                            <td style="padding:12px 16px; text-align:center;">
                                <span class="pv-pill {{ $variant->is_active ? 'active' : 'inactive' }}" style="font-size: 10px;">{{ $variant->is_active ? 'Active' : 'Draft' }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<script>
    function viewImage(src, element) {
        var viewer = document.getElementById('galleryViewer');
        if (!viewer) return;
        
        viewer.style.opacity = 0;
        setTimeout(function() {
            viewer.src = src;
            viewer.style.opacity = 1;
        }, 150);
        
        document.querySelectorAll('.pv-gthumb').forEach(el => el.classList.remove('active'));
        if (element && element.classList.contains('pv-gthumb')) {
            element.classList.add('active');
        }
    }

    function confirmResubmit() {
        showResubmitModal((note) => {
            document.getElementById('sellerNoteHiddenInput').value = note;
            document.getElementById('resubmitForm').submit();
        });
    }

    function showResubmitModal(callback) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fadeIn';
        modal.innerHTML = `
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-800 transform scale-95 opacity-0 transition-all duration-300 popup-content text-left">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white font-display">Submit for Approval</h3>
                    <button class="modal-close text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer border-none bg-transparent outline-none focus:outline-none">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                    Please provide a brief note explaining the corrections or updates you made to this product listing. The administrator will review this note.
                </p>
                <div class="mb-5">
                    <textarea id="sellerNoteInput" class="w-full min-h-[100px] p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:border-indigo-500" placeholder="e.g. Added correct category, fixed pricing, and updated main gallery photo..."></textarea>
                    <p id="sellerNoteErrorMsg" class="text-xs text-rose-500 mt-1.5 hidden">Please enter a reason or description of changes.</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button class="modal-cancel border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer transition-colors bg-transparent" style="text-transform: none;">Cancel</button>
                    <button class="modal-confirm bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-lg shadow-emerald-600/10 border-none px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer" style="text-transform: none;">Submit</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Trigger scale-in transition
        setTimeout(() => {
            const content = modal.querySelector('.popup-content');
            if (content) {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }
            modal.querySelector('#sellerNoteInput').focus();
        }, 10);

        function closeModal(confirmed = false) {
            const noteVal = modal.querySelector('#sellerNoteInput').value.trim();
            if (confirmed) {
                if (!noteVal) {
                    modal.querySelector('#sellerNoteErrorMsg').classList.remove('hidden');
                    return;
                }
                
                // Show loader on click submit without closing the modal
                const confirmBtn = modal.querySelector('.modal-confirm');
                const cancelBtn = modal.querySelector('.modal-cancel');
                const closeBtn = modal.querySelector('.modal-close');
                const textarea = modal.querySelector('#sellerNoteInput');
                
                confirmBtn.disabled = true;
                confirmBtn.style.opacity = '0.7';
                confirmBtn.style.cursor = 'not-allowed';
                confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Submitting...';
                
                cancelBtn.disabled = true;
                cancelBtn.style.opacity = '0.5';
                cancelBtn.style.cursor = 'not-allowed';
                
                closeBtn.disabled = true;
                textarea.disabled = true;
                
                if (typeof callback === 'function') {
                    callback(noteVal);
                }
                return;
            }

            const content = modal.querySelector('.popup-content');
            if (content) {
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
            }
            modal.classList.remove('animate-fadeIn');
            modal.classList.add('animate-fadeOut');
            setTimeout(() => {
                modal.remove();
            }, 200);
        }

        modal.querySelector('.modal-close').addEventListener('click', () => closeModal(false));
        modal.querySelector('.modal-cancel').addEventListener('click', () => closeModal(false));
        modal.querySelector('.modal-confirm').addEventListener('click', () => closeModal(true));
    }
</script>
@endsection
