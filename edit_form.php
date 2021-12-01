<?php

/**
 * Edit page
 *
 * @package mod_scratchpad
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

class mod_scratchpad_entry_form extends moodleform {

    public function definition() {
        $this->_form->addElement('editor', 'text_editor', get_string('entry', 'mod_scratchpad'),
                null, $this->_customdata['editoroptions']);
        $this->_form->setType('text_editor', PARAM_RAW);
        $this->_form->addRule('text_editor', null, 'required', null, 'client');
        $this->_form->addElement('hidden', 'id');
        $this->_form->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }
}
