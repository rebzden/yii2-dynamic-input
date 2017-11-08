function DynamicInput(inputOptions) {

    var options = inputOptions;
    var regexID = /\b\S[-\d-]{1,}/i;
    var regexName = /\[\d]/i;

    function updateId(oldElementId) {
        return oldElementId.replace(regexID, '-' + options.currentIndex + '-');
    }

    function updateName(oldElementName) {
        return oldElementName.replace(regexName, '[' + options.currentIndex + ']');
    }

    function changeElementName(element) {
        element.attr('name', updateName(element.attr('name')));
    }

    function addToForm(element) {
        if ($("#" + options.form).yiiActiveForm("find", element.attr('id')) == undefined) {
            // $("#" + options.form).yiiActiveForm("remove", element.attr('id'));
            $('#' + options.form).yiiActiveForm('add', {
                id: element.attr('id'),
                name: element.attr('name').replace(options.className, ""),
                container: '.field-' + element.attr('id'),
                input: '#' + element.attr('id'),
                error: '.help-block',
                enableAjaxValidation: true,
                value: element.val()
            });
        }
    }

    function createNextItem(template) {
        options.currentIndex += 1;
        var templateClone = $(template.content).clone(false, false);
        var inputs = [];
        templateClone.find('input, select, textarea').each(function () {
            var element = $(this);
            var oldElementId = element.attr('id');
            var newElementId = updateId(oldElementId);
            element.attr('id', newElementId);
            templateClone.find("label[for='" + oldElementId + "']").attr('for', newElementId);
            processParentClasses(element, oldElementId);
            changeElementName(element);
            inputs.push(element);
        });
        restoreSpecialJs(templateClone);
        return {template: templateClone, inputs: inputs};
    }

    function processParentClasses(inputElem, inputId) {
        var foundClasses = [];
        var regexId = new RegExp("\\b[\\S]{0,}" + inputId, 'gi');
        inputElem.parent('[class*="' + inputId + '"]').removeClass(function (index, className) {
            var matchedClasses = className.match(regexId);
            foundClasses = matchedClasses;
            return matchedClasses.join(' ');
        }).addClass(function () {
            var replacedClasses = foundClasses.map(function (className) {
                return updateId(className);
            });
            return replacedClasses.join(' ');
        });
    }

    function addItem() {
        var newItem = createNextItem(document.querySelector('.dynamicTemplate-'+options.widgetId));
        $('.dynamic-input-container-' + options.widgetId).append(newItem.template);
        newItem.inputs.forEach(function (element) {
            addToForm(element);
        });
    }

    function removeItem(inputIndex) {
        $(".dynamic-input-" + options.widgetId + "[data-id=" + inputIndex + "]").remove();
    }

    function refreshExistingInput() {
        $('.dynamic-input-container-' + options.widgetId + ' .dynamic-input-' + options.widgetId).each(function () {
            $(this).find('input, select, textarea').each(function () {
                addToForm($(this));
            });
        });
    }

    function refreshWidgets() {
        $('.dynamic-input-container-' + options.widgetId + ' .dynamic-input-' + options.widgetId).each(function () {
            processSelect2($(this));
        });
    }

    function init() {
        $('body').on('click', '.add-button', function () {
            addItem();
        });
        $('body').on('click', '.remove-button', function () {
            removeItem($(this).data('id'));
        });
        $('#' + options.form).on('afterInit', function () {
            refreshExistingInput();
        });
        refreshWidgets();
    }

    function processSelect2(element) {
        element.find('select[data-krajee-select2]').each(function () {
            var $el = $(this), settings = window[$el.attr('data-krajee-select2')] || {};
            if ($el.data('select2')) {
                $el.select2('destroy');
            }
            $.when($el.select2(settings)).done(function () {
                $el.select2("val", $el.attr('value'));
                $el.parent().find('.kv-plugin-loading').remove();// temporary solution find better way
                initS2Loading($el.attr('id'), '.select2-container--krajee'); // jshint ignore:line
            });
        });
    }

    function restoreSpecialJs(element) {
        processSelect2(element);
    }


    init();
}