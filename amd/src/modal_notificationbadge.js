/**
 * Custom notification modal js logic.
 *
 * @package    local_evokegame
 * @copyright  2021 World Bank Group <https://worldbank.org>
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 */

define(['jquery', 'core/notification', 'core/custom_interaction_events', 'core/modal', 'core/modal_registry'],
    function($, Notification, CustomEvents, Modal, ModalRegistry) {

        var registered = false;

        /**
         * Constructor for the Modal.
         *
         * @param {object} root The root jQuery element for the modal
         */
        var ModalNotificationBadge = function(root) {
            Modal.call(this, root);
        };

        ModalNotificationBadge.TYPE = 'local_evokegame-modal_notificationbadge';
        ModalNotificationBadge.prototype = Object.create(Modal.prototype);
        ModalNotificationBadge.prototype.constructor = ModalNotificationBadge;

        /**
         * Set up all of the event handling for the modal.
         *
         * @method registerEventListeners
         */
        ModalNotificationBadge.prototype.registerEventListeners = function() {
            // Apply parent event listeners.
            Modal.prototype.registerEventListeners.call(this);
        };

        // Automatically register with the modal registry the first time this module is imported so that you can create modals
        // of this type using the modal factory.
        if (!registered) {
            ModalRegistry.register(ModalNotificationBadge.TYPE, ModalNotificationBadge, 'local_evokegame/modal_notificationbadge');

            registered = true;
        }

        return ModalNotificationBadge;
});