@props([
    'value' => '',
    'name' => 'email_or_phone',
    'id' => 'email_or_phone',
    'required' => false,
    'placeholder' => 'email@example.com or phone number'
])

@once
    <style>
        /* Styling for the custom Select2 dropdown selection box */
        .email-or-phone-container .select2-container--default .select2-selection--single {
            border: 1px solid #cbd5e1 !important;
            border-radius: 10px !important;
            height: 46px !important;
            background-color: #fff !important;
            display: flex !important;
            align-items: center !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
            box-shadow: none !important;
        }
        .email-or-phone-container .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #0f172a !important;
            font-family: inherit !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            padding-left: 14px !important;
            padding-right: 24px !important;
        }
        .email-or-phone-container .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
            right: 8px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .email-or-phone-container .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #888 transparent transparent transparent !important;
        }
        .email-or-phone-container .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #888 transparent !important;
        }
        .email-or-phone-container .select2-container--default.select2-container--focus .select2-selection--single,
        .email-or-phone-container .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #0f172a !important;
            outline: none !important;
        }

        /* Red border on error */
        .email-or-phone-container .select2-container--default .select2-selection--single.border-rose-500 {
            border-color: #f43f5e !important;
        }

        /* Dark Mode styling */
        .dark .email-or-phone-container .select2-container--default .select2-selection--single {
            border-color: #2b2a27 !important;
            background-color: #1c1a17 !important;
        }
        .dark .email-or-phone-container .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f3f4f6 !important;
        }
        .dark .email-or-phone-container .select2-container--default.select2-container--focus .select2-selection--single,
        .dark .email-or-phone-container .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--color-accent, #c8a97e) !important;
        }

        /* Dropdown container */
        .select2-dropdown {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            background-color: #fff !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            z-index: 99999 !important;
        }
        .dark .select2-dropdown {
            border-color: #2b2a27 !important;
            background-color: #1c1a17 !important;
        }
        .select2-results__option {
            font-family: inherit !important;
            font-size: 13px !important;
            padding: 8px 12px !important;
            color: #4a4a4a !important;
        }
        .dark .select2-results__option {
            color: #d1d5db !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--color-accent, #c8a97e) !important;
            color: #fff !important;
        }
        .select2-container--default .select2-results__option[aria-selected="true"] {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
        }
        .dark .select2-container--default .select2-results__option[aria-selected="true"] {
            background-color: #2b2a27 !important;
            color: #f3f4f6 !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 6px 10px !important;
            outline: none !important;
            font-family: inherit !important;
        }
        .dark .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #171614 !important;
            color: #fff !important;
            border-color: #2b2a27 !important;
        }
    </style>
@endonce

