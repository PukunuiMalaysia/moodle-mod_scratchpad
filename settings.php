<?php

/**
 * Settings page
 *
 * @package mod_scratchpad
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configselect('scratchpad/showrecentactivity', get_string('showrecentactivity', 'scratchpad'),
                                                  get_string('showrecentactivity', 'scratchpad'), 0,
                                                  array('0' => get_string('no'), '1' => get_string('yes'))));

    $settings->add(new admin_setting_configselect('scratchpad/overview', get_string('showoverview', 'scratchpad'),
                                                  get_string('showoverview', 'scratchpad'), 1,
                                                  array('0' => get_string('no'), '1' => get_string('yes'))));
}
