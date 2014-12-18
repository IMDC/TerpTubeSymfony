define(['core/mediaManager'], function(MediaManager) {
    "use strict";

    var MyFilesSelector = function(options) {
        var defaults = {
            multiSelect: true,
            filter: {}
        };

        options = options || defaults;
        for (var o in defaults) {
            this[o] = typeof options[o] != "undefined" ? options[o] : defaults[o];
        }

        //this.container = options.container;

        this.mediaManager = new MediaManager();

        this.bind__onRender = this._onRender.bind(this);
        this.bind__onShownModal = this._onShownModal.bind(this);
        this.bind__onHiddenModal = this._onHiddenModal.bind(this);
        this.bind__onClickBulkAction = this._onClickBulkAction.bind(this);
        this.bind__onClickFile = this._onClickFile.bind(this);
        this.bind__onClickSelectSelected = this._onClickSelectSelected.bind(this);
        this.bind__onGetMediaInfoSuccess = this._onGetMediaInfoSuccess.bind(this);
        this.bind__onGetMediaInfoError = this._onGetMediaInfoError.bind(this);
        this.bind__onLoadPageSuccess = this._onLoadPageSuccess.bind(this);
        this.bind__onLoadPageError = this._onLoadPageError.bind(this);

        $(this.mediaManager).on(MediaManager.Event.GET_INFO_SUCCESS, this.bind__onGetMediaInfoSuccess);
        $(this.mediaManager).on(MediaManager.Event.GET_INFO_ERROR, this.bind__onGetMediaInfoError);

        //dust.compileFn($("#myFilesSelector").html(), "myFilesSelector");
    };

    MyFilesSelector.TAG = "MyFilesSelector";

    MyFilesSelector.Event = {
        READY: "eventReady",
        DONE: "eventDone",
        HIDDEN: "eventHidden"
    };

    MyFilesSelector.Binder = {
        MODAL_DIALOG: ".my-files-selector-modal",
        BULK_ACTION: ".my-files-selector-bulk-action",
        TOGGLE_SELECTION: ".my-files-selector-toggle-selection",
        TOGGLE_STYLE: ".my-files-selector-toggle-style",
        FILES_LIST: ".my-files-selector-files-list",
        FILE: ".my-files-selector-file",
        SELECT_SELECTED: ".my-files-selector-select-selected"
    };

    MyFilesSelector.prototype.getContainer = function() {
        return $("body");
    };

    MyFilesSelector.prototype._getElement = function(binder) {
        return this.getContainer().find(binder);
    };

    MyFilesSelector.prototype._onClickBulkAction = function(e) {
        var action = this._getElement(MyFilesSelector.Binder.BULK_ACTION).data("action");
        var selectedFiles = this._getSelectedFiles();

        switch (action) {
            case 1:
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

                //FIXME multi confirmation
                $.each(selectedFiles, (function(index, element) {
                    this.mediaManager.deleteMedia($(element).data("mid")/*, Translator.trans('filesGateway.deleteConfirmMessage')*/);
                }).bind(this));

                //FIXME: make me better
                setTimeout(function() {
                    window.location.reload(true);
                }, 1000);
                break;
        }
    };

    MyFilesSelector.prototype._onClickFile = function(e) {
        e.stopPropagation();

        this.gallery = new $tt.Core.Gallery({
            mode: $tt.Core.Gallery.Mode.PREVIEW
        });
        $(this.gallery).on($tt.Core.Gallery.Event.READY, (function(e) {
            this.gallery.show();
        }).bind(this));
        $(this.gallery).on($tt.Core.Gallery.Event.HIDDEN, (function(e) {
            this.gallery.destroy();
        }).bind(this));
        this.gallery.render([$(e.currentTarget).data("mid")]);
    };

    MyFilesSelector.prototype._bindUIEventsFilesList = function() {
        var container = this._getElement(MyFilesSelector.Binder.FILES_LIST);
        var checkBoxes = container.find("input[type='checkbox']");

        this._getElement(MyFilesSelector.Binder.BULK_ACTION).on("click", this.bind__onClickBulkAction);

        if (this.multiSelect) {
            this._getElement("input" + MyFilesSelector.Binder.TOGGLE_SELECTION).on("change", function(e) {
                checkBoxes.prop("checked", $(e.target).prop("checked"));
            });
            this._getElement("button" + MyFilesSelector.Binder.TOGGLE_SELECTION).on("click", function(e) {
                checkBoxes.prop("checked", container.find("input:checked").length == 0);
            });

            this._getElement(MyFilesSelector.Binder.TOGGLE_SELECTION).attr("disabled", false);
        }

        checkBoxes.on("change", (function(e) {
            var checked = $(e.target).prop("checked");
            if (!this.multiSelect) {
                checkBoxes.prop("checked", false);
            }
            $(e.target).prop("checked", checked);

            this._getElement(MyFilesSelector.Binder.SELECT_SELECTED)
                .attr("disabled", this._getSelectedFiles().length == 0);
        }).bind(this));

        checkBoxes.trigger("change");

        this._getElement(MyFilesSelector.Binder.FILE).not("disabled").on("click", this.bind__onClickFile);

        var instance = this;
        container.find("span.edit-title").editable({
            toggle: 'manual',
            unsavedclass: null,
            success: function(response, newValue) {
                instance.mediaManager.updateMedia({
                    id: $(this).data('mid'),
                    title: newValue
                });
            }
        });

        container.find("button.edit-title").on("click", function(e) {
            e.stopPropagation();
            $(this).parent().prev().editable('toggle');
        });

        var urlOverride = (function(e) {
            e.preventDefault();
            this._loadPage($(e.currentTarget).attr("href"));
        }).bind(this);

        this._getElement(MyFilesSelector.Binder.MODAL_DIALOG)
            .find(MyFilesSelector.Binder.TOGGLE_STYLE)
            .on("click", urlOverride);

        // KnpPaginatorBundle:Pagination:twitter_bootstrap_v3_pagination.html.twig
        // override pagination urls
        this._getElement(MyFilesSelector.Binder.MODAL_DIALOG)
            .find("ul.pagination li a")
            .on("click", urlOverride);
    };

    MyFilesSelector.prototype._onLoadPageSuccess = function(data, textStatus, jqXHR) {
        this._getElement(MyFilesSelector.Binder.MODAL_DIALOG)
            .find(".modal-body")
            .html(data.page);
        this._bindUIEventsFilesList();
    };

    MyFilesSelector.prototype._onLoadPageError = function(jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
    };

    MyFilesSelector.prototype._makeUrl = function(url) {
        if (typeof url === "undefined")
            return Routing.generate("imdc_myfiles_list", this.filter);

        for (var key in this.filter) {
            url += (url.substr("?") ? "&" : "?") + key + "=" + this.filter[key];
        }

        return url;
    };

    MyFilesSelector.prototype._loadPage = function(url) {
        $.ajax({
            url: this._makeUrl(url),
            success: this.bind__onLoadPageSuccess,
            error: this.bind__onLoadPageError
        });
    };

    MyFilesSelector.prototype._onShownModal = function(e) {
        this._loadPage();
    };

    MyFilesSelector.prototype._onHiddenModal = function(e) {
        $(this).trigger($.Event(MyFilesSelector.Event.HIDDEN, {}));
    };

    MyFilesSelector.prototype._onGetMediaInfoSuccess = function(e) {
        //data, textStatus, jqXHR
        $(this).trigger($.Event(MyFilesSelector.Event.DONE, {media: e.payload.media}));
    };

    MyFilesSelector.prototype._onGetMediaInfoError = function(e) {
        //jqXHR, textStatus, errorThrown
        this._getElement(MyFilesSelector.Binder.SELECT_SELECTED).button("reset");

        console.log(e.jqXHR);
    };

    MyFilesSelector.prototype._getSelectedFiles = function(e) {
        return this._getElement(MyFilesSelector.Binder.FILES_LIST).find("input:checked");
    };

    MyFilesSelector.prototype._onClickSelectSelected = function(e) {
        e.preventDefault();

        $(e.target).button("loading");

        var selectedFiles = this._getSelectedFiles();
        var mediaIds = [];

        if (this.multiSelect) {
            selectedFiles.each(function(index, element) {
                mediaIds.push($(element).data("mid"));
            });
        } else {
            mediaIds.push(selectedFiles.first().data("mid"));
        }

        this.mediaManager.getInfo(mediaIds);
    };

    MyFilesSelector.prototype._bindUIEvents = function() {
        var modal = this._getElement(MyFilesSelector.Binder.MODAL_DIALOG);
        modal.modal({backdrop: "static", show: false});
        modal.on("shown.bs.modal", this.bind__onShownModal);
        modal.on("hidden.bs.modal", this.bind__onHiddenModal);

        this._getElement(MyFilesSelector.Binder.SELECT_SELECTED).on("click", this.bind__onClickSelectSelected);
    };

    MyFilesSelector.prototype._onRender = function(err, out) {
        this.getContainer().append(out);
        this._bindUIEvents();

        $(this).trigger($.Event(MyFilesSelector.Event.READY, {}));
    };

    MyFilesSelector.prototype.render = function() {
        dust.render("myFilesSelector", {}, this.bind__onRender);
    };

    MyFilesSelector.prototype.show = function() {
        this._getElement(MyFilesSelector.Binder.MODAL_DIALOG).modal("show");
    };

    MyFilesSelector.prototype.hide = function() {
        this._getElement(MyFilesSelector.Binder.MODAL_DIALOG).modal("hide");
    };

    MyFilesSelector.prototype.destroy = function() {
        this._getElement(MyFilesSelector.Binder.MODAL_DIALOG).remove();
    };

    MyFilesSelector.prototype.bindUIEvents = function() {
        this._bindUIEventsFilesList();
    };

    return MyFilesSelector;
});
