@props([
    'id' => 'datePicker',
    'name' => 'date_range',
    'value' => null,
    'placeholder' => 'Filter by date range…',
    'mode' => 'range', // single, multiple, range
    'dateFormat' => 'd-m-Y',
    'allowInput' => 'true',
    'enableTime' => 'false',
    'time_24hr' => 'false',
    'quickFilters' => true,
    'width' => 'w-48',
])
<style>
    .flatpickr-calendar .flatpickr-current-month .flatpickr-monthDropdown-months,
    .flatpickr-calendar .flatpickr-current-month .flatpickr-yearDropdown-years,
    .flatpickr-calendar .flatpickr-current-month .flatpickr-monthDropdown-custom {
        font-weight: 700 !important;
        font-size: 11px !important;
        padding: 2px 6px !important;
        border-radius: 4px !important;
        border: 1px solid #cbd5e1 !important;
        background: #f1f5f9 !important;
        color: #1e293b !important;
        cursor: pointer !important;
        outline: none !important;
        transition: all 0.15s ease-in-out !important;
        height: 24px !important;
        line-height: normal !important;
        display: inline-block !important;
        margin: 0 2px !important;
        vertical-align: middle !important;
    }
    .dark .flatpickr-calendar .flatpickr-current-month .flatpickr-monthDropdown-months,
    .dark .flatpickr-calendar .flatpickr-current-month .flatpickr-yearDropdown-years,
    .dark .flatpickr-calendar .flatpickr-current-month .flatpickr-monthDropdown-custom {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }
    .flatpickr-calendar .flatpickr-current-month .flatpickr-monthDropdown-months:hover,
    .flatpickr-calendar .flatpickr-current-month .flatpickr-yearDropdown-years:hover,
    .flatpickr-calendar .flatpickr-current-month .flatpickr-monthDropdown-custom:hover {
        background: #e2e8f0 !important;
        border-color: #cbd5e1 !important;
    }
    .dark .flatpickr-calendar .flatpickr-current-month .flatpickr-monthDropdown-months:hover,
    .dark .flatpickr-calendar .flatpickr-current-month .flatpickr-yearDropdown-years:hover,
    .dark .flatpickr-calendar .flatpickr-current-month .flatpickr-monthDropdown-custom:hover {
        background: #334155 !important;
        border-color: #475569 !important;
    }
    .flatpickr-monthDropdown-months .flatpickr-monthDropdown-month {
        background-color: #ffffff !important;
        color: #0f172a !important;
    }
    .dark .flatpickr-monthDropdown-months .flatpickr-monthDropdown-month {
        background-color: #0f172a !important;
        color: #f8fafc !important;
    }
</style>

