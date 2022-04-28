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
