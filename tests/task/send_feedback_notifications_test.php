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

namespace mod_scratchpad;

use mod_scratchpad\task\send_feedback_notifications;

// Moodle 3.4 supports PHP 7.0, where the void return type is unavailable.
// phpcs:disable moodle.PHPUnit.TestReturnType.MissingReturnType
/**
 * Tests for the feedback notification scheduled task.
 *
 * @package    mod_scratchpad
 * @category   test
 * @copyright  2026 Pukunui Malaysia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_scratchpad\task\send_feedback_notifications
 */
final class send_feedback_notifications_test extends \advanced_testcase {
    /**
     * The task is installed and has a translated name.
     */
    public function test_task_is_registered() {
        $task = \core\task\manager::get_scheduled_task(send_feedback_notifications::class);

        $this->assertInstanceOf(send_feedback_notifications::class, $task);
        $this->assertSame(
            get_string('tasksendfeedbacknotifications', 'mod_scratchpad'),
            $task->get_name()
        );
    }

    /**
     * Feedback inside the editing window remains queued.
     */
    public function test_execute_defers_recent_feedback() {
        global $CFG, $DB;

        $this->resetAfterTest();
        $CFG->maxeditingtime = 60;
        $entry = $this->create_graded_entry(time());
        $sink = $this->redirectEmails();

        $task = new send_feedback_notifications();
        $task->execute();

        $this->assertSame(0, $sink->count());
        $this->assertEquals(0, $DB->get_field('scratchpad_entries', 'mailed', ['id' => $entry->id]));
    }

    /**
     * Eligible feedback is sent and is not sent again.
     */
    public function test_execute_sends_feedback_once() {
        global $CFG, $DB;

        $this->resetAfterTest();
        $CFG->maxeditingtime = 60;
        $entry = $this->create_graded_entry(time() - 120);
        $sink = $this->redirectEmails();
        $this->expectOutputRegex('/Processing scratchpad entry [0-9]+\\./');

        $task = new send_feedback_notifications();
        $task->execute();

        $this->assertSame(1, $sink->count());
        $this->assertEquals(1, $DB->get_field('scratchpad_entries', 'mailed', ['id' => $entry->id]));
        $this->assertStringContainsString('Scratchpad feedback', $sink->get_messages()[0]->subject);

        $task->execute();
        $this->assertSame(1, $sink->count());
    }

    /**
     * Creates a graded scratchpad entry for a student.
     *
     * @param int $timemarked Time at which feedback was saved.
     * @return \stdClass
     */
    private function create_graded_entry($timemarked) {
        global $DB;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $student = $generator->create_user(['mailformat' => 1]);
        $teacher = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        $generator->enrol_user($teacher->id, $course->id, 'editingteacher');
        $scratchpad = $generator->create_module('scratchpad', ['course' => $course->id]);

        $entry = (object) [
            'scratchpad' => $scratchpad->id,
            'userid' => $student->id,
            'modified' => $timemarked - 30,
            'text' => 'A student reflection.',
            'format' => FORMAT_PLAIN,
            'rating' => 80,
            'entrycomment' => 'Thoughtful reflection.',
            'teacher' => $teacher->id,
            'timemarked' => $timemarked,
            'mailed' => 0,
        ];
        $entry->id = $DB->insert_record('scratchpad_entries', $entry);

        return $entry;
    }
}
// phpcs:enable moodle.PHPUnit.TestReturnType.MissingReturnType
