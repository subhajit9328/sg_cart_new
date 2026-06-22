@extends('layouts.store')

@section('title', $product['name'] . ' — sgcart')

@section('content')


<div class="section-inner pt-8 md:pt-12">
    
    <!-- PRODUCT PANEL -->
    <div class="pd-layout">
        
        <!-- Left Gallery Column -->
        <!-- Left Gallery Column -->
        <div class="pd-gallery">
            @if(isset($product['images']) && count($product['images']) > 1)
                <div class="pd-thumbs">
                    @foreach($product['images'] as $imgSrc)
                        <div class="pd-thumb {{ $loop->first ? 'active' : '' }}" onclick="changeImage('{{ $imgSrc }}', this)">
                            <img src="{{ $imgSrc }}" alt="{{ $product['name'] }} Thumb"/>
                        </div>
                    @endforeach
                </div>
            @endif
            
            <div class="pd-main-img">
                <img id="mainProductImg" src="{{ $product['img'] }}" alt="{{ $product['name'] }}"/>
                @if($product['badge'])
                    <span class="product-badge badge-{{ strtolower($product['badge']) }}" style="top:16px; left:16px">{{ $product['badge'] }}</span>
                @endif
            </div>
        </div>

        <!-- Right Purchase Options Column -->
        <div>
            <form action="{{ route('store.cart.add') }}" method="POST" id="purchaseForm">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product['id'] }}"/>
                
                <p class="text-xs font-bold text-accent uppercase tracking-widest mb-1.5">{{ $product['cat'] }} Capsule</p>
                <h1 class="font-display font-extrabold text-3xl text-slate-900 leading-tight mb-2">{{ $product['name'] }}</h1>
                


                <!-- Pricing -->
                <div class="flex items-end gap-3 mb-5 pb-5 border-b border-slate-100">
                    <span class="font-display font-extrabold text-2xl text-slate-900">₹{{ number_format($product['price'], 2) }}</span>
                    @if($product['old'])
                        <span class="text-lg text-slate-400 line-through">₹{{ number_format($product['old'], 2) }}</span>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Save {{ round((1 - $product['price'] / $product['old']) * 100) }}%</span>
                    @endif
                </div>

                <p class="text-xs text-slate-500 leading-relaxed mb-6">{{ $product['desc'] }}</p>

                <!-- Size Picker -->
                @if(!empty($product['sizes']))
                    <div class="mb-5">
                        <label class="label">Select Size</label>
                        <input type="hidden" name="size" id="sizeInput" value="{{ $product['sizes'][0] ?? '' }}"/>
                        <div class="flex flex-wrap gap-2">
                            @foreach($product['sizes'] as $sz)
                                <button type="button" class="size-btn {{ $loop->first ? 'active' : '' }}" onclick="selectSize('{{ $sz }}', this)">
                                    {{ $sz }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Color Picker -->
                @if(!empty($product['colors']))
                    <div class="mb-6">
                        <label class="label">Select Color</label>
                        <input type="hidden" name="color" id="colorInput" value="{{ $product['colors'][0] ?? '' }}"/>
                        <div class="flex gap-3">
                            @foreach($product['colors'] as $col)
                                <button type="button" class="color-btn {{ $loop->first ? 'active' : '' }}" 
                                    style="background: {{ $col }}" 
                                    onclick="selectColor('{{ $col }}', this)">
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Quantity & Actions -->
                <div class="flex gap-3 items-end pt-5 border-t border-slate-100 mb-6">
                    <div>
                        <label class="label">Quantity</label>
                        <div class="qty-row" style="height:46px">
                            <button type="button" class="qty-btn" onclick="adjQty(-1)"><i class="fa-solid fa-minus text-xs"></i></button>
                            <input type="number" name="quantity" id="qtyInput" value="1" min="1" class="qty-num" readonly/>
                            <button type="button" class="qty-btn" onclick="adjQty(1)"><i class="fa-solid fa-plus text-xs"></i></button>
                        </div>
                    </div>
                    <div class="flex-1">
                        <button type="submit" class="btn btn-primary w-full" style="height:46px"><i class="fa-solid fa-bag-shopping"></i> Add To Bag</button>
                    </div>
                    <div>
                        @php
                            $inWishlist = in_array($product['id'], session('wishlist', [3, 5, 6]));
                        @endphp
                        <button type="button" onclick="document.getElementById('wlForm').submit();" 
                            class="btn btn-outline" style="height:46px; width:46px; padding:0; display:flex; align-items:center; justify-content:center;" 
                            title="{{ $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' }}">
                            <i class="{{ $inWishlist ? 'fa-solid fa-heart text-rose-500' : 'fa-regular fa-heart text-slate-500' }} text-base"></i>
                        </button>
                    </div>
                </div>

                <!-- Info list -->
                <div class="border-t border-slate-100 pt-3">
                    <div class="info-row"><i class="fa-solid fa-truck-fast"></i><span>Free global delivery on orders.</span></div>
                    <div class="info-row"><i class="fa-solid fa-rotate-left"></i><span>30-day effortless returns and refunds.</span></div>
                </div>

                <!-- Product specs tabs -->
                <div class="mt-6 pt-5 border-t border-slate-100">
                    <div class="flex gap-4 border-b border-slate-100 pb-2 mb-3">
                        <button type="button" class="tab-btn active" onclick="setSpecTab('description')">Description</button>
                        @if(!empty($product['sku']) || !empty($product['manufacturer']) || !empty($product['weight']) || !empty($product['dimensions']))
                            <button type="button" class="tab-btn" onclick="setSpecTab('specs')">Specifications</button>
                        @endif
                        <button type="button" class="tab-btn" onclick="setSpecTab('shipping')">Shipping</button>
                    </div>
                    <div id="spec-description" class="spec-content">
                        <p class="text-xs text-slate-500 leading-relaxed">{!! nl2br(e($product['description'])) !!}</p>
                    </div>
                    @if(!empty($product['sku']) || !empty($product['manufacturer']) || !empty($product['weight']) || !empty($product['dimensions']))
                        <div id="spec-specs" class="spec-content" style="display:none">
                            <table class="w-full text-xs text-left text-slate-500">
                                <tbody>
                                    @if(!empty($product['sku']))
                                        <tr class="border-b border-slate-100/50">
                                            <td class="py-2 font-bold text-slate-400 w-1/3">SKU</td>
                                            <td class="py-2 font-mono text-slate-700">{{ $product['sku'] }}</td>
                                        </tr>
                                    @endif
                                    @if(!empty($product['manufacturer']))
                                        <tr class="border-b border-slate-100/50">
                                            <td class="py-2 font-bold text-slate-400 w-1/3">Manufacturer</td>
                                            <td class="py-2 text-slate-700">{{ $product['manufacturer'] }}</td>
                                        </tr>
                                    @endif
                                    @if(!empty($product['weight']))
                                        <tr class="border-b border-slate-100/50">
                                            <td class="py-2 font-bold text-slate-400 w-1/3">Weight</td>
                                            <td class="py-2 text-slate-700">{{ $product['weight'] }}</td>
                                        </tr>
                                    @endif
                                    @if(!empty($product['dimensions']))
                                        <tr class="border-b border-slate-100/50">
                                            <td class="py-2 font-bold text-slate-400 w-1/3">Dimensions</td>
                                            <td class="py-2 text-slate-700">{{ $product['dimensions'] }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @endif
                    <div id="spec-shipping" class="spec-content" style="display:none">
                        <p class="text-xs text-slate-500 leading-relaxed">Standard shipping takes between 3 to 7 business days depending on location. Tracking information is sent automatically via email once shipped.</p>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <!-- Hidden form for Wishlist -->
    <form id="wlForm" action="{{ route('store.wishlist.toggle') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product['id'] }}"/>
    </form>

    <!-- RELATED PRODUCTS -->
    <div class="section" style="margin-top:40px">
        <div class="section-header">
            <h2 class="section-title">Related Capsule</h2>
        </div>
        <div class="grid-4">
            @foreach($related as $rel)
                <div class="product-card" onclick="window.location.href='{{ route('store.product', $rel['id']) }}'">
                    <div class="product-card-img">
                        <img src="{{ $rel['img'] }}" alt="{{ $rel['name'] }}"/>
                    </div>
                    <div class="product-card-body">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">{{ $rel['cat'] }}</p>
                        <h3 class="font-display font-bold text-sm mt-1 text-slate-800 line-clamp-1">{{ $rel['name'] }}</h3>
                        <div class="flex items-center gap-1.5 mt-2">
                            <span class="font-bold text-sm text-slate-900">₹{{ number_format($rel['price'], 2) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function changeImage(src, el) {
        document.getElementById('mainProductImg').src = src;
        document.querySelectorAll('.pd-thumb').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
    }

    function selectSize(val, el) {
        document.getElementById('sizeInput').value = val;
        document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
        el.classList.add('active');
    }

    function selectColor(val, el) {
        document.getElementById('colorInput').value = val;
        document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('active'));
        el.classList.add('active');
    }

    function adjQty(dir) {
        const inp = document.getElementById('qtyInput');
        let val = parseInt(inp.value) + dir;
        if (val < 1) val = 1;
        inp.value = val;
    }

    function setSpecTab(name) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.spec-content').forEach(c => c.style.display = 'none');
        
        event.target.classList.add('active');
        document.getElementById(`spec-${name}`).style.display = 'block';
    }
</script>
@endsection
