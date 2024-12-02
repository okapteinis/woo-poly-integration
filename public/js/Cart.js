/**
 * Modified WooCommerce cart-fragments.js script to break HTML5 fragment caching.
 * Useful when switching languages. Adds support for new Cart page ajax.
 * 
 * Updated in line with WooCommerce 3.6.3 cart-fragments.js
 */
jQuery(function($) {
    // wc_cart_fragments_params is required to continue, ensure the object exists
    if (typeof wc_cart_fragments_params === 'undefined') {
        return false;
    }

    // Storage Handling
    const $supports_html5_storage = (() => {
        try {
            const storage = window.sessionStorage;
            storage.setItem('wc', 'test');
            storage.removeItem('wc');
            return true;
        } catch (err) {
            return false;
        }
    })();

    const cart_hash_key = wc_cart_fragments_params.cart_hash_key;

    /**
     * Set cart creation timestamp
     */
    const setCartCreationTimestamp = () => {
        if ($supports_html5_storage) {
            sessionStorage.setItem('wc_cart_created', Date.now());
        }
    };

    /**
     * Set the cart hash in both session and local storage
     */
    const setCartHash = (cart_hash) => {
        if ($supports_html5_storage) {
            localStorage.setItem(cart_hash_key, cart_hash);
            sessionStorage.setItem(cart_hash_key, cart_hash);
        }
    };

    /**
     * Get current Polylang language
     */
    const getPllLanguage = () => {
        return $.cookie('pll_language') || '';
    };

    const fragmentRefresh = {
        url: wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
        type: 'POST',
        data: {
            time: Date.now()
        },
        timeout: wc_cart_fragments_params.request_timeout,
        success: function(data) {
            if (!data || !data.fragments) {
                return;
            }

            $.each(data.fragments, function(key, value) {
                $(key).replaceWith(value);
            });

            if ($supports_html5_storage) {
                sessionStorage.setItem(wc_cart_fragments_params.fragment_name, JSON.stringify(data.fragments));
                setCartHash(data.cart_hash);

                if (data.cart_hash) {
                    setCartCreationTimestamp();
                }
            }

            $(document.body).trigger('wc_fragments_refreshed');
        },
        error: function() {
            $(document.body).trigger('wc_fragments_ajax_error');
        }
    };

    // Rest of the code...
});
