define([
    'core/subscriber',
    'service',
    'component/tableComponent',
    'factory/mediaFactory',
    'extra'
], function (Subscriber, Service, TableComponent, MediaFactory) {
    'use strict';

    var MyFilesSelectorComponent = function (options) {
        Subscriber.prototype.constructor.apply(this);

        this.options = options;

        this.bind__onShownModal = this._onShownModal.bind(this);
        this.bind__onHiddenModal = this._onHiddenModal.bind(this);
        this.bind__onLoadPageSuccess = this._onLoadPageSuccess.bind(this);
        this.bind__onLoadPageError = this._onLoadPageError.bind(this);
        this.bind__onClickSelectSelected = this._onClickSelectSelected.bind(this);
        this.bind__onMyFilesListViewViewLoaded = this._onMyFilesListViewViewLoaded.bind(this);
        this.bind__onSelectionChange = this._onSelectionChange.bind(this);

        this.$container = this.options.$container;
        this.$modalDialog = this.$container.find(MyFilesSelectorComponent.Binder.MODAL_DIALOG);
        this.$selectSelected = this.$container.find(MyFilesSelectorComponent.Binder.SELECT_SELECTED);

        this.$modalDialog.modal({backdrop: 'static', show: false});
        this.$modalDialog.on('shown.bs.modal', this.bind__onShownModal);
        this.$modalDialog.on('hidden.bs.modal', this.bind__onHiddenModal);
        this.$selectSelected.on('click', this.bind__onClickSelectSelected);

        var sub = Service.get('subscriber');
        sub.subscribe('onViewLoaded', this.bind__onMyFilesListViewViewLoaded);
    };

    MyFilesSelectorComponent.extend(Subscriber);

    MyFilesSelectorComponent.TAG = 'MyFilesSelectorComponent';

    MyFilesSelectorComponent.Binder = {
        MODAL_DIALOG: '.my-files-selector-modal',
        SELECT_SELECTED: '.my-files-selector-select-selected'
    };

    MyFilesSelectorComponent.Event = {
        DONE: 'eventDone',
        HIDDEN: 'eventHidden'
    };

    MyFilesSelectorComponent.prototype._onSelectionChange = function (e) {
        this.$selectSelected.attr('disabled', e.$selection.length == 0);
    };

    MyFilesSelectorComponent.prototype._onMyFilesListViewViewLoaded = function (e) {
        var MyFilesListView = require('views/myFiles/listView');

        if (e.tag !== MyFilesListView.TAG) {
            return;
        }

        this.tblCmp = e.view.getTableComponent();
        this.tblCmp.setMultiSelect(this.options.multiSelect);
        this.tblCmp.subscribe(TableComponent.Event.SELECTION_CHANGE, this.bind__onSelectionChange);

        var urlOverride = (function (e) {
            e.preventDefault();
            this._loadPage($(e.currentTarget).attr('href'));
        }).bind(this);

        this.$modalDialog.find(MyFilesListView.Binder.TOGGLE_STYLE).on('click', urlOverride);

        // KnpPaginatorBundle:Pagination:twitter_bootstrap_v3_pagination.html.twig
        // override pagination urls
        this.$modalDialog.find('ul.pagination li a').on('click', urlOverride);
    };

    MyFilesSelectorComponent.prototype._onLoadPageSuccess = function (data, textStatus, jqXHR) {
        this.$modalDialog.find('.modal-body').html(data.page);
    };

    MyFilesSelectorComponent.prototype._onLoadPageError = function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
    };

    MyFilesSelectorComponent.prototype._makeUrl = function (url) {
        if (typeof url === 'undefined')
            return Routing.generate('imdc_myfiles_list', this.options.filter);

        for (var key in this.filter) {
            url += (url.substr('?') ? '&' : '?') + key + '=' + this.options.filter[key];
        }

        return url;
    };

    MyFilesSelectorComponent.prototype._loadPage = function (url) {
        $.ajax({
            url: this._makeUrl(url),
            success: this.bind__onLoadPageSuccess,
            error: this.bind__onLoadPageError
        });
    };

    MyFilesSelectorComponent.prototype._onShownModal = function (e) {
        this._loadPage();
    };

    MyFilesSelectorComponent.prototype._onHiddenModal = function (e) {
        this._dispatch(MyFilesSelectorComponent.Event.HIDDEN, {
            myFilesSelectorComponent: this
        });
    };

    MyFilesSelectorComponent.prototype._onClickSelectSelected = function (e) {
        e.preventDefault();

        this.$selectSelected.button('loading');

        var selectedFiles = this.tblCmp.getSelection();
        var mediaIds = [];

        if (this.options.multiSelect) {
            selectedFiles.each(function (index, element) {
                mediaIds.push($(element).data('mid'));
            });
        } else {
            mediaIds.push(selectedFiles.first().data('mid'));
        }

        MediaFactory.list(mediaIds)
            .done(function (data) {
                this._dispatch(MyFilesSelectorComponent.Event.DONE, {
                    media: data.media,
                    myFilesSelectorComponent: this
                });
            }.bind(this))
            .fail(function () {
                this.$selectSelected.button('reset');

                console.error('%s: media factory list', MyFilesSelectorComponent.TAG);
            }.bind(this));
    };

    MyFilesSelectorComponent.prototype.show = function () {
        this.$modalDialog.modal('show');
    };

    MyFilesSelectorComponent.prototype.hide = function () {
        this.$modalDialog.modal('hide');
    };

    MyFilesSelectorComponent.prototype.destroy = function () {
        this.$modalDialog.remove();
    };

    MyFilesSelectorComponent.render = function (options, callback) {
        var defaults = {
            $container: $('body'),
            multiSelect: true,
            filter: {}
        };

        options = options || defaults;
        for (var o in defaults) {
            options[o] = typeof options[o] != 'undefined' ? options[o] : defaults[o];
        }

        dust.render('myFilesSelector', {}, function (err, out) {
            options.$container.append(out);

            var cmp = new MyFilesSelectorComponent(options);
            callback.call(cmp, {
                myFilesSelectorComponent: cmp
            });
        });
    };

    return MyFilesSelectorComponent;
});
