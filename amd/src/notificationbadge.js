/**
 * Delete badge js logic.
 *
 * @package    local_evokegame
 * @copyright  2021 World Bank Group <https://worldbank.org>
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 */

define([
    'jquery',
    'core/ajax',
    'core/config',
    'core/modal_factory',
    'local_evokegame/sweetalert',
    'local_evokegame/modal_notificationbadge'],
    function($, Ajax, Config, ModalFactory, Swal, ModalNotificationBadge) {

    var NotificationBadge = function() {
        this.checkIfHasNotification();
    };

    NotificationBadge.prototype.checkIfHasNotification = function() {
        var request = Ajax.call([{
            methodname: 'local_evokegame_checknotificationbadge',
            args: {}
        }]);

        request[0].done(function(result) {
            if (result.status == true) {
                ModalFactory.create({
                    type: ModalNotificationBadge.TYPE,
                    templateContext: {
                        isachievement: result.isachievement,
                        badgename: result.badgename,
                        badgeimage: result.badgeimage,
                        courseid: result.courseid,
                        profileurl: Config.wwwroot + '/local/evokegame/profile.php?id=' + result.courseid
                    }
                }).then(function(modal) {
                    return modal.show();
                });
            }
        }.bind(this)).fail(function(error) {
            var message = error.message;

            if (!message) {
                message = error.error;
            }
            this.showToast('error', message);
        }.bind(this));
    };

    NotificationBadge.prototype.showToast = function(type, message) {
        var Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 8000,
            timerProgressBar: true,
            onOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: type,
            title: message
        });
    };

    return {
        'init': function() {
            return new NotificationBadge();
        }
    };
});