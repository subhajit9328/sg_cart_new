@props([
    'value' => '',
    'name' => 'email_or_phone',
    'id' => 'email_or_phone',
    'required' => false,
    'placeholder' => 'email@example.com or phone number'
])

<div class="flex flex-col gap-1 email-or-phone-container">
    <div class="flex gap-2 items-center w-full relative">
        <div id="country_code_wrapper" class="flex items-center gap-1 hidden select-none relative">
            <span class="text-slate-400 dark:text-slate-500 font-bold text-base left-[8px] top-2.5 pl-1 absolute">+</span>
            <input type="text"
                   id="country_code_input"
                   class="inp text-center w-20!"
                   placeholder="91"
                   value=""
                   maxlength="3"
            />
        </div>

        <input type="text"
               id="main_input"
               class="inp flex-1"
               placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
        />
    </div>
    <p class="error-email-phone text-rose-500 text-xs mt-1 hidden"></p>
    @error('email_or_phone') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}">
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ccWrapper = document.getElementById('country_code_wrapper');
            const ccInput = document.getElementById('country_code_input');
            const mainInput = document.getElementById('main_input');
            const hiddenVal = document.getElementById('{{ $id }}');
            const errorMsg = document.querySelector('.error-email-phone');

            let debounceTimer;

            // Helper to determine if a string looks like an email
            function isEmailLike(val) {
                return val.includes('@') || /[a-zA-Z]/.test(val);
            }

            // Helper to parse phone number into country code and number
            function parsePhoneNumber(val) {
                if (!val.startsWith('+')) {
                    return { cc: '91', num: val };
                }
                const digits = val.substring(1);
                // Single digit country codes
                if (/^(1|7)/.test(digits)) {
                    return { cc: digits.substring(0, 1), num: digits.substring(1) };
                }
                // Two digit country codes
                if (/^(20|27|30|31|32|33|34|36|39|40|41|43|44|45|46|47|48|49|51|52|53|54|55|56|57|58|60|61|62|63|64|65|66|81|82|84|86|90|91|92|93|94|95|98)/.test(digits)) {
                    return { cc: digits.substring(0, 2), num: digits.substring(2) };
                }
                // Default to 3 digits country code
                return { cc: digits.substring(0, 3), num: digits.substring(3) };
            }

            // Helper to update the hidden input value
            function updateHiddenValue() {
                const mainVal = mainInput.value.trim();
                const ccVal = ccInput.value.trim();

                if (mainVal === '') {
                    hiddenVal.value = '';
                    return;
                }

                if (isEmailLike(mainVal)) {
                    hiddenVal.value = mainVal;
                } else {
                    // Strip any leading '+' from country code and main input digits
                    const cleanCc = ccVal.replace(/^\+/, '').replace(/\D/g, '');
                    const cleanMain = mainVal.replace(/^\+/, '').replace(/\D/g, '');
                    hiddenVal.value = '+' + cleanCc + cleanMain;
                }

                // Trigger validation events for vanilla JS
                hiddenVal.dispatchEvent(new Event('input', { bubbles: true }));
                hiddenVal.dispatchEvent(new Event('change', { bubbles: true }));
                hiddenVal.dispatchEvent(new Event('blur', { bubbles: true }));
            }

            function checkInputType() {
                const val = mainInput.value.trim();
                if (val === '') {
                    ccWrapper.classList.add('hidden');
                    mainInput.removeAttribute('maxlength');
                    updateHiddenValue();
                    return;
                }

                if (isEmailLike(val)) {
                    ccWrapper.classList.add('hidden');
                    mainInput.removeAttribute('maxlength');
                    mainInput.setAttribute('placeholder', 'email@example.com or phone number')
                    updateHiddenValue();
                } else {
                    mainInput.setAttribute('placeholder', 'phone number');
                    // Debounce showing country code by 250ms
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function() {
                        const currentVal = mainInput.value.trim();
                        if (!isEmailLike(currentVal) && currentVal !== '') {
                            ccWrapper.classList.remove('hidden');
                            mainInput.setAttribute('maxlength', '15'); // Fixed from 12 to 15

                            // If the user typed a '+' in main input, parse it
                            if (currentVal.startsWith('+')) {
                                const parsed = parsePhoneNumber(currentVal);
                                ccInput.value = parsed.cc;
                                mainInput.value = parsed.num;
                            }
                        }
                        updateHiddenValue();
                    }, 500);
                }
            }

            function validatePhoneLengths() {
                const mainVal = mainInput.value.trim();
                const ccVal = ccInput.value.trim();
                let errorMessages = '';
                let hasError = false;

                // Skip validation if empty
                if (mainVal === '') {
                    errorMsg.classList.add('hidden');
                    errorMsg.textContent = '';
                    mainInput.classList.remove('border-rose-500');
                    ccInput.classList.remove('border-rose-500');
                    return true;
                }

                if (isEmailLike(mainVal)) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(mainVal)) {
                        errorMsg.textContent = 'Please enter a valid email address.';
                        errorMsg.classList.remove('hidden');
                        mainInput.classList.add('border-rose-500');
                        ccInput.classList.remove('border-rose-500');
                        return false;
                    } else {
                        errorMsg.classList.add('hidden');
                        errorMsg.textContent = '';
                        mainInput.classList.remove('border-rose-500');
                        ccInput.classList.remove('border-rose-500');
                        return true;
                    }
                }

                const cleanCc = ccVal.replace(/\D/g, '');
                const cleanMain = mainVal.replace(/\D/g, '');

                // 1. Validate Country Code (Min 1, Max 3)
                if (cleanCc.length < 1 || cleanCc.length > 3) {
                    errorMsg.textContent = '';
                    errorMsg.classList.remove('hidden');
                    errorMessages += 'Country code must be between 1 and 3 digits. \n';
                    ccInput.classList.add('border-rose-500');
                    hasError = true;
                } else {
                    ccInput.classList.remove('border-rose-500');
                }

                // 2. Validate Phone Number (Min 7, Max 15)
                if (cleanMain.length < 7 || cleanMain.length > 15) {
                    errorMessages += 'Phone number must be between 7 and 15 digits. \n';
                    mainInput.classList.add('border-rose-500');
                    hasError = true;
                } else {
                    mainInput.classList.remove('border-rose-500');
                }

                if (hasError) {
                    errorMsg.textContent = errorMessages;
                    errorMsg.classList.remove('hidden');
                    return false;
                }

                // If everything passes
                errorMsg.classList.add('hidden');
                errorMsg.textContent = '';
                return true;
            }

            // Expose the validator function to the global scope
            window.validateEmailPhoneComponent = function() {
                // Ensure the hidden value is up to date first
                updateHiddenValue();

                // Check if the input is entirely empty first
                const mainVal = mainInput.value.trim();
                if (mainVal === '') {
                    errorMsg.textContent = 'Email or phone number is required.';
                    errorMsg.classList.remove('hidden');
                    mainInput.classList.add('border-rose-500');
                    return false;
                }

                // Run the length validations
                return validatePhoneLengths();
            };

            // Initialize state on page load based on initial value
            function init() {
                const initialVal = hiddenVal.value.trim();
                if (initialVal === '') {
                    return;
                }

                if (isEmailLike(initialVal)) {
                    mainInput.value = initialVal;
                    ccWrapper.classList.add('hidden');
                    mainInput.removeAttribute('maxlength');
                } else {
                    const parsed = parsePhoneNumber(initialVal);
                    ccInput.value = parsed.cc;
                    mainInput.value = parsed.num;
                    ccWrapper.classList.remove('hidden');
                    mainInput.setAttribute('maxlength', '15');
                    ccInput.setAttribute('maxlength', '3'); // Fixed: Apply to input, not wrapper
                }
            }

            mainInput.addEventListener('input', checkInputType);
            mainInput.addEventListener('blur', function() {
                updateHiddenValue();
                validatePhoneLengths();
            });

            // Country code input limits: only digits, max 3
            ccInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '');
                updateHiddenValue();
            });

            ccInput.addEventListener('blur', function() {
                updateHiddenValue();
                validatePhoneLengths();
            });

            // Sync validation classes from hidden input to main inputs
            if (hiddenVal) {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'class') {
                            const hiddenClasses = hiddenVal.className || '';
                            if (hiddenClasses.includes('border-rose-500')) {
                                mainInput.classList.add('border-rose-500');
                                ccInput.classList.add('border-rose-500');
                            } else {
                                mainInput.classList.remove('border-rose-500');
                                ccInput.classList.remove('border-rose-500');
                            }
                        }
                    });
                });
                observer.observe(hiddenVal, { attributes: true });
            }

            init();
        });
    </script>
@endpush
