# Testing Checklist for v1.7.0

This document provides a comprehensive testing checklist for the new features added in v1.7.0.

## Test Environment Setup

### Required Setup
- [ ] WordPress 5.4+
- [ ] WooCommerce 7.0+
- [ ] Polylang 2.0+
- [ ] PHP 7.4+
- [ ] At least 2 languages configured in Polylang
- [ ] Test products in multiple languages

### Optional Setup (for specific tests)
- [ ] Block theme installed (e.g., Twenty Twenty-Four)
- [ ] REST API testing tool (Postman, Insomnia, or curl)
- [ ] WooCommerce Blocks plugin (usually included with WooCommerce)

---

## 1. HPOS (High-Performance Order Storage) Testing

### Setup HPOS
- [ ] Go to WooCommerce → Settings → Advanced → Features
- [ ] Enable "High-Performance Order Storage (HPOS)"
- [ ] Save settings

### Test Order Language Storage
- [ ] Create a new order in Language A
- [ ] Verify order language is saved (check order meta: `_order_language`)
- [ ] Switch to Language B
- [ ] Create another order
- [ ] Verify each order has correct language metadata

### Test Legacy Compatibility
- [ ] Disable HPOS
- [ ] Create an order
- [ ] Verify order language is saved via Polylang post language
- [ ] Re-enable HPOS
- [ ] Verify old orders still display correctly

### Test Admin Orders List
- [ ] Go to WooCommerce → Orders
- [ ] Verify "Language" column appears (HPOS mode only)
- [ ] Check each order shows correct language
- [ ] Try filtering orders by language (if Polylang filter available)

### Test Email Language
- [ ] Create order in Language A
- [ ] Check order confirmation email is in Language A
- [ ] Create order in Language B
- [ ] Check order confirmation email is in Language B

---

## 2. WooCommerce Blocks Testing

### Test Cart Block
- [ ] Create a page with Cart Block
- [ ] Add products from Language A to cart
- [ ] Visit cart page in Language A
- [ ] Verify products display in Language A
- [ ] Switch to Language B
- [ ] Verify products are translated to Language B (if translations exist)

### Test Checkout Block
- [ ] Create a page with Checkout Block
- [ ] Add products to cart
- [ ] Go through checkout process
- [ ] Complete order
- [ ] Verify order has correct language assigned

### Test Product Collection Block
- [ ] Create a page with Product Collection Block
- [ ] View page in Language A
- [ ] Verify only Language A products appear
- [ ] Switch to Language B
- [ ] Verify only Language B products appear

### Test Other Product Blocks
- [ ] Test "Best Selling Products" block
- [ ] Test "Featured Products" block
- [ ] Test "On Sale Products" block
- [ ] Test "Products by Category" block
- [ ] Verify each block shows only current language products

### Test Mini Cart Block
- [ ] Add Mini Cart block to header/sidebar
- [ ] Add products to cart
- [ ] Verify mini cart displays correct language
- [ ] Switch language
- [ ] Verify mini cart updates correctly

---

## 3. REST API v3 Testing

### Setup REST API Authentication
- [ ] Create WooCommerce REST API keys (WooCommerce → Settings → Advanced → REST API)
- [ ] Note Consumer Key and Consumer Secret

### Test Product Endpoints

**Get all products (no language filter):**
```bash
curl -u ck_XXXXX:cs_XXXXX https://yoursite.com/wp-json/wc/v3/products
```
- [ ] Verify products from all languages are returned
- [ ] Check each product has `language` field

**Get products in specific language:**
```bash
curl -u ck_XXXXX:cs_XXXXX "https://yoursite.com/wp-json/wc/v3/products?lang=en"
```
- [ ] Verify only English products are returned
- [ ] Check `language` field shows "en"

**Using header instead:**
```bash
curl -u ck_XXXXX:cs_XXXXX -H "X-WC-Language: en" https://yoursite.com/wp-json/wc/v3/products
```
- [ ] Verify same results as query parameter method

**Get single product:**
```bash
curl -u ck_XXXXX:cs_XXXXX https://yoursite.com/wp-json/wc/v3/products/{id}
```
- [ ] Check response includes `language` field
- [ ] Check response includes `translations` array with translation IDs

### Test Order Endpoints

**Get orders with language filter:**
```bash
curl -u ck_XXXXX:cs_XXXXX "https://yoursite.com/wp-json/wc/v3/orders?lang=en"
```
- [ ] Verify only English orders are returned
- [ ] Check `language` field in response
- [ ] Test with HPOS enabled
- [ ] Test with HPOS disabled

### Test Category/Tag Endpoints

**Get categories:**
```bash
curl -u ck_XXXXX:cs_XXXXX "https://yoursite.com/wp-json/wc/v3/products/categories?lang=en"
```
- [ ] Verify language filtering works
- [ ] Check `translations` field exists

