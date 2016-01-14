# WDS Network Require Login #
**Contributors:**      WebDevStudios  
**Donate link:**       http://webdevstudios.com  
**Tags:**              multisite, multi-network, network, require login, authentication, access, closed, hidden, login, password, privacy, private, protected, registered only, restricted    
**Requires at least:** 3.6.0  
**Tested up to:**      4.3  
**Stable tag:**        0.2.0  
**License:**           GPLv2  
**License URI:**       http://www.gnu.org/licenses/gpl-2.0.html  

## Description ##

A require-login plugin that can be network-activated as well as overridden on the site level.

Settings can be found under the Network Settings menu as well as the site-level Settings menu.

If you want more control over the login requirement for the [WP-API](http://wp-api.org/), the [WDS Allow REST API](https://github.com/WebDevStudios/WDS-Allow-REST-API) plugin is built to complement this one, where you can separately require or not require login for the read-only portion of the REST API or even set an authorization header key/token pair which can be used to bypass the login requirement. 

Requires [CMB2](https://github.com/WebDevStudios/CMB2).

## Installation ##

### Manual Installation ###

1. Upload the entire `/wds-network-require-login` directory to the `/wp-content/plugins/` directory.
2. Activate WDS Network Require Login through the 'Plugins' menu in WordPress.

## Frequently Asked Questions ##


## Screenshots ##


## Changelog ##

### 0.2.0 ###
* Added `wds_network_require_login_path_whitelist` filter
* Added a method to determine the requested path.
* Added inline documentation for the `wds_network_require_login_whitelist` filter.

### 0.1.0 ###
* First release
