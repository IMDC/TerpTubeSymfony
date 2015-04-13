define([
    'service',
    'model/model',
    'component/tableComponent',
    'component/mediaChooserComponent',
    'component/galleryComponent'
], function (Service, Model, TableComponent, MediaChooserComponent, GalleryComponent) {
    'use strict';

    var ListView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickBulkAction = this._onClickBulkAction.bind(this);
        this.bind__onClickFile = this._onClickFile.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onError = this._onError.bind(this);

        this.$container = options.container;
        this.$filesList = this.$container.find(ListView.Binder.FILES_LIST);
        this.$files = this.$container.find(ListView.Binder.FILE);

        this.tblCmp = TableComponent.table(this.$filesList);
        this.tblCmp.subscribe(TableComponent.Event.CLICK_BULK_ACTION, this.bind__onClickBulkAction);

        this.$filesList.find('button.edit-title').on('click', function (e) {
            e.stopPropagation();
            $(this).parent().prev().editable('toggle');
        });

        this.$files.not(':disabled').on('click', this.bind__onClickFile);

        this.mcCmp = MediaChooserComponent.render(this.$container);
        this.mcCmp.subscribe(MediaChooserComponent.Event.SUCCESS, this.bind__onSuccess);
        this.mcCmp.subscribe(MediaChooserComponent.Event.RESET, this.bind__onReset);
        this.mcCmp.subscribe(MediaChooserComponent.Event.ERROR, this.bind__onError);
        
        this.$filesList.find('span.edit-title').editable({
            toggle: 'manual',
            unsavedclass: null,
            pk: function () {
                return $(this).data('mid')
            },
            url: function (params) {
                return this.controller.edit(params.pk, {title: params.value});
            }.bind(this)
        });

        var sub = Service.get('subscriber');
        sub.dispatch(ListView.TAG, 'onViewLoaded', {
            view: this
        });

        // not exactly needed. just an example
        this.controller.model.subscribe(Model.Event.CHANGE, function (e) {
            var media = e.model.get(e.keyPath);
            var $file = this.$files.filter('span[data-mid="' + media.get('id') + '"]');
            $file.html(media.get('title'));
        }.bind(this));

        $tt._instances.push(this);
    };

    ListView.TAG = 'MyFilesListView';

    ListView.Binder = {
        TOGGLE_STYLE: '.my-files-selector-toggle-style',
        FILES_LIST: '.my-files-selector-files-list',
        FILE: '.my-files-selector-file'
    };

    ListView.prototype._onClickBulkAction = function (e) {
        switch (e.action) {
            case 1: // delete
                //FIXME confirmation. update controller to allow mass deletion
                if (confirm(Translator.trans('filesGateway.deleteConfirmMessage'))) {
                    $.each(e.$selection, (function (index, element) {
                        this.controller.delete($(element).data('mid'), true);
                    }).bind(this));
                }

                //FIXME: make me better
                setTimeout(function () {
                    window.location.reload(true);
                }, 2000);
                break;
        }
    };

    ListView.prototype._onClickFile = function (e) {
        e.stopPropagation();

        var media = this.controller.model.getMedia($(e.currentTarget).data('mid'));
        if (!media) {
            throw new Error('media not found');
        }

        GalleryComponent.render({
            //mediaIds: [$(e.currentTarget).data('mid')]
            media: this.controller.model.get('media'),
            activeMedia: media
        }, function (e) {
            this.galleryCmp = e.galleryComponent;
            this.galleryCmp.subscribe(GalleryComponent.Event.HIDDEN, function (e) {
                this.galleryCmp.destroy();
            }.bind(this));
            this.galleryCmp.show();
        }.bind(this));
    };

    ListView.prototype.getTableComponent = function () {
        return this.tblCmp;
    };

    ListView.prototype._onSuccess = function (e) {
        window.location.reload(true); //TODO load to last page?
    };
    
    ListView.prototype._onError = function (e) {
        alert('Error: ' + e.error);
    };

    return ListView;
});
