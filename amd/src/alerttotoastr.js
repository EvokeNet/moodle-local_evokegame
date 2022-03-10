/**
 * Convert boostrap alert to sweetalert.
 *
 * @package
 * @subpackage    mod_evokeportfolio
 * @copyright  2021 World Bank Group <https://worldbank.org>
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 */

import $ from 'jquery';
import Toastr from 'local_evokegame/toastr';

export const init = () => {
    const containers = $('.alert.alert-block');

    containers.each(function(index, item) {
        const alertdiv = $(item);

        const message = alertdiv.clone().children().remove().end().text();

        showToastr(alertdiv, message);

        alertdiv.remove();
    });
};

const showToastr = (element, message) => {
    if (element.hasClass('alert-info')) {
        Toastr.info(message);
    }

    if (element.hasClass('alert-success')) {
        Toastr.success(message);
    }

    if (element.hasClass('alert-warning')) {
        Toastr.warning(message);
    }

    if (element.hasClass('alert-danger')) {
        Toastr.error(message);
    }
};
