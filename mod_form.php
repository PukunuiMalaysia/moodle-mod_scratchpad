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
 * Settings page
 *
 * @package mod_scratchpad
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Defines the scratchpad activity settings form.
 */
class mod_scratchpad_mod_form extends moodleform_mod {
    /**
     * Defines the activity settings form.
     */
    public function definition() {
        global $COURSE, $DB;

        $mform = & $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('scratchpadname', 'scratchpad'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements(get_string('scratchpadquestion', 'scratchpad'));

        $link = $DB->get_records('scratchpad', ['course' => $COURSE->id]);
        $currentid = optional_param('update', '', PARAM_INT);
        $cm = '';

        $options = [];
        $options[0] = get_string('blankentry', 'scratchpad');
        foreach ($link as $a) {
            $options[$a->id] = $a->name;
        }

        // Avoid passing an empty course module ID to PostgreSQL.
        if (!empty($currentid)) {
            $cm = get_coursemodule_from_id('scratchpad', $currentid);
        }

        if (!empty($cm)) {
            unset($options[$cm->instance]);
        }
        $mform->addElement('select', 'preventry', get_string('preventry', 'scratchpad'), $options);
        $options = [];
        $options[0] = get_string('viewmode', 'scratchpad');
        $options[1] = get_string('downloadmode', 'scratchpad');
        $mform->addElement('select', 'mode', get_string('mode', 'scratchpad'), $options);

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    /**
     * Preprocesses completion settings before displaying the form.
     *
     * @param array $defaultvalues Default form values.
     */
    public function data_preprocessing(&$defaultvalues) {
        parent::data_preprocessing($defaultvalues);

        // Set up the completion checkboxes which aren't part of standard data.
        // We also make the default value (if you turn on the checkbox) for those
        // numbers to be 1, this will not apply unless checkbox is ticked.
        $defaultvalues['completionanswerenabled'] =
            !empty($defaultvalues['completionanswer']) ? 1 : 0;
        if (empty($defaultvalues['completionanswer'])) {
            $defaultvalues['completionanswer'] = 1;
        }
        // Tick by default if Add mode or if completion posts settings is set to 1 or more.
        if (empty($this->_instance) || !empty($defaultvalues['completionanswer'])) {
            $defaultvalues['completionanswerenabled'] = 1;
        } else {
            $defaultvalues['completionanswerenabled'] = 0;
        }
        if (empty($defaultvalues['completionanswer'])) {
            $defaultvalues['completionanswer'] = 1;
        }
    }

    /**
     * Add custom completion rules.
     *
     * @return array Array of string IDs of added items, empty array if none
     */
    public function add_completion_rules() {
        $mform =& $this->_form;

        $group = [];
        $group[] =& $mform->createElement('checkbox', 'completionanswerenabled', '', get_string('completionanswer', 'scratchpad'));
        $mform->setType('completionanswer', PARAM_INT);
        $mform->addGroup($group, 'completionanswergroup', get_string('completionanswergroup', 'scratchpad'), [' '], false);
        $mform->disabledIf('completionanswer', 'completionanswerenabled', 'notchecked');

        return ['completionanswergroup'];
    }

    /**
     * Returns whether the custom completion rule is enabled.
     *
     * @param array $data Form data.
     * @return bool
     */
    public function completion_rule_enabled($data) {
        return (!empty($data['completionanswerenabled']) && $data['completionanswer'] != 0);
    }

    /**
     * Return submitted data if properly submitted or returns NULL if validation fails or
     * if there is no submitted data.
     *
     * Do not override this method, override data_postprocessing() instead.
     *
     * @return object submitted data; NULL if not valid or not submitted or cancelled
     */
    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }
        if (!empty($data->completionunlocked)) {
            // Turn off completion settings if the checkboxes are not selected.
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionanswerenabled) || !$autocompletion) {
                $data->completionanswer = 0;
            } else {
                $data->completionanswer = 1;
            }
        }
        return $data;
    }

    /**
     * Allows module to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data the form data to be modified.
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        // Turn off completion settings if the checkboxes are not selected.
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionanswerenabled) || !$autocompletion) {
                $data->completionanswer = 0;
            } else {
                $data->completionanswer = 1;
            }
        }
    }
}
