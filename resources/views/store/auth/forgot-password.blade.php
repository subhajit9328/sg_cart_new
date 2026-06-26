@extends('layouts.store')

@section('title', 'Forgot Password — sgcart')

@section('content')
<div class="max-w-[480px] mx-auto px-6 pt-8 pb-12 md:pt-12 md:pb-20">
    <div class="bg-white border border-border rounded-2xl p-6 md:p-8 shadow-[0_4px_30px_rgba(0,0,0,0.02)]">
        <h2 class="font-display font-extrabold text-2xl text-slate-800 text-center mb-2">Forgot Password</h2>
        <p class="text-xs text-slate-400 text-center mb-8">Enter your registered email address or phone number to reset your password.</p>

        <form action="{{ route('store.forgot-password.submit') }}" method="POST" class="flex flex-col gap-4" novalidate>
            @csrf
            
            <div class="flex flex-col gap-1">
                <label class="label">Email or Phone Number</label>
                <input type="text" name="email_or_phone" id="email_or_phone" required class="inp" placeholder="email@example.com or +1234567890" value="{{ old('email_or_phone') }}" autofocus/>
                <p class="error-email-phone text-rose-500 text-xs mt-1 hidden"></p>
                @error('email_or_phone') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn btn-primary w-full py-4 mt-2" style="height: 50px;">Send Verification Code</button>
        </form>

        <div class="text-center mt-6 pt-6 border-t border-slate-100 text-xs text-slate-400">
            Remember your password? <a href="{{ route('store.login') }}" class="text-accent hover:text-ink font-bold no-underline transition-colors">Sign In</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    const $form = $('form[action*="forgot-password"]');
    const $emailOrPhone = $('#email_or_phone');
    const $errEmailPhone = $('.error-email-phone');

    function validateEmailOrPhone() {
        const val = $emailOrPhone.val().trim();
        if (!val) {
            $errEmailPhone.text('Email or phone number is required.').removeClass('hidden');
            $emailOrPhone.addClass('border-rose-500');
            return false;
        }

        const isEmail = val.includes('@');
        if (isEmail) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(val)) {
                $errEmailPhone.text('Please enter a valid email address.').removeClass('hidden');
                $emailOrPhone.addClass('border-rose-500');
                return false;
            }
        } else {
            const phoneRegex = /^\+\d{7,15}$/;
            if (!phoneRegex.test(val)) {
                $errEmailPhone.text('The phone number must include a country code starting with + followed by the number (e.g. +1234567890).').removeClass('hidden');
                $emailOrPhone.addClass('border-rose-500');
                return false;
            }
        }

        $errEmailPhone.addClass('hidden').text('');
        $emailOrPhone.removeClass('border-rose-500');
        return true;
    }

    $emailOrPhone.on('input blur', validateEmailOrPhone);

    $form.on('submit', function(e) {
        if (!validateEmailOrPhone()) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
