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
 * Version information
 *
 * @package    report_rolessitemap
 * @copyright  2022 Andreas Schenkel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_rolessitemap;
use moodle_url;
use report_rolessitemap\Misc;

defined('MOODLE_INTERNAL') || die();

class Helper {
    /**
     * Show all roles that exists in this moodle
     * This function renders ALL existing roles not only rows that can be assigned to categories.
     *
     * @return void
     * @throws \dml_exception
     */
    public function renderallroles(): void {
        GLOBAL $DB, $OUTPUT;
        $roles = $DB->get_records('role', null, 'id', 'id, shortname');
        $data['allRoles'] = $this->convertstdclasstoarray($roles);
        echo $OUTPUT->render_from_template('report_rolessitemap/allRoles', $data);
    }


    /**
     * Shows all categories and the assigned users with their roles
     *
     * @return void
     * @throws \dml_exception
     */
    public function rendercategoricesandroles(): void {
        GLOBAL $DB, $OUTPUT;
        $roles = $DB->get_records('role', null, 'id', 'id, shortname');
        $categorieslist = \core_course_category::make_categories_list();
        // Now populate $categorieslistandroles with the information to be rendered using mustach-template.
        $categorieslistandroles = [];
        foreach ($categorieslist as $categoryid => $categoryname) {
            $context = \context_coursecat::instance($categoryid);
            $params = array('contextid' => $context->id);
            $roleassignments = $DB->get_records('role_assignments', $params, 'contextid, roleid, userid', 'id, contextid, roleid, userid');
            $roleassignmentsasarray = [];
            $oldshortname = "";
            foreach ($roleassignments as $roleassignment) {
                $counter = false;
                if (!in_array($roleassignment->roleid, $this->get_supported_roles())) {
                    continue;
                }
                if ($roles[$roleassignment->roleid]->shortname != $oldshortname) {
                    $oldshortname = $roles[$roleassignment->roleid]->shortname;
                    $counter = true;
                }
                $roleassignmenteditingurl = new moodle_url('/admin/roles/assign.php',
                    array('contextid' => $context->id , 'roleid' => $roleassignment->roleid));
                $userprofilgurl = new moodle_url('/user/profile.php',
                    array('userid' => $roleassignment->userid));
                $user = \core_user::get_user($roleassignment->userid,  'username, firstname, lastname');
                $roleassignmentsasarray[] = [
                    "counter" => $counter,
                    "roleid" => $roleassignment->roleid,
                    'roleassignmenteditingurl' => $roleassignmenteditingurl->out(false),
                    "shortname" => $roles[$roleassignment->roleid]->shortname,
                    "userid" => $roleassignment->userid,
                    "userprofilgurl" => $userprofilgurl->out(false),
                    "username" => fullname($user)
                ];
            }
            $url = new moodle_url('/course/index.php', array('categoryid' => $categoryid));
            $categorieslistandroles[] = [
                'categoryid' => "$categoryid",
                'categoryname' => "$categoryname",
                'url' => $url,
                'roleassignmentsinthiscategory' => $roleassignmentsasarray
            ];
        }
        $data['categorieslistandroles'] = $categorieslistandroles;
        $translations['header'] = Misc::translate(['page'], 'report_rolessitemap', 'header.');
        $data['translations'] = $translations;
        echo $OUTPUT->render_from_template('report_rolessitemap/categorieslistandroles', $data);
    }

    /**
     * Converts an array of stdClass objects in an array of arrays
     *
     * @param $roles
     * @return array
     */
    public function convertstdclasstoarray(array $roles): array {
        $rolesasarray = [];
        foreach ($roles as $role) {
            $rolesasarray[] = (array)$role;
        }
        return $rolesasarray;
    }

    /**
     * Gets the selected roles that should be supported in this report.
     *
     * @return array
     * @throws \dml_exception
     */
    public function get_supported_roles(): array {
        GLOBAL $DB;
        $roles = $DB->get_records('role', null, 'id', 'id');
        $supportedroles = [];
        foreach ($roles as $role) {
            if (get_config('report_rolessitemap', 'roleid_' . $role->id)) {
                $supportedroles[] = $role->id;
            }
        }
        return $supportedroles;
    }
}
