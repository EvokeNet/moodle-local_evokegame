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
 * Helper script to check user points in evokegame
 *
 * @package    local_evokegame
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_login();

// Only allow admins
require_capability('moodle/site:config', context_system::instance());

$email = optional_param('email', 'marcos-5@quanti.ca', PARAM_EMAIL);

// Get user by email
$user = $DB->get_record('user', ['email' => $email], '*', MUST_EXIST);

echo "<h1>User Points Check for: {$user->firstname} {$user->lastname} ({$user->email})</h1>";
echo "<p>User ID: {$user->id}</p>";

echo "<h2>1. Total Points by Course (evokegame_points)</h2>";
$points = $DB->get_records('evokegame_points', ['userid' => $user->id]);
if ($points) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Course ID</th><th>Course Name</th><th>Points</th><th>Created</th><th>Modified</th></tr>";
    foreach ($points as $point) {
        $course = $DB->get_record('course', ['id' => $point->courseid], 'shortname, fullname');
        $coursename = $course ? $course->fullname : "Course ID {$point->courseid}";
        echo "<tr>";
        echo "<td>{$point->id}</td>";
        echo "<td>{$point->courseid}</td>";
        echo "<td>{$coursename}</td>";
        echo "<td><strong>{$point->points}</strong></td>";
        echo "<td>" . userdate($point->timecreated) . "</td>";
        echo "<td>" . userdate($point->timemodified) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p><strong>No points found!</strong></p>";
}

echo "<h2>2. Skill Points by Module (evokegame_skills_users)</h2>";
$skillusers = $DB->get_records('evokegame_skills_users', ['userid' => $user->id]);
if ($skillusers) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Skill Module ID</th><th>Skill Name</th><th>Course Module</th><th>Points</th><th>Created</th></tr>";
    foreach ($skillusers as $su) {
        $skillmodule = $DB->get_record('evokegame_skills_modules', ['id' => $su->skillmoduleid], '*', IGNORE_MISSING);
        if ($skillmodule) {
            $skill = $DB->get_record('evokegame_skills', ['id' => $skillmodule->skillid], 'name', IGNORE_MISSING);
            $skillname = $skill ? $skill->name : "Skill ID {$skillmodule->skillid}";
            $cm = $DB->get_record('course_modules', ['id' => $skillmodule->cmid], '*', IGNORE_MISSING);
            $moduleinfo = $cm ? "CM {$skillmodule->cmid}" : "CM ID {$skillmodule->cmid}";
        } else {
            $skillname = "Unknown";
            $moduleinfo = "Unknown";
        }
        echo "<tr>";
        echo "<td>{$su->id}</td>";
        echo "<td>{$su->skillmoduleid}</td>";
        echo "<td>{$skillname}</td>";
        echo "<td>{$moduleinfo}</td>";
        echo "<td><strong>{$su->value}</strong></td>";
        echo "<td>" . userdate($su->timecreated) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p><strong>No skill points found!</strong></p>";
}

echo "<h2>3. Total Evocoins (evokegame_evcs)</h2>";
$evcs = $DB->get_record('evokegame_evcs', ['userid' => $user->id]);
if ($evcs) {
    echo "<p><strong>Total Evocoins: {$evcs->coins}</strong></p>";
    echo "<p>Created: " . userdate($evcs->timecreated) . "</p>";
    echo "<p>Modified: " . userdate($evcs->timemodified) . "</p>";
} else {
    echo "<p><strong>No evocoins found!</strong></p>";
}

