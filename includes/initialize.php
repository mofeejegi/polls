<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 29/06/2018
 * Time: 4:07 PM
 */
ob_start();

date_default_timezone_set("UTC");

// Define core paths to make sure require_once works as expected

defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);

//defined('SITE_ROOT') ? null : define('SITE_ROOT', DS.'Applications'.DS.'XAMPP'.DS.'xamppfiles'.DS.'htdocs'.DS.'polls');
defined('SITE_ROOT') ? null : define('SITE_ROOT', DS.'Storage'.DS.'ssd1'.DS.'719'.DS.'6623719');

defined('LIB_PATH') ? null : define('LIB_PATH', SITE_ROOT.DS.'includes');

// Load db connect file first
require_once(LIB_PATH.DS."db_connection.php");


// load session and basic functions so that everything after can use them
require_once(LIB_PATH.DS."session.php");
require_once (LIB_PATH.DS."time_initialize.php");
require_once(LIB_PATH.DS."functions.php");

// load core objects
require_once(LIB_PATH.DS."database.php");


// load database-related classes
require_once(LIB_PATH . DS . "databaseobject.php");
require_once(LIB_PATH.DS."user.php");