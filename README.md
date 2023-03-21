# WP Decoupled Preview

[![Actively Maintained](https://img.shields.io/badge/Pantheon-Actively_Maintained-yellow?logo=pantheon&color=FFDC28)](https://docs.pantheon.io/oss-support-levels#actively-maintained-support) [![Packagist Version](https://img.shields.io/packagist/v/pantheon-systems/decoupled-preview)](https://packagist.org/packages/pantheon-systems/decoupled-preview) [![GPL 2.0 License](https://img.shields.io/github/license/pantheon-systems/wp-decoupled-preview)](https://github.com/pantheon-systems/wp-decoupled-preview/blob/main/LICENSE) [![Build Status](https://img.shields.io/github/actions/workflow/status/pantheon-systems/wp-decoupled-preview/lint-test.yml)](https://github.com/pantheon-systems/wp-decoupled-preview/actions)


Preview headless WordPress content on your front-end site.

This plugin intends to provide a single home for preview on a variety
of decoupled front-ends.

## Features

- Preview while editing a Post/Page.
- Configure multiple preview sites.
- Specify the post types (Post and Pages) that each preview site applies to.

## Configuration

In the WordPress Admin Dashboard, navigate to **Settings** -> **Preview Sites** (`/wp-admin/options-general.php?page=preview_sites`), add
one or more preview sites, and configure the following:

- Label: The name of the site.
- URL: The URL of the decoupled site you are providing preview data to.
- Secret: A token that will be passed to your decoupled site used to
  limit access to the preview.
- Preview Type: The type of preview site - currently only NextJS is supported.
- Content Types: The post types (like Post and Page) that this preview site applies to.

## Preview Types

While we hope to expand in the future, the initial release of this plugin only
supports NextJS. It was developed in support of [Pantheon's Next WordPress Starter](https://github.com/pantheon-systems/next-wordpress-starter), but can be applied to other
NextJS sites using a similar approach.

## Known Issues

- Currently this plugin does not support custom post types.

## Linting and Testing

This plugin uses [Composer](https://getcomposer.org/) to manage dependencies. To install dependencies, run `composer install` from the plugin directory.

Linting is done with [PHP_CodeSniffer](https://packagist.org/packages/squizlabs/php_codesniffer) using the [Pantheon WP Coding Standards](https://packagist.org/packages/pantheon-systems/pantheon-wp-coding-standards) ruleset. To run the linting checks, use the following command:

```bash
composer lint
```

Unit tests are written with [PHPUnit](https://packagist.org/packages/phpunit/phpunit) using the WP Unit test framework. To set up your local maching to be able to run the unit tests, use the following command:

```bash
composer test:install
```

Note that you will need to have MariaDB or MySQL installed and running on your local machine. Once you have the test environment set up, you can run the unit tests with the following command:

```bash
composer test
```

Both linting and testing are done in a GitHub Action on every commit and pull request. Tests are located in the `tests` directory.
