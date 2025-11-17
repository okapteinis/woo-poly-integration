# WooCommerce 9.3+ Compatibility Implementation Roadmap

**Date:** 2025-11-17
**Current Status:** ❌ **NOT COMPATIBLE** with WooCommerce 9.0+
**Target:** Full WooCommerce 9.3+ compatibility

---

## 🎯 COMPATIBILITY STATUS

| Feature | Status | Priority | Estimated Effort |
|---------|--------|----------|------------------|
| HPOS (Custom Order Tables) | ❌ Missing | 🔥 Critical | 5-7 days |
| WooCommerce Blocks | ❌ Missing | 🔥 Critical | 3-5 days |
| REST API v3 | ❌ Missing | ⚠️ High | 2-3 days |
| Block Themes (FSE) | ❌ Missing | ⚠️ Medium | 2-3 days |
| Order Migration Tool | ❌ Missing | ⚠️ High | 2-3 days |

---

## 1. HPOS (High-Performance Order Storage) Implementation

### Overview
WooCommerce 9.0+ uses Custom Order Tables instead of posts for orders. This plugin currently breaks completely on HPOS-enabled stores.

### Current Problem
- `src/Hyyan/WPI/Order.php` assumes all orders are posts
- Uses `pll_set_post_language()` which doesn't work with HPOS
- No detection of HPOS vs legacy mode

### Implementation Plan

#### Phase 1: Detection & Compatibility Layer (Day 1-2)

**Create:** `src/Hyyan/WPI/Compatibility/HPOSAdapter.php`

```php
<?php
namespace Hyyan\WPI\Compatibility;

use Automattic\WooCommerce\Utilities\OrderUtil;

class HPOSAdapter
{
    /**
     * Check if HPOS is enabled
     */
    public static function is_hpos_enabled(): bool
    {
        if (!class_exists('\Automattic\WooCommerce\Utilities\OrderUtil')) {
            return false;
        }
        return OrderUtil::custom_orders_table_usage_is_enabled();
    }

    /**
     * Get order language (works with both HPOS and legacy)
     */
    public static function get_order_language($order_id): ?string
    {
        if (self::is_hpos_enabled()) {
            $order = wc_get_order($order_id);
            if ($order) {
                return $order->get_meta('_order_language', true);
            }
        } else {
            return pll_get_post_language($order_id);
        }
        return null;
    }

    /**
     * Set order language (works with both HPOS and legacy)
     */
    public static function set_order_language($order_id, string $language): bool
    {
        if (self::is_hpos_enabled()) {
            $order = wc_get_order($order_id);
            if ($order) {
                $order->update_meta_data('_order_language', $language);
                $order->save();
                return true;
            }
        } else {
            pll_set_post_language($order_id, $language);
            return true;
        }
        return false;
    }

    /**
     * Query orders by language (works with both HPOS and legacy)
     */
    public static function get_orders_by_language(array $args, string $language): array
    {
        if (self::is_hpos_enabled()) {
            $args['meta_query'] = array(
                array(
                    'key' => '_order_language',
                    'value' => $language,
                )
            );
        } else {
            $args['lang'] = $language;
        }
        return wc_get_orders($args);
    }
}
```

#### Phase 2: Update Order.php (Day 2-3)

**Modify:** `src/Hyyan/WPI/Order.php`

Key changes:
```php
use Hyyan\WPI\Compatibility\HPOSAdapter;

public function saveOrderLanguage($order)
{
    $current = pll_current_language();
    if ($current) {
        HPOSAdapter::set_order_language($order, $current);
    }
}

public static function getOrderLangauge($ID)
{
    return HPOSAdapter::get_order_language($ID);
}
```

#### Phase 3: Update Reports (Day 3-4)

**Modify:** `src/Hyyan/WPI/Reports.php`

Update all order queries to use HPOS-compatible methods.

#### Phase 4: Testing (Day 4-5)

Test matrix:
- [ ] Legacy orders (HPOS disabled)
- [ ] HPOS orders (HPOS enabled)
- [ ] Mixed environment (migration in progress)
- [ ] Order language on checkout
- [ ] Order language in admin
- [ ] Reports filtering by language
- [ ] My Account orders page

---

## 2. WooCommerce Blocks Support

### Overview
Modern WooCommerce uses Block Editor for product displays, cart, and checkout. Plugin needs to hook into block rendering.

### Implementation Plan

#### Phase 1: Product Blocks (Day 1-2)

**Create:** `src/Hyyan/WPI/Blocks/ProductBlocks.php`

