@extends('layouts.store')

@section('title', 'Sign In — sgcart')

@section('content')
<div class="max-w-[480px] mx-auto px-6 pt-8 pb-12 md:pt-12 md:pb-20">
    <div class="bg-white border border-border rounded-2xl p-6 md:p-8 shadow-[0_4px_30px_rgba(0,0,0,0.02)]">
        <h2 class="font-display font-extrabold text-2xl text-slate-800 text-center mb-2">Welcome Back</h2>
        <p class="text-xs text-slate-400 text-center mb-8">Please enter your credentials to access your account.</p>

        <form id="login_form" action="{{ route('store.login.submit') }}" method="POST" class="flex flex-col gap-4" novalidate>
            @csrf
            <div class="flex flex-col gap-1">
                <label class="label">Email or Phone Number</label>
                <x-email-or-phone name="email_or_phone" id="email_or_phone" required="true" placeholder="email@example.com or phone number" value="{{ old('email_or_phone', $rememberedIdentifier ?? '') }}"/>
            </div>

            <div class="flex flex-col gap-1">
                <label class="label">Password</label>
                <input type="password" name="password" id="password" required class="inp" placeholder="••••••••"/>
                <p class="error-password text-rose-500 text-xs mt-1 hidden"></p>
                @error('password') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-between text-xs text-stone mt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="accent-ink w-4 h-4" {{ old('remember') || isset($rememberedIdentifier) ? 'checked' : '' }}/>
                    <span>Remember me</span>
                </label>
                <a href="{{ route('store.forgot-password') }}" class="text-accent hover:text-ink font-semibold no-underline transition-colors">Forgot password?</a>
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
<script>
$(document).ready(function() {
    const $form = $('#login_form');
    const $emailOrPhone = $('#email_or_phone');
    const $password = $('#password');
    const $errPassword = $('.error-password');

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

    $password.on('input blur', validatePassword);

    // Form submit validation
    $form.on('submit', function(e) {
        const isPasswordValid = validatePassword();
        let isEmailOrPhoneValid = true;

        // Call the component's exposed validation function safely
        if (typeof window.validateEmailPhoneComponent === 'function') {
            isEmailOrPhoneValid = window.validateEmailPhoneComponent();
        }
        if (!isEmailOrPhoneValid || !isPasswordValid) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
