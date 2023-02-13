# Decoupled Preview

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