define(['core/mediaChooser'], function(MediaChooser) {
    var Post = function() {

    }

    Post.TAG = "Post";

    Post.Page = {
        EDIT: 0,
        REPLY: 1
    };

    /**
     * MediaChooser options for each related page that uses MediaChooser
     * @param {number} page
     * @param {number} postId
     */
    Post.mediaChooserOptions = function(page, postId) {
        switch (page) {
            case Post.Page.EDIT:
            case Post.Page.REPLY:
                return {
                    element: $("#filesPost" + postId),
                    isPopUp: true,
                    callbacks: {
                        success: function(media, postId) {
                            //$("#PostEditForm_mediatextarea").val(media.id);
                            $(".mediatextarea-post-" + postId).val(media.id);
                        },
				successAndPost: function(media, postId) {
				    //$("#PostEditForm_mediatextarea").val(media.id);
				    $(".mediatextarea-post-" + postId).val(media.id);
				    //TODO do the post
				    $("#PostReplyToPostForm_submit").trigger("click");
				},
                        reset: function() {
                            //$("#PostEditForm_mediatextarea").val("");
                            $(".mediatextarea-post-" + postId).val("");
                        }
                    },
                    isPost: true,
                    postId: postId
                };
        }
    };

    /**
     * ui element event bindings in order of appearance
     * @param {number} page
     * @param {number} postId
     */
    Post.bindUIEvents = function(page, postId) {
        console.log("%s: %s- page=%d", Post.TAG, "bindUIEvents", page);

        switch (page) {
            case Post.Page.EDIT:
                Post._bindUIEventsEdit(postId);
                break;
            case Post.Page.REPLY:
                Post._bindUIEventsReply(postId);
                break;
        }
    };

    Post._bindUIEventsEdit = function(postId) {
        console.log("%s: %s- postId=%d", Post.TAG, "_bindUIEventsEdit", postId);

        MediaChooser.bindUIEvents(Post.mediaChooserOptions(Post.Page.EDIT, postId));

        $("#cancelEditPost" + postId).on("click", function(e) {
            e.preventDefault();

            $("#containerEditPost" + postId).html("");
            $("#containerEditPost" + postId).hide();

            $("#containerPost" + postId).show();

            if (thread.player) {
                enableTemporalComment(thread.player, false, $("#PostEditForm_startTime"), $("#PostEditForm_endTime"));
            }
        });
    };

    Post._bindUIEventsReply = function(postId) {
        console.log("%s: %s- postId=%d", Post.TAG, "_bindUIEventsReply", postId);

        MediaChooser.bindUIEvents(Post.mediaChooserOptions(Post.Page.REPLY, postId));

        $("#cancelReplyPost" + postId).on("click", function(e) {
            e.preventDefault();

            $("#replyPostContainer" + postId).remove();

            // restore the comment reply link if you click the cancel button
            $(".post-reply[data-pid=" + postId + "]").show();
        });
    };

    return Post;
});
