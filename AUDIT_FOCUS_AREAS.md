# Security & Code Quality Audit - Focus Areas

## Executive Summary

**WooCommerce Polylang Integration v1.5.1** is a multilingual e-commerce plugin with ~9,300 lines of PHP code managing product translation, order localization, and e-commerce feature translation across the Polylang multilingual framework.

**Status:** Seeking maintainers - limited active development  
**Scope:** 61 PHP files across 10 modules  
**Primary Concern:** Input validation and data flow security across complex product translation system

---

## HIGH PRIORITY AUDIT ITEMS

### 1. Product/Meta.php (944 lines) - CRITICAL
**Risk Level:** HIGH  
**Reason:** Most complex file handling meta field synchronization

**Areas to Examine:**
- Line 72: `$_GET['from_post']` parameter usage without full validation
- Line 88: `absint($_GET['product_id'])` in AJAX handler (`sync_ajax_woocommerce_feature_product`)
- Meta field copy/sync logic (lines 120-250)
- SKU validation suppression (line 49-51)
- Product import sync (line 100-110)
- Nonce verification for AJAX calls
- SQL injection risk in meta queries

**Key Questions:**
- Is AJAX action properly nonce-protected?
- Are all product IDs sanitized before database queries?
- Could metadata be injected with malicious content?
- Are translated product relationships maintained securely?

---

### 2. Utilities.php (665 lines) - CRITICAL
**Risk Level:** HIGH  
**Reason:** Core helper functions used throughout codebase

**Areas to Examine:**
- Line 156-157: URL construction using `$_SERVER['HTTP_HOST']` and `$_SERVER['REQUEST_URI']`
- Product translation lookup methods (lines 34-98)
- Term translation methods (lines 135-144)
- Attribute translation (lines 248-350+)
- Data escaping and sanitization patterns
- Polylang API interaction (PLL() global usage)

**Key Questions:**
- Are SERVER variables properly validated before URL construction?
- Could HOST header be spoofed for XSS attacks?
- Are database queries properly prepared/escaped?
- Could translation lookups be exploited for data leakage?

---

### 3. Order.php (169 lines) - HIGH
**Risk Level:** HIGH  
**Reason:** Handles order language setting and sensitive order data filtering

**Areas to Examine:**
- Line 64-68: Direct update to WordPress polylang options
- Order language setting (line 84: `pll_set_post_language`)
- Order query filtering (lines 113-137)
- Product translation in order details (lines 95-102)
- Order metadata access patterns

**Key Questions:**
- Is order language setting validated?
- Can non-authenticated users manipulate order language?
- Are order queries properly filtered by user permissions?
- Could this expose orders from other languages/customers?

---

### 4. Coupon.php (331 lines) - HIGH
**Risk Level:** HIGH  
**Reason:** Handles coupon rules applied to translated products

**Areas to Examine:**
- Line 52-54, 81-83: Error logging with `$_SERVER['REQUEST_URI']` - information disclosure
- Line 49: WJECF function existence check
- Line 74-91: `getFreeProductsInLanguage()` - product ID translation
- Line 85: `explode()` on product IDs without proper validation
- Coupon code and description translation (lines 94-200+)

**Key Questions:**
- Should REQUEST_URI be exposed in logs?
- Are product IDs properly validated after explode()?
- Could coupon rules be bypassed through translation manipulation?
- Is WJECF integration safe against plugin conflicts?

---

### 5. Cart.php (271 lines) - MEDIUM-HIGH
**Risk Level:** MEDIUM-HIGH  
**Reason:** Cart manipulation on language switch - affects checkout

**Areas to Examine:**
- Line 86-87: Accesses cart item array without validation
- Product translation on cart switch (lines 84-125)
- Variation translation logic (lines 94-125)
- JavaScript cart fragment replacement

**Key Questions:**
- Are cart items properly validated?
- Could cart be manipulated during language switch?
- Are product prices secured during translation?
- Could variation selection be exploited?

---

## MEDIUM PRIORITY AUDIT ITEMS

### 6. Product/Product.php (272 lines)
**Risk Level:** MEDIUM  
**Specific Concerns:**
- AJAX handler for featured product sync
- Product duplication and translation relationships
- Query parameter handling

### 7. Admin/MetasList.php (91 lines) + Admin/Features.php (184 lines)
**Risk Level:** MEDIUM  
**Specific Concerns:**
- Option sanitization in settings
- Settings form validation
- Role-based access control (capability checks)

### 8. Emails.php (517 lines)
**Risk Level:** MEDIUM  
**Specific Concerns:**
- Email content injection prevention
- Translation string safety
- Order data exposure in emails

### 9. Taxonomies/Taxonomies.php (206 lines)
**Risk Level:** MEDIUM  
**Specific Concerns:**
- Term meta synchronization
- Slug translation validation
- Taxonomy option updates

### 10. Pages.php (216 lines)
**Risk Level:** MEDIUM  
**Specific Concerns:**
- Page creation/modification on plugin initialization
- Locale switching during page checks (lines 292-532)
- Permissions for page creation

---

## SECURITY PATTERNS TO VERIFY

### Input Validation
- [ ] All `$_GET`, `$_POST` parameters sanitized/validated
- [ ] AJAX nonce verification present for all AJAX handlers
- [ ] Product/Term IDs converted to int before use
- [ ] String inputs escaped appropriately

### Output Escaping
- [ ] All dynamic content in HTML contexts escaped
- [ ] esc_html(), esc_attr(), esc_url() used correctly
- [ ] Database output properly handled
- [ ] JSON responses properly encoded

