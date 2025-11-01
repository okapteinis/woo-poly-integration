# Quick Testing Guide for v1.7.0

This is a streamlined testing guide to verify the core functionality works. For comprehensive testing, see `TESTING.md`.

## 🚀 Quick Setup (5 minutes)

### Prerequisites
- [ ] WordPress 5.4+ installed
- [ ] WooCommerce 7.0+ installed
- [ ] Polylang installed and configured with at least 2 languages
- [ ] Some test products in multiple languages

### Installation
```bash
# In your WordPress plugins directory
cd wp-content/plugins/woo-poly-integration
git pull origin nightly
```

Or activate the plugin from WordPress admin if already installed.

---

## ✅ Critical Tests (15 minutes)

### Test 1: HPOS Detection & Order Language

**Time: 3 minutes**

1. Go to **WooCommerce → Settings → Advanced → Features**
2. Check if "High-Performance Order Storage" is enabled
3. Note the status (enabled/disabled)

**With HPOS Enabled:**
```
✓ Create a test order in Language A
✓ Go to WooCommerce → Orders
✓ Verify you see a "Language" column
✓ Verify order shows correct language in the column
✓ Create another order in Language B
✓ Verify it shows Language B
```

**With HPOS Disabled (Legacy Mode):**
```
✓ Create a test order
✓ Verify no errors occur
✓ Order should still save correctly
```

**Expected Result:** ✅ Orders save with correct language in both modes

---

### Test 2: WooCommerce Blocks

**Time: 5 minutes**

1. Create a new page: **Pages → Add New**
2. Add the **Cart Block** (search for "Cart" in block inserter)
3. Add another page with **Checkout Block**
4. Add a third page with **Product Collection Block**

**Test Cart Block:**
```
✓ Add products to cart
✓ Visit cart page in Language A
✓ Products should display in Language A
✓ Switch to Language B (if translations exist)
✓ Products should update to Language B
```

**Test Checkout Block:**
```
✓ Go through checkout process
✓ Complete a test order
✓ Verify order has correct language assigned
```

**Test Product Collection Block:**
```
✓ View page in Language A
✓ Only Language A products appear
✓ Switch to Language B
✓ Only Language B products appear
```

**Expected Result:** ✅ All blocks respect language filtering

---

### Test 3: REST API Language Filtering

**Time: 4 minutes**

**Option A: Using Browser (Simple)**

1. Open browser
2. Go to: `https://yoursite.com/wp-json/wc/v3/products`
3. You'll need to authenticate (create API keys first if needed)

**Option B: Using curl (Recommended)**

First, create API keys:
1. Go to **WooCommerce → Settings → Advanced → REST API**
2. Click **Add Key**
3. Note the Consumer Key (ck_...) and Consumer Secret (cs_...)

Then test:

```bash
# Replace with your credentials
CK="your_consumer_key"
CS="your_consumer_secret"
SITE="https://yoursite.com"

# Test 1: Get all products (should show all languages)
curl -u $CK:$CS "$SITE/wp-json/wc/v3/products"

# Test 2: Get only English products
curl -u $CK:$CS "$SITE/wp-json/wc/v3/products?lang=en"

# Test 3: Get only French products
curl -u $CK:$CS "$SITE/wp-json/wc/v3/products?lang=fr"

# Test 4: Check if language field is in response
curl -u $CK:$CS "$SITE/wp-json/wc/v3/products?lang=en" | grep '"language"'
```

**Expected Results:**
```
✓ Test 1: Returns products from all languages
✓ Test 2: Returns only English products
✓ Test 3: Returns only French products
✓ Test 4: Shows "language": "en" in response
```

**Quick check:** Response should include these fields:
```json
{
  "id": 123,
  "name": "Product Name",
  "language": "en",          ← Should be present
  "translations": {           ← Should be present
    "fr": 456
  }
}
```

---

### Test 4: Migration Tool (if HPOS is enabled)

**Time: 3 minutes**

1. Go to **WooCommerce Polylang Integration → Order Migration**
2. Check HPOS status shows "Enabled"
3. Click **"Start Migration"**
4. Watch progress bar
5. Wait for completion message

**Expected Results:**
```
✓ Progress bar shows percentage
✓ Status shows "Processed X of Y orders"
✓ Completes without errors
✓ Shows "Migration completed successfully!"
```

