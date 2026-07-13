@extends('layouts.store')

@section('title', 'Create Account — sgcart')

@section('content')
<div class="max-w-[480px] mx-auto px-6 pt-8 pb-12 md:pt-12 md:pb-20">
    <div class="bg-white border border-border rounded-2xl p-6 md:p-8 shadow-[0_4px_30px_rgba(0,0,0,0.02)]">
        <h2 class="font-display font-extrabold text-2xl text-slate-800 text-center mb-2">Create Account</h2>
        <p class="text-xs text-slate-400 text-center mb-8">Join us to manage orders, addresses, and wishlist items.</p>

        <form id="register_form" action="{{ route('store.register.submit') }}" method="POST" class="flex flex-col gap-4" novalidate>
            @csrf

            <div class="flex flex-col gap-1">
                <label class="label">Full Name</label>
                <input type="text" name="name" id="name" required class="inp" placeholder="John Doe" value="{{ old('name') }}"/>
                <p class="error-name text-rose-500 text-xs mt-1 hidden"></p>
                @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-1">
                <label class="label">Email or Phone Number</label>
                <x-email-or-phone name="email_or_phone" id="email_or_phone" required="true" placeholder="email@example.com or phone number" value="{{ old('email_or_phone', request('email_or_phone')) }}"/>
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
        <div class="text-center mt-4 text-xs text-slate-400">
            Want to expand your business? <a href="{{ route('seller.register') }}"  target="_blank" class="text-accent hover:text-ink font-bold no-underline transition-colors text-blue-500">Register as a Seller</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const $form = $('#register_form');
    const $name = $('#name');
    const $emailOrPhone = $('#email_or_phone');
    const $password = $('#password');
    const $passwordConf = $('#password_confirmation');

    const $errName = $('.error-name');
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
    $password.on('input blur', validatePassword);
    $passwordConf.on('input blur', validatePasswordConf);

    // Form submit validation
    $form.on('submit', function(e) {
        const isNameValid = validateName();
        const isPasswordValid = validatePassword();
        const isPasswordConfValid = validatePasswordConf();
        let isEmailOrPhoneValid = true;

        if (typeof window.validateEmailPhoneComponent === 'function') {
            isEmailOrPhoneValid = window.validateEmailPhoneComponent();
        }

        if (!isNameValid || !isEmailOrPhoneValid || !isPasswordValid || !isPasswordConfValid) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
