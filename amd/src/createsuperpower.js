/**
 * Create superpower js logic.
 *
 * @package    local_evokegame
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
        'local_evokegame/sweetalert',
        'core/yui'],
    function($, Config, Str, ModalFactory, ModalEvents, Fragment, Ajax, Swal, Y) {
        /**
         * Constructor for the CreateSuperpower.
         *
         * @param selector The selector to open the modal
         * @param contextid The course module contextid
         * @param course The course id
         */
        var CreateSuperpower = function(selector, contextid, course) {
            this.contextid = contextid;

            this.course = course;

            this.init(selector);
        };

        /**
         * @var {Modal} modal
         * @private
         */
        CreateSuperpower.prototype.modal = null;

        /**
         * @var {int} contextid
         * @private
         */
        CreateSuperpower.prototype.contextid = -1;

        /**
         * @var {int} course
         * @private
         */
        CreateSuperpower.prototype.course = -1;

        /**
         * Set up all of the event handling for the modal.
         *
         * @method init
         */
        CreateSuperpower.prototype.init = function(selector) {
            var triggers = $(selector);

            return Str.get_string('createsuperpower', 'local_evokegame').then(function(title) {
                // Create the modal.
                return ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: title,
                    body: this.getBody({course: this.course})
                }, triggers);
            }.bind(this)).then(function(modal) {
                // Keep a reference to the modal.
                this.modal = modal;

                // We want to reset the form every time it is opened.
                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    this.modal.setBody(this.getBody({course: this.course}));
                }.bind(this));

                // We want to hide the submit buttons every time it is opened.
                this.modal.getRoot().on(ModalEvents.shown, function() {
                    this.modal.getRoot().append('<style>[data-fieldtype=submit] { display: none ! important; }</style>');
                }.bind(this));

                // We catch the modal save event, and use it to submit the form inside the modal.
                // Triggering a form submission will give JS validation scripts a chance to check for errors.
                this.modal.getRoot().on(ModalEvents.save, this.submitForm.bind(this));
                // We also catch the form submit event and use it to submit the form with ajax.
                this.modal.getRoot().on('submit', 'form', this.submitFormAjax.bind(this));

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
        CreateSuperpower.prototype.getBody = function(formdata) {
            if (typeof formdata === "undefined") {
                formdata = {};
            }

            // Get the content of the modal.
            var params = {jsonformdata: JSON.stringify(formdata)};

            return Fragment.loadFragment('local_evokegame', 'superpower_form', this.contextid, params);
        };

        /**
         * @method handleFormSubmissionResponse
         *
         * @private
         *
         * @return {Promise}
         */
        CreateSuperpower.prototype.handleFormSubmissionResponse = function(data) {
            this.modal.hide();
            // We could trigger an event instead.
            Y.use('moodle-core-formchangechecker', function() {
                M.core_formchangechecker.reset_form_dirty_state();
            });

            var item = JSON.parse(data.data);

            var tableLine = $('<tr>' +
                '<th scope="row">'+item.id+'</th>' +
                '<td>'+item.name+'</td>' +
                '<td style="width: 120px; text-align: center;">' +
                '<a href="#" data-id="'+item.id+'" data-name="'+item.name+'"' +
                    'data-courseid="'+item.courseid+'" data-badgeid="'+item.badgeid+'"' +
                    'class="btn btn-warning btn-sm edit-evokegame-superpower">' +
                '<i class="fa fa-pencil-square-o text-white"></i>' +
                '</a> ' +
                '<a href="#" data-id="'+item.id+'" class="btn btn-danger btn-sm delete-evokegame-superpower">' +
                '<i class="fa fa-trash-o"></i>' +
                '</a> ' +
                '</td>' +
                '</tr>');

            tableLine
                .appendTo('.table-evokegame-superpowers tbody')
                .hide().fadeIn('normal');

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
        CreateSuperpower.prototype.handleFormSubmissionFailure = function(data) {
            // Oh noes! Epic fail :(
            // Ah wait - this is normal. We need to re-display the form with errors!
            this.modal.setBody(this.getBody(data));
        };

        /**
         * Private method
         *
         * @method submitFormAjax
         *
         * @private
         *
         * @param {Event} e Form submission event.
         */
        CreateSuperpower.prototype.submitFormAjax = function(e) {
            // We don't want to do a real form submission.
            e.preventDefault();

            var changeEvent = document.createEvent('HTMLEvents');
            changeEvent.initEvent('change', true, true);

            // Prompt all inputs to run their validation functions.
            // Normally this would happen when the form is submitted, but
            // since we aren't submitting the form normally we need to run client side
            // validation.
            this.modal.getRoot().find(':input').each(function(index, element) {
                element.dispatchEvent(changeEvent);
            });

            // Now the change events have run, see if there are any "invalid" form fields.
            var invalid = $.merge(
                this.modal.getRoot().find('[aria-invalid="true"]'),
                this.modal.getRoot().find('.error')
            );

            // If we found invalid fields, focus on the first one and do not submit via ajax.
            if (invalid.length) {
                invalid.first().focus();
                return;
            }

            // Convert all the form elements values to a serialised string.
            var formData = this.modal.getRoot().find('form').serialize();

            // Now we can continue...
            Ajax.call([{
                methodname: 'local_evokegame_createsuperpower',
                args: {contextid: this.contextid, course: this.course, jsonformdata: JSON.stringify(formData)},
                done: this.handleFormSubmissionResponse.bind(this),
                fail: this.handleFormSubmissionFailure.bind(this, formData)
            }]);
        };

        /**
         * This triggers a form submission, so that any mform elements can do final tricks before the form submission is processed.
         *
         * @method submitForm
         * @param {Event} e Form submission event.
         * @private
         */
        CreateSuperpower.prototype.submitForm = function(e) {
            e.preventDefault();

            this.modal.getRoot().find('form').submit();
        };

        return {
            init: function(selector, contextid, course) {
                return new CreateSuperpower(selector, contextid, course);
            }
        };
    }
);
