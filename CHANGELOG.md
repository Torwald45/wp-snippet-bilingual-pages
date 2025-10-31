# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [1.0.2] - 2025-10-31

### Fixed
- Fixed hreflang format to comply with Google standards (pl_PL -> pl-PL)
- WordPress locale format (underscore) is now converted to hreflang format (hyphen)
- Fixes "Document doesn't have a valid hreflang" error in PageSpeed Insights

### Added
- New function `torwald45_bl_locale_to_hreflang()` for locale conversion

## [1.0.1] - 2025-10-31

### Fixed
- Fixed x-default hreflang to always point to first language (default)
- Previously each page pointed x-default to itself, now both pages point to the primary language version

## [1.0.0] - 2025-10-30

### Added
- Two separate post types for bilingual pages
- Automatic lang attribute management for HTML tag
- Automatic hreflang tags for SEO in head section
- Bidirectional translation linking between pages
- Configurable second language via constant
- Translation metabox in page editor
- Support for page templates in second language pages
- Unique prefixes (torwald45_bl_) for all function names, IDs, and meta keys to prevent conflicts
- First language automatically detected from WordPress settings (Settings → General → Site Language)
