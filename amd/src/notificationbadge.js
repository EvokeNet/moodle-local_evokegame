/**
 * Delete badge js logic.
 *
 * @copyright  2021 World Bank Group <https://worldbank.org>
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 */

define([
    'jquery',
    'core/ajax',
    'core/config',
    'local_evokegame/sweetalert',
    'local_evokegame/modal_notificationbadge'],
    function($, Ajax, Config, Swal, ModalNotificationBadge) {

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
                // Generate a unique ID for the modal
                var uniqid = 'evokegame-badge-notification-' + Math.random().toString(36).substr(2, 9);

                ModalNotificationBadge.create({
                    templateContext: {
                        uniqid: uniqid,
                        isachievement: result.isachievement || false,
                        badgename: result.badgename || 'Badge',
                        badgeimage: result.badgeimage || '',
                        courseid: result.courseid || 0,
                        profileurl: Config.wwwroot + '/local/evokegame/profile.php?id=' +
                            (result.courseid || 0),
                        title: result.title || (result.isachievement ?
                            'You\'ve earned an achievement!' : 'You\'ve earned a badge!'),
                        description: result.description || '',
                        buttontext: result.buttontext || 'Check your scoreboard',
                        closetext: result.closetext || 'Close',
                        classes: 'evoke-game-badge-modal',
                        headerclasses: '',
                        footer: ''
                    },
                    large: true,
                    removeOnClose: true,
                    show: true
                }).catch(function(error) {
                    this.showToast('error', 'Failed to display badge notification');
                }.bind(this));
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