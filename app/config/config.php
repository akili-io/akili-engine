<?php
/*
 * This is common config for all servers.
 * If you want to set up DB config please update local.config.php
 */

define ('SALT', 'Some random string here'); // Set random string here here
define ('COOKIE_LIFE_TIME', 86400*30); // Default cookie life time (30 days)
define ('DEFAULT_MODULE', 'auth'); // Default module name
define ('DEFAULT_ACTION', 'login'); // Default action name
