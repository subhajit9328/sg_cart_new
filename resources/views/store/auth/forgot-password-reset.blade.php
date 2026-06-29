@extends('layouts.store')

@section('title', 'Reset Password — sgcart')

@section('content')
<div class="max-w-[480px] mx-auto px-6 pt-8 pb-12 md:pt-12 md:pb-20">
    <div class="bg-white border border-border rounded-2xl p-6 md:p-8 shadow-[0_4px_30px_rgba(0,0,0,0.02)]">
        <h2 class="font-display font-extrabold text-2xl text-slate-800 text-center mb-2">New Password</h2>
        <p class="text-xs text-slate-400 text-center mb-8">Please choose a secure new password for your account.</p>

        <form action="{{ route('store.forgot-password.reset.submit') }}" method="POST" class="flex flex-col gap-4" novalidate>
            @csrf
            
            <div class="flex flex-col gap-1">
                <label class="label">New Password</label>
                <input type="password" name="password" id="password" required class="inp" placeholder="Minimum 8 characters" autofocus/>
                <p class="error-password text-rose-500 text-xs mt-1 hidden"></p>
                @error('password') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-1">
                <label class="label">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="inp" placeholder="Confirm your new password"/>
                <p class="error-password-conf text-rose-500 text-xs mt-1 hidden"></p>
            </div>

            <button type="submit" class="btn btn-primary w-full py-4 mt-2" style="height: 50px;">Reset Password</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    const $form = $('form[action*="reset"]');
    const $password = $('#password');
    const $passwordConf = $('#password_confirmation');

    const $errPassword = $('.error-password');
    const $errPasswordConf = $('.error-password-conf');

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

    $password.on('input blur', validatePassword);
    $passwordConf.on('input blur', validatePasswordConf);

    $form.on('submit', function(e) {
        const isPasswordValid = validatePassword();
        const isPasswordConfValid = validatePasswordConf();

        if (!isPasswordValid || !isPasswordConfValid) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
