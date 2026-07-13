@props([
    'items',
    'headers',
    'childrenField' => 'children',
    'parentField' => 'parent',
    'reorderRoute',
    'editRoute',
    'deleteCallback' => 'openDeleteModal',
    'idKey' => 'id',
    'ulidKey' => 'ulid',
    'nameKey' => 'name',
    'imageKey' => 'image',
    'parentLabelKey' => 'name',
    'statusKey' => 'is_active',
    'title' => 'All Items',
    'totalCount' => 0,
    'searchPlaceholder' => 'Search…',
    'action',
    'tableId' => 'nestedTableWrapper',
    'searchInputId' => 'nestedSearchInput',
    'totalCountId' => 'nestedTotalCount',
])

@php
    $isSearching = request()->filled('search');
    $search = request('search');
@endphp

<x-data-table
    {{ $attributes }}
    :items="$items"
    :headers="$headers"
    :title="$title"
    :totalCount="$totalCount"
    :searchPlaceholder="$searchPlaceholder"
    :action="$action"
    :tableId="$tableId"
    :searchInputId="$searchInputId"
    :totalCountId="$totalCountId"
>
    @forelse($items as $item)
        @php
            $itemChildren = $item->{$childrenField};
            $hasChildren = $itemChildren && $itemChildren->isNotEmpty();
            $hasMatchingChild = false;
            if ($isSearching && $hasChildren) {
                $hasMatchingChild = $itemChildren->contains(fn($child) => stripos($child->{$nameKey}, $search) !== false);
            }
            $isExpanded = $isSearching && $hasMatchingChild;
        @endphp
        <!-- Parent Row -->
        <tr {{ $hasChildren ? "onclick=if(!event.target.closest('button,a,input,select'))toggleNestedTableChildren('" . $item->{$idKey} . "','" . $tableId . "')" : '' }} class="{{ $hasChildren ? 'cursor-pointer' : '' }} hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors border-b border-slate-200 dark:border-slate-800" data-parent-row="{{ $item->{$idKey} }}" data-id="{{ $item->{$idKey} }}">
            <td class="px-5 py-3.5 text-center text-slate-400 dark:text-slate-600 w-12 drag-handle cursor-grab active:cursor-grabbing select-none">
                <i class="fa-solid fa-grip-vertical"></i>
            </td>
            <td class="px-5 py-3.5 font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap text-sm">
                <div class="flex items-center gap-3">
                    @if($hasChildren)
                        <button type="button"
                                onclick="toggleNestedTableChildren('{{ $item->{$idKey} }}','{{ $tableId }}')"
                                class="w-6 h-6 flex items-center justify-center rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-slate-850 dark:text-slate-400 dark:hover:text-slate-200 transition-colors focus:outline-none cursor-pointer"
                                aria-expanded="{{ $isExpanded ? 'true' : 'false' }}"
                                id="btn-toggle-{{ $item->{$idKey} }}">
                            <i class="fa-solid fa-chevron-right transform transition-transform duration-200 {{ $isExpanded ? 'rotate-90' : '' }}" id="icon-{{ $item->{$idKey} }}"></i>
                        </button>
                    @else
                        <div class="w-6 h-6 flex items-center justify-center select-none">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></span>
                        </div>
                    @endif

                    @if($item->{$imageKey})
                        <img src="{{ Storage::url($item->{$imageKey}) }}" class="w-8 h-8 object-cover rounded-lg border border-slate-200 dark:border-slate-800 bg-white">
                    @else
                        <div class="w-8 h-8 bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center text-xs font-bold border border-blue-100 dark:border-blue-900/50 select-none">
                            {{ \App\Helpers\AvatarHelper::getInitials($item->{$nameKey}) }}
                        </div>
                    @endif
                    <span>{{ $item->{$nameKey} }}</span>
                </div>
            </td>
            <td class="px-5 py-3.5 text-xs text-slate-655 dark:text-slate-400 whitespace-nowrap w-48">
                {{ $item->{$parentField}?->{$parentLabelKey} ?? '—' }}
            </td>
            <td class="px-5 py-3.5 whitespace-nowrap w-32">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold
                    {{ $item->{$statusKey} ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/20' : 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200/20' }}">
                    {{ $item->{$statusKey} ? 'Active' : 'Inactive' }}
                </span>
            </td>
            <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 font-mono text-xs whitespace-nowrap sort-order-display w-24">
                {{ $item->sort_order }}
            </td>
            <td class="px-5 py-3.5 text-right whitespace-nowrap w-32">
                <div class="inline-flex gap-1.5 justify-end">
                    <a href="{{ route($editRoute, $item->{$ulidKey}) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Edit">
                        <i class="fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs"></i>
                    </a>
                    <button onclick="{{ $deleteCallback }}('{{ $item->{$ulidKey} }}', '{{ addslashes($item->{$nameKey}) }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors cursor-pointer" title="Delete">
                        <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                    </button>
                </div>
            </td>
        </tr>

        <!-- Nested Child Rows -->
        @if($hasChildren)
            @foreach($itemChildren as $index => $child)
                @php
                    $isLastChild = $index === $itemChildren->count() - 1;
                @endphp
                <tr class="{{ $isExpanded ? 'table-row' : 'hidden' }} px-5 py-3.5 text-center text-slate-400 dark:text-slate-200 w-12 drag-handle cursor-grab active:cursor-grabbing bg-slate-50/70 hover:bg-slate-100/70 dark:bg-slate-950 dark:hover:bg-slate-950/20 select-none child-row-of-{{ $item->{$idKey} }}" data-child-of="{{ $item->{$idKey} }}" data-id="{{ $child->{$idKey} }}">
                    <td class="px-5 py-2.5 text-center text-slate-350 dark:text-slate-800 dark:hover:text-slate-700 w-12 drag-handle cursor-grab active:cursor-grabbing select-none">
                        <i class="fa-solid fa-grip-vertical"></i>
                    </td>
                    <td class="pl-[58px] pr-5 py-2.5 text-slate-700 dark:text-slate-200 whitespace-nowrap text-sm relative">
                        <!-- YouTube-style Tree line connectors -->
                        <div class="absolute left-0 top-0 bottom-0 w-[58px] pointer-events-none tree-lines-container">
                            @if($isLastChild)
                                <!-- Vertical line that stops halfway and curves to the right -->
                                <div class="absolute left-[32px] top-0 h-[22px] w-[18px] border-l-2 border-b-2 border-slate-200/60 dark:border-slate-800/80 rounded-bl-xl"></div>
                            @else
                                <!-- Full height vertical line with a branch to the right -->
                                <div class="absolute left-[32px] top-0 bottom-0 w-[1.5px] bg-slate-200/60 dark:bg-slate-800/80"></div>
                                <div class="absolute left-[32px] top-[20px] w-[18px] h-[2px] bg-slate-200/60 dark:bg-slate-800/80"></div>
                            @endif
                        </div>

                        <div class="flex items-center gap-3">
                            @if($child->{$imageKey})
                                <img src="{{ Storage::url($child->{$imageKey}) }}" class="w-7 h-7 object-cover rounded-lg border border-slate-200 dark:border-slate-800 bg-white">
                            @else
                                <div class="w-7 h-7 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-lg flex items-center justify-center text-[10px] font-bold border border-slate-200 dark:border-slate-750 select-none">
                                    {{ \App\Helpers\AvatarHelper::getInitials($child->{$nameKey}) }}
                                </div>
                            @endif
                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $child->{$nameKey} }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-2.5 text-xs text-slate-655 dark:text-slate-200 whitespace-nowrap w-48">
                        {{ $item->{$nameKey} }}
                    </td>
                    <td class="px-5 py-2.5 whitespace-nowrap w-32">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold
                            {{ $child->{$statusKey} ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/20' : 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200/20' }}">
                            {{ $child->{$statusKey} ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-5 py-2.5 text-slate-500 dark:text-slate-400 font-mono text-xs whitespace-nowrap sort-order-display w-24">
                        {{ $child->sort_order }}
                    </td>
                    <td class="px-5 py-2.5 text-right whitespace-nowrap w-32">
                        <div class="inline-flex gap-1.5 justify-end">
                            <a href="{{ route($editRoute, $child->{$ulidKey}) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Edit">
                                <i class="fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs"></i>
                            </a>
                            <button onclick="{{ $deleteCallback }}('{{ $child->{$ulidKey} }}', '{{ addslashes($child->{$nameKey}) }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors cursor-pointer" title="Delete">
                                <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif
    @empty
        <tr>
            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                <i class="fa-solid fa-folder-open text-4xl mb-3 opacity-20 block"></i>
                No items found matching the criteria.
            </td>
        </tr>
    @endforelse
</x-data-table>

<script>
    if (typeof window.toggleNestedTableChildren !== 'function') {
        window.toggleNestedTableChildren = function(parentId, tableId) {
            const rows = document.querySelectorAll(`#${tableId} .child-row-of-${parentId}`);
            const icon = document.getElementById(`icon-${parentId}`);
            const btn = document.getElementById(`btn-toggle-${parentId}`);

            if (rows.length === 0) return;

            const isCollapsed = rows[0].classList.contains('hidden');

            rows.forEach(row => {
                if (isCollapsed) {
                    row.classList.remove('hidden');
                    row.classList.add('table-row');
                } else {
                    row.classList.remove('table-row');
                    row.classList.add('hidden');
                }
            });

            if (isCollapsed) {
                if (icon) icon.classList.add('rotate-90');
                if (btn) btn.setAttribute('aria-expanded', 'true');
            } else {
                if (icon) icon.classList.remove('rotate-90');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }
        };
    }

    if (typeof window.rebuildCategoryTreeLines !== 'function') {
        window.rebuildCategoryTreeLines = function(parentId) {
            const childRows = document.querySelectorAll(`tr[data-child-of="${parentId}"]`);
            childRows.forEach((row, idx) => {
                const isLast = (idx === childRows.length - 1);
                const linesContainer = row.querySelector('.tree-lines-container');
                if (linesContainer) {
                    if (isLast) {
                        linesContainer.innerHTML = `
                            <!-- Vertical line that stops halfway and curves to the right -->
                            <div class="absolute left-[32px] top-0 h-[22px] w-[18px] border-l-2 border-b-2 border-slate-200/60 dark:border-slate-800/80 rounded-bl-xl"></div>
                        `;
                    } else {
                        linesContainer.innerHTML = `
                            <!-- Full height vertical line with a branch to the right -->
                            <div class="absolute left-[32px] top-0 bottom-0 w-[1.5px] bg-slate-200/60 dark:bg-slate-800/80"></div>
                            <div class="absolute left-[32px] top-[20px] w-[18px] h-[2px] bg-slate-200/60 dark:bg-slate-800/80"></div>
                        `;
                    }
                }
            });
        };
    }

    if (typeof window.initCategorySortable !== 'function') {
        window.initCategorySortable = function(tableId, reorderUrl) {
            const wrapper = document.getElementById(tableId);
            if (!wrapper) return;
            const tbody = wrapper.querySelector('tbody');
            if (!tbody || tbody.dataset.sortableInitialized) return;

            tbody.dataset.sortableInitialized = 'true';

            if (typeof Sortable === 'undefined') {
                const script = document.createElement('script');
                script.src = "https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js";
                script.onload = () => window.setupNestedSortableInstance(tbody, reorderUrl);
                document.head.appendChild(script);
            } else {
                window.setupNestedSortableInstance(tbody, reorderUrl);
            }
        };
    }

    if (typeof window.setupNestedSortableInstance !== 'function') {
        window.setupNestedSortableInstance = function(tbody, reorderUrl) {
            new Sortable(tbody, {
                handle: '.drag-handle',
                animation: 150,
                draggable: 'tr',
                forceFallback: true,
                dragClass: 'hidden-drag-overlay',
                ghostClass: 'drag-ghost-class',
                chosenClass: 'drag-chosen-class',
                onStart: function(evt) {
                    document.body.classList.add('cursor-grabbing-active');
                    const draggedEl = evt.item;
                    const childOfId = draggedEl.getAttribute('data-child-of');

                    if (!childOfId) {
                        const expandedButtons = document.querySelectorAll('button[aria-expanded="true"]');
                        expandedButtons.forEach(btn => {
                            const parentId = btn.id.replace('btn-toggle-', '');
                            if (typeof window.toggleNestedTableChildren === 'function') {
                                window.toggleNestedTableChildren(parentId, tbody.closest('[id]').id);
                            }
                        });
                    }
                },
                onMove: function(evt) {
                    const dragged = evt.dragged;
                    const related = evt.related;

                    if (dragged.hasAttribute('data-child-of')) {
                        return related.getAttribute('data-child-of') === dragged.getAttribute('data-child-of');
                    }
                    return related.hasAttribute('data-parent-row');
                },
                onChange: function(evt) {
                    const draggedEl = evt.item;
                    const childOfId = draggedEl.getAttribute('data-child-of');

                    if (childOfId) {
                        const childRows = document.querySelectorAll(`tr[data-child-of="${childOfId}"]`);
                        childRows.forEach((row, idx) => {
                            const orderDisplay = row.querySelector('.sort-order-display');
                            if (orderDisplay) {
                                orderDisplay.innerText = idx;
                            }
                        });
                        window.rebuildCategoryTreeLines(childOfId);
                    } else {
                        const parentRows = document.querySelectorAll('tr[data-parent-row]');
                        parentRows.forEach((row, idx) => {
                            const orderDisplay = row.querySelector('.sort-order-display');
                            if (orderDisplay) {
                                orderDisplay.innerText = idx;
                            }
                        });
                    }
                },
                onEnd: function(evt) {
                    document.body.classList.remove('cursor-grabbing-active');
                    const draggedEl = evt.item;
                    const childOfId = draggedEl.getAttribute('data-child-of');

                    let ids = [];

                    if (childOfId) {
                        const childRows = document.querySelectorAll(`tr[data-child-of="${childOfId}"]`);
                        const parentRow = document.querySelector(`tr[data-parent-row="${childOfId}"]`);
                        let lastNode = parentRow;
                        childRows.forEach(childRow => {
                            lastNode.after(childRow);
                            lastNode = childRow;
                            if (parentRow.querySelector('button[aria-expanded="true"]')) {
                                childRow.classList.remove('hidden');
                                childRow.classList.add('table-row');
                            }
                        });

                        childRows.forEach((row, idx) => {
                            ids.push(row.getAttribute('data-id'));
                            const orderDisplay = row.querySelector('.sort-order-display');
                            if (orderDisplay) {
                                orderDisplay.innerText = idx;
                            }
                        });
                        window.rebuildCategoryTreeLines(childOfId);
                    } else {
                        const parentIdAttr = draggedEl.getAttribute('data-parent-row');
                        const childRows = document.querySelectorAll(`tr[data-child-of="${parentIdAttr}"]`);
                        let lastNode = draggedEl;
                        childRows.forEach(childRow => {
                            lastNode.after(childRow);
                            lastNode = childRow;
                            if (draggedEl.querySelector('button[aria-expanded="true"]')) {
                                childRow.classList.remove('hidden');
                                childRow.classList.add('table-row');
                            }
                        });

                        const parentRows = document.querySelectorAll('tr[data-parent-row]');
                        parentRows.forEach((row, idx) => {
                            ids.push(row.getAttribute('data-id'));
                            const orderDisplay = row.querySelector('.sort-order-display');
                            if (orderDisplay) {
                                orderDisplay.innerText = idx;
                            }
                        });
                    }

                    fetch(reorderUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ order: ids })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && typeof window.showToast === 'function') {
                            window.showToast(data.message, 'success');
                        }
                    })
                    .catch(err => console.error('Error reordering items:', err));
                }
            });
        };
    }

    // Set up MutationObserver to re-initialize on AJAX page load
    (function() {
        const tableId = "{{ $tableId }}";
        const reorderUrl = "{{ route($reorderRoute) }}";

        const observer = new MutationObserver(() => {
            window.initCategorySortable(tableId, reorderUrl);
        });

        const wrapper = document.getElementById(tableId);
        if (wrapper) {
            observer.observe(wrapper, { childList: true, subtree: true });
        }

        window.initCategorySortable(tableId, reorderUrl);
    })();
</script>

<style>
    .hidden-drag-overlay {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        width: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
    }

    .drag-chosen-class {
        background-color: rgb(239 246 255 / 0.7) !important;
    }
    .dark .drag-chosen-class {
        background-color: rgb(30 41 59 / 0.5) !important;
    }

    .drag-ghost-class {
        opacity: 0.4;
        background-color: rgb(219 234 254 / 0.5) !important;
        border: 2px dashed #3b82f6 !important;
    }

    .cursor-grabbing-active,
    .cursor-grabbing-active * {
        cursor: grabbing !important;
    }
</style>
