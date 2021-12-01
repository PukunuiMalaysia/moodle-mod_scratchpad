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
 * Scratchpad entries search.
 *
 * @package mod_scratchpad
 * @copyright  2016 David Monllao {@link http://www.davidmonllao.com}
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

namespace mod_scratchpad\search;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/scratchpad/lib.php');

function get_dynamic_parent_entry() {
    global $CFG;
    if (class_exists('\core_search\area\base_mod')) {
        return '\core_search\area\base_mod';
    } else {
        return '\core_search\base_mod';
    }
}
class_alias(get_dynamic_parent_entry(), '\mod_scratchpad\search\DynamicParentEntry');

/**
 * Scratchpad entries search.
 *
 * @package    mod_scratchpad
 * @copyright  2016 David Monllao {@link http://www.davidmonllao.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entry extends \mod_scratchpad\search\DynamicParentEntry {

    /**
     * Returns recordset containing required data for indexing scratchpad entries.
     *
     * @param int $modifiedfrom timestamp
     * @return moodle_recordset
     */
    public function get_recordset_by_timestamp($modifiedfrom = 0) {
        global $DB;

        $sql = "SELECT je.*, j.course FROM {scratchpad_entries} je
                JOIN {scratchpad} j ON j.id = je.scratchpad
                WHERE je.modified >= ? ORDER BY je.modified ASC";
        return $DB->get_recordset_sql($sql, array($modifiedfrom));
    }

    /**
     * Returns the documents associated with this scratchpad entry id.
     *
     * @param stdClass $entry scratchpad entry.
     * @param array    $options
     * @return \core_search\document
     */
    public function get_document($entry, $options = array()) {

        try {
            $cm = $this->get_cm('scratchpad', $entry->scratchpad, $entry->course);
            $context = \context_module::instance($cm->id);
        } catch (\dml_missing_record_exception $ex) {
            // Notify it as we run here as admin, we should see everything.
            debugging('Error retrieving mod_scratchpad ' . $entry->id . ' document, not all required data is available: ' .
                $ex->getMessage(), DEBUG_DEVELOPER);
            return false;
        } catch (\dml_exception $ex) {
            // Notify it as we run here as admin, we should see everything.
            debugging('Error retrieving mod_scratchpad' . $entry->id . ' document: ' . $ex->getMessage(), DEBUG_DEVELOPER);
            return false;
        }

        // Prepare associative array with data from DB.
        $doc = \core_search\document_factory::instance($entry->id, $this->componentname, $this->areaname);

        // Not a nice solution to copy a subset of the content but I don't want
        // to use a kind of "Firstname Lastname scratchpad entry"
        // because of i18n (the entry can be searched by both the student and
        // any course teacher (they all have different languages).
        $doc->set('title', shorten_text(content_to_text($entry->text, $entry->format), 50));
        $doc->set('content', content_to_text($entry->text, $entry->format));
        $doc->set('contextid', $context->id);
        $doc->set('courseid', $entry->course);
        $doc->set('userid', $entry->userid);
        $doc->set('owneruserid', \core_search\manager::NO_OWNER_ID);
        $doc->set('modified', $entry->modified);

        // Check if this document should be considered new.
        if (isset($options['lastindexedtime']) && ($options['lastindexedtime'] < $entry->modified)) {
            // If the document was created after the last index time, it must be new.
            $doc->set_is_new(true);
        }

        return $doc;
    }

    /**
     * Whether the user can access the document or not.
     *
     * @throws \dml_missing_record_exception
     * @throws \dml_exception
     * @param int $id Glossary entry id
     * @return bool
     */
    public function check_access($id) {
        global $USER;

        try {
            $entry = $this->get_entry($id);
            $cminfo = $this->get_cm('scratchpad', $entry->scratchpad, $entry->course);
        } catch (\dml_missing_record_exception $ex) {
            return \core_search\manager::ACCESS_DELETED;
        } catch (\dml_exception $ex) {
            return \core_search\manager::ACCESS_DENIED;
        }

        if (!$cminfo->uservisible) {
            return \core_search\manager::ACCESS_DENIED;
        }

        if ($entry->userid != $USER->id && !has_capability('mod/scratchpad:manageentries', $cminfo->context)) {
            return \core_search\manager::ACCESS_DENIED;
        }

        return \core_search\manager::ACCESS_GRANTED;
    }

    /**
     * Link to scratchpad entry.
     *
     * @param \core_search\document $doc
     * @return \moodle_url
     */
    public function get_doc_url(\core_search\document $doc) {
        global $USER;

        $contextmodule = \context::instance_by_id($doc->get('contextid'));

        $entryuserid = $doc->get('userid');
        if ($entryuserid == $USER->id) {
            $url = '/mod/scratchpad/view.php';
        } else {
            // Teachers see student's entries in the report page.
            $url = '/mod/scratchpad/report.php#entry-' . $entryuserid;
        }
        return new \moodle_url($url, array('id' => $contextmodule->instanceid));
    }

    /**
     * Link to the scratchpad.
     *
     * @param \core_search\document $doc
     * @return \moodle_url
     */
    public function get_context_url(\core_search\document $doc) {
        $contextmodule = \context::instance_by_id($doc->get('contextid'));
        return new \moodle_url('/mod/scratchpad/view.php', array('id' => $contextmodule->instanceid));
    }

    /**
     * Returns the specified scratchpad entry checking the internal cache.
     *
     * Store minimal information as this might grow.
     *
     * @throws \dml_exception
     * @param int $entryid
     * @return stdClass
     */
    protected function get_entry($entryid) {
        global $DB;

        return $DB->get_record_sql("SELECT je.*, j.course FROM {scratchpad_entries} je
                                    JOIN {scratchpad} j ON j.id = je.scratchpad
                                    WHERE je.id = ?", array('id' => $entryid), MUST_EXIST);
    }
}
