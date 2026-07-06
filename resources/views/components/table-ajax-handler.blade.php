@props([
    'tableId',
    'searchInputId' => null,
    'totalCountId' => null,
    'clearBtnWrapperId' => null,
    'refreshBtnId' => null,
    'clearBtnId' => null,
])

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tableWrapper = document.getElementById('{{ $tableId }}');
        const searchInput = @json($searchInputId) ? document.getElementById(@json($searchInputId)) : null;
        const form = searchInput ? searchInput.form : (tableWrapper ? tableWrapper.closest('.relative')?.querySelector('form[action]') || tableWrapper.parentNode.querySelector('form[action]') : null);
        const refreshBtn = @json($refreshBtnId) ? document.getElementById(@json($refreshBtnId)) : null;
        const clearBtnId = @json($clearBtnId);
        const inlineClearBtn = document.getElementById('clearSearchInputBtn');

        function updateTable(url, push = true) {
            if (!tableWrapper) return;
            
            const overlay = document.getElementById('{{ $tableId }}_overlay') || document.getElementById(tableWrapper.id.replace('TableWrapper', '') + 'TableWrapper_overlay');
            const progress = document.getElementById('{{ $tableId }}_progress') || document.getElementById(tableWrapper.id.replace('TableWrapper', '') + 'TableWrapper_progress');
            
            if (overlay) overlay.classList.remove('hidden');
            if (progress) progress.classList.remove('hidden');
            
            // Add visual transition (dim and blur table)
            tableWrapper.style.transition = 'all 0.2s ease-in-out';
            tableWrapper.style.opacity = '0.4';
            tableWrapper.style.filter = 'blur(0.2px)';
            tableWrapper.style.pointerEvents = 'none';

            // Spin the refresh button icon if present
            let refreshIcon = null;
            if (refreshBtn) {
                refreshIcon = refreshBtn.querySelector('i');
                if (refreshIcon) refreshIcon.classList.add('fa-spin');
            }

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Swap table wrapper
                const newTableWrapper = doc.getElementById('{{ $tableId }}');
                if (newTableWrapper) {
                    tableWrapper.innerHTML = newTableWrapper.innerHTML;
                }

                // Swap total count
                const totalId = @json($totalCountId);
                if (totalId) {
                    const newTotalCount = doc.getElementById(totalId);
                    const currentTotalCount = document.getElementById(totalId);
                    if (newTotalCount && currentTotalCount) {
                        currentTotalCount.innerHTML = newTotalCount.innerHTML;
                    }
                }

                // Swap clear button
                const clearWrapperId = @json($clearBtnWrapperId);
                if (clearWrapperId) {
                    const newClearBtnWrapper = doc.getElementById(clearWrapperId);
                    const currentClearBtnWrapper = document.getElementById(clearWrapperId);
                    if (newClearBtnWrapper && currentClearBtnWrapper) {
                        currentClearBtnWrapper.innerHTML = newClearBtnWrapper.innerHTML;
                    }
                }

                // Update history
                if (push) {
                    history.pushState(null, '', url);
                }
            })
            .catch(err => console.error('AJAX load failed:', err))
            .finally(() => {
                // Restore visual state
                const overlay = document.getElementById('{{ $tableId }}_overlay') || document.getElementById(tableWrapper.id.replace('TableWrapper', '') + 'TableWrapper_overlay');
                const progress = document.getElementById('{{ $tableId }}_progress') || document.getElementById(tableWrapper.id.replace('TableWrapper', '') + 'TableWrapper_progress');
                
                if (overlay) overlay.classList.add('hidden');
                if (progress) progress.classList.add('hidden');
                
                tableWrapper.style.opacity = '1';
                tableWrapper.style.filter = 'none';
                tableWrapper.style.pointerEvents = 'auto';
                if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            });
        }

        // Intercept form submissions
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                const params = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value.trim() !== '') {
                        params.append(key, value);
                    }
                }
                const queryString = params.toString();
                const url = queryString ? `${form.action}?${queryString}` : form.action;
                updateTable(url);
            });

            // Intercept select dropdown changes and date input changes
            form.addEventListener('change', (e) => {
                if (e.target.tagName === 'SELECT' || (e.target.tagName === 'INPUT' && e.target.type === 'date')) {
                    form.dispatchEvent(new Event('submit'));
                }
            });
        }

        // Debounced search typing with inline clear button handling
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                if (inlineClearBtn) {
                    if (searchInput.value.length > 0) {
                        inlineClearBtn.classList.remove('hidden');
                    } else {
                        inlineClearBtn.classList.add('hidden');
                    }
                }
            });

            let debounceTimer;
            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    if (form) form.dispatchEvent(new Event('submit'));
                }, 500);
            });

            if (inlineClearBtn) {
                inlineClearBtn.addEventListener('click', () => {
                    searchInput.value = '';
                    inlineClearBtn.classList.add('hidden');
                    if (form) form.dispatchEvent(new Event('submit'));
                });
            }

            // Focus on input and place cursor at the end of the text if it was searched
            if (searchInput.value.length > 0) {
                const val = searchInput.value;
                searchInput.value = '';
                searchInput.focus();
                searchInput.value = val;
            }
        }

        // Intercept pagination clicks and column header sorting via event delegation
        if (tableWrapper) {
            tableWrapper.addEventListener('click', (e) => {
                const sortingOrPaginationLink = e.target.closest('thead a, nav a');
                if (sortingOrPaginationLink) {
                    e.preventDefault();
                    updateTable(sortingOrPaginationLink.href);
                }
            });
        }

        // Intercept Clear Button clicks
        document.addEventListener('click', (e) => {
            if (clearBtnId) {
                const clearBtn = e.target.closest('#' + clearBtnId);
                if (clearBtn) {
                    e.preventDefault();
                    
                    // If page-level query filters are active, do a full reload to reset all components
                    const params = new URLSearchParams(window.location.search);
                    const hasPageFilters = params.has('date_range') || params.has('start_date') || params.has('end_date');
                    if (hasPageFilters) {
                        if (typeof window.showFullPageLoader === 'function') {
                            window.showFullPageLoader();
                        }
                        window.location.href = clearBtn.href;
                        return;
                    }

                    if (form) {
                        form.reset();
                        const inputs = form.querySelectorAll('input, select');
                        inputs.forEach(input => {
                            if (input._flatpickr) input._flatpickr.clear();
                            else if (input.type === 'text') input.value = '';
                            else if (input.type === 'date') input.value = '';
                            else if (input.tagName === 'SELECT') input.selectedIndex = 0;
                        });
                    }
                    if (inlineClearBtn) inlineClearBtn.classList.add('hidden');
                    updateTable(clearBtn.href);
                }
            }
        });

        // Intercept Refresh Button clicks
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                updateTable(window.location.href);
            });
        }

        // Handle browser back/forward buttons
        window.addEventListener('popstate', () => {
            updateTable(window.location.href, false);
            
            // Sync form inputs with new URL parameters
            if (form) {
                const params = new URLSearchParams(window.location.search);
                const search = params.get('search') || '';

                if (searchInput) {
                    searchInput.value = search;
                    if (inlineClearBtn) {
                        if (search.length > 0) inlineClearBtn.classList.remove('hidden');
                        else inlineClearBtn.classList.add('hidden');
                    }
                }

                // Sync other filters (select dropdowns)
                const selects = form.querySelectorAll('select');
                selects.forEach(select => {
                    const val = params.get(select.name) || '';
                    select.value = val;
                });

                // Sync text inputs (including Flatpickr) and date inputs
                const textAndDateInputs = form.querySelectorAll('input');
                textAndDateInputs.forEach(input => {
                    if (input._flatpickr) {
                        const val = params.get(input.name) || '';
                        input._flatpickr.setDate(val, false);
                    } else if (input.type === 'date') {
                        const val = params.get(input.name) || '';
                        input.value = val;
                    }
                });
            }
        });
    });
</script>
