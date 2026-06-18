<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden — SGCart Admin</title>

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
<body class="bg-slate-950 flex items-center justify-center min-h-screen p-4 overflow-hidden relative">
    
    <!-- Decorative Gradients -->
    <div class="absolute w-[40rem] h-[40rem] rounded-full bg-rose-500/5 blur-3xl -top-40 -left-40"></div>
    <div class="absolute w-[40rem] h-[40rem] rounded-full bg-red-500/5 blur-3xl -bottom-40 -right-40"></div>

    <div class="w-full max-w-md bg-slate-900 border border-slate-800/80 rounded-2xl p-8 shadow-2xl text-center relative z-10">
        
        <!-- Icon -->
        <div class="w-16 h-16 rounded-full bg-rose-500/10 text-rose-500 mx-auto flex items-center justify-center text-3xl mb-6">
            <i class="fa-solid fa-hand-fist"></i>
        </div>

        <!-- Heading -->
        <h1 class="font-display font-extrabold text-white text-3xl mb-2 tracking-tight">403</h1>
        <h2 class="font-display font-semibold text-slate-200 text-lg mb-3">Access Denied</h2>
        
        <!-- Description -->
        <p class="text-slate-400 text-sm mb-8 leading-relaxed">
            You do not have the required permissions to access this page. Please contact your system administrator if you believe this is a mistake.
        </p>

        <!-- Navigation Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            @auth
                <a href="{{ route('admin.dashboard') }}" 
                    class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium py-2.5 px-5 rounded-xl transition-colors text-sm shadow-lg shadow-blue-600/10 inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-house text-xs"></i>
                    Back to Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" 
                    class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium py-2.5 px-5 rounded-xl transition-colors text-sm shadow-lg shadow-blue-600/10 inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-from-bracket text-xs"></i>
                    Go to Sign In
                </a>
            @endauth
            
            <form action="{{ route('logout') }}" method="POST" id="logoutForm" class="hidden">
                @csrf
            </form>
            <button onclick="event.preventDefault(); document.getElementById('logoutForm').submit();"
                class="w-full sm:w-auto border border-slate-700 hover:bg-slate-800 text-slate-300 font-medium py-2.5 px-5 rounded-xl transition-colors text-sm">
                Sign Out
            </button>
        </div>
    </div>
</body>
</html>
