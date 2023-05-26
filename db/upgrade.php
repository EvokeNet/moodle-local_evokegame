<?php

/**
 * Upgrade file.
 *
 * @package    mod_evokeportfolio
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Upgrade code for the evokegame local plugin.
 *
 * @param int $oldversion - the version we are upgrading from.
 *
 * @return bool result
 *
 * @throws ddl_exception
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_local_evokegame_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2021111000) {
        $dbman = $DB->get_manager();

        $table = new xmldb_table('evokegame_badges');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('badgeid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);
        $table->add_key('fk_courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $table->add_key('fk_badgeid', XMLDB_KEY_FOREIGN, array('badgeid'), 'badge', array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2021111000, 'local', 'evokegame');
    }

    if ($oldversion < 2021111500) {
        $dbman = $DB->get_manager();

        $table = new xmldb_table('evokegame_badges_criterias');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('evokebadgeid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('method', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->add_field('target', XMLDB_TYPE_CHAR, '255');
        $table->add_field('value', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('extras', XMLDB_TYPE_TEXT);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);
        $table->add_key('fk_courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $table->add_key('fk_evokebadgeid', XMLDB_KEY_FOREIGN, array('evokebadgeid'), 'evokegame_badges', array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2021111500, 'local', 'evokegame');
    }

    if ($oldversion < 2021112100) {
        $dbman = $DB->get_manager();

        $table = new xmldb_table('evokegame_badges');

        if ($dbman->table_exists($table)) {
            $field = new xmldb_field('type', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1', 'badgeid');

            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2021112100, 'local', 'evokegame');
    }

    if ($oldversion < 2021112200) {
        $dbman = $DB->get_manager();

        $table = new xmldb_table('evokegame_evcs');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('coins', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);
        $table->add_key('fk_userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('evokegame_evcs_transactions');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('source', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->add_field('sourcetype', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->add_field('sourceid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('coins', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('action', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);
        $table->add_key('fk_courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $table->add_key('fk_userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2021112200, 'local', 'evokegame');
    }

    if ($oldversion < 2022071700) {
        $dbman = $DB->get_manager();

        $table = new xmldb_table('evokegame_badges');

        if ($dbman->table_exists($table)) {
                $field = new xmldb_field('highlight', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'name');

            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2022071700, 'local', 'evokegame');
    }

    if ($oldversion < 2023051500) {
        $dbman = $DB->get_manager();

        $table = new xmldb_table('evokegame_evcs_modules');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('value', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id'], null, null);
        $table->add_key('fk_cmid', XMLDB_KEY_FOREIGN, ['cmid'], 'course_modules', ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2023051500, 'local', 'evokegame');
    }

    return true;
}