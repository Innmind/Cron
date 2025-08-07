# Changelog

## 4.0.0 - 2025-08-07

### Changed

- Requires `innmind/foundation:~1.5`
- `Innmind\Cron\Crontab::_invoke()` now returns an `Innmind\Immutable\Attempt<Innmind\Immutable\SideEffect>`
- `Innmind\Cron\Read::_invoke()` now returns an `Innmind\Immutable\Attempt`
- `Innmind\Cron\Schedule\Range` is now declared internal

## 3.2.0 - 2023-09-23

### Added

- Support for `innmind/immutable:~5.0`

### Removed

- Support for PHP `8.1`

## 3.1.0 - 2023-01-29

### Added

- Support for `innmind/server-control:~5.0`
