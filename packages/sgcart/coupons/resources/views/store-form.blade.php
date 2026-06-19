@php
    $appliedCoupon = session('coupon_code');
    $discountAmount = 0.00;
    if (app()->bound('coupon.calculator') && $appliedCoupon) {
        $discountAmount = app('coupon.calculator')->calculate($appliedCoupon, $subtotal);
    }
@endphp

<!-- Coupon Code Form -->
<form action="{{ route('store.cart.coupon') }}" method="POST" class="flex gap-2 mb-4">
    @csrf
    <input type="text" name="code" value="{{ $appliedCoupon }}" placeholder="Coupon code" required class="inp" {{ $appliedCoupon ? 'readonly disabled' : '' }} style="flex:1;padding:10px 14px;font-size:13px;height:38px"/>
    @if($appliedCoupon)
        <a href="{{ route('store.cart.coupon.remove') }}" class="btn btn-outline btn-sm text-rose-500 border-rose-200 hover:bg-rose-50 flex items-center justify-center" style="height:38px;padding-left:12px;padding-right:12px;text-decoration:none">Remove</a>
    @else
        <button type="submit" class="btn btn-outline btn-sm" style="height:38px">Apply</button>
    @endif
</form>

@if(!$appliedCoupon)
    <p class="text-[11px] text-slate-400 mt-2 mb-4"><i class="fa-solid fa-circle-info text-[10px] mr-0.5"></i> Try entering promo codes like <strong>SGCART20</strong> or custom coupon codes.</p>
@else
    <p class="text-[11px] text-emerald-600 mt-2 mb-4"><i class="fa-solid fa-circle-check text-[10px] mr-0.5"></i> Coupon <strong>{{ $appliedCoupon }}</strong> applied! (Saved ${{ number_format($discountAmount, 2) }})</p>
@endif
