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
 * Download functions
 *
 * @package mod_scratchpad
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir . '/pdflib.php');

$id = required_param('id', PARAM_INT);    // Course Module ID.

if (!$cm = get_coursemodule_from_id('scratchpad', $id)) {
    throw new moodle_exception('invalidcoursemodule', 'mod_scratchpad');
}

if (!$course = $DB->get_record('course', ['id' => $cm->course])) {
    throw new moodle_exception('invalidcourse', 'mod_scratchpad');
}
$categoryname = $DB->get_record("course_categories", ['id' => $course->category]);
$categoryname = $categoryname->name;
$coursename = $course->fullname;
$username = fullname($USER);

$context = context_module::instance($cm->id);

require_login($course, true, $cm);

if (!$scratchpad = $DB->get_record('scratchpad', ['id' => $cm->instance])) {
    throw new moodle_exception('invalidcoursemodule', 'mod_scratchpad');
}
// Retrieve course sections in display order.
if (!$cw = $DB->get_records('course_sections', ['course' => $cm->course], 'section')) {
    throw new moodle_exception('invalidcoursemodule', 'mod_scratchpad');
}

$moduleid = $DB->get_record("modules", ['name' => 'scratchpad']);

// Retrieve visible scratchpad modules in the course.
$moduleslist = $DB->get_records("course_modules", ['course' => $cm->course, 'module' => $moduleid->id]);

$modulesearch = [];
foreach ($moduleslist as $module) {
    if ($module->deletioninprogress || !$module->visible) {
        unset($moduleslist[$module->id]);
    } else {
        array_push($modulesearch, $module->id);
    }
}
// Sort items using the section name for display.
$item = [];
foreach ($cw as $section) {
    if (!isset($item[$section->section])) {
        $item[$section->section] = new stdclass();
    }
    if (empty($section->name)) {
        if ($section->section == 0) {
            $item[$section->section]->section_name = "General";
        } else {
            $item[$section->section]->section_name = "Topic " . (string)$section->section;
        }
    } else {
        $item[$section->section]->section_name = $section->name;
    }
    // Filter unrelated modules from the section sequence.
    if (!empty($section->sequence)) {
        if (strpos($section->sequence, ',') !== false) {
            $seq = [];
            $seq = explode(',', $section->sequence);
            $prep = [];
            foreach ($seq as $s) {
                if (in_array($s, $modulesearch)) {
                    array_push($prep, $s);
                }
            }
            if (empty($prep)) {
                // Remove unneeded sections.
                unset($item[$section->section]);
            } else {
                $item[$section->section]->sequence = $prep;
            }
        } else {
            if (in_array($section->sequence, $modulesearch)) {
                $item[$section->section]->sequence = [$section->sequence];
            } else {
                unset($item[$section->section]);
            }
        }
    } else {
        unset($item[$section->section]);
    }
}

$moduleid = $DB->get_record("modules", ['name' => 'scratchpad']);
// Retrieve visible scratchpad modules in course order.
$moduleslist = $DB->get_records("course_modules", ['course' => $cm->course, 'module' => $moduleid->id]);
$modulesearch = [];
$moduleinstance = [];
foreach ($moduleslist as $module) {
    if ($module->deletioninprogress || !$module->visible) {
        unset($moduleslist[$module->id]);
    } else {
        array_push($modulesearch, $module->id);
        $moduleinstance[$module->id] = $module->instance;
    }
}

$sp = $DB->get_records("scratchpad", ["course" => $course->id]);
foreach ($sp as $s) {
    if ($s->mode == 1) {
        unset($sp[$s->id]);
    }
}
ob_clean();
$doc = new pdf();
$doc->setPrintHeader(false);
$doc->setPrintFooter(false);
$doc->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

$doc->AddPage();

$html = '<h1>' . $categoryname . ':' . $coursename . '</h1>';
$html .= '<h4>' . get_string('name', 'scratchpad') . $username . '</h4>';

foreach ($item as $list) {
    $htmlsection = $htmlmodule = '';
    $sectioncount = 0;
    $htmlsection .= '<hr><h3>' . format_text($list->section_name, FORMAT_PLAIN) . '</h3>';
    foreach ($list->sequence as $l) {
        $sectioncount++;
        $obj = $sp[$moduleinstance[$l]];
        if (empty($obj)) {
            continue;
        }
        $pagetitle = $obj->name;
        $question = $obj->intro;
        $htmlmodule = '<strong><u>' . format_text($pagetitle, FORMAT_PLAIN) . '</u></strong><br>';
        $htmlmodule .= $question;

        $entry = $DB->get_record('scratchpad_entries', ['userid' => $USER->id, 'scratchpad' => $obj->id]);
        $text = format_text($entry->text, FORMAT_PLAIN);
        $htmlmodule .= '<p><em>' . $text . '</em></p>';

        if (!empty($htmlmodule)) {
            if ($sectioncount == 1) {
                $html .= $htmlsection;
            }
            $html .= $htmlmodule;
            $html .= '<br>';
        }
    }
}
// Output the HTML content.
$doc->writeHTML($html, true, false, true, false, '');

$doc->Output('Scratchpad - ' . $coursename . ' - ' . $username . '.pdf', 'D');
