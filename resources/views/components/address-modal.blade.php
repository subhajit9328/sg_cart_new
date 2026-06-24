@props([
    'id' => 'addressModal',
    'formId' => 'addressForm',
    'onSubmit' => '',
    'onClose' => 'closeAddressModal()',
    'submitBtnId' => 'saveAddressSubmitBtn',
    'action' => '',
    'method' => 'POST'
])

<div id="{{ $id }}" class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 transition-all duration-300 opacity-0 pointer-events-none">
    <div class="bg-white border border-[#e8e4df] rounded-2xl w-full max-w-[550px] overflow-hidden transform scale-95 transition-all duration-300 shadow-2xl" id="{{ $id }}Content">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-[#e8e4df] flex items-center justify-between bg-[#f8f7f5]">
            <h3 class="font-display font-bold text-base text-slate-900 flex items-center gap-2" id="{{ $id }}Title">
                <i class="fa-solid fa-map-location-dot text-accent"></i> Add New Address
            </h3>
            <button type="button" onclick="{{ $onClose }}" class="text-slate-400 hover:text-slate-700 border-none bg-transparent cursor-pointer text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Form Body -->
        <form id="{{ $formId }}" @if($action) action="{{ $action }}" @endif @if($onSubmit) onsubmit="{{ $onSubmit }}" @endif method="{{ $method }}" class="p-6 flex flex-col gap-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="font-sans text-[11px] font-bold text-slate-500 uppercase tracking-wider">First Name <span class="text-rose-600">*</span></label>
                    <input name="first_name" required class="w-full px-3.5 py-2.5 border border-[#e8e4df] rounded-lg text-sm text-slate-900 bg-white outline-none focus:border-slate-900 focus:ring-3 focus:ring-slate-900/5" placeholder="John"/>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-sans text-[11px] font-bold text-slate-500 uppercase tracking-wider">Last Name <span class="text-rose-600">*</span></label>
                    <input name="last_name" required class="w-full px-3.5 py-2.5 border border-[#e8e4df] rounded-lg text-sm text-slate-900 bg-white outline-none focus:border-slate-900 focus:ring-3 focus:ring-slate-900/5" placeholder="Doe"/>
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <label class="font-sans text-[11px] font-bold text-slate-500 uppercase tracking-wider">Street Address <span class="text-rose-600">*</span></label>
                <input name="address" required class="w-full px-3.5 py-2.5 border border-[#e8e4df] rounded-lg text-sm text-slate-900 bg-white outline-none focus:border-slate-900 focus:ring-3 focus:ring-slate-900/5" placeholder="123 Main St, Apt 4B"/>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="font-sans text-[11px] font-bold text-slate-500 uppercase tracking-wider">City <span class="text-rose-600">*</span></label>
                    <input name="city" required class="w-full px-3.5 py-2.5 border border-[#e8e4df] rounded-lg text-sm text-slate-900 bg-white outline-none focus:border-slate-900 focus:ring-3 focus:ring-slate-900/5" placeholder="New York"/>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-sans text-[11px] font-bold text-slate-500 uppercase tracking-wider">State / Province / Region <span class="text-rose-600">*</span></label>
                    <input name="state" required class="w-full px-3.5 py-2.5 border border-[#e8e4df] rounded-lg text-sm text-slate-900 bg-white outline-none focus:border-slate-900 focus:ring-3 focus:ring-slate-900/5" placeholder="NY"/>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="font-sans text-[11px] font-bold text-slate-500 uppercase tracking-wider">Zip / Postal Code <span class="text-rose-600">*</span></label>
                    <input name="zip" required class="w-full px-3.5 py-2.5 border border-[#e8e4df] rounded-lg text-sm text-slate-900 bg-white outline-none focus:border-slate-900 focus:ring-3 focus:ring-slate-900/5" placeholder="10001"/>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-sans text-[11px] font-bold text-slate-500 uppercase tracking-wider">Country <span class="text-rose-600">*</span></label>
                    <input name="country" required class="w-full px-3.5 py-2.5 border border-[#e8e4df] rounded-lg text-sm text-slate-900 bg-white outline-none focus:border-slate-900 focus:ring-3 focus:ring-slate-900/5" placeholder="United States"/>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-[#e8e4df] mt-2">
                <button type="button" onclick="{{ $onClose }}" class="btn btn-outline btn-sm px-6">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm px-6" id="{{ $submitBtnId }}">Save Address</button>
            </div>
        </form>
    </div>
</div>
