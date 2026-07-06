@extends('layouts.admin')
@section('title', $product->name . ' · Product Details — SGCart Admin')

@push('styles')
<style>
.pv-page * { box-sizing: border-box; }
.pv-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 3px 0 rgba(0,0,0,.06), 0 1px 2px -1px rgba(0,0,0,.04);
}
.dark .pv-card { background: #0f172a; border-color: #1e293b; }
.pv-card-header {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 20px;
    border-bottom: 1px solid #f1f5f9;
    background: #fafafa;
}
.dark .pv-card-header { border-color: #1e293b; background: rgba(255,255,255,.025); }
.pv-card-hicon {
    width: 28px; height: 28px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; background: #ede9fe; color: #7c3aed; flex-shrink: 0;
}
.dark .pv-card-hicon { background: rgba(139,92,246,.15); color: #a78bfa; }
.pv-card-htitle {
    font-size: 11px; font-weight: 800; letter-spacing: .09em;
    text-transform: uppercase; color: #64748b;
}
.dark .pv-card-htitle { color: #94a3b8; }
.pv-hero {
    border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
    background: linear-gradient(135deg, #fff 0%, #f8fafc 50%, #f0f4ff 100%);
}
.dark .pv-hero {
    background: linear-gradient(135deg,#0f172a 0%,#111827 60%,#1a1040 100%);
    border-color: #1e293b;
}
.pv-hero-top { padding: 20px 24px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.pv-thumb-lg {
    width: 64px; height: 64px; border-radius: 14px; overflow: hidden;
    border: 1px solid #e2e8f0; background: #020617; flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
}
.dark .pv-thumb-lg { border-color: #334155; }
.pv-thumb-lg img { width: 100%; height: 100%; object-fit: cover; }
.pv-thumb-empty { width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:20px; }
.pv-hero-info { flex: 1; min-width: 0; }
.pv-pname {
    font-size: 20px; font-weight: 800; line-height: 1.2; letter-spacing: -.01em;
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 55%, #ec4899 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.pv-meta-row { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 6px; }
.pv-meta-item { font-size: 11.5px; color: #94a3b8; display: flex; align-items: center; gap: 5px; }
.pv-meta-item strong { color: #475569; font-weight: 600; }
.dark .pv-meta-item strong { color: #cbd5e1; }
.pv-price-box { text-align: right; flex-shrink: 0; }
.pv-price-main { font-size: 26px; font-weight: 900; line-height: 1; letter-spacing: -.02em; color: #1e293b; }
.dark .pv-price-main { color: #f1f5f9; }
.pv-price-main.sale { color: #059669; }
.dark .pv-price-main.sale { color: #34d399; }
.pv-price-orig { font-size: 11px; color: #94a3b8; text-decoration: line-through; margin-top: 2px; }
.pv-pill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 999px;
    font-size: 11px; font-weight: 700; letter-spacing: .04em; line-height: 1.4;
}
.pv-pill.active   { background:#d1fae5; color:#065f46; }
.pv-pill.draft    { background:#fef3c7; color:#92400e; }
.pv-pill.inactive { background:#f1f5f9; color:#475569; }
.dark .pv-pill.active   { background:rgba(16,185,129,.15); color:#34d399; }
.dark .pv-pill.draft    { background:rgba(245,158,11,.15); color:#fbbf24; }
.dark .pv-pill.inactive { background:rgba(148,163,184,.1); color:#94a3b8; }
.pv-pill.indigo  { background:#ede9fe; color:#5b21b6; }
.dark .pv-pill.indigo { background:rgba(139,92,246,.15); color:#a78bfa; }
.pv-pill.rose    { background:#ffe4e6; color:#9f1239; }
.dark .pv-pill.rose   { background:rgba(244,63,94,.15); color:#fb7185; }
.pv-pill.emerald { background:#d1fae5; color:#065f46; }
.dark .pv-pill.emerald{ background:rgba(16,185,129,.15); color:#34d399; }
.pv-pill.amber   { background:#fef3c7; color:#92400e; }
.dark .pv-pill.amber  { background:rgba(245,158,11,.15); color:#fbbf24; }
.pv-pill.slate   { background:#f1f5f9; color:#475569; }
.dark .pv-pill.slate  { background:rgba(148,163,184,.1); color:#94a3b8; }
.pv-pill.pink    { background:#fce7f3; color:#9d174d; }
.dark .pv-pill.pink   { background:rgba(236,72,153,.15); color:#f472b6; }
.pv-stats-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    border-top: 1px solid #e2e8f0;
}
.dark .pv-stats-grid { border-color: #1e293b; }
@media (max-width: 640px) { .pv-stats-grid { grid-template-columns: 1fr 1fr; } }
.pv-stat {
    display: flex; align-items: center; gap: 16px;
    padding: 16px 20px; border-right: 1px solid #f1f5f9;
}
.dark .pv-stat { border-color: #1e293b; }
.pv-stat:last-child { border-right: none; }
@media (max-width: 640px) {
    .pv-stat:nth-child(2n) { border-right: none; }
    .pv-stat:nth-child(n+3) { border-top: 1px solid #f1f5f9; }
    .dark .pv-stat:nth-child(n+3) { border-color: #1e293b; }
}
.pv-sicon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}
.pv-slabel { font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#94a3b8;margin-bottom:4px; }
.pv-svalue { font-size:24px;font-weight:900;line-height:1;letter-spacing:-.02em; }
.pv-ssub   { font-size:11px;font-weight:600; }
.pv-sicon.green  { background:#d1fae5; color:#059669; }
.pv-sicon.amber  { background:#fef3c7; color:#d97706; }
.pv-sicon.rose   { background:#ffe4e6; color:#e11d48; }
.pv-sicon.indigo { background:#ede9fe; color:#7c3aed; }
.pv-sicon.violet { background:#f3e8ff; color:#7c3aed; }
.pv-sicon.pink   { background:#fce7f3; color:#db2777; }
.dark .pv-sicon.green  { background:rgba(16,185,129,.15); color:#34d399; }
.dark .pv-sicon.amber  { background:rgba(245,158,11,.15); color:#fbbf24; }
.dark .pv-sicon.rose   { background:rgba(244,63,94,.15);  color:#fb7185; }
.dark .pv-sicon.indigo { background:rgba(139,92,246,.15); color:#a78bfa; }
.dark .pv-sicon.violet { background:rgba(167,139,250,.15);color:#c4b5fd; }
.dark .pv-sicon.pink   { background:rgba(236,72,153,.15); color:#f472b6; }
.pv-svalue.green  { color:#065f46; }  .dark .pv-svalue.green  { color:#34d399; }
.pv-svalue.amber  { color:#92400e; }  .dark .pv-svalue.amber  { color:#fbbf24; }
.pv-svalue.rose   { color:#9f1239; }  .dark .pv-svalue.rose   { color:#fb7185; }
.pv-svalue.indigo { color:#4c1d95; }  .dark .pv-svalue.indigo { color:#a78bfa; }
.pv-svalue.violet { color:#5b21b6; }  .dark .pv-svalue.violet { color:#c4b5fd; }
.pv-svalue.pink   { color:#831843; }  .dark .pv-svalue.pink   { color:#f472b6; }
.pv-ssub.green  { color:#059669; }    .dark .pv-ssub.green  { color:#6ee7b7; }
.pv-ssub.amber  { color:#d97706; }    .dark .pv-ssub.amber  { color:#fcd34d; }
.pv-ssub.rose   { color:#e11d48; }    .dark .pv-ssub.rose   { color:#fca5a5; }
.pv-ssub.indigo { color:#6d28d9; }    .dark .pv-ssub.indigo { color:#c4b5fd; }
.pv-ssub.violet { color:#7c3aed; }    .dark .pv-ssub.violet { color:#ddd6fe; }
.pv-ssub.pink   { color:#db2777; }    .dark .pv-ssub.pink   { color:#fbcfe8; }
.pv-gmain {
    flex: 1; min-height: 300px; max-height: 400px;
    background: #020617; border-radius: 12px; overflow: hidden;
    display: flex; align-items: center; justify-content: center;
}
.pv-gmain img {
    width: 100%; height: 100%; max-height: 400px;
    object-fit: contain; transition: opacity .2s ease;
}
.pv-gmain img.fading { opacity: 0; }
.pv-gthumbs {
    display: flex; flex-direction: column; gap: 8px;
    max-height: 400px; overflow-y: auto; padding-right: 2px;
    width: 78px; flex-shrink: 0;
}
.pv-gthumbs::-webkit-scrollbar { width: 4px; }
.pv-gthumbs::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
.dark .pv-gthumbs::-webkit-scrollbar-thumb { background: #334155; }
.pv-gthumb {
    width: 74px; height: 74px; border-radius: 10px; overflow: hidden;
    border: 2px solid transparent; background: #020617;
    cursor: pointer; transition: all .18s; flex-shrink: 0; padding: 0;
}
.pv-gthumb:hover  { border-color: #818cf8; transform: scale(1.04); }
.pv-gthumb.active { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.2); }
.pv-gthumb img    { width: 100%; height: 100%; object-fit: cover; display: block; }
.pv-slabel2 {
    font-size: 10px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #94a3b8;
    padding-bottom: 10px; border-bottom: 1.5px solid #f1f5f9; margin-bottom: 12px;
}
.dark .pv-slabel2 { border-color: #1e293b; }
.pv-vt { width: 100%; border-collapse: collapse; }
.pv-vt thead tr { border-bottom: 1.5px solid #f1f5f9; background: #f8fafc; }
.dark .pv-vt thead tr { border-color: #1e293b; background: rgba(255,255,255,.025); }
.pv-vt thead th {
    padding: 10px 16px; text-align: left;
    font-size: 10px; font-weight: 800; letter-spacing: .1em;
    text-transform: uppercase; color: #94a3b8; white-space: nowrap;
}
.pv-vt tbody tr { border-bottom: 1px solid #f8fafc; transition: background .12s; }
.dark .pv-vt tbody tr { border-color: rgba(255,255,255,.04); }
.pv-vt tbody tr:hover { background: rgba(99,102,241,.04); }
.pv-vt tbody tr:last-child { border-bottom: none; }
.pv-vt td { padding: 12px 16px; vertical-align: middle; font-size: 13px; }
.pv-cswatch {
    width: 12px; height: 12px; border-radius: 50%;
    border: 1.5px solid rgba(0,0,0,.12); display: inline-block; flex-shrink: 0;
}
.pv-irow {
    display: flex; justify-content: space-between; align-items: center;
    padding: 11px 0; border-bottom: 1px dashed #f1f5f9;
    font-size: 13px; gap: 12px;
}
.dark .pv-irow { border-color: rgba(255,255,255,.05); }
.pv-irow:last-child { border-bottom: none; padding-bottom: 0; }
.pv-ilabel { color: #94a3b8; flex-shrink: 0; }
.pv-ivalue { font-weight: 600; color: #334155; text-align: right; }
.dark .pv-ivalue { color: #cbd5e1; }
.pv-sblock {
    border-radius: 12px; padding: 14px 16px;
    display: flex; justify-content: space-between; align-items: center;
}
.pv-sblock.active   { background:#ecfdf5; border:1px solid #a7f3d0; }
.pv-sblock.draft    { background:#fffbeb; border:1px solid #fde68a; }
.pv-sblock.inactive { background:#f8fafc; border:1px solid #e2e8f0; }
.dark .pv-sblock.active   { background:rgba(16,185,129,.08); border-color:rgba(16,185,129,.2); }
.dark .pv-sblock.draft    { background:rgba(245,158,11,.08);  border-color:rgba(245,158,11,.2); }
.dark .pv-sblock.inactive { background:rgba(148,163,184,.06); border-color:rgba(148,163,184,.15); }
.pv-sname { font-weight: 700; font-size: 14px; }
.pv-sname.active   { color:#065f46; }  .dark .pv-sname.active   { color:#34d399; }
.pv-sname.draft    { color:#92400e; }  .dark .pv-sname.draft    { color:#fbbf24; }
.pv-sname.inactive { color:#475569; }  .dark .pv-sname.inactive { color:#94a3b8; }
.pv-salebox {
    border-radius: 12px; background: #f0fdf4;
    border: 1px solid #a7f3d0; padding: 14px 16px;
}
.dark .pv-salebox { background:rgba(16,185,129,.08); border-color:rgba(16,185,129,.2); }
.pv-empty {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; padding: 48px 24px; color: #94a3b8;
}
.pv-eicon {
    width: 56px; height: 56px; border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; background: #f1f5f9; margin-bottom: 12px; opacity: .7;
}
.dark .pv-eicon { background: #1e293b; }
.pv-back {
    width: 38px; height: 38px; border-radius: 10px;
    border: 1px solid #e2e8f0; background: #fff;
    display: flex; align-items: center; justify-content: center;
    color: #64748b; text-decoration: none; transition: all .15s; flex-shrink: 0;
}
.pv-back:hover { background:#f8fafc; border-color:#cbd5e1; color:#334155; }
.dark .pv-back { background:#0f172a; border-color:#334155; color:#94a3b8; }
.dark .pv-back:hover { background:#1e293b; color:#cbd5e1; }
.pv-btn-out {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 10px;
    border: 1px solid #e2e8f0; background: #fff;
    color: #475569; font-size: 13px; font-weight: 600;
    text-decoration: none; transition: all .15s; white-space: nowrap;
}
.pv-btn-out:hover { background:#f8fafc; border-color:#cbd5e1; color:#1e293b; }
.dark .pv-btn-out { background:#0f172a; border-color:#334155; color:#cbd5e1; }
.dark .pv-btn-out:hover { background:#1e293b; }
.pv-btn-out.off { pointer-events:none; opacity:.45; cursor:not-allowed; }
.pv-btn-pri {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: 10px;
    background: #6366f1; color: #fff;
    font-size: 13px; font-weight: 700;
    text-decoration: none; transition: background .15s, box-shadow .15s;
    box-shadow: 0 4px 14px rgba(99,102,241,.3); white-space: nowrap;
}
.pv-btn-pri:hover { background:#4f46e5; box-shadow:0 6px 20px rgba(99,102,241,.4); }
.pv-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
    align-items: start;
}
@media (max-width: 1023px) { .pv-layout { grid-template-columns: 1fr; } }
.pv-col  { display: flex; flex-direction: column; gap: 20px; min-width: 0; }
.pv-side { display: flex; flex-direction: column; gap: 16px; }
</style>
@endpush

@section('content')
@php
    $defaultImage   = $product->images->firstWhere('is_default', true) ?: $product->images->first();
    $totalVariants  = $product->variants->count();
    $activeVariants = $product->variants->where('is_active', true)->count();
    $totalVarStock  = $product->variants->sum('stock');
    $statusKey      = in_array($product->status->value, ['active','draft','inactive','rejected']) ? $product->status->value : 'inactive';
@endphp
<div class="pv-page">

<!-- PAGE HEADER -->
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="{{ route('admin.products.index') }}" class="pv-back">
            <i class="fa-solid fa-arrow-left" style="font-size:13px;"></i>
        </a>
        <div>
            <h1 style="font-size:22px;font-weight:800;letter-spacing:-.02em;line-height:1.2;margin:0 0 2px;">Product Details</h1>
            <x-breadcrumbs :items="[
                ['label' => 'Admin',    'url' => route('admin.dashboard')],
                ['label' => 'Catalogue'],
                ['label' => 'Products', 'url' => route('admin.products.index')],
                ['label' => $product->name, 'mono' => true]
            ]" />
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        @if($product->status === 'active')
            <a href="{{ route('store.product', $product->slug) }}" target="_blank" class="pv-btn-out">
                <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:11px;color:#94a3b8;"></i>
                Product Preview
            </a>
        @else
            <span class="pv-btn-out off" title="Publish product to enable storefront preview">
                <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:11px;"></i>
                Product Preview
            </span>
        @endif
        <a href="{{ route('admin.products.edit', $product->ulid) }}" class="pv-btn-pri">
            <i class="fa-solid fa-pen-to-square" style="font-size:11px;"></i>
            Edit Product
        </a>
    </div>
</div>

<!-- HERO -->
<div class="pv-hero" style="margin-bottom:20px;">
    <div class="pv-hero-top">
        <div class="pv-thumb-lg">
            @if($defaultImage)
                <img src="{{ Storage::url($defaultImage->image_path) }}" alt="{{ $product->name }}">
            @else
                <div class="pv-thumb-empty"><i class="fa-solid fa-image"></i></div>
            @endif
        </div>
        <div class="pv-hero-info">
            <div style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:6px;">
                <span class="pv-pname">{{ $product->name }}</span>
                <span class="pv-pill {{ $statusKey }}">
                    <i class="fa-solid fa-circle" style="font-size:5px;opacity:.7;"></i>
                    {{ ucfirst($product->status->value) }}
                </span>
                @if($product->seller)
                    <span class="pv-pill indigo" title="Seller Product: {{ $product->seller->shop_name }}">
                        <i class="fa-solid fa-store" style="font-size:9px;opacity:.7;"></i>
                        {{ \Illuminate\Support\Str::limit($product->seller->shop_name, 18) }}
                    </span>
                @else
                    <span class="pv-pill emerald">
                        <i class="fa-solid fa-shield-halved" style="font-size:9px;opacity:.7;"></i>
                        SGCart
                    </span>
                @endif
            </div>
            <div class="pv-meta-row">
                @if($product->sku)
                <span class="pv-meta-item">
                    <i class="fa-solid fa-barcode" style="opacity:.45;"></i>
                    <strong style="font-family:monospace;">{{ $product->sku }}</strong>
                </span>
                @endif
                @if($product->category)
                <span class="pv-meta-item">
                    <i class="fa-solid fa-folder-open" style="opacity:.45;"></i>
                    {{ $product->category->name }}
                </span>
                @endif
                @if($product->manufacturer)
                <span class="pv-meta-item">
                    <i class="fa-solid fa-industry" style="opacity:.45;"></i>
                    {{ $product->manufacturer->name }}
                </span>
                @endif
                <span class="pv-meta-item">
                    <i class="fa-regular fa-clock" style="opacity:.45;"></i>
                    Updated {{ $product->updated_at->diffForHumans() }}
                </span>
            </div>
        </div>
        <div class="pv-price-box">
            @if($product->sale_price)
                @php $heroPct = round(($product->price - $product->sale_price) / $product->price * 100); @endphp
                <div class="pv-price-main sale">&#x20B9;{{ number_format($product->sale_price, 2) }}</div>
                <div class="pv-price-orig">&#x20B9;{{ number_format($product->price, 2) }}</div>
                <span class="pv-pill emerald" style="margin-top:6px;">{{ $heroPct }}% OFF</span>
            @else
                <div class="pv-price-main">&#x20B9;{{ number_format($product->price, 2) }}</div>
                <div style="font-size:11px;color:#94a3b8;margin-top:2px;">Base Price</div>
            @endif
        </div>
    </div>
    @php
        $sColor = $product->stock > 10 ? 'green' : ($product->stock > 0 ? 'amber' : 'rose');
        $sLabel = $product->stock > 10 ? 'In Stock' : ($product->stock > 0 ? 'Low Stock' : 'Out of Stock');
    @endphp
    <div class="pv-stats-grid">
        <div class="pv-stat">
            <div class="pv-sicon {{ $sColor }}"><i class="fa-solid fa-boxes-stacked"></i></div>
            <div style="display:flex; flex-direction:column; justify-content:center;">
                <div class="pv-slabel">Base Stock</div>
                <div style="display:flex; align-items:baseline; gap:8px; flex-wrap:wrap;">
                    <span class="pv-svalue {{ $sColor }}">{{ $product->stock }}</span>
                    <span class="pv-ssub {{ $sColor }}">{{ $sLabel }}</span>
                </div>
            </div>
        </div>
        @if(Route::has('admin.products.variants.grid'))
        <div class="pv-stat">
            <div class="pv-sicon indigo"><i class="fa-solid fa-tags"></i></div>
            <div style="display:flex; flex-direction:column; justify-content:center;">
                <div class="pv-slabel">Variants</div>
                <div style="display:flex; align-items:baseline; gap:8px; flex-wrap:wrap;">
                    <span class="pv-svalue indigo">{{ $totalVariants }}</span>
                    <span class="pv-ssub indigo">{{ $activeVariants }} Active</span>
                </div>
            </div>
        </div>
        <div class="pv-stat">
            <div class="pv-sicon violet"><i class="fa-solid fa-layer-group"></i></div>
            <div style="display:flex; flex-direction:column; justify-content:center;">
                <div class="pv-slabel">Variant Stock</div>
                <div style="display:flex; align-items:baseline; gap:8px; flex-wrap:wrap;">
                    <span class="pv-svalue violet">{{ $totalVarStock }}</span>
                    <span class="pv-ssub violet">All variants</span>
                </div>
            </div>
        </div>
        @endif
        <div class="pv-stat">
            <div class="pv-sicon pink"><i class="fa-solid fa-images"></i></div>
            <div style="display:flex; flex-direction:column; justify-content:center;">
                <div class="pv-slabel">Gallery</div>
                <div style="display:flex; align-items:baseline; gap:8px; flex-wrap:wrap;">
                    <span class="pv-svalue pink">{{ $product->images->count() }}</span>
                    <span class="pv-ssub pink">Photo{{ $product->images->count() !== 1 ? 's' : '' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- LAYOUT -->
<div class="pv-layout">
    <div class="pv-col">
        <!-- Gallery Card -->
        <div class="pv-card">
            <div class="pv-card-header">
                <div class="pv-card-hicon"><i class="fa-solid fa-images"></i></div>
                <span class="pv-card-htitle">Product Gallery</span>
                <span class="pv-pill slate" style="margin-left:auto;">{{ $product->images->count() }} Photo{{ $product->images->count() !== 1 ? 's' : '' }}</span>
            </div>
            <div style="padding:16px;">
                @if($product->images->count())
                    <div style="display:flex;gap:14px;align-items:flex-start;">
                        <div class="pv-gmain">
                            <img id="pvMainImg" src="{{ Storage::url($defaultImage->image_path) }}" alt="{{ $product->name }}">
                        </div>
                        <div class="pv-gthumbs">
                            @foreach($product->images as $img)
                            <button type="button" onclick="pvSwitch('{{ Storage::url($img->image_path) }}', this)" class="pv-gthumb {{ $img->is_default ? 'active' : '' }}">
                                <img src="{{ Storage::url($img->image_path) }}" alt="thumb">
                            </button>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="pv-empty">
                        <div class="pv-eicon"><i class="fa-solid fa-image-slash"></i></div>
                        <p style="font-size:14px;font-weight:600;margin:0 0 4px;">No images uploaded</p>
                        <p style="font-size:12px;margin:0;">Upload photos from the edit page</p>
                    </div>
                @endif
            </div>
        </div>
        <!-- Description Card -->
        <div class="pv-card">
            <div class="pv-card-header">
                <div class="pv-card-hicon"><i class="fa-solid fa-align-left"></i></div>
                <span class="pv-card-htitle">Product Description</span>
            </div>
            <div style="padding:20px;display:flex;flex-direction:column;gap:20px;">
                <div>
                    <div class="pv-slabel2">Short Description</div>
                    <p style="font-size:13.5px;color:#475569;line-height:1.75;margin:0;">{!! nl2br(e($product->short_description ?: '— No short description provided.')) !!}</p>
                </div>
                <div>
                    <div class="pv-slabel2">Full Description</div>
                    <div style="font-size:13.5px;color:#475569;line-height:1.75;">{!! nl2br(e($product->description ?: '— No full description provided.')) !!}</div>
                </div>
            </div>
        </div>
        
        @if($product->seller)
        <!-- Seller & Financials Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <!-- Seller Shop Info -->
            <div class="pv-card" style="margin: 0;">
                <div class="pv-card-header">
                    <div class="pv-card-hicon"><i class="fa-solid fa-store"></i></div>
                    <span class="pv-card-htitle">Seller &amp; Shop</span>
                </div>
                <div style="padding:16px;display:flex;flex-direction:column;gap:12px;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="pv-sicon indigo" style="width:36px;height:36px;font-size:14px;"><i class="fa-solid fa-store"></i></div>
                        <div style="min-width:0;flex:1;">
                            <div class="pv-ivalue" style="text-align:left;font-size:14px;text-overflow:ellipsis;overflow:hidden;white-space:nowrap;">
                                {{ $product->seller->shop_name }}
                            </div>
                            @if(Route::has('admin.sellers.show'))
                                <a href="{{ route('admin.sellers.show', $product->seller) }}" style="font-size:11px;color:#6366f1;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:3px;">
                                    View Profile <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:9px;"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                    <div style="margin-top:4px;">
                        <div class="pv-irow">
                            <span class="pv-ilabel">Shop Owner</span>
                            <span class="pv-ivalue">{{ $product->seller->name }}</span>
                        </div>
                        <div class="pv-irow">
                            <span class="pv-ilabel">Email</span>
                            <span class="pv-ivalue" style="font-family:monospace;font-size:12px;word-break:break-all;">{{ $product->seller->email }}</span>
                        </div>
                        <div class="pv-irow">
                            <span class="pv-ilabel">Commission</span>
                            <span class="pv-ivalue">
                                {{ $product->seller->commission_rate ? $product->seller->commission_rate.'%' : 'Default ('.config('marketplace.default_commission_rate', 10.00).'%)' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Commission & Payout -->
            <div class="pv-card" style="margin: 0;">
                <div class="pv-card-header">
                    <div class="pv-card-hicon"><i class="fa-solid fa-indian-rupee-sign"></i></div>
                    <span class="pv-card-htitle">Commission &amp; Payout</span>
                </div>
                <div style="padding:16px;display:flex;flex-direction:column;gap:12px;">
                    @php
                        $price = (float) ($product->sale_price ?: $product->price);
                        $rate = (float) ($product->seller->commission_rate ?: config('marketplace.default_commission_rate', 10.00));
                        $platformCommission = $price * ($rate / 100);
                        $sellerPayout = $price - $platformCommission;
                    @endphp
                    <div class="pv-irow">
                        <span class="pv-ilabel">Commission Rate</span>
                        <span class="pv-ivalue">
                            {{ $product->seller->commission_rate ? $product->seller->commission_rate.'%' : 'Default ('.config('marketplace.default_commission_rate', 10.00).'%)' }}
                        </span>
                    </div>
                    <div class="pv-irow">
                        <span class="pv-ilabel">Platform Share</span>
                        <span class="pv-ivalue" style="color:#7c3aed;font-weight:700;">
                            &#x20B9;{{ number_format($platformCommission, 2) }}
                        </span>
                    </div>
                    <div class="pv-irow">
                        <span class="pv-ilabel">Seller Payout</span>
                        <span class="pv-ivalue" style="color:#059669;font-weight:700;">
                            &#x20B9;{{ number_format($sellerPayout, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(Route::has('admin.products.variants.grid'))
        <!-- Variants Card -->
        <div class="pv-card">
            <div class="pv-card-header">
                <div class="pv-card-hicon"><i class="fa-solid fa-swatchbook"></i></div>
                <span class="pv-card-htitle">Product Variants</span>
                <span class="pv-pill indigo" style="margin-left:auto;">{{ $totalVariants }} Total</span>
            </div>
            @if($product->variants->count())
            <div style="overflow-x:auto;">
                <table class="pv-vt">
                    <thead>
                        <tr>
                            <th style="width:60px;">Image</th>
                            <th>Color</th><th>Size</th><th>SKU Override</th>
                            <th>Price</th>
                            <th>Sale Price</th>
                            <th style="text-align:center;">Stock</th>
                            <th style="text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($product->variants as $variant)
                    <tr style="{{ !$variant->is_active ? 'opacity:.45;' : '' }}">
                        <td>
                            @if($variant->image)
                                <div class="pv-gthumb" style="width:40px; height:40px; border-radius:6px; overflow:hidden; border:1px solid #e2e8f0; cursor:pointer;" onclick="pvSwitch('{{ Storage::url($variant->image) }}', this)">
                                    <img src="{{ Storage::url($variant->image) }}" alt="Variant Image" style="width:100%; height:100%; object-fit:cover;">
                                </div>
                            @else
                                <div style="width:40px; height:40px; border-radius:6px; background:#f1f5f9; border:1px solid #e2e8f0; display:flex; align-items:center; justify-content:center; color:#94a3b8;">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:7px;">
                                @if($variant->color)
                                    @if($variant->color->hex_code ?? false)
                                        <span class="pv-cswatch" style="background:{{ $variant->color->hex_code }};"></span>
                                    @endif
                                    <span style="font-weight:600;color:#1e293b;">{{ $variant->color->name }}</span>
                                @else
                                    <span style="color:#94a3b8;">—</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($variant->size)
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span style="font-weight:600;color:#1e293b;">{{ $variant->size->name }}</span>
                                    @if($variant->size->code)
                                        <span class="pv-pill slate" style="font-size:10px;">{{ $variant->size->code }}</span>
                                    @endif
                                </div>
                            @else
                                <span style="color:#94a3b8;">—</span>
                            @endif
                        </td>
                        <td style="font-family:monospace;font-size:12px;color:#64748b;">{{ $variant->sku ?: $product->sku }}</td>
                        <td>
                            @if($variant->price)
                                <span style="font-weight:700;font-size:13px;color:#1e293b;">&#x20B9;{{ number_format($variant->price, 2) }}</span>
                            @else
                                <span style="font-size:12px;color:#94a3b8;font-style:italic;">Inherit <span style="font-style:normal;font-weight:600;color:#64748b;">&#x20B9;{{ number_format($product->price, 2) }}</span></span>
                            @endif
                        </td>
                        <td>
                            @if($variant->sale_price)
                                <span style="font-weight:700;font-size:13px;color:#1e293b;">&#x20B9;{{ number_format($variant->sale_price, 2) }}</span>
                            @elseif($product->sale_price)
                                <span style="font-size:12px;color:#94a3b8;font-style:italic;">Inherit <span style="font-style:normal;font-weight:600;color:#64748b;">&#x20B9;{{ number_format($product->sale_price, 2) }}</span></span>
                            @else
                                <span style="color:#94a3b8;font-size:12px;font-style:italic;">—</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <span class="pv-pill {{ $variant->stock > 0 ? 'emerald' : 'rose' }}">{{ $variant->stock }} qty</span>
                        </td>
                        <td style="text-align:center;">
                            <span class="pv-pill {{ $variant->is_active ? 'emerald' : 'slate' }}">{{ $variant->is_active ? 'Active' : 'Off' }}</span>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="pv-empty">
                <div class="pv-eicon"><i class="fa-solid fa-tags"></i></div>
                <p style="font-size:14px;font-weight:600;margin:0 0 4px;">No variants configured</p>
                <p style="font-size:12px;margin:0;">Add color &amp; size combos from the edit page</p>
            </div>
            @endif
        </div>
        @endif
    </div>
    <!-- SIDEBAR -->
    <div class="pv-side">
        <!-- Status -->
        <div class="pv-card">
            <div class="pv-card-header">
                <div class="pv-card-hicon"><i class="fa-solid fa-circle-dot"></i></div>
                <span class="pv-card-htitle">Status &amp; Activity</span>
            </div>
            <div style="padding:16px;display:flex;flex-direction:column;gap:12px;">
                <div class="pv-sblock {{ $statusKey }}">
                    <div>
                        <div style="font-size:10px;color:#94a3b8;font-weight:700;letter-spacing:.07em;text-transform:uppercase;margin-bottom:3px;">Publish Status</div>
                        <div class="pv-sname {{ $statusKey }}">
                            <i class="fa-solid fa-circle" style="font-size:7px;margin-right:5px;opacity:.7;"></i>
                            {{ ucfirst($product->status->value) }}
                        </div>
                    </div>
                    @if($product->status->value === 'active')
                        <i class="fa-solid fa-circle-check" style="font-size:22px;color:#10b981;opacity:.7;"></i>
                    @elseif($product->status->value === 'draft')
                        <i class="fa-solid fa-pen-ruler" style="font-size:22px;color:#f59e0b;opacity:.7;"></i>
                    @elseif($product->status->value === 'rejected')
                        <i class="fa-solid fa-triangle-exclamation" style="font-size:22px;color:#ef4444;opacity:.8;"></i>
                    @else
                        <i class="fa-solid fa-eye-slash" style="font-size:22px;color:#94a3b8;opacity:.5;"></i>
                    @endif
                </div>
                <div>
                    <div class="pv-irow">
                        <span class="pv-ilabel">Created</span>
                        <span class="pv-ivalue" style="font-size:12px;font-family:monospace;">{{ $product->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="pv-irow">
                        <span class="pv-ilabel">Last Updated</span>
                        <span class="pv-ivalue" style="font-size:12px;font-family:monospace;">{{ $product->updated_at->format('d M Y') }}</span>
                    </div>
                    <div class="pv-irow">
                        <span class="pv-ilabel">Relative</span>
                        <span class="pv-ivalue" style="color:#6366f1;">{{ $product->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="pv-card">
            <div class="pv-card-header">
                <div class="pv-card-hicon"><i class="fa-solid fa-indian-rupee-sign"></i></div>
                <span class="pv-card-htitle">Pricing</span>
            </div>
            <div style="padding:16px;display:flex;flex-direction:column;gap:14px;">
                <div>
                    <div style="font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#94a3b8;margin-bottom:4px;">Base Price</div>
                    <div style="font-size:28px;font-weight:900;letter-spacing:-.02em;color:#1e293b;line-height:1;">&#x20B9;{{ number_format($product->price, 2) }}</div>
                </div>
                @if($product->sale_price)
                @php $sv = $product->price - $product->sale_price; $spct = ($sv / $product->price) * 100; @endphp
                <div class="pv-salebox">
                    <div style="font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#059669;margin-bottom:4px;">Sale Price</div>
                    <div style="font-size:22px;font-weight:900;letter-spacing:-.02em;color:#059669;line-height:1;">&#x20B9;{{ number_format($product->sale_price, 2) }}</div>
                    <div style="margin-top:8px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span class="pv-pill emerald">Save {{ number_format($spct, 0) }}%</span>
                        <span style="font-size:12px;color:#059669;font-weight:600;">&#x20B9;{{ number_format($sv, 2) }} off</span>
                    </div>
                </div>
                @else
                <div style="border-radius:10px;background:#f8fafc;border:1.5px dashed #e2e8f0;padding:14px;text-align:center;">
                    <p style="font-size:12px;color:#94a3b8;margin:0;">No sale price set</p>
                </div>
                @endif
            </div>
        </div>


        <!-- Inventory -->
        <div class="pv-card">
            <div class="pv-card-header">
                <div class="pv-card-hicon"><i class="fa-solid fa-warehouse"></i></div>
                <span class="pv-card-htitle">Inventory</span>
            </div>
            <div style="padding:16px;">
                <div class="pv-irow"><span class="pv-ilabel">SKU Code</span><span class="pv-ivalue" style="font-family:monospace;font-size:12px;">{{ $product->sku ?: '—' }}</span></div>
                <div class="pv-irow"><span class="pv-ilabel">Base Stock</span><span class="pv-ivalue" style="font-size:16px;font-weight:800;">{{ $product->stock }}</span></div>
                <div class="pv-irow">
                    <span class="pv-ilabel">Availability</span>
                    @if($product->stock > 10)
                        <span class="pv-pill emerald">In Stock</span>
                    @elseif($product->stock > 0)
                        <span class="pv-pill amber">Low Stock</span>
                    @else
                        <span class="pv-pill rose">Out of Stock</span>
                    @endif
                </div>
                @if($totalVariants)
                <div class="pv-irow"><span class="pv-ilabel">Variant Stock</span><span class="pv-ivalue" style="color:#6366f1;font-weight:800;">{{ $totalVarStock }}</span></div>
                @endif
            </div>
        </div>
        <!-- Specifications -->
        <div class="pv-card">
            <div class="pv-card-header">
                <div class="pv-card-hicon"><i class="fa-solid fa-ruler-combined"></i></div>
                <span class="pv-card-htitle">Specifications</span>
            </div>
            <div style="padding:16px;">
                <div class="pv-irow"><span class="pv-ilabel">Category</span><span class="pv-ivalue">{{ $product->category?->name ?? '—' }}</span></div>
                <div class="pv-irow"><span class="pv-ilabel">Manufacturer</span><span class="pv-ivalue">{{ $product->manufacturer?->name ?? '—' }}</span></div>
                <div class="pv-irow"><span class="pv-ilabel">Weight</span><span class="pv-ivalue" style="font-family:monospace;font-size:12px;">{{ $product->weight ?: '—' }}</span></div>
                <div class="pv-irow"><span class="pv-ilabel">Dimensions</span><span class="pv-ivalue" style="font-family:monospace;font-size:12px;">{{ $product->dimensions ?: '—' }}</span></div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
function pvSwitch(src, btn) {
    var img = document.getElementById('pvMainImg');
    if (!img) return;
    img.classList.add('fading');
    setTimeout(function() { img.src = src; img.classList.remove('fading'); }, 180);
    document.querySelectorAll('.pv-gthumb').forEach(function(t) { t.classList.remove('active'); });
    if (btn && btn.classList.contains('pv-gthumb')) {
        btn.classList.add('active');
    }
}
</script>
@endpush
