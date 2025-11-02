/**
 * Custom notification modal js logic.
 *
 * @copyright  2021 World Bank Group <https://worldbank.org>
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 */

define([
    'jquery',
    'core/notification',
    'core/custom_interaction_events',
    'core/templates',
    'core/pending',
    'core/modal',
    'core/modal_registry'
],
    function($, Notification, CustomEvents, Templates, Pending, Modal, ModalRegistry) {

        var registered = false;

        /**
         * ModalNotificationBadge class.
         *
         * @class
         * @extends Modal
         */
        class ModalNotificationBadge extends Modal {
            /**
             * Constructor for the Modal.
             *
             * @param {HTMLElement} root The root HTMLElement for the modal
             */
            constructor(root) {
                super(root);
            }

            /**
             * Set up all of the event handling for the modal.
             *
             * @method registerEventListeners
             */
            registerEventListeners() {
                // Apply parent event listeners.
                super.registerEventListeners();
            }
        }

        ModalNotificationBadge.TYPE = 'local_evokegame-modal_notificationbadge';
        ModalNotificationBadge.TEMPLATE = 'local_evokegame/modal_notificationbadge';

        /**
         * Create a new modal using the supplied configuration.
         *
         * @param {object} modalConfig The configuration to create the modal instance
         * @returns {Promise} Resolved with a ModalNotificationBadge instance
         */
        ModalNotificationBadge.create = function(modalConfig) {
            var pendingModalPromise = new Pending('local_evokegame/modal_notificationbadge:create');
            modalConfig = modalConfig || {};
            modalConfig.type = ModalNotificationBadge.TYPE;

            var templateName = modalConfig.template || ModalNotificationBadge.TEMPLATE;
            var templateContext = modalConfig.templateContext || {};

            return Templates.renderForPromise(templateName, templateContext)
                .then(function(rendered) {
                    var html = rendered.html;
                    var js = rendered.js;

                    var modal = new ModalNotificationBadge(html);
                    if (js) {
                        modal.setTemplateJS(js);
                    }

                    // Configure the modal (this will handle show if configured)
                    modal.configure(modalConfig);

                    pendingModalPromise.resolve();

                    return modal;
                })
                .catch(function(error) {
                    pendingModalPromise.resolve();
                    Notification.exception(error);
                    throw error;
                });
        };

        // Automatically register with the modal registry the first time this module is imported so that you can create modals
        // of this type using the modal factory.
        if (!registered) {
            ModalRegistry.register(ModalNotificationBadge.TYPE, ModalNotificationBadge, 'local_evokegame/modal_notificationbadge');

            registered = true;
        }

        return ModalNotificationBadge;
});