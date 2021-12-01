<?php

/**
 * Service file
 *
 * @package mod_scratchpad
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'mod_scratchpad_get_entry' => array(
        'classname'   => 'mod_scratchpad_external',
        'methodname'  => 'get_entry',
        'classpath'   => 'mod/scratchpad/externallib.php',
        'description' => 'Gets the user\'s scratchpad.',
        'type'        => 'read',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'mod_scratchpad_set_text' => array(
        'classname'   => 'mod_scratchpad_external',
        'methodname'  => 'set_text',
        'classpath'   => 'mod/scratchpad/externallib.php',
        'description' => 'Sets the scratchpad text.',
        'type'        => 'write',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
);