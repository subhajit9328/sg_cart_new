@extends('layouts.admin')

@section('title', 'Influencer Posts — SGCart Admin')

@section('content')

<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Influencer Social Posts</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Social Share', 'url' => route('admin.social-shares.index')],
            ['label' => 'Influencer Posts']
        ]" />
    </div>
</div>

<!-- KPI Metrics Section -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-400 text-lg shrink-0">
            <i class="fa-solid fa-share-nodes"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Posts</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ $posts->total() }}</span>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg shrink-0">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pending Approval</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ \SGCart\SocialShare\Models\SocialPost::where('status', 'pending')->count() }}</span>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg shrink-0">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Approved Posts</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ \SGCart\SocialShare\Models\SocialPost::where('status', 'approved')->count() }}</span>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center text-rose-600 dark:text-rose-400 text-lg shrink-0">
            <i class="fa-solid fa-ban"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Rejected Posts</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ \SGCart\SocialShare\Models\SocialPost::where('status', 'rejected')->count() }}</span>
        </div>
    </div>
</div>

<!-- Posts Table Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
    <!-- Card Header with Filters -->
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
        <div class="flex items-center gap-2">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Moderation Queue</h2>
        </div>

        <form action="{{ route('admin.social-shares.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <!-- Status Filter -->
            <select name="status" onchange="this.form.submit()" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-slate-100 cursor-pointer">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>

            @if(request('status'))
                <a href="{{ route('admin.social-shares.index') }}" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 dark:bg-rose-950/20 dark:border-rose-900/30 dark:text-rose-400 text-xs font-semibold transition-colors">
                    Clear Filters
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 dark:bg-slate-900/50 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <th class="py-3 px-5 w-1/4">Influencer</th>
                    <th class="py-3 px-5">Media Preview</th>
                    <th class="py-3 px-5">Verification Context</th>
                    <th class="py-3 px-5">Caption</th>
                    <th class="py-3 px-5">Status</th>
                    <th class="py-3 px-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                @forelse($posts as $post)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/50 transition-colors">
                        <!-- Customer -->
                        <td class="py-4 px-5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center font-bold text-slate-650 overflow-hidden shrink-0">
                                    @if($post->customer?->profile_picture)
                                        <img src="{{ Storage::url($post->customer->profile_picture) }}" alt="Avatar" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr($post->customer?->name ?? '?', 0, 2)) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold text-slate-850 dark:text-white">{{ $post->customer?->name ?? 'Unknown Customer' }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $post->customer?->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Media Preview -->
                        <td class="py-4 px-5">
                            @if($post->media_type === 'video')
                                <div class="relative w-16 h-20 bg-slate-950 rounded-lg overflow-hidden flex items-center justify-center border border-slate-200 dark:border-slate-800 group">
                                    <video src="{{ Storage::url($post->media_path) }}" class="w-full h-full object-cover opacity-75"></video>
                                    <span class="absolute inset-0 flex items-center justify-center text-white bg-black/40 group-hover:bg-black/20 transition-all rounded-lg cursor-pointer" onclick="openMediaPreview('{{ Storage::url($post->media_path) }}', 'video')">
                                        <i class="fa-solid fa-play text-sm"></i>
                                    </span>
                                </div>
                            @else
                                <div class="w-16 h-20 bg-slate-100 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 cursor-pointer hover:opacity-90 transition-opacity" onclick="openMediaPreview('{{ Storage::url($post->media_path) }}', 'image')">
                                    <img src="{{ Storage::url($post->media_path) }}" alt="Preview" class="w-full h-full object-cover">
                                </div>
                            @endif
                        </td>

                        <!-- Verification Context -->
                        <td class="py-4 px-5">
                            <div class="flex flex-col gap-1">
                                @if($post->order_number)
                                    <div>
                                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-955/20 dark:text-blue-400 border border-blue-100 dark:border-blue-900/50">Order #{{ $post->order_number }}</span>
                                    </div>
                                @endif
                                @if($post->product_sku)
                                    <div>
                                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 dark:bg-purple-955/20 dark:text-purple-400 border border-purple-100 dark:border-purple-900/50">SKU: {{ $post->product_sku }}</span>
                                    </div>
                                @endif
                                @if($post->product)
                                    <div class="text-[10px] text-slate-550 dark:text-slate-400 font-medium max-w-[200px] truncate" title="{{ $post->product->name }}">
                                        Product: {{ $post->product->name }}
                                    </div>
                                @else
                                    <div class="text-[10px] text-slate-400 italic">No linked product</div>
                                @endif
                            </div>
                        </td>

                        <!-- Caption -->
                        <td class="py-4 px-5 max-w-xs">
                            <div class="text-slate-650 dark:text-slate-300 truncate font-medium" title="{{ $post->caption }}">
                                {{ $post->caption ?? 'No caption provided.' }}
                            </div>
                        </td>

                        <!-- Status Badge -->
                        <td class="py-4 px-5">
                            @if($post->status === 'approved')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">Approved</span>
                            @elseif($post->status === 'rejected')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50" title="Reason: {{ $post->rejection_reason }}">Rejected</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">Pending</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-4 px-5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($post->status === 'pending')
                                    <form action="{{ route('admin.social-shares.approve', $post->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 dark:bg-emerald-950/30 dark:hover:bg-emerald-900/50 dark:text-emerald-400 flex items-center justify-center border border-emerald-250/20 transition-colors" title="Approve Post">
                                            <i class="fa-solid fa-check text-sm"></i>
                                        </button>
                                    </form>
                                    <button onclick="openRejectModal('{{ $post->id }}')" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/30 dark:hover:bg-rose-900/50 dark:text-rose-400 flex items-center justify-center border border-rose-250/20 transition-colors cursor-pointer" title="Reject Post">
                                        <i class="fa-solid fa-xmark text-sm"></i>
                                    </button>
                                @endif
                                
                                <form action="{{ route('admin.social-shares.destroy', $post->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this post?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-red-600 dark:bg-slate-800/50 dark:hover:bg-red-950/30 dark:hover:text-red-400 flex items-center justify-center border border-slate-200 dark:border-slate-750 transition-colors" title="Delete Post">
                                        <i class="fa-regular fa-trash-can text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-slate-400 dark:text-slate-500">
                            <i class="fa-solid fa-share-nodes text-4xl mb-3 opacity-25 block"></i>
                            <p class="text-sm font-medium">No influencer posts found matching the filters.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($posts->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            {{ $posts->links() }}
        </div>
    @endif
</div>

<!-- Media Preview Modal -->
<div id="mediaModal" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300">
    <div class="relative bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full max-h-[85vh] overflow-hidden shadow-2xl p-2 flex flex-col items-center">
        <button onclick="closeMediaPreview()" class="absolute right-4 top-4 z-10 w-9 h-9 bg-black/60 hover:bg-black/80 text-white rounded-full flex items-center justify-center transition-colors border-none cursor-pointer">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div id="modalMediaContainer" class="w-full h-full flex items-center justify-center p-2 rounded-xl bg-slate-950 min-h-[300px]">
            <!-- Injected by JS -->
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="font-display font-bold text-slate-850 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-ban text-rose-500"></i> Reject Influencer Post
            </h3>
            <button onclick="closeRejectModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 bg-transparent border-none cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="rejectForm" action="" method="POST" class="p-6">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Rejection Reason</label>
                <textarea name="rejection_reason" required rows="4" class="w-full px-3 py-2 text-xs border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg text-slate-800 dark:text-slate-100 outline-none focus:border-blue-500 transition-all placeholder:text-slate-400" placeholder="Describe why this post is rejected (e.g. invalid SKU, poor video quality, irrelevant content)..."></textarea>
            </div>
            <div class="flex justify-end gap-3 mt-5">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-bold hover:bg-slate-50 transition-colors cursor-pointer">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition-colors cursor-pointer border-none">Reject Post</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openMediaPreview(url, type) {
        const modal = document.getElementById('mediaModal');
        const container = document.getElementById('modalMediaContainer');
        
        if (type === 'video') {
            container.innerHTML = `<video src="${url}" controls autoplay class="max-w-full max-h-[70vh] rounded-lg" style="outline:none;"></video>`;
        } else {
            container.innerHTML = `<img src="${url}" alt="Preview" class="max-w-full max-h-[70vh] rounded-lg object-contain" />`;
        }
        
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
    }

    function closeMediaPreview() {
        const modal = document.getElementById('mediaModal');
        const container = document.getElementById('modalMediaContainer');
        container.innerHTML = '';
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
    }

    function openRejectModal(postId) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        form.action = "{{ route('admin.social-shares.reject', ':id') }}".replace(':id', postId);
        
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
    }

    function closeRejectModal() {
        const modal = document.getElementById('rejectModal');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
    }
</script>

@endsection
