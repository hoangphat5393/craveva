/**
 * Loaded from main.js after vendor/helper/helper.js.
 * Normalizes Laravel validation values (arrays), finds a sensible DOM host
 * when fields are not wrapped in .form-group (e.g. x-forms.input-group),
 * and shows Swal for errors that cannot be mapped to an input.
 */
(function ($) {
    'use strict';

    if (typeof $ === 'undefined') {
        return;
    }

    function escapeHtml(text) {
        return $('<div/>')
            .text(text == null ? '' : String(text))
            .html();
    }

    function flattenValidationMessage(val) {
        if (val == null) {
            return '';
        }
        if (Array.isArray(val)) {
            return val
                .filter(function (item) {
                    return item !== null && item !== undefined && item !== '';
                })
                .map(String)
                .join(' ');
        }
        return String(val);
    }

    $.showErrors = function (object) {
        if (!object || typeof object !== 'object') {
            return;
        }

        var keys = Object.keys(object);

        $('.has-error').find('.help-block').remove();
        $('.has-error').removeClass('has-error');

        var orphaned = [];

        for (var i = 0; i < keys.length; i++) {
            var key = keys[i];
            var msg = flattenValidationMessage(object[key]);
            if (!msg) {
                continue;
            }

            var ele = $('[name="' + String(key).replace(/\\/g, '\\\\').replace(/"/g, '\\"') + '"]');
            if (ele.length === 0) {
                ele = $('#' + key);
            }
            if (ele.length === 0) {
                orphaned.push(msg);
                continue;
            }

            var grp = ele.closest('.form-group');
            if (grp.length === 0) {
                grp = ele.closest('.input-group');
            }
            if (grp.length === 0) {
                grp = ele.closest(
                    '.col-md-6, .col-md-4, .col-lg-6, .col-lg-4, .col-lg-3, .col-sm-12, .col-12'
                );
            }
            if (grp.length === 0) {
                grp = ele.parent();
            }
            if (grp.length === 0) {
                orphaned.push(msg);
                continue;
            }

            grp.find('.help-block').remove();
            grp.append(
                '<div class="help-block invalid-feedback d-block text-danger">' +
                escapeHtml(msg) +
                '</div>'
            );
            grp.addClass('has-error');
            ele.addClass('is-invalid');
        }

        if (orphaned.length > 0 && typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                text: orphaned.join(' · '),
                toast: true,
                position: 'top-end',
                timer: 6000,
                showConfirmButton: false,
            });
        }
    };
})(window.jQuery);
