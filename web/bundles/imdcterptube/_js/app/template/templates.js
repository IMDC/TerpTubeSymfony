!function(){function a(a,l){return a.write('<div class="tt-gallery-prev disabled gallery-prev"><i class="fa fa-chevron-left fa-3x"></i></div><div class="tt-gallery-item gallery-item"></div><div class="tt-gallery-next disabled gallery-next"><i class="fa fa-chevron-right fa-3x next"></i></div><div class="tt-gallery-carousel gallery-carousel"><div class="tt-gallery-left gallery-left"><i class="fa fa-chevron-left fa-2x"></i></div><div class="tt-gallery-thumbs gallery-thumbs"><div><ul></ul></div> </div><div class="tt-gallery-right gallery-right"><i class="fa fa-chevron-right fa-2x right"></i></div><div class="clear"></div></div><div class="tt-gallery-action gallery-action"><a title="Cut" data-action="4"><i class="fa fa-scissors fa-2x"></i></a><a title="Interpret" data-action="3"><i class="fa fa-video-camera fa-2x"></i></a><a data-action="2"><i class="fa fa-expand fa-2x"></i></a><a data-action="1"><i class="fa fa-times fa-2x"></i></a></div>')}return dust.register("gallery_common",a),a}();
;!function(){function e(e,i){return e.write('<div id="').reference(i.get(["identifier"],!1),i,"h").write('" class="tt-gallery-inline gallery-inner">').partial("gallery_common",i,null).write("</div>")}return dust.register("gallery_inline",e),e}();
;!function(){function e(e,r){return e.write('<div id="').reference(r.get(["identifier"],!1),r,"h").write('" class="tt-gallery-background gallery-inner"></div><div id="').reference(r.get(["identifier"],!1),r,"h").write('" class="tt-gallery-preview gallery-inner">').partial("gallery_common",r,null).write("</div>")}return dust.register("gallery_preview",e),e}();
;!function(){function t(t,i){return t.section(i.get(["media"],!1),i,{block:e},null)}function e(t,e){return t.write('<li data-mid="').reference(e.getPath(!1,["data","id"]),e,"h").write('">').exists(e.get(["can_edit"],!1),e,{block:i},null).write("<div>").exists(e.getPath(!1,["data","thumbnail_path"]),e,{block:a},null).write("</div> </li>")}function i(t,e){return t.write('<span class="gallery-thumb-remove" title="Remove" data-mid="').reference(e.getPath(!1,["data","id"]),e,"h").write('"><i class="fa fa-times-circle"></i></span>')}function a(t,e){return t.write('<img src="').helper("generateUrl",e,{},{path:e.getPath(!1,["data","thumbnail_path"])}).write('" />')}return dust.register("gallery_thumbnail",t),t}();
;!function(){function e(e,r){return e.helper("select",r,{block:t},{key:r.getPath(!1,["media","type"])})}function t(e,t){return e.helper("eq",t,{block:r},{value:1}).helper("eq",t,{block:s},{value:2}).helper("eq",t,{block:d},{value:0}).helper("eq",t,{block:w},{value:9})}function r(e,t){return e.exists(t.getPath(!1,["media","is_interpretation"]),t,{block:i},null).write('<video class="tt-media-video"').exists(t.get(["enable_controls"],!1),t,{block:o},null).exists(t.getPath(!1,["media","thumbnail_path"]),t,{block:c},null).write('preload="none">').section(t.getPath(!1,["media","resources"]),t,{block:u},null).write("</video>")}function i(e,t){return e.write('<video class="tt-media-pip-video"').exists(t.get(["enable_controls"],!1),t,{block:n},null).exists(t.getPath(!1,["media","source","thumbnail_path"]),t,{block:l},null).write('preload="none">').section(t.getPath(!1,["media","source","resources"]),t,{block:a},null).write("</video>")}function n(e,t){return e.write(" controls ")}function l(e,t){return e.write(' poster="').helper("generateUrl",t,{},{path:t.getPath(!1,["media","source","thumbnail_path"])}).write('" ')}function a(e,t){return e.write('<source src="').helper("generateUrl",t,{},{path:t.get(["web_path"],!1)}).write('" />')}function o(e,t){return e.write(" controls ")}function c(e,t){return e.write(' poster="').helper("generateUrl",t,{},{path:t.getPath(!1,["media","thumbnail_path"])}).write('" ')}function u(e,t){return e.write('<source src="').helper("generateUrl",t,{},{path:t.get(["web_path"],!1)}).write('" />')}function s(e,t){return e.write('<audio class="tt-media-audio"').exists(t.get(["enable_controls"],!1),t,{block:h},null).write(">").section(t.getPath(!1,["media","resources"]),t,{block:p},null).write("</audio>")}function h(e,t){return e.write(" controls")}function p(e,t){return e.write('<source src="').helper("generateUrl",t,{},{path:t.get(["web_path"],!1)}).write('" />')}function d(e,t){return e.write('<img class="img-responsive center-block tt-media-img" src="').helper("generateUrl",t,{},{path:t.getPath(!1,["media","source_resource","web_path"])}).write('" title="').reference(t.get(["title"],!1),t,"h").write('" alt="').reference(t.get(["title"],!1),t,"h").write('" />')}function w(e,t){return e.write('<div class="row"><div class="col-md-3 thumbnail text-center"><a href="').helper("generateUrl",t,{},{path:t.getPath(!1,["media","source_resource","web_path"])}).write('"><p><i class="fa fa-file fa-5x"></i></p><div>').reference(t.get(["title"],!1),t,"h").write("</div></a></div></div>")}return dust.register("media_element",e),e}();
;!function(){function e(e,l){return e.write('<div class="col-md-3"><div class="thumbnail tt-grid-div-body"><div class="tt-grid-div-item"><input type="checkbox" class="table-component-item" data-mid="').reference(l.getPath(!1,["media","id"]),l,"h").write('" /></div><div class="tt-grid-div-thumbnail my-files-selector-file" data-mid="').reference(l.getPath(!1,["media","id"]),l,"h").write('" disabled><div><span class="fa-stack fa-lg"><i class="fa fa-file-o fa-stack-2x"></i><i class="fa fa-play-circle fa-stack-1x"></i></span></div></div><div class="tt-grid-div-primary"><span class="edit-title my-files-selector-file" title="').reference(l.get(["previewTitle"],!1),l,"h").write('" data-mid="').reference(l.getPath(!1,["media","id"]),l,"h").write('" data-type="text" data-title="').reference(l.get(["editTitle"],!1),l,"h").write('" disabled><i class="fa ').reference(l.getPath(!1,["mediaIcon","icon"]),l,"h").write(' fa-2x" title="').reference(l.getPath(!1,["mediaIcon","text"]),l,"h").write('"></i>').reference(l.getPath(!1,["media","title"]),l,"h").write('</span><span><button class="btn btn-default edit-title"><i class="fa fa-pencil"></i></button><div class="btn-group"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-download"></i> <span class="caret"></span></button><ul class="dropdown-menu" role="menu">').section(l.getPath(!1,["media","resources"]),l,{block:t},null).helper("if",l,{block:i},{cond:a}).exists(l.getPath(!1,["media","source_resource"]),l,{block:r},null).write('</ul></div><a class="btn btn-default" target="_blank" href="').reference(l.get(["shareUrl"],!1),l,"h").write('" title="').reference(l.get(["shareTitle"],!1),l,"h").write('"><i class="fa fa-share-square-o"></i></a></span></div></div></div>')}function t(e,t){return e.write('<li><a href="').helper("generateUrl",t,{},{path:t.get(["web_path"],!1)}).write(' download="').reference(t.getPath(!1,["media","title"]),t,"h").write(".").reference(t.get(["path"],!1),t,"h").write('">').reference(t.get(["path"],!1),t,"h").write("</a></li>\n")}function i(e,t){return e.write('<li class="divider"></li>')}function a(e,t){return e.write("'").reference(t.getPath(!1,["media","resources"]),t,"h").write("'.length > 0 && '").reference(t.getPath(!1,["media","source_resource"]),t,"h").write("'.length")}function r(e,t){return e.write('<li><a href="').helper("generateUrl",t,{},{path:t.getPath(!1,["media","source_resource","web_path"])}).write('" download="').reference(t.getPath(!1,["media","title"]),t,"h").write(".").reference(t.getPath(!1,["media","source_resource","path"]),t,"h").write('">Original</a></li>')}return dust.register("myFilesGridElement",e),e}();
;!function(){function e(e,s){return e.write('<tr><td class="tt-list-table-col-item"><input type="checkbox" class="table-component-item" data-mid="').reference(s.getPath(!1,["media","id"]),s,"h").write('" /></td><td class="tt-list-table-col-thumbnail my-files-selector-file" data-mid="').reference(s.getPath(!1,["media","id"]),s,"h").write('" disabled ><div><span class="fa-stack fa-lg"><i class="fa fa-file-o fa-stack-2x"></i><i class="fa fa-play-circle fa-stack-1x"></i></span></div></td><td class="tt-list-table-col-primary tt-myFiles-nameColumn"><span class="edit-title my-files-selector-file" title="').reference(s.get(["previewTitle"],!1),s,"h").write('" data-mid="').reference(s.getPath(!1,["media","id"]),s,"h").write('" data-type="text" data-title="').reference(s.get(["editTitle"],!1),s,"h").write('" disabled >').reference(s.getPath(!1,["media","title"]),s,"h").write('</span><span><button class="btn btn-default edit-title"><i class="fa fa-pencil"></i></button><div class="btn-group"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-download"></i> <span class="caret"></span></button><ul class="dropdown-menu" role="menu">').section(s.getPath(!1,["media","resources"]),s,{block:t},null).helper("if",s,{block:a},{cond:r}).exists(s.getPath(!1,["media","source_resource"]),s,{block:i},null).write('</ul></div><a class="btn btn-default" target="_blank" href="').reference(s.get(["shareUrl"],!1),s,"h").write('" title="').reference(s.get(["shareTitle"],!1),s,"h").write('"><i class="fa fa-share-square-o"></i></a></span></td><td>').reference(s.get(["timeUploaded"],!1),s,"h").write('</td><td><i class="fa ').reference(s.getPath(!1,["mediaIcon","icon"]),s,"h").write(' fa-2x" title="').reference(s.getPath(!1,["mediaIcon","text"]),s,"h").write('"></i></td><td>').exists(s.get(["spinner"],!1),s,{"else":l,block:n},null).write("</td></tr>")}function t(e,t){return e.write('<li><a href="').helper("generateUrl",t,{},{path:t.get(["web_path"],!1)}).write(' download="').reference(t.getPath(!1,["media","title"]),t,"h").write(".").reference(t.get(["path"],!1),t,"h").write('">').reference(t.get(["path"],!1),t,"h").write("</a></li>\n")}function a(e,t){return e.write('<li class="divider"></li>')}function r(e,t){return e.write("'").reference(t.getPath(!1,["media","resources"]),t,"h").write("'.length > 0 && '").reference(t.getPath(!1,["media","source_resource"]),t,"h").write("'.length")}function i(e,t){return e.write('<li><a href="').helper("generateUrl",t,{},{path:t.getPath(!1,["media","source_resource","web_path"])}).write('" download="').reference(t.getPath(!1,["media","title"]),t,"h").write(".").reference(t.getPath(!1,["media","source_resource","path"]),t,"h").write('">Original</a></li>')}function l(e,t){return e.reference(t.get(["formattedSize"],!1),t,"h")}function n(e,t){return e.write('<i class="fa fa-spinner fa-spin fa-large"> </i>')}return dust.register("myFilesListElement",e),e}();
;!function(){function a(a,s){return a.write('<div class="modal fade my-files-selector-modal"><div class="modal-dialog" style="width: 80%;"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4 class="modal-title">Select from My Files</h4></div><div class="modal-body"><div class="text-center"><i class="fa fa-spinner fa-4x fa-spin"></i></div></div><div class="modal-footer"><a class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</a><a class="btn btn-primary my-files-selector-select-selected" disabled="disabled" data-loading-text="<i class=&quot;fa fa-spinner fa-spin&quot;></i> Downloading data..."><i class="fa fa-check"></i> Select</a></div></div></div></div>')}return dust.register("myFilesSelector",a),a}();
;!function(){function t(t,r){return r=r.shiftBlocks(i),t.partial("post_view",r,null)}function r(t,r){return r=r.shiftBlocks(i),t.write('<div class="row"><div class="col-md-12">').reference(r.get(["form"],!1),r,"h",["s"]).write("</div></div>")}function s(t,r){return r=r.shiftBlocks(i),t}function o(t,r){return r=r.shiftBlocks(i),t}dust.register("post_edit",t);var i={post_content:r,post_tools:s,post_reply:o};return t}();
;!function(){function t(t,s){return t.write('<div class="row post-container').exists(s.get(["is_post_reply"],!1),s,{block:e},null).write('" data-pid="').reference(s.get(["id"],!1),s,"h").write('"><div class="').exists(s.get(["is_post_reply"],!1),s,{"else":r,block:i},null).write('">').exists(s.get(["is_post_reply"],!1),s,{block:n},null).reference(s.get(["form"],!1),s,"h",["s"]).exists(s.get(["is_post_reply"],!1),s,{block:o},null).write("</div></div>")}function e(t,e){return t.write(' tt-post-container tt-post-reply-container" style="margin-bottom: 15px;')}function r(t,e){return t.write("col-md-12")}function i(t,e){return t.write("col-md-offset-1 col-md-11")}function n(t,e){return t.write('<div class="row tt-post-container-inner tt-post-reply-container-inner"><div class="col-md-12">')}function o(t,e){return t.write("</div></div>")}return dust.register("post_new",t),t}();
;!function(){function t(t,a){return t.write('<div class="row post-container tt-post-container').exists(a.get(["is_post_reply"],!1),a,{block:e},null).write('" style="margin-bottom: 15px;" data-pid="').reference(a.get(["id"],!1),a,"h").write('"').exists(a.get(["is_post_reply"],!1),a,{block:i},null).write('><div class="').exists(a.get(["is_post_reply"],!1),a,{"else":s,block:l},null).write('"><div class="row tt-post-container-inner').exists(a.get(["is_post_reply"],!1),a,{block:o},null).write('"><!-- user info --><div class="col-md-2 text-center"><div class="row"><div class="col-md-12">').partial("user_avatar",a,{user:a.get(["author"],!1)}).partial("user_username",a,{user:a.get(["author"],!1)}).write('</div></div><div class="row"><div class="col-md-12">').partial("user_connect_tools",a,{user:a.get(["author"],!1)}).write('</div></div></div><!-- post content --><div class="col-md-9 tt-post-content-container">').block(a.getBlock("post_content"),a,{block:r},null).write('</div><!-- post tools --><div class="col-md-1 pull-right">').block(a.getBlock("post_tools"),a,{block:c},null).write("</div></div>").notexists(a.get(["is_post_reply"],!1),a,{block:u},null).write("</div></div>")}function e(t,e){return t.write(" tt-post-reply-container")}function i(t,e){return t.write(' data-ppid="').reference(e.get(["parent_post_id"],!1),e,"h").write('"')}function s(t,e){return t.write("col-md-12")}function l(t,e){return t.write("col-md-offset-1 col-md-11")}function o(t,e){return t.write(" tt-post-reply-container-inner")}function r(t,e){return t.write('<div class="row"><div class="col-md-12"><div class="text-right tt-date">').reference(e.get(["created"],!1),e,"h").exists(e.get(["edited_by"],!1),e,{block:a},null).write('</div></div><div class="col-md-12">').exists(e.get(["is_temporal"],!1),e,{block:n},null).helper("gt",e,{block:d},{key:e.getPath(!1,["ordered_media","length"]),value:0}).write("<div><p>").reference(e.get(["content"],!1),e,"h",["nl2br","s"]).write("</p></div></div></div>")}function a(t,e){return t.write("(edited ").reference(e.get(["edited_at"],!1),e,"h").write(" by ").reference(e.getPath(!1,["edited_by","username"]),e,"h").write(")")}function n(t,e){return t.write('<div class="tt-post-timeline-container"><div class="tt-post-timeline"><div class="tt-post-timeline-keypoint post-timeline-keypoint"></div></div></div>')}function d(t,e){return t.write('<div class="row"><div class="col-md-12"><div class="post-gallery" style="margin-bottom: 10px; height: 400px; width: 100%;"></div></div></div>')}function c(t,e){return t.write('<ul class="list-unstyled">').exists(e.get(["is_temporal"],!1),e,{block:p},null).write('<li><a class="post-edit" href="#" title="Edit this post"><i class="fa fa-pencil"></i></a></li><li><a href="#" title="Delete this post" data-toggle="modal" data-target=".post-delete-modal[data-pid=').reference(e.get(["id"],!1),e,"h").write(']"><i class="fa fa-trash-o"></i></a></li></ul><div class="modal fade post-delete-modal" data-pid="').reference(e.get(["id"],!1),e,"h").write('"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4 class="modal-title">Delete this post</h4></div><div class="modal-body text-center"><p>This post will be permanently deleted.<br />This action cannot be undone.<br />Are you sure?</p></div><div class="modal-footer"><a class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</a><a class="btn btn-danger post-delete" data-loading-text="<i class=&quot;fa fa-spinner fa-spin&quot;></i> Deleting..."><i class="fa fa-trash-o"></i> Delete</a></div></div></div></div>')}function p(t,e){return t.write('<li><a class="post-timeline-keypoint" href="#" title="Jump to selected timeline region"><i class="fa fa-clock-o"></i></a></li>')}function u(t,e){return t.write("<!-- post reply -->").block(e.getBlock("post_reply"),e,{block:v},null)}function v(t,e){return t.write('<div class="row"><div class="col-md-1 col-md-offset-11"><p><a class="post-new" href="#" title="Reply to this post"><i class="fa fa-reply fa-rotate-180 fa-lg"></i></a></p></div></div>')}return dust.register("post_view",t),t}();
;!function(){function e(e,a){return e.write('<div class="modal fade recorder-modal"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4 class="modal-title">Recorder</h4></div><div class="modal-body"><div class="recorder-container-record"><ul class="nav nav-tabs" role="tablist"><li class="active"><a href=".recorder-normal" role="tab" data-toggle="tab">Normal</a></li><li><a href=".recorder-interp" role="tab" data-toggle="tab">Interpretation</a></li></ul><div class="tab-content" style="padding-top: 15px;"><div class="tab-pane active recorder-normal"><div class="tt-media-preview-title"><input class="recorder-normal-title" style="display: none;" type="text" value="" /></div><video class="recorder-normal-video" width="100%" preload="auto" muted="muted"></video></div><div class="tab-pane recorder-interp"><div style="border: 2px dashed #ccc; line-height: 200px; height: 200px; text-align: center; display: none;"><button class="btn btn-primary btn-lg recorder-interp-select">Select a video to interpret</button></div><div class="row"><div class="col-md-6"><video class="recorder-interp-video-p" style="display: none;" width="100%" preload="auto" muted="muted"></video></div><div class="col-md-6"><div class="tt-media-preview-title"><input class="recorder-interp-title" style="display: none;" type="text" value="" /></div><video class="recorder-interp-video-r" style="display: none;" width="100%" preload="auto" muted="muted"></video></div></div></div></div><div class="recorder-controls"></div></div><div class="recorder-container-upload" style="display: none;"><label>Uploading...</label><div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">0%</div></div></div></div></div></div></div>')}return dust.register("recorder",e),e}();
;!function(){function e(e,t){return e.section(t.get(["resources"],!1),t,{block:r},null)}function r(e,r){return e.write('<source src="').helper("generateUrl",r,{},{path:r.get(["web_path"],!1)}).write('" />')}return dust.register("recorder_source",e),e}();
;!function(){function t(t,r){return t.write('<!-- thread replies --><div class="row"><div class="col-md-12">').section(r.get(["posts"],!1),r,{"else":e,block:i},null).write("</div></div>")}function e(t,e){return t.write('<div class="lead text-center" style="padding-top: 30px;">No replies yet!</div>')}function i(t,e){return t.notexists(e.get(["is_post_reply"],!1),e,{block:r},null)}function r(t,e){return t.write("<!-- post -->").partial("post_view",e,{post:e.getPath(!0,[])}).write("<!-- post replies -->").section(e.get(["replies"],!1),e,{block:s},null)}function s(t,e){return t.partial("post_view",e,{post:e.getPath(!0,[])})}return dust.register("thread_view_posts",t),t}();
;!function(){function e(e,t){return e.section(t.get(["user"],!1),t,{block:r},null)}function r(e,r){return e.write('<a href="').helper("generateRouteUrl",r,{},{name:"imdc_profile_user",str_params:"userName:username"}).write('"><img class="img-responsive center-block tt-avatar" src="').exists(r.getPath(!1,["profile","avatar"]),r,{"else":t,block:a},null).write('" /></a>')}function t(e,r){return e.helper("generateUrl",r,{},{path:"bundles/imdcterptube/img/no_avatar.jpg"})}function a(e,r){return e.helper("generateUrl",r,{},{path:r.getPath(!1,["profile","avatar","source_resource","web_path"])})}return dust.register("user_avatar",e),e}();
;!function(){function e(e,t){return e.section(t.get(["user"],!1),t,{block:r},null)}function r(e,r){return e.write('<ul class="list-unstyled list-inline"><li>').helper("isLoggedInUser",r,{"else":t,block:i},{id:r.get(["id"],!1)}).write("</li>&nbsp; <!-- match spaces that are still present between tags when rendering from twig --><li>").helper("isLoggedInUser",r,{"else":n,block:l},{id:r.get(["id"],!1)}).write("</li>").helper("isNotLoggedInUser",r,{block:o},{id:r.get(["id"],!1)}).write("</ul>")}function t(e,r){return e.write('<a href="').helper("generateRouteUrl",r,{},{name:"imdc_message_new",str_params:"userid:username"}).write('" title="Send a message to ').reference(r.get(["username"],!1),r,"h").write('"><i class="fa fa-envelope-o"></i></a>')}function i(e,r){return e.write('<i class="fa fa-envelope-o" style="color: lightgray;"></i>')}function n(e,r){return e.helper("isInUserOnList",r,{"else":s,block:a},{list:"Friends",username:r.get(["username"],!1)})}function s(e,r){return e.write('<a href="').helper("generateRouteUrl",r,{},{name:"imdc_member_friend_add",str_params:"userid:id"}).write('" title="Click to add ').reference(r.get(["username"],!1),r,"h").write(' to your friends list"><i class="fa fa-plus"></i></a>')}function a(e,r){return e.write('<a href="').helper("generateRouteUrl",r,{},{name:"imdc_member_friend_remove",str_params:"userid:id"}).write('" title="Remove ').reference(r.get(["username"],!1),r,"h").write(' from your friends list"><i class="fa fa-minus"></i></a>')}function l(e,r){return e.write('<i class="fa fa-user" style="color: lightgray;" title="This is you"></i>')}function o(e,r){return e.write("&nbsp;<li>").helper("isInUserOnList",r,{"else":u,block:m},{list:"Mentor",username:r.get(["username"],!1)}).write("</li>&nbsp;<li>").helper("isInUserOnList",r,{"else":d,block:w},{list:"Mentee",username:r.get(["username"],!1)}).write("</li>")}function u(e,r){return e.helper("isInUserOnList",r,{"else":f,block:c},{list:"InvitedMentor",username:r.get(["username"],!1)})}function f(e,r){return e.write('<a href="').helper("generateRouteUrl",r,{},{name:"imdc_invitation_mentor",str_params:"id:id"}).write('" title="Invite user to be your mentor"><i class="fa fa-inbox"></i></a>')}function c(e,r){return e.write('<i class="fa fa-inbox" title="Invitation for mentorship already sent"></i>')}function m(e,r){return e.write('<i class="fa fa-star" title="This user is your mentor"></i>')}function d(e,r){return e.helper("isInUserOnList",r,{"else":g,block:h},{list:"InvitedMentee",username:r.get(["username"],!1)})}function g(e,r){return e.write('<a href="').helper("generateRouteUrl",r,{},{name:"imdc_invitation_mentee",str_params:"id:id"}).write('" title="Invite user to be your mentee"><i class="fa fa-star-o"></i></a>')}function h(e,r){return e.write('<i class="fa fa-star-o" title="Invitation for menteeship already sent"></i>')}function w(e,r){return e.write('<i class="fa fa-star" title="This user is your mentee"></i>')}return dust.register("user_connect_tools",e),e}();
;!function(){function e(e,t){return e.section(t.get(["user"],!1),t,{block:r},null)}function r(e,r){return e.write('<a href="').helper("generateRouteUrl",r,{},{name:"imdc_profile_user",str_params:"userName:username"}).write('" title="').reference(r.get(["username"],!1),r,"h").write('">').reference(r.get(["username"],!1),r,"h").write("</a>")}return dust.register("user_username",e),e}();