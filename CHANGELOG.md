# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.0] - 2026-01-22

### Added
- **Firewall Counter Display**: Added "Showing X of Y firewalls" counter with real-time filtering support on dashboard and firewalls index pages
- **Shared Filterable Mixin**: Created reusable `window.filterableMixin()` component for consistent filtering across pages
- **URL-Persisted Filters**: Search, status, and customer filters now persist in URL query parameters and survive page refreshes
- **Settings Page Restore**: Added "Restore to Default" buttons for logo and favicon in system settings
- **Gateway Display Fallback**: Offline firewalls now show WAN gateway with "Unknown" status instead of empty section
- **Documentation**: Added usage guide for filterable mixin at `.agent/filterable-mixin-usage.md`

### Changed
- **Gateway UI Redesign**: Updated gateway display with neutral backgrounds and color-coded left borders
- **Table Spacing**: Increased vertical padding in System Information tables for better readability
- **Gateway Labels**: Standardized gateway labels to use `text-sm` font size across all pages
- **Project Organization**: Moved deployment scripts (`install.sh`, `setup_reverb.sh`) to `/scripts` directory
- **Route Names**: Updated system settings routes from `system.customization.*` to `system.settings.*`

### Fixed
- **Settings Page Error**: Fixed "Route [system.customization.index] not defined" error when saving settings
- **Layout Stability**: Resolved issue where gateway section disappeared entirely for offline firewalls
- **Code Duplication**: Reduced ~100+ lines of duplicate filtering logic by using shared mixin

### Technical
- Refactored dashboard and firewalls index pages to use shared `filterableMixin`
- Added `device-updated` event with detailed payload (id, online status)
- Implemented reactive filtering with Alpine.js computed properties
- Enhanced event-driven architecture for real-time status updates

## [0.1.0] - Initial Release

### Added
- Initial AdmixCentral application for pfSense firewall management
- Dashboard with firewall status monitoring
- Real-time status updates via WebSocket (Reverb)
- Queue-based background job processing
- Multi-tenant architecture with company isolation
- System settings management
- Custom logo and favicon support
- Dark/light theme support

[Unreleased]: https://github.com/geekasso/admixcentral/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/geekasso/admixcentral/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/geekasso/admixcentral/releases/tag/v0.1.0
