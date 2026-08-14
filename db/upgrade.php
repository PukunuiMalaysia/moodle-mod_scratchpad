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

/**
 * Performs mod_scratchpad database upgrades.
 *
 * @param int $oldversion Previously installed plugin version.
 * @return bool
 */
function xmldb_scratchpad_upgrade($oldversion = 0) {
    global $DB;

    if ($oldversion < 2021113007) {
        // Clean up legacy HTML stored in entry text.
        $search = ["<br />", "<br>", "><", '&nbsp;'];
        $replace  = ["\n", "\n", ">\n<", ' '];

        $rs = $DB->get_recordset_sql('SELECT id, text FROM {scratchpad_entries}', []);
        foreach ($rs as $record) {
            $newline = str_replace($search, $replace, $record->text);
            $newline = strip_tags(html_entity_decode($newline));
            $record->text = $newline;

            $DB->update_record('scratchpad_entries', $record);
        }
        $rs->close();

        upgrade_mod_savepoint(true, 2021113007, 'scratchpad');
    }
    return true;
}
