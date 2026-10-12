# Security changes

The 2026-10-12 refactor removes browser-authoritative balances and JSON-backed shared game state. All ten games now use authenticated SQL-backed rounds. The server validates wagers, chooses outcomes with `random_int`, hides unrevealed state, and settles each round once within a transaction. A player can only read or act on their own rounds. The legacy balance, daily-reward and multiplayer poker protocols return HTTP 410.

Every administrator action requires a current confirmed, unbanned administrator from the database. Login rotates sessions and limits attempts. Remember-me uses Laravel's encrypted opaque token; legacy password cookies are removed. Password changes invalidate other sessions. Mutating routes use CSRF protection, registration validates inputs, and confirmation links are signed, expire, and contain a hashed single-use token.

Pages use local assets and a Content Security Policy that restricts scripts to the application's origin. Responses disable shared caching and framing, restrict browser permissions, and suppress referrers. Production uses a configured canonical HTTPS URL and trusts only explicitly configured reverse proxy addresses. Secrets and databases are excluded from source archives.

Dependency versions are fixed by `composer.lock` and `package-lock.json`. Run `composer audit --locked` and `npm audit` to check current advisories. A clean audit describes known dependency advisories, not a guarantee that the application has no remaining bugs.

The test suite exercises unauthorized administrator requests, stale privileges and bans, forged balances, invalid wagers, concealed state, round ownership, repeated payouts, password/session handling, CSRF, migrations, import preservation, and database reset safeguards. CI installs the lockfiles, audits dependencies, builds assets, and runs these checks with PHP 8.2 and 8.4.

Deployment applies forward migrations. Database rebuilding is a separate operation that requires the exact resolved target and is blocked outside local/testing environments. Back up existing data before importing the old JSON export or applying a release. The SQL migration preserves legacy user values; the explicit JSON importer preserves password hashes and live records on reruns.

Poker now uses five-card video poker rather than the former shared multiplayer table. The arcade uses virtual points. See [README.md](README.md) for deployment configuration and reproducibility instructions.
