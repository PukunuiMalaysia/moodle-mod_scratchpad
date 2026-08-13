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

namespace mod_scratchpad\task;

/**
 * Sends notifications for feedback which is outside the editing window.
 *
 * @package    mod_scratchpad
 * @copyright  2026 Pukunui Malaysia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_feedback_notifications extends \core\task\scheduled_task {
    /**
     * Returns the task name shown in the scheduled task administration page.
     *
     * @return string
     */
    public function get_name() {
        return get_string('tasksendfeedbacknotifications', 'mod_scratchpad');
    }

    /**
     * Sends pending feedback notification emails.
     */
    public function execute() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/scratchpad/lib.php');

        $entries = scratchpad_get_unmailed_graded(time() - $CFG->maxeditingtime);
        if (!$entries) {
            return;
        }

        $users = [];
        $courses = [];

        foreach ($entries as $entry) {
            mtrace("Processing scratchpad entry {$entry->id}.");

            if (!empty($users[$entry->userid])) {
                $user = $users[$entry->userid];
            } else {
                $user = $DB->get_record('user', ['id' => $entry->userid]);
                if (!$user) {
                    mtrace("Could not find the recipient for scratchpad entry {$entry->id}.");
                    continue;
                }
                $users[$entry->userid] = $user;
            }

            if (!empty($courses[$entry->course])) {
                $course = $courses[$entry->course];
            } else {
                $course = $DB->get_record('course', ['id' => $entry->course], 'id, shortname');
                if (!$course) {
                    mtrace("Could not find the course for scratchpad entry {$entry->id}.");
                    continue;
                }
                $courses[$entry->course] = $course;
            }

            if (!empty($users[$entry->teacher])) {
                $teacher = $users[$entry->teacher];
            } else {
                $teacher = $DB->get_record('user', ['id' => $entry->teacher]);
                if (!$teacher) {
                    mtrace("Could not find the feedback author for scratchpad entry {$entry->id}.");
                    continue;
                }
                $users[$entry->teacher] = $teacher;
            }

            $coursescratchpads = get_fast_modinfo($course)->get_instances_of('scratchpad');
            if (empty($coursescratchpads[$entry->scratchpad])) {
                mtrace("Could not find the activity for scratchpad entry {$entry->id}.");
                continue;
            }
            $mod = $coursescratchpads[$entry->scratchpad];

            $context = \context_module::instance($mod->id);
            $canadd = has_capability('mod/scratchpad:addentries', $context, $user);
            $entriesmanager = has_capability('mod/scratchpad:manageentries', $context, $user);

            if (!$canadd && $entriesmanager) {
                continue;
            }

            $previouslanguage = current_language();
            force_current_language($user->lang);

            try {
                $scratchpadname = format_string($entry->name, true);
                $scratchpadinfo = new \stdClass();
                $scratchpadinfo->teacher = fullname($teacher);
                $scratchpadinfo->scratchpad = $scratchpadname;
                $scratchpadinfo->url = (new \moodle_url('/mod/scratchpad/view.php', ['id' => $mod->id]))->out(false);

                $modulenameplural = get_string('modulenameplural', 'mod_scratchpad');
                $mailsubject = get_string('mailsubject', 'mod_scratchpad');
                $postsubject = "{$course->shortname}: {$mailsubject}: {$scratchpadname}";

                $posttext = "{$course->shortname} -> {$modulenameplural} -> {$scratchpadname}\n";
                $posttext .= "---------------------------------------------------------------------\n";
                $posttext .= get_string('scratchpadmail', 'mod_scratchpad', $scratchpadinfo) . "\n";
                $posttext .= "---------------------------------------------------------------------\n";

                $posthtml = '';
                if ($user->mailformat == 1) {
                    $courseurl = new \moodle_url('/course/view.php', ['id' => $course->id]);
                    $indexurl = new \moodle_url('/mod/scratchpad/index.php', ['id' => $course->id]);
                    $viewurl = new \moodle_url('/mod/scratchpad/view.php', ['id' => $mod->id]);
                    $posthtml = \html_writer::start_tag('p');
                    $posthtml .= \html_writer::link($courseurl, $course->shortname) . ' -> ';
                    $posthtml .= \html_writer::link($indexurl, $modulenameplural) . ' -> ';
                    $posthtml .= \html_writer::link($viewurl, $scratchpadname);
                    $posthtml .= \html_writer::end_tag('p');
                    $posthtml .= \html_writer::tag(
                        'p',
                        get_string('scratchpadmailhtml', 'mod_scratchpad', $scratchpadinfo)
                    );
                }

                if (!email_to_user($user, $teacher, $postsubject, $posttext, $posthtml)) {
                    mtrace("Could not send feedback for scratchpad entry {$entry->id}.");
                }
            } finally {
                force_current_language($previouslanguage);
            }

            if (!$DB->set_field('scratchpad_entries', 'mailed', 1, ['id' => $entry->id])) {
                mtrace("Could not mark scratchpad entry {$entry->id} as mailed.");
            }
        }
    }
}
