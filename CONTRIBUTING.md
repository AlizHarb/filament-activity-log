# Contributing

Thank you for considering contributing to the Filament Activity Log plugin!

## Support Questions

Please do not use the issue tracker for support questions.

## Bug Reports

To encourage active collaboration, we encourage you to open pull requests instead of just filing bugs. "Bug reports" may also be sent in the form of a pull request containing a failing test.

However, if you file a bug report, your issue should contain a title and a clear description of the issue. You should also include as much relevant information as possible and a code sample that demonstrates the issue. The goal of a bug report is to make it easy for yourself - and others - to replicate the bug and develop a fix.

## Coding Style

This package follows the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard and the [PSR-4](https://www.php-fig.org/psr/psr-4/) autoloading standard.

## Development

```bash
composer install
composer check
composer audit
```

Add or update tests for every behavior change. Changes to user-facing text must update every shipped locale; `LocalizationTest` enforces key and placeholder parity.

## Pull Requests and Commits

- Keep each pull request focused on one concern.
- Use clear commit subjects such as `feat:`, `fix:`, `docs:`, `test:`, or `chore:`.
- Avoid separate formatting-only follow-up commits; run `composer format` before pushing.
- Do not rewrite commits after review has started unless a maintainer requests it.
- Pull requests are squash-merged so each merged change produces one meaningful commit on `main`.

Maintainers should follow [the release procedure](docs/releasing.md) for version tags and release verification.

## Security Vulnerabilities

Follow the private reporting process in [SECURITY.md](SECURITY.md). Do not disclose suspected vulnerabilities in public issues.
