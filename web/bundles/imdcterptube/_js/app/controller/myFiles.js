define(
		[ 'core/mediaChooser', 'core/mediaManager' ],
		function(MediaChooser, MediaManager) {
			var MyFiles = function() {
				this.forwardButton = "<button class='cutButton'></button>";
				this.media = null;

				this.mediaChooser = new MediaChooser(MyFiles
						.mediaChooserOptions(MyFiles.Page.PREVIEW));
				this.mediaManager = new MediaManager();
				// TODO move to media chooser, as this may be a more general
				// function
				this.bind_onRecordingSuccess = this.onRecordingSuccess
						.bind(this);
				this.bind_onRecordingError = this.onRecordingError.bind(this);
				this.bind_forwardFunction = this.forwardFunction.bind(this);

				this.bind__addMediaRow = this._addMediaRow.bind(this);
				this.bind__deleteFile = this._deleteFile.bind(this);
				this.bind__updateMediaRow = this._updateMediaRow.bind(this);

				dust.compileFn($("#mediaRow").html(), "mediaRow");
			}

			MyFiles.TAG = "MyFiles";

			MyFiles.Page = {
				INDEX : 0,
				PREVIEW : 1
			};

			/**
			 * MediaChooser options for each related page that uses MediaChooser
			 * 
			 * @param {number}
			 *            page
			 */
			MyFiles.mediaChooserOptions = function(page) {
				switch (page) {
				case MyFiles.Page.INDEX:
					return {
						element : $("#preview"),
						isPopUp : true,
						callbacks : {
							success : function(media) {
								context.bind__addMediaRow(media);
							},
							reset : function() {

							}
						},
						isFileSelection : false
					};
				case MyFiles.Page.PREVIEW:
					return {
						element : $("#preview"),
						isPopUp : true,
						callbacks : {
							success : function(media) {
								console.log("Done previewing");
							},
							reset : function() {

							},
							dialogClose: function(media) {
								console.log("Terminating function called");
								console.log(media);
								context.bind__updateMediaRow(media);
							}
						},
						isFileSelection : false
					};
				}
			};

			/**
			 * ui element event bindings in order of appearance
			 * 
			 * @param {number}
			 *            page
			 */
			MyFiles.prototype.bindUIEvents = function(page) {
				console.log("%s: %s- page=%d", MyFiles.TAG, "bindUIEvents",
						page);

				switch (page) {
				case MyFiles.Page.INDEX:
					this._bindUIEventsIndex();
					break;
				}
			};

			/**
			 * @param {object}
			 *            options
			 */
			MyFiles.prototype._bindUIEventsIndex = function() {
				console.log("%s: %s", MyFiles.TAG, "_bindUIEventsIndex");

				MediaChooser.bindUIEvents(MyFiles
						.mediaChooserOptions(MyFiles.Page.INDEX));
				var instance = this;
				$(".preview-button").on("click", function(e) {
					instance.onPreviewButtonClick(e);
				});

				$(".delete-button").on("click", function(e) {
					instance.onDeleteButtonClick(e);
				});
			};

			/**
			 * @param {object}
			 *            videoElement
			 */
			// TODO move to media chooser, as this may be a more general
			// function
			MyFiles.prototype.createVideoRecorder = function(videoElement) {
				console.log("%s: %s", MyFiles.TAG, "createVideoRecorder");

				this.player = new Player(videoElement, {
					areaSelectionEnabled : false,
					updateTimeType : Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE,
					type : Player.DENSITY_BAR_TYPE_RECORDER,
					audioBar : false,
					volumeControl : false,
					recordingSuccessFunction : this.bind_onRecordingSuccess,
					recordingErrorFunction : this.bind_onRecordingError,
					recordingPostURL : Routing
							.generate('imdc_files_gateway_record'),
					forwardButtons : [ this.forwardButton ],
					forwardFunctions : [ this.bind_forwardFunction ]
				});
				this.player.createControls();

				// TODO revise
				videoElement.parents(".ui-dialog").on("dialogbeforeclose",
						(function(event, ui) {
							console.log("videoElement dialogbeforeclose");
							if (this.player != null) {
								this.player.destroyRecorder();
							}
						}).bind(this));
			};

			// TODO move to media chooser, as this may be a more general
			// function
			MyFiles.prototype.onRecordingSuccess = function(data) {
				console.log("%s: %s- mediaId=%d", MyFiles.TAG,
						"onRecordingSuccess", data.media.id);

				this.media = data.media;
				mediaChooser.setMedia(this.media);
			};

			// TODO move to media chooser, as this may be a more general
			// function
			MyFiles.prototype.onRecordingError = function(e) {
				console.log("%s: %s- e=%s", MyFiles.TAG, "onRecordingError", e);
			};

			// TODO move to media chooser, as this may be a more general
			// function
			MyFiles.prototype.forwardFunction = function() {
				console.log("%s: %s", MyFiles.TAG, "forwardFunction");

				this.player.destroyRecorder();

				// mediaChooser = this.mediaChooser;
				mediaChooser.previewMedia({
					type : MediaChooser.TYPE_RECORD_VIDEO,
					mediaUrl : Routing.generate('imdc_files_gateway_preview', {
						mediaId : this.media.id
					}),
					mediaId : this.media.id,
					recording : true
				});
			};

			MyFiles.prototype._addMediaRow = function(media) {
				console.log("%s: %s", MyFiles.TAG, "_addMediaRow");

				this.media = media;

				var data = {
					media : this.media
				};

				if (this.media.isReady == 0) {
					data.previewDisabled = true;
				}

				// TODO revise
				switch (this.media.type) {
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

				// TODO revise
				var timeUploaded = new Date(
						this.media.metaData.timeUploaded.date);
				var ampm = timeUploaded.getHours() >= 12 ? "pm" : "am";
				var hours = (timeUploaded.getHours() > 12 ? timeUploaded
						.getHours() - 12 : timeUploaded.getHours());
				hours = hours < 10 ? "0" + hours : hours;
				var time = hours
						+ ":"
						+ (timeUploaded.getMinutes() < 10 ? "0"
								+ timeUploaded.getMinutes() : timeUploaded
								.getMinutes()) + ampm;
				var timeDateString = time + " "
						+ $.datepicker.formatDate('M d', timeUploaded);

				data.dateString = timeDateString;

				if (this.media.metaData.size > 0) {
					data.mediaSize = (this.media.metaData.size / 1024 / 1024)
							.toFixed(2);
				}
				var instance = this;

				data.deleteUrl = Routing.generate('imdc_files_gateway_remove',
						{
							mediaId : this.media.id
						});
				data.previewUrl = Routing.generate(
						'imdc_files_gateway_preview', {
							mediaId : this.media.id
						});
				data.newThreadUrl = Routing.generate(
						'imdc_thread_create_new_from_media', {
							resourceid : this.media.id
						});
				data.simulRecordUrl = Routing.generate(
						'imdc_media_simultaneous_record', {
							mediaID : this.media.id
						});

				dust.render("mediaRow", data, function(err, out) {
					$("#files-table").append(out);
				});

				$(".preview-button").on("click", function(e) {
					instance.onPreviewButtonClick(e);
				});

				$(".delete-button").on("click", function(e) {
					instance.onDeleteButtonClick(e);
				});
			};

			MyFiles.prototype._updateMediaRow = function(media) {
				// At this points it updates the title and the file-size
				console.log("%s: %s", MyFiles.TAG, "_updateMediaRow");

				this.media = media;

				var data = {
					media : this.media
				};

				if (this.media.metaData.size > 0) {
					data.mediaSize = (this.media.metaData.size / 1024 / 1024)
							.toFixed(2)
							+ " MB";
				}
				data.title = media.title;
				
				var row = $('a[data-val|=' + data.media.id + ']').eq(0)
						.parent().parent().parent();
				console.log(row);
				row.children().eq(1).text(data.title);
				row.children().eq(4).text(data.mediaSize);
				var instance = this;

			};

			MyFiles.prototype._deleteFile = function(currentElement, message) {
				console.log("%s: %s", MyFiles.TAG, "_deleteFile");

				$(this.mediaManager).one(
						MediaManager.EVENT_DELETE_SUCCESS,
						function() {
							$(currentElement).parent().parent().parent()
									.remove();
						});
				$(this.mediaManager).one(MediaManager.EVENT_DELETE_ERROR,
						function(error, e) {
							if (e.status == 500) {
								alert(e.statusText);
							} else {
								alert('Error: ' + error);
							}
						});

				return this.mediaManager.deleteMedia($(currentElement).data(
						"val"), message);
				// var response = confirm(message);
				// if (!response) {
				// return false;
				// }
				// var address = $(currentElement).data("url");
				//
				// $.ajax({
				// url : address,
				// type : "POST",
				// contentType : "application/x-www-form-urlencoded",
				// data : {
				// mediaId : $(currentElement).data("val")
				// },
				// success : function(data) {
				// if (data.responseCode == 200) {
				// $(currentElement).parent().parent().remove();
				// } else if (data.responseCode == 400) { // bad request
				// alert('Error: ' + data.feedback);
				// } else {
				// alert('An unexpected error occured');
				// }
				// },
				// error : function(request) {
				// console.log(request.statusText);
				// }
				// });
			};

			MyFiles.prototype.onPreviewButtonClick = function(e) {
				e.preventDefault();
				console.log("Preview");
				if ($(e.target).hasClass("disabled")) {
					return false;
				}
				$('#preview').html('');
				mediaChooser = this.mediaChooser;
				this.mediaChooser.previewMedia({
					mediaUrl : $(e.target).data("url"),
					mediaId : $(e.target).data("val")
				});
			};

			MyFiles.prototype.onDeleteButtonClick = function(e) {
				e.preventDefault();

				this.bind__deleteFile($(e.target), $(
						"#mediaDeleteConfirmMessage").html());
			};

			return MyFiles;
		});
