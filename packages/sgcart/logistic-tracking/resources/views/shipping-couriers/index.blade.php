@extends('layouts.admin')

@section('title', 'Shipping Carriers — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Shipping Carriers</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Logistics'],
            ['label' => 'Shipping Carriers']
        ]" />
    </div>
    <button type="button" onclick="openCreateModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10 transition-all cursor-pointer">
        <i class="fa-solid fa-plus"></i> Add Courier
    </button>
</div>

<!-- Couriers List Table -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <h2 class="text-sm font-bold text-slate-700 dark:text-slate-200">All Shipping Couriers</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Name</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Tracking URL</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Support Email</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($couriers as $courier)
                <tr>
                    <td class="px-5 py-4 font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                        {{ $courier->name }}
                    </td>
                    <td class="px-5 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                        @if($courier->url)
                            <a href="{{ $courier->url }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                                Visit Website <i class="fa-solid fa-up-right-from-square text-[9px]"></i>
                            </a>
                        @else
                            <span class="text-slate-400 dark:text-slate-600 italic">N/A</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                        @if($courier->support_email)
                            <a href="mailto:{{ $courier->support_email }}" class="text-slate-600 dark:text-slate-300 hover:underline">
                                {{ $courier->support_email }}
                            </a>
                        @else
                            <span class="text-slate-400 dark:text-slate-600 italic">N/A</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right whitespace-nowrap">
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" onclick="openEditModal({{ $courier->id }}, '{{ addslashes($courier->name) }}', '{{ addslashes($courier->url) }}', '{{ addslashes($courier->support_email) }}', this)" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors cursor-pointer" title="Edit Courier">
                                <i class="fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs"></i>
                            </button>
                            <button type="button" onclick="openDeleteModal({{ $courier->id }}, '{{ addslashes($courier->name) }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors" title="Delete Courier">
                                <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-slate-400 dark:text-slate-500 py-8">
                        <i class="fa-solid fa-truck-fast text-3xl mb-2 opacity-25 block"></i>
                        No shipping couriers added yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Courier Modal -->
<div id="createCourierModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fadeIn">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-lg w-full overflow-hidden transform transition-all scale-95 duration-200" id="createModalContainer">
        <!-- Modal Header -->
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between">
            <h2 class="font-semibold text-sm text-slate-750 dark:text-slate-200">Add Shipping Courier</h2>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer border-none bg-transparent outline-none">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Modal Body / Form -->
        <form action="{{ route('admin.couriers.store') }}" method="POST" class="p-6 space-y-5">
            @csrf

            <!-- Name Field -->
            <div>
                <label for="name" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Courier Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="name" required
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                    placeholder="e.g., DHL Express">
            </div>

                <!-- Website/Tracking URL Field -->
                <div>
                    <label for="url" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Website / Tracking URL</label>
                    <input type="url" name="url" id="url"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                        placeholder="https://...">
                </div>

                <!-- Support Email Field -->
                <div>
                    <label for="support_email" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Support Email</label>
                    <input type="email" name="support_email" id="support_email"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                        placeholder="support@courier.com">
                </div>

            <!-- Modal Footer -->
            <div class="pt-2 flex gap-3 justify-end">
                <button type="button" onclick="closeCreateModal()" class="inline-flex items-center justify-center border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-semibold py-2.5 px-6 rounded-lg transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold py-2.5 px-6 rounded-lg shadow-md transition-all cursor-pointer">
                    Save Courier
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Courier Modal -->
<div id="editCourierModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fadeIn">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-lg w-full overflow-hidden transform transition-all scale-95 duration-200" id="editModalContainer">
        <!-- Modal Header -->
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between">
            <h2 class="font-semibold text-sm text-slate-750 dark:text-slate-200">Edit Shipping Courier</h2>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer border-none bg-transparent outline-none">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Modal Body / Form -->
        <form id="editCourierForm" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <!-- Name Field -->
            <div>
                <label for="edit_name" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Courier Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="edit_name" required
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                    placeholder="e.g., DHL Express">
            </div>

                <!-- Website/Tracking URL Field -->
                <div>
                    <label for="edit_url" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Website / Tracking URL</label>
                    <input type="url" name="url" id="edit_url"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                        placeholder="https://...">
                </div>

                <!-- Support Email Field -->
                <div>
                    <label for="edit_support_email" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Support Email</label>
                    <input type="email" name="support_email" id="edit_support_email"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                        placeholder="support@courier.com">
                </div>

            <!-- Modal Footer -->
            <div class="pt-2 flex gap-3 justify-end">
                <button type="button" onclick="closeEditModal()" class="inline-flex items-center justify-center border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-semibold py-2.5 px-6 rounded-lg transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold py-2.5 px-6 rounded-lg shadow-md transition-all cursor-pointer">
                    Update Courier
                </button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    function openCreateModal() {
        const modal = document.getElementById('createCourierModal');
        const container = document.getElementById('createModalContainer');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            container.classList.remove('scale-95');
            container.classList.add('scale-100');
        }, 10);
    }

    function closeCreateModal() {
        const modal = document.getElementById('createCourierModal');
        const container = document.getElementById('createModalContainer');
        container.classList.remove('scale-100');
        container.classList.add('scale-95');
        modal.classList.remove('animate-fadeIn');
        modal.classList.add('animate-fadeOut');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            modal.classList.remove('animate-fadeOut');
            modal.classList.add('animate-fadeIn');
        }, 200);
    }

    function openEditModal(courierId, name, url, supportEmail, buttonElement) {
        if (buttonElement) {
            const icon = buttonElement.querySelector('i');
            if (icon) {
                icon.className = 'fa-solid fa-spinner fa-spin text-slate-500 dark:text-slate-400 text-xs';
            }
        }

        const modal = document.getElementById('editCourierModal');
        const container = document.getElementById('editModalContainer');
        const form = document.getElementById('editCourierForm');

        document.getElementById('edit_name').value = name;
        document.getElementById('edit_url').value = url || '';
        document.getElementById('edit_support_email').value = supportEmail || '';

        form.action = `/admin/couriers/${courierId}`;

        setTimeout(() => {
            if (buttonElement) {
                const icon = buttonElement.querySelector('i');
                if (icon) {
                    icon.className = 'fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs';
                }
            }
        }, 500);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            container.classList.remove('scale-95');
            container.classList.add('scale-100');
        }, 10);
    }

    function closeEditModal() {
        const modal = document.getElementById('editCourierModal');
        const container = document.getElementById('editModalContainer');
        container.classList.remove('scale-100');
        container.classList.add('scale-95');
        modal.classList.remove('animate-fadeIn');
        modal.classList.add('animate-fadeOut');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            modal.classList.remove('animate-fadeOut');
            modal.classList.add('animate-fadeIn');
        }, 200);
    }

    function openDeleteModal(courierId, name) {
        showConfirm(
            `Are you sure you want to delete shipping courier "${name}"? This action cannot be undone.`,
            () => {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/couriers/${courierId}`;
                form.submit();
            },
            'Delete Shipping Courier?'
        );
    }
</script>
@endsection
