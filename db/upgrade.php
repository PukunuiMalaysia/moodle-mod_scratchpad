<?php

/**
 * Upgrade script
 *
 * @package mod_scratchpad
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/scratchpad/lib.php');

function xmldb_scratchpad_upgrade($oldversion=0) {
    global $DB;

    $dbman = $DB->get_manager();

    // No DB changes since 1.9.0.

    // Add scratchpad instances to the gradebook.
    if ($oldversion < 2010120300) {

        scratchpad_update_grades();

        upgrade_mod_savepoint(true, 2010120300, 'scratchpad');
    }

    // Change assessed field for grade.
    if ($oldversion < 2011040600) {

        // Rename field assessed on table scratchpad to grade.
        $table = new xmldb_table('scratchpad');
        $field = new xmldb_field('assessed', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'days');

        // Launch rename field grade.
        $dbman->rename_field($table, $field, 'grade');

        // Scratchpad savepoint reached.
        upgrade_mod_savepoint(true, 2011040600, 'scratchpad');
    }

    if ($oldversion < 2012032001) {

        // Changing the default of field rating on table scratchpad_entries to drop it.
        $table = new xmldb_table('scratchpad_entries');
        $field = new xmldb_field('rating', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'format');

        // Launch change of default for field rating.
        $dbman->change_field_default($table, $field);

        // Updating the non-marked entries with rating = NULL.
        $entries = $DB->get_records('scratchpad_entries', array('timemarked' => 0));
        if ($entries) {
            foreach ($entries as $entry) {
                $entry->rating = null;
                $DB->update_record('scratchpad_entries', $entry);
            }
        }

        // Scratchpad savepoint reached.
        upgrade_mod_savepoint(true, 2012032001, 'scratchpad');
    }

    return true;
}