**Get tags:**
```bash
curl -u ck_XXXXX:cs_XXXXX "https://yoursite.com/wp-json/wc/v3/products/tags?lang=en"
```
- [ ] Verify language filtering works

### Test Coupon Endpoints

**Get coupons:**
```bash
curl -u ck_XXXXX:cs_XXXXX "https://yoursite.com/wp-json/wc/v3/coupons?lang=en"
```
- [ ] Verify language filtering works for coupons

---

## 4. Order Search Testing

### Test Admin Order Search (Legacy Mode)
- [ ] Disable HPOS
- [ ] Go to WooCommerce → Orders
- [ ] Use search box to search for order
- [ ] Verify search results respect current language filter

### Test Admin Order Search (HPOS Mode)
- [ ] Enable HPOS
- [ ] Go to WooCommerce → Orders
- [ ] Search for orders
- [ ] Verify language filtering works correctly

### Test Full-Text Search
- [ ] Create orders with distinct product names in different languages
- [ ] Search for product name in Language A
- [ ] Verify only Language A orders appear
- [ ] Switch to Language B and repeat

---

## 5. Site Editor & Block Theme Testing

### Prerequisites
- [ ] Install and activate a block theme (e.g., Twenty Twenty-Four)

### Test Template Filtering
- [ ] Go to Appearance → Editor
- [ ] Create a custom WooCommerce template (e.g., Single Product)
- [ ] Assign language to template via Polylang
- [ ] Switch languages on frontend
- [ ] Verify correct template displays

### Test Template Parts
- [ ] Create a custom template part (e.g., header)
- [ ] Translate template part
- [ ] Verify correct version displays per language

---

## 6. Migration Tool Testing

### Test Migration Process
- [ ] Create some orders in legacy mode (HPOS disabled)
- [ ] Enable HPOS
- [ ] Go to WooCommerce Polylang Integration → Order Migration
- [ ] Verify HPOS status shows "Enabled"
- [ ] Click "Start Migration"
- [ ] Monitor progress bar
- [ ] Wait for completion

### Verify Migration Results
- [ ] Check migrated orders have `_order_language` meta
- [ ] Run migration again (should skip already-migrated orders)
- [ ] Verify no duplicates or errors

---

## 7. Backward Compatibility Testing

### Test with HPOS Disabled
- [ ] Disable HPOS
- [ ] Verify all features work as before
- [ ] Create orders
- [ ] Test cart and checkout
- [ ] Verify emails

### Test with Old WooCommerce Version
- [ ] If possible, test with WooCommerce 6.x
- [ ] Verify plugin doesn't break
- [ ] Check for PHP errors

---

## 8. Performance Testing

### Test with Large Product Catalog
- [ ] Test with 1000+ products
- [ ] Check product blocks load time
- [ ] Verify REST API response time
- [ ] Monitor admin order list performance

### Test with Many Orders
- [ ] Test with 10,000+ orders
- [ ] Run migration tool
- [ ] Check search performance
- [ ] Verify admin UI responsiveness

---

## 9. Edge Cases & Error Handling

### Test Products Without Translations
- [ ] Create product in Language A only
- [ ] View in Language B
- [ ] Verify graceful handling (no errors)

### Test Orders Without Language
- [ ] Manually create order without language meta
- [ ] Verify it doesn't crash admin
- [ ] Run migration tool
- [ ] Check if language can be assigned

### Test API with Invalid Language
```bash
curl -u ck_XXXXX:cs_XXXXX "https://yoursite.com/wp-json/wc/v3/products?lang=invalid"
```
- [ ] Verify graceful error or shows all products

---

## 10. Cross-Browser Testing

### Browsers to Test
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

### Features to Test in Each
- [ ] Checkout Block
- [ ] Cart Block
- [ ] Admin order migration
- [ ] Admin notices dismissal

---

## 11. Mobile Testing

### Responsive Design
- [ ] Test cart block on mobile
- [ ] Test checkout block on mobile
- [ ] Test admin migration tool on tablet
- [ ] Test product blocks on mobile

---

## Regression Testing

### Test Existing Features Still Work
- [ ] Product translation
- [ ] Category translation
- [ ] Stock synchronization
- [ ] Email translation
- [ ] Reports by language
- [ ] Coupon translation
- [ ] Classic cart shortcode
- [ ] Classic checkout shortcode

---

## Post-Testing Checklist

- [ ] Document any bugs found
- [ ] Create GitHub issues for bugs
- [ ] Test all critical bugs are fixed
- [ ] Get second tester to verify
- [ ] Update documentation with findings

---

## Test Results Summary

**Date Tested:** _____________
**Tester Name:** _____________
**WooCommerce Version:** _____________
**WordPress Version:** _____________
**PHP Version:** _____________

### Results:
- Critical Issues Found: _____
- Minor Issues Found: _____
- All Tests Passed: ☐ Yes ☐ No

### Notes:
_____________________________________
_____________________________________
_____________________________________
