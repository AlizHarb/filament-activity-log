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
