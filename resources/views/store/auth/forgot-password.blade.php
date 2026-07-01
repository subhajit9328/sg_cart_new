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
                <x-email-or-phone name="email_or_phone" id="email_or_phone" required="true" placeholder="email@example.com or phone number" value="{{ old('email_or_phone') }}"/>
            </div>

            <button type="submit" class="btn btn-primary w-full py-4 mt-2" style="height: 50px;">Send Verification Code</button>
        </form>

        <div class="text-center mt-6 pt-6 border-t border-slate-100 text-xs text-slate-400">
            Remember your password? <a href="{{ route('store.login') }}" class="text-accent hover:text-ink font-bold no-underline transition-colors">Sign In</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select the form using standard querySelector
        const form = document.querySelector('form[action*="forgot-password"]');

        // Safety check to ensure the form exists on the page
        if (form) {
            form.addEventListener('submit', function(e) {
                let isEmailOrPhoneValid = true;

                // Check if our global component validation function is available
                if (typeof window.validateEmailPhoneComponent === 'function') {
                    isEmailOrPhoneValid = window.validateEmailPhoneComponent();
                }

                // If validation fails, stop the form submission
                if (!isEmailOrPhoneValid) {
                    e.preventDefault();
                }
            });
        }
    });
</script>
@endpush
