<?php
/**
 * @package    block_up1_course_logistics
 * @copyright  2021 Silecs & Université Paris1 Panthéon-Sorbonne
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'block/up1_course_logistics:myaddinstance' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'user' => CAP_ALLOW
        ],

        'clonepermissionsfrom' => 'moodle/my:manageblocks'
    ],

    'block/up1_course_logistics:addinstance' => [
        'riskbitmask' => RISK_SPAM | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => [
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ],

        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ],

    'block/up1_course_logistics:openarchived' => [
        'riskbitmask' => 0,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'manager' => CAP_ALLOW
        ],
    ],
);
