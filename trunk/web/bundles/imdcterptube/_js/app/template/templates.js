!function(){function a(a){return a.write('<div class="tt-gallery-prev disabled gallery-prev"><i class="fa fa-chevron-left fa-3x"></i></div><div class="tt-gallery-item gallery-item"><div><i class="fa fa-spinner fa-4x fa-spin"></i></div> </div><div class="tt-gallery-next disabled gallery-next"><i class="fa fa-chevron-right fa-3x next"></i></div><div class="tt-gallery-carousel gallery-carousel"><div class="tt-gallery-left gallery-left"><i class="fa fa-chevron-left fa-2x"></i></div><div class="tt-gallery-thumbs gallery-thumbs"><div><ul></ul></div> </div><div class="tt-gallery-right gallery-right"><i class="fa fa-chevron-right fa-2x right"></i></div><div class="clear"></div></div><div class="tt-gallery-action gallery-action"><a title="Cut" data-action="4"><i class="fa fa-scissors fa-2x"></i></a><a title="Interpret" data-action="3"><i class="fa fa-video-camera fa-2x"></i></a><a data-action="2"><i class="fa fa-expand fa-2x"></i></a><a data-action="1"><i class="fa fa-times fa-2x"></i></a></div>')}return dust.register("gallery_common",a),a}();
;!function(){function l(l,r){return l.write('<div class="tt-gallery-inline gallery-normal">').partial("gallery_common",r,null).write("</div>")}return dust.register("gallery_inline",l),l}();
;!function(){function e(e,r){return e.write('<div class="tt-gallery-background gallery-preview"></div><div class="tt-gallery-preview gallery-preview">').partial("gallery_common",r,null).write("</div>")}return dust.register("gallery_preview",e),e}();
;!function(){function t(t,i){return t.section(i.get(["media"],!1),i,{block:e},null)}function e(t,e){return t.write('<li data-mid="').reference(e.getPath(!1,["data","id"]),e,"h").write('"><div>').exists(e.getPath(!1,["data","thumbnail_path"]),e,{block:i},null).write("</div> </li>")}function i(t,e){return t.write('<img src="').helper("generateUrl",e,{},{path:e.getPath(!1,["data","thumbnail_path"])}).write('" />')}return dust.register("gallery_thumbnail",t),t}();
;!function(){function e(e,r){return e.helper("select",r,{block:t},{key:r.getPath(!1,["media","type"])})}function t(e,t){return e.helper("eq",t,{block:r},{value:1}).helper("eq",t,{block:c},{value:2}).helper("eq",t,{block:s},{value:0}).helper("eq",t,{block:h},{value:9})}function r(e,t){return e.exists(t.getPath(!1,["media","is_interpretation"]),t,{block:i},null).write('<video class="tt-media-video"').exists(t.get(["enableControls"],!1),t,{block:n},null).exists(t.getPath(!1,["media","thumbnail_path"]),t,{block:o},null).write('preload="none"><source src="').helper("generateUrl",t,{},{path:t.getPath(!1,["media","resource","web_path"])}).write('" /></video>')}function i(e,t){return e.write('<video class="tt-media-pip-video"').exists(t.get(["enableControls"],!1),t,{block:a},null).exists(t.getPath(!1,["media","source","thumbnail_path"]),t,{block:l},null).write('preload="none"><source src="').helper("generateUrl",t,{},{path:t.getPath(!1,["media","source","resource","web_path"])}).write('" /></video>')}function a(e){return e.write(" controls ")}function l(e,t){return e.write(' poster="').helper("generateUrl",t,{},{path:t.getPath(!1,["media","source","thumbnail_path"])}).write('" ')}function n(e){return e.write(" controls ")}function o(e,t){return e.write(' poster="').helper("generateUrl",t,{},{path:t.getPath(!1,["media","thumbnail_path"])}).write('" ')}function c(e,t){return e.write('<audio class="tt-media-audio"').exists(t.get(["enableControls"],!1),t,{block:u},null).write('><source src="').helper("generateUrl",t,{},{path:t.getPath(!1,["media","resource","web_path"])}).write('" /></audio>')}function u(e){return e.write(" controls")}function s(e,t){return e.write('<img class="img-responsive center-block tt-media-img" src="').helper("generateUrl",t,{},{path:t.getPath(!1,["media","resource","web_path"])}).write('" title="').reference(t.get(["title"],!1),t,"h").write('" alt="').reference(t.get(["title"],!1),t,"h").write('" />')}function h(e,t){return e.write('<div class="row"><div class="col-md-3 thumbnail text-center"><a href="').helper("generateUrl",t,{},{path:t.getPath(!1,["media","resource","web_path"])}).write('"><p><i class="fa fa-file fa-5x"></i></p><div>').reference(t.get(["title"],!1),t,"h").write("</div></a></div></div>")}return dust.register("media_element",e),e}();
;!function(){function a(a){return a.write('<div class="modal fade my-files-selector-modal"><div class="modal-dialog" style="width: 80%;"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4 class="modal-title">Select from My Files</h4></div><div class="modal-body"><div class="text-center"><i class="fa fa-spinner fa-4x fa-spin"></i></div></div><div class="modal-footer"><a class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</a><a class="btn btn-primary my-files-selector-select-selected" disabled="disabled" data-loading-text="<i class=&quot;fa fa-spinner fa-spin&quot;></i> Downloading data..."><i class="fa fa-check"></i> Select</a></div></div></div></div>')}return dust.register("myFilesSelector",a),a}();
;!function(){function e(e){return e.write('<div class="modal fade recorder-modal"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4 class="modal-title">Recorder</h4></div><div class="modal-body"><div class="recorder-container-record"><ul class="nav nav-tabs" role="tablist"><li class="active"><a href=".recorder-normal" role="tab" data-toggle="tab">Normal</a></li><li><a href=".recorder-interp" role="tab" data-toggle="tab">Interpretation</a></li></ul><div class="tab-content" style="padding-top: 15px;"><div class="tab-pane active recorder-normal"><div class="tt-media-preview-title"><input class="recorder-normal-title" style="display: none;" type="text" value="" /></div><video class="recorder-normal-video" width="100%" preload="auto" muted="muted"><source src="" /></video></div><div class="tab-pane recorder-interp"><div style="border: 2px dashed #ccc; line-height: 200px; height: 200px; text-align: center; display: none;"><button class="btn btn-primary btn-lg recorder-interp-select">Select a video to interpret</button></div><div class="row"><div class="col-md-6"><video class="recorder-interp-video-p" style="display: none;" width="100%" preload="auto" muted="muted"><source src="" /></video></div><div class="col-md-6"><div class="tt-media-preview-title"><input class="recorder-interp-title" style="display: none;" type="text" value="" /></div><video class="recorder-interp-video-r" style="display: none;" width="100%" preload="auto" muted="muted"><source src="" /></video></div></div></div></div><div class="recorder-controls"></div></div><div class="recorder-container-upload" style="display: none;"><label>Uploading...</label><div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">0%</div></div></div></div></div></div></div>')}return dust.register("recorder",e),e}();