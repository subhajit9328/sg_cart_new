<script>
(function() {
    const hasColor = {{ config('product-variants.features.color', true) ? 'true' : 'false' }};
    const hasSize = {{ config('product-variants.features.size', true) ? 'true' : 'false' }};

    const originalSelectSize = window.selectSize;
    const originalSelectColor = window.selectColor;

    window.selectSize = function(val, el) {
        if (!hasSize) return;
        if (originalSelectSize) originalSelectSize(val, el);
        checkVariant();
    };

    window.selectColor = function(val, el) {
        if (!hasColor) return;
        if (originalSelectColor) originalSelectColor(val, el);
        checkVariant();
    };

    const originalChangeImage = window.changeImage;
    window.changeImage = function(src, el) {
        if (originalChangeImage) originalChangeImage(src, el);
        currentDisplayedSrc = src;
    };

    function getAbsoluteUrl(url) {
        if (!url) return '';
        const a = document.createElement('a');
        a.href = url;
        return a.href;
    }

    let productVariants = [];
    const productId = "{{ $product['id'] }}";

    @php
        $dbProduct = \App\Models\Product::find($product['id']);
        $variantsJson = [];
        if ($dbProduct) {
            $variantsJson = \SGCart\ProductVariants\Models\ProductVariant::with(['color', 'size', 'images'])
                ->where('product_id', $dbProduct->id)
                ->where('is_active', true)
                ->get()
                ->map(function($v) {
                    $defaultImg = $v->images->firstWhere('is_default', true) ?: $v->images->first();
                    $variantImages = $v->images->sortByDesc('is_default')->map(function($img) {
                        return \Storage::url($img->image_path);
                    })->values()->toArray();
                    return [
                        'id'         => $v->id,
                        'color_hex'  => $v->color?->hex_code,
                        'color_id'   => $v->color_id,
                        'size_code'  => $v->size?->code,
                        'size_id'    => $v->size_id,
                        'sku'        => $v->sku,
                        'price'      => $v->price ? number_format($v->price, 2, '.', '') : null,
                        'sale_price' => $v->sale_price ? number_format($v->sale_price, 2, '.', '') : null,
                        'stock'      => (int) $v->stock,
                        'img'        => $defaultImg ? \Storage::url($defaultImg->image_path) : null,
                        'imgs'       => $variantImages,
                    ];
                })->toArray();
        }
    @endphp

    productVariants = @json($variantsJson);

    // ── Store originals so we can restore them when no variant matches ──
    const mainImg        = document.getElementById('mainProductImg');
    const originalImgSrc = mainImg ? mainImg.src : null;

    // Track what is currently displayed so we can avoid redundant swaps.
    // We use a separate variable because mainImg.src is always a full absolute URL
    // while Storage::url() returns root-relative paths — direct comparison fails.
    let currentDisplayedSrc = originalImgSrc;

    const priceWrapper = document.getElementById('variantPriceWrapper');
    const originalPriceHTML = priceWrapper ? priceWrapper.innerHTML : '';

    const originalSkuEl  = document.querySelector('.spec-content table td.font-mono');
    const originalSkuTxt = originalSkuEl ? originalSkuEl.textContent : null;

    const originalImgs = @json($product['images'] ?? []);
    const thumbsContainer = document.querySelector('.pd-thumbs');
    const originalThumbsHTML = thumbsContainer ? thumbsContainer.innerHTML : '';
    const originalThumbsDisplay = thumbsContainer ? thumbsContainer.style.display : 'none';

    // Helper to update thumbnail strip
    function updateThumbs(imgs) {
        if (!thumbsContainer) return;
        
        if (imgs && imgs.length > 1) {
            let html = '';
            imgs.forEach(function(imgSrc, index) {
                html += `<div class="pd-thumb ${index === 0 ? 'active' : ''}" onclick="changeImage('${imgSrc}', this)">`;
                html += `<img src="${imgSrc}" alt="Thumb"/>`;
                html += `</div>`;
            });
            thumbsContainer.innerHTML = html;
            thumbsContainer.style.display = ''; // Show container
        } else {
            thumbsContainer.innerHTML = '';
            thumbsContainer.style.display = 'none'; // Hide container
        }
    }

    // Helper to filter and select size options based on selected color
    let isUpdatingSize = false;
    function updateSizeAvailability(currentColor) {
        if (!hasSize) return;
        const sizeButtons = document.querySelectorAll('.size-btn');
        if (sizeButtons.length === 0) return;

        // If no color is selected or there are no variants, enable all sizes
        if (!currentColor || productVariants.length === 0) {
            sizeButtons.forEach(function(btn) {
                btn.style.opacity = '';
                btn.style.pointerEvents = '';
            });
            return;
        }

        // Collect sizes available for the selected color
        const availableSizes = [];
        productVariants.forEach(function(v) {
            if (v.color_hex && v.color_hex.toLowerCase() === currentColor.toLowerCase()) {
                if (v.size_code) {
                    availableSizes.push(v.size_code.toUpperCase());
                }
            }
        });

        // Enable/disable size buttons
        sizeButtons.forEach(function(btn) {
            const btnSize = btn.textContent.trim().toUpperCase();
            if (availableSizes.includes(btnSize)) {
                btn.style.opacity = '';
                btn.style.pointerEvents = '';
            } else {
                btn.style.opacity = '0.2';
                btn.style.pointerEvents = 'none';
            }
        });

        // Check if the currently selected size is disabled.
        // If it is, automatically select the first available size!
        const currentSizeInput = document.getElementById('sizeInput');
        const currentSize = currentSizeInput ? currentSizeInput.value : '';
        if (currentSize && !availableSizes.includes(currentSize.toUpperCase())) {
            if (availableSizes.length > 0) {
                const firstAvailable = availableSizes[0];
                let targetBtn = null;
                sizeButtons.forEach(function(btn) {
                    if (btn.textContent.trim().toUpperCase() === firstAvailable) {
                        targetBtn = btn;
                    }
                });
                if (targetBtn) {
                    isUpdatingSize = true;
                    selectSize(firstAvailable, targetBtn);
                    isUpdatingSize = false;
                }
            }
        }
    }

    // ── Smooth image swap helper ──
    function swapMainImage(newSrc) {
        const absoluteNewSrc = getAbsoluteUrl(newSrc);
        const absoluteCurrentSrc = getAbsoluteUrl(currentDisplayedSrc);
        if (!mainImg || !newSrc || absoluteCurrentSrc === absoluteNewSrc) return;
        currentDisplayedSrc = newSrc;
        mainImg.style.transition = 'opacity 0.2s ease';
        mainImg.style.opacity    = '0';
        setTimeout(function() {
            mainImg.src           = absoluteNewSrc;
            mainImg.style.opacity = '1';
        }, 200);

        // Update active class on thumbnails to match new image
        document.querySelectorAll('.pd-thumb').forEach(function(t) {
            const img = t.querySelector('img');
            if (img && getAbsoluteUrl(img.getAttribute('src')) === absoluteNewSrc) {
                t.classList.add('active');
            } else {
                t.classList.remove('active');
            }
        });
    }

    // Initial check on page load
    if (productVariants.length > 0) {
        setTimeout(checkVariant, 100);
    }

    function checkVariant() {
        if (productVariants.length === 0) return;

        const currentColor = (hasColor && document.getElementById('colorInput')) ? document.getElementById('colorInput').value : null;
        
        // Update size options availability based on selected color
        if (hasSize) {
            updateSizeAvailability(currentColor);
        }
        
        const currentSize  = (hasSize && document.getElementById('sizeInput'))  ? document.getElementById('sizeInput').value  : null;

        // Try exact match first
        let match = productVariants.find(function(v) {
            const colorMatch = !hasColor || (!currentColor && !v.color_hex) ||
                (currentColor && v.color_hex && currentColor.toLowerCase() === v.color_hex.toLowerCase());
            const sizeMatch  = !hasSize || (!currentSize  && !v.size_code)  ||
                (currentSize  && v.size_code  && currentSize.toUpperCase()  === v.size_code.toUpperCase());
            return colorMatch && sizeMatch;
        });

        // Fallback: partial match (color only or size only)
        if (!match) {
            match = productVariants.find(function(v) {
                const colorMatch = hasColor && currentColor && v.color_hex && currentColor.toLowerCase() === v.color_hex.toLowerCase();
                const sizeMatch  = hasSize && currentSize  && v.size_code  && currentSize.toUpperCase()  === v.size_code.toUpperCase();
                return colorMatch || sizeMatch;
            });
        }

        if (match) {
            // ── Images ──
            if (match.imgs && match.imgs.length > 0) {
                updateThumbs(match.imgs);
                swapMainImage(match.img || match.imgs[0] || originalImgSrc);
            } else {
                updateThumbs(originalImgs);
                swapMainImage(match.img || originalImgSrc);
            }

            // ── Price ──
            if (priceWrapper) {
                const sellingPrice = match.sale_price || match.price;
                const regularPrice = match.sale_price ? match.price : null;
                
                if (sellingPrice) {
                    const formattedSelling = parseFloat(sellingPrice).toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    
                    if (regularPrice) {
                        const formattedRegular = parseFloat(regularPrice).toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        const savings = Math.round((1 - parseFloat(sellingPrice) / parseFloat(regularPrice)) * 100);
                        
                        priceWrapper.innerHTML = `
                            <span class="font-display font-extrabold text-2xl text-slate-900">₹${formattedSelling}</span>
                            <span class="text-lg text-slate-400 line-through">₹${formattedRegular}</span>
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Save ${savings}%</span>
                        `;
                    } else {
                        priceWrapper.innerHTML = `
                            <span class="font-display font-extrabold text-2xl text-slate-900">₹${formattedSelling}</span>
                        `;
                    }
                } else {
                    priceWrapper.innerHTML = originalPriceHTML;
                }
            }

            // ── SKU ──
            if (match.sku && originalSkuEl) {
                originalSkuEl.textContent = match.sku;
            } else if (!match.sku && originalSkuEl && originalSkuTxt) {
                originalSkuEl.textContent = originalSkuTxt;
            }

            // ── Stock / Add to Bag button ──
            const addToBagBtn = document.querySelector('#purchaseForm button[type="submit"]');
            if (addToBagBtn) {
                if (match.stock <= 0) {
                    addToBagBtn.disabled = true;
                    addToBagBtn.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Out of Stock';
                    addToBagBtn.style.cssText = 'background-color:#94a3b8;cursor:not-allowed;';
                } else {
                    addToBagBtn.disabled  = false;
                    addToBagBtn.innerHTML = '<i class="fa-solid fa-bag-shopping"></i> Add To Cart';
                    addToBagBtn.style.cssText = '';
                }
            }
        } else {
            // ── No variant matched — restore all originals ──
            swapMainImage(originalImgSrc);
            if (thumbsContainer) {
                thumbsContainer.innerHTML = originalThumbsHTML;
                thumbsContainer.style.display = originalThumbsDisplay;
            }

            if (priceWrapper) {
                priceWrapper.innerHTML = originalPriceHTML;
            }
            if (originalSkuEl && originalSkuTxt) {
                originalSkuEl.textContent = originalSkuTxt;
            }

            const addToBagBtn = document.querySelector('#purchaseForm button[type="submit"]');
            if (addToBagBtn) {
                addToBagBtn.disabled  = false;
                addToBagBtn.innerHTML = '<i class="fa-solid fa-bag-shopping"></i> Add To Cart';
                addToBagBtn.style.cssText = '';
            }
        }
    }
})();
</script>