```php
<?php
namespace Hyyan\WPI\Blocks;

class ProductBlocks
{
    public function __construct()
    {
        // Filter product queries in blocks
        add_filter(
            'woocommerce_blocks_product_grid_query_args',
            array($this, 'filterProductQuery')
        );

        // Filter product collection block
        add_filter(
            'woocommerce_blocks_product_query_args',
            array($this, 'filterProductQuery')
        );
    }

    /**
     * Filter products by current language
     */
    public function filterProductQuery(array $args): array
    {
        $current_lang = pll_current_language();
        if ($current_lang) {
            $args['lang'] = $current_lang;
        }
        return $args;
    }
}
```

#### Phase 2: Cart & Checkout Blocks (Day 2-3)

**Create:** `src/Hyyan/WPI/Blocks/CartCheckoutBlocks.php`

```php
<?php
namespace Hyyan\WPI\Blocks;

use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\StoreApi;

class CartCheckoutBlocks
{
    public function __construct()
    {
        // Hook into Store API
        add_action('woocommerce_blocks_loaded', array($this, 'registerStoreApi'));

        // Translate product names in cart
        add_filter(
            'woocommerce_cart_item_name',
            array($this, 'translateCartItemName'),
            10, 3
        );
    }

    public function registerStoreApi()
    {
        if (!function_exists('woocommerce_store_api_register_endpoint_data')) {
            return;
        }

        woocommerce_store_api_register_endpoint_data([
            'endpoint' => 'cart-item',
            'namespace' => 'woo-poly-integration',
            'data_callback' => array($this, 'addLanguageToCartItem'),
            'schema_callback' => array($this, 'extendCartItemSchema'),
        ]);
    }

    public function addLanguageToCartItem(array $cart_item): array
    {
        return [
            'language' => pll_current_language(),
        ];
    }

    public function extendCartItemSchema(): array
    {
        return [
            'language' => [
                'description' => __('Current language', 'woo-poly-integration'),
                'type' => 'string',
                'readonly' => true,
            ],
        ];
    }

    public function translateCartItemName($name, $cart_item, $cart_item_key)
    {
        if (isset($cart_item['product_id'])) {
            $product = wc_get_product($cart_item['product_id']);
            if ($product) {
                $translated = \Hyyan\WPI\Utilities::getProductTranslationByObject($product);
                if ($translated) {
                    return $translated->get_name();
                }
            }
        }
        return $name;
    }
}
```

#### Phase 3: Main Blocks Class (Day 3)

**Create:** `src/Hyyan/WPI/Blocks.php`

```php
<?php
namespace Hyyan\WPI;

use Hyyan\WPI\Blocks\ProductBlocks;
use Hyyan\WPI\Blocks\CartCheckoutBlocks;

class Blocks
{
    public function __construct()
    {
        // Only initialize if WooCommerce Blocks is active
        if (!$this->is_blocks_active()) {
            return;
        }

        new ProductBlocks();
        new CartCheckoutBlocks();
    }

    private function is_blocks_active(): bool
    {
        return class_exists('\Automattic\WooCommerce\Blocks\Package');
    }
}
```

#### Phase 4: Register in Plugin (Day 3)

**Modify:** `src/Hyyan/WPI/Plugin.php`

Add to `registerCore()` method:
```php
new Blocks();
```

---

## 3. REST API v3 Integration

### Implementation Plan

**Create:** `src/Hyyan/WPI/RestAPI.php`

```php
<?php
namespace Hyyan\WPI;

class RestAPI
{
    public function __construct()
    {
        // Add language parameter to product queries
        add_filter('woocommerce_rest_product_query', array($this, 'filterProducts'), 10, 2);

        // Add language to product response
        add_filter('woocommerce_rest_prepare_product_object', array($this, 'addLanguageToProduct'), 10, 3);

        // Support language header
        add_filter('rest_pre_dispatch', array($this, 'parseLanguageHeader'), 10, 3);
    }

    public function parseLanguageHeader($result, $server, $request)
    {
        $language = $request->get_header('X-WC-Language');
        if ($language) {
            // Set current language for this request
            // Note: Polylang API may need adaptation for REST context
            if (function_exists('PLL')) {
                PLL()->curlang = PLL()->model->get_language($language);
            }
        }
        return $result;
    }

    public function filterProducts($args, $request)
    {
        $lang = $request->get_param('lang');
        if ($lang) {
            $args['lang'] = sanitize_text_field($lang);
        }
        return $args;
    }

    public function addLanguageToProduct($response, $object, $request)
    {
        $language = pll_get_post_language($object->get_id());
        $response->data['language'] = $language;

        // Add translations
        $translations = pll_get_post_translations($object->get_id());
        $response->data['translations'] = $translations;

        return $response;
    }
}
```

---

## 4. Order Migration Tool

### Purpose
Migrate order language data from legacy post meta to HPOS meta when stores upgrade.

**Create:** `src/Hyyan/WPI/Tools/OrderMigration.php`

