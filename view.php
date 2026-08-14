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
 * Plugin view page
 *
 * @package mod_scratchpad
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->dirroot . '/lib/completionlib.php');

$id = required_param('id', PARAM_INT);    // Course Module ID.

if (!$cm = get_coursemodule_from_id('scratchpad', $id)) {
    throw new moodle_exception('invalidcoursemodule', 'mod_scratchpad');
}

if (!$course = $DB->get_record('course', ['id' => $cm->course])) {
    throw new moodle_exception('invalidcourse', 'mod_scratchpad');
}

$context = context_module::instance($cm->id);

require_login($course, true, $cm);

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$entriesmanager = has_capability('mod/scratchpad:manageentries', $context);
$canadd = has_capability('mod/scratchpad:addentries', $context);

if (!$entriesmanager && !$canadd) {
    throw new moodle_exception('accessdenied', 'mod_scratchpad');
}

if (!$scratchpad = $DB->get_record('scratchpad', ['id' => $cm->instance])) {
    throw new moodle_exception('invalidcoursemodule', 'mod_scratchpad');
}
if (!empty($scratchpad->preventry)) {
    $prevscratchpad = $DB->get_record('scratchpad', ['id' => $scratchpad->preventry]);
}

if (!$cw = $DB->get_record('course_sections', ['id' => $cm->section])) {
    throw new moodle_exception('invalidcoursemodule', 'mod_scratchpad');
}

$scratchpadname = format_string($scratchpad->name, true, ['context' => $context]);

// Header.
$PAGE->set_url('/mod/scratchpad/view.php', ['id' => $id]);
$PAGE->navbar->add($scratchpadname);
$PAGE->set_title($scratchpadname);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($scratchpadname);

// Check download mode and display the download button.
if ($scratchpad->mode == 1) {
    if (!empty($scratchpad->intro)) {
        $intro = format_module_intro('scratchpad', $scratchpad, $cm->id);
        echo '<table><tr><td>' . $intro . '</td></tr></table>';
    }
    echo '<br /><br />';
    echo $OUTPUT->single_button(
        'download.php?id=' . $cm->id,
        get_string('download', 'scratchpad'),
        'get',
        ['class' => 'singlebutton scratchpadstart']
    );
    echo $OUTPUT->footer();
    die;
}

if (!empty($prevscratchpad)) {
    echo '<table border="2" width="99%"><tr><td>';

    $prevscratchpad->intro = trim($prevscratchpad->intro);
    if (!empty($prevscratchpad->intro)) {
        $intro = format_module_intro('scratchpad', $prevscratchpad, $cm->id);
        $questionicon = $OUTPUT->image_icon(
            'q-button',
            '',
            'scratchpad',
            ['style' => 'height:48px; width:48px']
        );
        echo '<table><tr><td>' . $questionicon . '</td><td>' . $intro . '</td></tr></table>';
    }

    $timenow = time();
    if ($course->format == 'weeks' && $prevscratchpad->days) {
        $timestart = $course->startdate + (($cw->section - 1) * 604800);
        if ($prevscratchpad->days) {
            $timefinish = $timestart + (3600 * 24 * $prevscratchpad->days);
        } else {
            $timefinish = $course->enddate;
        }
    } else {  // Have no time limits on the scratchpads.
        $timestart = $timenow - 1;
        $timefinish = $timenow + 1;
        $prevscratchpad->days = 0;
    }
    if ($timenow > $timestart) {
        // Display entry.
        $preventry = $DB->get_record(
            'scratchpad_entries',
            ['userid' => $USER->id, 'scratchpad' => $prevscratchpad->id]
        );
        if ($preventry) {
            if (empty($preventry->text)) {
                echo '<p align="center"><b>' . get_string('blankentry', 'scratchpad') . '</b></p>';
            } else {
                $answericon = $OUTPUT->image_icon(
                    'a-button',
                    '',
                    'scratchpad',
                    ['style' => 'height:48px; width:48px']
                );
                $answer = format_text(scratchpad_format_entry_text($preventry, $course, $cm), FORMAT_PLAIN);
                echo '<table><tr><td>' . $answericon . '</td><td>' . $answer . '</td></tr></table>';
            }
        } else {
            echo '<br><span class="warning">' . get_string('notstarted', 'scratchpad') . '</span>';
        }

        // Info.
        if ($timenow < $timefinish) {
            if (!empty($preventry->modified)) {
                echo '<div class="s"><strong>' . get_string('submitted', 'scratchpad') . ' ';
                echo userdate($preventry->modified);
                echo "</strong></div>";
            }
            // Added three lines to mark entry as being dirty and needing regrade.
            if (
                !empty($preventry->modified) &&
                !empty($preventry->timemarked) &&
                $preventry->modified > $preventry->timemarked
            ) {
                echo '<div class="lastedit">' . get_string('needsregrade', 'scratchpad') . '</div>';
            }

            if (!empty($prevscratchpad->days)) {
                echo '<div class="editend"><strong>' . get_string('editingends', 'scratchpad') . ': </strong> ';
                echo userdate($timefinish) . '</div>';
            }
        } else {
            echo '<div class="editend"><strong>' . get_string('editingended', 'scratchpad') . ': </strong> ';
            echo userdate($timefinish) . '</div>';
        }

        // Feedback.
        if (!empty($preventry->entrycomment) || !empty($preventry->rating)) {
            $grades = make_grades_menu($prevscratchpad->grade);
            echo $OUTPUT->heading(get_string('feedback'));
            scratchpad_print_feedback($course, $preventry, $grades);
        }
    } else {
        echo '<div class="warning">' . get_string('notopenuntil', 'scratchpad') . ': ';
        echo userdate($timestart) . '</div>';
    }
    echo '</td></tr></table>';

    echo '<hr>';
}

