<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Registration — SGCart Marketplace</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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

    <div class="w-full max-w-md bg-slate-950/80 border border-slate-800 rounded-2xl p-8 shadow-2xl relative z-10 backdrop-blur-md my-8 transition-all">
        
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center gap-2.5">
                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-500 to-indigo-600 shadow-md shadow-blue-500/20">
                    <i class="fa-solid fa-cart-shopping text-white text-sm"></i>
                </div>
                <h1 class="font-display text-3xl font-bold tracking-tight text-white flex items-baseline leading-none">sg<span class="text-blue-400 font-extrabold">cart</span><span class="ml-2 text-[9px] uppercase font-bold tracking-wider text-blue-200 bg-blue-500/10 px-1.5 py-0.5 rounded-md border border-blue-500/20 self-end leading-none mb-0.5">Seller</span></h1>
            </div>
            <p class="text-slate-400 text-xs mt-4">Submit your details to set up your marketplace shop</p>
        </div>

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-400 text-sm flex gap-3 items-start">
                <i class="fa-solid fa-circle-exclamation mt-0.5 text-rose-500"></i>
                <div class="flex-1">
                    <p class="font-semibold">Registration details invalid</p>
                    <ul class="list-disc pl-4 mt-1 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('seller.register.submit') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-1 gap-4">
                <!-- Owner Name -->
                <div>
                    <label for="name" class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Owner Full Name</label>
                    <div class="relative flex items-center">
                        <i class="fa-regular fa-user absolute left-4 text-slate-500 text-base"></i>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                            class="w-full bg-slate-900 border border-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl py-3 pl-12 pr-4 text-sm text-white placeholder:text-slate-500 outline-none transition-all"
                            placeholder="e.g. John Doe">
                    </div>
                </div>

                <!-- Email or Phone Number -->
                <div>
                    <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Email or Phone Number</label>
                    <x-email-or-phone name="email_or_phone" id="email_or_phone" required="true" placeholder="email@example.com or phone number" value="{{ old('email_or_phone', request('email_or_phone')) }}"/>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Password</label>
                    <div class="relative flex items-center">
                        <i class="fa-solid fa-lock absolute left-4 text-slate-500 text-base"></i>
                        <input type="password" name="password" id="password" required
                            class="w-full bg-slate-900 border border-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl py-3 pl-12 pr-4 text-sm text-white placeholder:text-slate-500 outline-none transition-all"
                            placeholder="Minimum 8 characters">
                    </div>
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Confirm Password</label>
                    <div class="relative flex items-center">
                        <i class="fa-solid fa-lock absolute left-4 text-slate-500 text-base"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="w-full bg-slate-900 border border-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl py-3 pl-12 pr-4 text-sm text-white placeholder:text-slate-500 outline-none transition-all"
                            placeholder="Repeat your password">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium py-3 rounded-xl transition-colors text-sm shadow-lg shadow-blue-600/20 flex items-center justify-center gap-2 mt-4">
                Submit Registration Request
                <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
            </button>
        </form>

        <div class="text-center mt-6 pt-6 border-t border-slate-800 text-xs text-slate-400">
            Already have a seller account? <a href="{{ route('seller.login') }}" class="text-blue-500 hover:underline font-semibold">Sign In</a>
        </div>

    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Password visibility toggle
        document.querySelectorAll('input[type="password"]').forEach(input => {
            let parent = input.parentNode;
            if (!parent.classList.contains('relative')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'relative w-full flex items-center';
                parent.insertBefore(wrapper, input);
                wrapper.appendChild(input);
                parent = wrapper;
            } else {
                parent.classList.add('flex', 'items-center');
            }
            
            input.classList.add('pr-12');
            
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'absolute right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer border-none bg-transparent outline-none focus:outline-none flex items-center justify-center p-1 text-sm z-10';
            toggleBtn.innerHTML = '<i class="fa-regular fa-eye"></i>';
            
            parent.appendChild(toggleBtn);
            
            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (input.type === 'password') {
                    input.type = 'text';
                    toggleBtn.innerHTML = '<i class="fa-regular fa-eye-slash"></i>';
                } else {
                    input.type = 'password';
                    toggleBtn.innerHTML = '<i class="fa-regular fa-eye"></i>';
                }
            });
        });

        // Inline Form Validation using jQuery
        $(document).ready(function() {
            const $form = $('form');
            const $name = $('#name');
            const $password = $('#password');
            const $passwordConf = $('#password_confirmation');

            $form.attr('novalidate', 'novalidate');

            function getErrorElement($input) {
                const name = $input.attr('name');
                let $err = $(`.js-error-${name}`);
                if ($err.length === 0) {
                    $err = $(`<p class="js-error-${name} text-rose-500 text-xs mt-1.5 font-medium"></p>`);
                    $input.parent().after($err);
                }
                return $err;
            }

            function showError($input, message) {
                const $err = getErrorElement($input);
                $err.text(message).show();
                $input.addClass('border-rose-500 focus:border-rose-500 focus:ring-rose-500');
                $input.removeClass('border-slate-800 focus:border-blue-500 focus:ring-blue-500');
            }

            function clearError($input) {
                const $err = getErrorElement($input);
                $err.text('').hide();
                $input.removeClass('border-rose-500 focus:border-rose-500 focus:ring-rose-500');
                $input.addClass('border-slate-800 focus:border-blue-500 focus:ring-blue-500');
            }

            function validateName() {
                if (!$name.val().trim()) {
                    showError($name, 'Full name is required.');
                    return false;
                }
                clearError($name);
                return true;
            }

            function validatePassword() {
                const val = $password.val();
                if (!val) {
                    showError($password, 'Password is required.');
                    return false;
                } else if (val.length < 8) {
                    showError($password, 'Password must be at least 8 characters.');
                    return false;
                } else {
                    clearError($password);
                    return true;
                }
            }

            function validatePasswordConf() {
                const val = $password.val();
                const valConf = $passwordConf.val();
                if (!valConf) {
                    showError($passwordConf, 'Please confirm your password.');
                    return false;
                } else if (val !== valConf) {
                    showError($passwordConf, 'Passwords do not match.');
                    return false;
                } else {
                    clearError($passwordConf);
                    return true;
                }
            }

            $name.on('input blur', validateName);
            $password.on('input blur', validatePassword);
            $passwordConf.on('input blur', validatePasswordConf);

            $form.on('submit', function(e) {
                const n = validateName();
                const p = validatePassword();
                const pc = validatePasswordConf();
                
                let isEmailOrPhoneValid = true;
                if (typeof window.validateEmailPhoneComponent === 'function') {
                    isEmailOrPhoneValid = window.validateEmailPhoneComponent();
                }

                if (!n || !isEmailOrPhoneValid || !p || !pc) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
            });
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
