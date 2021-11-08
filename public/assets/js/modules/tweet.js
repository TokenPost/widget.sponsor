require(['base'], function () {
    require(['popup'], function (popup) {
        $(document).ready(function () {
        });
        
        /**
         * Start of Tweet
         **/
        $('#formTweetReg').on('submit', function () {
            var target = $(this);

            if(this.tweet.value == ''){
                alert(translate_1);
                return false;
            }

            $.ajax({
                url: '/tweet/ajax/addTweet',
                type: 'post',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.error != 0) {
                        alert(response.message);
                        return false;
                    }

                    $.factoryTweet('form', target, response);
                    target.parents('.tweetRegArea').find('.tweetFileUpload').show();
                    target.parents('.tweetRegArea').find('.btnTweetImageUpload').show();
                    $('.displayTweetCountArea').text(parseInt($('.displayTweetCountArea').text()) + 1);

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
         * Tweet delete
         */
        $('#tweetListWrap').on('click', '.btnTweetDelete', function () {
            var idx = $(this).attr('idx');
            var target = $(this);

            if(confirm(translate_3) !== true) return false;

            $.ajax({
                url: '/tweet/ajax/deleteTweet',
                type: 'post',
                data: {
                    'clientId' : clientId,
                    'tweetId' : idx
                },
                dataType: 'json',
                success: function (response) {
                    if (response.error != 0) {
                        alert(response.message);
                        return false;
                    }
                    target.parents('.tweetItem').find('.tweetVoteUp').attr('idx', '0');
                    target.parents('.tweetItem').find('.tweetVoteUp').removeClass('tweetVoteUp');
                    target.parents('.tweetItem').find('.tweetVoteDown').attr('idx', '0');
                    target.parents('.tweetItem').find('.tweetVoteDown').removeClass('tweetVoteDown');

                    target.parents('.tweetItem').find('.tweetContent').html('<p>' + translate_12 + '</p>');

                    target.parents('.tweetItem').find('.tweetUserArea').find('.option').remove();
                    target.parents('.tweetItem').find('.tweetUserArea').find('.optionBtnBox').remove();

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
         * Tweet modify cancel
         */
        $('#tweetListWrap').on('click', '.btnTweetModifyCancel', function () {
            var parentsObj = $(this).parents('.tweetItem');
            parentsObj.find('.tweetContent').show();
            parentsObj.find('.tweetContentModify').hide();
            parentsObj.find('.status').show();
            parentsObj.find('.option').show();
        });

        /**
         * Tweet modify form open
         */
        $('#tweetListWrap').on('click', '.btnTweetModify', function () {


            var parentsObj = $(this).closest('.tweetItem');

            parentsObj.find('.tweetContent').hide();
            parentsObj.find('.tweetContentModify').show();
            parentsObj.find('.status').hide();
            parentsObj.find('.option').hide();

            if(isMobile == 'Y'){
                parentsObj.find('.optionBtnBox ').hide();
            }


            return false;
            /*

             $(this).parents('.tweetItem').find('.tweetContentModify').html($('#tweetWrap .writingArea .addTweet').clone().html());
             $(this).parents('.tweetItem').find('.tweetContent').hide();
             $(this).parents('.tweetItem').find('.tweetContentModify').show();
             */

            var text = '';
            $(this).parents('.tweetItem').find('.tweetContentModify').find('textarea').val($(this).parents('.tweetItem').find('.tweetContent').text().trim());


            if($(this).parents('.tweetItem').find('.tweetContent').find('img').length >= 1) {

                text += '<div class="box tweetUploadImagePreviewArea">';
                text += '   <img src="' + $(this).parents('.tweetItem').find('.tweetContent').find('img').attr('src') + '">';
                text += '   <input type="hidden" name="imageId" value="' + $(this).parents('.tweetItem').find('.tweetContent').find('img').attr('idx') + '" class="uploadImagePreviewAreaImageId" />';
                text += '   <div class="cancel cursor ">';
                text += '       <img src="' + staticUrl + '/assets/images/front/1/web/comment/icon/delete.svg" alt="" class="btnRemoveUploadImagePreviewAreaImage">';
                text += '   </div>';
                text += '</div>';

                text += '<div class="tweetFileUpload" style="display:none;">';
                text += '   <input type="file" name="hiddenUpload" class="tweetFileHidden hide" >';
                text += '   <img src="' + staticUrl + '/assets/images/front/1/web/tweet/icon/upload.svg" alt="" class="btnTweetImageUpload">';
                text += '</div>';

                $(this).parents('.tweetItem').find('.tweetContentModify').find('.fileBox').find('.fileContent').prepend(text);

            } else {

                $(this).parents('.tweetItem').find('.tweetUploadImagePreviewArea').html('');
                $(this).parents('.tweetItem').find('.tweetFileUpload').show();

                if($(this).parents('.tweetItem').find('.tweetContentModify').find('.tweetModifyFile').find('.tweetFileUpload').length === 0){
                    text += '<div class="tweetFileUpload">';
                    text += '<input type="file" name="hiddenUpload" class="tweetFileHidden hide" >';
                    text += '<img src="' + staticUrl + '/assets/images/front/1/web/tweet/icon/upload.svg" alt="" class="btnTweetImageUpload">';
                    text += '</div>';
                    $(this).parents('.tweetItem').find('.tweetContentModify').find('.fileBox').find('.fileContent').append(text);
                }
            }

            $(this).parents('.tweetItem').find('.tweetContent').hide();
            $(this).parents('.tweetItem').find('.tweetContentModify').show();
            $(this).parents('.tweetItem').find('.tweetContentModify').find('.btnTweetModifySubmit').attr('idx', $(this).attr('idx'))




        });

        /**
         * Tweet modify submit
         */

        $('#tweetListWrap').on('click', '.btnTweetModifySubmit', function () {
            var imageId = 0;
            var target = $(this);
            var tweetId = $(this).attr('idx');
            if($(this).parents('.tweetContentModify').find('.uploadImagePreviewAreaImageId').length == 1){
                imageId = $(this).parents('.tweetContentModify').find('.uploadImagePreviewAreaImageId').val();
            }

            $.ajax({
                url: '/tweet/ajax/modifyTweet',
                type: 'post',
                data: {
                    'imageId' : imageId,
                    'clientId' : clientId,
                    'tweetId' : tweetId,
                    'tweet' : $(this).parents('.tweetContentModify ').find('textarea').val()
                },
                dataType: 'json',
                success: function (response) {
                    if (response.error != 0) {
                        alert(response.message);
                        return false;
                    } else {
                        target.parents('.tweetItem').find('.tweetContent').find('p').html(response.tweet);
                        var text = '';
                        if (response.image != '') {
                            text += '<img src="' + response.image + '">';
                        }

                        target.parents('.tweetItem').find('.tweetContent').find('p').append(text);
                        target.parents('.tweetItem').find('.tweetContent').show();
                        target.parents('.tweetItem').find('.tweetContentModify').hide();
                        target.parents('.tweetItem').find('.status').show();
                        target.parents('.tweetItem').find('.option').show();
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



        $(".btnTweetMore").on("click", function () {


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
                url: "/tweet/ajax/moreTweet",
                data: {
                    clientId: clientId,
                    tweetId: headTweetId,
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
                                $.factoryTweet('more', '', response['tweet'][i]);

                                target.data('id', response['tweet'][i]['id']);
                                target.data('ref', response['tweet'][i]['ref']);
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
         * Tweet image upload
         */

        /**
         * Uploaded image cancel
         */
        $("#tweetWrap").on("click", '.btnRemoveUploadImagePreviewAreaImage', function () {

            $(this).closest('.tweetUploadImagePreviewArea').next().show();
            $(this).closest('.tweetUploadImagePreviewArea').html('');
            if ($(this).parents('.fileBox').prev().find('img').length > 0) {
                $(this).parents('.fileBox').prev().find('img').remove();
            }


            /*

             $(this).next().next().show();
             $(this).parents('.tweetRegArea').find('.tweetFileUpload').show();
             $(this).parents('.tweetRegArea').find('.btnTweetImageUpload').show();
             $(this).parents('.tweetItem').find('.tweetFileUpload').show();
             $(this).parents('.tweetItem').find('.btnTweetImageUpload').show();
             $(this).parent().parent().remove();*/
        });

        $("#tweetWrap").on("click", '.btnTweetImageUpload', function () {
            $(this).prev().click();
        });

        $("#tweetWrap").on("change", '.tweetFileHidden', function () {
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
            formData.append("file", fileData);
            formData.append("fileCaption", fileCaption);
            formData.append("articleCaption", articleCaption);
            formData.append("clientId", clientId);
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
                url: "/tweet/ajax/imageUploads",
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
                        text += '<img src="' + staticUrl + '/assets/images/front/1/web/comment/icon/delete.svg" alt="" class="btnRemoveUploadImagePreviewAreaImage">';
                        text += '</div>';

                        if(isMobile == 'Y'){
                            target.closest('.tweetImageUploadArea').find('.tweetUploadImagePreviewArea').html('');
                            target.closest('.tweetImageUploadArea').find('.tweetUploadImagePreviewArea').show();
                            target.closest('.tweetImageUploadArea').find('.tweetUploadImagePreviewArea').append(text);
                        } else {
                            //target.closest('.fileBox').find('.tweetUploadImagePreviewArea').append(text);
                            target.closest('.tweetImageUploadArea').find('.tweetUploadImagePreviewArea').show();
                            target.closest('.tweetImageUploadArea').find('.tweetUploadImagePreviewArea').append(text);
                        }
                        /*
                         if (target.closest('.fileBox').length > 0) {
                         target.closest('.fileBox').find('.tweetUploadImagePreviewArea').append(text);
                         } else if (target.closest('.fileContent').length > 0) {
                         target.closest('.fileContent').find('.tweetUploadImagePreviewArea').html('');
                         target.closest('.fileContent').find('.tweetUploadImagePreviewArea').show();
                         target.closest('.fileContent').find('.tweetUploadImagePreviewArea').append(text);
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

                    target.val('');
                    window.inAjax = 'N';
                    if (xhr.status > 0) {
                        alert("서버 처리중 문제가 발생했습니다.");
                        return;
                    }
                }
            });
            return false;
        });

        /**
         * Tweet reply
         */
        $('#tweetListWrap').on('click', '.btnTweetReply', function () {

            var tweetId = $(this).data('tweet_id');
            if($(this).closest('.tweetItem').is(':last-child') == true) {
            } else if($(this).closest('.tweetItem').next().hasClass('addTweet') == true) {
                $(this).closest('.tweetItem').next().find('textarea').focus();
                return false;
            }
            $(this).closest('.tweetItem').after($('#tweetWrap .writingArea .addTweet').clone().show());
            $(this).closest('.tweetItem').next().find('.btnTweetReplySubmit').data('tweet_id', tweetId);
            $(this).closest('.tweetItem').next().find('textarea').focus();
        });

        $('#tweetListWrap').on('click', '.btnTweetReplyCancel', function () {
            $(this).closest('.addTweet').remove();
        });

        $.factoryTweet = function (type, target, response) {

            var text = '';
            text += '<div class="tweetItem">';
            text += '<div class="list"';
            if(response.depth >= 1){
                text += ' style="padding-left:10px;"';
            }
            text += '>';

            text += '<div class="row row1 clear tweetUserArea">';

            if(response.depth >= 1){
                text += '<img class="floatL "  src="' + staticUrl + '/assets/images/front/1/web/tweet/icon/addedReply.svg" alt="" style="vertical-align: middle;margin:0 5px;">';
            }
            text += '<ul class="floatL" style="padding-top:0px;">';
            text += '<li><span class="name">' + response.userNickname + '</span></li>';
            text += '<li><span class="date">' + response.regTimestampFront + '</span></li>';
            text += '</ul>';
            text += '<div id="option" class="option floatR cursor">';
            text += '<img src="' + staticUrl + '/assets/images/front/1/web/tweet/icon/option.svg" alt="">';
            text += '</div>';
            text += '<div class="optionBtnBox clear">';
            text += '<span class="btnSmall btn07 width70 btnTweetModify" idx="' + response.id + '">' + translate_8 + '</span>';
            text += '<span class="btnSmall btn07 width70 btnTweetDelete" idx="' + response.id + '">' + translate_9 + '</span>';
            text += '</div>';
            text += '</div>';

            text += '<div class="row row2 tweetContent"';
            if(response.depth >= 1){
                text += ' style="padding-left:30px;"';
            }
            text += '>';


            text += '<p>';

            if(type == 'more'){
                text += $.nl2br(response.tweet);
            } else {
                text += response.tweet;
            }

            if(response.image != ''){
                text += '<img src="' + response.image + '" idx="' + response.imageId + '" style="margin-top:10px;max-width: 100%"/>';
            }
            text += '</p>';
            text += '</div>';



            if(isMobile == 'Y'){

                text += '<div class="tweetContentModify hide">';
                text += '<textarea name="tweet">' + response.tweet + '</textarea>';
                text += '<div class="tweetModifyOptionWrap">';
                text += '<div class="tweetImageUploadArea tweetModifyFile">';
                text += '<div class="tweetUploadImagePreviewArea">';
                if(response.image != '') {
                    text += '<img src="' + response.image + '" idx="' + response.imageId + '" style="max-width: 100%"/>';
                    text += '<input type="hidden" name="imageId" value="' + response.imageId + '" class="uploadImagePreviewAreaImageId" />';
                    text += '<span class="btnRemoveUploadImagePreviewAreaImage">';
                    text += '<img src="' + staticUrl + '/assets/images/front/1/web/tweet/icon/delete.svg">';
                    text += '</span>';
                }
                text += '</div>';

                if(response.image != '') {
                    text += '<div class="hide">';
                } else {
                    text += '<div class="">';
                }
                text += '<input type="file" name="hiddenUpload" class="tweetFileHidden hide" >';
                text += '<img src="' + staticUrl + '/assets/images/front/1/web/tweet/icon/upload.svg" alt="" class="btnTweetImageUpload">';
                text += '</div>';


                text += '</div>';
                text += '<ul  class="tweetModifyOption clear">';
                text += '<li><span class="btnTweetModifyCancel btn05">' + translate_7 + '</span></li>';
                text += '<li><span class="btnTweetModifySubmit btn01" idx="' + response.id + '">' + translate_8 + '</span></li>';
                text += '</ul>';
                text += '</div>';
                text += '</div>';


            } else {

                text += '<div class="tweetContentModify hide">';
                text += '<div class="clear box tweetUserArea">';
                text += '<textarea name="tweet" cols="30" rows="10" class="floatL">';
                text += response.tweet;
                text += '</textarea>';

                text += '<button class="floatR btn04 width88 btnMiddle btnTweetModifySubmit" data-mode="modify" idx="' + response.id + '">' + translate_8 + '</button>';
                text += '</div>';
                text += '<div class="fileBox clear bg3f3f3f">';
                text += '<div class="tweetImageUploadArea fileContent floatL">';
                text += '<div class="box tweetUploadImagePreviewArea">';

                if(response.image != '') {
                    text += '<img src="' + response.image + '" idx="' + response.imageId + '" style="max-width: 100%"/>';
                    text += '<input type="hidden" name="imageId" value="' + response.imageId  + '" class="uploadImagePreviewAreaImageId" />';
                    text += '<span class="btnRemoveUploadImagePreviewAreaImage">';
                    text += '<img src="' + staticUrl + '/assets/images/front/1/web/tweet/icon/delete.svg">';
                    text += '</span>';
                }
                text += '</div>';

                text += '<div class="tweetFileUpload"';
                if(response.image != '') {
                    text += 'style="display:none;"';
                }
                text += '>';
                text += '<input type="file" name="hiddenUpload" class="tweetFileHidden hide" >';
                text += '<img src="' + staticUrl + '/assets/images/front/1/web/tweet/icon/upload.svg" alt="" class="btnTweetImageUpload">';
                text += '</div>';
                text += '</div>';
                text += '<div class="floatR"><span class="btn05 btnMiddle width88 btnTweetModifyCancel">' + translate_7 + '</span></div>';
                text += '</div>';
                text += '</div>';
            }






            text += '<div class="row row3 clear"';
            if(response.refLevel >= 1){
                text += ' style="padding-left:30px;"';
            }
            text += '>';


            if(response.depth == 0){
                text += '<div class="btnAdd floatL cursor btnTweetReply" data-tweet_id="' + response.id + '">';
                text += '<img src="' + staticUrl + '/assets/images/front/1/web/tweet/icon/addReply.svg" alt=""><span>' + translate_11 + '</span>';
                text += '</div>';
            }
            text += '</div>';
            text += '</div>';
            text += '</div>';



            if(type == 'more'){
                $('#tweetListWrap').append(text);
            }else if(type == 'form'){
                $('#tweetListWrap').prepend(text);
                $('#formTweetReg')[0].reset();
                target.find('.tweetUploadImagePreviewArea').html('');
            } else {
                target.closest('.addTweet').after(text);
                target.closest('.addTweet').next().find('.tweetWritingImg').html($('<img>').attr('src',  $('.tweetRegArea').find('.tweetWritingImg').find('img').attr('src')));

                if(target.closest('.addTweet').find('.tweetVoteUp').hasClass('voteUpVoted') == true){
                    target.closest('.addTweet').next().find('.tweetVoteUp').addClass('voteUpVoted');
                    target.closest('.addTweet').next().find('.tweetVoteUp').next().addClass('voteUpVoted');
                    target.closest('.addTweet').next().find('.tweetVoteUp').find('img').attr('src', staticUrl + '/assets/images/front/common/icon/iconVoteUpOn.png');
                }
                if(target.closest('.addTweet').find('.tweetVoteDown').hasClass('voteDownVoted') == true){
                    target.closest('.addTweet').next().find('.tweetVoteDown').addClass('voteDownVoted');
                    target.closest('.addTweet').next().find('.tweetVoteDown').next().addClass('voteDownVoted');
                    target.closest('.addTweet').next().find('.tweetVoteDown').find('img').attr('src', staticUrl + '/assets/images/front/common/icon/iconVoteDownOff.png');
                }
                target.closest('.addTweet').remove();
            }
        };


        $('#tweetListWrap').on('click', '.btnTweetReplySubmit', function () {
            var tweetId = $(this).data('tweet_id');
            var target = $(this);
            var depth = 0;

            var imageId = 0;
            if($(this).closest('.addTweet').find('.uploadImagePreviewAreaImageId').length == 1){
                imageId = $(this).closest('.addTweet').find('.uploadImagePreviewAreaImageId').val();
            }


            var tweet = $(this).closest('.addTweet').find('textarea').val();

            $.ajax({
                url: '/tweet/ajax/addTweet',
                type: 'post',
                data: {
                    'imageId' : imageId,
                    'clientId' : clientId,
                    'tweetId' : tweetId,
                    'tweet' : tweet
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
                        $.factoryTweet('tweet', target, response);
                        $('.displayTweetCountArea').text(parseInt($('.displayTweetCountArea').text()) + 1);
                        return false;
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



        /**
         * Tweet voting(Up / Down)
         */
        $.tweetVote = function (tweetId, typeId, target) {
            if(clientId < 1){
                alert(translate_4);
                var offset = $("#tweetWrap").offset();
                if($('.articleCoverLogin').length == 1){
                    offset = $(".articleCoverLogin").offset();
                }

                $('html, body').animate({scrollTop : offset.top - 250}, 400);
                return false;
            } else {

                $.ajax({
                    url: '/tweet/ajax/voteTweet',
                    type: 'post',
                    data: {
                        'clientId' : clientId,
                        'tweetId' : tweetId,
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
                                    target.attr('src', staticUrl + '/assets/images/front/common/icon/iconVoteUpOff.png');
                                    target.next().text( parseInt(target.next().text()) - 1);
                                } else {
                                    target.addClass('voteUpVoted');
                                    target.next().addClass('voteUpVoted');
                                    target.attr('src', staticUrl + '/assets/images/front/common/icon/iconVoteUpOn.png');
                                    target.next().text( parseInt(target.next().text()) + 1);
                                }
                            } else if(typeId == 2) {
                                if(target.hasClass('voteDownVoted')){
                                    target.removeClass('voteDownVoted');
                                    target.next().removeClass('voteDownVoted');
                                    target.attr('src', staticUrl + '/assets/images/front/common/icon/iconVoteDownOff.png');
                                    target.next().text( parseInt(target.next().text()) - 1);
                                } else {
                                    target.addClass('voteDownVoted');
                                    target.next().addClass('voteDownVoted');
                                    target.attr('src', staticUrl + '/assets/images/front/common/icon/iconVoteDownOn.png');
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
         * Tweet vote up
         */
        $('#tweetListWrap').on('click', '.tweetVoteUp', function () {
            var idx = $(this).attr('idx');
            if(idx < 1){
                return false;
            }
            $.tweetVote(idx, 1, $(this));
        });

        /**
         * Tweet vote down
         */
        $('#tweetListWrap').on('click', '.tweetVoteDown', function () {
            var idx = $(this).attr('idx');
            if(idx < 1){
                return false;
            }
            $.tweetVote(idx, 2, $(this));
        });



        /*mobile document클릭시 닫기*/
        var optionBtnBox = $('.optionBtnBox');
        $('#tweetListWrap').on('click', '.option', function () {
            $(this).next(optionBtnBox).show();
        });

        $(document).mouseup(function (e) {
            if (!optionBtnBox.is(e.target) && optionBtnBox.has(e.target).length === 0){
                optionBtnBox.stop().hide();
            }
        });

        /**
         * Tweet
         * 댓글 끝
         **/


    });
});