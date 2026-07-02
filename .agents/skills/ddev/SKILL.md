---
name: ddev
description: Critical command wrappers and guidelines for DDEV environment.
---

# DDEV Command Wrappers

CRITICAL: Never use raw docker or artisan commands. Always use DDEV wrappers.

| Task | Command |
| --- | --- |
| Start environment | `ddev start` |
| Stop environment | `ddev stop` |
| Artisan commands | `ddev artisan <command>` |
| Composer | `ddev composer <command>` |
| NPM | `ddev exec npm <command>` |
| Run tests | `ddev exec php artisan test` |
| Run specific test | `ddev exec php artisan test --filter=TestName` |
| Database import | `ddev import-db < dump.sql` |
| Database export | `ddev export-db > dump.sql` |
| Migrations | `ddev artisan migrate` |
| Fresh migrate | `ddev artisan migrate:fresh --seed` |
| Tinker | `ddev artisan tinker` |
