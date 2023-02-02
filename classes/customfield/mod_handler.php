<?php

/**
 * Course handler for custom fields
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\customfield;

defined('MOODLE_INTERNAL') || die;

use core_customfield\api;
use core_customfield\field_controller;

/**
 * Course handler for custom fields
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class mod_handler extends \core_customfield\handler {

    /**
     * @var mod_handler
     */
    static protected $singleton;

    /**
     * @var \context
     */
    protected $parentcontext;

    /**
     * Returns a singleton
     *
     * @param int $itemid
     * @return \core_mod\customfield\mod_handler
     */
    public static function create(int $itemid = 0) : \core_customfield\handler {
        if (static::$singleton === null) {
            self::$singleton = new static(0);
        }
        return self::$singleton;
    }

    /**
     * Run reset code after unit tests to reset the singleton usage.
     */
    public static function reset_caches(): void {
        if (!PHPUNIT_TEST) {
            throw new \coding_exception('This feature is only intended for use in unit tests');
        }

        static::$singleton = null;
    }

    /**
     * The current user can configure custom fields on this component.
     *
     * @return bool true if the current can configure custom fields, false otherwise
     */
    public function can_configure() : bool {
        return has_capability('moodle/course:configurecustomfields', $this->get_configuration_context());
    }

    /**
     * The current user can edit custom fields on the given course.
     *
     * @param field_controller $field
     * @param int $instanceid id of the course to test edit permission
     * @return bool true if the current can edit custom fields, false otherwise
     */
    public function can_edit(field_controller $field, int $instanceid = 0) : bool {
        if ($instanceid) {
            $context = $this->get_instance_context($instanceid);
            return (!$field->get_configdata_property('locked') ||
                    has_capability('moodle/course:changelockedcustomfields', $context));
        } else {
            $context = $this->get_parent_context();
            return (!$field->get_configdata_property('locked') ||
                guess_if_creator_will_have_course_capability('moodle/course:changelockedcustomfields', $context));
        }
    }

    /**
     * The current user can view custom fields on the given course.
     *
     * @param field_controller $field
     * @param int $instanceid id of the course to test edit permission
     * @return bool true if the current can edit custom fields, false otherwise
     */
    public function can_view(field_controller $field, int $instanceid) : bool {
        return true;
    }

    /**
     * Sets parent context for the course
     *
     * This may be needed when course is being created, there is no course context but we need to check capabilities
     *
     * @param \context $context
     */
    public function set_parent_context(\context $context) {
        $this->parentcontext = $context;
    }

    /**
     * Returns the parent context for the course
     *
     * @return \context
     */
    protected function get_parent_context() : \context {
        global $PAGE;
        if ($this->parentcontext) {
            return $this->parentcontext;
        } else if ($PAGE->context && $PAGE->context instanceof \context_coursecat) {
            return $PAGE->context;
        }
        return \context_system::instance();
    }

    /**
     * Context that should be used for new categories created by this handler
     *
     * @return \context the context for configuration
     */
    public function get_configuration_context() : \context {
        return \context_system::instance();
    }

    /**
     * URL for configuration of the fields on this handler.
     *
     * @return \moodle_url The URL to configure custom fields for this component
     */
    public function get_configuration_url() : \moodle_url {
        return new \moodle_url('/local/evokegame/customfield.php');
    }

    /**
     * Returns the context for the data associated with the given instanceid.
     *
     * @param int $instanceid id of the record to get the context for
     * @return \context the context for the given record
     */
    public function get_instance_context(int $instanceid = 0) : \context {
        if ($instanceid > 0) {
            return \context_module::instance($instanceid);
        } else {
            return \context_system::instance();
        }
    }

    /**
     * Allows to add custom controls to the field configuration form that will be saved in configdata
     *
     * @param \MoodleQuickForm $mform
     */
    public function config_form_definition(\MoodleQuickForm $mform) {
        $mform->addElement('header', 'mod_handler_header', get_string('customfieldsettings', 'core_course'));
        $mform->setExpanded('mod_handler_header', true);

        // If field is locked.
        $mform->addElement('selectyesno', 'configdata[locked]', get_string('customfield_islocked', 'core_course'));
        $mform->addHelpButton('configdata[locked]', 'customfield_islocked', 'core_course');

        $options = array('multiple' => true, 'includefrontpage' => false);
        $mform->addElement('course', 'configdata[availableincourses]', get_string('courses'), $options);
    }

    /**
     * Creates or updates custom field data.
     *
     * @param \restore_task $task
     * @param array $data
     */
    public function restore_instance_data_from_backup(\restore_task $task, array $data) {
        $courseid = $task->get_courseid();
        $context = $this->get_instance_context($courseid);
        $editablefields = $this->get_editable_fields($courseid);
        $records = api::get_instance_fields_data($editablefields, $courseid);
        $target = $task->get_target();
        $override = ($target != \backup::TARGET_CURRENT_ADDING && $target != \backup::TARGET_EXISTING_ADDING);

        foreach ($records as $d) {
            $field = $d->get_field();
            if ($field->get('shortname') === $data['shortname'] && $field->get('type') === $data['type']) {
                if (!$d->get('id') || $override) {
                    $d->set($d->datafield(), $data['value']);
                    $d->set('value', $data['value']);
                    $d->set('valueformat', $data['valueformat']);
                    $d->set('contextid', $context->id);
                    $d->save();
                }
                return;
            }
        }
    }

    /**
     * Set up page customfield/edit.php
     *
     * @param field_controller $field
     * @return string page heading
     */
    public function setup_edit_page(field_controller $field) : string {
        global $CFG, $PAGE;

        require_once($CFG->libdir.'/adminlib.php');

        $title = parent::setup_edit_page($field);

        admin_externalpage_setup('local_evokegame');

        $PAGE->navbar->add($title);

        return $title;
    }

    /**
     * Returns list of fields defined for this instance as an array (not groupped by categories)
     *
     * Fields are sorted in the same order they would appear on the instance edit form
     *
     * Note that this function returns all fields in all categories regardless of whether the current user
     * can view or edit data associated with them
     *
     * @return field_controller[]
     */
    public function get_fields() : array {
        global $COURSE;

        $categories = $this->get_categories_with_fields();
        $fields = [];
        foreach ($categories as $category) {
            foreach ($category->get_fields() as $field) {
                $courses = $field->get_configdata_property('availableincourses');

                if (!empty($courses) && !in_array($COURSE->id, $courses)) {
                    continue;
                }

                $fields[$field->get('id')] = $field;
            }
        }
        return $fields;
    }

    /**
     * Adds custom fields to instance editing form
     *
     * Example:
     *   public function definition() {
     *     // ... normal instance definition, including hidden 'id' field.
     *     $handler->instance_form_definition($this->_form, $instanceid);
     *     $this->add_action_buttons();
     *   }
     *
     * @param \MoodleQuickForm $mform
     * @param int $instanceid id of the instance, can be null when instance is being created
     * @param string $headerlangidentifier If specified, a lang string will be used for field category headings
     * @param string $headerlangcomponent
     */
    public function instance_form_definition(\MoodleQuickForm $mform, int $instanceid = 0,
                                             ?string $headerlangidentifier = null, ?string $headerlangcomponent = null) {

        $editablefields = $this->get_editable_fields($instanceid);
        $fieldswithdata = api::get_instance_fields_data($editablefields, $instanceid);
        $lastcategoryid = null;
        foreach ($fieldswithdata as $data) {
            $categoryname = $data->get_field()->get_category()->get('name');
            $evokemodules = ['mod_evokeportfolio_mod_form', 'mod_portfoliobuilder_mod_form', 'mod_portfoliogroup_mod_form'];

            if (!in_array($mform->_formName, $evokemodules) && (strtolower($categoryname) != 'evocoins')) {
                continue;
            }

            $categoryid = $data->get_field()->get_category()->get('id');
            if ($categoryid != $lastcategoryid) {
                $categoryname = format_string($data->get_field()->get_category()->get('name'));

                // Load category header lang string if specified.
                if (!empty($headerlangidentifier)) {
                    $categoryname = get_string($headerlangidentifier, $headerlangcomponent, $categoryname);
                }

                $mform->addElement('header', 'category_' . $categoryid, $categoryname);
                $lastcategoryid = $categoryid;
            }
            $data->instance_form_definition($mform);
            $field = $data->get_field()->to_record();
            if (strlen($field->description)) {
                // Add field description.
                $context = $this->get_configuration_context();
                $value = file_rewrite_pluginfile_urls($field->description, 'pluginfile.php',
                    $context->id, 'core_customfield', 'description', $field->id);
                $value = format_text($value, $field->descriptionformat, ['context' => $context]);
                $mform->addElement('static', 'customfield_' . $field->shortname . '_static', '', $value);
            }
        }
    }
}
