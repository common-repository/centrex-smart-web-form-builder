// End-user-facing Javascript:
(function ($, centrexApp) {
    'use strict';
    const formRenderer = centrexApp.formRenderer = centrexApp.formRenderer || {};
    const utils = centrexApp.utils;

    // form data types that we should not be submitting to the server:
    const INVALID_FORM_DATA_TYPES = {
        paragraph: true,
        file: true,
        pageBreak: true
    };

    formRenderer.getCondensedFormData = function () {
        const $formRenderContainer = formRenderer.$formRenderContainer;
        const formData = $formRenderContainer.formRender('userData');
        const validFormData = formData.filter(fd => !INVALID_FORM_DATA_TYPES[fd.type]);

        return validFormData.reduce((formValuesByFormName, control) => {
            const value = control.userData?.join(', ');
            if (centrexApp.utils.isFormGroup(control.name)) {
                const {formGroupName, propertyName} = centrexApp.utils.parseFormGroupProperties(control.name);
                formValuesByFormName[formGroupName] = formValuesByFormName[formGroupName] || {value: {}};
                formValuesByFormName[formGroupName].value[propertyName] = value;
                return formValuesByFormName;
            }

            const result = {
                label: control.label || control.name,
                value: value
            };

            if (control.customFieldId) {
                result.customFieldId = control.customFieldId;
            }
            formValuesByFormName[control.name] = result;
            return formValuesByFormName;
        }, {});
    };

    formRenderer.getFormDataWithFiles = function () {
        const $formRenderContainer = formRenderer.$formRenderContainer;
        const formData = new FormData();

        const getFilesForField = (field) => {
            if (field.subtype === 'fineuploader') {
                const $fineUploaderElement = $(document.getElementById(field.name + '-wrapper'));

                return $fineUploaderElement.fineUploader('getUploads')
                    .map(({id}) => $fineUploaderElement.fineUploader('getFile', id));
            }

            return document.getElementById(field.name).files;
        };
        const allFiles = $formRenderContainer
            .formRender('userData')
            .filter(f => f.type === 'file')
            .map(getFilesForField)
            .reduce((result, f) => {
                result.push.apply(result, f);
                return result;
            }, []);

        if (!allFiles.length) {
            return null;
        }

        allFiles
            .forEach((file, index) => {
                formData.append(`file_${index}`, file);
            });

        return formData;
    };

    /**
     * Pre-populates the form data with the values from the URL params if available.
     * @param formRendererData
     * @returns {object}
     */
    formRenderer.prePopulateFormDataFromUrl = function (formRendererData) {
        const urlParams = new URLSearchParams(window.location.search);
        const fieldsThatCanBePrePopulated = formRendererData.filter(field => field.prePopulate === true);
        if (!fieldsThatCanBePrePopulated.length) {
            return formRendererData;
        }

        fieldsThatCanBePrePopulated.forEach(field => {
            const {name} = field;
            if (urlParams.has(name)) {
                field.value = urlParams.get(name);
            }
        });

        return formRendererData;

    };
    formRenderer.init = function () {
        const $formRenderContainer = formRenderer.$formRenderContainer = $('#formRenderContainer');
        formRenderer.validator = $formRenderContainer.validate({
            ignore: ':hidden:not(.required-on-hidden)',
            errorPlacement: function (error, element) {
                if (element.prop('type') === 'radio') {
                    error.appendTo(element.closest('.formbuilder-radio-group').find('label.formbuilder-radio-group-label'));
                } else {
                    error.insertBefore(element);
                }
            }
        });

        if (!formRenderer.enableConditionalFields) {
            formRenderer.enableConditionalFields = function (options) {
                return options;
            };
        }
        if (!formRenderer.enableMultiPageForms) {
            formRenderer.enableMultiPageForms = function (options) {
                return options;
            };
        }

        if (!formRenderer.enablePlaidIntegration) {
            window.fbControls = window.fbControls || [];
            window.fbControls.push(function (ControlClass) {
                class PlaidControl extends ControlClass {
                    configure() {
                        this.js = '//cdn.plaid.com/link/v2/stable/link-initialize.js';
                    }

                    build() {
                        this.link_token = this.markup('input', null, {
                            className: 'centrex-link-token',
                            type: 'hidden',
                            'data-api-environment': this.config.apiEnvironment,
                        });
                        const plaidTokenAttributes = {
                            className: 'centrex-plaid-token form-control required-on-hidden',
                            type: 'hidden',
                            name: this.config.name,
                            value: this.config.value || '',
                        };

                        if (this.config.required) {
                            plaidTokenAttributes.required = this.config.required;
                            plaidTokenAttributes['data-msg'] = 'Please link your financial account to proceed.';
                        }
                        plaidTokenAttributes['data-api-environment'] = this.config.apiEnvironment;

                        this.plaid_token = this.markup('input', null, plaidTokenAttributes);

                        this.loadingSpinner = this.markup('div', null, {
                            className: 'ld ld-ring ld-spin',
                        });
                        this.linkButton = this.markup('button', ['Click To Link Account &#8644;', this.loadingSpinner], {
                            type: 'button',
                            className: 'btn btn-primary centrex-plaid-link-button',
                        });
                        this.plaidLoadMessage = this.markup('div', ['Plaid uses best-in-class encryption protocols, secure cloud infrastructure, multi-factor authentication, and around-the-clock monitoring to protect your data.'], {
                            className: 'plaid-load-message'
                        });
                        this.plaidLogo = this.markup('div', null, {
                            className: 'plaid-logo'
                        });
                        this.wrapper = this.markup('div', [this.plaid_token, this.link_token, this.linkButton, this.plaidLoadMessage, this.plaidLogo], {
                            className: 'form-group loading'
                        });
                        return this.wrapper;
                    }

                }

                ControlClass.register('plaid', PlaidControl);
                return PlaidControl;
            });

            formRenderer.enablePlaidIntegration = function (options) {
                $formRenderContainer.on('click', '.centrex-plaid-link-button', function () {
                    const $linkTokenButton = $(this);
                    const $formGroup = $linkTokenButton.closest('.form-group');

                    const $linkTokenInput = $formGroup.find('.centrex-link-token');
                    const $plaidTokenInput = $formGroup.find('.centrex-plaid-token');
                    const $plaidTokenMsg = $formGroup.find('.plaid-load-message');
                    const $plaidTokenLogo = $formGroup.find('.plaid-logo');
                    if (!$linkTokenInput.val()) {
                        return;
                    }

                    $formGroup.addClass('loading');

                    const plaidHandler = Plaid.create({
                        token: $linkTokenInput.val(),
                        onExit: function (err,) {
                            $formGroup.removeClass('loading');
                        },
                        onSuccess: (public_token, metadata) => {
                            $plaidTokenInput.val(public_token);
                            utils.ajaxPost('centrex_plugin_process_token', {
                                publicToken: public_token,
                                contactId: formRenderer.contactId,
                                apiEnvironment: $plaidTokenInput.data('api-environment')
                            })
                                .then((response) => {
                                    if (!response.success || !response.data.success) {
                                        throw new Error(response.message);
                                    }
                                    return response;
                                })
                                .always((response) => {
                                    $formGroup.removeClass('loading');

                                    if (!response.success || !response.data.success) {
                                        $linkTokenButton.addClass('btn-danger');
                                        $linkTokenButton.text('Link Failed');
                                        $plaidTokenInput.val('');
                                        return;
                                    }

                                    $linkTokenButton.addClass('btn-success');
                                    $linkTokenButton.text('Link Successful');
                                    $linkTokenButton.prop('disabled', true);

                                    $plaidTokenMsg.slideUp();

                                    $plaidTokenLogo.addClass('logo-success');
                                });
                        }
                    });
                    plaidHandler.open();
                });
                return options;
            };
        }

        window.fbControls = window.fbControls || [];

        centrexApp.formRendererData = formRenderer.prePopulateFormDataFromUrl(centrexApp.formRendererData);

        let formRenderOptions = {
            container: $formRenderContainer,
            formData: centrexApp.formRendererData,
            controlConfig: {
                'file.fineuploader': {
                    autoUpload: false,
                    // other fine uploader configuration options here
                    callbacks: {

                        onStatusChange: function () {
                            const $fileInput = $(this._options.element).prev();
                            const fileInputName = $fileInput.attr('name');
                            const fieldConfig = centrexApp.formRendererData.find(f => f.name === fileInputName);
                            if (!fieldConfig?.required) {
                                return;
                            }

                            // Will cause validation to succeed if there are submitted files.
                            // Otherwise, validation will fail because the hidden input is empty string ''.
                            const currentlySubmittedFiles = this.getUploads({status: qq.status.SUBMITTED});
                            const currentlySubmittedFileNames = currentlySubmittedFiles.map(f => f.name);
                            $fileInput.val(currentlySubmittedFileNames.join(','));

                        },
                        onValidate: function (file) {
                            const $fileInput = $(this._options.element).prev();
                            const inputName = $fileInput.attr('name');
                            const fieldConfig = centrexApp.formRendererData.find(f => f.name === inputName);

                            const currentlySubmittedFiles = this.getUploads({status: qq.status.SUBMITTED});
                            if (fieldConfig?.multiple) {
                                // always allow multiple files if multiple is enabled:
                                return true;
                            }


                            // prevent multiple file uploads for non-multiple file fields:
                            return currentlySubmittedFiles?.length <= 0;
                        }
                    }
                }
            }
        };
        const $formButtonsContainer = formRenderer.$formButtonsContainer = $('<div class="centrex-forms-button-container"></div>');
        formRenderer.$submitButton = $('<button type="submit" id="centrex-submit">'
            + centrexApp.options.formSubmitText +
            '</button>')
            .appendTo($formButtonsContainer);

        formRenderOptions = formRenderer.enableConditionalFields(formRenderOptions);
        formRenderOptions = formRenderer.enableMultiPageForms(formRenderOptions);
        formRenderOptions = formRenderer.enablePlaidIntegration(formRenderOptions);

        // call this render function before others so that other functions can disable our submit event:
        formRenderOptions.onRender = centrexApp.utils.chain(function () {
            // remove all `multiple` attributes from elements:
            centrexApp
                .formRendererData
                .filter(fd => fd.multiple === false)
                .forEach(fd => {
                    const $elementsToRemoveMultipleAttribute = $formRenderContainer.find(`[name="${fd.name}"]`);
                    $elementsToRemoveMultipleAttribute.removeAttr('multiple');
                });

            $formRenderContainer.on('submit', formRenderer.submitHandlerFactory());
        }, formRenderOptions.onRender);

        $formRenderContainer.formRender(formRenderOptions);
        $formButtonsContainer.appendTo($formRenderContainer);
        setWidthClasses();


        function setWidthClasses() {
            $('.form-group:has(.field-half)').addClass('field-half-group');
            $('.form-group:has(.field-full)').addClass('field-full-group');
        }
    };


    formRenderer.disableSubmitButton = function () {
        const $submitButton = formRenderer.$submitButton;
        $submitButton.attr('disabled', 'disabled');
        $submitButton.html(
            '<i class="centrex-fas centrex-fa-sync-alt centrex-fa-spin"></i> ' +
            'Submitting'
        );
    };

    formRenderer.updateSubmitButtonSuccess = function () {
        const $submitButton = formRenderer.$submitButton;
        $submitButton.removeAttr('disabled');
        $submitButton.html(centrexApp.options.formSubmitText);
    };

    formRenderer.redirectToThankUrl = function () {
        if (centrexApp.options.thankUrl) {
            window.location.assign(centrexApp.options.thankUrl);
        }
    };

    formRenderer.submitHandlerFactory = function () {
        let isFormSubmitting = false;

        return function (event) {
            const validator = formRenderer.validator;
            validator.form();
            if (isFormSubmitting || !validator.valid()) {
                event.preventDefault();
                return false;
            }
            isFormSubmitting = true;
            formRenderer.disableSubmitButton();

            const condensedFormData = formRenderer.getCondensedFormData();

            utils.ajaxPost('centrex_plugin_submit_form', {
                formId: centrexApp.options.formId,
                formData: condensedFormData
            })
                .then((response) => {
                    const formDataWithFiles = formRenderer.getFormDataWithFiles();

                    if (formDataWithFiles && response?.data?.id) {
                        return formRenderer.uploadFilesInForm(response.data.id, formDataWithFiles);
                    }
                    return response;
                })
                .always((response) => {
                    isFormSubmitting = false;
                    formRenderer.updateSubmitButtonSuccess();
                    formRenderer.redirectToThankUrl();
                });
            event.stopImmediatePropagation();
            return false;
        };
    };
})(jQuery, window.CENTREX_APP = window.CENTREX_APP || {});