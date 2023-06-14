<?php
/*
 * usefulcron
 *
 * https://josecarlosphp.com
 *
 * EXAMPLE
 */

$debug = true;
$fake = true;

require 'vendor/autoload.php';
//require 'src/UsefulCron.php';

$usefulCron = new josecarlosphp\usefulcron\UsefulCron();

$usefulCron->checkAuth('');

$usefulCron->debug($debug);
$usefulCron->fake($fake);

$usefulCron->cleanDir('./log', 0);
