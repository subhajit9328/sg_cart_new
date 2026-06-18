@extends('layouts.store')

@section('title', 'Sign In — sgcart')

@section('content')
<div class="max-w-[480px] mx-auto px-6 pt-8 pb-12 md:pt-12 md:pb-20">
    <div class="bg-white border border-border rounded-2xl p-6 md:p-8 shadow-[0_4px_30px_rgba(0,0,0,0.02)]">
        <h2 class="font-display font-extrabold text-2xl text-slate-800 text-center mb-2">Welcome Back</h2>
        <p class="text-xs text-slate-400 text-center mb-8">Please enter your credentials to access your account.</p>

        <form action="{{ route('store.login.submit') }}" method="POST" class="flex flex-col gap-4" novalidate>
            @csrf
            
            <div class="flex flex-col gap-1">
                <label class="label">Email Address</label>
                <input type="email" name="email" id="email" required class="inp" placeholder="your.email@domain.com" value="{{ old('email') }}"/>
                <p class="error-email text-rose-500 text-xs mt-1 hidden"></p>
                @error('email') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-1">
                <label class="label">Password</label>
                <input type="password" name="password" id="password" required class="inp" placeholder="••••••••"/>
                <p class="error-password text-rose-500 text-xs mt-1 hidden"></p>
                @error('password') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-between text-xs text-stone mt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="accent-ink w-4 h-4"/>
                    <span>Remember me</span>
                </label>
                <a href="#" class="text-accent hover:text-ink font-semibold no-underline transition-colors" onclick="showToast('Password reset is coming soon!','success')">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-primary w-full py-4 mt-2" style="height: 50px;">Sign In</button>
        </form>

        <div class="text-center mt-6 pt-6 border-t border-slate-100 text-xs text-slate-400">
            Don't have an account? <a href="{{ route('store.register') }}" class="text-accent hover:text-ink font-bold no-underline transition-colors">Create one</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    const $form = $('form');
    const $email = $('#email');
    const $password = $('#password');
    const $errEmail = $('.error-email');
    const $errPassword = $('.error-password');

    function validateEmail() {
        const val = $email.val().trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!val) {
            $errEmail.text('Email address is required.').removeClass('hidden');
            $email.addClass('border-rose-500');
            return false;
        } else if (!emailRegex.test(val)) {
            $errEmail.text('Please enter a valid email address.').removeClass('hidden');
            $email.addClass('border-rose-500');
            return false;
        } else {
            $errEmail.addClass('hidden').text('');
            $email.removeClass('border-rose-500');
            return true;
        }
    }

    function validatePassword() {
        const val = $password.val();
        if (!val) {
            $errPassword.text('Password is required.').removeClass('hidden');
            $password.addClass('border-rose-500');
            return false;
        } else {
            $errPassword.addClass('hidden').text('');
            $password.removeClass('border-rose-500');
            return true;
        }
    }

    // Inline triggers on input
    $email.on('input blur', validateEmail);
    $password.on('input blur', validatePassword);

    // Form submit validation
    $form.on('submit', function(e) {
        const isEmailValid = validateEmail();
        const isPasswordValid = validatePassword();
        if (!isEmailValid || !isPasswordValid) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
