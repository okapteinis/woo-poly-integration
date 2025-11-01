# WooCommerce REST API Language Support

Version 1.7.0 adds comprehensive language support to the WooCommerce REST API v3, making it easy to build multilingual headless stores and integrations.

## Table of Contents

- [Overview](#overview)
- [Authentication](#authentication)
- [Language Filtering](#language-filtering)
- [Products](#products)
- [Orders](#orders)
- [Categories & Tags](#categories--tags)
- [Coupons](#coupons)
- [Response Fields](#response-fields)
- [Error Handling](#error-handling)
- [Examples](#examples)

---

## Overview

The REST API language support allows you to:
- Filter all WooCommerce endpoints by language
- Get language information in API responses
- Access product/category translations
- Build multilingual headless stores
- Integrate with multilingual mobile apps

---

## Authentication

All examples require WooCommerce REST API authentication. There are two methods:

### 1. Basic Authentication (recommended for development)
```bash
curl -u ck_XXXXX:cs_XXXXX https://yoursite.com/wp-json/wc/v3/products
```

### 2. OAuth 1.0a (recommended for production)
See [WooCommerce REST API Authentication docs](https://woocommerce.github.io/woocommerce-rest-api-docs/#authentication)

---

## Language Filtering

You can filter endpoints by language using two methods:

### Method 1: Query Parameter (recommended)

Add `?lang=LANGUAGE_CODE` to any endpoint:

```bash
GET /wp-json/wc/v3/products?lang=en
GET /wp-json/wc/v3/orders?lang=fr
GET /wp-json/wc/v3/products/categories?lang=de
```

### Method 2: HTTP Header

Send the `X-WC-Language` header:

```bash
curl -H "X-WC-Language: en" https://yoursite.com/wp-json/wc/v3/products
```

### Supported Language Codes

Use the language codes configured in your Polylang settings:
- `en` - English
- `fr` - French
- `de` - German
- `es` - Spanish
- etc.

To get available languages, use the Polylang REST API:
```bash
GET /wp-json/pll/v1/languages
```

---

## Products

### Get All Products (Filtered by Language)

**Request:**
```bash
GET /wp-json/wc/v3/products?lang=en
```

**Response:**
```json
[
  {
    "id": 123,
    "name": "Product Name",
    "slug": "product-name",
    "language": "en",
    "translations": {
      "fr": 456,
      "de": 789
    },
    ...other product fields
  }
]
```

### Get Single Product

**Request:**
```bash
GET /wp-json/wc/v3/products/123
```

**Response includes language info:**
```json
{
  "id": 123,
  "name": "Product Name",
  "language": "en",
  "translations": {
    "fr": 456,
    "de": 789
  },
  ...other product fields
}
```

### Get Product Translation

To get a specific translation, either:

**Option 1:** Use translation ID directly:
```bash
GET /wp-json/wc/v3/products/456
```

**Option 2:** Get original product, extract translation ID from `translations` field, then fetch:
```bash
# Step 1: Get English product
GET /wp-json/wc/v3/products/123

# Step 2: Extract French ID from translations.fr (456)
# Step 3: Fetch French product
GET /wp-json/wc/v3/products/456
```

### Filter Products by Language and Other Criteria

You can combine language filter with other WooCommerce filters:

```bash
GET /wp-json/wc/v3/products?lang=en&category=15&per_page=20&page=1
```

---

## Orders

### Get All Orders (Filtered by Language)

**Request:**
```bash
GET /wp-json/wc/v3/orders?lang=en
```

**Response:**
```json
[
  {
    "id": 999,
    "status": "completed",
    "language": "en",
    "language_name": "English",
    ...other order fields
  }
]
```

### HPOS Compatibility

The order language filtering works with both:
- **Legacy post-based orders** (WooCommerce < 7.0)
- **HPOS orders** (WooCommerce 7.0+)

The plugin automatically detects your order storage mode and filters accordingly.

### Get Single Order

**Request:**
```bash
GET /wp-json/wc/v3/orders/999
```

**Response includes:**
```json
{
  "id": 999,
  "language": "en",
  "language_name": "English",
  ...other order fields
}
```

---

## Categories & Tags

### Get Product Categories (Filtered by Language)

**Request:**
```bash
GET /wp-json/wc/v3/products/categories?lang=en
```

**Response:**
```json
[
  {
    "id": 15,
    "name": "Electronics",
    "slug": "electronics",
    "language": "en",
    "translations": {
      "fr": 16,
      "de": 17
    },
    ...other category fields
  }
]
```

### Get Product Tags (Filtered by Language)

**Request:**
```bash
GET /wp-json/wc/v3/products/tags?lang=en
```

**Response:**
```json
[
  {
    "id": 20,
    "name": "Featured",
    "language": "en",
    "translations": {
      "fr": 21,
      "de": 22
    },
    ...other tag fields
  }
]
```

---

## Coupons

### Get Coupons (Filtered by Language)

**Request:**
```bash
GET /wp-json/wc/v3/coupons?lang=en
```

**Response:**
```json
[
  {
    "id": 50,
    "code": "SUMMER2025",
    "language": "en",
    ...other coupon fields
  }
]
```

---

## Response Fields

All language-aware endpoints include these additional fields:

### Products, Categories, Tags

| Field | Type | Description |
|-------|------|-------------|
| `language` | string | Language code (e.g., "en", "fr") |
| `translations` | object | Key-value pairs of language codes to translation IDs |

**Example:**
```json
{
  "language": "en",
  "translations": {
    "fr": 456,
    "de": 789
  }
}
```

### Orders

| Field | Type | Description |
|-------|------|-------------|
| `language` | string | Language code (e.g., "en", "fr") |
| `language_name` | string | Human-readable language name (e.g., "English") |

**Example:**
```json
{
  "language": "en",
  "language_name": "English"
}
```

---

## Error Handling

### Invalid Language Code

If you provide an invalid language code:
```bash
GET /wp-json/wc/v3/products?lang=invalid
```

The API will return all products (no language filter applied). This is intentional to ensure the API doesn't break.

### Missing Language Parameter

If you don't provide a language parameter, all languages are returned:
```bash
GET /wp-json/wc/v3/products
# Returns products from all languages
```

---

## Examples

### Complete E-commerce Integration

Here's how to build a multilingual store frontend:

#### 1. Get Available Languages

```javascript
const response = await fetch('https://yoursite.com/wp-json/pll/v1/languages');
const languages = await response.json();
// languages = [{code: 'en', name: 'English'}, {code: 'fr', name: 'Français'}]
```

#### 2. Get Products in User's Language

```javascript
const userLang = 'en';
const response = await fetch(
  `https://yoursite.com/wp-json/wc/v3/products?lang=${userLang}`,
  {
    headers: {
      'Authorization': 'Basic ' + btoa('ck_XXX:cs_XXX')
    }
  }
);
const products = await response.json();
```

#### 3. Handle Product Translations

```javascript
async function getProductWithTranslations(productId) {
  const response = await fetch(
    `https://yoursite.com/wp-json/wc/v3/products/${productId}`,
    {
      headers: {
        'Authorization': 'Basic ' + btoa('ck_XXX:cs_XXX')
      }
    }
  );

  const product = await response.json();

  // Get translations
  const translations = {};
  for (const [lang, translationId] of Object.entries(product.translations)) {
    const translationResponse = await fetch(
      `https://yoursite.com/wp-json/wc/v3/products/${translationId}`,
      {
        headers: {
          'Authorization': 'Basic ' + btoa('ck_XXX:cs_XXX')
        }
      }
    );
    translations[lang] = await translationResponse.json();
  }

  return {
    ...product,
    translationData: translations
  };
}
```

#### 4. Create Order in Specific Language

```javascript
async function createOrder(orderData, language) {
  const response = await fetch(
    `https://yoursite.com/wp-json/wc/v3/orders`,
    {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Basic ' + btoa('ck_XXX:cs_XXX'),
        'X-WC-Language': language
      },
      body: JSON.stringify(orderData)
    }
  );

  return await response.json();
}
```

### Mobile App Integration

```swift
// Swift example for iOS app
func fetchProducts(language: String) async throws -> [Product] {
    let url = URL(string: "https://yoursite.com/wp-json/wc/v3/products?lang=\(language)")!

    var request = URLRequest(url: url)
    request.setValue("Basic \(authToken)", forHTTPHeaderField: "Authorization")

    let (data, _) = try await URLSession.shared.data(for: request)
    return try JSONDecoder().decode([Product].self, from: data)
}
```

### Vue.js / React Integration

```javascript
// Vue composable
import { ref } from 'vue';

export function useProducts(language) {
  const products = ref([]);
  const loading = ref(false);
  const error = ref(null);

  async function fetchProducts() {
    loading.value = true;
    try {
      const response = await fetch(
        `https://yoursite.com/wp-json/wc/v3/products?lang=${language.value}`,
        {
          headers: {
            'Authorization': 'Basic ' + btoa('ck_XXX:cs_XXX')
          }
        }
      );
      products.value = await response.json();
    } catch (e) {
      error.value = e;
    } finally {
      loading.value = false;
    }
  }

  return {
    products,
    loading,
    error,
    fetchProducts
  };
}
```

---

## Best Practices

### 1. Always Specify Language

For best performance and predictable results, always specify the language:
```bash
✓ Good: GET /wp-json/wc/v3/products?lang=en
✗ Avoid: GET /wp-json/wc/v3/products
```

### 2. Cache Translation Mappings

If you frequently need to switch between translations, cache the translation IDs:

```javascript
const translationCache = new Map();

function getTranslationId(productId, targetLang) {
  const cacheKey = `${productId}:${targetLang}`;

  if (translationCache.has(cacheKey)) {
    return translationCache.get(cacheKey);
  }

  // Fetch and cache
  // ...
}
```

### 3. Handle Missing Translations

Always check if a translation exists:

```javascript
const product = await fetch(`/wp-json/wc/v3/products/${id}`).then(r => r.json());

if (product.translations[desiredLang]) {
  // Translation exists
  const translationId = product.translations[desiredLang];
  // Fetch translation...
} else {
  // Show original or fallback language
}
```

### 4. Use HTTP Header for SPA

For Single Page Applications, use the header method to avoid repetitive query parameters:

```javascript
const api = axios.create({
  baseURL: 'https://yoursite.com/wp-json/wc/v3',
  headers: {
    'X-WC-Language': currentLanguage,
    'Authorization': 'Basic ' + authToken
  }
});

// Now all requests use the language header
const products = await api.get('/products');
```

---

## Troubleshooting

### Language Filter Not Working

**Problem:** Products from all languages are returned even with `?lang=en`

**Solutions:**
1. Verify language code is correct (check Polylang settings)
2. Ensure products have language assigned in admin
3. Check plugin version is 1.7.0+
4. Clear any caching plugins

### Orders Not Showing Language

**Problem:** Order response doesn't include `language` field

**Solutions:**
1. Ensure order was created with v1.7.0+ plugin
2. Run the order migration tool (for old orders)
3. Check HPOS compatibility

### Translations Field Empty

**Problem:** `translations` object is empty

**Solutions:**
1. Verify product has translations created in admin
2. Check Polylang is properly configured
3. Ensure translations are published (not drafts)

---

## Support

For issues or questions:
- GitHub Issues: https://github.com/hyyan/woo-poly-integration/issues
- Documentation: https://github.com/hyyan/woo-poly-integration/wiki

---

## Changelog

### v1.7.0
- Initial REST API language support
- Added language filtering for products, orders, categories, tags, coupons
- Added translation IDs in responses
- HPOS-compatible order filtering
