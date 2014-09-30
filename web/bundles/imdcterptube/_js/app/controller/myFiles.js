define(['core/mediaChooser', 'core/mediaManager'], function(MediaChooser, MediaManager) {
    "use strict";

    var MyFiles = function(options) {
        console.log("%s: %s- options=%o", MyFiles.TAG, "constructor", options);

        this.page = options.page;
        this.mediaChooser = null;
        this.mediaManager = new MediaManager();

        this.bind__onPreviewButtonClick = this.onPreviewButtonClick.bind(this);
        this.bind__onDeleteButtonClick = this.onDeleteButtonClick.bind(this);
        this.bind__onPageLoaded = this._onPageLoaded.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onDialogClose = this._onDialogClose.bind(this);

        dust.compileFn($("#mediaRow").html(), "mediaRow");

        $tt._instances.push(this);
    };

    MyFiles.TAG = "MyFiles";

    MyFiles.Page = {
        INDEX: 0,
        PREVIEW: 1
    };

    MyFiles.prototype.getContainer = function() {
        return $("body");
    };

    MyFiles.prototype.bindUIEvents = function() {
        console.log("%s: %s", MyFiles.TAG, "bindUIEvents");

        switch (this.page) {
            case MyFiles.Page.INDEX:
                this._bindUIEventsIndex();
                break;
            case MyFiles.Page.PREVIEW:
                break;
        }
    };

    MyFiles.prototype._bindUIEventsIndex = function() {
        console.log("%s: %s", MyFiles.TAG, "_bindUIEventsIndex");

        this.mediaChooser = new MediaChooser({isFileSelection: false});
        $(this.mediaChooser).on(MediaChooser.Event.PAGE_LOADED, this.bind__onPageLoaded);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.DIALOG_CLOSE, this.bind__onDialogClose);
        this.mediaChooser.setContainer(this.getContainer());
        this.mediaChooser.bindUIEvents();

        $(".preview-button").on("click", this.bind__onPreviewButtonClick);
        $(".delete-button").on("click", this.bind__onDeleteButtonClick);
    };

    MyFiles.prototype.onPreviewButtonClick = function(e) {
        e.preventDefault();
        console.log("Preview");
        if ($(e.target).hasClass("disabled")) {
            return false;
        }
        this.page = MyFiles.Page.PREVIEW;
        this.mediaChooser.setMedia({id: $(e.target).data("val")});
        this.mediaChooser.previewMedia();
    };

    MyFiles.prototype.onDeleteButtonClick = function(e) {
        e.preventDefault();

        var file = $(e.target);

        $(this.mediaManager).one(MediaManager.EVENT_DELETE_SUCCESS, function() {
            file.parent().parent().parent().remove();
        });
        $(this.mediaManager).one(MediaManager.EVENT_DELETE_ERROR, function(error, e) {
            if (e.status == 500) {
                alert(e.statusText);
            } else {
                alert('Error: ' + error);
            }
        });

        return this.mediaManager.deleteMedia(file.data("val"), $("#mediaDeleteConfirmMessage").html());

        /*var response = confirm($("#mediaDeleteConfirmMessage").html());
        if (!response) {
            return false;
        }
        var file = $(e.target);
        var address = file.data("url");

        $.ajax({
            url: address,
            type: "POST",
            contentType: "application/x-www-form-urlencoded",
            data: {
                mediaId: file.data("val")
            },
            success: function(data) {
                if (data.responseCode == 200) {
                    file.parent().parent().remove();
                } else if (data.responseCode == 400) { // bad request
                    alert('Error: ' + data.feedback);
                } else {
                    alert('An unexpected error occured');
                }
            },
            error: function(request) {
                console.log(request.statusText);
            }
        });*/
    };

    MyFiles.prototype._onPageLoaded = function(e) {
        console.log("%s: %s", MyFiles.TAG, "_onPageLoaded");

        switch (this.mediaChooser.page) {
            case MediaChooser.Page.RECORD_VIDEO:
                this.mediaChooser.createVideoRecorder();
                break;
            case MediaChooser.Page.PREVIEW:
                if (e.payload.media.type == MediaChooser.MEDIA_TYPE.VIDEO.id)
                    this.mediaChooser.createVideoPlayer();

                break;
        }
    };

    MyFiles.prototype._onSuccess = function(e) {
        switch (this.page) {
            case MyFiles.Page.INDEX:
                this._addMediaRow(e.media); //FIXME pagination makes this impractical
                break;
            case MyFiles.Page.PREVIEW:
                console.log("Done previewing");
                break;
        }
    };

    MyFiles.prototype._onDialogClose = function(e) {
        switch (this.page) {
            case MyFiles.Page.PREVIEW:
                this.page = MyFiles.Page.INDEX;
                console.log("Terminating function called");
                console.log(e.media);
                this._updateMediaRow(e.media);
                break;
        }
    };

    MyFiles.prototype._addMediaRow = function(media) { //FIXME pagination makes this impractical
        console.log("%s: %s", MyFiles.TAG, "_addMediaRow");

        var data = {
                media: media
        };

        if (media.isReady == 0) {
            data.previewDisabled = true;
        }

        //TODO revise
        switch (media.type) {
        case 0:
            data.icon = "fa-picture-o";
            data.mediaType = "Image";
            break;
        case 1:
            data.icon = "fa-film";
            data.mediaType = "Video";
            data.canInterpret = true;
            break;
        case 2:
            data.icon = "fa-headphones";
            data.mediaType = "Audio";
            data.canInterpret = true;
            break;
        default:
            data.icon = "fa-film";
            data.mediaType = "Other";
            break;
        }

        //TODO revise
        var timeUploaded = new Date(media.metaData.timeUploaded.date);
        var ampm = timeUploaded.getHours()>=12?"pm":"am";
        var hours = (timeUploaded.getHours()>12?timeUploaded.getHours()-12:timeUploaded.getHours());
        hours = hours<10?"0"+hours:hours;
        var time = hours+":"+(timeUploaded.getMinutes()<10?"0"+timeUploaded.getMinutes():timeUploaded.getMinutes())+ampm ;
        var timeDateString = time+" "+$.datepicker.formatDate('M d', timeUploaded);

        data.dateString = timeDateString;

        if (media.metaData.size > 0) {
            data.mediaSize = (media.metaData.size / 1024 / 1024).toFixed(2);
        }
        var instance = this;

        data.deleteUrl = Routing.generate('imdc_myfiles_delete', { mediaId: media.id });
        data.previewUrl = Routing.generate('imdc_myfiles_preview', { mediaId: media.id });
        data.newThreadUrl = Routing.generate('imdc_thread_new_from_media', { resourceid: media.id });
        data.simulRecordUrl = ""; //Routing.generate('imdc_media_simultaneous_record', { mediaID: media.id });

        dust.render("mediaRow", data, function(err, out) {
            $(".tt-myFiles-table").append(out);
        });

        $(".preview-button:last").on("click", this.bind__onPreviewButtonClick);
        $(".delete-button:last").on("click", this.bind__onDeleteButtonClick);
    };

    MyFiles.prototype._updateMediaRow = function(media) {
        //At this points it updates the title and the file-size
        console.log("%s: %s", MyFiles.TAG, "_updateMediaRow");

        var data = {
                media: media
        };

        if (media.metaData.size > 0) {
            data.mediaSize = (media.metaData.size / 1024 / 1024).toFixed(2) + " MB";
        }
        data.title = media.title;
        var row = $('a[data-val|='+data.media.id+']').eq(0).parents('tr').eq(0);
        console.log(row);
        row.children().eq(1).text(data.title);
        row.children().eq(4).text(data.mediaSize);
        var instance = this;

    };

    return MyFiles;
});
