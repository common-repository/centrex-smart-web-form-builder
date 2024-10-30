'use strict';
(function ($, centrexApp) {
    const ALL_STATES_OPTIONS = [
        {
            'label': 'Alabama',
            'value': 'Alabama'
        },
        {
            'label': 'Alaska',
            'value': 'Alaska'
        },
        {
            'label': 'Arizona',
            'value': 'Arizona'
        },
        {
            'label': 'Arkansas',
            'value': 'Arkansas'
        },
        {
            'label': 'California',
            'value': 'California'
        },
        {
            'label': 'Colorado',
            'value': 'Colorado'
        },
        {
            'label': 'Connecticut',
            'value': 'Connecticut'
        },
        {
            'label': 'Delaware',
            'value': 'Delaware'
        },
        {
            'label': 'Florida',
            'value': 'Florida'
        },
        {
            'label': 'Georgia',
            'value': 'Georgia'
        },
        {
            'label': 'Hawaii',
            'value': 'Hawaii'
        },
        {
            'label': 'Idaho',
            'value': 'Idaho'
        },
        {
            'label': 'Illinois',
            'value': 'Illinois'
        },
        {
            'label': 'Indiana',
            'value': 'Indiana'
        },
        {
            'label': 'Iowa',
            'value': 'Iowa'
        },
        {
            'label': 'Kansas',
            'value': 'Kansas'
        },
        {
            'label': 'Kentucky',
            'value': 'Kentucky'
        },
        {
            'label': 'Louisiana',
            'value': 'Louisiana'
        },
        {
            'label': 'Maine',
            'value': 'Maine'
        },
        {
            'label': 'Maryland',
            'value': 'Maryland'
        },
        {
            'label': 'Massachusetts',
            'value': 'Massachusetts'
        },
        {
            'label': 'Michigan',
            'value': 'Michigan'
        },
        {
            'label': 'Minnesota',
            'value': 'Minnesota'
        },
        {
            'label': 'Mississippi',
            'value': 'Mississippi'
        },
        {
            'label': 'Missouri',
            'value': 'Missouri'
        },
        {
            'label': 'Montana',
            'value': 'Montana'
        },
        {
            'label': 'Nebraska',
            'value': 'Nebraska'
        },
        {
            'label': 'Nevada',
            'value': 'Nevada'
        },
        {
            'label': 'New Hampshire',
            'value': 'New Hampshire'
        },
        {
            'label': 'New Jersey',
            'value': 'New Jersey'
        },
        {
            'label': 'New Mexico',
            'value': 'New Mexico'
        },
        {
            'label': 'New York',
            'value': 'New York'
        },
        {
            'label': 'North Carolina',
            'value': 'North Carolina'
        },
        {
            'label': 'North Dakota',
            'value': 'North Dakota'
        },
        {
            'label': 'Ohio',
            'value': 'Ohio'
        },
        {
            'label': 'Oklahoma',
            'value': 'Oklahoma'
        },
        {
            'label': 'Oregon',
            'value': 'Oregon'
        },
        {
            'label': 'Pennsylvania',
            'value': 'Pennsylvania'
        },
        {
            'label': 'Rhode Island',
            'value': 'Rhode Island'
        },
        {
            'label': 'South Carolina',
            'value': 'South Carolina'
        },
        {
            'label': 'South Dakota',
            'value': 'South Dakota'
        },
        {
            'label': 'Tennessee',
            'value': 'Tennessee'
        },
        {
            'label': 'Texas',
            'value': 'Texas'
        },
        {
            'label': 'Utah',
            'value': 'Utah'
        },
        {
            'label': 'Vermont',
            'value': 'Vermont'
        },
        {
            'label': 'Virginia',
            'value': 'Virginia'
        },
        {
            'label': 'Washington',
            'value': 'Washington'
        },
        {
            'label': 'West Virginia',
            'value': 'West Virginia'
        },
        {
            'label': 'Wisconsin',
            'value': 'Wisconsin'
        },
        {
            'label': 'Wyoming',
            'value': 'Wyoming'
        }
    ];

    // Form-builder specific code:
    centrexApp.formBuilderPage = centrexApp.formBuilderPage || {};
    const REQUIRED_FIELD_NAMES = centrexApp.formBuilderPage.REQUIRED_FIELD_NAMES = ['first_name', 'last_name', 'email', 'phone_number'];

    // Fields that should not have the field_id input added to them.
    const DIRECTLY_MAPPED_INPUTS = [
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'address__address1',
        'address__address2',
        'address__city',
        'address__state',
        'address__zip'
    ];

    //The field types to be disabled from being shown in the elements panel.
    const DISABLED_FIELD_TYPES = [
        'autocomplete',
        'button',
        'file',
        'header',
        'starRating',
    ];

    // Fields that the user needs to map with the Centrex field_id so that they show up correctly in the Centrex CRM.
    const USER_CUSTOM_FIELD_TYPES = ['checkbox', 'checkbox-group', 'date', 'radio-group', 'select', 'text'];


    const formBuilderPage = centrexApp.formBuilderPage;
    let formBuilderPageApiResolve;
    formBuilderPage.apiPromise = new Promise(function (resolve) {
        formBuilderPageApiResolve = resolve;
    });


    if (!formBuilderPage.enableConditionalFields) {
        // Disables conditional fields on free version:
        formBuilderPage.enableConditionalFields = function (existingOptions) {
            return existingOptions;
        };
    }

    if (!formBuilderPage.enableMultiPageForms) {
        formBuilderPage.enableMultiPageForms = function (existingOptions) {
            return existingOptions;
        };
    }

    const $errorMessageContainer = formBuilderPage.$errorMessageContainer = $('#centrexErrorMessageContainer');
    formBuilderPage.setErrorMessage = function (text) {
        if (!text?.length) {
            $errorMessageContainer.hide().removeClass('has-error');
            return;
        }

        $errorMessageContainer.show().addClass('has-error');
        $errorMessageContainer.text(`Error: ${text}`);
        $errorMessageContainer[0].scrollIntoView({
            behavior: 'smooth',
        });
    };

    formBuilderPage.init = function (formBuilderOptions) {
        formBuilderPage.setErrorMessage('');

        const $fbEditorStage = formBuilderPage.$fbEditorStage = $('#formBuilder');

        const $mainFormSettingsContainer = formBuilderPage.$mainFormSettingsContainer = $('.centrex-form-settings-container');
        const $mainFormSettingsToggle = formBuilderPage.$mainFormSettingsToggle = $('#centrex-form-settings-toggle');


        if (centrexApp.backendOptions.isProVersion === true) {
            DISABLED_FIELD_TYPES.splice(DISABLED_FIELD_TYPES.indexOf('file'), 1);
        }

        const PRE_POPULATE_ATTRIBUTE_CONFIG = {
            label: 'Populate from URL',
            description: 'Check this box to dynamically populate from url parameter. ' +
                'For example: if you have a field with the name "first_name" and you want to pre-populate it with the value of the url parameter "first_name", then check this box.',
            value: false,
            type: 'checkbox',
        };

        const WIDTH_ATTRIBUTE_CONFIG = {
            label: 'Width',
            options: {
                'field-full form-control': 'Full Width',
                'field-half form-control': 'Half Width',
            }
        };
        const options = {
            formData: formBuilderOptions.formBuilderData,
            disabledAttrs: [
                'access',
                'description',
                'inline',
                'other'
            ],
            controlOrder: [
                'text',
                'textarea'
            ],
            disableFields: [
                'autocomplete',
                'button',
                'file',
                'header',
                'starRating'
            ],
            disabledSubtypes: {
                text: ['color'],
                file: ['fineuploader'],
                paragraph: ['address', 'blockquote', 'canvas', 'output'],
            },
            typeUserDisabledAttrs: {
                'plaid': [
                    'placeholder',
                    'className',
                    'value'
                ]
            },
            disableHTMLLabels: true,
            disabledActionButtons: ['clear','data'],
            inputSets: [
                {
                    icon: '🏠',
                    label: 'User Address',
                    name: 'address',
                    fields: [
                        {
                            type: 'text',
                            label: 'Address Line 1',
                            name: 'address__address1',

                        },
                        {
                            type: 'text',
                            label: 'Address Line 2',
                            name: 'address__address2',
                        },
                        {
                            type: 'text',
                            label: 'City',
                            name: 'address__city',
                            className: 'field-half',
                        },
                        {
                            type: 'select',
                            label: 'State',
                            name: 'address__state',
                            className: 'field-half',
                            required: 'required',
                            placeholder: 'Please Select One',
                            values: ALL_STATES_OPTIONS
                        },
                        {
                            type: 'text',
                            label: 'Zip',
                            name: 'address__zip',
                            className: 'field-half',
                        },
                    ]
                }
            ],
            fields: [
                {
                    label: 'First Name',
                    type: 'text',
                    icon: '👤',
                    required: 'required',
                    name: 'first_name'
                },
                {
                    label: 'Last Name',
                    type: 'text',
                    icon: '👤',
                    required: 'required',
                    name: 'last_name'
                },
                {
                    label: 'Email',
                    type: 'text',
                    subtype: 'email',
                    icon: '✉',
                    required: 'required',
                    name: 'email'
                },
                {
                    label: 'Phone',
                    type: 'text',
                    subtype: 'tel',
                    icon: '☎',
                    required: 'required',
                    name: 'phone_number'
                },
                {
                    label: 'State',
                    type: 'select',
                    icon: '📍',
                    required: 'required',
                    placeholder: 'Please Select One',
                    name: 'state',
                    values: ALL_STATES_OPTIONS
                },
                {
                    label: 'Plaid Link (Financial)',
                    type: 'plaid',
                    icon: '💰',
                    required: 'required',
                    name: 'plaid_token'
                }
            ],
            defaultFields: [
                {
                    label: 'First Name',
                    type: 'text',
                    required: 'required',
                    name: 'first_name'
                },
                {
                    label: 'Last Name',
                    type: 'text',
                    required: 'required',
                    name: 'last_name'
                },
                {
                    label: 'Email',
                    type: 'text',
                    required: 'required',
                    subtype: 'email',
                    name: 'email'
                },
                {
                    label: 'Phone',
                    type: 'text',
                    required: 'required',
                    subtype: 'tel',
                    name: 'phone_number'
                },
            ],
            typeUserAttrs: {
                text: {
                    className: WIDTH_ATTRIBUTE_CONFIG,
                    prePopulate: PRE_POPULATE_ATTRIBUTE_CONFIG,
                },
                hidden: {
                    prePopulate: PRE_POPULATE_ATTRIBUTE_CONFIG,
                },
                number: {
                    className: WIDTH_ATTRIBUTE_CONFIG,
                    prePopulate: PRE_POPULATE_ATTRIBUTE_CONFIG,
                },
                date: {
                    className: WIDTH_ATTRIBUTE_CONFIG,
                    prePopulate: PRE_POPULATE_ATTRIBUTE_CONFIG,
                },
                select: {
                    className: WIDTH_ATTRIBUTE_CONFIG,
                    prePopulate: PRE_POPULATE_ATTRIBUTE_CONFIG,
                },
                file: {
                    accept: {
                        label: 'Accept File Extensions',
                        value: 'image/*, application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint, text/plain, application/pdf'
                    }
                },
                plaid: {
                    assetReport: {
                        label: 'Asset Report?',
                        description: 'Check this box if you want to generate an asset report.',
                        value: false,
                        type: 'checkbox',
                    },
                    apiEnvironment: {
                        label: 'API Environment',
                        description: 'Test or Live Plaid API Endpoint',
                        options: {
                            'staging': 'Test',
                            'production': 'Live'
                        },
                    }
                }
            },
            templates: {
                plaid: function (fieldData) {
                    return {
                        field: '<div class="plaid-integration-backend">' +
                            '<span>Plaid Link (Financial)</span>' +
                            '<div class="plaid-logo"></div>' +
                            '</div>',
                    };
                }
            },
            dataType: 'json',
            stickyControl: {
                enable: true,
                offset: {
                    top: 60,
                    right: 20,
                    left: 'auto'
                }
            },
            onAddField: function (fieldId, fieldData) {
                fieldData.name = ensureUniqueName(fieldData.name);

                setFineUploaderAsDefault(fieldData);

            },
            onAddFieldAfter: function (fieldId, fieldData) {
                formBuilderPage.setErrorMessage('');

                setWidthClassesForField(fieldId, fieldData);

                disableDefaultFieldEditing();
                disableRemovingOfRequiredFields(fieldId, fieldData);
                hideCustomFieldIDFromDefaultFields(fieldId, fieldData);

                ensurePlaidForPremiumOnly(fieldId, fieldData);
            },
            onCloseFieldEdit: function () {
                disableDefaultFieldEditing();
            },
            onAddOption: function (option) {
                window.requestAnimationFrame(() => {
                    disableDefaultFieldEditing();
                });
                return option;
            },
            onSave: function (evt, formData) {
                console.log('saving formData', formData);
                window.sessionStorage.setItem('formData', JSON.stringify(formData));

                const $formBuilderContainer = $('#formBuilderContainer');
                const $formTitle = $formBuilderContainer.find('#formTitle');
                const $formThankUrl = $formBuilderContainer.find('#formThankUrl');
                const $formPostUrl = $formBuilderContainer.find('#formPostUrl');
                const $formSubmitText = $formBuilderContainer.find('#formSubmitText');
                const $formFileTypeId = $formBuilderContainer.find('#formFileTypeId');
                const $formNonce = $formBuilderContainer.find('#_wpnonce');

                jQuery.post(ajaxurl, {
                    action: 'centrex_plugin_update_form_row',
                    formData,
                    formTitle: $formTitle.val(),
                    formThankUrl: $formThankUrl.val(),
                    formSubmitText: $formSubmitText.val(),
                    formId: formBuilderOptions.currentFormId,
                    formFileTypeId: $formFileTypeId.val(),
                    formPostUrl: $formPostUrl.val(),
                    formNonce: $formNonce.val(),
                })
                    .always((response) => {
                        console.log('response from server', response);
                        if (response && response.success) {
                            alert('Form Saved');
                            location.reload();
                        } else {
                            formBuilderPage.setErrorMessage('Something went wrong...');
                        }
                    });
            },
        };

        addCustomFieldIdAttributeToCustomFields(options);

        formBuilderPage.formBuilderHasRendered = false;
        formBuilderPage.enableConditionalFields(options);
        formBuilderPage.enableMultiPageForms(options);

        $fbEditorStage.formBuilder(options).promise
            .then((formBuilderApi) => {
                formBuilderPage.formBuilderHasRendered = true;
                formBuilderPage.formBuilderApi = formBuilderApi;

                addSidebarTitle();
                disableDefaultFieldEditing();

                formBuilderPageApiResolve(formBuilderApi);
            });

        document.addEventListener('fieldRendered', function () {
            disableDefaultFieldEditing();
        }, true);

        $fbEditorStage.on('blur', '.fld-name', function (event) {
            event.target.value = ensureUniqueName(event.target.value, event.target);
        });

        $fbEditorStage.on('change', '.fld-className', function () {
            const $parentFormField = $(this).closest('.form-field');
            window.setTimeout(function () {
                setWidthClassesForField($parentFormField.attr('id'), $parentFormField.data('fieldData'));
            }, 350);

        });

        $fbEditorStage.on('blur', '.fld-maxlength', function (event) {
            event.target.value = ensureValidMaxLengthValue(event.target);
        });

        $fbEditorStage.on('fieldEditClosed', function () {
            disableDefaultFieldEditing();
        });

        $mainFormSettingsToggle.on('click', function () {
            $mainFormSettingsContainer.toggle('slow');
            $mainFormSettingsToggle
                .find('.centrex-fas')
                .toggleClass('centrex-fa-chevron-down centrex-fa-chevron-right');
        });

        /**
         * Add the customs field_id attribute to the following fields:
         * @param optionsToAddIdAttributesTo
         */
        function addCustomFieldIdAttributeToCustomFields(optionsToAddIdAttributesTo) {
            USER_CUSTOM_FIELD_TYPES.forEach((customFieldType) => {
                optionsToAddIdAttributesTo.typeUserAttrs = optionsToAddIdAttributesTo.typeUserAttrs || {};
                if (!optionsToAddIdAttributesTo.typeUserAttrs[customFieldType]) {
                    optionsToAddIdAttributesTo.typeUserAttrs[customFieldType] = {};
                }

                const customFieldTypeUserAttrs = optionsToAddIdAttributesTo.typeUserAttrs[customFieldType];
                customFieldTypeUserAttrs.customFieldId = {
                    label: 'Centrex Field Id',
                    value: '',
                    required: true,
                    description: 'This is your custom field ID defined in your Centrex settings.',
                };
            });
        }

        function getAllFieldNames(filterTarget) {
            const allFieldNamesExceptTarget = $('.fld-name', $fbEditorStage).filter((i, e) => {
                if (!filterTarget) {
                    return true;
                }
                return e !== filterTarget;
            }).map((i, input) => input.value);
            return new Set(Array.from(allFieldNamesExceptTarget));
        }

        function ensureUniqueName(nameBase, eventTarget) {
            const allFieldNames = getAllFieldNames(eventTarget);
            let generatedName = nameBase;
            for (let i = 1; allFieldNames.has(generatedName); i++) {
                generatedName = `${generatedName}_${i}`;
            }
            return generatedName;
        }

        function ensureValidMaxLengthValue(eventTarget) {
            const currentVal = eventTarget.value;
            if (+currentVal === 0) {
                return null;
            }
            return currentVal;
        }

        function disableEditingFor(inputClassName, filterFn) {
            const fieldsToDisable = $(inputClassName, $fbEditorStage)
                .filter(filterFn);
            fieldsToDisable.attr('disabled', true);
        }

        function disableDefaultFieldEditing() {
            if (!formBuilderPage.formBuilderHasRendered) {
                return;
            }
            const PREVENT_DEFAULT_OPTION_SELECTION_INPUT_TYPES = ['radio'];
            disableEditingFor('.fld-name', (index, element) => DIRECTLY_MAPPED_INPUTS.includes(element.value));
            disableEditingFor('.fld-subtype', (index, element) => {
                const parentFormField = $(element).closest('.form-field');
                const fieldData = parentFormField.data('fieldData');
                return DIRECTLY_MAPPED_INPUTS.some((directlyMappedInput) => fieldData.name?.startsWith(directlyMappedInput));
            });

            $fbEditorStage
                .find('.form-group input, .form-group select, .form-group textarea')
                .filter(function () {
                    const isPreviewInput = this.name && this.name.indexOf('-preview') > -1;
                    const isPartOfSelectOptions = $(this).parents('.field-options').length > 0 && PREVENT_DEFAULT_OPTION_SELECTION_INPUT_TYPES.includes(this.type);
                    return isPreviewInput || isPartOfSelectOptions;
                }).attr('disabled', true);
        }

        function addSidebarTitle() {
            const $rightHandSidebar = $fbEditorStage.find('.pull-right');
            const titleHtml = '<div class="centrex-sidebar-notice">' +
                '<h1 class="centrex-sbbuilder-h1">Form Builder Elements</h1>' +
                '<p>Click an item to add it to the bottom of the stage, or drag and drop it where you would like to place it.</p>' +
                '</div>';

            $rightHandSidebar.prepend(titleHtml);
        }

        function setWidthClassesForField(fieldId, fieldData) {
            const $fieldWrapper = $fbEditorStage.find('#' + fieldId);
            const classNames = (fieldData.className || '').split(' ');
            $fieldWrapper.removeClass('full-width half-width');

            if (classNames.includes('field-half')) {
                $fieldWrapper.addClass('half-width');
            } else {
                $fieldWrapper.addClass('full-width');
            }
        }

        function hideCustomFieldIDFromDefaultFields(fieldId, fieldData) {
            if (!DIRECTLY_MAPPED_INPUTS.includes(fieldData.name)) {
                return;
            }
            const $fieldWrapper = $fbEditorStage.find('#' + fieldId);
            const $customFieldIdInput = $fieldWrapper.find('.customFieldId-wrap');
            $customFieldIdInput.hide();
        }


        /**
         * Removes the "remove-field" button from the actions bar.
         * This prevents the user from deleting required fields.
         * Also disables the editing of the "required" checkbox so that the field is always required.
         * @param fieldId
         * @param fieldData
         */
        function disableRemovingOfRequiredFields(fieldId, fieldData) {
            if (!REQUIRED_FIELD_NAMES.includes(fieldData.name)) {
                return;
            }

            const $removeFieldActionButton = $fbEditorStage.find('#del_' + fieldId);
            $removeFieldActionButton.remove();

            const $requiredInput = $fbEditorStage.find('#required-' + fieldId);
            $requiredInput.attr('disabled', true);
        }

        /**
         * Sets fineUploader as the default file upload method.
         * see: https://github.com/FirstClassCode/centrex/issues/73
         * @param fieldData
         */
        function setFineUploaderAsDefault(fieldData) {
            fieldData.subtype = fieldData.subtype || 'fineuploader';
        }

        function ensurePlaidForPremiumOnly(fieldId, fieldData) {
            if (fieldData.type !== 'plaid') {
                return;
            }

            let hasPlaidAccess = true;
            if (!centrexApp.backendOptions.isProVersion) {
                hasPlaidAccess = false;
                formBuilderPage.setErrorMessage('The Plaid field is only available in Centrex Premium.');
            }

            if (hasPlaidAccess && !centrexApp.backendOptions.hasPlaidAccess) {
                hasPlaidAccess = false;
                formBuilderPage.setErrorMessage('Your account does not have access to the Plaid field. To enable this feature, email sales@centrexsoftware.com or call us at 888-622-5810.');
            }

            if (!hasPlaidAccess) {
                formBuilderPage.apiPromise.then((formBuilderApi) => formBuilderApi.actions.removeField(fieldId));
            }

        }
    };

})(jQuery, window.CENTREX_APP = window.CENTREX_APP || {});