**If you have no old orders:**
- Migration will complete instantly (0 orders to migrate)
- This is normal and means everything is working

---

## 🎨 Visual Tests (5 minutes)

### Test 5: Admin Notices

1. Go to **WooCommerce → Orders**
2. You should see a blue info notice about v1.7.0 features
3. Click the **X** to dismiss
4. Refresh page
5. Notice should stay dismissed

**Expected Result:** ✅ Notice appears, is informative, and can be dismissed

---

### Test 6: Admin Order Language Column (HPOS only)

If HPOS is enabled:

1. Go to **WooCommerce → Orders**
2. Look for "Language" column after "Order" column
3. Each order should show its language (e.g., "English", "Français")

**Expected Result:** ✅ Language column visible and shows correct language names

---

## 🔍 Smoke Tests (Quick Checks)

**Time: 2 minutes**

Go through these quickly to ensure nothing broke:

```
✓ Can create a product
✓ Can create a product translation
✓ Can add product to cart
✓ Can view cart
✓ Can complete checkout (classic or block)
✓ Can view orders in admin
✓ Admin settings page loads
✓ No PHP errors in debug.log
```

---

## 🐛 What to Look For

### ✅ Good Signs:
- Orders save with language
- Language column appears (HPOS mode)
- Blocks show only current language products
- REST API includes language field
- Migration completes successfully
- Admin notices appear and can be dismissed
- No PHP errors

### ❌ Red Flags:
- PHP errors or warnings
- Orders without language assigned
- Products from wrong language in blocks
- REST API crashes or returns errors
- Migration tool freezes or crashes
- Admin pages don't load
- Checkout fails

---

## 📝 Test Results Template

Copy this and fill in your results:

```
## Test Results - v1.7.0

**Date:** _______________
**Tester:** _______________
**Environment:**
- WordPress: _______________
- WooCommerce: _______________
- PHP: _______________
- HPOS Enabled: Yes / No

### Test Results:

Test 1 - HPOS & Order Language: ✅ / ❌
Notes: _________________________________

Test 2 - WooCommerce Blocks: ✅ / ❌
Notes: _________________________________

Test 3 - REST API: ✅ / ❌
Notes: _________________________________

Test 4 - Migration Tool: ✅ / ❌
Notes: _________________________________

Test 5 - Admin Notices: ✅ / ❌
Notes: _________________________________

Test 6 - Language Column: ✅ / ❌
Notes: _________________________________

Smoke Tests: ✅ / ❌
Notes: _________________________________

### Issues Found:
- Issue 1: _______________________________
- Issue 2: _______________________________
- Issue 3: _______________________________

### Overall Assessment:
✅ Ready for production
⚠️ Minor issues (acceptable for release)
❌ Critical issues (needs fixes)

### Additional Notes:
_________________________________________
_________________________________________
```

---

## 🆘 Troubleshooting

### Problem: "Language column not showing"
**Solution:**
- Make sure HPOS is enabled (WooCommerce → Settings → Advanced → Features)
- Legacy mode doesn't have language column (this is expected)

### Problem: "REST API returns authentication error"
**Solution:**
- Create API keys in WooCommerce → Settings → Advanced → REST API
- Use the keys in your curl command

### Problem: "Migration tool says 0 orders"
**Solution:**
- This is normal if all your orders were created after installing v1.7.0
- Only old orders need migration

### Problem: "Blocks showing products from all languages"
**Solution:**
- Make sure products have language assigned in admin
- Check Polylang is configured correctly
- Try clearing cache (if using cache plugin)

### Problem: "PHP errors"
**Solution:**
- Check debug.log in wp-content/
- Report the error as a GitHub issue
- Include the full error message

---

## 📞 Need Help?

- **Full Testing Guide:** See `TESTING.md` for comprehensive tests
- **REST API Docs:** See `REST-API.md` for detailed API usage
- **Issues:** https://github.com/okapteinis/woo-poly-integration/issues

---

## ✅ Next Steps After Testing

If all tests pass:
1. Mark as ready for production
2. Create PR from nightly to main
3. Merge and release v1.7.0

If issues found:
1. Document them clearly
2. Create GitHub issues
3. Tag as blocking/non-blocking
4. Fix critical issues before release

---

**Good luck with testing!** 🚀
