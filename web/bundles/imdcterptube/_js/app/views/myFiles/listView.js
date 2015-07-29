define([
    'service',
    'model/model',
    'component/tableComponent',
    'component/mediaChooserComponent',
    'component/galleryComponent',
    'core/helper'
], function (Service, Model, TableComponent, MediaChooserComponent, GalleryComponent, Helper) {
    'use strict';

    var ListView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickBulkAction = this._onClickBulkAction.bind(this);
        this.bind__onClickFile = this._onClickFile.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onError = this._onError.bind(this);
        this.bind__onAddGridElement = this._onAddGridElement.bind(this);
        this.bind__onAddListElement = this._onAddListElement.bind(this);

        this.$container = options.container;
        this.$filesList = this.$container.find(ListView.Binder.FILES_LIST);
        this.$fileButtons = this.$container.find(ListView.Binder.FILE_BUTTON);

        this.tblCmp = TableComponent.table(this.$filesList);
        this.tblCmp.subscribe(TableComponent.Event.CLICK_BULK_ACTION, this.bind__onClickBulkAction);

        this.$filesList.find('button.edit-title').on('click', function (e) {
            e.stopPropagation();
            $(this).parent().parent().parent().find('span.edit-title').editable('toggle');
        });

        this.$fileButtons.not('[disabled]').on('click', this.bind__onClickFile);

        if (this.$filesList.children('div.table-responsive').length > 0)
        {
           this.style = ListView.Binder.STYLE_LIST;
        }
        else
        {
            this.style = ListView.Binder.STYLE_GRID;
        }
        
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

        //TODO update/re-render the entire thumbnail container
        this.controller.model.subscribe(Model.Event.CHANGE, function (e) {
            var media = e.model.get(e.keyPath);
            var $title = this.$filesList.find('span.edit-title[data-mid="' + media.get('id') + '"]');
            $title.html(media.get('title'));
        }.bind(this));

        $tt._instances.push(this);
    };

    ListView.TAG = 'MyFilesListView';

    ListView.Binder = {
        TOGGLE_STYLE: '.my-files-selector-toggle-style',
        FILES_LIST: '.my-files-selector-files-list',
        FILE_BUTTON: '.my-files-selector-file-button',
        STYLE_LIST: 'list',
        STYLE_GRID: 'grid'
    };
    
    ListView.MAX_PER_PAGE = 24;
    ListView.MAX_PER_ROW = 4;

    ListView.prototype._onClickBulkAction = function (e) {
        switch (e.action) {
            case 1: // delete
                // FIXME confirmation. update controller to allow mass deletion
                if (confirm(Translator.trans('filesGateway.deleteConfirmMessage'))) {
                    $.each(e.$selection, (function (index, element) {
                        this.controller.delete($(element).data('mid'), true);
                        var $fullElement; 
                        if (this.style == ListView.Binder.STYLE_LIST)
                            $fullElement = $(element).parents('tr');
                        else
                            $fullElement = $(element).parents('div.col-md-3');
                        $fullElement.fadeOut(1000, function() {
                    		$fullElement.remove();
                        });
                        $(element).prop('checked', false);
                    }).bind(this));
                    
                    var wait = setInterval((function () {
                	this.$filesList = this.$container.find(ListView.Binder.FILES_LIST);
                	this.$fileButtons = this.$container.find(ListView.Binder.FILE_BUTTON);
                	var elements = $(this.$fileButtons).parent("div.thumbnail.tt-grid-div-body").parent();
                	if (!$(elements).is(":animated"))
                	{
                	    this.tblCmp.updateElements(this.$filesList);
                	    clearInterval(wait)
                	    if (this.style == ListView.Binder.STYLE_GRID)
                	    {
                		var allRows = $(elements).parent();
                		var count = $(this.$fileButtons).parent("div.thumbnail.tt-grid-div-body").length;
                		for (var i=1; i<count; i++)
                		{
                		    if (!$(elements[i]).parent().is($(elements[i-1]).parent()) && i % ListView.MAX_PER_ROW !=0)
                		    {
                			$(elements[i]).insertAfter($(elements[i-1]));
                		    }
                		}
                		//remove empty rows
                		$.each($(allRows), function (index, element) {
                		    if ($(element).children().length == 0) 
                			$(element).remove();
                		});
                	    }
                	}
                	
                    }).bind(this), 200);
                    
                }

                // FIXME: make me better
//                setTimeout(function () {
//                    window.location.reload(true);
//                }, 2000);
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
            // mediaIds: [$(e.currentTarget).data('mid')]
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

    ListView.prototype._onAddGridElement = function (err, out) {
//	console.log ("OnAddGridElement");
//	console.log (err);
	var newElement = $(out);
	var count = $(this.$fileButtons).parent("div.thumbnail.tt-grid-div-body").length;
	var elements = $(this.$fileButtons).parent("div.thumbnail.tt-grid-div-body").parent();
	if (count >= ListView.MAX_PER_PAGE)
	{
//	    console.log("Maximum elements on the page, removing last from view");
	    newElement.insertBefore(elements[0]);
	    for (var i=ListView.MAX_PER_ROW-1; i<count; i+=ListView.MAX_PER_ROW)
	    {
		$(elements[i]).insertBefore($(elements[i+1]));
	    }
	    $(elements[count-1]).remove();
	}
	else if (count % ListView.MAX_PER_ROW == 0)
	{
	    //Need to add row
	    var newRow = $("<div class='row'></div>"); 
	    var parent = $(this.$filesList.children('div.tt-myFiles-grid-div')[0]);
	    if (count == 0)
	    {
		parent.empty();
		newRow.append(newElement);
		parent.append(newRow);
	    }
	    else
	    {
    	    	newElement.insertBefore(elements[0]);
    	    	for (var i=ListView.MAX_PER_ROW-1; i<count; i+=ListView.MAX_PER_ROW)
    	    	{
    	    	    $(elements[i]).insertBefore($(elements[i+1]));
    	    	}
    	    	parent.append(newRow);	    
    	    	newRow.append($(elements[count-1]).detach())
	    }
	}
	else
	{
	    newElement.insertBefore(elements[0]);
	    for (var i=ListView.MAX_PER_ROW-1; i<count; i+=ListView.MAX_PER_ROW)
	    {
	        $(elements[i]).insertBefore($(elements[i+1]));
	    }
	}
	newElement.find('button.edit-title').on('click', function (e) {
            e.stopPropagation();
            $(this).parent().parent().parent().find('span.edit-title').editable('toggle');
        });
	newElement.find('span.edit-title').editable({
            toggle: 'manual',
            unsavedclass: null,
            pk: function () {
                return $(this).data('mid')
            },
            url: function (params) {
                return this.controller.edit(params.pk, {title: params.value});
            }.bind(this)
        });
	newElement.find('.my-files-selector-file-click').not('[disabled]').on('click', this.bind__onClickFile);
	//Reinitialize the files and filesList
	this.$filesList = this.$container.find(ListView.Binder.FILES_LIST);
        this.$fileButtons = this.$container.find(ListView.Binder.FILE_BUTTON);
        this.tblCmp.updateElements(this.$filesList);
	
    }
    
    ListView.prototype._onAddListElement = function (err, out) {
//	console.log ("OnAddListElement");
//	console.log (err);
//	console.log (out);
	var newElement = $(out);
	var count = $(this.$fileButtons).parent("tr").length;
	var elements = $(this.$fileButtons).parent("tr");
	if (count >= ListView.MAX_PER_PAGE)
	{
//	    console.log("Maximum elements on the page, removing last from view");
	    newElement.insertBefore(elements[0]);
	    elements[count-1].remove();
	}
	else if (count == 0)
	{
//	    console.log("No elements on page");
	    //Remove the text saying there are no elements.
	    $(this.$filesList).find('div.table-responsive > div').remove();
	    elements = $(this.$filesList).find('tbody > tr');
	    newElement.insertBefore(elements[0]);
	    $(elements[0]).remove();
	}
	else
	{
//	    console.log("Else");
	    newElement.insertBefore(elements[0]);
	}
	
	newElement.find('button.edit-title').on('click', function (e) {
            e.stopPropagation();
            $(this).parent().parent().parent().find('span.edit-title').editable('toggle');
        });
	newElement.find('span.edit-title').editable({
            toggle: 'manual',
            unsavedclass: null,
            pk: function () {
                return $(this).data('mid')
            },
            url: function (params) {
                return this.controller.edit(params.pk, {title: params.value});
            }.bind(this)
        });
	newElement.find('.my-files-selector-file-click').not('[disabled]').on('click', this.bind__onClickFile);
	
	this.$filesList = this.$container.find(ListView.Binder.FILES_LIST);
        this.$fileButtons = this.$container.find(ListView.Binder.FILE_BUTTON);
        this.tblCmp.updateElements(this.$filesList);
	
    }
    
    ListView.prototype._onSuccess = function (e) {
// window.location.reload(true); //TODO load to last page?
//	console.log("On success:");
	console.log(e);
	var media = e.media[e.media.length - 1].data;
//	console.log(media);
	this.controller.model.addMedia(media);
	if (this.style == ListView.Binder.STYLE_GRID)
	{
	    
	    dust.render('myFilesGridElement', {
	            media: media,
	            previewTitle: Translator.trans('filesGateway.previewLink'),
	            editTitle: Translator.trans('form.media.title'),
	            shareTitle: Translator.trans('filesGateway.shareLink'),
	            shareUrl: Routing.generate('imdc_thread_new_from_media', {mediaId: media.id}),
	            mediaIcon: Helper.getIconForMediaType(media.type),
	        }, this.bind__onAddGridElement);
	}
	else
	{
	    var size = Helper.formatSize(media.source_resource.meta_data.size);
	    var spinner = false;
	    if (size <0)
		spinner = true;
	    dust.render('myFilesListElement', {
	            media: media,
	            previewTitle: Translator.trans('filesGateway.previewLink'),
	            editTitle: Translator.trans('form.media.title'),
	            shareTitle: Translator.trans('filesGateway.shareLink'),
	            shareUrl: Routing.generate('imdc_thread_new_from_media', {mediaId: media.id}),
	            mediaIcon: Helper.getIconForMediaType(media.type),
	            timeUploaded: dust.filters.date(media.source_resource.meta_data.time_created),
	            spinner: spinner,
	            formattedSize: size
	        }, this.bind__onAddListElement);
	}
    };
    
    ListView.prototype._onError = function (e) {
        alert('Error: ' + e.error);
    };

    return ListView;
});
