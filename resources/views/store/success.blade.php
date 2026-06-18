@extends('layouts.store')

@section('title', 'Order Confirmed — sgcart')

@section('content')
<div class="section-inner">
    <div style="max-width: 480px; margin: 60px auto; background: #fff; border: 1px solid var(--border); border-radius: var(--radius); padding: 40px; text-align: center; box-shadow: 0 4px 30px rgba(0,0,0,.02);">
        
        <!-- Animated Checked Icon -->
        <div style="width: 72px; height: 72px; border-radius: 50%; background: #f0fdf4; color: #22c55e; margin: 0 auto 24px; display: flex; align-items: center; justify-content: center; font-size: 32px; border: 1px solid #dcfce7;">
            <i class="fa-solid fa-circle-check"></i>
        </div>

        <h1 class="font-display font-extrabold text-2xl mb-2 text-slate-900">Thank You For Your Order!</h1>
        <p class="text-slate-400 text-sm mb-6">Your order has been placed successfully. A receipt and shipment tracker details will be sent to your email shortly.</p>
        
        <div style="background: var(--silk); border-radius: var(--radius-sm); padding: 16px; margin-bottom: 28px; border: 1px solid var(--border);">
            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Order ID</p>
            <p class="font-display font-bold text-slate-800 text-base mt-1" id="orderIdText">{{ $orderId }}</p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            <a href="{{ route('store.shop') }}" class="btn btn-primary w-full py-3">Continue Shopping</a>
            <a href="{{ route('store.account') }}" class="btn btn-outline w-full py-3">View Order History</a>
        </div>

    </div>
</div>
@endsection
