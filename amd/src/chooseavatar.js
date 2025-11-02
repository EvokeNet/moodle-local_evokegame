/**
 * Create section js logic.
 *
 * @package    mod_evokeportfolio
 * @copyright  2021 World Bank Group <https://worldbank.org>
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 */

define([
        'jquery',
        'core/config',
        'core/str',
        'core/modal_factory',
        'core/modal_events',
        'core/fragment',
        'core/ajax',
        'local_evokegame/sweetalert'],
    function($, Config, Str, ModalFactory, ModalEvents, Fragment, Ajax, Swal) {
        /**
         * Constructor for the ChooseAvatar.
         *
         * @param selector The selector to open the modal
         * @param contextid The course module contextid
         * @param portfolioid The portfolio id
         */
        var ChooseAvatar = function(selector, contextid) {
            this.contextid = contextid;

            this.init(selector);
        };

        /**
         * @var {Modal} modal
         * @private
         */
        ChooseAvatar.prototype.modal = null;

        /**
         * @var {int} contextid
         * @private
         */
        ChooseAvatar.prototype.contextid = -1;

        /**
         * Set up all of the event handling for the modal.
         *
         * @method init
         */
        ChooseAvatar.prototype.init = function(selector) {
            var triggers = $(selector);

            return Str.get_string('chooseavatar', 'local_evokegame').then(function(title) {
                // Create the modal.
                return ModalFactory.create({
                    type: ModalFactory.types.DEFAULT,
                    title: title,
                    body: this.getBody(),
                    large: true
                }, triggers);
            }.bind(this)).then(function(modal) {
                // Keep a reference to the modal.
                this.modal = modal;

                // We want to reset the form every time it is opened.
                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    this.modal.setBody(this.getBody());
                }.bind(this));

                // We want to hide the submit buttons every time it is opened.
                this.modal.getRoot().on(ModalEvents.shown, function() {
                    this.modal.getRoot().append('<style>[data-fieldtype=submit] { display: none ! important; }</style>');
                }.bind(this));

                // We also catch the form submit event and use it to submit the form with ajax.
                this.modal.getRoot().on('click', '.chooseavatar-form img.avatar', this.avatarSelected.bind(this));

                return this.modal;
            }.bind(this));
        };

        /**
         * @method getBody
         *
         * @private
         *
         * @return {Promise}
         */
        ChooseAvatar.prototype.getBody = function(formdata) {
            if (typeof formdata === "undefined") {
                formdata = {};
            }

            // Get the content of the modal.
            var params = {jsonformdata: JSON.stringify(formdata)};

            return Fragment.loadFragment('local_evokegame', 'chooseavatar_form', this.contextid, params);
        };

        /**
         * @method handleFormSubmissionResponse
         *
         * @private
         *
         * @return {Promise}
         */
        ChooseAvatar.prototype.handleFormSubmissionResponse = function(data) {
            this.modal.hide();

            var item = JSON.parse(data.data);

            var useravatar = $('#evokegame-user-avatar');
            useravatar.attr('src', item);

            var navuserimg = $('.usermenu .userpicture');
            navuserimg.attr('src', item);

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
                icon: 'success',
                title: data.message
            });
        };

        /**
         * @method handleFormSubmissionFailure
         *
         * @private
         *
         * @return {Promise}
         */
        ChooseAvatar.prototype.handleFormSubmissionFailure = function() {
            // Oh noes! Epic fail :(
            // Ah wait - this is normal. We need to re-display the form with errors!
            this.modal.setBody(this.getBody());
        };

        /**
         * Private method
         *
         * @method avatarSelected
         *
         * @private
         *
         * @param {Event} e Form submission event.
         */
        ChooseAvatar.prototype.avatarSelected = function(e) {
            // We don't want to do a real form submission.
            e.preventDefault();

            var avatarid = $(e.currentTarget).data('id');

            Ajax.call([{
                methodname: 'local_evokegame_chooseavatar',
                args: {contextid: this.contextid, avatarid: avatarid},
                done: this.handleFormSubmissionResponse.bind(this),
                fail: this.handleFormSubmissionFailure.bind(this)
            }]);
        };

        return {
            init: function(selector, contextid) {
                return new ChooseAvatar(selector, contextid);
            }
        };
    }
);
