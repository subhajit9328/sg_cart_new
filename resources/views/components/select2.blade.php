@props([
    'id' => null,
    'name',
    'label' => null,
    'placeholder' => 'Select an option',
    'allowClear' => true,
    'multiple' => false,
    'searchable' => true,
    'required' => false,
    'options' => null,
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'selected' => null,
    'ajaxUrl' => null,
    'dropdownParent' => null,
    'tags' => false,
    'width' => '100%',
    'compact' => false,
])

@php
    $id = $id ?? 'select2_' . str_replace(['[', ']', ' '], ['_', '', '_'], $name) . '_' . uniqid();
    
    // Normalize selected value(s) to a simple array of strings for accurate comparison
    if ($selected instanceof \Illuminate\Support\Collection) {
        $selectedValues = $selected->toArray();
    } elseif (is_object($selected) && method_exists($selected, 'toArray')) {
        $selectedValues = $selected->toArray();
    } else {
        $selectedValues = (array)$selected;
    }
    
    $selectedValues = array_map('strval', $selectedValues);
@endphp

<div class="w-full select2-wrapper {{ $compact ? 'select2-wrapper-compact' : '' }}">
    @if($label)
        <label for="{{ $id }}" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">
            {{ $label }}
            @if($required)
                <span class="text-rose-600">*</span>
            @endif
        </label>
    @endif

    <select 
        id="{{ $id }}" 
        name="{{ $name }}{{ $multiple ? '[]' : '' }}" 
        class="w-full select2-select {{ $attributes->get('class') }}"
        {{ $multiple ? 'multiple' : '' }}
        {{ $required ? 'required' : '' }}
        {{ $attributes->except(['class', 'id', 'name', 'multiple', 'required']) }}
    >
        @if($placeholder && !$multiple)
            <option value="">{{ $placeholder }}</option>
        @endif

        @if($options)
            @foreach($options as $key => $option)
                @php
                    if (is_object($option)) {
                        $val = (string)($option->{$optionValue} ?? $option->id ?? $key);
                        $lbl = $option->{$optionLabel} ?? $option->name ?? $option->title ?? $option->label ?? $val;
                    } elseif (is_array($option)) {
                        $val = (string)($option[$optionValue] ?? $option['id'] ?? $key);
                        $lbl = $option[$optionLabel] ?? $option['name'] ?? $option['title'] ?? $option['label'] ?? $val;
                    } else {
                        // Support associative or simple index arrays
                        $val = (string)(is_numeric($key) ? $option : $key);
                        $lbl = $option;
                    }
                    
                    $isSelected = in_array($val, $selectedValues, true);
                @endphp
                <option value="{{ $val }}" {{ $isSelected ? 'selected' : '' }}>
                    {{ $lbl }}
                </option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        const select2Options = {
            allowClear: {{ $allowClear ? 'true' : 'false' }},
            width: "{{ $width }}",
            tags: {{ $tags ? 'true' : 'false' }},
        };

        @if($placeholder && $allowClear)
            select2Options.placeholder = "{{ $placeholder }}";
        @endif

        // Disable search bar if requested
        @if(!$searchable)
            select2Options.minimumResultsForSearch = -1;
        @endif

        @if($compact)
            select2Options.dropdownCssClass = "select2-compact-dropdown";
        @endif

        // Handle parent element (important for Select2 in Bootstrap/Tailwind modals)
        @if($dropdownParent)
            select2Options.dropdownParent = $("{{ $dropdownParent }}");
        @endif

        // AJAX configuration for dynamic/remote search options
        @if($ajaxUrl)
            select2Options.ajax = {
                url: "{{ $ajaxUrl }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search keyword
                        page: params.page || 1
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    const items = data.items || data.data || data;
                    
                    return {
                        results: $.map(items, function(item) {
                            return {
                                id: item.{{ $optionValue }} || item.id,
                                text: item.{{ $optionLabel }} || item.name || item.text
                            };
                        }),
                        pagination: {
                            more: data.more || (data.next_page_url ? true : false)
                        }
                    };
                },
                cache: true
            };
        @endif

        // Initialize Select2 on the select element
        const $select = $('#{{ $id }}').select2(select2Options);

        // Keep internal Laravel/DOM state synced when value changes and trigger native DOM change for AJAX reloading
        $select.on('select2:select select2:unselect select2:clear', function() {
            this.dispatchEvent(new Event('change', { bubbles: true }));
        });

        // Sync with form resets to update the Select2 UI display
        const formElement = document.getElementById('{{ $id }}').closest('form');
        if (formElement) {
            formElement.addEventListener('reset', function() {
                setTimeout(() => {
                    $select.val($select.prop('multiple') ? null : '').trigger('change.select2');
                }, 10);
            });
        }
    });
</script>
@endpush
