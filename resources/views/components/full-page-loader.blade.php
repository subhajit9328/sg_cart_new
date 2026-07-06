@props([
    'id' => 'full-page-loader',
    'text' => 'Updating...',
])

<style>
    @keyframes spin-reverse {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(-360deg); }
    }
    .animate-spin-reverse {
        animation: spin-reverse 1.2s linear infinite;
    }
</style>

<div id="{{ $id }}" class="fixed inset-0 bg-white/85 dark:bg-slate-950/85 backdrop-blur-[5px] flex flex-col items-center justify-center z-[999999] opacity-0 pointer-events-none transition-opacity duration-300">
    <div class="relative w-16 h-16">
        <div class="absolute inset-0 border-4 border-transparent border-t-blue-500 rounded-full animate-spin"></div>
        <div class="absolute inset-0 border-4 border-transparent border-b-emerald-500 rounded-full animate-spin-reverse"></div>
    </div>
    <p class="mt-5 font-display text-sm font-semibold text-slate-800 dark:text-slate-200 tracking-wider">{{ $text }}</p>
</div>

<script>
    window.showFullPageLoader = function() {
        const loader = document.getElementById('{{ $id }}');
        if (loader) {
            loader.classList.remove('pointer-events-none');
            loader.classList.add('opacity-100');
        }
    };
    
    window.hideFullPageLoader = function() {
        const loader = document.getElementById('{{ $id }}');
        if (loader) {
            loader.classList.add('pointer-events-none');
            loader.classList.remove('opacity-100');
        }
    };
</script>
