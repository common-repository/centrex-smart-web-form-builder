(function ($, centrexApp) {
    const utils = centrexApp.utils = centrexApp.utils || {};
    utils.chain = function chain(firstFunc, secondFunc) {
        return function () {
            if (firstFunc) {
                firstFunc.apply(this, arguments);
            }
            if (secondFunc) {
                secondFunc.apply(this, arguments);
            }
        };
    };


    centrexApp.utils.ShownWhenValueEnum = {
        IsEqualTo: 'IsEqualTo',
        IsNotEqualTo: 'IsNotEqualTo',
    };


    /**
     * Regex to determine if the value is part of a group of other values.
     * For example: address[0][city] will result in two capture groups with values: address and city.
     * @type {RegExp}
     */
    const FORM_GROUP_NAME_REGEX = /(\w+)__(\w+)/;
    centrexApp.utils.isFormGroup = function isFormGroupProperty(fieldName) {
        return FORM_GROUP_NAME_REGEX.test(fieldName);
    };

    centrexApp.utils.parseFormGroupProperties = function parseFormGroupProperties(propertyName) {
        const matches = FORM_GROUP_NAME_REGEX.exec(propertyName);
        if (matches) {
            return {
                formGroupName: matches[1],
                propertyName: matches[2],
            };
        }
        return null;
    };

    centrexApp.utils.controlNameToFormDataValue = function getValueForFormGroup(formData, controlName) {
        if (!utils.isFormGroup(controlName)) {
            return formData[controlName];
        }

        const {formGroupName, propertyName} = utils.parseFormGroupProperties(controlName);
        if (typeof formData[formGroupName] === 'undefined') {
            return null;
        }

        return formData[formGroupName].value[propertyName];
    };

    centrexApp.utils.ajaxPost = function ajaxPost(action, request) {
        const requestBase = {
            _ajax_nonce: centrexApp.options.nonce,
            action,
        };

        return $.ajax({
            url: centrexApp.options.ajaxUrl,
            type: 'POST',
            data: {
                ...requestBase,
                ...request,
            }
        });
    };

})(jQuery, window.CENTREX_APP = window.CENTREX_APP || {});
