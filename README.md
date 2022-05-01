# Cron

[![Build Status](https://github.com/innmind/cron/workflows/CI/badge.svg?branch=master)](https://github.com/innmind/cron/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/innmind/cron/branch/develop/graph/badge.svg)](https://codecov.io/gh/innmind/cron)
[![Type Coverage](https://shepherd.dev/github/innmind/cron/coverage.svg)](https://shepherd.dev/github/innmind/cron)

Library to help manage crontabs of a machine

**Important**: to correctly use this library you must validate your code with [`vimeo/psalm`](https://packagist.org/packages/vimeo/psalm)

## Installation

```sh
composer require innmind/cron
```

## Usage

### Insalling jobs

For the default user :

```php
use Innmind\Cron\{
    Crontab,
    Job,
};
use Innmind\OperatingSystem\Factory;

$os = Factory::build();
$install = Crontab::forConnectedUser(
    Job::of('* * * * * say hello'),
    Job::of('*/2 * * * * say world'),
);
$install($os->control());
// this is the same as running "echo '* * * * * say hello' | crontab" in your terminal
```

For a specific user :

```php
use Innmind\Cron\{
    Crontab,
    Job,
};
use Innmind\OperatingSystem\Factory;
use Innmind\Server\Control\ScriptFailed;
use Innmind\Immutable\{
    Either,
    SideEffect,
};

$os = Factory::build();
$install = Crontab::forUser(
    'watev',
    Job::of('* * * * * say hello'),
);
$install($os->control()); // Either<ScriptFailed, SideEffect>
// this is the same as running "echo '* * * * * say hello' | crontab -u admin" in your terminal
```

Since this library rely on [`innmind/server-control`](https://github.com/Innmind/ServerControl) you can easily install a crontab on a remote server. For example installing a crontab for the user `admin` on the server `example.com` would be done like this :

```php
use Innmind\Cron\{
    Crontab,
    Job,
};
use Innmind\OperatingSystem\Factory;
use Innmind\Url\Url;

$os = Factory::build();
$install = Crontab::forUser(
    'admin',
    Job::of('* * * * * say hello'),
);
$install(
    $os->remote()->ssh(Url::of('ssh://example.com')),
);
```

**Note**: At the moment the library does **not** support adding comments and spaces in the crontab.

### Reading a crontab

For the default user :

```php
use Innmind\Cron\{
    Read,
    Job,
};
use Innmind\OperatingSystem\Factory;
use Innmind\Immutable\{
    Maybe,
    Sequence,
};

$os = Factory::build();
$read = Read::forConnectedUser();
$jobs = $read($os->control()); // it will run "crontab -l"
// $jobs is an instance of Maybe<Sequence<Job>>
```

For a specific user :

```php
use Innmind\Cron\{
    Read,
    Job,
};
use Innmind\OperatingSystem\Factory;
use Innmind\Immutable\{
    Maybe,
    Sequence,
};

$os = Factory::build();
$read = Read::forUser('watev');
$jobs = $read($os->control()); // it will run "crontab -u watev -l"
// $jobs is an instance of Maybe<Sequence<Job>>
```

**Note**: At the moment comments and spaces are not listed in the `$jobs` variable.
