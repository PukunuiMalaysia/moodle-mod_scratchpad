---
title: Scratchpad activity
---

# Scratchpad activity

Scratchpad gives Moodle learners a private place to respond to a focused reflection prompt. Teachers can review every submitted entry, award a grade, and provide individual feedback without leaving the activity.

The plugin supports Moodle 5.2 on the `MOODLE_502_STABLE` branch.

## What Scratchpad provides

- Individual student reflection entries.
- Prompts that teachers can tailor to a course or learning activity.
- A consolidated teacher report for reading and grading entries.
- Written feedback displayed with the student's reflection.
- Automatic activity completion after a learner submits an answer.
- A download mode for producing PDF reflections.
- Moodle Privacy API support for stored entries, feedback, ratings, and user identifiers.

## Learner experience

The activity keeps the prompt and the learner's response together, making the next action clear and keeping reflection work focused.

![A student viewing a completed Scratchpad reflection](images/student-reflection.jpg)

Students can return to edit their entry. Once a teacher has reviewed it, the grade and written feedback appear directly beneath the reflection.

![A student viewing a Scratchpad grade and teacher feedback](images/student-feedback.jpg)

## Course completion

Teachers can require a student to answer the activity before Moodle marks it complete. This allows Scratchpad to participate in course progress tracking and completion reports.

![A completed Scratchpad activity in a Moodle course](images/course-completion.jpg)

## Teacher experience

The entries report brings the student's response, grade selector, and written feedback field into one review workflow.

![A teacher grading a Scratchpad entry and writing feedback](images/teacher-grading.jpg)

The activity overview also shows how many entries are ready for review and links directly to the report.

![The Scratchpad teacher overview with a link to submitted entries](images/teacher-overview.jpg)

All people and reflection content shown in these screenshots are fictional demonstration data.

## Add Scratchpad to a course

1. Turn editing on in the Moodle course.
2. Select **Add an activity or resource**.
3. Choose **Scratchpad**.
4. Enter an activity name and reflection question.
5. Configure the mode, grading, availability, and completion conditions required by the course.
6. Save the activity and verify it with a student account.

## Privacy and data

Scratchpad stores student entry text, teacher feedback, ratings, and related user identifiers in Moodle. Its Privacy API provider supports metadata, export, and deletion requests. The plugin does not send this information to an external service.

Administrators should apply their normal Moodle retention, role, capability, backup, and privacy policies to Scratchpad activities.

## Support

- Issues and bug reports: [Pukunui Malaysia](https://pukunui.com/home/location/malaysia/).
- Subscription terms and support: [Pukunui Plugin Subscription Terms & Support Policy](https://pukunui.com/docs/policy-moodle-marketplace/).

Scratchpad is licensed under the GNU General Public License v3 or later.
