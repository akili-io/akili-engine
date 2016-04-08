<?php
/*
 * This is local config for your server.
 * On your production server set permission of this file to 444 (deny write this file for all)
 */

/*
 * If site installed not to the root of domain set BASE_URL const
 * Path to root of your website
 * Ex.:
 * - BASE_URL is '' for http://domain.com/
 * - BASE_URL is 'folder/' for http://domain.com/folder/
 */

define ('BASE_URL', '');

// Database config
define ('DB_HOST', 'localhost');
define ('DB_NAME', 'akiliengine');
define ('DB_USER', 'root');
define ('DB_PASSWORD', 'root');

// Show trace log in errors
define ('DEBUG', true);
