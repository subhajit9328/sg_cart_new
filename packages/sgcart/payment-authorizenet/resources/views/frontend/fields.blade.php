<div class="mt-4 border-t border-slate-100 pt-4 flex flex-col gap-4">
    <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Cardholder Name <span class="text-rose-600">*</span></label>
        <input type="text" name="authorizenet_card_name" required value="{{ old('authorizenet_card_name') }}" class="w-full bg-white border border-[#e8e4df] rounded-lg px-3 py-2 text-sm outline-none focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-slate-800" placeholder="John Doe"/>
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Credit Card Number <span class="text-rose-600">*</span></label>
        <input type="text" name="authorizenet_card_num" required value="{{ old('authorizenet_card_num') }}" class="w-full bg-white border border-[#e8e4df] rounded-lg px-3 py-2 text-sm outline-none focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-slate-800" placeholder="4111 2222 3333 4444"/>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Expiry Date <span class="text-rose-600">*</span></label>
            <input type="text" name="authorizenet_card_expiry" required value="{{ old('authorizenet_card_expiry') }}" class="w-full bg-white border border-[#e8e4df] rounded-lg px-3 py-2 text-sm outline-none focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-slate-800" placeholder="MM/YY"/>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">CVV Code <span class="text-rose-600">*</span></label>
            <input type="password" name="authorizenet_card_cvv" required value="{{ old('authorizenet_card_cvv') }}" class="w-full bg-white border border-[#e8e4df] rounded-lg px-3 py-2 text-sm outline-none focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-slate-800" placeholder="123"/>
        </div>
    </div>
</div>
