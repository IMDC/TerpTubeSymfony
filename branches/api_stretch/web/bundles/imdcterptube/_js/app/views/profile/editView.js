define(function () {
    'use strict';

    var EditView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickAddLanguage = this._onClickAddLanguage.bind(this);
        this.bind__onClickDeleteLanguage = this._onClickDeleteLanguage.bind(this);

        this.$container = options.container;
        this.$addLang = this.$container.find(EditView.Binder.ADD_LANGUAGE);
        this.$delLang = this.$container.find(EditView.Binder.DELETE_LANGUAGE);

        this.$addLang.on('click', this.bind__onClickAddLanguage);
        this.$delLang.on('click', this.bind__onClickDeleteLanguage);

        $tt._instances.push(this);
    };

    EditView.TAG = 'ProfileEditView';

    EditView.Binder = {
        ADD_LANGUAGE: '.profile-add-lang',
        DELETE_LANGUAGE: '.profile-del-lang'
    };

    EditView.prototype._onClickAddLanguage = function(e) {
        e.preventDefault();

        var languageList = $("#foss_user_profile_form_languages");
        var deleteButton;
        var deleteLanguageButtonText = languageList.data("delete-language-text");
        var newWidget = languageList.data("prototype"); // grab the prototype template
        var languageCount = $("#foss_user_profile_form_languages > li").length;

        $('#no-languages').remove();

        //TODO dustjs?
        deleteButton = $("<button class=\"btn btn-danger btn-sm profile-del-lang\"></a>").html(deleteLanguageButtonText);
        deleteButton.on('click', this.bind__onClickDeleteLanguage);

        // replace the "__name__" used in the id and name of the prototype
        // with a number that's unique to your languages
        newWidget = newWidget.replace(/__name__/g, languageCount);
        languageCount++;

        // create a new list element and add it to the list
        var newLi = $("<li></li>").html(newWidget);
        newLi.append(deleteButton);
        $('#foss_user_profile_form_languages').append(newLi);
    };

    EditView.prototype._onClickDeleteLanguage = function(e) {
        e.preventDefault();

        var languageList = $("#foss_user_profile_form_languages");
        var emptyTitle = languageList.data("no-languages-text");
        var noLanguages = $("<span id=\"no-languages\"></span>").html(emptyTitle); //TODO dustjs?

        $(e.target).parent().remove();

        if ($("#foss_user_profile_form_languages > li").length == 0) {
            $('#foss_user_profile_form_languages').append(noLanguages);
        }
    };

    return EditView;
});
