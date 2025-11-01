#!/bin/bash

# GitHub Issues Creator for WooCommerce Compatibility Gaps
# Hyyan WooCommerce Polylang Integration
#
# Usage:
#   1. Create a GitHub Personal Access Token with 'repo' scope at:
#      https://github.com/settings/tokens
#   2. Export it: export GITHUB_TOKEN="your_token_here"
#   3. Run: ./create-github-issues.sh

set -e

# Configuration
REPO_OWNER="okapteinis"
REPO_NAME="woo-poly-integration"
API_URL="https://api.github.com/repos/${REPO_OWNER}/${REPO_NAME}/issues"

# Check for GitHub token
if [ -z "$GITHUB_TOKEN" ]; then
    echo "Error: GITHUB_TOKEN environment variable not set"
    echo ""
    echo "Please create a GitHub Personal Access Token with 'repo' scope at:"
    echo "https://github.com/settings/tokens"
    echo ""
    echo "Then run:"
    echo "export GITHUB_TOKEN=\"your_token_here\""
    echo "./create-github-issues.sh"
    exit 1
fi

echo "Creating GitHub issues for WooCommerce compatibility gaps..."
echo "Repository: ${REPO_OWNER}/${REPO_NAME}"
echo ""

# Function to create an issue
create_issue() {
    local title="$1"
    local body="$2"
    local labels="$3"

    echo "Creating issue: $title"

    # Escape body for JSON
    body_json=$(echo "$body" | jq -Rs .)

    # Create JSON payload
    json_payload=$(cat <<EOF
{
  "title": "$title",
  "body": $body_json,
  "labels": $labels
}
EOF
)

    # Create the issue
    response=$(curl -s -X POST \
        -H "Authorization: token $GITHUB_TOKEN" \
        -H "Accept: application/vnd.github.v3+json" \
        -d "$json_payload" \
        "$API_URL")

    # Extract issue number
    issue_number=$(echo "$response" | jq -r '.number')

    if [ "$issue_number" != "null" ] && [ -n "$issue_number" ]; then
        echo "✓ Created issue #$issue_number"
        echo "  https://github.com/${REPO_OWNER}/${REPO_NAME}/issues/${issue_number}"
    else
        echo "✗ Failed to create issue"
        echo "Response: $response"
    fi
    echo ""
}

# Issue 1: HPOS Support
create_issue \
    "[CRITICAL] Add High-Performance Order Storage (HPOS) Support" \
    "## Problem

The plugin currently does not support WooCommerce's High-Performance Order Storage (HPOS), which has been the **default for all new WooCommerce 8.2+ installations** since October 2023.

## Impact

**Severity:** 🔴 CRITICAL

When HPOS is enabled (default in WooCommerce 8.2+):
- ❌ Orders have no language metadata
- ❌ Cannot filter orders by language in admin
- ❌ My Account page doesn't show correct language orders
- ❌ Email language detection fails
- ❌ Reports don't respect language filtering

## Current Behavior

