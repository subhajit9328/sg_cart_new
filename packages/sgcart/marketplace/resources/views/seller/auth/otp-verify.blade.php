<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Seller Account — SGCart Marketplace</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind compiled by Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-display { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    </style>
</head>
<body class="bg-slate-900 flex items-center justify-center min-h-screen p-4 overflow-y-auto relative">
    
    <!-- Decorative Gradients -->
    <div class="absolute w-[40rem] h-[40rem] rounded-full bg-blue-600/10 blur-3xl -top-40 -left-40"></div>
    <div class="absolute w-[40rem] h-[40rem] rounded-full bg-indigo-600/10 blur-3xl -bottom-40 -right-40"></div>

    <div class="w-full max-w-md bg-slate-950/80 border border-slate-800 rounded-2xl p-8 shadow-2xl relative z-10 backdrop-blur-md">
        
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center gap-2.5">
                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-500 to-indigo-600 shadow-md shadow-blue-500/20">
                    <i class="fa-solid fa-cart-shopping text-white text-sm"></i>
                </div>
                <h1 class="font-display text-3xl font-bold tracking-tight text-white flex items-baseline leading-none">sg<span class="text-blue-400 font-extrabold">cart</span><span class="ml-2 text-[9px] uppercase font-bold tracking-wider text-blue-200 bg-blue-500/10 px-1.5 py-0.5 rounded-md border border-blue-500/20 self-end leading-none mb-0.5">Seller</span></h1>
            </div>
            <p class="text-slate-400 text-xs mt-4">Verify your contact details to continue</p>
            <p class="text-slate-400 text-xs mt-2">
                We've sent a 6-digit verification code to <strong class="text-white">{{ $identifier }}</strong>.
            </p>
            <a class="inline-block mt-3 text-blue-500 hover:underline font-semibold text-xs" href="{{ route('seller.register') }}">
                Wrong email or phone number?
            </a>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-sm flex gap-3 items-start">
                <i class="fa-solid fa-circle-check mt-0.5 text-emerald-500"></i>
                <span class="flex-1 font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Error Message -->
        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-400 text-sm flex gap-3 items-start">
                <i class="fa-solid fa-circle-exclamation mt-0.5 text-rose-500"></i>
                <span class="flex-1 font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if(!$isEmail && config('app.test_mode'))
        <div class="mb-6 p-4 bg-amber-950/20 border border-amber-500/30 rounded-xl text-amber-400 text-xs flex gap-3 items-start leading-relaxed">
            <i class="fa-solid fa-circle-info text-base mt-0.5 text-amber-500"></i>
            <div>
                <span class="font-bold block mb-0.5 text-white">Test Mode Active</span>
                For phone verification in test mode, you can enter any arbitrary 6-digit number to bypass verification.
            </div>
        </div>
        @endif

        <form action="{{ route('seller.otp.verify.submit') }}" method="POST" class="space-y-5" novalidate>
            @csrf
            
            <!-- OTP Input -->
            <div>
                <label for="otp" class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2 text-center">Verification Code (OTP)</label>
                <div class="relative flex items-center">
                    <input type="text" name="otp" id="otp" required maxlength="6" pattern="[0-9]{6}" autofocus
                        class="w-full bg-slate-900 border border-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl py-3.5 text-center tracking-[12px] font-mono text-2xl text-white placeholder:text-slate-700 outline-none transition-all"
                        placeholder="••••••">
                </div>
                <p class="error-otp text-rose-500 text-xs mt-1.5 font-medium text-center hidden"></p>
                @error('otp') <p class="text-rose-500 text-xs mt-1.5 font-medium text-center">{{ $message }}</p> @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium py-3 rounded-xl transition-colors text-sm shadow-lg shadow-blue-600/20 flex items-center justify-center gap-2">
                Verify & Continue
                <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
            </button>
        </form>

        <div class="text-center mt-6 pt-6 border-t border-slate-850 text-xs text-slate-400 flex flex-col items-center gap-2">
            <div>
                Didn't receive the code?
                <form id="resend-form" action="{{ route('seller.otp.resend') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" id="resend-btn" class="text-blue-500 hover:underline font-semibold bg-transparent border-none p-0 inline disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer" {{ $remainingSeconds > 0 ? 'disabled' : '' }}>
                        Resend Code
                    </button>
                </form>
            </div>

            <div id="countdown-wrapper" class="{{ $remainingSeconds > 0 ? '' : 'hidden' }}">
                <span class="text-xs text-slate-500">
                    Resend code in <span id="timer" class="font-bold text-slate-400">{{ sprintf('%02d:%02d', floor($remainingSeconds / 60), $remainingSeconds % 60) }}</span>
                </span>
            </div>
        </div>

    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function() {
            const $form = $('form');
            const $otp = $('#otp');
            const $errOtp = $('.error-otp');

            function validateOtp() {
                const val = $otp.val().trim();
                if (!val) {
                    $errOtp.text('Verification code is required.').removeClass('hidden');
                    $otp.addClass('border-rose-500');
                    return false;
                } else if (!/^[0-9]{6}$/.test(val)) {
                    $errOtp.text('Please enter a valid 6-digit code.').removeClass('hidden');
                    $otp.addClass('border-rose-500');
                    return false;
                } else {
                    $errOtp.addClass('hidden').text('');
                    $otp.removeClass('border-rose-500');
                    return true;
                }
            }

            $otp.on('input blur', validateOtp);

            // Limit input to digits only
            $otp.on('keypress', function(e) {
                if (e.which < 48 || e.which > 57) {
                    e.preventDefault();
                }
            });

            $form.on('submit', function(e) {
                if (!validateOtp()) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
            });

            // Countdown Timer logic
            let remainingSeconds = {{ $remainingSeconds }};
            if (remainingSeconds > 0) {
                const $timer = $('#timer');
                const $wrapper = $('#countdown-wrapper');
                const $btn = $('#resend-btn');

                const interval = setInterval(function() {
                    remainingSeconds--;
                    if (remainingSeconds <= 0) {
                        clearInterval(interval);
                        $wrapper.addClass('hidden');
                        $btn.removeAttr('disabled');
                    } else {
                        const minutes = Math.floor(remainingSeconds / 60);
                        const seconds = remainingSeconds % 60;
                        $timer.text(minutes + ':' + (seconds < 10 ? '0' : '') + seconds);
                    }
                }, 1000);
            }
        });

        // Loader on submit
        document.addEventListener('submit', (e) => {
            if (e.defaultPrevented) return;
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                if (!btn.querySelector('.fa-spinner')) {
                    const spinner = document.createElement('i');
                    spinner.className = 'fa-solid fa-spinner fa-spin mr-2';
                    btn.insertBefore(spinner, btn.firstChild);
                }
                btn.disabled = true;
                btn.style.pointerEvents = 'none';
                btn.style.opacity = '0.8';
            }
        });
    </script>
</body>
</html>
