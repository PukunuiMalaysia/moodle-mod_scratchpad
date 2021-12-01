<?php

/**
 * Search area for mod_scratchpad activities.
 *
 * @package mod_scratchpad
 * @copyright  2016 David Monllao {@link http://www.davidmonllao.com}
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

namespace mod_scratchpad\search;

defined('MOODLE_INTERNAL') || die();

function get_dynamic_parent_activity() {
    global $CFG;
    if (class_exists('\core_search\area\base_activity')) {
        return '\core_search\area\base_activity';
    } else {
        return '\core_search\base_activity';
    }
}
class_alias(get_dynamic_parent_activity(), '\mod_scratchpad\search\DynamicParentActivity');


/**
 * Search area for mod_scratchpad activities.
 *
 * @package    mod_scratchpad
 * @copyright  2016 David Monllao {@link http://www.davidmonllao.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity extends \mod_scratchpad\search\DynamicParentActivity {

}

