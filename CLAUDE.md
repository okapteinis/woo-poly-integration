# CLAUDE CODE COLLABORATION GUIDELINES

This document provides essential context and guidelines for Claude Code when working on the woo-poly-integration project.

---

## CRITICAL RULES - READ FIRST

### 1. BRANCH STRATEGY

**ALWAYS work directly on the nightly branch**
**NEVER create separate feature branches unless explicitly requested**
**All commits go directly to nightly**

Commands to use:
```bash
git checkout nightly
git pull origin nightly
# make changes
git add .
git commit -m "message"
git push origin nightly
```

### 2. LICENSING

- This project uses **MIT License**
- **NEVER** add CC BY-NC-ND 4.0 or any other license
- **NEVER** modify the existing LICENSE file
- Do not add license headers to new files beyond standard copyright

### 3. CO-AUTHORSHIP

**ALL commits MUST include co-author attribution**

Required format at end of commit message:
```
Co-authored-by: Ojars Kapteinis <ojars@kapteinis.lv>
Co-authored-by: Claude (AI Assistant) <code@anthropic.com>
```

Example commit:
```bash
git commit -m "fix: SQL injection vulnerability in Variation.php

Replace raw SQL with prepared statements using wpdb->prepare()

Co-authored-by: Ojars Kapteinis <ojars@kapteinis.lv>
Co-authored-by: Claude (AI Assistant) <code@anthropic.com>"
```

### 4. COMMIT MESSAGE FORMAT

Use conventional commits style:
- `fix:` for bug fixes
- `feat:` for new features
- `docs:` for documentation
- `refactor:` for code refactoring
- `test:` for tests
- `chore:` for maintenance

Example:
```
fix: SQL injection in product variations

Replace direct SQL concatenation with wpdb->prepare()
to prevent SQL injection attacks in variation meta queries.

Co-authored-by: Ojars Kapteinis <ojars@kapteinis.lv>
Co-authored-by: Claude (AI Assistant) <code@anthropic.com>
```

---

## PROJECT CONTEXT

**Repository:** https://github.com/okapteinis/woo-poly-integration
**Working Branch:** nightly (default branch for all development)
**Original Author:** Hyyan Abo Fakher (hyyan)
**Current Maintainer:** Ojars Kapteinis (okapteinis)

**Purpose:** WordPress plugin for WooCommerce + Polylang integration
**Tech Stack:** PHP 7.4+, WordPress 5.4+, WooCommerce 7.0+, Polylang 2.0+

---

## CURRENT DEVELOPMENT PRIORITIES

### Phase 1: CRITICAL Security Fixes ✅ COMPLETED
- SQL injection fixes
- CSRF protection
- XSS prevention
- Input validation

### Phase 2: Input Validation (IN PROGRESS)
- isset() checks throughout codebase
- Sanitization standardization
- Strict type comparisons

### Phase 3: WooCommerce 9.3+ Compatibility (HIGH PRIORITY)
- HPOS support (Critical for WooCommerce 9.0+)
- WooCommerce Blocks support
- REST API v3 integration
- Block Themes support
- Order migration tool

### Phase 4: Code Quality
- Type hints and return types
- PHPDoc blocks
- Error handling
- Deprecated function replacement

### Phase 5: Testing Infrastructure
- PHPUnit setup
- Unit tests
- Integration tests
- CI/CD pipeline

### Phase 6: Documentation
- README updates
- CHANGELOG maintenance
- Migration guides

---

## TECHNICAL GUIDELINES

### Code Standards
- WordPress Coding Standards
- PSR-12 compatibility where applicable
- PHP 7.4+ features (type hints, return types)
- Strict comparisons (`===` not `==`)

### Security Requirements
- Always use `$wpdb->prepare()` for SQL queries
- Always sanitize input (`$_GET`, `$_POST`, `$_REQUEST`)
- Always escape output (`esc_html`, `esc_attr`, `esc_url`)
- Always verify nonces for form submissions
- Always check capabilities (`current_user_can`)

### WordPress Best Practices
- Use WordPress functions over PHP equivalents
- Hook into WordPress/WooCommerce actions and filters
- Follow WordPress plugin development guidelines
- Use `WP_Error` for error handling
- Use `WC_Logger` for logging

### File Organization
- Main plugin file: `__init__.php`
- Source code: `src/Hyyan/WPI/`
- Tests: `tests/`
- Documentation: root directory (README.md, CHANGELOG.md, etc.)

---

## REFERENCE DOCUMENTATION

### Key Files to Review:
1. `SECURITY_CODE_QUALITY_AUDIT_REPORT.md` - Complete audit findings
2. `CRITICAL_SECURITY_ISSUES.md` - Critical fixes needed
3. `WOOCOMMERCE_9_COMPATIBILITY_ROADMAP.md` - Implementation roadmap
4. `SECURITY_FIXES_V1.7.0.md` - Completed security fixes

### Commit History Context:
- Recent focus: PHP 8.4 compatibility
- v1.6.0: Security fixes and strict comparisons
- v1.7.0 (in progress): HPOS, Blocks, REST API support

---

## WORKFLOW FOR NEW TASKS

1. Read CLAUDE.md (this file)
2. Check current nightly branch state
3. Review relevant documentation (audit reports, roadmaps)
4. Make changes directly on nightly
5. Test changes if possible
6. Commit with proper co-authorship
7. Push to origin nightly
8. Report completion with summary

---

## COMMON PITFALLS TO AVOID

❌ **DO NOT** create separate feature branches
❌ **DO NOT** change the license
❌ **DO NOT** forget co-author attribution
❌ **DO NOT** use loose comparisons (`==`, `!=`)
❌ **DO NOT** access superglobals without isset()
❌ **DO NOT** output without escaping
❌ **DO NOT** use raw SQL without $wpdb->prepare()
❌ **DO NOT** skip nonce verification
❌ **DO NOT** skip capability checks

---

## CURRENT VERSION INFO

**Plugin Version:** 1.7.0 (in development)
**WordPress Tested:** 6.7
**WooCommerce Tested:** 9.3.0
**PHP Minimum:** 7.4
**PHP Tested:** 7.4, 8.0, 8.1, 8.2, 8.3, 8.4

---

## QUICK REFERENCE COMMANDS

### Start working:
```bash
git checkout nightly
git pull origin nightly
```

### Check status:
```bash
git status
git log --oneline -10
```

### Commit and push:
```bash
git add .
git commit -m "type: description

Detailed explanation

Co-authored-by: Ojars Kapteinis <ojars@kapteinis.lv>
Co-authored-by: Claude (AI Assistant) <code@anthropic.com>"
git push origin nightly
```

---

## CONTACT AND QUESTIONS

- If unclear about any requirement, ask the user before proceeding
- When in doubt, follow WordPress and WooCommerce coding standards
- Always prioritize security over convenience

---

## LAST UPDATED

**Date:** 2025-11-17
**Status:** Phase 1 security fixes completed
**Current Work:** Phase 2-3 implementation

---

*This file should be consulted at the start of every Claude Code session to ensure consistency and proper workflow.*
