# WooCommerce Polylang Integration - Comprehensive Codebase Overview

**Project:** Hyyan WooCommerce Polylang Integration  
**Version:** 1.5.1  
**Repository:** https://github.com/hyyan/woo-poly-integration  
**License:** MIT  
**Primary Language:** PHP  
**Total Lines of Code (src):** ~9,300 lines  
**Total PHP Files:** 61

---

## 1. DIRECTORY STRUCTURE & ORGANIZATION

### Root Level Structure
```
/home/user/woo-poly-integration/
├── __init__.php                 # Main plugin entry point with headers
├── index.php                    # Empty security file
├── LICENSE                      # MIT License
├── README.md                    # GitHub documentation
├── readme.txt                   # WordPress.org readme
├── CHANGELOG.md                 # Version history
├── composer.json                # PHP dependencies
├── .travis.yml                  # Travis CI configuration
├── deploy.sh                    # SVN deployment script
├── .gitignore                   # Git ignore rules
├── .gitattributes               # Git attributes
├── .github/                     # GitHub configuration
│   ├── CONTRIBUTING.md
│   ├── ISSUE_TEMPLATE.md
│   └── PULL_REQUEST_TEMPLATE.md
├── assets/                      # Images and animations for docs
├── languages/                   # Translation files (po, mo, pot)
├── public/                      # Frontend JavaScript
│   └── js/
│       ├── Cart.js / Cart.min.js
│       └── Variables.js / Variables.min.js
├── src/Hyyan/WPI/              # Main source code
├── vendor/                      # Composer dependencies
├── nbproject/                   # NetBeans IDE files
└── screenshots/                 # WordPress plugin screenshots
```

### Source Code Structure: src/Hyyan/WPI/
```
/src/Hyyan/WPI/
├── Plugin.php                   # Main plugin orchestrator (534 lines)
├── Autoloader.php               # PSR-4 namespace autoloader
├── Utilities.php                # Helper methods (665 lines)
├── HooksInterface.php           # Extension hooks constants (286 lines)
├── MessagesInterface.php        # Message constants
│
├── Admin/                       # Administration & settings
│   ├── AbstractSettings.php     # Base settings class
│   ├── Settings.php             # Main settings page
│   ├── Features.php             # Plugin features toggles
│   ├── MetasList.php            # Product meta configuration
│   ├── SettingsInterface.php    # Settings interface contract
│   └── StatusReport.php         # WooCommerce status report integration
│
├── Product/                     # Product handling (6 files, ~2,000 lines)
│   ├── Product.php              # Main product class (272 lines)
│   ├── Meta.php                 # Product meta sync (944 lines) **SECURITY CRITICAL**
│   ├── Variable.php             # Variable products (489 lines)
│   ├── Variation.php            # Product variations (415 lines)
│   ├── Stock.php                # Stock synchronization
│   └── Duplicator.php           # Product duplication logic
│
├── Taxonomies/                  # Category/tag/attribute handling
│   ├── Taxonomies.php           # Main taxonomy class (206 lines)
│   ├── Categories.php           # Product categories
│   ├── Tags.php                 # Product tags
│   ├── Attributes.php           # Product attributes
│   ├── ShippingCalss.php        # Shipping classes
│   └── TaxonomiesInterface.php  # Interface
│
├── Tools/                       # Utility tools
│   ├── FlashMessages.php        # User notifications (142 lines)
│   └── TranslationsDownloader.php # WooCommerce translation fetcher
│
├── Views/                       # Template files
│   ├── admin.php                # Main admin template
│   ├── badges.php               # Social badges
│   ├── social.php               # Social links
│   ├── Admin/
│   │   ├── main.php             # Settings page HTML
│   │   ├── about.php
│   │   ├── getHelp.php
│   │   └── support.php
│   └── Messages/
│       ├── activateError.php    # Activation error message
│       ├── endpointsTranslations.php
│       └── support.php
│
├── Widgets/                     # Frontend widgets
│   ├── LayeredNav.php           # Product filters
│   └── SearchWidget.php         # Product search
│
├── Gateways/                    # Payment gateway translation
│   ├── Gateways.php             # Gateway manager (226 lines)
│   ├── GatewayBACS.php          # BACS gateway
│   ├── GatewayCOD.php           # Cash on delivery gateway
│   └── GatewayCheque.php        # Cheque payment gateway
│
├── Ajax.php                     # AJAX endpoint handling (48 lines)
├── Cart.php                     # Shopping cart translation (271 lines)
├── Coupon.php                   # Coupon handling (331 lines) **SECURITY CRITICAL**
├── Emails.php                   # Email template translation (517 lines)
├── Endpoints.php                # WooCommerce endpoint URLs (273 lines)
├── Order.php                    # Order language handling (169 lines) **SECURITY CRITICAL**
├── Pages.php                    # WooCommerce pages (216 lines)
├── Permalinks.php               # URL slug translation (60 lines)
├── Language.php                 # Language management (103 lines)
├── LocaleNumbers.php            # Number formatting (148 lines)
├── Login.php                    # Customer login handling
├── Media.php                    # Media handling
├── Privacy.php                  # Privacy policy support
├── Reports.php                  # Sales reports (332 lines)
├── Shipping.php                 # Shipping methods (194 lines)
├── Tax.php                      # Tax class handling (119 lines)
├── Breadcrumb.php               # Breadcrumb navigation
└── Currencies.php               # Currency support

```

