# Upgrade Guide

This guide will help you upgrade between major versions of the Filament Activity Log package.

## Versioning

This package follows [Semantic Versioning](https://semver.org/):

- **Major versions** (e.g., 1.0.0 → 2.0.0) may include breaking changes
- **Minor versions** (e.g., 1.0.0 → 1.1.0) add new features without breaking changes
- **Patch versions** (e.g., 1.0.0 → 1.0.1) include bug fixes and minor improvements

## General Upgrade Process

When upgrading to a new version:

1. **Review the changelog** - Check [CHANGELOG.md](CHANGELOG.md) for changes in the new version
2. **Update composer** - Run `composer update alizharb/filament-activity-log`
3. **Clear caches** - Run `php artisan optimize:clear`
4. **Test thoroughly** - Test all activity logging functionality in your application

## Version-Specific Upgrade Guides

### Upgrading to Filament v5

> **Note**: This package supports both Filament v4 and v5. You can use the same package version with either Filament version.

**What Changed:**

- Added support for Filament v5 (which requires Livewire v4)
- Minimum PHP version increased to 8.3+
- Minimum Laravel version increased to 11+
- All existing features remain fully compatible

**Breaking Changes:**

- **PHP 8.3+ Required**: Livewire v4 (required by Filament v5) needs PHP 8.3 or higher
- **Laravel 11+ Required**: Filament v5 requires Laravel 11 or higher

**Migration Steps:**

1. **Ensure your environment meets the requirements:**

   ```bash
   php -v  # Should show 8.3 or higher
   ```

2. **Upgrade Laravel to v11+ (if needed):**

   ```bash
   # Follow Laravel's upgrade guide
   # https://laravel.com/docs/11.x/upgrade
   ```

3. **Upgrade Filament to v5:**

   ```bash
   composer require filament/filament:"^5.0" -W
   ```

4. **Update this package (if needed):**

   ```bash
   composer update alizharb/filament-activity-log
   ```

5. **Clear caches:**

   ```bash
   php artisan optimize:clear
   ```

6. **Test your application:**
   - Verify activity logging works correctly
   - Check timeline views render properly
   - Test dashboard widgets
   - Verify relation managers function correctly

**Compatibility:**

- ✅ Works with Filament v4 (PHP 8.3+, Laravel 11+)
- ✅ Works with Filament v5 (PHP 8.3+, Laravel 11+)
- ❌ No longer supports PHP 8.2 or Laravel 10

**No Code Changes Required:**

The package API remains unchanged. Your existing code will continue to work without modifications.

### Upgrading to 2.0.0 (Future)

> This section will be populated when version 2.0.0 is released.

**Breaking Changes:**

- TBD

**New Features:**

- TBD

**Migration Steps:**

1. TBD

### Upgrading to 1.1.0 (Future)

> This section will be populated when version 1.1.0 is released.

**New Features:**

- TBD

**Migration Steps:**

1. Update via Composer: `composer update alizharb/filament-activity-log`
2. Clear caches: `php artisan optimize:clear`

## Current Version: 1.0.0

This is the initial release. No upgrade steps are required.

### Fresh Installation

For a fresh installation, see the [Installation Guide](INSTALLATION.md).

## Common Upgrade Issues

### Configuration Changes

If you've published the configuration file, you may need to merge new configuration options:

1. **Backup your config**: `cp config/filament-activity-log.php config/filament-activity-log.php.backup`
2. **Publish new config**: `php artisan vendor:publish --tag="filament-activity-log-config" --force`
3. **Merge your customizations** from the backup file

### Translation Updates

If you've customized translations:

1. **Backup your translations**: Copy `lang/vendor/filament-activity-log` to a safe location
2. **Publish new translations**: `php artisan vendor:publish --tag="filament-activity-log-translations" --force`
3. **Merge your customizations** from the backup

### View Customizations

If you've customized views:

1. **Check the changelog** for view changes
2. **Compare your customized views** with the new versions
3. **Update your views** to match the new structure if needed

## Rollback

If you need to rollback to a previous version:

```bash
# Rollback to a specific version
composer require alizharb/filament-activity-log:1.0.0

# Clear caches
php artisan optimize:clear
```

## Getting Help

If you encounter issues during an upgrade:

- **Check the changelog**: [CHANGELOG.md](CHANGELOG.md)
- **Search existing issues**: [GitHub Issues](https://github.com/alizharb/filament-activity-log/issues)
- **Create a new issue**: Include your current version, target version, and error details
- **Review the documentation**: [README.md](README.md)

## Best Practices

### Before Upgrading

1. **Backup your database** - Always backup before major upgrades
2. **Test in staging** - Test the upgrade in a staging environment first
3. **Review the changelog** - Understand what's changing
4. **Check dependencies** - Ensure your other packages are compatible

### After Upgrading

1. **Clear all caches**: `php artisan optimize:clear`
2. **Test activity logging** - Create, update, and delete records
3. **Check the timeline** - Verify timeline views work correctly
4. **Test widgets** - Ensure dashboard widgets display properly
5. **Verify permissions** - Check that access control works as expected

## Deprecation Policy

When we deprecate features:

1. **Deprecation notice** - We'll add a deprecation notice in the code
2. **Documentation update** - The changelog will list deprecated features
3. **Removal timeline** - Deprecated features will be removed in the next major version
4. **Migration path** - We'll provide guidance on migrating to the new approach

## Support

For upgrade assistance:

- **Documentation**: [README.md](README.md) | [INSTALLATION.md](INSTALLATION.md)
- **Issues**: [GitHub Issues](https://github.com/alizharb/filament-activity-log/issues)
- **Discussions**: [GitHub Discussions](https://github.com/alizharb/filament-activity-log/discussions)

---

**Note**: This upgrade guide will be updated with each new release. Always refer to the version-specific sections above when upgrading.
