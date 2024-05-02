=== Decoupled Preview ===
Contributors: getpantheon, backlineint, abhisekmazumdar, jspellman,jazzs3quence
Tags: headless,next.js,decoupled,preview
Tested up to: 6.3.1
Stable tag: 1.0.6-dev
License: GPL2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html

Preview headless WordPress content on Front-end sites including Next.js.

== Description ==

This plugin intends to provide a single home for preview on a variety of decoupled front-ends. Can be used with the [Decoupled Kit Next.js WordPress Starter](https://decoupledkit.pantheon.io/docs/frontend-starters/nextjs/nextjs-wordpress/introduction) for pre-configured examples for post and page content.

=== Features ===

- Preview while editing a Post/Page.
- Configure multiple preview sites.
- Specify the post types (Post and Pages) that each preview site applies to.

== Configuration ==

In the WordPress Admin Dashboard, navigate to **Settings** -> **Preview Sites**, add one or more preview sites, and configure the following:

- Label: The name of the site.
- URL: The URL of the decoupled site you are providing preview data to.
- Secret: A token that will be passed to your decoupled site used to limit access to the preview.
- Preview Type: The type of preview site - currently only NextJS is supported.
- Content Types: The post types (like Post and Page) that this preview site applies to.

For more information on how preview data can be consumed on your front-end site, see [implementing decoupled preview](https://decoupledkit.pantheon.io/docs/frontend-starters/nextjs/nextjs-wordpress/implementing-preview).

== Installation ==

To install Decoupled Preview, follow these steps:

- Install the plugin from WordPress.org using the WordPress dashboard.
- Activate the plugin.

To install Decoupled Preview in one line with WP-CLI:

`wp plugin install decoupled-preview --activate`

Additional information on configuring the plugin can be found in the configuration section of the project details.

== Frequently Asked Questions ==

= What type of content can be previewed? =

While we hope to expand in the future, the initial release of this plugin only supports NextJS. It was developed in support of [Pantheon's Next WordPress Starter](https://github.com/pantheon-systems/next-wordpress-starter), but can be applied to other NextJS sites using a similar approach.

= Does this plugin support the classic editor? =

This plugin currently only supports the block editor.

== Changelog ==

** Latest **
* Updates Pantheon WP Coding Standards to 2.0 [[#63](https://github.com/pantheon-systems/wp-decoupled-preview/pull/63)]
* Fixes an issue with broken activation and deactivation hooks

= 1.0.5 =
* Preview Button Regression in WordPress 6.3. [[#58](https://github.com/pantheon-systems/wp-decoupled-preview/pull/58)]

= 1.0.4 =
* Improve Handling of secrets field. [[#53](https://github.com/pantheon-systems/wp-decoupled-preview/pull/53)]
* Move set default preview site functionality to wp-pantheon-decoupled. [[#54](https://github.com/pantheon-systems/wp-decoupled-preview/pull/54)]

= 1.0.3 =
* Bugfix for cases where Decoupled Preview link did not load due to a js error. [[#43](https://github.com/pantheon-systems/wp-decoupled-preview/pull/43)]

= 1.0.2 =
* Bugfix that prevented posts that didn't have a revision from being previewed.[[#39](https://github.com/pantheon-systems/wp-decoupled-preview/pull/39)].

= 1.0.1 =
* Update plugin slug for automated releases to wp.org [[#26](https://github.com/pantheon-systems/wp-decoupled-preview/pull/26)].

= 1.0.0 =
* Bugfixes and refactoring.

= 0.1.0 =
* Initial release