### Database Security
- [ ] All custom queries use wpdb->prepare()
- [ ] No direct SQL concatenation
- [ ] Meta queries use proper API
- [ ] Option/Term meta operations validated

### Authorization
- [ ] Admin functions check user capabilities
- [ ] AJAX handlers verify user permissions
- [ ] Settings page requires manage_options
- [ ] Order/Product access checks in place

### Data Exposure
- [ ] No sensitive data in error logs/messages
- [ ] SERVER variables not exposed in output
- [ ] Database queries don't leak user data
- [ ] Error messages don't reveal internals

---

## CODE QUALITY ISSUES

### Issues Found:
1. **Version Mismatch:** composer.json requires PHP 5.3.2, but code/headers require 7.0+
2. **Error Logging:** REQUEST_URI exposed in error logs (Coupon.php line 52-54, 81-83)
3. **Deprecated Code:** Commented code blocks in Product.php (lines 48-58)
4. **Global Variables:** Direct use of global $polylang and $woocommerce
5. **No Test Coverage:** Travis CI only runs deploy script, no unit tests
6. **Complex Methods:** Meta.php and Emails.php have methods exceeding 100 lines
7. **TODO Comments:** Unresolved TODO in Meta.php line 9

### Recommendations:
- Fix composer.json PHP version requirement
- Remove/secure error logging of REQUEST_URI
- Clean up commented code
- Add unit tests
- Refactor large methods
- Update tested versions (last tested with WC 5.3.0, current is 9.3+)

---

## WooCommerce 9.3+ COMPATIBILITY

**No modern compatibility files found:**
- No Blocks.php (block editor support)
- No RestAPI.php (REST API integration)
- No BlockThemes.php (block theme support)
- No Tools/OrderMigration.php

**Implication:** Plugin may not be compatible with:
- WordPress 6.0+ with block editor integration
- WooCommerce 9.3+ block-based checkout
- Modern headless commerce setups
- HPOS (High-Performance Order Storage) in WooCommerce

**Note:** Tested versions are outdated:
- WordPress: up to 5.7.1
- WooCommerce: up to 5.3.0

---

## FEATURE RISK MATRIX

| Feature | Complexity | Security Risk | Status |
|---------|-----------|---------------|--------|
| Product Translation | HIGH | MEDIUM | Active |
| Meta Sync | VERY HIGH | HIGH | Active |
| Order Language | MEDIUM | MEDIUM | Active |
| Coupon Rules | MEDIUM | MEDIUM | Active |
| Cart Translation | MEDIUM | MEDIUM | Active |
| Email Localization | HIGH | MEDIUM | Active |
| Stock Sync | MEDIUM | MEDIUM | Active |
| Variable Products | HIGH | MEDIUM | Active |
| Admin Settings | MEDIUM | LOW | Active |
| Reports | MEDIUM | LOW | Active |

---

## EXTENSION POINTS SECURITY

**Custom Hooks (from HooksInterface.php):**
- `woo-poly.product.metaSync` - Filter product meta before sync
- `woo-poly.fieldsLockerSelectors` - Add field selectors
- `woo-poly.product.syncCategoryCustomFields` - Sync category fields
- `woo-poly.settings.sections` - Add settings sections
- `woo-poly.settings.fields` - Add settings fields

**Security Concern:** Verify hooks don't allow privilege escalation through plugins.

---

## TESTING RECOMMENDATIONS

### Unit Tests Needed:
1. Product translation creation/update
2. Order language setting and query filtering
3. Coupon product ID translation
4. Cart item translation on language switch
5. Meta field synchronization
6. Taxonomy translation

### Integration Tests Needed:
1. End-to-end product translation workflow
2. Checkout in multiple languages
3. Order view in different languages
4. Variable product variation translation
5. Quick edit synchronization

### Security Tests Needed:
1. CSRF/Nonce validation for AJAX
2. SQL injection attempts in queries
3. XSS in translated content
4. Privilege escalation attempts
5. Data leakage in error logs

---

## FILES BY PRIORITY FOR AUDIT

**Priority 1 (Review First):**
1. /src/Hyyan/WPI/Product/Meta.php (944 lines)
2. /src/Hyyan/WPI/Utilities.php (665 lines)
3. /src/Hyyan/WPI/Order.php (169 lines)
4. /src/Hyyan/WPI/Coupon.php (331 lines)
5. /src/Hyyan/WPI/Product/Product.php (272 lines)

**Priority 2 (Review Second):**
6. /src/Hyyan/WPI/Cart.php (271 lines)
7. /src/Hyyan/WPI/Emails.php (517 lines)
8. /src/Hyyan/WPI/Admin/Features.php (184 lines)
9. /src/Hyyan/WPI/Pages.php (216 lines)
10. /src/Hyyan/WPI/Product/Variable.php (489 lines)

**Priority 3 (Review After):**
- Remaining Product/* files
- Taxonomies/* files
- All other modules

---

## SUMMARY

This plugin handles critical e-commerce functionality with complex product translation logic. The main security concerns are:

1. **Input Validation** - Several instances of $_GET/$_POST usage
2. **SQL Injection** - Custom queries need verification
3. **Information Disclosure** - Error logging may expose sensitive data
4. **Privilege Escalation** - Admin functions need capability checks
5. **Data Integrity** - Product/Order translation relationships must be secure
6. **Code Quality** - Outdated version requirements and no test coverage

The plugin is stable but seeks new maintainers and has not been updated for WooCommerce 9.3+ or modern WordPress block editor features.

---

**Document Generated:** 2025-11-17  
**Auditor:** Security Team  
**Scope:** WooCommerce Polylang Integration v1.5.1  
**Status:** Ready for Comprehensive Audit
