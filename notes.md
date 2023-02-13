## TODO
* Brian and Abishek should make WordPress.org profiles to ensure they are properly credited in the plugin - https://profiles.wordpress.org/
* ~~full~~ update readme.txt
	* Long description required from Decoupled team
	* Brian and Abishek added to readme.txt
* ~~deploy to WP.org workflows~~
* ~~WPCS linting~~
* domain path is defined but there is no /languages folder
* nonce validation
* ~~global $pagenow used in open scope~~
* all strings should be run through i18n functions
	* ~~wp-decoupled-preview.php~~
* proof of life unit testing to ensure the plugin doesn't error/still functions after these changes are made
* I've allowed short array syntax in the PHPCS ruleset but there's inconsistency between using short arrays and not. One thing _or_ the other should be used, not both~~, and that rule should be added & enforced~~.
	* I am of the opinion that short array syntax is superior to long array syntax, although much of the WordPress ecosystem might be more familiar with long arrays. I'm open to discussion on this, but I think we can do something somewhat different than what's seen most frequently as a rising tide.
* Similar to the above, I've added namespaces. There isn't a specific standard in WP for this, but it solves a lot of common issues with WP plugins and a lot of plugins have adopted namespaces (our own other plugins use namespaces). 