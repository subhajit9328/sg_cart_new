@extends('layouts.admin')

@section('title', 'Review: ' . $product->name . ' — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.products.approvals') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors no-underline" title="Back to Approvals">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <div class="flex items-center gap-2 flex-wrap">
            <h1 class="font-display text-xl sm:text-2xl font-bold leading-tight text-slate-900 dark:text-white">{{ $product->name }}</h1>
            @if($product->status->value === 'rejected')
                <span class="text-[10px] font-bold uppercase tracking-widest text-rose-500 bg-rose-500/10 px-2 py-0.5 rounded border border-rose-500/20">
                    Rejected
                </span>
            @else
                <span class="text-[10px] font-bold uppercase tracking-widest text-amber-500 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">
                    Pending Review
                </span>
            @endif
        </div>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Marketplace'],
            ['label' => 'Approvals', 'url' => route('admin.products.approvals')],
            ['label' => 'Review']
        ]" />
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Left Column: Details -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Hero Product Banner Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row gap-6 items-center">
            @php
                $defaultImage = $product->images->where('is_default', true)->first() ?? $product->images->first();
            @endphp
            <div class="w-full md:w-48 h-48 rounded-xl overflow-hidden bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-center shrink-0 shadow-md group relative">
                @if($defaultImage)
                    <img src="{{ asset('storage/' . $defaultImage->image_path) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                @else
                    <div class="text-slate-600 dark:text-slate-500 flex flex-col items-center justify-center gap-2">
                        <i class="fa-regular fa-image text-3xl"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider">No Preview</span>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3 justify-center">
                    <span class="text-[10px] text-white font-semibold uppercase tracking-wider">Primary Image</span>
                </div>
            </div>
            
            <div class="flex-1 w-full space-y-4">
                <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
                    <div>
                        <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">SKU Code</span>
                        <span class="font-mono text-slate-800 dark:text-slate-200 font-semibold mt-0.5 block break-all">
                            {{ $product->sku }}
                        </span>
                    </div>
                    
                    <div>
                        <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">Product Category</span>
                        <span class="text-slate-800 dark:text-slate-200 font-semibold mt-0.5 block">
                            <i class="fa-solid fa-folder text-slate-400 mr-1 text-xs"></i>
                            {{ $product->category->name ?? 'Uncategorized' }}
                        </span>
                    </div>
                    
                    <div>
                        <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">Manufacturer</span>
                        <span class="text-slate-800 dark:text-slate-200 font-semibold mt-0.5 block">
                            <i class="fa-solid fa-industry text-slate-400 mr-1 text-xs"></i>
                            {{ $product->manufacturer->name ?? 'N/A' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">Weight & Dimensions</span>
                        <span class="text-slate-800 dark:text-slate-200 font-semibold mt-0.5 block text-xs truncate">
                            @if($product->weight || $product->dimensions)
                                {{ $product->weight ? $product->weight . ' kg' : '' }} 
                                {{ $product->dimensions ? ' (' . $product->dimensions . ')' : '' }}
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing & Stock Metrics Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/50 flex items-center gap-2">
                <i class="fa-solid fa-dollar-sign text-blue-500 text-xs"></i>
                <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-widest">
                    Pricing & Inventory Metrics
                </h3>
            </div>
            
            <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-6 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 dark:divide-slate-800">
                <div class="space-y-1">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">Regular Retail Price</span>
                    <span class="text-2xl font-extrabold text-slate-850 dark:text-slate-100 block">
                        ₹{{ number_format($product->price, 2) }}
                    </span>
                </div>
                
                <div class="sm:pl-6 space-y-1">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">Special Sale Price</span>
                    @if($product->sale_price)
                        <div class="flex items-center gap-2">
                            <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-450 block">
                                ₹{{ number_format($product->sale_price, 2) }}
                            </span>
                            <span class="text-[9px] font-bold text-emerald-600 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20">
                                Save {{ round((($product->price - $product->sale_price) / $product->price) * 100) }}%
                            </span>
                        </div>
                    @else
                        <span class="text-slate-400 text-sm font-semibold block mt-1">
                            No active discount
                        </span>
                    @endif
                </div>

                <div class="sm:pl-6 space-y-1">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">Inventory Stock</span>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-extrabold text-slate-850 dark:text-slate-100 block">
                            {{ $product->stock }}
                        </span>
                        <span class="text-slate-400 text-xs font-semibold mt-1">units available</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description Box -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/50 flex items-center gap-2">
                <i class="fa-solid fa-align-left text-blue-500 text-xs"></i>
                <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-widest">
                    Description & Specifications
                </h3>
            </div>
            
            <div class="p-6 space-y-5">
                @if($product->short_description)
                    <div class="space-y-1.5">
                        <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">Short Description Summary</span>
                        <div class="text-sm text-slate-600 dark:text-slate-350 leading-relaxed italic bg-slate-50 dark:bg-slate-950 p-4 rounded-xl border border-slate-200/50 dark:border-slate-800/40">
                            {{ $product->short_description }}
                        </div>
                    </div>
                @endif
                
                <div class="space-y-1.5">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">Full Details</span>
                    <div class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed space-y-2 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                        {!! nl2br(e($product->description ?? 'No detailed description provided.')) !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Gallery Grid -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/50 flex items-center gap-2">
                <i class="fa-regular fa-images text-blue-500 text-xs"></i>
                <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-widest">
                    Media Gallery ({{ $product->images->count() }})
                </h3>
            </div>
            
            <div class="p-6">
                @if($product->images->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @foreach($product->images as $img)
                            <div class="relative group rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 aspect-square bg-slate-950 flex items-center justify-center shadow-sm">
                                <img src="{{ asset('storage/' . $img->image_path) }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                @if($img->is_default)
                                    <span class="absolute top-2 left-2 bg-blue-600 text-white text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded shadow">
                                        Default
                                    </span>
                                @endif
                                <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                                    <a href="{{ asset('storage/' . $img->image_path) }}" target="_blank" class="w-8 h-8 rounded-full bg-white text-slate-850 hover:bg-slate-100 flex items-center justify-center text-sm shadow-md transition-transform duration-300 scale-90 group-hover:scale-100">
                                        <i class="fa-solid fa-magnifying-glass-plus"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 text-slate-400 border border-dashed border-slate-200 dark:border-slate-855 rounded-xl">
                        <i class="fa-regular fa-image text-4xl mb-2.5 block text-slate-500"></i>
                        <span class="text-sm font-semibold">No media uploaded</span>
                        <p class="text-xs text-slate-400/80 mt-1">This product does not have any gallery images.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Variants Block (If variants exist) -->
        @if(isset($product->variants) && $product->variants->count() > 0)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/50 flex items-center gap-2">
                    <i class="fa-solid fa-cubes text-blue-500 text-xs"></i>
                    <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-widest">
                        Available Variants ({{ $product->variants->count() }})
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 font-bold text-xs uppercase bg-slate-50/40 dark:bg-slate-900/30">
                                <th class="py-3 px-5" style="width: 80px;">Image</th>
                                <th class="py-3 px-5">SKU</th>
                                <th class="py-3 px-5">Attributes</th>
                                <th class="py-3 px-5">Price</th>
                                <th class="py-3 px-5">Stock</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-855 text-slate-700 dark:text-slate-300">
                            @foreach($product->variants as $variant)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/10 transition-colors">
                                    <td class="py-3 px-5">
                                        @if($variant->image)
                                            <div class="w-10 h-10 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex items-center justify-center shadow-sm">
                                                <img src="{{ Storage::url($variant->image) }}" alt="Variant Image" class="w-full h-full object-cover">
                                            </div>
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-slate-50 dark:bg-slate-855 border border-slate-200 dark:border-slate-750 flex items-center justify-center text-slate-400 text-xs shadow-sm">
                                                <i class="fa-solid fa-image"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5 font-mono text-xs font-semibold">{{ $variant->sku ?? $product->sku }}</td>
                                    <td class="py-3.5 px-5">
                                        <div class="flex flex-wrap gap-1.5">
                                            @if($variant->color)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-955 text-[11px] font-medium text-slate-600 dark:text-slate-350 font-sans">
                                                    Color: {{ $variant->color->name }}
                                                </span>
                                            @endif
                                            @if($variant->size)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-955 text-[11px] font-medium text-slate-600 dark:text-slate-355 font-sans">
                                                    Size: {{ $variant->size->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-5 text-xs font-bold text-slate-855 dark:text-slate-200">
                                        @if($variant->price > 0)
                                            +&#x20B9;{{ number_format($variant->price, 2) }}
                                        @else
                                            <span class="text-slate-400 font-normal">Base Price</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5 text-xs">
                                        <span class="font-semibold">{{ $variant->stock }}</span> units
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>

    <!-- Right Column: Decision Dashboard & Vendor Card -->
    <div class="space-y-6">
        
        <!-- Decision Controller Box -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl shadow-lg p-6 relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>

            <h3 class="text-sm font-extrabold text-slate-800 dark:text-white uppercase tracking-wider mb-4 border-b border-slate-100 dark:border-slate-800 pb-2.5">
                Review Decision
            </h3>
            
            <p class="text-xs text-slate-400 leading-relaxed mb-6">
                Approve to publish the item directly on the live store catalog, or Reject to flag the listing back to the seller's dashboard.
            </p>

            @if($product->seller_note)
                <!-- Seller Submission Note -->
                <div class="rounded-xl bg-indigo-50/70 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/30 p-4 mb-5 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 text-sm">
                        <i class="fa-solid fa-comment-dots"></i>
                    </div>
                    <div style="min-width:0; flex:1;">
                        <div class="text-xs font-bold text-indigo-800 dark:text-indigo-300 uppercase tracking-wide">Seller Note on Resubmission</div>
                        <p class="text-xs text-indigo-700 dark:text-indigo-350 mt-1 leading-relaxed break-words" style="margin:0;">
                            "{{ $product->seller_note }}"
                        </p>
                    </div>
                </div>
            @endif

            @if($product->status->value === 'rejected')
                <div class="rounded-xl bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/40 p-4 mb-4 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-450 flex items-center justify-center flex-shrink-0 text-sm">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-rose-800 dark:text-rose-400 uppercase tracking-wide">Product Rejected</div>
                        <p class="text-xs text-rose-700 dark:text-rose-350 mt-1 leading-relaxed">
                            <strong>Reason:</strong> {{ $product->rejection_reason }}
                        </p>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <form id="approveForm" action="{{ route('admin.products.approvals.approve', $product) }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <button type="button" onclick="confirmApprove()" class="w-full bg-emerald-600 hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-500/10 active:bg-emerald-800 text-white font-bold py-3 px-4 rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer border-none text-sm tracking-wide">
                        <i class="fa-solid fa-circle-check text-base"></i> Approve & Publish
                    </button>
                </div>
            @else
                <div class="space-y-3">
                    <form id="approveForm" action="{{ route('admin.products.approvals.approve', $product) }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <button type="button" onclick="confirmApprove()" class="w-full bg-emerald-600 hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-500/10 active:bg-emerald-800 text-white font-bold py-3 px-4 rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer border-none text-sm tracking-wide">
                        <i class="fa-solid fa-circle-check text-base"></i> Approve & Publish
                    </button>

                    <form id="rejectForm" action="{{ route('admin.products.approvals.reject', $product) }}" method="POST" style="display: none;">
                        @csrf
                        <input type="hidden" name="rejection_reason" id="rejectionReasonHiddenInput">
                    </form>
                    <button type="button" onclick="confirmReject()" class="w-full bg-rose-600 hover:bg-rose-700 hover:shadow-lg hover:shadow-rose-500/10 active:bg-rose-800 text-white font-bold py-3 px-4 rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer border-none text-sm tracking-wide">
                        <i class="fa-solid fa-circle-xmark text-base"></i> Reject & Archive
                    </button>
                </div>
            @endif
        </div>

        <!-- Vendor Shop Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-white uppercase tracking-wider mb-4 border-b border-slate-100 dark:border-slate-800 pb-2.5">
                Seller & Shop Profile
            </h3>
            
            @if($product->seller)
                <div class="space-y-5">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-500/10 to-indigo-500/10 text-blue-500 border border-blue-500/20 flex items-center justify-center text-xl font-bold">
                            <i class="fa-solid fa-store"></i>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 leading-tight truncate">
                                {{ $product->seller->shop_name }}
                            </h4>
                            <a href="{{ route('admin.sellers.show', $product->seller) }}" class="text-[11px] text-blue-500 hover:underline mt-0.5 block font-semibold no-underline">
                                View Seller Profile <i class="fa-solid fa-arrow-up-right-from-square text-[9px] ml-0.5"></i>
                            </a>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-150 dark:border-slate-800/60 space-y-3 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 font-medium"><i class="fa-regular fa-user mr-1.5 w-3.5 text-center"></i>Shop Owner</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $product->seller->name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 font-medium"><i class="fa-regular fa-envelope mr-1.5 w-3.5 text-center"></i>Contact Email</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300 font-mono text-[11px] truncate max-w-44">{{ $product->seller->email }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 font-medium"><i class="fa-solid fa-percent mr-1.5 w-3.5 text-center"></i>Commission Rate</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">
                                {{ $product->seller->commission_rate ? $product->seller->commission_rate.'%' : 'Default ('.config('marketplace.default_commission_rate', 10.00).'%)' }}
                            </span>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5 text-slate-400 italic">
                    Unknown or deleted seller account.
                </div>
            @endif
        </div>

        @if($product->seller)
        <!-- Commission & Payout Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-white uppercase tracking-wider mb-4 border-b border-slate-100 dark:border-slate-800 pb-2.5 flex items-center justify-between">
                <span><i class="fa-solid fa-indian-rupee-sign mr-2 text-indigo-500"></i>Commission &amp; Payout</span>
            </h3>
            
            <div class="space-y-3 text-xs">
                @php
                    $price = (float) ($product->sale_price ?: $product->price);
                    $rate = (float) ($product->seller->commission_rate ?: config('marketplace.default_commission_rate', 10.00));
                    $platformCommission = $price * ($rate / 100);
                    $sellerPayout = $price - $platformCommission;
                @endphp
                <div class="flex justify-between items-center">
                    <span class="text-slate-400 font-medium"><i class="fa-solid fa-percent mr-1.5 w-3.5 text-center"></i>Commission Rate</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $rate }}%</span>
                </div>
                <div class="flex justify-between items-center border-t border-slate-100 dark:border-slate-800 pt-2.5">
                    <span class="text-slate-400 font-medium"><i class="fa-solid fa-calculator mr-1.5 w-3.5 text-center text-indigo-500"></i>Platform Share</span>
                    <span class="font-bold text-indigo-500 dark:text-indigo-400">&#x20B9;{{ number_format($platformCommission, 2) }}</span>
                </div>
                <div class="flex justify-between items-center border-t border-slate-100 dark:border-slate-800 pt-2.5">
                    <span class="text-slate-400 font-medium"><i class="fa-solid fa-indian-rupee-sign mr-1.5 w-3.5 text-center text-emerald-500"></i>Seller Payout</span>
                    <span class="font-bold text-emerald-500 dark:text-emerald-400">&#x20B9;{{ number_format($sellerPayout, 2) }}</span>
                </div>
            </div>
        </div>
        @endif



    </div>

</div>
@endsection

@pushOnce('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(100, 116, 139, .25);
        border-radius: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(100, 116, 139, .4);
    }
    @keyframes pulse-subtle {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: .85; transform: scale(1.03); }
    }
    .animate-pulse-subtle {
        animation: pulse-subtle 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
@endpushOnce

@push('scripts')
<script>
    function confirmApprove() {
        showConfirm(
            'Are you sure you want to approve this product and publish it live on the store?',
            () => document.getElementById('approveForm').submit(),
            'Approve & Publish?'
        );
    }
    
    function confirmReject() {
        showRejectModal((reason) => {
            document.getElementById('rejectionReasonHiddenInput').value = reason;
            document.getElementById('rejectForm').submit();
        });
    }

    function showRejectModal(callback) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fadeIn';
        modal.innerHTML = `
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-800 transform scale-95 opacity-0 transition-all duration-300 popup-content text-left">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white font-display">Reject Listing</h3>
                    <button class="modal-close text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer border-none bg-transparent outline-none focus:outline-none">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                    Please provide a reason for rejecting this product listing. The seller will see this reason in their dashboard.
                </p>
                <div class="mb-5">
                    <textarea id="rejectionReasonInput" class="w-full min-h-[100px] p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:border-indigo-500" placeholder="e.g. Incomplete specifications, blurry photos, or incorrect category selection..."></textarea>
                    <p id="rejectionErrorMsg" class="text-xs text-rose-500 mt-1.5 hidden">Rejection reason is required.</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button class="modal-cancel border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer transition-colors bg-transparent" style="text-transform: none;">Cancel</button>
                    <button class="modal-confirm bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-lg shadow-rose-600/10 border-none px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer" style="text-transform: none;">Reject</button>
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
            modal.querySelector('#rejectionReasonInput').focus();
        }, 10);

        function closeModal(confirmed = false) {
            const reasonVal = modal.querySelector('#rejectionReasonInput').value.trim();
            if (confirmed) {
                if (!reasonVal) {
                    modal.querySelector('#rejectionErrorMsg').classList.remove('hidden');
                    return;
                }
                
                // Show loader on click reject without closing the modal
                const confirmBtn = modal.querySelector('.modal-confirm');
                const cancelBtn = modal.querySelector('.modal-cancel');
                const closeBtn = modal.querySelector('.modal-close');
                const textarea = modal.querySelector('#rejectionReasonInput');
                
                confirmBtn.disabled = true;
                confirmBtn.style.opacity = '0.7';
                confirmBtn.style.cursor = 'not-allowed';
                confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Rejecting...';
                
                cancelBtn.disabled = true;
                cancelBtn.style.opacity = '0.5';
                cancelBtn.style.cursor = 'not-allowed';
                
                closeBtn.disabled = true;
                textarea.disabled = true;
                
                if (typeof callback === 'function') {
                    callback(reasonVal);
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
@endpush


