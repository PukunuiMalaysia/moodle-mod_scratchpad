<?php

/**
 * Log files
 *
 * @package mod_scratchpad
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die();

$logs = array(
    array('module' => 'scratchpad', 'action' => 'view', 'mtable' => 'scratchpad', 'field' => 'name'),
    array('module' => 'scratchpad', 'action' => 'view all', 'mtable' => 'scratchpad', 'field' => 'name'),
    array('module' => 'scratchpad', 'action' => 'view responses', 'mtable' => 'scratchpad', 'field' => 'name'),
    array('module' => 'scratchpad', 'action' => 'add entry', 'mtable' => 'scratchpad', 'field' => 'name'),
    array('module' => 'scratchpad', 'action' => 'update entry', 'mtable' => 'scratchpad', 'field' => 'name'),
    array('module' => 'scratchpad', 'action' => 'update feedback', 'mtable' => 'scratchpad', 'field' => 'name')
);
