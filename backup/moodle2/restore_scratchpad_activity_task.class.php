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

require_once($CFG->dirroot.'/mod/scratchpad/backup/moodle2/restore_scratchpad_stepslib.php');

class restore_scratchpad_activity_task extends restore_activity_task {

    protected function define_my_settings() {
    }

    protected function define_my_steps() {
        $this->add_step(new restore_scratchpad_activity_structure_step('scratchpad_structure', 'scratchpad.xml'));
    }

    static public function define_decode_contents() {

        $contents = array();
        $contents[] = new restore_decode_content('scratchpad', array('intro'), 'scratchpad');
        $contents[] = new restore_decode_content('scratchpad_entries', array('text', 'entrycomment'), 'scratchpad_entry');

        return $contents;
    }

    static public function define_decode_rules() {

        $rules = array();
        $rules[] = new restore_decode_rule('SCRATCHPADINDEX', '/mod/scratchpad/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('SCRATCHPADVIEWBYID', '/mod/scratchpad/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('SCRATCHPADREPORT', '/mod/scratchpad/report.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('SCRATCHPADEDIT', '/mod/scratchpad/edit.php?id=$1', 'course_module');

        return $rules;

    }

    public static function define_restore_log_rules() {

        $rules = array();
        $rules[] = new restore_log_rule('scratchpad', 'view', 'view.php?id={course_module}', '{scratchpad}');
        $rules[] = new restore_log_rule('scratchpad', 'view responses', 'report.php?id={course_module}', '{scratchpad}');
        $rules[] = new restore_log_rule('scratchpad', 'add entry', 'edit.php?id={course_module}', '{scratchpad}');
        $rules[] = new restore_log_rule('scratchpad', 'update entry', 'edit.php?id={course_module}', '{scratchpad}');
        $rules[] = new restore_log_rule('scratchpad', 'update feedback', 'report.php?id={course_module}', '{scratchpad}');

        return $rules;
    }

    public static function define_restore_log_rules_for_course() {

        $rules = array();
        $rules[] = new restore_log_rule('scratchpad', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
