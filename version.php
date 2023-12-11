<?php
/**
 * @package    block_up1_course_logistics
 * @copyright  2021 Silecs & Université Paris1 Panthéon-Sorbonne
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 20230216000;
$plugin->requires =  2020060900;
$plugin->component = 'block_up1_course_logistics';

$plugin->dependencies = [
    'local_crswizard' => 2021060600,
    'local_up1_courselist' => 2020100300,
    'local_up1_metadata' => 2020100300,
    'block_lightsynopsis' => 2020120800,
];
