/**
 * Deliver skill points to selected users.
 *
 * @package     local_evokegame
 * @copyright   2026
 */

define([
    'jquery',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/fragment',
    'core/ajax',
    'local_evokegame/sweetalert'
], function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Swal) {
    var STRINGS = {
        TITLE: 'Deliver skill points',
        SUCCESS: 'Skill points delivered'
    };

    var componentStrings = [
        {key: 'skills_deliver_title', component: 'local_evokegame'},
        {key: 'skills_deliver_success', component: 'local_evokegame'},
    ];

    var DeliverPoints = function(selector, contextid, courseid) {
        this.contextid = contextid;
        this.courseid = courseid;
        this.getStrings();
        this.init(selector);
    };

    DeliverPoints.prototype.getStrings = function() {
        var stringsPromise = Str.get_strings(componentStrings);
        $.when(stringsPromise).done(function(strings) {
            STRINGS.TITLE = strings[0];
            STRINGS.SUCCESS = strings[1];
        });
    };

    DeliverPoints.prototype.init = function(selector) {
        var triggers = $(selector);
        var self = this;

        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: STRINGS.TITLE,
            body: self.getBody({}),
            preShowCallback: function(triggerElement, modal) {
                modal.setBody(self.getBody({courseid: self.courseid}));
            }
        }, triggers).then(function(modal) {
            self.modal = modal;

            self.modal.getRoot().on(ModalEvents.hidden, function() {
                self.modal.setBody(self.getBody({}));
            });

            self.modal.getRoot().on(ModalEvents.shown, function() {
                self.modal.getRoot().append('<style>[data-fieldtype=submit] { display: none ! important; }</style>');
            });

            self.modal.getRoot().on(ModalEvents.save, self.submitForm.bind(self));
            self.modal.getRoot().on('submit', 'form', self.submitFormAjax.bind(self));

            return self.modal;
        });
    };

    DeliverPoints.prototype.getBody = function(formdata) {
        if (typeof formdata === 'undefined') {
            formdata = {};
        }

        var params = {jsonformdata: JSON.stringify(formdata)};
        return Fragment.loadFragment('local_evokegame', 'skill_points_form', this.contextid, params);
    };

    DeliverPoints.prototype.submitFormAjax = function(e) {
        e.preventDefault();
        var formData = this.modal.getRoot().find('form').serialize();

        Ajax.call([{
            methodname: 'local_evokegame_deliverskillpoints',
            args: {
                contextid: this.contextid,
                jsonformdata: JSON.stringify(formData)
            }
        }])[0].done(function(response) {
            this.modal.hide();
            this.showToast('success', response.message || STRINGS.SUCCESS);
        }.bind(this)).fail(function(error) {
            var message = error.message;
            if (!message) {
                message = error.error;
            }
            this.showToast('error', message);
        }.bind(this));
    };

    DeliverPoints.prototype.submitForm = function(e) {
        e.preventDefault();
        this.modal.getRoot().find('form').submit();
    };

    DeliverPoints.prototype.showToast = function(type, message) {
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
        init: function(selector, contextid, courseid) {
            return new DeliverPoints(selector, contextid, courseid);
        }
    };
});
