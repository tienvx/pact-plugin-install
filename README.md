Pact Plugin Install [![Build Status][actions_badge]][actions_link] [![Coverage Status][coveralls_badge]][coveralls_link] [![PHP Version][php-version-image]][php-version-url]
===========================

This Composer's plugin allows all pact plugins will be in single directory.

## Example

Add this extra info `pact-plugin` to your plugin's package:

```json
{
  "name": "foo/bar",
  "require": {
    "tienvx/pact-plugin-install": "^1.0"
  },
  "extra": {
    "pact-plugin-dir": "bin/pact-plugins/name"
  }
}
```

Then the directory `bin/pact-plugins/name` will be symlinked into `vendor/pact-plugins/name`

## Tests

To run the tests:

```console
./vendor/bin/phpunit
```

To debug, set project dir so it will not be removed after running:

```console
env USE_TEST_PROJECT=$HOME/my-project DEBUG_COMPOSER=1 ./vendor/bin/phpunit tests/Integration/Valid/InstallTest.php
```

## License

This package is available under the [MIT license](LICENSE).

[actions_badge]: https://github.com/tienvx/pact-plugin-install/workflows/main/badge.svg
[actions_link]: https://github.com/tienvx/pact-plugin-install/actions

[coveralls_badge]: https://coveralls.io/repos/tienvx/pact-plugin-install/badge.svg?branch=main&service=github
[coveralls_link]: https://coveralls.io/github/tienvx/pact-plugin-install?branch=main

[php-version-url]: https://packagist.org/packages/tienvx/pact-plugin-install
[php-version-image]: http://img.shields.io/badge/php-8.0.0+-ff69b4.svg
