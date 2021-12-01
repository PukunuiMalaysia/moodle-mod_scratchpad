<?php

/**
 * The mod_scratchpad entry created event.
 *
 * @package     mod_scratchpad
 * @copyright   2015 David Monllao
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_scratchpad\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The mod_scratchpad entry created class.
 *
 * @package    mod_scratchpad
 * @since      Moodle 3.1
 * @copyright  2015 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entry_created extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'scratchpad';
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('evententrycreated', 'mod_scratchpad');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' has created an entry for the scratchpad activity with " .
            "the course module id '$this->contextinstanceid'";
    }

    /**
     * Returns relevant URL.
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/scratchpad/edit.php', array('id' => $this->contextinstanceid));
    }

    /**
     * replace add_to_log() statement.
     *
     * @return array of parameters to be passed to legacy add_to_log() function.
     */
    protected function get_legacy_logdata() {
        $url = new \moodle_url('edit.php', array('id' => $this->contextinstanceid));
        return array($this->courseid, 'scratchpad', 'add entry', $url->out(), $this->objectid, $this->contextinstanceid);
    }
}