// forms-list-specific code:
(function ($, centrexApp) {
    centrexApp.formsListPage = {
        init: function (formsListOptions) {

            const $formsListContainer = $('#centrex_form_list_table');
            const $deleteDialog = $('#centrex_delete_dialog');

            $formsListContainer.on('click', '.copy-shortcode', function () {
                const $copyShortCodeEl = $(this);
                const originalText = $copyShortCodeEl.html();
                if (!navigator.clipboard || !navigator.clipboard.writeText) {
                    return;
                }

                const formId = $copyShortCodeEl.data('formId');
                navigator.clipboard.writeText('[centrex_smart_app id="' + formId + '"]');

                const onTransitionEnd = function () {
                    $copyShortCodeEl.removeClass('copied');
                    $copyShortCodeEl.html(originalText);
                    $copyShortCodeEl.off('transitionend', onTransitionEnd);
                };
                $copyShortCodeEl.on('transitionend', onTransitionEnd);

                $copyShortCodeEl.text('Copied!');
                $copyShortCodeEl.addClass('copied');
            });

            $formsListContainer.on('click', '.delete-form-row', function () {
                const $deleteFormRowButton = $(this);
                const formIdToBeDeleted = $deleteFormRowButton.data('formId');
                const $formRowToBeDeleted = $('#centrexFormRow_' + formIdToBeDeleted);
                const formDeleteNonce = $deleteFormRowButton.data('formNonce');

                preventDeletionOfLastRow();
                if ($formRowToBeDeleted.hasClass('disable-deletion')) {
                    return false;
                }

                $deleteDialog.dialog({
                    resizable: false,
                    height: 'auto',
                    width: 400,
                    modal: true,
                    buttons: {
                        'Delete form': function () {
                            $deleteDialog.dialog('close');

                            $.post(ajaxurl, {
                                action: 'centrex_plugin_delete_form_row',
                                formId: formIdToBeDeleted,
                                formNonce: formDeleteNonce
                            }).always((response) => {
                                if (response && response.success) {
                                    $formRowToBeDeleted.addClass('is-deleting');
                                    const onTransitionEnd = function () {
                                        $formRowToBeDeleted.off('transitionend', onTransitionEnd);
                                        $formRowToBeDeleted.remove();
                                        preventDeletionOfLastRow();
                                    };
                                    $formRowToBeDeleted.on('transitionend', onTransitionEnd);
                                } else {
                                    alert('Could not delete row. Verify plugin and DB settings');
                                }
                            });
                        },
                        Cancel: function () {
                            $deleteDialog.dialog('close');
                        }
                    }
                });
            });

            $('#centrexAddNewFormButton').on('click', function () {
                const $addNewFormButton = $(this);
                const originalText = $addNewFormButton.text();

                $addNewFormButton.text('Creating new form...');

                $.post(ajaxurl, {
                    action: 'centrex_plugin_add_new_form',
                }).always((response) => {
                    if (response && response.success && response.data.redirectUrl) {
                        window.location = response.data.redirectUrl;
                    } else {
                        $addNewFormButton.text(originalText);
                        alert('Could not create new row. Verify plugin and DB settings');
                    }
                });
            });

            $formsListContainer.on('click', '.clone-form', function () {
                const $cloneFormButton = $(this);
                const formIdToClone = $cloneFormButton.data('formId');
                const formCloneNonce = $cloneFormButton.data('formNonce');

                $.post(ajaxurl, {
                    action: 'centrex_plugin_clone_form_row',
                    formId: formIdToClone,
                    formNonce: formCloneNonce
                }).always((response) => {
                    if (response && response.success && response.data?.redirectUrl) {
                        window.location = response.data.redirectUrl;
                    } else {
                        alert('There was an error cloning the row. Please try again later.');
                    }
                });
            });

            preventDeletionOfLastRow();

            function preventDeletionOfLastRow() {
                const $formRows = $formsListContainer.find('.centrex-form-row');
                if ($formRows.length < 2) {
                    $formRows.addClass('disable-deletion');
                } else {
                    $formRows.removeClass('disable-deletion');
                }
            }
        }
    };


})(jQuery, window.CENTREX_APP);