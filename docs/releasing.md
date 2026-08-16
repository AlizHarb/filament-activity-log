# Releasing

Releases are created from `main` after the release pull request passes every required check.

## Prepare

1. Confirm `CHANGELOG.md` contains the intended version and release date.
2. Confirm upgrade instructions cover migrations, configuration changes, and authorization changes.
3. Run the supported dependency matrix in GitHub Actions.
4. Run the local release checks:

```bash
composer validate --strict --no-check-publish
composer check
composer audit
git diff --check
```

5. Install the package in a clean Laravel application, publish its migrations, migrate, and run:

```bash
php artisan filament-activity-log:doctor
php artisan filament-activity-log:verify-integrity
```

6. Verify the resource, dashboard, relation manager, filters, exports, and authorized mutation flows in a browser.

## Publish

Merge the release pull request using **Squash and merge**. Then create and push a signed semantic-version tag from the resulting `main` commit:

```bash
git switch main
git pull --ff-only origin main
git tag -s v2.0.0 -m "Release v2.0.0"
git push origin v2.0.0
```

Create the GitHub release from the matching changelog section. Do not mark a major release as `latest` until Packagist sees the tag and a clean application can install the tagged constraint.

## Verify

```bash
composer create-project laravel/laravel release-smoke
cd release-smoke
composer require alizharb/filament-activity-log:^2.0
php artisan filament-activity-log:install
php artisan filament-activity-log:doctor
```

After verification, update the changelog comparison link so `[Unreleased]` starts at the published tag.
