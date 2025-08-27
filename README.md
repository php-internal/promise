# Promise

A lightweight implementation of [CommonJS Promises/A][CommonJS Promises/A] for PHP.

> [!NOTE]
> This is a fork of [reactphp/promise][reactphp/promise] with the following improvements:
> - PHP 8.1+ compatibility
> - `declare(strict_types=1);` in all files
> - `@yield` annotation in the PromiseInterface
> - Enhanced type annotations
> - Replaces `react/promise` v2

## Install

The recommended way to install this library is [through Composer][Composer].

```bash
composer require internal/promise
```

[![PHP](https://img.shields.io/packagist/php-v/internal/promise.svg?style=flat-square&logo=php)](https://packagist.org/packages/internal/promise)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/internal/promise.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/internal/promise)
[![License](https://img.shields.io/packagist/l/internal/promise.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/internal/promise.svg?style=flat-square)](https://packagist.org/packages/buggregator/trap)

## Tests

To run the test suite, go to the project root and run:

```bash
composer test
```

On top of this, Psalm is used to ensure type safety across the project:

```bash
composer psalm
```

## Credits

This fork is based on [reactphp/promise][reactphp/promise], which is a port of [when.js][when.js]
by [Brian Cavalier][Brian Cavalier].

Also, large parts of the [documentation][documentation] have been ported from the when.js
[Wiki][Wiki] and the
[API docs][API docs].

[documentation]: documentation.md
[CommonJS Promises/A]: http://wiki.commonjs.org/wiki/Promises/A
[CI status]: https://img.shields.io/github/actions/workflow/status/internal/promise/ci.yml?branch=2.x
[CI status link]: https://github.com/internal/promise/actions
[installs]: https://img.shields.io/packagist/dt/internal/promise?color=blue&label=installs%20on%20Packagist
[packagist link]: https://packagist.org/packages/internal/promise
[Composer]: https://getcomposer.org
[when.js]: https://github.com/cujojs/when
[Brian Cavalier]: https://github.com/briancavalier
[reactphp/promise]: https://github.com/reactphp/promise
