<?php
/*
 * usefulcron
 *
 * https://josecarlosphp.com
 *
 * EXAMPLE
 */

use josecarlosphp\usefulcron\Config;
use josecarlosphp\usefulcron\UsefulCron;

require 'vendor/autoload.php';

$config = new Config();
$config->debug(true);
$config->fake(true);
$config->addDirToClean('./log', 0);

$usefulCron = new UsefulCron($config);
$usefulCron->run('');
