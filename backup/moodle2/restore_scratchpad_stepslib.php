<?php

/**
 * Restore function
 *
 * @package mod_scratchpad
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
 
defined('MOODLE_INTERNAL') || die();

class restore_scratchpad_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('scratchpad', '/activity/scratchpad');

        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('scratchpad_entry', '/activity/scratchpad/entries/entry');
        }

        return $this->prepare_activity_structure($paths);
    }

    protected function process_scratchpad($data) {

        global $DB;

        $data = (Object)$data;

        unset($data->id);

        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->preventry = $this->get_mappingid('scratchpad', $data->preventry);

        $newid = $DB->insert_record('scratchpad', $data);
        $this->apply_activity_instance($newid);
    }

    protected function process_scratchpad_entry($data) {

        global $DB;

        $data = (Object)$data;

        $oldid = $data->id;
        unset($data->id);

        $data->scratchpad = $this->get_new_parentid('scratchpad');
        $data->modified = $this->apply_date_offset($data->modified);
        $data->timemarked = $this->apply_date_offset($data->timemarked);
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->teacher = $this->get_mappingid('user', $data->teacher);

        $newid = $DB->insert_record('scratchpad_entries', $data);
        $this->set_mapping('scratchpad_entry', $oldid, $newid);
    }

    protected function after_execute() {
        $this->add_related_files('mod_scratchpad', 'intro', null);
        $this->add_related_files('mod_scratchpad_entries', 'text', null);
        $this->add_related_files('mod_scratchpad_entries', 'entrycomment', null);
    }
}