---

## 2. KEY FILES & PURPOSES

### Core Plugin Architecture
| File | Lines | Purpose |
|------|-------|---------|
| **__init__.php** | 56 | Plugin header, entry point, autoloader bootstrap |
| **Plugin.php** | 534 | Core orchestrator, feature registration, lifecycle hooks |
| **Autoloader.php** | 65 | PSR-4 namespace autoloader for src/Hyyan/WPI |
| **Utilities.php** | 665 | Static helper methods for product/term translation |
| **HooksInterface.php** | 286 | Extension hooks/filters documentation |

### Security-Critical Files
| File | Lines | Risk Areas |
|------|-------|-----------|
| **Product/Meta.php** | 944 | Meta field synchronization, SKU validation, import handling |
| **Order.php** | 169 | Order language setting, translation filtering |
| **Coupon.php** | 331 | Product ID translation, WooCommerce Extended Coupon Features |
| **Product/Product.php** | 272 | AJAX featured product sync (line 88: absint($_GET)) |
| **Cart.php** | 271 | Cart item translation, product ID handling |

### Admin & Configuration
| File | Purpose |
|------|---------|
| **Admin/Settings.php** | Main settings page using WeDevs_Settings_API |
| **Admin/Features.php** | Feature toggles (fields locker, emails, reports, coupons, stock, etc.) |
| **Admin/MetasList.php** | Meta field sync configuration interface |
| **Admin/StatusReport.php** | Integration with WooCommerce system status report |

