@extends('layouts.store')

@section('title', 'Create Account — sgcart')

@section('content')
<div class="max-w-[480px] mx-auto px-6 pt-8 pb-12 md:pt-12 md:pb-20">
    <div class="bg-white border border-border rounded-2xl p-6 md:p-8 shadow-[0_4px_30px_rgba(0,0,0,0.02)]">
        <h2 class="font-display font-extrabold text-2xl text-slate-800 text-center mb-2">Create Account</h2>
        <p class="text-xs text-slate-400 text-center mb-8">Join us to manage orders, addresses, and wishlist items.</p>

        <form action="{{ route('store.register.submit') }}" method="POST" class="flex flex-col gap-4" novalidate>
            @csrf
            
            <div class="flex flex-col gap-1">
                <label class="label">Full Name</label>
                <input type="text" name="name" id="name" required class="inp" placeholder="John Doe" value="{{ old('name') }}"/>
                <p class="error-name text-rose-500 text-xs mt-1 hidden"></p>
                @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-1">
                <label class="label">Email or Phone Number</label>
                <input type="text" name="email_or_phone" id="email_or_phone" required class="inp" placeholder="email@example.com or +1234567890" value="{{ old('email_or_phone', request('email_or_phone')) }}"/>
                <p class="error-email-phone text-rose-500 text-xs mt-1 hidden"></p>
                @error('email_or_phone') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-1">
                <label class="label">Password</label>
                <input type="password" name="password" id="password" required class="inp" placeholder="Minimum 8 characters"/>
                <p class="error-password text-rose-500 text-xs mt-1 hidden"></p>
                @error('password') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-1">
                <label class="label">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="inp" placeholder="Confirm your password"/>
                <p class="error-password-conf text-rose-500 text-xs mt-1 hidden"></p>
            </div>

            <button type="submit" class="btn btn-primary w-full py-4 mt-2" style="height: 50px;">Sign Up</button>
        </form>

        <div class="text-center mt-6 pt-6 border-t border-slate-100 text-xs text-slate-400">
            Already have an account? <a href="{{ route('store.login') }}" class="text-accent hover:text-ink font-bold no-underline transition-colors">Sign In</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    const $form = $('form');
    const $name = $('#name');
    const $emailOrPhone = $('#email_or_phone');
    const $password = $('#password');
    const $passwordConf = $('#password_confirmation');

    const $errName = $('.error-name');
    const $errEmailPhone = $('.error-email-phone');
    const $errPassword = $('.error-password');
    const $errPasswordConf = $('.error-password-conf');

    function validateName() {
        const val = $name.val().trim();
        if (!val) {
            $errName.text('Full name is required.').removeClass('hidden');
            $name.addClass('border-rose-500');
            return false;
        } else {
            $errName.addClass('hidden').text('');
            $name.removeClass('border-rose-500');
            return true;
        }
    }

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

    // Password must be at least 8 characters
    function validatePassword() {
        const val = $password.val();
        if (!val) {
            $errPassword.text('Password is required.').removeClass('hidden');
            $password.addClass('border-rose-500');
            return false;
        } else if (val.length < 8) {
            $errPassword.text('Password must be at least 8 characters.').removeClass('hidden');
            $password.addClass('border-rose-500');
            return false;
        } else {
            $errPassword.addClass('hidden').text('');
            $password.removeClass('border-rose-500');
            return true;
        }
    }

    function validatePasswordConf() {
        const val = $password.val();
        const valConf = $passwordConf.val();
        if (!valConf) {
            $errPasswordConf.text('Please confirm your password.').removeClass('hidden');
            $passwordConf.addClass('border-rose-500');
            return false;
        } else if (val !== valConf) {
            $errPasswordConf.text('Passwords do not match.').removeClass('hidden');
            $passwordConf.addClass('border-rose-500');
            return false;
        } else {
            $errPasswordConf.addClass('hidden').text('');
            $passwordConf.removeClass('border-rose-500');
            return true;
        }
    }

    // Inline triggers on input
    $name.on('input blur', validateName);
    $emailOrPhone.on('input blur', validateEmailOrPhone);
    $password.on('input blur', validatePassword);
    $passwordConf.on('input blur', validatePasswordConf);

    // Form submit validation
    $form.on('submit', function(e) {
        const isNameValid = validateName();
        const isEmailOrPhoneValid = validateEmailOrPhone();
        const isPasswordValid = validatePassword();
        const isPasswordConfValid = validatePasswordConf();

        if (!isNameValid || !isEmailOrPhoneValid || !isPasswordValid || !isPasswordConfValid) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
