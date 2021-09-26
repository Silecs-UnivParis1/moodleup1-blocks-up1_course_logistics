<?php
/**
 * @package    block_up1_course_logistics
 * @copyright  2021 Silecs & Université Paris1 Panthéon-Sorbonne
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$studenthelp = new admin_setting_confightmleditor(
    'block_up1_course_logistics/studenthelp',
    'Bloc assistance étudiant',
    "Bloc HTML d'assistance pour les étudiants",
    ''
);
$settings->add($studenthelp);


