# Prefixing `wp-init` with `php-scoper`

When developing plugins, it's worth to prefix all dependencies to avoid version collision between
different plugins loaded during runtime. For this library to enable prefixing you will need to
introduce following configuration to your `scoper.inc.php` file.

- Whitelist `vendor/php-di/php-di/src/Compiler/Template.php`
- Include `php-di` and it's dependencies in finders

> *Note*
>
> Pay attention to actual installed `php-di/php-di` version, as it's dependencies may change,
> requiring to update `scoper.inc.php` accordingly.

## Example configuration

**`php-di/php-di` up to 6.3.5**

```php
return [
  'finder' => Finder::create()->in(['vendor/wpdesk/wp-init', 'vendor/php-di', 'vendor/opis/closure']),
  'files-whitelist' => [
    'vendor/php-di/php-di/src/Compiler/Template.php'
  ],
];
```

**`php-di/php-di` since 6.4.0**

```php
return [
  'finder' => Finder::create()->in(['vendor/wpdesk/wp-init', 'vendor/php-di', 'vendor/laravel/serializable-closure']),
  'files-whitelist' => [
    'vendor/php-di/php-di/src/Compiler/Template.php'
  ],
];
```
