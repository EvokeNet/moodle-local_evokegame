/**
 * Delete badge js logic.
 *
 * @copyright  2021 World Bank Group <https://worldbank.org>
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 */

define(['jquery', 'core/ajax', 'core/str', 'local_evokegame/sweetalert'], function($, Ajax, Str, Swal) {
    var STRINGS = {
        CONFIRM_TITLE: 'Are you sure?',
        CONFIRM_MSG: 'Once delivered, the badge cannot be revoked!',
        CONFIRM_YES: 'Yes, deliver it!',
        CONFIRM_NO: 'Cancel',
        SUCCESS: 'Chapter successfully delivered.'
    };

    var componentStrings = [
        {
            key: 'deliverbadge_confirm_title',
            component: 'local_evokegame'
        },
        {
            key: 'deliverbadge_confirm_msg',
            component: 'local_evokegame'
        },
        {
            key: 'deliverbadge_confirm_yes',
            component: 'local_evokegame'
        },
        {
            key: 'deliverbadge_confirm_no',
            component: 'local_evokegame'
        },
        {
            key: 'deliverbadge_success',
            component: 'local_evokegame'
        },
    ];

    var DeliverBadge = function() {
        this.getStrings();

        $('body').append('<div id="evokegame-fullscreenloading"></div>');

        this.registerEventListeners();
    };

    DeliverBadge.prototype.getStrings = function() {
        var stringsPromise = Str.get_strings(componentStrings);

        $.when(stringsPromise).done(function(strings) {
            STRINGS.CONFIRM_TITLE = strings[0];
            STRINGS.CONFIRM_MSG = strings[1];
            STRINGS.CONFIRM_YES = strings[2];
            STRINGS.CONFIRM_NO = strings[3];
            STRINGS.SUCCESS = strings[4];
        });
    };

    DeliverBadge.prototype.registerEventListeners = function() {
        $("body").on("click", ".deliver-evokegame-badge", function(event) {
            event.preventDefault();

            var eventTarget = $(event.currentTarget);

            Swal.fire({
                title: STRINGS.CONFIRM_TITLE,
                text: STRINGS.CONFIRM_MSG,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: STRINGS.CONFIRM_YES,
                cancelButtonText: STRINGS.CONFIRM_NO
            }).then(function(result) {
                if (result.value) {
                    this.callDelivery(eventTarget);
                }
            }.bind(this));
        }.bind(this));
    };

    DeliverBadge.prototype.callDelivery = function(eventTarget) {
        $('#evokegame-fullscreenloading').show();

        var request = Ajax.call([{
            methodname: 'local_evokegame_deliverbadge',
            args: {
                badge: {
                    id: eventTarget.data('id')
                }
            }
        }]);

        request[0].done(function(response) {
            this.reloadPage(response.message);
        }.bind(this)).fail(function(error) {
            $('#evokegame-fullscreenloading').hide();

            var message = error.message;

            if (!message) {
                message = error.error;
            }

            this.showToast('error', message);
        }.bind(this));
    };

    DeliverBadge.prototype.reloadPage = function(message) {
        $('#evokegame-fullscreenloading').hide();

        this.showToast('success', message);
    };

    DeliverBadge.prototype.showToast = function(type, message) {
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
            return new DeliverBadge();
        }
    };
});