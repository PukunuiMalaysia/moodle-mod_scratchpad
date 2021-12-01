<?php

/**
 * The mod_scratchpad instance list viewed event.
 *
 * @package    mod_scratchpad
 * @copyright  2014 drachels@drachels.com
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_scratchpad\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_scratchpad instance list viewed event class.
 *
 * @package    mod_scratchpad
 * @since      Moodle 2.7
 * @copyright  2014 drachels@drachels.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_module_instance_list_viewed extends \core\event\course_module_instance_list_viewed {
    // No need for any code here as everything is handled by the parent class.
}