### Content Management
| File | Purpose |
|------|---------|
| **Product/** | Product translation, variations, attributes, stock sync |
| **Taxonomies/** | Categories, tags, attributes, shipping classes |
| **Pages.php** | WooCommerce core pages (shop, cart, checkout, my-account) |
| **Endpoints.php** | Endpoint URL translations |
| **Language.php** | Language management and detection |

### E-commerce Features
| File | Purpose |
|------|---------|
| **Emails.php** | Order email translation by language |
| **Coupon.php** | Coupon rules applied to translations |
| **Cart.php** | Cart item translation on language switch |
| **Order.php** | Order language tracking |
| **Shipping.php** | Shipping method translation |
| **Tax.php** | Tax class handling |
| **Reports.php** | Sales reports filtered by language |
| **Gateways/** | Payment gateway translation |

---

## 3. SOURCE CODE ORGANIZATION & ARCHITECTURE

### Design Patterns

1. **Namespace-based Organization**
   - PSR-4 autoloading via `Hyyan\WPI\Autoloader`
   - Clean separation by functionality
   - Supports WordPress plugin architecture

2. **Hooks-Based Architecture**
   - WordPress actions and filters extensively used
   - `HooksInterface` documents custom hooks
   - Extensibility via `apply_filters()` and `do_action()`

3. **Settings API Integration**
   - Extends `WeDevs_Settings_API` (vendor/class.settings-api.php)
   - `AbstractSettings` provides base class
   - Settings stored as WordPress options

4. **Polylang Integration**
   - Direct use of Polylang global functions
   - `pll_get_post()`, `pll_get_term()` for translations
   - `pll_current_language()` for language detection

5. **WooCommerce Integration**
   - Uses WooCommerce hooks and filters
   - Direct object manipulation via WooCommerce API
   - Product/Order/Coupon object methods

### Data Flow Examples

**Product Translation Flow:**
```
User creates product in Language A
  ↓
Product\Meta::handleProductScreen() triggers
  ↓
Product\Product->syncPostParent() handles parent
  ↓
pll_copy_post_metas filter fired
  ↓
Utilities::getProductTranslationsArrayByID() gets all versions
  ↓
Meta fields synchronized to all translations
  ↓
Taxonomies synchronized (categories, tags, attributes)
```

**Cart Translation Flow:**
```
Customer switches language
  ↓
Cart::replaceCartFragmentsScript() enqueues JS
  ↓
woocommerce_cart_item_product filter
  ↓
Utilities::getProductTranslationByObject() gets translated version
  ↓
Cart items updated to translated products
```

---

## 4. RECENT CHANGES (After 2025-11-01)

All files in the repository appear to be snapshot as of 2025-11-17, indicating this is a complete project state dump. Notable recent commits:

| Commit | Description |
|--------|-------------|
| b5f7495 | fixes #568 |
| b43c494 | fixes #545 (props mrleemon) - keep fields unlocked if products not in default language |
| 479b3cb | #548 #519 correction for new products |
| cd136d7 | version compatibility bump |
| ebd9350 | fixes #549 quick edit + #548 additional sync fixes |

**Focus Areas in Recent Fixes:**
- Quick edit synchronization issues (#549)
- Product type and visibility inconsistency (#548)
- Field locking when products missing in default language (#545)
- Breadcrumb translation (#542)
- Variation visibility and form defaults (#534, #527)

**No WooCommerce 9.3+ Compatibility Files Found:**
- Blocks.php - NOT PRESENT
- RestAPI.php - NOT PRESENT
- BlockThemes.php - NOT PRESENT
- Tools/OrderMigration.php - NOT PRESENT

---

## 5. CONFIGURATION FILES

### composer.json
```json
{
  "name": "hyyan/woo-poly-integration",
  "type": "wordpress-plugin",
  "license": "MIT",
  "require": {
    "php": ">=5.3.2",
    "composer/installers": "~1.0"
  }
}
```
**Note:** Minimum PHP requirement listed as 5.3.2, but plugin header and code require PHP 7.0+

### .travis.yml
- PHP 7.0 test environment
- Runs deploy.sh on master branch push
- Automatic SVN deployment to WordPress.org via Travis CI
- Uses environment variable `$WP_ORG_PASSWORD` for authentication

### deploy.sh
- Prepares files using rsync with .exclude-list
- Checks out WordPress.org SVN repository
- Copies plugin files to trunk
- Commits changes with version tag
- **Security Note:** Contains embedded credentials handling

### .gitignore
- `/nbproject/private/`
- `/build`
- `.php_cs.cache`
- `*.gif`
- `.idea`

### .exclude-list
- Files/directories excluded from deployment

### Plugin Version & Requirements
**From __init__.php:**
- Plugin Name: Hyyan WooCommerce Polylang Integration
- Version: 1.5.1
- Requires: WordPress 5.4+, PHP 7.0+
- Requires: WooCommerce 4.0.0+ (tested to 5.3.0)
- Text Domain: woo-poly-integration
- Domain Path: /languages

---

## 6. DOCUMENTATION FILES

### README.md (GitHub Documentation)
- Feature overview with checkmarks
- Installation instructions (classical, composer)
- Known limitations (variable products, Polylang language setting method)
- Contribution guidelines
- Maintenance status note about seeking maintainers

### CHANGELOG.md (25KB, ~100 entries)
**Recent Versions:**
- 1.5.1 - Fixes #545, #548, #549, #542
- 1.5.0 - Variation and stock synchronization improvements
- 1.4.5 - jQuery deprecation, variation duplication fixes
- 1.4.4 - Email translation enhancements
- 1.4.3 - Page checking and screen error fixes
- 1.4.2 - Default variation attribute sync, email fixes
- 1.4.1 - Cart price retention on language switch
- 1.4.0 - WooCommerce 3.6.x support, pages checker, cart improvements

**Key Issues Fixed by Version:**
- #568, #545, #549, #548, #542, #527, #534, #536, #535, #524, #526, #522, #529, #475

### readme.txt (WordPress Plugin Repository)
- Same content as README.md, WordPress format
- Tested up to: WordPress 5.7.1, WooCommerce 5.3.0
- List of contributors
- Feature matrix
- Installation instructions

### .github/CONTRIBUTING.md
- Guidelines for contributing code and issues
- Pull request process

### .github/ISSUE_TEMPLATE.md
- Template for bug reports

### .github/PULL_REQUEST_TEMPLATE.md
- Template for pull requests

---

## 7. PLUGIN ARCHITECTURE & MAIN COMPONENTS

### Initialization Flow

```
1. __init__.php loads
   ├── Defines constants (Hyyan_WPI_DIR, Hyyan_WPI_URL)
   ├── Loads vendor autoloader settings-api.php
   ├── Registers Hyyan\WPI\Autoloader
   └── Instantiates Plugin()
   
2. Plugin::__construct()
   ├── Registers FlashMessages
   ├── Hooks into 'init' and 'plugins_loaded'
   ├── Checks requirements
   ├── Displays error if requirements not met
   └── Calls registerCore()
   
3. Plugin::registerCore() instantiates:
   ├── Emails
   ├── Admin\Settings (which creates Features and MetasList)
   ├── Cart
   ├── Order
   ├── Pages
   ├── Endpoints
   ├── Product\Product (which creates Meta, Variable, Duplicator, Stock)
   ├── Taxonomies\Taxonomies
   ├── Media
   ├── Permalinks
   ├── Privacy
   ├── Language
   ├── Coupon
   ├── Reports
   ├── Widgets\SearchWidget
   ├── Widgets\LayeredNav
   ├── Gateways
   ├── Shipping
   ├── Breadcrumb
   ├── Tax
   ├── LocaleNumbers
   └── Ajax
```

### Component Responsibilities

#### Admin Layer
- **Settings.php**: Settings page UI and option management
- **Features.php**: Plugin feature toggles (14 features)
- **MetasList.php**: Meta field synchronization configuration
- **StatusReport.php**: WooCommerce status report integration

#### Product Layer
- **Product.php**: Main product translation orchestrator
- **Meta.php**: Meta field sync, SKU validation, quick edit handling
- **Variable.php**: Variable product variations
- **Variation.php**: Individual variation translation
- **Stock.php**: Stock synchronization across translations
- **Duplicator.php**: Product duplication logic

#### Taxonomy Layer
- **Taxonomies.php**: Coordinator for all taxonomies
- **Categories.php**: Product category translation
- **Tags.php**: Product tag translation
- **Attributes.php**: Product attributes translation
- **ShippingCalss.php**: Shipping class translation

#### Content Management
- **Pages.php**: WooCommerce page management (shop, cart, checkout, my-account)
- **Endpoints.php**: Endpoint URL translation
- **Language.php**: Language switching and detection
- **Permalinks.php**: URL slug translation

#### E-Commerce Features
- **Emails.php**: Order email language-aware translation
- **Coupon.php**: Coupon rules applied to translated products
- **Order.php**: Order language tracking
- **Cart.php**: Cart translation on language switch
- **Shipping.php**: Shipping method translation
- **Tax.php**: Tax class translation
- **Reports.php**: Sales reports by language
- **Gateways.php**: Payment gateway translation

#### Utility
- **Ajax.php**: AJAX endpoint URL correction
- **Utilities.php**: Core helper functions
- **FlashMessages.php**: User notification system
- **TranslationsDownloader.php**: WooCommerce translation downloader
- **LocaleNumbers.php**: Number formatting by locale
- **Media.php**: Media handling
- **Privacy.php**: Privacy policy support
- **Breadcrumb.php**: Breadcrumb navigation

---

## 8. LARGEST/MOST COMPLEX FILES

### By Line Count:
1. **Product/Meta.php** (944 lines) - Product meta synchronization, SKU handling
2. **Utilities.php** (665 lines) - Core helper functions
3. **Plugin.php** (534 lines) - Main orchestrator, WooCommerce page checks
4. **Emails.php** (517 lines) - Email template translation
5. **Product/Variable.php** (489 lines) - Variable product handling
6. **Product/Variation.php** (415 lines) - Variation translation
7. **Reports.php** (332 lines) - Sales reports filtering
8. **Coupon.php** (331 lines) - Coupon translation
9. **HooksInterface.php** (286 lines) - Hook documentation
10. **Endpoints.php** (273 lines) - Endpoint URL translation

---

## 9. SECURITY-CRITICAL FILES ANALYSIS

### Product/Meta.php (944 lines)
**Risk Areas:**
- Line 72: Uses `$_GET['from_post']` with isset() check
- Line 88: Uses `absint($_GET['product_id'])` in sync_ajax_woocommerce_feature_product
- Handles meta field synchronization across translations
- SKU validation suppression (line 49-51)

### Order.php (169 lines)
**Risk Areas:**
- Manages order language setting
- Updates WordPress options directly (pll_set_post_language)
- Filters order queries by language

### Coupon.php (331 lines)
**Risk Areas:**
- Line 52-54, 81-83: Error logging with $_SERVER['REQUEST_URI']
- Line 49: Checks for WJECF function existence
- Line 74-91: Translates product IDs from coupon rules
- Line 85: Uses explode() on comma-separated IDs

### Cart.php (271 lines)
**Risk Areas:**
- Handles product translation on cart operations
- Line 86-87: Accesses cart item array

### Utilities.php (665 lines)
**Risk Areas:**
- Line 156-157: Constructs URL using $_SERVER['HTTP_HOST'] and $_SERVER['REQUEST_URI']
- Core translation lookup methods used by other components

---

## 10. FEATURES OVERVIEW (From Features.php)

Configurable plugin features:
1. **fields-locker** - Lock meta fields that are synchronized
2. **emails** - Use order language for WooCommerce emails
3. **reports** - Enable language filtering in sales reports
4. **coupons** - Apply coupon rules to product translations
5. **stock** - Synchronize stock across translations
6. **categories** - Enable category translation
7. **tags** - Enable tag translation
8. **attributes** - Enable product attributes translation
9. **new-translation-defaults** - Default new translation title/content
10. **localenumbers** - Format numbers by locale
11. **importsync** - Synchronize on product import
12. **checkpages** - Auto-create/check WooCommerce pages
13. **language-downloader** - Auto-download WooCommerce translations

---

## 11. ADMIN INTERFACE COMPONENTS

### Main Settings Page
- Location: Settings → WooPoly
- Uses WeDevs_Settings_API for form rendering
- Sections: Features, Metas List
- Filters: `woo-poly.settings.sections`, `woo-poly.settings.fields`

### Features Section
- 13 configurable features
- Checkbox toggles for functionality
- Links to documentation

### Metas List Section
- Organized by category:
  - General
  - Polylang
  - Stock
  - Shipping
  - Attributes
  - Downloadable
  - Taxes
  - Price
  - Variables
- Multi-checkbox for each meta field
- Normalized meta display names

---

## 12. PLUGIN HOOKS & EXTENSION POINTS

### Key Filters (from HooksInterface.php)
- `woo-poly.product.metaSync` - Customize product meta sync
- `woo-poly.fieldsLockerSelectors` - Add field locker selectors
- `woo-poly.fieldsLockerVariableExcludeSelectors` - Exclude fields from locking
- `woo-poly.product.syncCategoryCustomFields` - Sync custom category fields
- `woo-poly.settings.sections` - Add settings sections
- `woo-poly.settings.fields` - Add settings fields

### Key Actions
- `woo-poly.product.syncCategoryCustomFields` - Sync category custom fields
- `pll_save_post` - Polylang post save hook
- `pll_get_post_types` - Add post types to Polylang
- `woocommerce_product_quick_edit_save` - Quick edit save

---

## 13. DEPENDENCIES

### External Dependencies
**composer.json requires:**
- `php >= 5.3.2` (but actually requires 7.0+)
- `composer/installers ~1.0`

**Embedded:**
- `vendor/class.settings-api.php` - WeDevs Settings API

### WordPress Dependencies
- **Plugins Required:**
  - Polylang 2.0.0+
  - WooCommerce 4.0.0+ (tested to 5.3.0)
  
- **WordPress Version:** 5.4+

- **Core APIs Used:**
  - WordPress Options API
  - WordPress Admin Pages/Menus
  - WordPress Hooks (actions/filters)
  - WordPress Security Functions (esc_*, wp_verify_nonce)
  - Post/Term management

---

## 14. CODE QUALITY OBSERVATIONS

### Strengths
- Well-organized namespace structure
- Consistent use of WordPress hooks
- Comprehensive feature configurability
- Extensive changelog tracking fixes
- MIT licensed with proper copyright headers
- PSR-4 autoloading

### Areas for Improvement
- Limited input validation/sanitization in some areas
- Error logging exposes REQUEST_URI
- Some complex methods (Meta.php, Emails.php)
- Limited test coverage indication
- Travis CI only runs deploy script, no actual tests
- Minimum PHP version in composer.json (5.3.2) doesn't match actual requirement (7.0)
- Some commented-out/deprecated code (Product.php line 48-58)
- Global variable usage for Polylang and WooCommerce

---

## 15. MAINTENANCE & STATUS

### Maintenance Note (from README)
> "Given that I am not using Wordpress these days and I haven't really been using WooPoly for a while. I am looking for maintainers to take over this project."

### Last Activity
- Current version: 1.5.1
- Last commits focus on bug fixes (#545, #548, #549, #542)
- No major version bumps in recent history
- Plugin appears stable but has limited active development

### Known Limitations
1. Variable products prevent changing default language
2. Polylang "language set from content" URL method not supported
3. Compatibility maintained up to WooCommerce 5.3.0 (plugin header)
4. No modern block editor/REST API support files present

---

## SUMMARY FOR SECURITY & CODE QUALITY AUDIT

### Key Files to Review First
1. **Product/Meta.php** - Most critical, handles meta sync
2. **Order.php** - Order language setting
3. **Coupon.php** - Coupon rules and product translation
4. **Utilities.php** - Core helper functions used everywhere
5. **Product/Product.php** - AJAX handling

### Major Subsystems
1. **Product Translation System** - Handles sync of 900+ lines of code
2. **Cart/Order Language Handling** - Critical for checkout
3. **Admin Settings Interface** - 3 configuration sections
4. **Email Localization** - 517 lines of email handling
5. **Taxonomies Management** - Categories, tags, attributes

### No WooCommerce 9.3+ Compatibility Files Found
- Plugin appears to not have been updated for WooCommerce 9.3+ features
- No block editor support
- No REST API integration for modern stores

### Plugin Lifecycle
- ~9,300 lines of source code
- 61 PHP files across 10 modules
- 13 configurable features
- 11 major components
- Active on WordPress.org and GitHub
- Seeking new maintainers

