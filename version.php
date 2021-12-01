<?php

/**
 * Version page
 *
 * @package mod_scratchpad
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'mod_scratchpad';
$plugin->version  = 2021113000;
$plugin->requires = 2017111300;  /* Moodle 3.4 */
$plugin->release = '1.0.0 (Build: 2021110100)';
$plugin->maturity = MATURITY_STABLE;
$plugin->cron     = 60;
