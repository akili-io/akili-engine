<?php
/**
 * Akili Engine
 *
 * @author Almaz Mubinov <almaz.mubinov@gmail.com>
 * @link http://akili.io/
 * @copyright 2016 Almaz Mubinov
 * @license MIT
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Cache-control: no-cache");
mb_internal_encoding('utf-8');

/** @define "ROOT" "" */
define('ROOT', str_replace ('\\', '/', rtrim(dirname(__FILE__), '/')).'/');
define('APP', ROOT.'app/');
define('HOST', isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'');

include ROOT . 'engine/run.php';