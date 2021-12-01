<?php

/**
 * The mod_scratchpad course module viewed event.
 *
 * @package    mod_scratchpad
 * @copyright  2014 drachels@drachels.com
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_scratchpad\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_scratchpad course module viewed event class.
 *
 * @package    mod_scratchpad
 * @since      Moodle 2.7
 * @copyright  2014 drachels@drachels.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_module_viewed extends \core\event\course_module_viewed {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'scratchpad';
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/scratchpad/view.php', array('id' => $this->contextinstanceid));
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
        $url = new \moodle_url('view.php', array('id' => $this->contextinstanceid));
        return array($this->courseid, 'scratchpad', 'view', $url->out(), $this->objectid, $this->contextinstanceid);
    }
}
