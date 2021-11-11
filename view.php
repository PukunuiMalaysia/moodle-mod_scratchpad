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

require_once("../../config.php");
require_once("lib.php");


$id = required_param('id', PARAM_INT);    // Course Module ID.

if (! $cm = get_coursemodule_from_id('scratchpad', $id)) {
    print_error("Course Module ID was incorrect");
}

if (! $course = $DB->get_record("course", array('id' => $cm->course))) {
    print_error("Course is misconfigured");
}

$context = context_module::instance($cm->id);

require_login($course, true, $cm);

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$entriesmanager = has_capability('mod/scratchpad:manageentries', $context);
$canadd = has_capability('mod/scratchpad:addentries', $context);

if (!$entriesmanager && !$canadd) {
    print_error('accessdenied', 'scratchpad');
}

if (! $scratchpad = $DB->get_record("scratchpad", array("id" => $cm->instance))) {
    print_error("Course module is incorrect");
}

if (! $cw = $DB->get_record("course_sections", array("id" => $cm->section))) {
    print_error("Course module is incorrect");
}

$scratchpadname = format_string($scratchpad->name, true, array('context' => $context));

// Header.
$PAGE->set_url('/mod/scratchpad/view.php', array('id' => $id));
$PAGE->navbar->add($scratchpadname);
$PAGE->set_title($scratchpadname);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($scratchpadname);

// Check to see if groups are being used here.
$groupmode = groups_get_activity_groupmode($cm);
$currentgroup = groups_get_activity_group($cm, true);
groups_print_activity_menu($cm, $CFG->wwwroot . "/mod/scratchpad/view.php?id=$cm->id");

if ($entriesmanager) {
    $entrycount = scratchpad_count_entries($scratchpad, $currentgroup);
    echo '<div class="reportlink"><a href="report.php?id='.$cm->id.'">'.
          get_string('viewallentries', 'scratchpad', $entrycount).'</a></div>';
}

$scratchpad->intro = trim($scratchpad->intro);
if (!empty($scratchpad->intro)) {
    $intro = format_module_intro('scratchpad', $scratchpad, $cm->id);
    echo $OUTPUT->box($intro, 'generalbox', 'intro');
}

echo '<br />';

$timenow = time();
if ($course->format == 'weeks' and $scratchpad->days) {
    $timestart = $course->startdate + (($cw->section - 1) * 604800);
    if ($scratchpad->days) {
        $timefinish = $timestart + (3600 * 24 * $scratchpad->days);
    } else {
        $timefinish = $course->enddate;
    }
} else {  // Have no time limits on the scratchpads.

    $timestart = $timenow - 1;
    $timefinish = $timenow + 1;
    $scratchpad->days = 0;
}
if ($timenow > $timestart) {

    echo $OUTPUT->box_start();

    // Edit button.
    if ($timenow < $timefinish) {

        if ($canadd) {
            echo $OUTPUT->single_button('edit.php?id='.$cm->id, get_string('startoredit', 'scratchpad'), 'get',
                array("class" => "singlebutton scratchpadstart"));
        }
    }

    // Display entry.
    if ($entry = $DB->get_record('scratchpad_entries', array('userid' => $USER->id, 'scratchpad' => $scratchpad->id))) {
        if (empty($entry->text)) {
            echo '<p align="center"><b>'.get_string('blankentry', 'scratchpad').'</b></p>';
        } else {
            echo scratchpad_format_entry_text($entry, $course, $cm);
        }
    } else {
        echo '<br><span class="warning">'.get_string('notstarted', 'scratchpad').'</span>';
    }

    echo $OUTPUT->box_end();

    // Info.
    if ($timenow < $timefinish) {
        if (!empty($entry->modified)) {
            echo '<div class="lastedit"><strong>'.get_string('lastedited').': </strong> ';
            echo userdate($entry->modified);
            echo ' ('.get_string('numwords', '', count_words($entry->text)).')';
            echo "</div>";
        }
        // Added three lines to mark entry as being dirty and needing regrade.
        if (!empty($entry->modified) AND !empty($entry->timemarked) AND $entry->modified > $entry->timemarked) {
            echo "<div class=\"lastedit\">".get_string("needsregrade", "scratchpad"). "</div>";
        }

        if (!empty($scratchpad->days)) {
            echo '<div class="editend"><strong>'.get_string('editingends', 'scratchpad').': </strong> ';
            echo userdate($timefinish).'</div>';
        }

    } else {
        echo '<div class="editend"><strong>'.get_string('editingended', 'scratchpad').': </strong> ';
        echo userdate($timefinish).'</div>';
    }

    // Feedback.
    if (!empty($entry->entrycomment) or !empty($entry->rating)) {
        $grades = make_grades_menu($scratchpad->grade);
        echo $OUTPUT->heading(get_string('feedback'));
        scratchpad_print_feedback($course, $entry, $grades);
    }

} else {
    echo '<div class="warning">'.get_string('notopenuntil', 'scratchpad').': ';
    echo userdate($timestart).'</div>';
}


// Trigger module viewed event.
$event = \mod_scratchpad\event\course_module_viewed::create(array(
   'objectid' => $scratchpad->id,
   'context' => $context
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('scratchpad', $scratchpad);
$event->trigger();

echo $OUTPUT->footer();
