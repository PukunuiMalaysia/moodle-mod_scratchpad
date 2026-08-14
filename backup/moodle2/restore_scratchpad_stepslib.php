<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Restore function
 *
 * @package mod_scratchpad
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

/**
 * Restores the scratchpad activity structure and entry data.
 */
class restore_scratchpad_activity_structure_step extends restore_activity_structure_step {
    /**
     * Defines the paths restored for this activity.
     *
     * @return restore_path_element[] Restore paths.
     */
    protected function define_structure() {

        $paths = [];
        $paths[] = new restore_path_element('scratchpad', '/activity/scratchpad');

        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('scratchpad_entry', '/activity/scratchpad/entries/entry');
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restores the scratchpad activity record.
     *
     * @param array|stdClass $data Restored activity data.
     */
    protected function process_scratchpad($data) {

        global $DB;

        $data = (object)$data;

        unset($data->id);

        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->preventry = $this->get_mappingid('scratchpad', $data->preventry);

        $newid = $DB->insert_record('scratchpad', $data);
        $this->apply_activity_instance($newid);
    }

    /**
     * Restores one scratchpad entry.
     *
     * @param array|stdClass $data Restored entry data.
     */
    protected function process_scratchpad_entry($data) {

        global $DB;

        $data = (object)$data;

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

    /**
     * Restores related files after record processing.
     */
    protected function after_execute() {
        $this->add_related_files('mod_scratchpad', 'intro', null);
        $this->add_related_files('mod_scratchpad_entries', 'text', null);
        $this->add_related_files('mod_scratchpad_entries', 'entrycomment', null);
    }
}