$scratchpad->intro = trim($scratchpad->intro);
if (!empty($scratchpad->intro)) {
    $intro = format_module_intro('scratchpad', $scratchpad, $cm->id);
    $questionicon = $OUTPUT->image_icon('q-button', '', 'scratchpad', ['style' => 'height:48px; width:48px']);
    echo '<table><tr><td>' . $questionicon . '</td><td>' . $intro . '</td></tr></table>';
}

if ($entriesmanager) {
    groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm, true);
    $entrycount = scratchpad_count_entries($scratchpad, $currentgroup);
    $reporturl = new moodle_url('/mod/scratchpad/report.php', ['id' => $cm->id]);
    echo html_writer::div(
        html_writer::link($reporturl, get_string('viewallentries', 'scratchpad', $entrycount)),
        'scratchpad-report-link my-3'
    );
}

$timenow = time();
if ($course->format == 'weeks' && $scratchpad->days) {
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
    // Display entry.
    if ($entry = $DB->get_record('scratchpad_entries', ['userid' => $USER->id, 'scratchpad' => $scratchpad->id])) {
        if (empty($entry->text)) {
            echo '<p align="center"><b>' . get_string('blankentry', 'scratchpad') . '</b></p>';
        } else {
            $answericon = $OUTPUT->image_icon('a-button', '', 'scratchpad', ['style' => 'height:48px; width:48px']);
            $answer = format_text(scratchpad_format_entry_text($entry, $course, $cm), FORMAT_PLAIN);
            echo '<table><tr><td>' . $answericon . '</td><td>' . $answer . '</td></tr></table>';
        }
    } else {
        echo '<br><span class="warning">' . get_string('notstarted', 'scratchpad') . '</span>';
    }

    // Edit button.
    if ($timenow < $timefinish) {
        if ($canadd) {
            echo '<br>' . $OUTPUT->single_button(
                'edit.php?id=' . $cm->id,
                get_string('startoredit', 'scratchpad'),
                'get',
                ['class' => 'singlebutton scratchpadstart']
            );
        }
    }

    // Info.
    if ($timenow < $timefinish) {
        if (!empty($entry->modified)) {
            echo '<div class="s"><strong>' . get_string('submitted', 'scratchpad') . ' ';
            echo userdate($entry->modified);
            echo "</strong></div>";
        }
        // Added three lines to mark entry as being dirty and needing regrade.
        if (!empty($entry->modified) && !empty($entry->timemarked) && $entry->modified > $entry->timemarked) {
            echo '<div class="lastedit">' . get_string('needsregrade', 'scratchpad') . '</div>';
        }

        if (!empty($scratchpad->days)) {
            echo '<div class="editend"><strong>' . get_string('editingends', 'scratchpad') . ': </strong> ';
            echo userdate($timefinish) . '</div>';
        }
    } else {
        echo '<div class="editend"><strong>' . get_string('editingended', 'scratchpad') . ': </strong> ';
        echo userdate($timefinish) . '</div>';
    }

    // Feedback.
    if (!empty($entry->entrycomment) || !empty($entry->rating)) {
        $grades = make_grades_menu($scratchpad->grade);
        echo $OUTPUT->heading(get_string('feedback'));
        scratchpad_print_feedback($course, $entry, $grades);
    }
} else {
    echo '<div class="warning">' . get_string('notopenuntil', 'scratchpad') . ': ';
    echo userdate($timestart) . '</div>';
}
// Trigger module viewed event.
$event = \mod_scratchpad\event\course_module_viewed::create([
    'objectid' => $scratchpad->id,
    'context' => $context,
]);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('scratchpad', $scratchpad);
$event->trigger();

echo $OUTPUT->footer();
