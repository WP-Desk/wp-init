# Prefixing `wp-init` with PHP-Scoper

Prefix plugin dependencies to avoid runtime collisions between plugins that load
different versions of the same package.

`wp-init` can be prefixed, but PHP-DI needs special handling:

- include `vendor/wpdesk/wp-init`
- include `vendor/php-di`
- include the runtime dependencies installed with PHP-DI
- leave `vendor/php-di/php-di/src/Compiler/Template.php` unprefixed

Check `composer.lock` for the exact PHP-DI dependency tree used by the plugin.
For common PHP-DI versions:

- PHP-DI up to `6.3.5` uses `vendor/opis/closure`
- PHP-DI `6.4.0` and newer commonly uses
  `vendor/laravel/serializable-closure`

## Example

Current PHP-Scoper config uses `finders` and `exclude-files`:

```php
<?php declare(strict_types=1);

$finder = Isolated\Symfony\Component\Finder\Finder::class;

return [
	'finders' => [
		$finder::create()
			->files()
			->in(
				[
					'vendor/wpdesk/wp-init',
					'vendor/php-di',
					'vendor/psr/container',
					'vendor/laravel/serializable-closure',
				]
			),
	],
	'exclude-files' => [
		'vendor/php-di/php-di/src/Compiler/Template.php',
	],
];
```

If the plugin uses a PHP-DI version that depends on `opis/closure`, replace
`vendor/laravel/serializable-closure` with `vendor/opis/closure`.

Do not use the old `finder` or `files-whitelist` option names in new
`scoper.inc.php` files.
