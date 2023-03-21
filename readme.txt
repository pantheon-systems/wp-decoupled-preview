=== Decoupled Preview ===
Contributors: getpantheon, backlineint, abhisekmazumdar, jspellman,jazzs3quence
Tags: headless,next.js,decoupled,preview
Tested up to: 6.1.1
Stable tag: 0.2.1
License: GPL2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html

Preview headless WordPress content on Front-end sites including Next.js.

== Description ==

This plugin intends to provide a single home for preview on a variety
of decoupled front-ends.

## Features

- Preview while editing a Post/Page.
- Configure multiple preview sites.
- Specify the post types (Post and Pages) that each preview site applies to.

## Configuration

In the WordPress Admin Dashboard, navigate to **Settings** -> **Preview Sites**, add
one or more preview sites, and configure the following:

- Label: The name of the site.
- URL: The URL of the decoupled site you are providing preview data to.
- Secret: A token that will be passed to your decoupled site used to
  limit access to the preview.
- Preview Type: The type of preview site - currently only NextJS is supported.
- Content Types: The post types (like Post and Page) that this preview site applies to.

== Installation ==

To install Decoupled Preview, follow these steps:

- Install the plugin from WordPress.org using the WordPress dashboard.
- Activate the plugin.

To install Decoupled Preview in one line with WP-CLI:

`wp plugin install decoupled-preview --activate`

== Frequently Asked Questions ==

= What type of content can be previewed? =

While we hope to expand in the future, the initial release of this plugin only
supports NextJS. It was developed in support of [Pantheon's Next WordPress Starter](https://github.com/pantheon-systems/next-wordpress-starter), but can be applied to other
NextJS sites using a similar approach.

== Changelog ==

= 0.2.1 =
* Bugfixes and refactoring.

= 0.1.0 =
* Initial release