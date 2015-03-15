define([
    'service',
    'component/tableComponent',
    'component/mediaChooserComponent',
    'component/galleryComponent'
], function (Service, TableComponent, MediaChooserComponent, GalleryComponent) {
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

        //var instance = this;

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
            /*success: function (response, newValue) {
                instance.mcCmp.mediaManager.updateMedia({
                    id: $(this).data('mid'),
                    title: newValue
                });
            }*/
            pk: function () {
                return $(this).data('mid')
            },
            url: function (params) {
                return this.controller.edit(params.pk, {title: params.value}).promise();
            }.bind(this)
        });

        var sub = Service.get('subscriber');
        sub.dispatch(ListView.TAG, 'onViewLoaded', {
            view: this
        });

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
//                var file = $(e.target);
//
//                $(this.mediaManager).one(MediaManager.EVENT_DELETE_SUCCESS, function() {
//                    file.parent().parent().parent().remove();
//                });
//                $(this.mediaManager).one(MediaManager.EVENT_DELETE_ERROR, function(error, e) {
//                    if (e.status == 500) {
//                        alert(e.statusText);
//                    } else {
//                        alert('Error: ' + error);
//                    }
//                });
//
//                return this.mediaManager.deleteMedia(file.data("val"), Translator.trans('filesGateway.deleteConfirmMessage'));

                if (confirm(Translator.trans('filesGateway.deleteConfirmMessage'))) {
                    $.each(e.$selection, (function (index, element) {
                        //this.mcCmp.mediaManager.deleteMedia($(element).data("mid")/*, Translator.trans('filesGateway.deleteConfirmMessage')*/);
                        this.controller.delete($(element).data('mid'), true);
                    }).bind(this));
                }

                //FIXME: make me better
                setTimeout(function () {
                    window.location.reload(true);
                }, 1000);
                break;
        }
    };

    ListView.prototype._onClickFile = function (e) {
        e.stopPropagation();

        GalleryComponent.render({
            mediaIds: [$(e.currentTarget).data('mid')]
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