```php
<?php
namespace Hyyan\WPI\Tools;

use Hyyan\WPI\Compatibility\HPOSAdapter;

class OrderMigration
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'addMenuItem'));
    }

    public function addMenuItem()
    {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        add_submenu_page(
            'woocommerce',
            __('WooPoly Order Migration', 'woo-poly-integration'),
            __('WooPoly Migration', 'woo-poly-integration'),
            'manage_woocommerce',
            'wpi-order-migration',
            array($this, 'renderPage')
        );
    }

    public function renderPage()
    {
        if (!HPOSAdapter::is_hpos_enabled()) {
            echo '<div class="notice notice-info"><p>';
            echo __('HPOS is not enabled. No migration needed.', 'woo-poly-integration');
            echo '</p></div>';
            return;
        }

        if (isset($_POST['start_migration']) && check_admin_referer('wpi_migration')) {
            $this->runMigration();
        }

        include __DIR__ . '/../Views/Tools/migration.php';
    }

    private function runMigration()
    {
        global $wpdb;

        // Find all orders with language in post meta (legacy)
        $orders = $wpdb->get_results("
            SELECT p.ID, pm.meta_value as language
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND pm.meta_key = '_order_language'
        ");

        $migrated = 0;
        foreach ($orders as $order_data) {
            $order = wc_get_order($order_data->ID);
            if ($order && HPOSAdapter::is_hpos_enabled()) {
                // Set in HPOS meta
                $order->update_meta_data('_order_language', $order_data->language);
                $order->save();
                $migrated++;
            }
        }

        echo '<div class="notice notice-success"><p>';
        printf(__('Migrated %d orders successfully!', 'woo-poly-integration'), $migrated);
        echo '</p></div>';
    }
}
```

---

## 5. Testing Checklist

### HPOS Testing
- [ ] Orders created with HPOS have correct language
- [ ] Orders display in correct language in admin
- [ ] Reports filter orders by language correctly
- [ ] My Account shows orders from all languages
- [ ] Legacy orders still work
- [ ] Migration tool works correctly

### Blocks Testing
- [ ] Product blocks filter by language
- [ ] Product Collection blocks show correct language
- [ ] Cart block translates product names
- [ ] Checkout block uses correct language
- [ ] Mini cart widget works
- [ ] Store API returns language data

### REST API Testing
- [ ] `GET /wp-json/wc/v3/products?lang=en` filters correctly
- [ ] `X-WC-Language` header works
- [ ] Product response includes language
- [ ] Product response includes translations
- [ ] Orders API respects language
- [ ] Categories/tags APIs work with language

---

## 6. Implementation Timeline

### Week 1: HPOS Implementation
- Day 1-2: HPOSAdapter class
- Day 3-4: Update Order.php and Reports.php
- Day 5: Testing and bug fixes

### Week 2: Blocks Implementation
- Day 1-2: Product blocks
- Day 2-3: Cart/Checkout blocks
- Day 4: Integration and testing
- Day 5: Bug fixes

### Week 3: REST API & Migration
- Day 1-2: REST API implementation
- Day 3: Order migration tool
- Day 4-5: End-to-end testing

### Week 4: QA and Release
- Day 1-3: Comprehensive testing
- Day 4: Documentation
- Day 5: Release preparation

---

## 7. Backward Compatibility

Ensure the plugin works with:
- ✅ WooCommerce 5.0+ (legacy orders)
- ✅ WooCommerce 9.0+ (HPOS)
- ✅ Classic themes
- ✅ Block themes
- ✅ Classic checkout
- ✅ Block-based checkout

Use feature detection, not version checking:
```php
if (HPOSAdapter::is_hpos_enabled()) {
    // Use HPOS methods
} else {
    // Use legacy methods
}
```

---

## 8. Dependencies to Add

Update `composer.json`:
```json
{
    "require": {
        "php": ">=7.4",
        "composer/installers": "~2.0",
        "woocommerce/woocommerce": ">=9.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.6",
        "mockery/mockery": "^1.5"
    }
}
```

---

## 9. Documentation to Create

- [ ] HPOS compatibility guide
- [ ] Blocks usage guide
- [ ] REST API documentation
- [ ] Migration guide for store owners
- [ ] Developer hooks documentation

---

## 10. Success Metrics

Plugin will be considered WooCommerce 9.3+ compatible when:
- ✅ All tests pass on WooCommerce 9.3+ with HPOS enabled
- ✅ Works with block-based themes
- ✅ Works with block-based checkout
- ✅ REST API fully supports language filtering
- ✅ Migration tool successfully migrates orders
- ✅ No PHP errors or warnings
- ✅ Performance is acceptable (no N+1 queries)

---

**Total Estimated Effort:** 3-4 weeks (15-20 days)
**Resources Needed:** 1-2 experienced WordPress/WooCommerce developers

**Priority:** 🔥 **CRITICAL** - Without this, plugin cannot be used on modern WooCommerce.