<!-- Date Picker Input Wrapper -->
<div class="relative flex items-center {{ $width }} flex-shrink-0 date-picker-wrapper">
    <i class="fa-solid fa-calendar absolute left-3 text-slate-400 text-xs pointer-events-none"></i>
    <input type="text" id="{{ $id }}" name="{{ $name }}" value="{{ $value ?? request($name) }}"
        class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 pl-8 pr-8 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100 cursor-pointer"
        placeholder="{{ $placeholder }}"
        autocomplete="off">
    <button type="button" id="{{ $id }}_clear" 
        class="absolute right-2.5 flex items-center justify-center w-5 h-5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700/50 text-slate-400 hover:text-slate-650 dark:hover:text-slate-300 border-none bg-transparent cursor-pointer transition-colors {{ ( $value ?? request($name) ) ? '' : 'hidden' }}"
        title="Clear Date Filter">
        <i class="fa-solid fa-xmark text-xs"></i>
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const inputEl = document.getElementById('{{ $id }}');
        const clearBtn = document.getElementById('{{ $id }}_clear');
        if (!inputEl) return;

        let initialDates = [];
        let applied = false;

        if (clearBtn) {
            clearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const fp = inputEl._flatpickr;
                if (fp) {
                    applied = true; // prevent revert in onClose
                    fp.clear(); // clears dates and triggers onChange
                    
                    const form = fp.element.form;
                    if (form) {
                        submitDatePickerForm(form);
                    }
                }
            });
        }

        function syncSidebarActiveState(selectedDates, sidebar, instance) {
            sidebar.querySelectorAll('.flatpickr-sidebar-btn').forEach(b => b.classList.remove('active'));
            
            if (selectedDates.length === 2) {
                const start = selectedDates[0];
                const end = selectedDates[1];
                const today = new Date();
                today.setHours(0,0,0,0);
                
                const isSameDay = (d1, d2) => 
                    d1.getFullYear() === d2.getFullYear() &&
                    d1.getMonth() === d2.getMonth() &&
                    d1.getDate() === d2.getDate();
                    
                let activeKey = 'custom';
                
                const checkToday = new Date();
                const checkYesterday = new Date();
                checkYesterday.setDate(today.getDate() - 1);
                const checkLast7 = new Date();
                checkLast7.setDate(today.getDate() - 6);
                const checkLast30 = new Date();
                checkLast30.setDate(today.getDate() - 29);
                const checkThisMonthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                const checkLastMonthStart = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const checkLastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
                
                if (isSameDay(start, today) && isSameDay(end, today)) {
                    activeKey = 'today';
                } else if (isSameDay(start, checkYesterday) && isSameDay(end, checkYesterday)) {
                    activeKey = 'yesterday';
                } else if (isSameDay(start, checkLast7) && isSameDay(end, today)) {
                    activeKey = 'last_7_days';
                } else if (isSameDay(start, checkLast30) && isSameDay(end, today)) {
                    activeKey = 'last_30_days';
                } else if (isSameDay(start, checkThisMonthStart) && isSameDay(end, today)) {
                    activeKey = 'this_month';
                } else if (isSameDay(start, checkLastMonthStart) && isSameDay(end, checkLastMonthEnd)) {
                    activeKey = 'last_month';
                }
                
                const activeBtn = sidebar.querySelector(`[data-preset="${activeKey}"]`);
                if (activeBtn) {
                    activeBtn.classList.add('active');
                }
            } else if (selectedDates.length === 1) {
                const customBtn = sidebar.querySelector('[data-preset="custom"]');
                if (customBtn) customBtn.classList.add('active');
            }
        }

        function syncCustomYearSelects(selectedDates, dateStr, instance) {
            const currentYearWrappers = instance.calendarContainer.querySelectorAll('.flatpickr-current-month');
            currentYearWrappers.forEach(container => {
                const nativeYearInput = container.querySelector('.numInput.cur-year');
                if (!nativeYearInput) return;
                
                const nativeWrapper = nativeYearInput.parentNode;
                if (nativeWrapper) nativeWrapper.style.display = 'none';
                
                let yearSelect = container.querySelector('.flatpickr-yearDropdown-years');
                const currentVal = parseInt(nativeYearInput.value, 10) || new Date().getFullYear();
                
                if (!yearSelect) {
                    yearSelect = document.createElement('select');
                    yearSelect.className = 'flatpickr-monthDropdown-months flatpickr-yearDropdown-years';
                    
                    const currentYear = new Date().getFullYear();
                    const startYear = currentYear - 80;
                    const endYear = currentYear + 20;
                    for (let y = startYear; y <= endYear; y++) {
                        const opt = document.createElement('option');
                        opt.value = y;
                        opt.textContent = y;
                        yearSelect.appendChild(opt);
                    }
                    
                    yearSelect.addEventListener('change', (e) => {
                        instance.changeYear(parseInt(e.target.value, 10));
                    });
                    
                    container.appendChild(yearSelect);
                }
                
                // Ensure currentVal option exists
                let opt = yearSelect.querySelector(`option[value="${currentVal}"]`);
                if (!opt) {
                    opt = document.createElement('option');
                    opt.value = currentVal;
                    opt.textContent = currentVal;
                    yearSelect.appendChild(opt);
                    // Sort options
                    Array.from(yearSelect.options)
                        .sort((a, b) => parseInt(a.value, 10) - parseInt(b.value, 10))
                        .forEach(o => yearSelect.appendChild(o));
                }
                
                yearSelect.value = currentVal;
            });
        }

        function syncCustomMonthSelects(selectedDates, dateStr, instance) {
            const currentMonthWrappers = instance.calendarContainer.querySelectorAll('.flatpickr-current-month');
            currentMonthWrappers.forEach((container, index) => {
                const nativeMonthSpan = container.querySelector('span.cur-month');
                if (!nativeMonthSpan) return;
                
                nativeMonthSpan.style.display = 'none';
                
                let monthSelect = container.querySelector('.flatpickr-monthDropdown-custom');
                const currentMonthText = nativeMonthSpan.textContent.trim();
                const monthsList = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                const currentMonthIndex = monthsList.indexOf(currentMonthText);
                
                if (!monthSelect) {
                    monthSelect = document.createElement('select');
                    monthSelect.className = 'flatpickr-monthDropdown-months flatpickr-monthDropdown-custom';
                    
                    monthsList.forEach((m, i) => {
                        const opt = document.createElement('option');
                        opt.value = i;
                        opt.textContent = m;
                        monthSelect.appendChild(opt);
                    });
                    
                    monthSelect.addEventListener('change', (e) => {
                        let targetMonth = parseInt(e.target.value, 10);
                        if (index === 1) {
                            targetMonth = targetMonth - 1;
                        }
                        instance.changeMonth(targetMonth);
                    });
                    
                    container.insertBefore(monthSelect, nativeMonthSpan);
                }
                
                if (currentMonthIndex !== -1) {
                    monthSelect.value = currentMonthIndex;
                }
            });
        }

        function syncHeaderDropdowns(selectedDates, dateStr, instance) {
            syncCustomYearSelects(selectedDates, dateStr, instance);
            syncCustomMonthSelects(selectedDates, dateStr, instance);
        }

        function submitDatePickerForm(form) {
            if (!form) return;
            
            // Dispatch a submit event first to see if any AJAX handler prevents default
            const event = new Event('submit', { cancelable: true });
            form.dispatchEvent(event);
            
            if (event.defaultPrevented) {
                // If it was prevented (AJAX intercepted it), we don't show full-page loader.
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                }
            } else {
                // If it was NOT prevented, it's a standard page reload form!
                if (typeof window.showFullPageLoader === 'function') {
                    window.showFullPageLoader();
                }
                form.submit();
            }
        }

        flatpickr(inputEl, {
            mode: "{{ $mode }}",
            dateFormat: "{{ $dateFormat }}",
            allowInput: {{ $allowInput }},
            enableTime: {{ $enableTime }},
            time_24hr: {{ $time_24hr }},
            showMonths: window.innerWidth < 640 ? 1 : ("{{ $mode }}" === "range" ? 2 : 1),
            closeOnSelect: "{{ $mode }}" !== "range",
            monthSelectorType: "dropdown",
            locale: {
                rangeSeparator: " - "
            },
            onOpen: function(selectedDates, dateStr, instance) {
                initialDates = [...selectedDates];
                applied = false;
                
                const sidebar = instance.calendarContainer.querySelector('.flatpickr-calendar-sidebar');
                if (sidebar) {
                    syncSidebarActiveState(selectedDates, sidebar, instance);
                }
                setTimeout(() => syncHeaderDropdowns(selectedDates, dateStr, instance), 0);
            },
            onMonthChange: function(selectedDates, dateStr, instance) {
                setTimeout(() => syncHeaderDropdowns(selectedDates, dateStr, instance), 0);
            },
            onYearChange: function(selectedDates, dateStr, instance) {
                setTimeout(() => syncHeaderDropdowns(selectedDates, dateStr, instance), 0);
            },
            onReady: function(selectedDates, dateStr, instance) {
                setTimeout(() => syncHeaderDropdowns(null, null, instance), 0);
                const isRange = "{{ $mode }}" === "range";
                const quickFiltersEnabled = {{ $quickFilters ? 'true' : 'false' }};
                
                if (isRange) {
                    if (quickFiltersEnabled) {
                        const sidebar = document.createElement('div');
                        sidebar.className = 'flatpickr-calendar-sidebar';
                        
                        const presets = [
                            { label: 'Today', key: 'today' },
                            { label: 'Yesterday', key: 'yesterday' },
                            { label: 'Last 7 Days', key: 'last_7_days' },
                            { label: 'Last 30 Days', key: 'last_30_days' },
                            { label: 'This Month', key: 'this_month' },
                            { label: 'Last Month', key: 'last_month' },
                            { label: 'Custom Range', key: 'custom' }
                        ];
                        
                        presets.forEach(p => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'flatpickr-sidebar-btn';
                            btn.textContent = p.label;
                            btn.setAttribute('data-preset', p.key);
                            
                            btn.addEventListener('click', (e) => {
                                if (p.key === 'custom') {
                                    sidebar.querySelectorAll('.flatpickr-sidebar-btn').forEach(b => b.classList.remove('active'));
                                    btn.classList.add('active');
                                    return;
                                }
                                
                                const today = new Date();
                                let start = new Date();
                                let end = new Date();
                                
                                if (p.key === 'today') {
                                    start = today;
                                    end = today;
                                } else if (p.key === 'yesterday') {
                                    const yesterday = new Date();
                                    yesterday.setDate(today.getDate() - 1);
                                    start = yesterday;
                                    end = yesterday;
                                } else if (p.key === 'last_7_days') {
                                    const last7 = new Date();
                                    last7.setDate(today.getDate() - 6);
                                    start = last7;
                                    end = today;
                                } else if (p.key === 'last_30_days') {
                                    const last30 = new Date();
                                    last30.setDate(today.getDate() - 29);
                                    start = last30;
                                    end = today;
                                } else if (p.key === 'this_month') {
                                    start = new Date(today.getFullYear(), today.getMonth(), 1);
                                    end = today;
                                } else if (p.key === 'last_month') {
                                    start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                                    end = new Date(today.getFullYear(), today.getMonth(), 0);
                                }
                                
                                applied = true;
                                instance.setDate([start, end], true);
                                instance.close();
                            });
                            
                            sidebar.appendChild(btn);
                        });
                        
                        instance.calendarContainer.classList.add('has-sidebar');
                        const innerContainer = instance.calendarContainer.querySelector('.flatpickr-innerContainer');
                        if (innerContainer) {
                            innerContainer.insertBefore(sidebar, innerContainer.firstChild);
                        }
                    }
                    
                    // Create Footer Panel
                    const footer = document.createElement('div');
                    footer.className = 'flatpickr-calendar-footer';
                    
                    const rangeLabel = document.createElement('span');
                    rangeLabel.className = 'flatpickr-footer-range';
                    rangeLabel.textContent = dateStr || 'Select range...';
                    
                    const actions = document.createElement('div');
                    actions.className = 'flatpickr-footer-actions';
                    
                    const cancelBtn = document.createElement('button');
                    cancelBtn.type = 'button';
                    cancelBtn.className = 'flatpickr-footer-btn-cancel';
                    cancelBtn.textContent = 'Cancel';
                    cancelBtn.addEventListener('click', () => {
                        applied = false;
                        instance.close();
                    });
                    
                    const applyBtn = document.createElement('button');
                    applyBtn.type = 'button';
                    applyBtn.className = 'flatpickr-footer-btn-apply';
                    applyBtn.textContent = 'Apply';
                    applyBtn.addEventListener('click', () => {
                        if (instance.selectedDates.length === 2 || instance.selectedDates.length === 0) {
                            applied = true;
                            instance.close();
                        }
                    });
                    
                    // Initial apply state validation
                    if (selectedDates.length === 1) {
                        applyBtn.disabled = true;
                        applyBtn.style.opacity = 0.5;
                        applyBtn.style.cursor = 'not-allowed';
                    }
                    
                    actions.appendChild(cancelBtn);
                    actions.appendChild(applyBtn);
                    footer.appendChild(rangeLabel);
                    footer.appendChild(actions);
                    
                    // Add modern human-friendly Start & End Time pickers
                    const isRange = "{{ $mode }}" === "range";
                    const is24hr = instance.config.time_24hr;
                    const showTime = {{ $enableTime ? 'true' : 'false' }};

                    if (showTime) {
                        const timeRow = document.createElement('div');
                        timeRow.className = 'flatpickr-custom-time-row';

                        // 1. Start Time Picker
                        const startPicker = document.createElement('div');
                        startPicker.className = 'flatpickr-custom-time-picker';
                        
                        const startLabel = document.createElement('span');
                        startLabel.className = 'flatpickr-custom-time-label';
                        startLabel.innerHTML = `<i class="fa-solid fa-clock mr-1"></i>${isRange ? 'Start:' : 'Time:'}`;
                        
                        const startHourSelect = document.createElement('select');
                        const startMinuteSelect = document.createElement('select');
                        
                        const maxHour = is24hr ? 23 : 12;
                        const minHour = is24hr ? 0 : 1;
                        for (let i = minHour; i <= maxHour; i++) {
                            const opt = document.createElement('option');
                            opt.value = i;
                            opt.textContent = i < 10 ? '0' + i : i;
                            startHourSelect.appendChild(opt);
                        }
                        for (let i = 0; i < 60; i++) {
                            const opt = document.createElement('option');
                            opt.value = i;
                            opt.textContent = i < 10 ? '0' + i : i;
                            startMinuteSelect.appendChild(opt);
                        }
                        
                        let startAmpmSelect = null;
                        if (!is24hr) {
                            startAmpmSelect = document.createElement('select');
                            const optAM = document.createElement('option'); optAM.value = 'AM'; optAM.textContent = 'AM';
                            const optPM = document.createElement('option'); optPM.value = 'PM'; optPM.textContent = 'PM';
                            startAmpmSelect.appendChild(optAM);
                            startAmpmSelect.appendChild(optPM);
                        }

                        const startSeparator = document.createElement('span');
                        startSeparator.className = 'flatpickr-custom-separator';
                        startSeparator.textContent = ':';

                        startPicker.appendChild(startLabel);
                        startPicker.appendChild(startHourSelect);
                        startPicker.appendChild(startSeparator);
                        startPicker.appendChild(startMinuteSelect);
                        if (startAmpmSelect) startPicker.appendChild(startAmpmSelect);
                        timeRow.appendChild(startPicker);

                        // 2. End Time Picker
                        let endHourSelect = null;
                        let endMinuteSelect = null;
                        let endAmpmSelect = null;

                        if (isRange) {
                            const endPicker = document.createElement('div');
                            endPicker.className = 'flatpickr-custom-time-picker';
                            
                            const endLabel = document.createElement('span');
                            endLabel.className = 'flatpickr-custom-time-label';
                            endLabel.innerHTML = '<i class="fa-solid fa-clock mr-1"></i>End:';
                            
                            endHourSelect = document.createElement('select');
                            endMinuteSelect = document.createElement('select');
                            
                            for (let i = minHour; i <= maxHour; i++) {
                                const opt = document.createElement('option');
                                opt.value = i;
                                opt.textContent = i < 10 ? '0' + i : i;
                                endHourSelect.appendChild(opt);
                            }
                            for (let i = 0; i < 60; i++) {
                                const opt = document.createElement('option');
                                opt.value = i;
                                opt.textContent = i < 10 ? '0' + i : i;
                                endMinuteSelect.appendChild(opt);
                            }
                            
                            if (!is24hr) {
                                endAmpmSelect = document.createElement('select');
                                const optAM = document.createElement('option'); optAM.value = 'AM'; optAM.textContent = 'AM';
                                const optPM = document.createElement('option'); optPM.value = 'PM'; optPM.textContent = 'PM';
                                endAmpmSelect.appendChild(optAM);
                                endAmpmSelect.appendChild(optPM);
                            }

                            const endSeparator = document.createElement('span');
                            endSeparator.className = 'flatpickr-custom-separator';
                            endSeparator.textContent = ':';

                            endPicker.appendChild(endLabel);
                            endPicker.appendChild(endHourSelect);
                            endPicker.appendChild(endSeparator);
                            endPicker.appendChild(endMinuteSelect);
                            if (endAmpmSelect) endPicker.appendChild(endAmpmSelect);
                            timeRow.appendChild(endPicker);
                        }

                        function applyCustomTimes() {
                            if (instance.selectedDates.length >= 1) {
                                const startHour = parseInt(startHourSelect.value, 10);
                                const startMinute = parseInt(startMinuteSelect.value, 10);
                                let finalStartHour = startHour;
                                if (startAmpmSelect) {
                                    if (startAmpmSelect.value === 'PM' && startHour < 12) finalStartHour += 12;
                                    if (startAmpmSelect.value === 'AM' && startHour === 12) finalStartHour = 0;
                                }
                                instance.selectedDates[0].setHours(finalStartHour, startMinute, 0, 0);
                            }
                            if (instance.selectedDates.length === 2 && endHourSelect && endMinuteSelect) {
                                const endHour = parseInt(endHourSelect.value, 10);
                                const endMinute = parseInt(endMinuteSelect.value, 10);
                                let finalEndHour = endHour;
                                if (endAmpmSelect) {
                                    if (endAmpmSelect.value === 'PM' && endHour < 12) finalEndHour += 12;
                                    if (endAmpmSelect.value === 'AM' && endHour === 12) finalEndHour = 0;
                                }
                                instance.selectedDates[1].setHours(finalEndHour, endMinute, 0, 0);
                            }
                            
                            const formatted = instance.selectedDates.map(d => instance.formatDate(d, instance.config.dateFormat)).join(instance.config.locale.rangeSeparator || " - ");
                            instance.input.value = formatted;
                            
                            const footerLabel = instance.calendarContainer.querySelector('.flatpickr-footer-range');
                            if (footerLabel) {
                                footerLabel.textContent = formatted || 'Select range...';
                            }
                        }

                        // Set Initial values
                        let initStartHr = 12;
                        let initStartMin = 0;
                        let initStartAmPm = 'AM';
                        let initEndHr = 12;
                        let initEndMin = 0;
                        let initEndAmPm = 'AM';

                        if (instance.selectedDates[0]) {
                            const hr = instance.selectedDates[0].getHours();
                            initStartMin = instance.selectedDates[0].getMinutes();
                            if (is24hr) {
                                initStartHr = hr;
                            } else {
                                initStartHr = hr % 12 || 12;
                                initStartAmPm = hr >= 12 ? 'PM' : 'AM';
                            }
                        }
                        if (instance.selectedDates[1]) {
                            const hr = instance.selectedDates[1].getHours();
                            initEndMin = instance.selectedDates[1].getMinutes();
                            if (is24hr) {
                                initEndHr = hr;
                            } else {
                                initEndHr = hr % 12 || 12;
                                initEndAmPm = hr >= 12 ? 'PM' : 'AM';
                            }
                        }

                        startHourSelect.value = initStartHr;
                        startMinuteSelect.value = initStartMin;
                        if (startAmpmSelect) startAmpmSelect.value = initStartAmPm;

                        if (endHourSelect && endMinuteSelect) {
                            endHourSelect.value = initEndHr;
                            endMinuteSelect.value = initEndMin;
                            if (endAmpmSelect) endAmpmSelect.value = initEndAmPm;
                        }

                        // Event Listeners
                        startHourSelect.addEventListener('change', applyCustomTimes);
                        startMinuteSelect.addEventListener('change', applyCustomTimes);
                        if (startAmpmSelect) startAmpmSelect.addEventListener('change', applyCustomTimes); // AM/PM update listener

                        if (endHourSelect && endMinuteSelect) {
                            endHourSelect.addEventListener('change', applyCustomTimes);
                            endMinuteSelect.addEventListener('change', applyCustomTimes);
                            if (endAmpmSelect) endAmpmSelect.addEventListener('change', applyCustomTimes);
                        }

                        // Insert Time Row in Calendar wrapper
                        instance.calendarContainer.appendChild(timeRow);

                        // Listen for onChange to update selects
                        instance.config.onChange.push((selectedDates) => {
                            if (selectedDates[0]) {
                                const hr = selectedDates[0].getHours();
                                startMinuteSelect.value = selectedDates[0].getMinutes();
                                if (is24hr) {
                                    startHourSelect.value = hr;
                                } else {
                                    startHourSelect.value = hr % 12 || 12;
                                    if (startAmpmSelect) startAmpmSelect.value = hr >= 12 ? 'PM' : 'AM';
                                }
                            }
                            if (selectedDates[1] && endHourSelect && endMinuteSelect) {
                                const hr = selectedDates[1].getHours();
                                endMinuteSelect.value = selectedDates[1].getMinutes();
                                if (is24hr) {
                                    endHourSelect.value = hr;
                                } else {
                                    endHourSelect.value = hr % 12 || 12;
                                    if (endAmpmSelect) endAmpmSelect.value = hr >= 12 ? 'PM' : 'AM';
                                }
                            }
                            applyCustomTimes();
                        });
                    }

                    // Append footer last so it sits at the absolute bottom
                    instance.calendarContainer.appendChild(footer);
                }
            },
            onClose: function(selectedDates, dateStr, instance) {
                const isRange = "{{ $mode }}" === "range";
                const isSingle = "{{ $mode }}" === "single";
                
                if (isRange) {
                    if (!applied) {
                        // Revert to initial dates
                        instance.setDate(initialDates, false);
                    } else {
                        const form = instance.element.form;
                        if (form) {
                            submitDatePickerForm(form);
                        }
                    }
                } else if (isSingle && selectedDates.length <= 1) {
                    const form = instance.element.form;
                    if (form) {
                        submitDatePickerForm(form);
                    }
                }
            },
            onChange: function(selectedDates, dateStr, instance) {
                // Update range label in footer
                const footerLabel = instance.calendarContainer.querySelector('.flatpickr-footer-range');
                if (footerLabel) {
                    footerLabel.textContent = dateStr || 'Select range...';
                }
                
                // Update Apply button availability
                const applyBtn = instance.calendarContainer.querySelector('.flatpickr-footer-btn-apply');
                if (applyBtn) {
                    if (selectedDates.length === 1) {
                        applyBtn.disabled = true;
                        applyBtn.style.opacity = 0.5;
                        applyBtn.style.cursor = 'not-allowed';
                    } else {
                        applyBtn.disabled = false;
                        applyBtn.style.opacity = 1;
                        applyBtn.style.cursor = 'pointer';
                    }
                }
                
                // Update active state in presets sidebar
                const sidebar = instance.calendarContainer.querySelector('.flatpickr-calendar-sidebar');
                if (sidebar) {
                    syncSidebarActiveState(selectedDates, sidebar, instance);
                }

                if (clearBtn) {
                    if (selectedDates.length > 0) {
                        clearBtn.classList.remove('hidden');
                    } else {
                        clearBtn.classList.add('hidden');
                    }
                }
                setTimeout(() => syncHeaderDropdowns(selectedDates, dateStr, instance), 0);
            }
        });
    });
</script>
