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
 * 
 * Define all the backup steps that will be used by the backup_via_activity_task
 * Define the complete via structure for backup, with file and id annotations
 * 
 * @package    mod_via
 * @subpackage backup-moodle2
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete via structure for backup, with file and id annotations
 */
class backup_via_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
        global $CFG, $DB;

        // Get type of backup / duplication = course or activity.
        $basepath = $this->task->get_taskbasepath();
        $string = str_replace($CFG->dataroot.'/temp/backup/', '', $basepath);
        $backupid = strstr($string, '/activities/via', true);

        $controller = $DB->get_record('backup_controllers', array('backupid' => $backupid));

        if ($controller->type == "activity") {
            $userinfo = 1;
        } else {
            $userinfo = $this->get_setting_value('userinfo');
        }

        // Define each element separated.
        $via = new backup_nested_element('via', array('id'), array('course', 'name', 'intro', 'introformat', 'creator',
            'viaactivityid', 'datebegin', 'duration', 'presence', 'audiotype', 'recordingmode', 'recordmodebehavior',
            'isreplayallowed', 'private', 'profilid', 'activitytype', 'remindertime', 'needconfirmation', 'roomtype',
            'waitingroomaccessmode', 'activitystate', 'enroltype', 'isnewvia', 'showparticipants', 'mailed', 'sendinvite', 'invitemsg',
            'timecreated', 'timemodified', 'category', 'groupingid'));

        // Define sources.
        $via->set_source_table('via', array('id' => backup::VAR_ACTIVITYID));

        $participants = new backup_nested_element('participants');

        $participant = new backup_nested_element('participant', array('id'),
            array('enrolid', 'userid', 'participanttype', 'confirmationstatus'));

        $via->add_child($participants);
        $participants->add_child($participant);

        if ($userinfo) {
            $participant->set_source_sql('SELECT * FROM {via_participants} WHERE activityid = ?', array(backup::VAR_PARENTID));
        }

        // Define id annotations.
        $participant->annotate_ids('user', 'userid');

        // Define file annotations.
        $via->annotate_files('mod_via', 'intro', null); // This file areas haven't itemid
        $via->annotate_files('mod_via', 'content', null); // This file areas haven't itemid.

        // Return the root element (via), wrapped into standard activity structure.
        return $this->prepare_activity_structure($via);

    }
}
