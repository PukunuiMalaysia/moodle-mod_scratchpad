# Scratchpad activity

Scratchpad is a reflection activity for Moodle 4.5. Teachers can pose a question, students can maintain an individual response, and teachers can review and provide feedback. Students can also download their reflections in PDF mode.

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

- Documentation: this `README.md` file.
- Issues and bug reports: [Pukunui Malaysia](https://pukunui.com/home/location/malaysia/).
- Subscription terms and support: [Pukunui Plugin Subscription Terms & Support Policy](https://pukunui.com/docs/policy-moodle-marketplace/).

## Author and licence

- Author: Vinny Stocker `<vinny@pukunui.com>` and the original contributors.
- Copyright: 2026 Pukunui Malaysia and the original copyright holders.
- Component: `mod_scratchpad`.
- Licence: GNU GPL v3 or later. See `LICENSE`.