The plugin stores order language using \`pll_set_post_language(\$order, \$current)\` which only works with WordPress posts table (legacy mode). HPOS uses custom tables (\`wc_orders\`, \`wc_order_addresses\`, etc.) where this metadata is not stored.

## Expected Behavior

Orders should have proper language association regardless of whether HPOS is enabled or disabled.

## Technical Details

### Files Affected
- \`src/Hyyan/WPI/Order.php\` - Lines 32-34, 80-86
- \`src/Hyyan/WPI/Reports.php\` - Order queries
- \`src/Hyyan/WPI/Emails.php\` - Order language detection

### Required Implementation

1. **Detect HPOS mode:**
\`\`\`php
public static function is_hpos_enabled() {
    if (!class_exists('\\\\Automattic\\\\WooCommerce\\\\Utilities\\\\OrderUtil')) {
        return false;
    }
    return \\\\Automattic\\\\WooCommerce\\\\Utilities\\\\OrderUtil::custom_orders_table_usage_is_enabled();
}
\`\`\`

2. **Store order language for both HPOS and legacy:**
\`\`\`php
public function saveOrderLanguage(\$order) {
    \$current = pll_current_language();
    if (!\$current) return;

    if (is_numeric(\$order)) {
        \$order = wc_get_order(\$order);
    }

    if (!\$order) return;

    if (Utilities::is_hpos_enabled()) {
        // HPOS: Store as order meta
        \$order->update_meta_data('_order_language', \$current);
        \$order->save();
    } else {
        // Legacy: Store as post language
        pll_set_post_language(\$order->get_id(), \$current);
    }
}
\`\`\`

3. **Update order queries to support both modes**
4. **Add language retrieval method**
5. **Update admin order filtering**

## Testing Requirements

- [ ] Test with HPOS enabled
- [ ] Test with HPOS disabled (legacy mode)
- [ ] Test order creation in multiple languages
- [ ] Verify My Account orders filter correctly
- [ ] Test email language selection
- [ ] Verify admin order filtering
- [ ] Test migration from legacy to HPOS

## References

- [WooCommerce HPOS Documentation](https://developer.woocommerce.com/docs/features/high-performance-order-storage/)
- Gap Analysis: \`WOOCOMMERCE_COMPATIBILITY_GAP_ANALYSIS.md\` - Section 1

## Development Estimate

**Time:** 3-5 days
**Priority:** CRITICAL - Must implement for WooCommerce 8.2+ compatibility

## Compatibility Notice

Until this is implemented, users should disable HPOS in WooCommerce settings:
- Navigate to: WooCommerce > Settings > Advanced > Features
- Set \"Order data storage\" to \"WordPress posts storage (legacy)\"

---

Related to v1.7.0 roadmap" \
    '["enhancement", "priority: critical", "woocommerce", "compatibility"]'

# Issue 2: Block Editor Support
create_issue \
    "[CRITICAL] Add WooCommerce Block Editor Support" \
    "## Problem

The plugin does not support WooCommerce Blocks (Cart Block, Checkout Block, Product Blocks), which have been the **default for all new WooCommerce 8.3+ installations** since November 2023.

## Impact

**Severity:** 🔴 CRITICAL

When using WooCommerce Blocks:
- ❌ Cart Block shows products from all languages
- ❌ Checkout Block is not language-filtered
- ❌ Product Query Blocks display mixed language products
- ❌ Product Collection Blocks don't filter by language
- ❌ Site Editor templates are not language-aware

## Current Behavior

The plugin only supports classic shortcode-based pages:
- \`[woocommerce_cart]\`
- \`[woocommerce_checkout]\`
- Classic product loops

Block-based pages introduced in WooCommerce 8.3+ are not filtered by language.

## Expected Behavior

All WooCommerce blocks should respect the current language and only display content in that language.

## Technical Details

### Files Affected
- **NEW:** \`src/Hyyan/WPI/Blocks.php\` (to be created)
- \`src/Hyyan/WPI/Pages.php\` - Extend for block support
- \`src/Hyyan/WPI/Product/Product.php\` - Add block filters

### Required Implementation

#### 1. Create Blocks Integration Class
\`\`\`php
// src/Hyyan/WPI/Blocks.php
class Blocks {
    public function __construct() {
        // Filter product collection blocks
        add_filter('woocommerce_blocks_product_query_args',
            array(\$this, 'filter_product_blocks'), 10, 2);

        // Filter cart block
        add_filter('render_block_woocommerce/cart',
            array(\$this, 'filter_cart_block'), 10, 2);

        // Filter checkout block
        add_filter('render_block_woocommerce/checkout',
            array(\$this, 'filter_checkout_block'), 10, 2);

        // Register block strings for translation
        add_action('init', array(\$this, 'register_block_strings'));
    }

    public function filter_product_blocks(\$args, \$request) {
        \$current_lang = pll_current_language();

        if (!\$current_lang) {
            return \$args;
        }

        // Add language filter to tax_query
        if (!isset(\$args['tax_query'])) {
            \$args['tax_query'] = array('relation' => 'AND');
        }

        \$args['tax_query'][] = array(
            'taxonomy' => 'language',
            'field' => 'slug',
            'terms' => \$current_lang,
        );

        return \$args;
    }
}
\`\`\`

#### 2. Product Collection Block Support
\`\`\`php
add_filter('woocommerce_product_collection_query_args', function(\$args) {
    \$current_lang = pll_current_language();

    if (!isset(\$args['tax_query'])) {
        \$args['tax_query'] = array('relation' => 'AND');
    }

    \$args['tax_query'][] = array(
        'taxonomy' => 'language',
        'field' => 'slug',
        'terms' => \$current_lang,
    );

    return \$args;
});
\`\`\`

#### 3. Register Block Strings for Translation
\`\`\`php
public function register_block_strings() {
    if (!function_exists('pll_register_string')) {
        return;
    }

    \$strings = array(
        'Add to cart',
        'Proceed to checkout',
        'Place order',
        'Your cart is empty',
        'Continue shopping',
        'Order summary',
        'Subtotal',
        'Total',
        // ... more strings
    );

    foreach (\$strings as \$string) {
        pll_register_string(\$string, \$string, 'WooCommerce Blocks');
    }
}
\`\`\`

## Testing Requirements

- [ ] Create Cart page with Cart Block
- [ ] Create Checkout page with Checkout Block
- [ ] Test Product Collection blocks
- [ ] Test All Products block
- [ ] Verify Featured Products block
- [ ] Test with Block theme (Twenty Twenty-Four)
- [ ] Test with Classic theme
- [ ] Verify Site Editor template language switching
- [ ] Test product query filtering
- [ ] Verify block string translations

## Block-Specific Requirements

### Cart Block
- Cart items show products in correct language
- \"Continue shopping\" link uses language shop page
- Empty cart message translated

### Checkout Block
- Billing/shipping forms in correct language
- Payment method names translated
- Order review shows translated products
- Success message translation

### Product Collection Block
- Filter products by current language
- Product titles/descriptions translated
- Category filters respect language
- Pagination with language parameter

## References

- [WooCommerce Blocks Documentation](https://developer.woocommerce.com/docs/category/woocommerce-blocks/)
- [Product Collection Block](https://woocommerce.com/document/woocommerce-store-editing/)
- Gap Analysis: \`WOOCOMMERCE_COMPATIBILITY_GAP_ANALYSIS.md\` - Section 2

## Development Estimate

**Time:** 5-7 days
**Priority:** CRITICAL - Required for WooCommerce 8.3+ default installations

## Compatibility Notice

Until this is implemented, users should:
- Use classic shortcode pages: \`[woocommerce_cart]\`, \`[woocommerce_checkout]\`
- Disable block-based cart/checkout in WooCommerce settings

---

Related to v1.7.0 roadmap" \
    '["enhancement", "priority: critical", "woocommerce", "blocks", "compatibility"]'

# Issue 3: Product Collection Blocks
create_issue \
    "[CRITICAL] Add Product Collection Block Support" \
    "## Problem

Product Collection Blocks (introduced in WooCommerce 9.0, production-ready June 2024) display products from all languages instead of filtering by the current language.

## Impact

**Severity:** 🔴 CRITICAL

When using Product Collection Blocks:
- ❌ Displays products from all languages mixed together
- ❌ Query builder doesn't respect language filtering
- ❌ Custom filters show mixed language results
- ❌ Category/attribute filters include all languages

## Current Behavior

Product Collection Blocks use a new query system that is not integrated with Polylang language filtering.

## Expected Behavior

Product Collection Blocks should only display products in the current language, with all filters respecting language boundaries.

## Technical Details

### Required Implementation

\`\`\`php
// Add to Blocks.php class
add_filter('woocommerce_product_collection_query_args', array(\$this, 'filter_product_collection'), 10, 1);

public function filter_product_collection(\$args) {
    \$current_lang = pll_current_language();

    if (!\$current_lang) {
        return \$args;
    }

    if (!isset(\$args['tax_query'])) {
        \$args['tax_query'] = array('relation' => 'AND');
    }

    \$args['tax_query'][] = array(
        'taxonomy' => 'language',
        'field' => 'slug',
        'terms' => \$current_lang,
    );

    return \$args;
}
\`\`\`

## Testing Requirements

- [ ] Create Product Collection blocks with various queries
- [ ] Test filtering by category (per language)
- [ ] Test custom attribute filters
- [ ] Verify sorting works correctly
- [ ] Check pagination with language parameter
- [ ] Test query builder interface
- [ ] Verify performance with large product catalogs

## References

- [Product Collection Documentation](https://woocommerce.com/document/woocommerce-store-editing/)
- WooCommerce 9.0 Release Notes
- Gap Analysis: \`WOOCOMMERCE_COMPATIBILITY_GAP_ANALYSIS.md\` - Section 3

## Development Estimate

**Time:** 1-2 days (included with Block Editor support)
**Priority:** CRITICAL
**Dependencies:** Issue #2 (Block Editor Support)

---

Related to v1.7.0 roadmap" \
    '["enhancement", "priority: critical", "woocommerce", "blocks", "compatibility"]'

# Issue 4: REST API Integration
create_issue \
    "[HIGH] Add REST API v3 Language Support" \
    "## Problem

The WooCommerce REST API does not respect or expose language information, making it impossible for external integrations (mobile apps, headless commerce, third-party systems) to filter content by language.

## Impact

**Severity:** 🟠 HIGH

REST API issues:
- ❌ Product queries return products from all languages
- ❌ Order responses don't include language information
- ❌ Category/tag queries mix languages
- ❌ Coupon API doesn't respect language
- ❌ No way to specify language in API requests

## Affected Use Cases

- Mobile apps can't filter by language
- Headless WooCommerce setups fail
- Third-party integrations break
- External order management systems confused
- API-driven storefronts show mixed languages

## Current Behavior

\`\`\`bash
# Returns products from ALL languages
GET /wp-json/wc/v3/products

# No language information in response
{
  \"id\": 123,
  \"name\": \"Product Name\",
  // No language field
}
\`\`\`

## Expected Behavior

\`\`\`bash
# Filter products by language
GET /wp-json/wc/v3/products?lang=en

# Or use header
GET /wp-json/wc/v3/products
X-WC-Language: en

# Response includes language info
{
  \"id\": 123,
  \"name\": \"Product Name\",
  \"language\": \"en\",
  \"translations\": {
    \"en\": 123,
    \"fr\": 456
  }
}
\`\`\`

## Technical Details

### Files to Create
- **NEW:** \`src/Hyyan/WPI/RestAPI.php\`

### Required Implementation

\`\`\`php
// src/Hyyan/WPI/RestAPI.php
class RestAPI {
    public function __construct() {
        // Add language parameter to requests
        add_filter('rest_request_before_callbacks',
            array(\$this, 'add_language_to_request'), 10, 3);

        // Filter products by language
        add_filter('woocommerce_rest_product_query',
            array(\$this, 'filter_products_by_language'), 10, 2);

        // Filter orders by language
        add_filter('woocommerce_rest_shop_order_query',
            array(\$this, 'filter_orders_by_language'), 10, 2);

        // Add language to product responses
        add_filter('woocommerce_rest_prepare_product_object',
            array(\$this, 'add_language_to_product_response'), 10, 2);

        // Add language to order responses
        add_filter('woocommerce_rest_prepare_shop_order_object',
            array(\$this, 'add_language_to_order_response'), 10, 2);
    }

    public function add_language_to_request(\$response, \$handler, \$request) {
        // Get language from query parameter or header
        \$lang = \$request->get_param('lang');

        if (!\$lang) {
            \$lang = \$request->get_header('X-WC-Language');
        }

        if (!\$lang) {
            \$lang = pll_default_language();
        }

        \$request->set_param('pll_language', \$lang);
        return \$response;
    }

    public function filter_products_by_language(\$args, \$request) {
        \$lang = \$request->get_param('pll_language');

        if (\$lang) {
            \$args['lang'] = \$lang;
        }

        return \$args;
    }

    public function add_language_to_product_response(\$response, \$product) {
        \$lang = pll_get_post_language(\$product->get_id());
        \$response->data['language'] = \$lang;
        \$response->data['translations'] = pll_get_post_translations(\$product->get_id());
        return \$response;
    }
}
\`\`\`

## Testing Requirements

- [ ] Test product queries with \`lang\` parameter
- [ ] Test with \`X-WC-Language\` header
- [ ] Verify order responses include language
- [ ] Test category/tag filtering by language
- [ ] Test coupon API with language
- [ ] Test authentication with language parameter
- [ ] Verify external integration compatibility
- [ ] Test mobile app scenarios
- [ ] Check performance impact

## API Documentation

### Query Parameters
- \`lang\` - Language code (e.g., \`en\`, \`fr\`, \`de\`)

### Request Headers
- \`X-WC-Language\` - Alternative to query parameter

### Response Format
All resources include:
- \`language\` - Current language code
- \`translations\` - Object mapping language codes to resource IDs

## References

- [WooCommerce REST API Documentation](https://developer.woocommerce.com/docs/category/rest-api/)
- Gap Analysis: \`WOOCOMMERCE_COMPATIBILITY_GAP_ANALYSIS.md\` - Section 4

## Development Estimate

**Time:** 3-4 days
**Priority:** HIGH - Important for modern integrations

---

Related to v1.7.0 roadmap" \
    '["enhancement", "priority: high", "rest-api", "compatibility"]'

# Issue 5: Order Full-Text Search
create_issue \
    "[HIGH] Make Order Full-Text Search Language-Aware" \
    "## Problem

WooCommerce 9.0's Order Full-Text Search (HPOS FTS) returns orders from all languages when searching in admin, making it difficult to find orders in a specific language.

## Impact

**Severity:** 🟠 HIGH

Admin experience issues:
- ❌ Searching for orders returns mixed language results
- ❌ Cannot filter search by language
- ❌ Product names in wrong language appear in results
- ❌ Customer addresses in different languages mixed

## Current Behavior

When admin searches for orders in WooCommerce admin:
- Results include orders from all languages
- No way to filter by admin's current language
- Search results are confusing for multilingual stores

## Expected Behavior

Order search should:
- Respect admin's current language selection
- Only show orders in selected language by default
- Provide option to search across all languages if needed

## Technical Details

### Files to Modify
- \`src/Hyyan/WPI/Order.php\`

### Required Implementation

\`\`\`php
// In Order.php
public function filter_order_search(\$args) {
    if (!is_admin()) {
        return \$args;
    }

    \$screen = get_current_screen();
    if (!\$screen || 'shop_order' !== \$screen->post_type) {
        return \$args;
    }

    // Get admin language preference
    \$admin_lang = pll_current_language();

    if (Utilities::is_hpos_enabled()) {
        // HPOS: Add meta query for FTS
        if (!isset(\$args['meta_query'])) {
            \$args['meta_query'] = array();
        }

        \$args['meta_query'][] = array(
            'key' => '_order_language',
            'value' => \$admin_lang,
            'compare' => '='
        );
    } else {
        // Legacy: Use post language filter
        \$args['lang'] = \$admin_lang;
    }

    return \$args;
}

// Hook into order search
add_filter('woocommerce_shop_order_search_fields',
    array(\$this, 'filter_order_search'));
\`\`\`

## Testing Requirements

- [ ] Enable HPOS FTS: WooCommerce > Settings > Advanced > Features
- [ ] Create orders in multiple languages
- [ ] Search for orders in admin
- [ ] Verify language filtering works
- [ ] Test search by customer name
- [ ] Test search by address
- [ ] Test search by product name
- [ ] Check performance impact
- [ ] Verify with large order volume

## Configuration

To enable HPOS Full-Text Search:
1. Enable HPOS: WooCommerce > Settings > Advanced > Features > Order data storage > HPOS
2. Enable FTS: Experimental features > HPOS Full-text search indexes

## References

- [WooCommerce 9.0 Release Notes](https://developer.woocommerce.com/2024/06/18/woocommerce-9-0-our-most-accessible-checkout-and-much-more/)
- Gap Analysis: \`WOOCOMMERCE_COMPATIBILITY_GAP_ANALYSIS.md\` - Section 5

## Development Estimate

**Time:** 2-3 days
**Priority:** HIGH
**Dependencies:** Issue #1 (HPOS Support)

---

Related to v1.7.0 roadmap" \
    '["enhancement", "priority: high", "admin", "search", "compatibility"]'

# Issue 6: Site Editor Support
create_issue \
    "[MEDIUM] Add Site Editor & Block Theme Support" \
    "## Problem

WordPress Full Site Editing (FSE) and block themes are not fully integrated with Polylang, preventing language-specific template customization in the Site Editor.

## Impact

**Severity:** 🟡 MEDIUM

Site Editor issues:
- ⚠️ Cart/Checkout templates not language-specific
- ⚠️ Header/footer block patterns can't vary by language
- ⚠️ Global styles not customizable per language
- ⚠️ Shop archive templates don't filter by language

## Current Behavior

- Plugin handles page translation
- Site Editor templates are not language-aware
- Block patterns shared across all languages
- Template parts don't support language variations

## Expected Behavior

- Site Editor shows templates for current language
- Ability to customize templates per language
- Block patterns can vary by language
- Template parts respect language context

## Technical Details

### Files to Create
- **NEW:** \`src/Hyyan/WPI/SiteEditor.php\`

### Required Implementation

\`\`\`php
// src/Hyyan/WPI/SiteEditor.php
class SiteEditor {
    public function __construct() {
        // Filter templates by language
        add_filter('get_block_templates',
            array(\$this, 'filter_templates_by_language'), 10, 3);

        // Get translated template
        add_filter('get_block_template',
            array(\$this, 'get_translated_template'), 10, 3);

        // Enable template translation
        add_filter('pll_get_post_types', function(\$types) {
            \$types[] = 'wp_template';
            \$types[] = 'wp_template_part';
            return \$types;
        });
    }

    public function filter_templates_by_language(\$templates, \$query, \$template_type) {
        \$current_lang = pll_current_language();

        foreach (\$templates as \$key => \$template) {
            \$template_lang = \$this->get_template_language(\$template->id);

            if (\$template_lang && \$template_lang !== \$current_lang) {
                unset(\$templates[\$key]);
            }
        }

        return array_values(\$templates);
    }
}
\`\`\`

## Testing Requirements

- [ ] Install block theme (Twenty Twenty-Four)
- [ ] Test Site Editor access
- [ ] Create custom templates
- [ ] Test template language switching
- [ ] Verify block patterns work
- [ ] Test global styles
- [ ] Check template parts
- [ ] Verify FSE navigation
- [ ] Test with WooCommerce templates

## Block Themes to Test
- Twenty Twenty-Four (WordPress default)
- Storefront Blocks (WooCommerce)
- Custom block themes

## Use Cases

1. **Custom Shop Template per Language**
   - Different layouts for different languages
   - Language-specific banners

2. **Header/Footer Variations**
   - Different menu structures
   - Language-specific contact info

3. **Global Styles**
   - Typography preferences per language
   - Color schemes per culture

## References

- [WordPress Site Editor Documentation](https://wordpress.org/documentation/article/site-editor/)
- [Block Themes](https://developer.wordpress.org/themes/block-themes/)
- Gap Analysis: \`WOOCOMMERCE_COMPATIBILITY_GAP_ANALYSIS.md\` - Section 6

## Development Estimate

**Time:** 4-5 days
**Priority:** MEDIUM - Affects FSE users

---

Related to v1.7.0 roadmap" \
    '["enhancement", "priority: medium", "site-editor", "fse", "compatibility"]'

# Issue 7: Modern Checkout
create_issue \
    "[MEDIUM] Optimize for Modern Checkout Block Experience" \
    "## Problem

WooCommerce 9.0 introduced significant Checkout Block improvements that may not have all strings registered for translation or fully optimized for multilingual stores.

## Impact

**Severity:** 🟡 MEDIUM

Modern checkout issues:
- ⚠️ Some UI strings may not be translatable
- ⚠️ Sticky order summary may not be language-aware
- ⚠️ New checkout elements may show in wrong language
- ⚠️ Accessibility improvements need translation verification

## Modern Checkout Features (WooCommerce 9.0+)

- Refreshed UI with better accessibility
- Sticky order summary on desktop
- Improved local pickup display
- Better address form layout
- Free shipping displays as \"FREE\" instead of \"0.00\"
- Enhanced error messaging

## Current Behavior

- Classic checkout fully translated ✅
- Checkout Block partially supported ⚠️
- Modern UI elements may need string registration

## Expected Behavior

All modern checkout features should be fully translatable and language-aware.

## Technical Details

### Files to Modify
- \`src/Hyyan/WPI/Endpoints.php\` or new \`Blocks.php\`

### Required Implementation

\`\`\`php
// Register modern checkout strings
public function register_checkout_block_strings() {
    if (!function_exists('pll_register_string')) {
        return;
    }

    \$checkout_strings = array(
        // Order summary
        'Order summary',
        'Subtotal',
        'Shipping',
        'FREE',
        'Total',
        'Tax',
        'Discount',

        // Pickup
        'Pickup from',
        'Available pickup locations',
        'Select a pickup location',

        // Address
        'Billing address',
        'Shipping address',
        'Same as billing address',
        'Use a different shipping address',

        // Payment
        'Choose a payment method',
        'Payment details',
        'Secure payment',

        // Actions
        'Place order',
        'Return to cart',
        'Processing...',

        // Validation
        'This field is required',
        'Please enter a valid email address',
        'Please select a country',

        // Success
        'Thank you for your order',
        'Your order has been received',
    );

    foreach (\$checkout_strings as \$string) {
        pll_register_string(\$string, \$string, 'WooCommerce Checkout Block');
    }
}
\`\`\`

## Testing Requirements

- [ ] Test checkout block with new UI
- [ ] Verify sticky order summary translates
- [ ] Check local pickup in multiple languages
- [ ] Test address form translations
- [ ] Verify all button texts translate
- [ ] Test error messages in each language
- [ ] Check \"FREE\" shipping translation
- [ ] Verify accessibility features work
- [ ] Test payment method translations
- [ ] Check success messages

## Checkout Features to Verify

### Order Summary
- Sticky behavior on desktop
- All price labels translated
- Currency formatting correct
- Discount display

### Local Pickup
- Location names translated
- Instructions in correct language
- Map integration (if applicable)

### Address Forms
- Field labels translated
- Placeholder text correct
- Validation messages translated
- Country/state dropdowns correct

### Payment Methods
- Method names translated
- Instructions in correct language
- Error messages translated

## References

- [WooCommerce 9.0 Checkout Improvements](https://developer.woocommerce.com/2024/06/18/woocommerce-9-0-our-most-accessible-checkout-and-much-more/)
- [Checkout Block Documentation](https://woocommerce.com/document/woocommerce-store-editing/customizing-cart-and-checkout/checkout-block/)
- Gap Analysis: \`WOOCOMMERCE_COMPATIBILITY_GAP_ANALYSIS.md\` - Section 7

## Development Estimate

**Time:** 2-3 days (if Block Editor already supported)
**Priority:** MEDIUM
**Dependencies:** Issue #2 (Block Editor Support)

---

Related to v1.7.0 roadmap" \
    '["enhancement", "priority: medium", "checkout", "blocks", "i18n"]'

echo ""
echo "================================================"
echo "All GitHub issues created successfully!"
echo "================================================"
echo ""
echo "View all issues at:"
echo "https://github.com/${REPO_OWNER}/${REPO_NAME}/issues"
echo ""
echo "Issues are labeled with:"
echo "  - Priority level (critical, high, medium)"
echo "  - Type (enhancement)"
echo "  - Component (woocommerce, blocks, rest-api, etc.)"
echo ""
echo "All issues are related to v1.7.0 roadmap"
echo ""
