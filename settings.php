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
 * Administrative settings
 *
 * @package    report_rolessitemap
 * @copyright  2022 Andreas Schenkel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/roles/lib.php');
require_login();

// Add a link in the menu reports.
// Users with capability report/rolessitemap:view' in systemcontext.
// Siteadmin has this capability by default because siteadmin has ALL capabilitys.
$ADMIN->add('reports', new admin_externalpage('reportrolessitemap', get_string('pluginname', 'report_rolessitemap'),
                                              "$CFG->wwwroot/report/rolessitemap/index.php", 'report/rolessitemap:view'));

$settings = new admin_settingpage('report_rolessitemap_settings', new lang_string('pluginname', 'report_rolessitemap'));

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox(
        'report_rolessitemap/isactive',
        get_string('isactive', 'report_rolessitemap'),
        get_string('configisactive', 'report_rolessitemap'),
        0
    ));

    $settings->add(new admin_setting_configcheckbox(
        'report_rolessitemap/isactiveforsiteadmin',
        get_string('isactiveforsiteadmin', 'report_rolessitemap'),
        get_string('configisactiveforsiteadmin', 'report_rolessitemap'),
        0
    ));

    GLOBAL $DB;
    $systemcontext = \context_system::instance();
    $roles = role_fix_names(get_all_roles(), $systemcontext, ROLENAME_ORIGINAL);
    foreach ($roles as $role) {
        $settings->add(new admin_setting_configcheckbox(
            'report_rolessitemap/' . 'roleid_' . $role->id,
            $role->shortname . ' - ' . $role->localname,
            '',
            1
        ));
    }

}