<div class="flex flex-col gap-1 email-or-phone-container">
    <div class="flex gap-2 items-center w-full relative">
        <!-- Country Code Wrapper (hidden by default) -->
        <div id="country_code_wrapper" class="flex items-center gap-1 hidden select-none relative">
            <select id="country_code_input" style="width: 80px;">
                <!-- Dynamically populated -->
            </select>
        </div>

        <!-- Main input -->
        <input type="text"
               id="main_input"
               class="inp flex-1"
               placeholder="{{ $placeholder }}"
               {{ $required ? 'required' : '' }}
        />
    </div>
    <p class="error-email-phone text-rose-500 text-xs mt-1 hidden"></p>
    @error('email_or_phone') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror

    <!-- Hidden input that holds the actual value submitted to Laravel -->
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}">
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ccWrapper = document.getElementById('country_code_wrapper');
    const ccInput = document.getElementById('country_code_input');
    const mainInput = document.getElementById('main_input');
    const hiddenVal = document.getElementById('{{ $id }}');
    const errorMsg = document.querySelector('.error-email-phone');

    let debounceTimer;

    const countries = {
        "AF": { "name": "Afghanistan", "code": "93" },
        "AL": { "name": "Albania", "code": "355" },
        "DZ": { "name": "Algeria", "code": "213" },
        "AD": { "name": "Andorra", "code": "376" },
        "AO": { "name": "Angola", "code": "244" },
        "AR": { "name": "Argentina", "code": "54" },
        "AM": { "name": "Armenia", "code": "374" },
        "AU": { "name": "Australia", "code": "61" },
        "AT": { "name": "Austria", "code": "43" },
        "AZ": { "name": "Azerbaijan", "code": "994" },
        "BH": { "name": "Bahrain", "code": "973" },
        "BD": { "name": "Bangladesh", "code": "880" },
        "BY": { "name": "Belarus", "code": "375" },
        "BE": { "name": "Belgium", "code": "32" },
        "BO": { "name": "Bolivia", "code": "591" },
        "BA": { "name": "Bosnia and Herzegovina", "code": "387" },
        "BR": { "name": "Brazil", "code": "55" },
        "BG": { "name": "Bulgaria", "code": "359" },
        "KH": { "name": "Cambodia", "code": "855" },
        "CM": { "name": "Cameroon", "code": "237" },
        "CA": { "name": "Canada", "code": "1" },
        "CL": { "name": "Chile", "code": "56" },
        "CN": { "name": "China", "code": "86" },
        "CO": { "name": "Colombia", "code": "57" },
        "CR": { "name": "Costa Rica", "code": "506" },
        "HR": { "name": "Croatia", "code": "385" },
        "CU": { "name": "Cuba", "code": "53" },
        "CY": { "name": "Cyprus", "code": "357" },
        "CZ": { "name": "Czech Republic", "code": "420" },
        "DK": { "name": "Denmark", "code": "45" },
        "DO": { "name": "Dominican Republic", "code": "1" },
        "EC": { "name": "Ecuador", "code": "593" },
        "EG": { "name": "Egypt", "code": "20" },
        "SV": { "name": "El Salvador", "code": "503" },
        "EE": { "name": "Estonia", "code": "372" },
        "ET": { "name": "Ethiopia", "code": "251" },
        "FI": { "name": "Finland", "code": "358" },
        "FR": { "name": "France", "code": "33" },
        "GE": { "name": "Georgia", "code": "995" },
        "DE": { "name": "Germany", "code": "49" },
        "GH": { "name": "Ghana", "code": "233" },
        "GR": { "name": "Greece", "code": "30" },
        "GT": { "name": "Guatemala", "code": "502" },
        "HN": { "name": "Honduras", "code": "504" },
        "HK": { "name": "Hong Kong", "code": "852" },
        "HU": { "name": "Hungary", "code": "36" },
        "IS": { "name": "Iceland", "code": "354" },
        "IN": { "name": "India", "code": "91" },
        "ID": { "name": "Indonesia", "code": "62" },
        "IR": { "name": "Iran", "code": "98" },
        "IQ": { "name": "Iraq", "code": "964" },
        "IE": { "name": "Ireland", "code": "353" },
        "IL": { "name": "Israel", "code": "972" },
        "IT": { "name": "Italy", "code": "39" },
        "JM": { "name": "Jamaica", "code": "1" },
        "JP": { "name": "Japan", "code": "81" },
        "JO": { "name": "Jordan", "code": "962" },
        "KZ": { "name": "Kazakhstan", "code": "7" },
        "KE": { "name": "Kenya", "code": "254" },
        "KW": { "name": "Kuwait", "code": "965" },
        "LV": { "name": "Latvia", "code": "371" },
        "LB": { "name": "Lebanon", "code": "961" },
        "LY": { "name": "Libya", "code": "218" },
        "LT": { "name": "Lithuania", "code": "370" },
        "LU": { "name": "Luxembourg", "code": "352" },
        "MY": { "name": "Malaysia", "code": "60" },
        "MT": { "name": "Malta", "code": "356" },
        "MX": { "name": "Mexico", "code": "52" },
        "MC": { "name": "Monaco", "code": "377" },
        "MN": { "name": "Mongolia", "code": "976" },
        "ME": { "name": "Montenegro", "code": "382" },
        "MA": { "name": "Morocco", "code": "212" },
        "NP": { "name": "Nepal", "code": "977" },
        "NL": { "name": "Netherlands", "code": "31" },
        "NZ": { "name": "New Zealand", "code": "64" },
        "NI": { "name": "Nicaragua", "code": "505" },
        "NG": { "name": "Nigeria", "code": "234" },
        "KP": { "name": "North Korea", "code": "850" },
        "MK": { "name": "North Macedonia", "code": "389" },
        "NO": { "name": "Norway", "code": "47" },
        "OM": { "name": "Oman", "code": "968" },
        "PK": { "name": "Pakistan", "code": "92" },
        "PA": { "name": "Panama", "code": "507" },
        "PY": { "name": "Paraguay", "code": "595" },
        "PE": { "name": "Peru", "code": "51" },
        "PH": { "name": "Philippines", "code": "63" },
        "PL": { "name": "Poland", "code": "48" },
        "PT": { "name": "Portugal", "code": "351" },
        "QA": { "name": "Qatar", "code": "974" },
        "RO": { "name": "Romania", "code": "40" },
        "RU": { "name": "Russia", "code": "7" },
        "SA": { "name": "Saudi Arabia", "code": "966" },
        "RS": { "name": "Serbia", "code": "381" },
        "SG": { "name": "Singapore", "code": "65" },
        "SK": { "name": "Slovakia", "code": "421" },
        "SI": { "name": "Slovenia", "code": "386" },
        "ZA": { "name": "South Africa", "code": "27" },
        "KR": { "name": "South Korea", "code": "82" },
        "ES": { "name": "Spain", "code": "34" },
        "LK": { "name": "Sri Lanka", "code": "94" },
        "SE": { "name": "Sweden", "code": "46" },
        "CH": { "name": "Switzerland", "code": "41" },
        "SY": { "name": "Syria", "code": "963" },
        "TW": { "name": "Taiwan", "code": "886" },
        "TH": { "name": "Thailand", "code": "66" },
        "TN": { "name": "Tunisia", "code": "216" },
        "TR": { "name": "Turkey", "code": "90" },
        "UA": { "name": "Ukraine", "code": "380" },
        "AE": { "name": "United Arab Emirates", "code": "971" },
        "GB": { "name": "United Kingdom", "code": "44" },
        "US": { "name": "United States", "code": "1" },
        "UY": { "name": "Uruguay", "code": "598" },
        "UZ": { "name": "Uzbekistan", "code": "998" },
        "VE": { "name": "Venezuela", "code": "58" },
        "VN": { "name": "Vietnam", "code": "84" },
        "YE": { "name": "Yemen", "code": "967" },
        "ZW": { "name": "Zimbabwe", "code": "263" }
    };

    // Populate dropdown sorted alphabetically by key (short code)
    const sortedCountryCodes = Object.keys(countries).map(key => {
        return {
            key: key,
            name: countries[key].name,
            code: countries[key].code
        };
    }).sort((a, b) => a.key.localeCompare(b.key));

    sortedCountryCodes.forEach(item => {
        const option = document.createElement('option');
        option.value = item.code;
        option.setAttribute('data-code', item.code);
        option.setAttribute('data-name', item.name);
        option.textContent = `+${item.code} ${item.name}`;
        option.setAttribute('title', item.name);
        ccInput.appendChild(option);
    });

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

    // Helper to toggle error style for Country Code
    function toggleCcError(isError) {
        if (isError) {
            ccInput.classList.add('border-rose-500');
            $(ccInput).next('.select2').find('.select2-selection').addClass('border-rose-500');
        } else {
            ccInput.classList.remove('border-rose-500');
            $(ccInput).next('.select2').find('.select2-selection').removeClass('border-rose-500');
        }
    }

    // Helper to select / add option to dropdown dynamically
    function selectCountryCode(code) {
        let exists = false;
        for (let i = 0; i < ccInput.options.length; i++) {
            if (ccInput.options[i].value === code) {
                exists = true;
                break;
            }
        }
        if (!exists) {
            const option = document.createElement('option');
            option.value = code;
            option.setAttribute('data-code', code);
            option.setAttribute('data-name', 'Custom');
            option.textContent = `+${code} Custom`;
            ccInput.appendChild(option);
        }
        ccInput.value = code;
        $(ccInput).val(code).trigger('change');
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
            const cleanCc = ccVal.replace(/\D/g, '');
            const cleanMain = mainVal.replace(/\D/g, '');
            hiddenVal.value = '+' + cleanCc + cleanMain;
        }

        // Trigger validation events
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
            updateHiddenValue();
        } else {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                const currentVal = mainInput.value.trim();
                if (!isEmailLike(currentVal) && currentVal !== '') {
                    ccWrapper.classList.remove('hidden');
                    mainInput.setAttribute('maxlength', '12');

                    if (currentVal.startsWith('+')) {
                        const parsed = parsePhoneNumber(currentVal);
                        selectCountryCode(parsed.cc);
                        mainInput.value = parsed.num;
                    }
                }
                updateHiddenValue();
            }, 250);
        }
    }

    function validatePhoneLengths() {
        const mainVal = mainInput.value.trim();
        const ccVal = ccInput.value.trim();
        let errorMessages = '';
        let hasError = false;

        if (mainVal === '') {
            errorMsg.classList.add('hidden');
            errorMsg.textContent = '';
            mainInput.classList.remove('border-rose-500');
            toggleCcError(false);
            return true;
        }

        if (isEmailLike(mainVal)) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(mainVal)) {
                errorMsg.textContent = 'Please enter a valid email address.';
                errorMsg.classList.remove('hidden');
                mainInput.classList.add('border-rose-500');
                toggleCcError(false);
                return false;
            } else {
                errorMsg.classList.add('hidden');
                errorMsg.textContent = '';
                mainInput.classList.remove('border-rose-500');
                toggleCcError(false);
                return true;
            }
        }

        const cleanCc = ccVal.replace(/\D/g, '');
        const cleanMain = mainVal.replace(/\D/g, '');

        if (cleanCc.length < 1 || cleanCc.length > 3) {
            errorMessages += 'Country code must be between 1 and 3 digits. \n';
            toggleCcError(true);
            hasError = true;
        } else {
            toggleCcError(false);
        }

        if (cleanMain.length < 7 || cleanMain.length > 12) {
            errorMessages += 'Phone number must be between 7 and 12 digits. \n';
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

        errorMsg.classList.add('hidden');
        errorMsg.textContent = '';
        return true;
    }

    window.validateEmailPhoneComponent = function() {
        updateHiddenValue();
        const mainVal = mainInput.value.trim();
        if (mainVal === '') {
            errorMsg.textContent = 'Email or phone number is required.';
            errorMsg.classList.remove('hidden');
            mainInput.classList.add('border-rose-500');
            return false;
        }
        return validatePhoneLengths();
    };

    function init() {
        const initialVal = hiddenVal.value.trim();
        if (initialVal === '') {
            // Default to India (91)
            $(ccInput).val('91').trigger('change');
            return;
        }

        if (isEmailLike(initialVal)) {
            mainInput.value = initialVal;
            ccWrapper.classList.add('hidden');
            mainInput.removeAttribute('maxlength');
        } else {
            const parsed = parsePhoneNumber(initialVal);
            selectCountryCode(parsed.cc);
            mainInput.value = parsed.num;
            ccWrapper.classList.remove('hidden');
            mainInput.setAttribute('maxlength', '12');
        }
    }

    mainInput.addEventListener('input', checkInputType);
    mainInput.addEventListener('blur', function() {
        updateHiddenValue();
        validatePhoneLengths();
    });

    ccInput.addEventListener('change', function() {
        updateHiddenValue();
        validatePhoneLengths();
    });

    if (hiddenVal) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    const hiddenClasses = hiddenVal.className || '';
                    if (hiddenClasses.includes('border-rose-500')) {
                        mainInput.classList.add('border-rose-500');
                        toggleCcError(true);
                    } else {
                        mainInput.classList.remove('border-rose-500');
                        toggleCcError(false);
                    }
                }
            });
        });
        observer.observe(hiddenVal, { attributes: true });
    }

    // Initialize Select2 on country code dropdown
    $(ccInput).select2({
        templateSelection: function(state) {
            if (!state.id) return state.text;
            const element = state.element;
            if (element) {
                const code = $(element).attr('data-code') || state.id;
                return `+${code}`;
            }
            return `+${state.id}`;
        },
        templateResult: function(state) {
            return state.text;
        },
        dropdownAutoWidth: true,
        width: '80px',
        minimumResultsForSearch: 0
    });

    init();
});
</script>
