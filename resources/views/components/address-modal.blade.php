@props([
    'id' => 'addressModal',
    'formId' => 'addressForm',
    'onSubmit' => '',
    'onClose' => 'closeAddressModal()',
    'submitBtnId' => 'saveAddressSubmitBtn',
    'action' => '',
    'method' => 'POST'
])

<style>
    .error-text {
        color: #dc2626 !important;
        font-size: 0.75rem !important;
        margin-top: 0.25rem !important;
        display: block !important;
        font-weight: 500 !important;
    }
</style>

<div id="{{ $id }}" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 transition-all duration-300 opacity-0 pointer-events-none">
    <div class="bg-white dark:bg-[#151411] border border-slate-200 dark:border-[#2e2c28] rounded-2xl w-full max-w-[650px] max-h-[90vh] flex flex-col overflow-hidden transform scale-95 transition-all duration-300 shadow-2xl animate-fade-in" id="{{ $id }}Content">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-[#1c1a17]/50 flex-shrink-0">
            <h3 class="font-display font-extrabold text-lg text-slate-950 dark:text-slate-100 flex items-center gap-2" id="{{ $id }}Title">
                <i class="fa-solid fa-map-location-dot text-accent"></i> Add New Address
            </h3>
            <button type="button" onclick="{{ $onClose }}" class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#1c1a17] border-none bg-transparent cursor-pointer text-base transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Form -->
        <form id="{{ $formId }}" @if($action) action="{{ $action }}" @endif @if($onSubmit) onsubmit="{{ $onSubmit }}" @endif method="{{ $method }}" class="flex-1 flex flex-col min-h-0 overflow-hidden">
            @csrf
            
            <!-- Scrollable Form Body -->
            <div class="flex-1 overflow-y-auto p-6 flex flex-col gap-5">
                
                <!-- Address Type (Office, Work, Other) -->
                <div class="flex flex-col gap-1.5">
                    <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Address Type <span class="text-rose-600">*</span></label>
                    <div class="grid grid-cols-3 gap-2 p-1.5 bg-slate-100 dark:bg-[#1c1a17] rounded-xl">
                        <label class="cursor-pointer">
                            <input type="radio" name="address_type" value="office" class="sr-only peer" checked>
                            <div class="text-center py-2 px-3 text-xs font-bold text-slate-600 dark:text-slate-400 rounded-lg transition-all peer-checked:bg-white peer-checked:dark:bg-[#2b2925] peer-checked:text-slate-950 peer-checked:dark:text-white peer-checked:shadow-sm flex items-center justify-center gap-2 select-none">
                                <i class="fa-solid fa-briefcase text-slate-400 dark:text-slate-500 peer-checked:text-accent"></i> Office
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="address_type" value="work" class="sr-only peer">
                            <div class="text-center py-2 px-3 text-xs font-bold text-slate-600 dark:text-slate-400 rounded-lg transition-all peer-checked:bg-white peer-checked:dark:bg-[#2b2925] peer-checked:text-slate-950 peer-checked:dark:text-white peer-checked:shadow-sm flex items-center justify-center gap-2 select-none">
                                <i class="fa-solid fa-laptop-code text-slate-400 dark:text-slate-500 peer-checked:text-accent"></i> Work
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="address_type" value="other" class="sr-only peer">
                            <div class="text-center py-2 px-3 text-xs font-bold text-slate-600 dark:text-slate-400 rounded-lg transition-all peer-checked:bg-white peer-checked:dark:bg-[#2b2925] peer-checked:text-slate-950 peer-checked:dark:text-white peer-checked:shadow-sm flex items-center justify-center gap-2 select-none">
                                <i class="fa-solid fa-location-dot text-slate-400 dark:text-slate-500 peer-checked:text-accent"></i> Other
                            </div>
                        </label>
                    </div>
                    @error('address_type') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Shipping Section Header -->
                <div class="flex items-center gap-2.5 pb-1 border-b border-slate-100 dark:border-slate-800 mt-1">
                    <span class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-truck text-slate-400 dark:text-slate-500 text-xs"></i> Shipping Address
                    </span>
                </div>

                <!-- Shipping Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">First Name <span class="text-rose-600">*</span></label>
                        <input name="first_name" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-950 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="John" value="{{ old('first_name') }}"/>
                        @error('first_name') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Last Name <span class="text-rose-600">*</span></label>
                        <input name="last_name" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="Doe" value="{{ old('last_name') }}"/>
                        @error('last_name') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Phone Number <span class="text-rose-600">*</span></label>
                        <input name="phone" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="+1 (555) 000-0000" value="{{ old('phone') }}"/>
                        @error('phone') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Alternate Phone Number</label>
                        <input name="alternate_phone" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="+1 (555) 000-0000" value="{{ old('alternate_phone') }}"/>
                        @error('alternate_phone') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[1fr_200px] gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Street Address <span class="text-rose-600">*</span></label>
                        <input name="address" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="123 Main St, Apt 4B" value="{{ old('address') }}"/>
                        @error('address') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Landmark</label>
                        <input name="landmark" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="Near City Park" value="{{ old('landmark') }}"/>
                        @error('landmark') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">City <span class="text-rose-600">*</span></label>
                        <input name="city" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="New York" value="{{ old('city') }}"/>
                        @error('city') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">State / Province / Region <span class="text-rose-600">*</span></label>
                        <input name="state" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="NY" value="{{ old('state') }}"/>
                        @error('state') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Zip / Postal Code <span class="text-rose-600">*</span></label>
                        <input name="zip" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="10001" value="{{ old('zip') }}"/>
                        @error('zip') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Country <span class="text-rose-600">*</span></label>
                        <input name="country" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="United States" value="{{ old('country') }}"/>
                        @error('country') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Shipping and Billing Same Checkbox -->
                <div class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-[#1c1a17] border border-slate-100 dark:border-[#2e2c28] rounded-xl my-2 cursor-pointer hover:bg-slate-100/50 dark:hover:bg-[#1c1a17]/80 transition-colors select-none" id="{{ $id }}SameAsShippingContainer">
                    <input type="checkbox" id="{{ $id }}SameAsShipping" name="shipping_and_billing_same" value="1" checked 
                           class="w-4.5 h-4.5 text-slate-955 dark:text-accent border-slate-300 dark:border-slate-700 rounded focus:ring-slate-955 focus:dark:ring-accent focus:ring-offset-0 focus:ring-2 cursor-pointer">
                    <label for="{{ $id }}SameAsShipping" class="text-xs font-semibold text-slate-800 dark:text-slate-200 cursor-pointer select-none">
                        Billing Address is the same as Shipping Address
                    </label>
                </div>

                <!-- Billing Section -->
                <div id="{{ $id }}BillingSection" class="hidden flex-col gap-5 transition-all duration-300">
                    <!-- Billing Section Header -->
                    <div class="flex items-center gap-2.5 pb-1 border-b border-slate-100 dark:border-slate-800 mt-2">
                        <span class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-receipt text-slate-400 dark:text-slate-500 text-xs"></i> Billing Address
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Billing First Name <span class="text-rose-600">*</span></label>
                            <input name="billing_first_name" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="John" value="{{ old('billing_first_name') }}"/>
                            @error('billing_first_name') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Billing Last Name <span class="text-rose-600">*</span></label>
                            <input name="billing_last_name" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="Doe" value="{{ old('billing_last_name') }}"/>
                            @error('billing_last_name') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Billing Phone <span class="text-rose-600">*</span></label>
                            <input name="billing_phone" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="+1 (555) 000-0000" value="{{ old('billing_phone') }}"/>
                            @error('billing_phone') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Billing Street Address <span class="text-rose-600">*</span></label>
                        <input name="billing_address" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="123 Main St, Apt 4B" value="{{ old('billing_address') }}"/>
                        @error('billing_address') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Billing City <span class="text-rose-600">*</span></label>
                            <input name="billing_city" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="New York" value="{{ old('billing_city') }}"/>
                            @error('billing_city') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Billing State / Province / Region <span class="text-rose-600">*</span></label>
                            <input name="billing_state" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="NY" value="{{ old('billing_state') }}"/>
                            @error('billing_state') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Billing Zip / Postal Code <span class="text-rose-600">*</span></label>
                            <input name="billing_zip" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="10001" value="{{ old('billing_zip') }}"/>
                            @error('billing_zip') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Billing Country <span class="text-rose-600">*</span></label>
                            <input name="billing_country" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none focus:border-slate-955 focus:dark:focus:border-accent focus:ring-4 focus:ring-slate-950/5 focus:dark:ring-accent/10 transition-all" placeholder="United States" value="{{ old('billing_country') }}"/>
                            @error('billing_country') <span class="error-text text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions Footer (Outside the Scrollable Body, Fixed at bottom) -->
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3 bg-slate-50/50 dark:bg-[#1c1a17]/50 flex-shrink-0">
                <button type="button" onclick="{{ $onClose }}" class="btn btn-outline btn-sm px-6 py-2.5 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-[#1c1a17] text-slate-700 dark:text-slate-300 font-bold transition-all text-xs">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm px-6 py-2.5 rounded-lg bg-[#0a0a0a] dark:bg-accent hover:bg-slate-900 dark:hover:bg-accent/80 text-white dark:text-slate-950 font-bold transition-all text-xs" id="{{ $submitBtnId }}">Save Address</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    // Unique ID closure scope for each modal instantiation
    document.addEventListener('DOMContentLoaded', function() {
        const id = "{{ $id }}";
        const formId = "{{ $formId }}";
        
        const container = document.getElementById(id + 'SameAsShippingContainer');
        const checkbox = document.getElementById(id + 'SameAsShipping');
        const billingSection = document.getElementById(id + 'BillingSection');
        const form = document.getElementById(formId);
        
        if (!checkbox || !billingSection || !form) return;
        
        const billingInputs = billingSection.querySelectorAll('input');

        function toggleBilling() {
            if (checkbox.checked) {
                billingSection.classList.remove('flex');
                billingSection.classList.add('hidden');
                billingInputs.forEach(input => {
                    clearFieldError(input);
                });
            } else {
                billingSection.classList.remove('hidden');
                billingSection.classList.add('flex');
            }
        }

        checkbox.addEventListener('change', toggleBilling);
        
        // Prevent double trigger on container click
        if (container) {
            container.addEventListener('click', function(e) {
                if (e.target !== checkbox && !checkbox.contains(e.target)) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        }

        toggleBilling(); // Initialize

        // --- Inline Validation Engine ---
        function validateField(input) {
            const name = input.getAttribute('name');
            if (!name) return true;
            
            const value = input.value.trim();
            let errorMsg = '';

            const isBillingInput = name.startsWith('billing_');
            if (isBillingInput && checkbox.checked) {
                clearFieldError(input);
                return true;
            }

            const requiredFields = [
                'first_name', 'last_name', 'phone', 'address', 'city', 'state', 'zip', 'country',
                'billing_first_name', 'billing_last_name', 'billing_phone', 'billing_address', 'billing_city', 'billing_state', 'billing_zip', 'billing_country'
            ];
            const isRequired = requiredFields.includes(name);

            if (isRequired && !value) {
                errorMsg = 'This field is required.';
            } else if (value) {
                if (name === 'first_name' || name === 'last_name' || name === 'billing_first_name' || name === 'billing_last_name') {
                    if (value.length < 2) {
                        errorMsg = 'Must be at least 2 characters.';
                    } else if (value.length > 100) {
                        errorMsg = 'Cannot exceed 100 characters.';
                    }
                } else if (name === 'phone' || name === 'alternate_phone' || name === 'billing_phone') {
                    if (name === 'alternate_phone' && !value) {
                        clearFieldError(input);
                        return true;
                    }
                    const phoneRegex = /^[+0-9\s()-]{7,15}$/;
                    if (!phoneRegex.test(value)) {
                        errorMsg = 'Please enter a valid phone number (7-15 digits/symbols).';
                    }
                } else if (name === 'zip' || name === 'billing_zip') {
                    const zipRegex = /^[a-zA-Z0-9\s-]{3,10}$/;
                    if (!zipRegex.test(value)) {
                        errorMsg = 'Please enter a valid postal code (3-10 characters).';
                    }
                } else if (name === 'address' || name === 'billing_address') {
                    if (value.length > 255) {
                        errorMsg = 'Cannot exceed 255 characters.';
                    }
                } else if (name === 'city' || name === 'state' || name === 'country' || name === 'billing_city' || name === 'billing_state' || name === 'billing_country') {
                    if (value.length > 100) {
                        errorMsg = 'Cannot exceed 100 characters.';
                    }
                }
            }

            if (errorMsg) {
                showFieldError(input, errorMsg);
                return false;
            } else {
                clearFieldError(input);
                return true;
            }
        }

        function showFieldError(input, message) {
            clearFieldError(input);
            input.classList.add('border-rose-500');
            input.classList.remove('focus:border-slate-950');
            
            const errorEl = document.createElement('span');
            errorEl.className = 'error-text text-rose-500 text-xs mt-1 block';
            errorEl.innerText = message;
            
            // For address type / radio buttons, append to parent flex-col
            if (input.getAttribute('name') === 'address_type') {
                input.closest('.flex-col').appendChild(errorEl);
            } else {
                input.parentNode.appendChild(errorEl);
            }
        }

        function clearFieldError(input) {
            input.classList.remove('border-rose-500');
            input.classList.add('focus:border-slate-950');
            const container = input.parentNode;
            if (container) {
                const errors = container.querySelectorAll('.error-text');
                errors.forEach(err => err.remove());
            }
        }

        // Attach listeners for immediate feedback
        form.querySelectorAll('input').forEach(input => {
            input.addEventListener('blur', function() {
                validateField(input);
            });
            input.addEventListener('input', function() {
                // If already marked invalid, check as they type
                if (input.classList.contains('border-rose-500')) {
                    validateField(input);
                }
            });
        });

        // Register global validation function associated with this form ID
        if (!window.validateAddressForm) {
            window.validateAddressForms = {};
        }
        window.validateAddressForms[formId] = function() {
            let isValid = true;
            form.querySelectorAll('input').forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });
            return isValid;
        };
        // Backward compatibility handler
        window.validateAddressForm = function(fId) {
            if (window.validateAddressForms && window.validateAddressForms[fId]) {
                return window.validateAddressForms[fId]();
            }
            return true;
        };

        // Form Submit interception for standard (non-AJAX) submissions
        form.addEventListener('submit', function(e) {
            let isValid = true;
            form.querySelectorAll('input').forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
                
                const firstInvalid = form.querySelector('.border-rose-500');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
                
                if (window.showToast) {
                    showToast('Please correct the validation errors.', 'error');
                }
            }
        });
    });
})();
</script>
