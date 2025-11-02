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
 * CLI script to check user points in evokegame
 *
 * @package    local_evokegame
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');

$email = 'marcos-5@quanti.ca';

// Get user by email
$user = $DB->get_record('user', ['email' => $email], '*', MUST_EXIST);

echo "\n========================================\n";
echo "User Points Check for: {$user->firstname} {$user->lastname}\n";
echo "Email: {$user->email}\n";
echo "User ID: {$user->id}\n";
echo "========================================\n\n";

// 1. Check total points by course
echo "1. Total Points by Course (evokegame_points):\n";
echo str_repeat("-", 60) . "\n";
$points = $DB->get_records('evokegame_points', ['userid' => $user->id]);
if ($points) {
    foreach ($points as $point) {
        $course = $DB->get_record('course', ['id' => $point->courseid], 'shortname, fullname', IGNORE_MISSING);
        $coursename = $course ? $course->fullname : "Course ID {$point->courseid}";
        echo "  Course: {$coursename} (ID: {$point->courseid})\n";
        echo "  Points: {$point->points}\n";
        echo "  Created: " . date('Y-m-d H:i:s', $point->timecreated) . "\n";
        echo "  Modified: " . date('Y-m-d H:i:s', $point->timemodified) . "\n\n";
    }
} else {
    echo "  ❌ No points found!\n\n";
}

// 2. Check skill points by module
echo "2. Skill Points by Module (evokegame_skills_users):\n";
echo str_repeat("-", 60) . "\n";
$skillusers = $DB->get_records('evokegame_skills_users', ['userid' => $user->id]);
if ($skillusers) {
    echo "  Found " . count($skillusers) . " skill point entries:\n\n";
    foreach ($skillusers as $su) {
        $skillmodule = $DB->get_record('evokegame_skills_modules', ['id' => $su->skillmoduleid], '*', IGNORE_MISSING);
        if ($skillmodule) {
            $skill = $DB->get_record('evokegame_skills', ['id' => $skillmodule->skillid], 'name', IGNORE_MISSING);
            $skillname = $skill ? $skill->name : "Skill ID {$skillmodule->skillid}";
            $course = $DB->get_record_sql(
                "SELECT c.id, c.shortname, c.fullname 
                   FROM {course_modules} cm
                   JOIN {course} c ON c.id = cm.course
                  WHERE cm.id = :cmid",
                ['cmid' => $skillmodule->cmid],
                IGNORE_MISSING
            );
            $coursename = $course ? $course->fullname : "Unknown Course";
            echo "  Skill: {$skillname}\n";
            echo "  Course: {$coursename}\n";
            echo "  Points: {$su->value}\n";
            echo "  Created: " . date('Y-m-d H:i:s', $su->timecreated) . "\n\n";
        } else {
            echo "  Skill Module ID: {$su->skillmoduleid} (not found)\n";
            echo "  Points: {$su->value}\n\n";
        }
    }
} else {
    echo "  ❌ No skill points found!\n\n";
}

// 3. Check total evocoins
echo "3. Total Evocoins (evokegame_evcs):\n";
echo str_repeat("-", 60) . "\n";
$evcs = $DB->get_record('evokegame_evcs', ['userid' => $user->id]);
if ($evcs) {
    echo "  Total Evocoins: {$evcs->coins}\n";
    echo "  Created: " . date('Y-m-d H:i:s', $evcs->timecreated) . "\n";
    echo "  Modified: " . date('Y-m-d H:i:s', $evcs->timemodified) . "\n\n";
} else {
    echo "  ❌ No evocoins found!\n\n";
}

// 4. Check evocoin transactions
echo "4. Recent Evocoin Transactions (evokegame_evcs_transactions):\n";
echo str_repeat("-", 60) . "\n";
$transactions = $DB->get_records('evokegame_evcs_transactions', ['userid' => $user->id], 'timecreated DESC', '*', 0, 10);
if ($transactions) {
    echo "  Showing last 10 transactions:\n\n";
    foreach ($transactions as $tx) {
        $course = $DB->get_record('course', ['id' => $tx->courseid], 'shortname', IGNORE_MISSING);
        $coursename = $course ? $course->shortname : "ID {$tx->courseid}";
        echo "  Course: {$coursename} | Source: {$tx->source} ({$tx->sourcetype}) | Action: {$tx->action} | Coins: {$tx->coins}\n";
        echo "    Date: " . date('Y-m-d H:i:s', $tx->timecreated) . "\n\n";
    }
} else {
    echo "  ❌ No transactions found!\n\n";
}

// 5. Check badges awarded
echo "5. Badges Awarded:\n";
echo str_repeat("-", 60) . "\n";
$badgeissued = $DB->get_records_sql(
    "SELECT bi.*, b.name, b.courseid 
       FROM {badge_issued} bi
       INNER JOIN {badge} b ON b.id = bi.badgeid
      WHERE bi.userid = :userid
      ORDER BY bi.dateissued DESC",
    ['userid' => $user->id]
);
if ($badgeissued) {
    foreach ($badgeissued as $bi) {
        echo "  Badge: {$bi->name}\n";
        echo "  Course ID: {$bi->courseid}\n";
        echo "  Date Issued: " . date('Y-m-d H:i:s', $bi->dateissued) . "\n\n";
    }
} else {
    echo "  ❌ No badges awarded!\n\n";
}

// 6. Check if evokegame is enabled in any courses the user is enrolled in
echo "6. Courses with Evokegame Enabled (user enrollments):\n";
echo str_repeat("-", 60) . "\n";
$enrolledcourses = $DB->get_records_sql(
    "SELECT DISTINCT c.id, c.shortname, c.fullname
       FROM {course} c
       INNER JOIN {enrol} e ON e.courseid = c.id
       INNER JOIN {user_enrolments} ue ON ue.enrolid = e.id
       WHERE ue.userid = :userid
       ORDER BY c.fullname",
    ['userid' => $user->id]
);
if ($enrolledcourses) {
    foreach ($enrolledcourses as $course) {
        // Check if evokegame is enabled
        $game = $DB->get_record('local_evokegame', ['courseid' => $course->id], '*', IGNORE_MISSING);
        $enabled = $game ? "✅ Enabled" : "❌ Disabled";
        echo "  {$course->fullname} (ID: {$course->id}) - {$enabled}\n";
    }
} else {
    echo "  ❌ User is not enrolled in any courses!\n";
}

echo "\n========================================\n";
echo "Check complete!\n";
echo "========================================\n\n";
