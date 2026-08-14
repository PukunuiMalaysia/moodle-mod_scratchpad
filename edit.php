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
 * Edit page
 *
 * @package mod_scratchpad
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once('../../config.php');
require_once('./edit_form.php');
require_once($CFG->dirroot . '/lib/completionlib.php');
$id = required_param('id', PARAM_INT);    // Course Module ID.

if (!$cm = get_coursemodule_from_id('scratchpad', $id)) {
    throw new moodle_exception('invalidcoursemodule', 'mod_scratchpad');
}

if (!$course = $DB->get_record('course', ['id' => $cm->course])) {
    throw new moodle_exception('invalidcourse', 'mod_scratchpad');
}

$context = context_module::instance($cm->id);

require_login($course, false, $cm);

require_capability('mod/scratchpad:addentries', $context);

if (!$scratchpad = $DB->get_record('scratchpad', ['id' => $cm->instance])) {
    throw new moodle_exception('invalidcoursemodule', 'mod_scratchpad');
}
if (!empty($scratchpad->preventry)) {
    $prevscratchpad = $DB->get_record('scratchpad', ['id' => $scratchpad->preventry]);
    $preventry = $DB->get_record(
        'scratchpad_entries',
        ['userid' => $USER->id, 'scratchpad' => $scratchpad->preventry]
    );
}
// Header.
$PAGE->set_url('/mod/scratchpad/edit.php', ['id' => $id]);
$PAGE->navbar->add(get_string('edit'));
$PAGE->set_title(format_string($scratchpad->name));
$PAGE->set_heading($course->fullname);

$data = new stdClass();

$entry = $DB->get_record("scratchpad_entries", ["userid" => $USER->id, "scratchpad" => $scratchpad->id]);
if ($entry) {
    $data->entryid = $entry->id;
    $data->text = $entry->text;
    $data->textformat = $entry->format;
} else {
    $data->entryid = null;
    $data->text = '';
    $data->textformat = FORMAT_HTML;
}

$data->id = $cm->id;

$form = new mod_scratchpad_entry_form(null, ['entryid' => $data->entryid, 'text_editor' => $data->text]);
if ($form->is_cancelled()) {
    redirect($CFG->wwwroot . '/mod/scratchpad/view.php?id=' . $cm->id);
} else if ($fromform = $form->get_data()) {
    // If data submitted, then process and store.

    // Prevent CSFR.
    confirm_sesskey();
    $timenow = time();

    // This will be overwriten after being we have the entryid.
    $newentry = new stdClass();
    $newentry->text = $fromform->text_editor;
    $newentry->format = FORMAT_HTML;
    $newentry->modified = $timenow;

    if ($entry) {
        $newentry->id = $entry->id;
        if (!$DB->update_record("scratchpad_entries", $newentry)) {
            throw new moodle_exception('cannotupdateentry', 'mod_scratchpad');
        }
    } else {
        $newentry->userid = $USER->id;
        $newentry->scratchpad = $scratchpad->id;
        if (!$newentry->id = $DB->insert_record("scratchpad_entries", $newentry)) {
            throw new moodle_exception('cannotinsertentry', 'mod_scratchpad');
        }
    }

    // Update completion state.
    $completion = new completion_info($course);
    if ($completion->is_enabled($cm) && $scratchpad->completionanswer) {
        $completion->update_state($cm, COMPLETION_COMPLETE);
    }

    // Store the submitted text using the final entry ID.
    $newentry->text = $fromform->text_editor;
    $newentry->format = FORMAT_HTML;

    $DB->update_record('scratchpad_entries', $newentry);

    if ($entry) {
        // Trigger module entry updated event.
        $event = \mod_scratchpad\event\entry_updated::create([
            'objectid' => $scratchpad->id,
            'context' => $context,
        ]);
    } else {
        // Trigger module entry created event.
        $event = \mod_scratchpad\event\entry_created::create([
            'objectid' => $scratchpad->id,
            'context' => $context,
        ]);
    }
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('scratchpad', $scratchpad);
    $event->trigger();

    redirect(new moodle_url('/mod/scratchpad/view.php?id=' . $cm->id));
    die;
} else {
    $form->set_data(['text_editor' => $data->text, 'id' => $data->id]);
}


echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($scratchpad->name));

if (!empty($prevscratchpad)) {
    $previntro = format_module_intro('scratchpad', $prevscratchpad, $cm->id);
}
if (!empty($previntro)) {
    echo '<table border="2" width="99%"><tr><td>';
    echo $OUTPUT->box($previntro);

    if (!empty($preventry->text)) {
        echo $OUTPUT->box($preventry->text);
    }
    echo '</td></tr></table>';
}

$intro = format_module_intro('scratchpad', $scratchpad, $cm->id);
echo $OUTPUT->box($intro);

// Otherwise fill and print the form.
$form->display();

echo $OUTPUT->footer();
