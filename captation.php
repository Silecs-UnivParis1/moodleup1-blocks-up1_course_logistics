<?php
/**
 * @package    block_up1_course_logistics
 * @copyright  2021 Silecs & Université Paris1 Panthéon-Sorbonne
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('../../local/up1_metadata/lib.php');

global $CFG, $DB;
$courseid = required_param('courseid', PARAM_INT);
$isblocked = required_param('blocked', PARAM_INT);
$returnurl = $_SERVER['HTTP_REFERER'];

$context = context_course::instance($courseid);
if ($data = data_submitted() and confirm_sesskey()) {
    $context = context_course::instance($data->courseid);
    if (has_capability('moodle/course:update', $context)) {
        if (!$course = $DB->get_record('course', ['id' => $data->courseid])) {
            error('Course ID was incorrect');
        } else {
            $id_metadata = up1_meta_get_id($courseid, 'up1bloquercaptation');

            $block = !$isblocked;
            if (!$id_metadata)  {
                $fieldid = $DB->get_record('customfield_field', ['shortname' =>'up1bloquercaptation'], '*', MUST_EXIST);
                $DB->insert_record('customfield_data', ['instanceid' => $course->id,'fieldid' => $fieldid->id , 'intvalue' =>  $block, 'value' =>  $block,'valueformat' => 0, 'timemodified' => time(),'timecreated' => time(), 'contextid' => $context->id]);
            }
            else   $DB->update_record('customfield_data',   ['id'=> $id_metadata , 'instanceid' => $course->id,'intvalue' =>  $block,'value' =>  $block,'timemodified' => time()]);
            
            $DB->update_record('course',  ['id'=> $courseid ,'timemodified' => time()]);
        }
    }
}
redirect($returnurl);
