<?php

/**
 * Configure course badges.
 *
 * @package     mod_evokeportfolio
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\notification;

defined('MOODLE_INTERNAL') || die();

/**
 * Evoke badge notification service.
 *
 * @package     mod_evokeportfolio
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badge {

    /** User preference prefix. */
    const USERPREF_NOTIFY = 'local_evokegame_notify_new_evoke_badge';

    protected $userid;

    /**
     * Constructor.
     *
     * @param int $courseid The course ID.
     */
    public function __construct($userid) {
        $this->userid = $userid;
    }

    /**
     * Flag the user as having been notified.
     *
     * @param int $userid The user ID.
     */
    public function mark_as_notified() {
        unset_user_preference(self::USERPREF_NOTIFY, $this->userid);
    }

    /**
     * Notify a user.
     *
     * @param int $userid The user ID.
     * @return void
     */
    public function notify($evokebadgeid) {
        set_user_preference(self::USERPREF_NOTIFY, $evokebadgeid, $this->userid);
    }

    /**
     * Whether the user should be notified.
     *
     * @param int $userid The user ID.
     * @return bool
     */
    public function should_be_notified() {
        return (bool) get_user_preferences(self::USERPREF_NOTIFY, false, $this->userid);
    }

    /**
     * Returns notification badge data.
     *
     * @return bool
     */
    public function get_notification_data() {
        return get_user_preferences(self::USERPREF_NOTIFY, false, $this->userid);
    }
}
