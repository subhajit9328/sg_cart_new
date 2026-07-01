@extends('layouts.store')

@section('title', $isEmail ? 'Verify Email — sgcart' : 'Verify Phone — sgcart')

@php
    function obfuscate_email($email){
         $em   = explode("@",$email);
         $name = implode('@', array_slice($em, 0, count($em)-1));
         $len  = floor(strlen($name)/2);

         return substr($name,0, $len) . str_repeat('*', $len) . "@" . end($em);
    }
     function obfuscate_mobile($number){
            return substr($number, 0, 5) . str_repeat('*', strlen($number) - 7) . substr($number, -2);
    }
@endphp

@section('content')
<div class="max-w-[480px] mx-auto px-6 pt-8 pb-12 md:pt-12 md:pb-20">
    <div class="bg-white border border-border rounded-2xl p-6 md:p-8 shadow-[0_4px_30px_rgba(0,0,0,0.02)]">
        <h2 class="font-display font-extrabold text-2xl text-slate-800 text-center mb-2">
            {{ $isEmail ? 'Verify Email' : 'Verify Phone Number' }}
        </h2>
        <p class="text-xs text-slate-400 text-center mb-8">
                We've sent a 6-digit verification code to <strong class="text-slate-600">{{$isEmail ? obfuscate_email($email) : obfuscate_mobile($email)}}</strong>.
            <a class="block mt-3 text-accent font-bold" href="{{route('store.register', ['wrong_email_or_phone' => true])}}">Wrong {{$isEmail ? 'Email' : 'Phone No.'}} ?</a>
        </p>

        @if(!$isEmail && config('app.test_mode'))
        <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-950/15 border border-amber-200/40 rounded-xl text-amber-800 dark:text-amber-300 text-xs flex gap-3 items-start leading-relaxed">
            <i class="fa-solid fa-circle-info text-base mt-0.5 text-amber-500"></i>
            <div>
                <span class="font-bold block mb-0.5">Test Mode Active</span>
                For phone verification in test mode, you can enter any arbitrary 6-digit number to bypass and verify the OTP.
            </div>
        </div>
        @endif

        <form action="{{ route('store.otp.verify.submit') }}" method="POST" class="flex flex-col gap-4" novalidate>
            @csrf

            <div class="flex flex-col gap-1">
                <label class="label">Verification Code (OTP)</label>
                <input type="text" name="otp" id="otp" required maxlength="6" pattern="[0-9]{6}" class="inp text-center tracking-[8px] font-mono text-xl" placeholder="••••••" autofocus value="{{ old('otp') }}"/>
                <p class="error-otp text-rose-500 text-xs mt-1 hidden"></p>
                @error('otp') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn btn-primary w-full py-4 mt-2" style="height: 50px;">Verify & Continue</button>
        </form>

        <div class="text-center mt-6 pt-6 border-t border-slate-100 text-xs text-slate-400 flex flex-col items-center gap-2">
            <div>
                Didn't receive the code?
                <form id="resend-form" action="{{ route('store.otp.resend') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" id="resend-btn" class="text-accent hover:text-ink font-bold no-underline transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer bg-transparent border-none p-0 inline font-display" {{ $remainingSeconds > 0 ? 'disabled' : '' }}>
                        Resend Code
                    </button>
                </form>
            </div>

            <div id="countdown-wrapper" class="{{ $remainingSeconds > 0 ? '' : 'hidden' }}">
                <span class="text-xs text-slate-400">
                    Resend code in <span id="timer" class="font-bold text-slate-600">{{ sprintf('%02d:%02d', floor($remainingSeconds / 60), $remainingSeconds % 60) }}</span>
                </span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    const $form = $('form[action*="verify"]');
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
</script>
@endsection
