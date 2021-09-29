<?php
/**
 * @package    block_up1_course_logistics
 * @copyright  2021 Silecs & Université Paris1 Panthéon-Sorbonne
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$settings->add(new admin_setting_heading('block_up1_course_logistics/headteacher', get_string('teacherview', 'block_up1_course_logistics'), ''));

$urlapofee = new admin_setting_configtext(
  'block_up1_course_logistics/urlapogee',
  'URL du lien code Apogée',
  "URL du lien permettant de trouver un code Apogée",
  'https://se-apogee.univ-paris1.fr/comp.php'
);
$settings->add($urlapofee);

$teacherhelp = new admin_setting_confightmleditor(
  'block_up1_course_logistics/teacherhelp',
  'Bloc assistance enseignant',
  "Bloc HTML d'assistance pour les enseignants",
  ''
);
$settings->add($teacherhelp);

$settings->add(new admin_setting_heading('block_up1_course_logistics/headstudent', get_string('studentview', 'block_up1_course_logistics'), ''));

$studenthelp = new admin_setting_confightmleditor(
  'block_up1_course_logistics/studenthelp',
  'Bloc assistance étudiant',
  "Bloc HTML d'assistance pour les étudiants",
  ''
);
$settings->add($studenthelp);
