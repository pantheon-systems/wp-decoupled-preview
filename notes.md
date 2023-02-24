## TODO
* Brian and Abishek should make WordPress.org profiles to ensure they are properly credited in the plugin - https://profiles.wordpress.org/
* ~~full~~ update readme.txt
	* Long description required from Decoupled team
	* Brian and Abishek added to readme.txt
* ~~deploy to WP.org workflows~~
* ~~WPCS linting~~
* ~~cannot deactivate the plugin~~
* domain path is defined but there is no /languages folder
	* this can wait until later since there are no translations
* ~~nonce validation~~
* ~~a WP list table is created manually rather than using the WP_List_Table class~~
* ~~content type doesn't do anything~~
	* ~~no value is saved for items, the output in the listing is just hard coded~~
	* all post types should be supportable, not just post and pages
		* recommend removing this option until later but may just add custom post type support
* ~~global $pagenow used in open scope~~
* ~~all strings should be run through i18n functions~~
	* ~~wp-decoupled-preview.php~~
* ~~proof of life unit testing to ensure the plugin doesn't error/still functions after these changes are made~~
* I've allowed short array syntax in the PHPCS ruleset but there's inconsistency between using short arrays and not. One thing _or_ the other should be used, not both~~, and that rule should be added & enforced~~.
	* I am of the opinion that short array syntax is superior to long array syntax, although much of the WordPress ecosystem might be more familiar with long arrays. I'm open to discussion on this, but I think we can do something somewhat different than what's seen most frequently as a rising tide.
	* See https://github.com/pantheon-systems/cms-platform/discussions/9 ~~-- we're going to create our own WP coding standards.~~
* Similar to the above, I've added namespaces. There isn't a specific standard in WP for this, but it solves a lot of common issues with WP plugins and a lot of plugins have adopted namespaces (our own other plugins use namespaces). 
* Test on an actual site
* Update readme.tx
* More unit tests
* Consider exposing the "secret" token (does this really need to be secure?)
* Add configuration/setup instructions to readme.txt (or link from readme.txt to external site)