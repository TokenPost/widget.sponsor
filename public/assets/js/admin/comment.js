require(['base'], function () {
    require(['popup'], function (popup) {
        $(document).ready(function () {
        });

        /*
         util.preload([
         'http://domain/assets/images/check1_on.gif',
         'http://domain.assets/images/radio_on.gif'
         ]);
         */

        /**
         * Start of Comment
         **/
        $('#formCommentReg').on('submit', function () {
            var target = $(this);

            if(this.comment.value == ''){
                alert(translate_1);
                return false;
            }

            $.ajax({
                url: '/communication/ajax/addComment',
                type: 'post',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.error != 0) {
                        alert(response.message);
                        return false;
                    }


                    if(response['rewardPopupDisplay'] != undefined && response['rewardPopupDisplay'] == 'Y'){
                        $.rewardPopupDisplay();
                    }

                    $.factoryComment('form', target, response);
                    target.parents('.commentRegArea').find('.commentFileUpload').show();
                    target.parents('.commentRegArea').find('.btnCommentImageUpload').show();

                    $('.displayCommentCountArea').text(parseInt($('.displayCommentCountArea').text()) + 1);

                    return false;
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    if (xhr.status > 0) {
                        alert(translate_2);
                        return false;
                    }
                }
            });
            return false;
        });

        /**
         * Comment delete
         */
        $('#commentListWrap').on('click', '.btnCommentDelete', function () {
            var idx = $(this).attr('idx');
            var target = $(this);

            // conform delete check
            if(confirm(translate_3) !== true) return false;

            $.ajax({
                url: '/communication/ajax/deleteComment',
                type: 'post',
                data: {
                    'userId' : userId,
                    'commentId' : idx
                },
                dataType: 'json',
                success: function (response) {
                    if (response.error != 0) {
                        alert(response.message);
                        return false;
                    }
                    // Display removed
                    target.parents('.commentItem').find('.commentVoteUp').attr('idx', '0');
                    target.parents('.commentItem').find('.commentVoteUp').removeClass('commentVoteUp');
                    target.parents('.commentItem').find('.commentVoteDown').attr('idx', '0');
                    target.parents('.commentItem').find('.commentVoteDown').removeClass('commentVoteDown');

                    // Deleted comment by user
                    target.parents('.commentItem').find('.commentContent').html('<p>' + translate_12 + '</p>');
                    //target.parents('.commentItem').remove();

                    // 버튼 삭제.
                    target.parents('.commentItem').find('.commentUserArea').find('.option').remove();
                    target.parents('.commentItem').find('.commentUserArea').find('.optionBtnBox').remove();


                    //$('.displayCommentCountArea').text(parseInt($('.displayCommentCountArea').text()) - 1);

                    return false;
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    if (xhr.status > 0) {
                        alert(translate_2);
                        return false;
                    }
                }
            });
            return false;
        });


        /**
         * Comment modify cancel
         */
        $('#commentListWrap').on('click', '.btnCommentModifyCancel', function () {
            var parentsObj = $(this).parents('.commentItem');
            parentsObj.find('.commentContent').show();
            parentsObj.find('.commentContentModify').hide();
            parentsObj.find('.status').show();
            parentsObj.find('.option').show();
        });

        /**
         * Comment modify form open
         */
        $('#commentListWrap').on('click', '.btnCommentModify', function () {
            // $(this).parents('.commentItem').find('.commentListInfoRight').hide();


            var parentsObj = $(this).closest('.commentItem');

            parentsObj.find('.commentContent').hide();
            parentsObj.find('.commentContentModify').show();
            parentsObj.find('.status').hide();
            parentsObj.find('.option').hide();

            if(isMobile == 'Y'){
                parentsObj.find('.optionBtnBox ').hide();
            }


            return false;
            /*

             $(this).parents('.commentItem').find('.commentContentModify').html($('#commentWrap .writingArea .addComment').clone().html());
             $(this).parents('.commentItem').find('.commentContent').hide();
             $(this).parents('.commentItem').find('.commentContentModify').show();
             */

            var text = '';
            $(this).parents('.commentItem').find('.commentContentModify').find('textarea').val($(this).parents('.commentItem').find('.commentContent').text().trim());


            if($(this).parents('.commentItem').find('.commentContent').find('img').length >= 1) {

                text += '<div class="box commentUploadImagePreviewArea">';
                text += '   <img src="' + $(this).parents('.commentItem').find('.commentContent').find('img').attr('src') + '">';
                text += '   <input type="hidden" name="imageId" value="' + $(this).parents('.commentItem').find('.commentContent').find('img').attr('idx') + '" class="uploadImagePreviewAreaImageId" />';
                text += '   <div class="cancel cursor ">';
                text += '       <img src="' + staticUrl + '/assets/images/admin/common/icon/delete.svg" alt="" class="btnRemoveUploadImagePreviewAreaImage">';
                text += '   </div>';
                text += '</div>';

                text += '<div class="commentFileUpload" style="display:none;">';
                text += '   <input type="file" name="hiddenUpload" class="commentFileHidden hide" >';
                text += '   <img src="' + staticUrl + '/assets/images/admin/common/icon/upload.svg" alt="" class="btnCommentImageUpload">';
                text += '</div>';

                $(this).parents('.commentItem').find('.commentContentModify').find('.fileBox').find('.fileContent').prepend(text);

            } else {

                $(this).parents('.commentItem').find('.commentUploadImagePreviewArea').html('');
                $(this).parents('.commentItem').find('.commentFileUpload').show();

                if($(this).parents('.commentItem').find('.commentContentModify').find('.commentModifyFile').find('.commentFileUpload').length === 0){
                    text += '<div class="commentFileUpload">';
                    text += '<input type="file" name="hiddenUpload" class="commentFileHidden hide" >';
                    text += '<img src="' + staticUrl + '/assets/images/admin/common/icon/upload.svg" alt="" class="btnCommentImageUpload">';
                    text += '</div>';
                    // $(this).parents('.commentItem').find('.commentContentModify').find('.fileBox').find('.commentFileUpload').remove();
                    $(this).parents('.commentItem').find('.commentContentModify').find('.fileBox').find('.fileContent').append(text);
                }
            }

            //$(this).parents('.commentItem').find('.commentContent').text().trim()
            //$(this).parents('.commentItem').find('.commentContentModify').find('.commentModifyFile').append();
            //$(this).parents('.commentItem').find('.commentContentModify').
            $(this).parents('.commentItem').find('.commentContent').hide();
            $(this).parents('.commentItem').find('.commentContentModify').show();
            $(this).parents('.commentItem').find('.commentContentModify').find('.btnCommentModifySubmit').attr('idx', $(this).attr('idx'))




        });

        /**
         * Comment modify submit
         */

        $('#commentListWrap').on('click', '.btnCommentModifySubmit', function () {
            var imageId = 0;
            var target = $(this);
            var commentId = $(this).attr('idx');
            if($(this).parents('.commentContentModify').find('.uploadImagePreviewAreaImageId').length == 1){
                imageId = $(this).parents('.commentContentModify').find('.uploadImagePreviewAreaImageId').val();
            }

            $.ajax({
                url: '/communication/ajax/modifyComment',
                type: 'post',
                data: {
                    'imageId' : imageId,
                    'userId' : userId,
                    'commentId' : commentId,
                    'comment' : $(this).parents('.commentContentModify ').find('textarea').val()
                },
                dataType: 'json',
                success: function (response) {
                    if (response.error != 0) {
                        alert(response.message);
                        return false;
                    } else {
                        // $.factoryComment('comment', target, response);
                        target.parents('.commentItem').find('.commentContent').find('p').html(response.comment);
                        var text = '';
                        if (response.image != '') {
                            text += '<img src="' + response.image + '">';
                        }

                        target.parents('.commentItem').find('.commentContent').find('p').append(text);
                        target.parents('.commentItem').find('.commentContent').show();
                        target.parents('.commentItem').find('.commentContentModify').hide();
                        target.parents('.commentItem').find('.status').show();
                        target.parents('.commentItem').find('.option').show();
                    }
                    return false;
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    if (xhr.status > 0) {
                        alert(translate_2);
                        return false;
                    }
                }
            });

        });



        $(".btnCommentBlock").on("click", function () {

            var target = $(this);
            var itemId = $(this).data('id');

            if(confirm(translate_14) != true) return false;

            if(window.inAjax != 'Y'){
                window.inAjax = 'Y';
            } else {
                alert(window.inAjaxMsg);
                return false;
            }

            $.ajax({
                url: "/communication/ajax/blockComment",
                data: {
                    adminId: userId,
                    itemId: itemId
                },
                dataType: 'json',
                type: 'post',
                success: function (response) {
                    window.inAjax = 'N';
                    if(response.error == 0){
                        target.closest('.list').find('.commentContent:first').find('p:first').text(translate_13);
                        target.closest('.optionBtnBox').prev().remove();
                        target.closest('.optionBtnBox').remove();
                        //optionBtnBox option

                    } else {
                        alert(response.message);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    window.inAjax = 'N';
                    if (xhr.status > 0) {
                        alert(translate_2);
                    }
                }
            });
            return false;
        });

        $(".btnCommentMore").on("click", function () {


            var target = $(this);
            var latestId = $(this).data('id');
            var latestRef = $(this).data('ref');



            if(latestRef < 1 || latestId < 1){
                $(this).remove();
                return false;
            }

            if(window.inAjax != 'Y'){
                window.inAjax = 'Y';
            } else {
                alert(window.inAjaxMsg);
                return false;
            }

            var option = $(this).attr('option');
            $.ajax({
                url: "/communication/ajax/moreComment",
                data: {
                    userId: userId,
                    commentId: headCommentId,
                    latestId: latestId,
                    latestRef: latestRef
                },
                dataType: 'json',
                type: 'post',
                success: function (response) {
                    window.inAjax = 'N';
                    if(response.error == 0){

                        if(response.size >= 1){
                            for(i = 0; i < response.size; i++){
                                $.factoryComment('more', '', response['comment'][i]);

                                target.data('id', response['comment'][i]['id']);
                                target.data('ref', response['comment'][i]['ref']);
                            }

                            if(response.size < response.listSize){
                                target.remove();
                            }

                        } else {
                            target.remove();
                        }

                    } else {
                        alert(response.message);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    window.inAjax = 'N';
                    if (xhr.status > 0) {
                        alert(translate_2);
                    }
                }
            });
            return false;
        });









        /**
         * Comment image upload
         */

        /**
         * Uploaded image cancel
         */
        $("#commentWrap").on("click", '.btnRemoveUploadImagePreviewAreaImage', function () {

            $(this).closest('.commentUploadImagePreviewArea').next().show();
            $(this).closest('.commentUploadImagePreviewArea').html('');
            if ($(this).parents('.fileBox').prev().find('img').length > 0) {
                $(this).parents('.fileBox').prev().find('img').remove();
            }


            /*

             $(this).next().next().show();
             $(this).parents('.commentRegArea').find('.commentFileUpload').show();
             $(this).parents('.commentRegArea').find('.btnCommentImageUpload').show();
             $(this).parents('.commentItem').find('.commentFileUpload').show();
             $(this).parents('.commentItem').find('.btnCommentImageUpload').show();
             $(this).parent().parent().remove();*/
        });

        $("#commentWrap").on("click", '.btnCommentImageUpload', function () {
            $(this).prev().click();
        });

        $("#commentWrap").on("change", '.commentFileHidden', function () {
            var fileData = $(this).prop("files")[0];
            if(fileData == undefined){
                return false;
            }

            if(typeof(fileData.size) !== 'undefined' && fileData.size >= 1 && fileData.size >= 1024 * 1024 * 8 ){
                // over limit size 8M
                alert(translate_5);
                $(this).val('');
                return false;
            }

            if(window.inAjax != 'Y'){
                window.inAjax = 'Y';
            } else {
                alert(window.inAjaxMsg);
                return false;
            }

            var fileCaption = '';
            var articleCaption = '';
            var option = $(this).attr('option');
            var target = $(this);

            var formData = new FormData();
            formData.append("mode", 'file');
            formData.append("file", fileData);
            formData.append("fileCaption", fileCaption);
            formData.append("articleCaption", articleCaption);
            formData.append("userId", userId);
            target.next().next().show();
            target.parent().hide();

            /**
             * Uploading dot
             *
             target.parent().find('.fileUploadDisplayArea').show();
             target.parent().find('.processingMessage').text('Uploading');
             var showDots = setInterval(function(){
             var d = target.parent().find('.loadingDots');
             d.text().length >= 3 ? d.text('') : d.append('.');
             },300);
             */

            $.ajax({
                //url: "/communication/ajax/fileUploads",
                url: "/news/ajax/imageUploads",
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
                dataType: 'json',
                type: 'post',
                success: function (response) {
                    //clearInterval(showDots);
                    //$('#fileUploadDisplayArea').hide();
                    //target.parent().find('.fileUploadDisplayArea').hide();
                    //target.next().show();
                    window.inAjax = 'N';
                    if(response.error == 0){

                        var text = '';
                        text += '<img src="' + imageUrl + response.fullUrl + '" alt="">';
                        text += '<input type="hidden" name="imageId" value="' + response.id + '" class="uploadImagePreviewAreaImageId" />';
                        text += '<div class="cancel cursor">';
                        text += '<img src="' + staticUrl + '/assets/images/admin/common/icon/delete.svg" alt="" class="btnRemoveUploadImagePreviewAreaImage">';
                        text += '</div>';

                        if(isMobile == 'Y'){
                            target.closest('.commentImageUploadArea').find('.commentUploadImagePreviewArea').html('');
                            target.closest('.commentImageUploadArea').find('.commentUploadImagePreviewArea').show();
                            target.closest('.commentImageUploadArea').find('.commentUploadImagePreviewArea').append(text);
                        } else {
                            //target.closest('.fileBox').find('.commentUploadImagePreviewArea').append(text);
                            target.closest('.commentImageUploadArea').find('.commentUploadImagePreviewArea').show();
                            target.closest('.commentImageUploadArea').find('.commentUploadImagePreviewArea').append(text);
                        }
                        /*
                        if (target.closest('.fileBox').length > 0) {
                            target.closest('.fileBox').find('.commentUploadImagePreviewArea').append(text);
                        } else if (target.closest('.fileContent').length > 0) {
                            target.closest('.fileContent').find('.commentUploadImagePreviewArea').html('');
                            target.closest('.fileContent').find('.commentUploadImagePreviewArea').show();
                            target.closest('.fileContent').find('.commentUploadImagePreviewArea').append(text);
                        }*/
                        target.val('');
                    } else {
                        target.next().next().hide();
                        target.parent().show();
                        alert(response.message);
                        target.val('');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    target.next().next().hide();
                    target.next().show();
                    target.parent().show();
                    //clearInterval(showDots);
                    //$('#fileUploadDisplayArea').hide();
                    //target.parent().find('.fileUploadDisplayArea').hide();

                    target.val('');
                    window.inAjax = 'N';
                    if (xhr.status > 0) {
                        alert(translate_2);
                    }
                }
            });
            return false;
        });

        /**
         * Comment reply
         */
        $('#commentListWrap').on('click', '.btnCommentReply', function () {

            var commentId = $(this).data('comment_id');
            // check reply box
            if($(this).closest('.commentItem').is(':last-child') == true) {
                // last child
            } else if($(this).closest('.commentItem').next().hasClass('addComment') == true) {
                $(this).closest('.commentItem').next().find('textarea').focus();
                return false;
            }

            $(this).closest('.commentItem').after($('#commentWrap .writingArea .addComment').clone().show());

            //$(this).parent().parent().parent().parent().next('.commentItem').find('.commentReplyBox').prepend(text);
            //$(this).closest('.commentItem').next().find('.commentWritingImg').html($('<img>').attr('src', $('.commentRegArea').find('.commentWritingImg img').attr('src')));
            $(this).closest('.commentItem').next().find('.btnCommentReplySubmit').data('comment_id', commentId);



            $(this).closest('.commentItem').next().find('textarea').focus();

        });

        // Comment reply cancel
        $('#commentListWrap').on('click', '.btnCommentReplyCancel', function () {
            $(this).closest('.addComment').remove();
        });



        $.factoryComment = function (type, target, response) {

            var text = '';
            text += '<div class="commentItem">';
            text += '<div class="list"';
            if(response.depth >= 1){
                text += ' style="padding-left:10px;"';
            }
            text += '>';

            text += '<div class="row row1 clear commentUserArea">';

            // 큰 화살표
            // 등록해서 새로 만들어지는 댓글
            if(response.depth >= 1){
                text += '<img class="floatL "  src="' + staticUrl + '/assets/images/admin/common/icon/iconAddedReply.svg" alt="" style="vertical-align: middle;margin:0 5px;">';
            }
            text += '<ul class="floatL" style="padding-top:3px;">';
            text += '<li><span class="name">' + response.userNickname + '</span></li>';
            text += '<li><span class="date">' + response.regTimestampFront + '</span></li>';
            text += '</ul>';
            text += '<div id="option" class="option floatR cursor">';
            text += '<img src="' + staticUrl + '/assets/images/admin/common/icon/option.svg" alt="">';
            text += '</div>';
            text += '<div class="optionBtnBox clear">';
            text += '<span class="btnSmall btn07 width70 btnCommentModify" idx="' + response.id + '">' + translate_8 + '</span>';
            text += '<span class="btnSmall btn07 width70 btnCommentDelete" idx="' + response.id + '">' + translate_9 + '</span>';
            text += '</div>';
            text += '</div>';


            text += '<div class="row row2 commentContent"';
            if(response.depth >= 1){
                text += ' style="padding-left:30px;"';
            }
            text += '>';


            text += '<p>';

            if(type == 'more'){
                text += $.nl2br(response.comment);
            } else {
                text += response.comment;
            }

            if(response.image != ''){
                text += '<img src="' + response.image + '" idx="' + response.imageId + '" style="margin-top:10px;max-width: 100%"/>';
            }
            text += '</p>';
            text += '</div>';



            if(isMobile == 'Y'){

                text += '<div class="commentContentModify hide">';
                text += '<textarea name="comment">' + response.comment + '</textarea>';
                text += '<div class="commentModifyOptionWrap">';
                text += '<div class="commentImageUploadArea commentModifyFile">';
                text += '<div class="commentUploadImagePreviewArea">';
                if(response.image != '') {
                    text += '<img src="' + response.image + '" idx="' + response.imageId + '" style="max-width: 100%"/>';
                    text += '<input type="hidden" name="imageId" value="' + response.imageId + '" class="uploadImagePreviewAreaImageId" />';
                    text += '<span class="btnRemoveUploadImagePreviewAreaImage">';
                    text += '<img src="' + staticUrl + '/assets/images/admin/common/icon/delete.svg">';
                    text += '</span>';
                }
                text += '</div>';



                if(response.image != '') {
                    text += '<div class="hide">';
                } else {
                    text += '<div class="">';
                }
                text += '<input type="file" name="hiddenUpload" class="commentFileHidden hide" >';
                text += '<img src="' + staticUrl + '/assets/images/admin/common/icon/upload.svg" alt="" class="btnCommentImageUpload">';
                text += '</div>';


                text += '</div>';
                text += '<ul  class="commentModifyOption clear">';
                text += '<li><span class="btnCommentModifyCancel btn05">' + translate_7 + '</span></li>';
                text += '<li><span class="btnCommentModifySubmit btn01" idx="' + response.id + '">' + translate_8 + '</span></li>';
                text += '</ul>';
                text += '</div>';
                text += '</div>';


            } else {

                text += '<div class="commentContentModify hide">';
                text += '<div class="clear box commentUserArea">';
                text += '<textarea name="comment" cols="30" rows="10" class="floatL">';
                text += response.comment;
                text += '</textarea>';

                text += '<button class="floatR btn04 width88 btnMiddle btnCommentModifySubmit" data-mode="modify" idx="' + response.id + '">' + translate_8 + '</button>';
                text += '</div>';
                text += '<div class="fileBox clear bg3f3f3f">';
                text += '<div class="commentImageUploadArea fileContent floatL">';
                text += '<div class="box commentUploadImagePreviewArea">';

                if(response.image != '') {
                    text += '<img src="' + response.image + '" idx="' + response.imageId + '" style="max-width: 100%"/>';
                    text += '<input type="hidden" name="imageId" value="' + response.imageId  + '" class="uploadImagePreviewAreaImageId" />';
                    text += '<span class="btnRemoveUploadImagePreviewAreaImage">';
                    text += '<img src="' + staticUrl + '/assets/images/admin/common/icon/delete.svg">';
                    text += '</span>';
                }
                text += '</div>';

                text += '<div class="commentFileUpload"';
                if(response.image != '') {
                    text += 'style="display:none;"';
                }
                text += '>';
                text += '<input type="file" name="hiddenUpload" class="commentFileHidden hide" >';
                text += '<img src="' + staticUrl + '/assets/images/admin/common/icon/upload.svg" alt="" class="btnCommentImageUpload">';
                text += '</div>';
                text += '</div>';
                text += '<div class="floatR"><span class="btnCommentModifyCancel btn btn1 width88">' + translate_7 + '</span></div>';
                text += '</div>';
                text += '</div>';
            }






            text += '<div class="row row3 clear"';
            if(response.refLevel >= 1){
                text += ' style="padding-left:30px;"';
            }
            text += '>';


            if(response.depth == 0){
                text += '<div class="btnAdd floatL cursor btnCommentReply" data-comment_id="' + response.id + '">';
                text += '<img src="' + staticUrl + '/assets/images/admin/common/icon/iconAddReply.svg" alt=""><span>' + translate_11 + '</span>';
                text += '</div>';
            }

            text += '<div class="status floatR">';
            text += '<ul class="clear">';
            text += '<li>';
            text += '<img src="' + staticUrl + '/assets/images/admin/theme/' + userColor + '/common/icon/voteUpOff.svg" alt="" class="commentVoteUp" idx="' + response.id + '">';
            text += ' <span>0</span>';
            text += '</li>';
            text += '<li>';
            text += '<img src="' + staticUrl + '/assets/images/admin/theme/' + userColor + '/common/icon/voteDownOff.svg" alt="" class="commentVoteDown" idx="' + response.id + '">';
            text += ' <span>0</span>';
            text += '</li>';
            text += '</ul>';
            text += '</div>';
            text += '</div>';

            text += '</div>';

            text += '</div>';



            // 프로필사진

            if(type == 'more'){
                // comment
                $('#commentListWrap').append(text);
            }else if(type == 'form'){
                // comment
                $('#commentListWrap').prepend(text);
                // profile image
                //$('#commentListWrap').find('.commentItem:first').find('.commentWritingImg').append($('<img>').attr('src',  $('.commentRegArea').find('.commentWritingImg').find('img').attr('src')));
                $('#formCommentReg')[0].reset();
                target.find('.commentUploadImagePreviewArea').html('');
            } else {
                // reply
                target.closest('.addComment').after(text);
                target.closest('.addComment').next().find('.commentWritingImg').html($('<img>').attr('src',  $('.commentRegArea').find('.commentWritingImg').find('img').attr('src')));

                if(target.closest('.addComment').find('.commentVoteUp').hasClass('voteUpVoted') == true){
                    target.closest('.addComment').next().find('.commentVoteUp').addClass('voteUpVoted');
                    target.closest('.addComment').next().find('.commentVoteUp').next().addClass('voteUpVoted');
                    target.closest('.addComment').next().find('.commentVoteUp').find('img').attr('src', staticUrl + '/assets/images/admin/theme/' + userColor + '/common/icon/voteUpOn.svg');
                }
                if(target.closest('.addComment').find('.commentVoteDown').hasClass('voteDownVoted') == true){
                    target.closest('.addComment').next().find('.commentVoteDown').addClass('voteDownVoted');
                    target.closest('.addComment').next().find('.commentVoteDown').next().addClass('voteDownVoted');
                    target.closest('.addComment').next().find('.commentVoteDown').find('img').attr('src', staticUrl + '/assets/images/admin/theme/' + userColor + '/common/icon/voteUpOff.svg');
                }
                target.closest('.addComment').remove();
            }
        };


        $('#commentListWrap').on('click', '.btnCommentReplySubmit', function () {
            var commentId = $(this).data('comment_id');
            var target = $(this);
            var depth = 0;

            var imageId = 0;
            if($(this).closest('.addComment').find('.uploadImagePreviewAreaImageId').length == 1){
                imageId = $(this).closest('.addComment').find('.uploadImagePreviewAreaImageId').val();
            }


            var comment = $(this).closest('.addComment').find('textarea').val();

            $.ajax({
                url: '/communication/ajax/addComment',
                type: 'post',
                data: {
                    'imageId' : imageId,
                    'userId' : userId,
                    'commentId' : commentId,
                    'comment' : comment
                },
                dataType: 'json',
                success: function (response) {
                    if (response.error != 0) {
                        alert(response.message);
                        return false;
                    } else {

                        if(response['rewardPopupDisplay'] != undefined && response['rewardPopupDisplay'] == 'Y'){
                            $.rewardPopupDisplay();
                        }
                        $.factoryComment('comment', target, response);
                        $('.displayCommentCountArea').text(parseInt($('.displayCommentCountArea').text()) + 1);
                        return false;
                    }

                    return false;
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    if (xhr.status > 0) {
                        /*서버 처리중 오류가 발생했습니다.*/
                        alert(translate_2);
                        return false;
                    }
                }
            });

        });



        /**
         * Comment voting(Up / Down)
         */
        $.commentVote = function (commentId, typeId, target) {
            if(userId < 1){
                alert(translate_4);
                var offset = $("#commentWrap").offset();
                if($('.articleCoverLogin').length == 1){
                    offset = $(".articleCoverLogin").offset();
                }

                $('html, body').animate({scrollTop : offset.top - 250}, 400);
                return false;
            } else {

                $.ajax({
                    url: '/communication/ajax/voteComment',
                    type: 'post',
                    data: {
                        'userId' : userId,
                        'commentId' : commentId,
                        'typeId' : typeId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.error != 0) {
                            alert(response.message);
                            return false;
                        } else {
                            if(typeId == 1){
                                if(target.hasClass('voteUpVoted')) {
                                    target.removeClass('voteUpVoted');
                                    target.next().removeClass('voteUpVoted');
                                    target.attr('src', staticUrl + '/assets/images/admin/theme/' + userColor + '/common/icon/voteUpOff.svg');
                                    target.next().text( parseInt(target.next().text()) - 1);
                                } else {
                                    target.addClass('voteUpVoted');
                                    target.next().addClass('voteUpVoted');
                                    target.attr('src', staticUrl + '/assets/images/admin/theme/' + userColor + '/common/icon/voteUpOn.svg');
                                    target.next().text( parseInt(target.next().text()) + 1);
                                }
                            } else if(typeId == 2) {
                                if(target.hasClass('voteDownVoted')){
                                    target.removeClass('voteDownVoted');
                                    target.next().removeClass('voteDownVoted');
                                    target.attr('src', staticUrl + '/assets/images/admin/theme/' + userColor + '/common/icon/voteDownOff.svg');
                                    target.next().text( parseInt(target.next().text()) - 1);
                                } else {
                                    target.addClass('voteDownVoted');
                                    target.next().addClass('voteDownVoted');
                                    target.attr('src', staticUrl + '/assets/images/admin/theme/' + userColor + '/common/icon/voteDownOn.svg');
                                    target.next().text( parseInt(target.next().text()) + 1);
                                }

                            } else {
                                // type error
                            }
                            //target.next().text(response.result);

                            if(response['rewardPopupDisplay'] != undefined && response['rewardPopupDisplay'] == 'Y'){
                                $.rewardPopupDisplay();
                            }
                        }

                        return false;
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        if (xhr.status > 0) {
                            alert(translate_2);
                            return false;
                        }
                    }
                });

            }
        };

        /**
         * Comment vote up
         */
        $('#commentListWrap').on('click', '.commentVoteUp', function () {
            var idx = $(this).attr('idx');
            if(idx < 1){
                return false;
            }
            $.commentVote(idx, 1, $(this));
        });

        /**
         * Comment vote down
         */
        $('#commentListWrap').on('click', '.commentVoteDown', function () {
            var idx = $(this).attr('idx');
            if(idx < 1){
                return false;
            }
            $.commentVote(idx, 2, $(this));
        });



        /*mobile document클릭시 닫기*/
        var optionBtnBox = $('.optionBtnBox');
        $('#commentListWrap').on('click', '.option', function () {
            $(this).next(optionBtnBox).show();
        });

        $(document).mouseup(function (e) {
            if (!optionBtnBox.is(e.target) && optionBtnBox.has(e.target).length === 0){
                optionBtnBox.stop().hide();
            }
        });

        /**
         * Comment
         * 댓글 끝
         **/


    });
});