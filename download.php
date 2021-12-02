<?php

/**
 * Download functions
 *
 * @package mod_scratchpad
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @copyright  2021 Tengku Alauddin - din@pukunui.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once("../../config.php");
require_once("lib.php");
require_once($CFG->libdir . '/pdflib.php');

function cmp($a, $b){
    return strcmp($a->section, $b->section);
}

$id = required_param('id', PARAM_INT);    // Course Module ID.

if (! $cm = get_coursemodule_from_id('scratchpad', $id)) {
    print_error("Course Module ID was incorrect");
}

if (! $course = $DB->get_record("course", array('id' => $cm->course))) {
    print_error("Course is misconfigured");
}
$categoryname = $DB->get_record("course_categories", array('id' => $course->category));
$categoryname = $categoryname->name;
$coursename = $course->fullname;
$username = $USER->firstname . ' ' . $USER->lastname;

$context = context_module::instance($cm->id);

require_login($course, true, $cm);

if (! $scratchpad = $DB->get_record("scratchpad", array("id" => $cm->instance))) {
    print_error("Course module is incorrect");
}
//Retrieve sections from course, sort by section and retrieve course module ids
if (! $cw = $DB->get_records("course_sections", array('course' => $cm->course))) {
    print_error("Course module is incorrect");
}

$moduleid = $DB->get_record("modules", array('name' => 'scratchpad'));

//Retrieve scratchpad modules in the course, remove deleted/hiddden
//build $modulesearch for $item 
$moduleslist = $DB->get_records("course_modules", array('course' => $cm->course, 'module' => $moduleid->id));

$modulesearch = array();
foreach ($moduleslist as $module){
    if ($module->deletioninprogress || !$module->visible){
        unset ($moduleslist[$module->id]);
    }else{
        array_push($modulesearch, $module->id);
    }
}
//$item is sorted using section name for display.
$item = array();
foreach ($cw as $section){
    if (empty($section->name)){
        if ($section->section == 0){
            $item[$section->section]->section_name="General";
        }else{
            $item[$section->section]->section_name="Topic " . (string)$section->section;            
        }
    }else{
        $item[$section->section]->section_name=$section->name;
    }
    // Filter out unrelated modules in sequence
    if (!empty($section->sequence)){
        if (strpos($section->sequence, ',') !== false){
            $seq = array();
            $seq = explode(',',$section->sequence);
            $prep = array();
            foreach ($seq as $s){
                if (in_array($s, $modulesearch)){
                    array_push($prep, $s);
                }
            }
            if (empty($prep)){
                // Remove unneeded section
                unset($item[$section->section]);
            }else{
                $item[$section->section]->sequence = $prep;
            }
        }else{
            if (in_array($section->sequence, $modulesearch)){
                $item[$section->section]->sequence=[$section->sequence];
            }else{
                unset($item[$section->section]);
            }
        }
    }else{
        unset($item[$section->section]);
    }
}

$moduleid = $DB->get_record("modules", array('name' => 'scratchpad'));
//Retrieve scratchpad modules in the course, remove deleted/hiddden and order by
$moduleslist = $DB->get_records("course_modules", array('course' => $cm->course, 'module' => $moduleid->id));
$modulesearch = array(); 
$moduleinstance = array();
foreach ($moduleslist as $module){
    if ($module->deletioninprogress || !$module->visible){
        unset ($moduleslist[$module->id]);
    }else{
        array_push($modulesearch, $module->id);
        $moduleinstance[$module->id] = $module->instance;
    }
}

usort($cw, 'cmp');
$sp = $DB->get_records("scratchpad", array("course" => $course->id));
foreach ($sp as $s){
    if ($s->mode == 1){
        unset($sp[$s->id]);
    }
}
ob_clean();
$doc = new pdf();
$doc->setPrintHeader(false);
$doc->setPrintFooter(false);
$doc->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

$doc->AddPage();

$html = '<h1>' . $categoryname . ':' . $coursename . '</h1>';
$html .= '<h4>Name: ' . $username.'</h4>';

foreach ($item as $list){
    $htmlsection = $htmlmodule = '';
    $section_count = 0;
    $htmlsection .= '<hr><h3>'. $list->section_name . '</h3>';
    foreach ($list->sequence as $l){
        $section_count++;
        $obj = $sp[$moduleinstance[$l]];
        if (empty($obj)){
            continue;
        }
        $pagetitle = $obj->name;
        $question = $obj->intro;
        $htmlmodule = '<strong><u>'.$pagetitle.'</u></strong><br>';
        $htmlmodule .= $question;
        
        $entry = $DB->get_record('scratchpad_entries', array('userid' => $USER->id, 'scratchpad' => $obj->id));
        $text = $entry->text;
        $htmlmodule .= '<p><em>'.$text.'</em></p>';
        
        if (!empty($htmlmodule)){
            if ($section_count == 1){
                $html .= $htmlsection;                
            }
            $html .= $htmlmodule;
            $html .='<br>';
        }
    }
}
// output the HTML content
$doc->writeHTML($html, true, false, true, false, '');

$doc->Output('Scratchpad - '.$coursename.' - '.$username.'.pdf', 'D');