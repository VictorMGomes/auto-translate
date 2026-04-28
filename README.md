# Auto Translate

[![Latest Version on Packagist](https://img.shields.io/packagist/v/victormgomes/auto-translate.svg?style=flat-square)](https://packagist.org/packages/victormgomes/auto-translate)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/victormgomes/auto-translate/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/victormgomes/auto-translate/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/victormgomes/auto-translate/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/victormgomes/auto-translate/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/victormgomes/auto-translate.svg?style=flat-square)](https://packagist.org/packages/victormgomes/auto-translate)
[![License](https://img.shields.io/packagist/l/victormgomes/auto-translate.svg?style=flat-square)](https://packagist.org/packages/victormgomes/auto-translate)

**Automatic translation for translatable attributes in Eloquent models**

---

## Introduction

**Auto Translate** is a powerful addon for `spatie/laravel-translatable` that automates the translation of your model attributes. It listens for model saving events and automatically translates your content into multiple languages using configured translation providers.

### Why use this package?

*   **Automation**: Save time by letting the package handle translations automatically upon saving models.
*   **Seamless Integration**: Built specifically to work with the industry-standard `spatie/laravel-translatable`.
*   **Declarative Configuration**: Use PHP attributes to define which fields should be translated, keeping your models clean and readable.
*   **Scalable**: Easily manage multi-language content across your entire application without manual intervention.

---

## Support us

We invest a lot of resources into creating [best in class open source packages](https://github.com/victormgomes). You can support us by [sponsoring us on GitHub](https://github.com/sponsors/VictorMGomes).

---

## Installation

You can install the package via composer:

```bash
composer require victormgomes/auto-translate
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="auto-translate-migrations"
php artisan migrate
```

---

## Usage

Simply use the `AutoTranslatable` trait and the `#[AutoTranslate]` attribute on your models. Specify which fields should be automatically translated.

```php
use Victormgomes\AutoTranslate\Concerns\AutoTranslatable;
use Victormgomes\AutoTranslate\AutoTranslate;
use Spatie\Translatable\HasTranslations;

#[AutoTranslate(fields: ['name'])]
class Category extends Model
{
    use AutoTranslatable, HasTranslations;

    public $translatable = ['name'];
}
```

When you save the `Category` model, the `name` attribute will be automatically translated into all supported locales defined in your configuration.

---

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Victor M. Gomes](https://github.com/VictorMGomes)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
