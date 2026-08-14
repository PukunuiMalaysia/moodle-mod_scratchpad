# Scratchpad activity

Scratchpad is a focused reflection activity for Moodle 4.5. Teachers pose a question, each student maintains an individual response, and teachers can review, grade, and provide personal feedback. Students can also download their reflections when the activity uses PDF mode.

## Features

- Give learners a clear reflection prompt inside a Moodle course.
- Keep an individual Scratchpad response for each student.
- Let students revisit and refine their response.
- Review entries and provide grades and written feedback from one teacher report.
- Mark the activity complete automatically after a student submits an answer.
- Offer downloadable reflections when PDF mode is enabled.
- Support Moodle privacy export and deletion workflows.

## Screenshots

The screenshots below use fictional users and demonstration content from a Moodle 4.5 test course.

### A simple learner reflection workflow

Students see the reflection prompt and their response together in one focused activity.

![A student viewing a completed Scratchpad reflection](docs/public/images/student-reflection.jpg)

### Personal grades and feedback

Teacher feedback and the awarded grade appear directly below the student's reflection.

![A student viewing a Scratchpad grade and teacher feedback](docs/public/images/student-feedback.jpg)

### Moodle activity completion

Scratchpad integrates with Moodle completion conditions so course progress can update automatically after a response is submitted.

![A completed Scratchpad activity in a Moodle course](docs/public/images/course-completion.jpg)

### Efficient teacher review

Teachers can read an entry, choose a grade, and write feedback from the consolidated entries report.

![A teacher grading a Scratchpad entry and writing feedback](docs/public/images/teacher-grading.jpg)

### Clear access to submitted entries

The activity overview gives teachers direct access to all submitted Scratchpad entries.

![The Scratchpad teacher overview with a link to submitted entries](docs/public/images/teacher-overview.jpg)

## Requirements

- Moodle 4.5.x.
- PHP and database versions supported by Moodle 4.5.

This `MOODLE_405_STABLE` branch supports Moodle 4.5 only.

## Installation

1. Place the plugin directory at `mod/scratchpad` in the Moodle installation. The directory must be named `scratchpad`.
2. Visit **Site administration > Notifications**, or run `php admin/cli/upgrade.php` from the Moodle root.
3. Add a **Scratchpad** activity to a course and configure its question, availability, completion, and grading options.

The distributed ZIP is self-contained and does not require Composer, npm, or any other post-install build step.

## Privacy and data

Scratchpad stores student entry text, teacher feedback, ratings, and the related user identifiers in Moodle. Its Moodle Privacy API provider supports metadata, export, and deletion requests. The plugin does not send this data to an external service.

## Support and documentation

- Documentation: [Scratchpad user and administrator guide](docs/public/index.md).
- Issues and bug reports: [Pukunui Malaysia](https://pukunui.com/home/location/malaysia/).
- Subscription terms and support: [Pukunui Plugin Subscription Terms & Support Policy](https://pukunui.com/docs/policy-moodle-marketplace/).

## Author and licence

- Author: Vinny Stocker `<vinny@pukunui.com>` and the original contributors.
- Copyright: 2026 Pukunui Malaysia and the original copyright holders.
- Component: `mod_scratchpad`.
- Licence: GNU GPL v3 or later. See `LICENSE`.