echo "<h2>4. Evocoin Transactions (evokegame_evcs_transactions)</h2>";
$transactions = $DB->get_records('evokegame_evcs_transactions', ['userid' => $user->id], 'timecreated DESC', '*', 0, 20);
if ($transactions) {
    echo "<p>Showing last 20 transactions:</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Course</th><th>Source</th><th>Type</th><th>Action</th><th>Coins</th><th>Created</th></tr>";
    foreach ($transactions as $tx) {
        $course = $DB->get_record('course', ['id' => $tx->courseid], 'shortname', IGNORE_MISSING);
        $coursename = $course ? $course->shortname : "ID {$tx->courseid}";
        echo "<tr>";
        echo "<td>{$tx->id}</td>";
        echo "<td>{$coursename}</td>";
        echo "<td>{$tx->source}</td>";
        echo "<td>{$tx->sourcetype}</td>";
        echo "<td>{$tx->action}</td>";
        echo "<td><strong>{$tx->coins}</strong></td>";
        echo "<td>" . userdate($tx->timecreated) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p><strong>No transactions found!</strong></p>";
}

echo "<h2>5. Badges Awarded</h2>";
$badgeissued = $DB->get_records_sql(
    "SELECT bi.*, b.name, b.courseid 
       FROM {badge_issued} bi
       INNER JOIN {badge} b ON b.id = bi.badgeid
      WHERE bi.userid = :userid
      ORDER BY bi.dateissued DESC",
    ['userid' => $user->id]
);
if ($badgeissued) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Badge ID</th><th>Badge Name</th><th>Course ID</th><th>Type</th><th>Date Issued</th><th>Unique Hash</th></tr>";
    foreach ($badgeissued as $bi) {
        // Check if it's an evokegame badge
        $evokebadge = $DB->get_record('evokegame_badges', ['badgeid' => $bi->badgeid, 'courseid' => $bi->courseid]);
        $badgetype = $evokebadge ? '<strong style="color: green;">Evokegame Badge</strong>' : 'Moodle Badge';
        echo "<tr>";
        echo "<td>{$bi->badgeid}</td>";
        echo "<td>{$bi->name}</td>";
        echo "<td>{$bi->courseid}</td>";
        echo "<td>{$badgetype}</td>";
        echo "<td>" . userdate($bi->dateissued) . "</td>";
        echo "<td>{$bi->uniquehash}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p><strong>No badges awarded!</strong></p>";
}

echo "<h2>6. Evokegame Configuration Check</h2>";
// Check if evokegame is enabled in enrolled courses
$enrolledcourses = $DB->get_records_sql(
    "SELECT DISTINCT c.id, c.fullname, c.shortname
       FROM {course} c
       INNER JOIN {enrol} e ON e.courseid = c.id
       INNER JOIN {user_enrolments} ue ON ue.enrolid = e.id
       WHERE ue.userid = :userid
       ORDER BY c.fullname",
    ['userid' => $user->id]
);

if ($enrolledcourses) {
    foreach ($enrolledcourses as $course) {
        echo "<h3>Course: {$course->fullname} (ID: {$course->id})</h3>";
        
        // Check if evokegame is enabled (using the same method as the plugin)
        $gameenabled = \local_evokegame\util\game::is_enabled_in_course($course->id);
        $enabledstatus = $gameenabled ? '<span style="color: green;">✅ Enabled</span>' : '<span style="color: red;">❌ Disabled</span>';
        echo "<p><strong>Evokegame Status:</strong> {$enabledstatus}</p>";
        
        if (!$gameenabled) {
            echo "<p><em>Evokegame is not enabled in this course. Skills points won't be awarded.</em></p><br>";
            continue;
        }
        
        // Get all skills configured for this course
        $courseskills = $DB->get_records('evokegame_skills', ['courseid' => $course->id]);
        if ($courseskills) {
            echo "<p><strong>Course Skills:</strong></p><ul>";
            foreach ($courseskills as $skill) {
                echo "<li>{$skill->name} (ID: {$skill->id})</li>";
            }
            echo "</ul>";
        } else {
            echo "<p><em>No skills configured for this course.</em></p>";
        }
        
        // Get grade items for this course that are module-related
        $gradeitems = $DB->get_records_sql(
            "SELECT gi.id, gi.itemname, gi.itemmodule, gi.iteminstance, gi.scaleid, gi.grademax,
                    gg.id as grade_id, gg.finalgrade, gg.rawgrademax, gg.rawgrademin, 
                    gg.rawscaleid, gg.feedback, gg.timemodified, gg.usermodified,
                    cm.id as cmid, m.name as modulename
               FROM {grade_items} gi
               LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id AND gg.userid = :userid
               LEFT JOIN {course_modules} cm ON cm.instance = gi.iteminstance AND cm.module = 
                    (SELECT id FROM {modules} WHERE name = gi.itemmodule)
               LEFT JOIN {modules} m ON m.name = gi.itemmodule
              WHERE gi.courseid = :courseid 
                AND gi.itemtype = 'mod'
                AND (gg.id IS NOT NULL OR gi.hidden = 0)
              ORDER BY gi.itemname, gi.id",
            ['userid' => $user->id, 'courseid' => $course->id]
        );
        
        if ($gradeitems) {
            echo "<h4>Activities with Grades:</h4>";
            echo "<table border='1' cellpadding='5' style='margin-bottom: 20px;'>";
            echo "<tr><th>Activity</th><th>Module</th><th>Grade</th><th>Max</th><th>Scale</th><th>CM ID</th><th>Skills (Grading)</th><th>Points Added?</th></tr>";
            foreach ($gradeitems as $item) {
                $modulename = $item->modulename ? $item->modulename : 'Unknown';
                $itemname = $item->itemname ? $item->itemname : "{$modulename} #{$item->iteminstance}";
                
                $grade = $item->finalgrade !== null ? number_format($item->finalgrade, 2) : 'Not graded';
                $grademax = $item->rawgrademax ? number_format($item->rawgrademax, 2) : ($item->grademax ? number_format($item->grademax, 2) : 'N/A');
                
                // Check if scale exists
                $scaleinfo = '';
                if ($item->scaleid) {
                    $scale = $DB->get_record('scale', ['id' => $item->scaleid], 'name', IGNORE_MISSING);
                    $scaleinfo = $scale ? $scale->name : "Scale ID {$item->scaleid}";
                }
                
                $cmid = $item->cmid ? $item->cmid : 'N/A';
                
                // Check if skills are configured for this CM with action 'grading'
                $skillmodules = [];
                if ($cmid && $cmid != 'N/A') {
                    $skillmodules = $DB->get_records_sql(
                        "SELECT esm.id as skillmoduleid, esm.skillid, esm.value, es.name as skillname
                           FROM {evokegame_skills_modules} esm
                           INNER JOIN {evokegame_skills} es ON es.id = esm.skillid
                          WHERE esm.cmid = :cmid AND esm.action = 'grading'",
                        ['cmid' => $cmid]
                    );
                }
                
                $skillinfo = '';
                $pointsinfo = '';
                if ($skillmodules) {
                    $skillnames = [];
                    foreach ($skillmodules as $sm) {
                        $skillnames[] = "{$sm->skillname} ({$sm->value} pts)";
                        
                        // Check if points were already added for this skillmodule
                        $pointsadded = $DB->get_records('evokegame_skills_users', [
                            'userid' => $user->id,
                            'skillmoduleid' => $sm->skillmoduleid
                        ]);
                        if ($pointsadded) {
                            $pointsinfo = '<span style="color: green;">✅ Yes</span>';
                        }
                    }
                    $skillinfo = implode(', ', $skillnames);
                    if (!$pointsinfo) {
                        $pointsinfo = '<span style="color: red;">❌ No</span>';
                    }
                } else {
                    $skillinfo = '<span style="color: orange;">⚠️ No skills configured</span>';
                    $pointsinfo = 'N/A';
                }
                
                echo "<tr>";
                echo "<td>{$itemname}</td>";
                echo "<td>{$modulename}</td>";
                echo "<td><strong>{$grade}</strong></td>";
                echo "<td>{$grademax}</td>";
                echo "<td>{$scaleinfo}</td>";
                echo "<td>{$cmid}</td>";
                echo "<td>{$skillinfo}</td>";
                echo "<td>{$pointsinfo}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p><em>No grades found for this course.</em></p>";
        }
        
        echo "<br>";
    }
} else {
    echo "<p><strong>User is not enrolled in any courses!</strong></p>";
}

echo "<h2>7. Badge Notification Status</h2>";
echo str_repeat("-", 60) . "<br>";
$notification = new \local_evokegame\notification\badge($user->id);
$shouldbenotified = $notification->should_be_notified();
$notificationdata = $notification->get_notification_data();

if ($shouldbenotified && $notificationdata) {
    echo "<p><strong>✅ Notification Pending:</strong> User has a pending badge notification!</p>";
    echo "<p><strong>Evoke Badge ID:</strong> {$notificationdata}</p>";
    
    // Get badge details
    $evokebadge = $DB->get_record('evokegame_badges', ['id' => $notificationdata], '*', IGNORE_MISSING);
    if ($evokebadge) {
        echo "<p><strong>Badge Name:</strong> {$evokebadge->name}</p>";
        echo "<p><strong>Course ID:</strong> {$evokebadge->courseid}</p>";
        echo "<p><em>If the modal isn't showing, check browser console for JavaScript errors.</em></p>";
    }
} else {
    echo "<p><strong>❌ No pending notifications</strong></p>";
    echo "<p><em>If a badge was just awarded, the notification should be set. Check if:</em></p>";
    echo "<ul>";
    echo "<li>The badge was actually awarded (check badge_issued table)</li>";
    echo "<li>The deliver_badge() function was called</li>";
    echo "<li>The notification->notify() was called</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='?email=" . urlencode($email) . "'>Refresh</a> | <a href='/admin'>Back to Admin</a></p>";
