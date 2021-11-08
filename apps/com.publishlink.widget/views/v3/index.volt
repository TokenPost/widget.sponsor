{% extends 'base.v3.volt' %}


{% block embedStyle %}
    <link rel="stylesheet" type="text/css" href="{{ staticUrl }}/assets/css/widget/index.v3.css?r={{ cssRevision }}"/>
    <link rel="stylesheet" type="text/css" href="{{ staticUrl }}/assets/css/widget/base.v3.css?r={{ cssRevision }}"/>
    <link rel="stylesheet" type="text/css" href="{{ staticUrl }}/assets/css/widget/media.v3.css?r={{ cssRevision }}"/>
{% endblock %}


{% block embedScript %}
<script xmlns="http://www.w3.org/1999/html">

    {# 상위 정보 받아오기 시작 #}
    window.addEventListener('message', function (e) {
        if(e.data.parentUrl != undefined && e.data.parentUrl != '') {
            document.getElementById('purl').value = e.data.parentUrl;
        }
    });
    {# 상위 정보 받아오기 끝 #}

    var purl = document.getElementById('purl').value;
    var code = "{{ widgetCode }}";
    var widgetId = "{{ widget.getId() }}";
    var clientId = "{{ clientId }}";
    require(['base'], function () {
        require(['popup'], function (popup) {

            {# 1초 후, formInfo 실행 #}
            setTimeout(function () {
                if($('form input#purl').val() != "" || $('form input#purl').val() != undefined) {
                    $('#formInfo').submit();
                }
            }, 1000);
            {# 실행 끝 #}


            {# 위젯 존재 여부 확인 시작 #}
            $('#formInfo').submit(function () {
                purl = this.purl.value;
                if(purl == '' || purl == 'undefined' || purl == undefined) {
                    setTimeout(function () {
                        $('#formInfo').submit();
                    }, 1000);
                    return false;
                }
                $.ajax({
                    url: '/index/ajax/widgetCheck',
                    type: 'post',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if (response.error != 0) {
                            alert(response.message);
                            window.inAjax = 'N';
                            return false;
                        } else {
                            if(response.display == 'Y') {
                                $('div.widgetWrap').removeClass('hide');
                                $('form#formLogin input#parentUrl').val(purl);

                                {% if clientId >= 0 %}
                                $.memberInfo({{ clientId }});
                                {% endif %}
                            }
                            return false;
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status > 0) {
                            alert('서버처리중 오류가 발생하였습니다.');
                            window.inAjax = 'N';
                            return false;
                        }
                    }
                });
                return false;
            });
            {# 위젯 존재 여부 확인 끝 #}


            $('#widgetToast').hide();


            {# 로그인 시작 #}
            $("form#formLogin").submit(function() {
                if (!this.id.value) {
                    this.id.focus();
                    return false;
                }
                if (!this.pw.value) {
                    this.pw.focus();
                    return false;
                }


                {# 이메일 저장 체크된 상태이면 localStorage에 저장 #}
                if($.publishLinkEmailSaveCheck() == true) {
                    localStorage.setItem("publishLinkEmailSaveCheck", this.id.value);
                } else {
                    localStorage.removeItem("publishLinkEmailSaveCheck");
                }

                $.ajax({
                    url: "/index/ajax/login",
                    type: "post",
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if (response.error != 0) {
                            if (response.error == 99) {
                                $('#widgetToast').fadeIn(800).delay(800).fadeOut(800);
                                return;
                            }
                            $('#widgetToast').fadeIn(800).delay(800).fadeOut(800);
                            return;
                        } else {
                            if(response.clientId > 0) {
                                clientId = response.clientId;
                                $.memberInfo(clientId);
                            }
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status > 0) {
                            $('p#loginNotice').show();
                        }
                    }
                });
                return false;

            });
            {# 로그인 끝 #}

            {# 로그인 후 정보 가져오기 시작 #}
            $.memberInfo = function(memberId) {
                if(memberId == 0 || memberId != clientId) {
                    $('div.widgetPadding').addClass('hide');
                    $('div.widgetIntro').removeClass('hide');
                    return false;
                }
                $.ajax({
                    url: "/index/ajax/memberInfo",
                    type: 'post',
                    data: {
                        memberId : memberId,
                        widgetCode : $('form#formInfo input#code').val(),
                        purl : $('form#formInfo input#purl').val()
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.error != 0) {
                            alert(response.message);
                            window.inAjax = 'N';
                            return false;
                        } else {
                            $('.widgetWrap').removeClass('hide');
                            {# 프로필 show, 초기화면 hide, 로그인 hide #}
                            $('div.widgetPadding').addClass('hide');
                            $("div.widgetIntro").addClass('hide');
                            $("div.widgetProfileWrap").removeClass('hide');

                            {# 회원 정보 뿌려주기 #}
                            {# 회원 기본 정보 #}
                            $('dt#userName').text(response.item.name);
                            $('dd#userEmail').text(response.item.email);
                            if(response.item.profileId >= 1) {
                                {# 프로필 이미지 있는 경우 #}
                                $('div.profileBasicArea').addClass('hide');
                                $('div.profileImgArea').removeClass('hide');
                                $('div.profileImgArea img').attr('src', response.item.profile)
                            } else {
                                {# 없는 경우 #}
                                $('div.profileBasicArea').removeClass('hide');
                                $('div.profileImgArea').addClass('hide');
                                $('div.profileBasicArea strong.basicName').text(response.item.profilename);
                            }
                            {# 회원 적립내역, 보유 포인트 #}
                            {# 보유 자산 #}
                            $('dl dd#pPoint').text(response.item.pPoint);
                            $('dl dd#news').text(response.item.news);
                            {# 하루 적립내역 #}
                            var nsign = '';
                            var nDifference = parseFloat(response.item.nDifference.replace(',', ''));
                            if(nDifference > 0) {
                                nsign = '+';
                            } else if(nDifference < 0) {
                                nsign = '-';
                            }
                            $('dl dd#nToday').text(nsign + Math.abs(nDifference));

                            var psign = '';
                            var pDifference = parseFloat(response.item.pDifference.replace(',', ''));
                            if(pDifference > 0) {
                                psign = '+';
                            } else if(pDifference < 0) {
                                psign = '-';
                            }
                            $('dl dd#pToday').text(psign + Math.abs(pDifference));


                            {# 후원하기 부분에 ppoint 표출 #}
                            $('p.rewardPoint span#pDonation').text(response.item.pPoint);
                            $('input[name=clientPoint]').val(response.item.pPoint);
                            $('form#formDonate input[name=clientId]').val(memberId);

                            {# 리워드알림 - 토스트 알림 시작 #}
                            if(response.item.toastDisplay == 'Y') {
                                $('#widgetRewardNotice').removeClass('hide');
                                $('#widgetRewardNotice').css('top', '300px');
                                $('#widgetRewardNotice').animate({top:0}, 0, 'linear');
                                $('form#formReward input[name=activityId]').val(response.item.activityId);
                                $('form#formReward input[name=clientId]').val(memberId);
                                $('form#formReward input[name=rewardId]').val(response.item.rewardId);
                            } else {
                                $('#widgetRewardNotice').addClass('hide');
                            }
                            {# 리워드알림 - 토스트 알림 끝 #}

                            purl = $('#formInfo input#purl').val();
                            {# 기사 공유하기 #}
                            $('input#referralCode').val(response.item.referralCode);
                            if(purl.indexOf('?wg_ref=') != -1) {
                                purl = purl.split('?wg_ref=')[0];
                            }
                            $('input#copyShareUrl').val(purl + '?wg_ref=' + response.item.referralCode);
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status > 0) {
                            alert('서버처리중 오류가 발생하였습니다.');
                            window.inAjax = 'N';
                            return false;
                        }
                    }
                });
                return false;
            };
            {# 로그인 후 정보 가져오기 끝 #}


            {# document ready 시작 #}
            $(document).ready(function() {
                {# 로그인 상태인지 체크 #}
                if(clientId == 0) {
                    var testInterval = setInterval(function() {
                        $.ajax({
                            url: "/index/ajax/loginSessionCheck",
                            type: 'post',
                            data: {
                                widgetCode : $('form#formInfo input#code').val(),
                                purl : $('form#formInfo input#purl').val()
                            },
                            dataType: 'json',
                            success: function (response) {
                                if (response.error != 0) {
                                    alert(response.message);
                                    window.inAjax = 'N';
                                    return false;
                                } else {
                                    if(response.id >= 1) {
                                        clientId = response.id;
                                        clearTimeout(testInterval);

                                        window.location.reload();
                                    }
                                }
                            },
                            error: function (xhr) {
                                if (xhr.status > 0) {
                                    alert('서버처리중 오류가 발생하였습니다.');
                                    window.inAjax = 'N';
                                    return false;
                                }
                            }
                        });
                        return false;
                    }, 3000);
                }

                {# 저장된 이메일 있으면 가져오기 #}
                var savedEmail = localStorage.getItem('publishLinkEmailSaveCheck');
                if(savedEmail != '' && savedEmail != null && savedEmail != undefined) {
                    $('div.idArea input#widgetId').val(savedEmail);
                    $('span.saveEmailInner img.checkBox').attr('src', '/assets/images/front/widget/icon/checkBoxActive.svg');
                    $("input#saveEmail").attr("checked", true);
                } else {
                    $('span.saveEmailInner img.checkBox').attr('src', '/assets/images/front/widget/icon/checkBoxBasic.svg');
                    $("input#saveEmail").attr("checked", false);
                }

            });
            {# document ready 끝 #}


            {# 초기화면 - 로그인 화면 이동 시작 #}
            $('.introBtn:last-child').on('click', function (){
                $('#widgetIntro').addClass('hide');
                $('#widgetLoginWrap').removeClass('hide');
            });
            {# 초기화면 - 로그인 화면 이동 끝 #}


            {# 로그인 - 이메일 저장 체크박스 시작 #}
            {# 세로형 #}
            $('#saveEmail').on('click', function (){
                if($('#saveEmail').attr('checked') == null){
                    $('#saveEmail').attr('checked', true);
                } else {
                    $('#saveEmail').attr('checked', false);
                }

               let saveEmailCheck = $(this).siblings('.checkBox');
               saveEmailCheck.attr('src', function (index, attr){
                  if(attr.match('checkBoxBasic')){
                      return attr.replace('/assets/images/front/widget/icon/checkBoxBasic.svg', '/assets/images/front/widget/icon/checkBoxActive.svg');
                  } else {
                      return attr.replace('/assets/images/front/widget/icon/checkBoxActive.svg', '/assets/images/front/widget/icon/checkBoxBasic.svg');
                  }
               });
            });
            {# 로그인 - 이메일 저장 체크박스 끝 #}

            {# 이메일 저장 유효성 체크 시작 #}
            $.publishLinkEmailSaveCheck = function() {
                if($('input#widgetId').val() == '') {
                    return false;
                }
                if($('input#widgetPw').val() == '') {
                    return false;
                }
                if($('#saveEmail').is(":checked") == false) {
                    return false;
                }
                return true;
            };
            {# 이메일 저장 유효성 체크 끝 #}

            {# 로그인 - 이메일 한글입력 무시 시작 #}
            $('#widgetId').on('keyup', function (){
               $(this).val($(this).val().replace(/[^a-z0-9*@_.-]/g,''));
            });
            {# 로그인 - 이메일 한글입력 무시 끝 #}

            {# 프로필 - 더보기 버튼 시작 #}
            $('.arrowArea').on('click', function (){
                if($(this).siblings('.userMoreWrap').hasClass('hide')){
                    $(this).siblings('.userMoreWrap').removeClass('hide');
                    $(this).siblings('.dim').removeClass('hide');
                    $(this).children('.downArrow').attr('src', '/assets/images/front/widget/icon/widgetDropDownWhite.svg');
                    $(this).children('.downArrow').css('transform', 'rotate(180deg)');
                } else {
                    $(this).siblings('.userMoreWrap').addClass('hide');
                    $(this).siblings('.dim').addClass('hide');
                    $(this).children('.downArrow').attr('src', '/assets/images/front/widget/icon/widgetDropDown.svg');
                    $(this).children('.downArrow').css('transform', 'rotate(360deg)');
                }
            });

            {# ==================================================================================================== #}
            {# 후원하기 - 시작 #}
            $.donateInputCheck = function() {
                let inputDonate = $('input#donate').val();
                let clientPoint = $('form#formDonate input[name=clientPoint]').val();
                clientPoint = clientPoint.replace(/,/g, "");

                if(inputDonate == '' || inputDonate.length == 0) {
                    $('#removeTxt').addClass('hide')
                    return false;
                }
                if(parseInt(inputDonate) > parseInt(clientPoint)) {
                    $('input[name=donate]').val('');
                    $('.excessNotice').css('display', 'block');
                    return false;
                } else {
                    $('.excessNotice').css('display', 'none');
                }
                return true;
            };

            {# 후원하기 - 입력값 삭제 X아이콘 표출 시작 #}
            let rewardInput = $('#donate');
            rewardInput.on('keyup', function (){
                $(this).siblings($('#removeTxt').removeClass('hide'));
            });
            $('#removeTxt').on('click', function (){
                rewardInput.val("");
                rewardInput.css('color', '#5D5FEF');
                $('.widgetRewardWrap .rewardInputArea.excess').css('border-bottom', '1px solid #5D5FEF');
                $('.excessNotice').css('display', 'none');
                $('.rewardBtn').addClass('btnLight');
                $('.rewardBtn').removeClass('btnDark');
            });
            {# 후원하기 - 입력값 삭제 X아이콘 표출 끝 #}


            {# 후원하기 - 버튼 활성화 시작 #}
            $('input[name=donate]').on('keyup', function (){
                if($.donateInputCheck()){
                    $('.widgetRewardArea button.rewardBtn').removeClass('btnLight');
                    $('.widgetRewardArea button.rewardBtn').addClass('btnDark');
                } else {
                    $('.widgetRewardArea button.rewardBtn').addClass('btnLight');
                    $('.widgetRewardArea button.rewardBtn').removeClass('btnDark');
                }
            });
            {# 후원하기 - 버튼 활성화 끝 #}

            {# 후원하기 화면 전환 시작 #}
            $('li #btnDonation').on('click', function() {
                $('input#donate').val('');
                $('div.widgetRewardWrap').removeClass('hide');
                $('div.widgetRewardComplete').addClass('hide');
                $('div.widgetRewardArea').removeClass('hide');
                $('div.widgetProfileWrap').addClass('hide');
                $('div.userMoreWrap').addClass('hide');
                $('div.dim').addClass('hide');
                $('img.downArrow').attr('src', '/assets/images/front/widget/icon/widgetDropDown.svg');
                $('img.downArrow').css('transform', 'rotate(360deg)');
            });
            {# 후원하기 화면 전환 끝 #}

            {# 후원하기 이벤트 시작 #}
            $('button#rewardBtn').on('click', function() {

                {# 후원금 입력 체크 #}
                if($('input[name=donate]').val() == '') {
                    $('input[name=donate]').focus();
                    return false;
                }

                var clientPoint = $('input[name=clientPoint]').val().replace(/,/g, "");
                if(Number($('input[name=donate]').val()) >= Number(clientPoint)) {
                    $('div.rewardInputArea').addClass('excess');
                    $('div.rewardInputArea input[name=donate]').css('color', '#ff4343');
                    return false;
                }

                if(window.inAjax != 'Y'){
                    window.inAjax = 'Y';
                } else {
                    alert(window.inAjaxMsg);
                    return false;
                }

                $.ajax({
                    url: "/index/ajax/addDonate",
                    type: 'post',
                    data: $('form#formDonate').serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if (response.error != 0) {
                            alert(response.message);
                            window.inAjax = 'N';
                            return false;
                        } else {
                            $('div.widgetRewardArea').addClass('hide');
                            $('div.widgetRewardComplete').removeClass('hide');

                            $('div.widgetRewardComplete div.rewardText p span#siteName').text(response.item.siteName);
                            if(response.item.tokenType == 1) {
                                $('div.widgetRewardComplete div.rewardText p span#donated').text(response.item.amount + " P.Point");
                            } else if(response.item.tokenType == 2) {
                            } else {
                            }
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status > 0) {
                            alert('서버처리중 오류가 발생하였습니다.');
                            window.inAjax = 'N';
                            return false;
                        }
                    }
                });
                return false;
            });
            {# 후원하기 이벤트 끝 #}

            {# 후원완료 - 확인 버튼 이벤트 시작 #}
            $('button#btnDonateConfirm').on('click', function () {
                $('div.widgetRewardWrap').addClass('hide');
                $('div.closeBtnWrap').addClass('hide');
                $('div.widgetProfileWrap').removeClass('hide');

                {# 회원정보 갱신 #}
                $.memberInfo(clientId);
            });
            {# 후원완료 - 확인 버튼 이벤트 끝 #}

            {# X아이콘 클릭시 창 닫기 시작 #}
            $('.closeBtn').on('click', function (){
                $('.widgetRewardArea').addClass('hide');
                $('.widgetRewardComplete').addClass('hide');
                $('.widgetProfileWrap').removeClass('hide');
                $('.arrowArea img').attr('src', '/assets/images/front/widget/icon/widgetDropDown.svg');
                $('.userMoreWrap').addClass('hide');
                $('.dim').addClass('hide');
            });
            {# X아이콘 클릭시 창 닫기 끝 #}


            {# SNS 로그인 화면 전환 시작 #}
            $('button#btnSnsLogin').on('click', function(){
                $('div.widgetPadding').addClass('hide');
                $('div.widgetSnsLoginWrap').removeClass('hide');
            });
            {# SNS 로그인 화면 전환 끝 #}


            {# 리워드알림 - 창 닫기 시작 #}
            $('#widgetRewardNotice .closeBtn2').on('click', function (){
               $('#widgetRewardNotice').addClass('hide');
            });
            $('div.noticeBtnWrap button.noticeBtnCancel').on('click', function (){
                $('#widgetRewardNotice').addClass('hide');
            });
            {# 리워드알림 - 창 닫기 끝 #}

            {# 리워드알림 - 받기 시작 #}
            $('#widgetRewardNotice button.noticeBtnActive').on('click', function (){
                if(window.inAjax != 'Y'){
                    window.inAjax = 'Y';
                } else {
                    alert(window.inAjaxMsg);
                    return false;
                }

                $.ajax({
                    url: "/index/ajax/rewardReceive",
                    type: 'post',
                    data: $('form#formReward').serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if (response.error != 0) {
                            alert(response.message);
                            window.inAjax = 'N';
                            return false;
                        } else {
                            $('div#widgetRewardNotice').addClass('hide');

                            var sign = "";
                            if(response.item.difference > 0) {
                                sign = '+';
                            } else if(response.item.difference < 0) {
                                sign = '-';
                            }
                            if(response.item.assetId == 1) {
                                $("dl dd#pPoint").text(response.item.point);
                                $("dl dd#pToday").text(sign + Math.abs(response.item.difference));

                            } else if(response.item.assetId == 2) {
                                $("dl dd#news").text(response.item.point);
                                $("dl dd#nToday").text(sign + Math.abs(response.item.difference));
                            } else {
                            }
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status > 0) {
                            alert('서버처리중 오류가 발생하였습니다.');
                            window.inAjax = 'N';
                            return false;
                        }
                    }
                });
                return false;
            });
            {# 리워드알림 - 받기 끝 #}


            {# 공유하기 - 화면 전환 시작 #}
            $('li a#btnShare').on('click', function() {
                $('#widgetShareWrap').removeClass('hide');
                $('#widgetProfileWrap').addClass('hide');

                $('div.userMoreWrap').addClass('hide');
                $('div.dim').addClass('hide');
                $('img.downArrow').attr('src', '/assets/images/front/widget/icon/widgetDropDown.svg');
                $('img.downArrow').css('transform', 'rotate(360deg)');
            });
            {# 공유하기 - 화면 전환 끝 #}

            {# 공유하기 시작 #}
            $("div.shareImg ul.snsList li.btnShare").on('click', function(){
                var snsCode = $(this).attr('id');
                var cUrl = $('input#copyShareUrl').val();
                switch(snsCode){
                    case"btnTwitter":
                        cUrl = 'https://twitter.com/intent/tweet?text=페이지제목:&url='+cUrl;
                        break;
                    case"btnTelegram":
                        cUrl = 'https://telegram.me/share/url?url='+cUrl;
                        break;
                    case"btnFacebook":
                        cUrl = 'http://www.facebook.com/sharer/sharer.php?u='+cUrl;
                        break;
                    case"btnNaver":
                        cUrl= "https://share.naver.com/web/shareView?url=" + cUrl + "&title=페이지제목";
                        break;
                    case"btnLinkedIn":
                        cUrl = 'https://www.linkedin.com/shareArticle?mini=true&amp;url='+cUrl;
                        break;
                }
                window.open(cUrl);
                return false;
            });
            {# 공유하기 끝 #}

            $('#urlToast').hide();

            {# 링크 복사하기 시작 #}
            $('#btnUrlCopy').on('click', function ()  {
                var pageUrl = $('input#copyShareUrl').val();

                {# a의 텍스트값을 가져옴 #}
                {#숨겨진 input박스 value값으로 nowLink 변수 넣어줌.#}
                $('#copyLinkHidden').val(pageUrl);
                {#input박스 value를 선택#}
                $('#copyLinkHidden').select();
                {#Use try & catch for unsupported browser#}
                try {
                    {#The important part (copy selected text)#}
                    var successful = document.execCommand('copy');

                } catch (err) { alert('이 브라우저는 지원하지 않습니다.') }
                $('input#copyLinkHidden').addClass('hide');
                $('#urlToast').fadeIn(800).delay(800).fadeOut(800);
            });
            {# 링크 복사하기 끝 #}

            {# 로그아웃 시작 #}
            $('li a#logOut').on('click', function() {

                $.ajax({
                    url: "/index/ajax/logout",
                    type: 'post',
                    data: {
                        widgetCode : $('form#formInfo input#code').val(),
                        purl : $('form#formInfo input#purl').val(),
                        clientId : clientId
                    },
                    dataType: 'json',
                    success: function (response) {
                        {#
                        if (response.error != 0) {
                            alert('로그인 세션이 만료되어 로그아웃되었습니다.');
                        }
                        #}

                        {# 저장된 이메일 있으면 가져오기 #}
                        var savedEmail = localStorage.getItem('publishLinkEmailSaveCheck');
                        if(savedEmail != '' && savedEmail != null && savedEmail != undefined) {
                            $('div.idArea input#widgetId').val(savedEmail);
                            $('span.saveEmailInner img.checkBox').attr('src', '/assets/images/front/widget/icon/checkBoxActive.svg');
                            $("input#saveEmail").attr("checked", true);
                        } else {
                            $('span.saveEmailInner img.checkBox').attr('src', '/assets/images/front/widget/icon/checkBoxBasic.svg');
                            $("input#saveEmail").attr("checked", false);
                        }
                        {# 화면 show/hide #}
                        $('div.widgetPadding').addClass('hide');
                        $('div.widgetIntro').removeClass('hide');
                        {# 더보기 영역 hide #}
                        if($('div.userMoreWrap').hasClass('hide') == false){
                            $('div.userMoreWrap').addClass('hide');
                            $('div.userMoreWrap').siblings('.dim').addClass('hide');
                        }
                        $('div.widgetProfileWrap.widgetPadding').children('.arrowArea').children('.downArrow').attr('src', '/assets/images/front/widget/icon/widgetDropDown.svg');
                        $('div.widgetProfileWrap.widgetPadding').children('.arrowArea').children('.downArrow').css('transform', 'rotate(360deg)');

                    },
                    error: function (xhr) {
                        if (xhr.status > 0) {
                            alert('서버처리중 오류가 발생하였습니다.');
                            return false;
                        }
                    }
                });
                return false;
            });
            {# 로그아웃 끝 #}

            {# X버튼 클릭 이벤트 시작 #}
            $('div.closeBtn').on('click', function() {
                $('div.widgetPadding').addClass('hide');
                $('div.widgetRewardWrap').addClass('hide');

                $.memberInfo(clientId);
            });
            {# X버튼 클릭 이벤트 끝#}


            {# 비회원 후원하기 페이지로 이동 시작 #}
            $('#introBtn').on('click', function (){
                window.open('{{ protocol }}://{{ serviceUrl }}/index/nonmemberSponsor');

            });
            {# 비회원 후원하기 페이지로 이동 끝 #}


            {# 로그인 실패시 알람 toast X버튼 시작 #}
            $('#widgetToastClose').on('click', function () {
                $("#widgetToast").hide();
            });
            {# 로그인 실패시 알람 toast X버튼 끝 #}


        });
    });
</script>
{% endblock %}
    
{% block content %}
    {#
        @fixme: admin(언론사) 페이지 생긴 후에 수정 필요
     #}
    <div id="widgetWrap" class="widgetWrap verticalWrap {% if clientSession != 'Y' %}hide{% endif %}">
        <div class="inner widgetInner">
            {# 위젯 세로형 #}
            <div class="verticalType">
                {# 위젯 #}
                <div class="widgetArea">
                    {# 초기화면 #}
                    <div id="widgetIntro" class="widgetIntro {% if clientId >= 1 %}hide{% endif %}">
                        <div class="introTitle">
                            <p class="subTitle">뉴스 기사 읽고 혜택 받자!</p>
                            <p class="mainTitle">
                                <span>지금 로그인 하고</span>
                                <span>리워드 적립받으세요!</span>
                            </p>
                        </div>
                        <img src="{{ staticUrl }}/assets/images/front/widget/img/widgetIntro.svg" alt="" class="introImg">
                        <div class="introBtnArea">
                            <button type="button" id="introBtn" class="introBtn btnLight2">
                                <span>후원하기</span>
                            </button>
                            <button type="button" class="introBtn btnDark2">
                                <span>로그인</span>
                            </button>
                        </div>
                    </div>

                    {# 로그인 #}
                    <div id="widgetLoginWrap" class="widgetLoginWrap widgetPadding hide">
                        {# 로고 #}
                        <div class="logoArea">
                            <img src="{{ staticUrl }}/assets/images/front/widget/common/logo/widgetLogoV3.svg" alt="퍼블리시 링크">
                        </div>
                        <div class="loginArea ">
                            <form id="formLogin" name="formLogin">
                                <input type="hidden" name="widgetCode" value="{{ widget.getCode() }}" />
                                <input type="hidden" id="parentUrl" name="parentUrl" value="" />
                                <input type="hidden" name="rdUrl" value="{{ rdUrl }}">
                                <fieldset>
                                    <legend class="ir_so">위젯 로그인</legend>
                                    <div class="login">
                                        <div class="idArea">
                                            <input type="email" id="widgetId" name="id" placeholder="이메일" autocomplete="off" value="" spellcheck="false">
                                            <label for="widgetId" class="ir_so">이메일</label>
                                        </div>
                                        <div class="pwArea">
                                            <input type="password" id="widgetPw" name="pw" placeholder="비밀번호" autocomplete="off" value="">
                                            <label for="widgetPw" class="ir_so">비밀번호</label>
                                        </div>
                                    </div>
                                    <div id="widgetToast" class="widgetToast">
                                        {# <span class="toastRed"></span>#}
                                        <span class="toast" id="loginNotice">회원정보가 일치하지 않습니다.</span>
                                        <img src="{{ staticUrl }}/assets/images/front/widget/icon/widgetToastCloseBtn.svg" alt="" id="widgetToastClose" class="toastClose">
                                    </div>
{#                                    <p class="excessNotice" id="loginNotice">회원정보가 일치하지 않습니다.</p>#}
                                    <div class="saveEmailArea">
                                        <span class="saveEmailInner">
                                            <img src="{{ staticUrl }}/assets/images/front/widget/icon/checkBoxBasic.svg" alt="" class="checkBox">
                                            <input type="checkbox" id="saveEmail" name="email" value="">
                                            <label for="saveEmail">이메일 저장</label>
                                        </span>
                                    </div>
                                    <div class="widgetBtn loginBtnArea">
                                        <button type="button" class="loginBtn btnStroke" id="btnSnsLogin">
                                            <span class="loginTxt">간편 로그인</span>
                                        </button>
                                        <button class="loginBtn btnLight" id="btnLogin" >
                                            <span class="loginTxt">로그인</span>
                                        </button>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                        <div class="findArea">
                            <div class="findAndJoin">
                                <a href="{{ protocol }}://{{ serviceUrl }}/common/findInfo" class="find id" target="_blank">아이디찾기</a>
                                <a href="{{ protocol }}://{{ serviceUrl }}/common/findPassword" class="find pw" target="_blank">비밀번호찾기</a>
                                <a href="{{ protocol }}://{{ serviceUrl }}/common/join" target="_blank">회원가입</a>
                            </div>
                        </div>
                        <div id="widgetToast" class="widgetToast hidden">
                            {# <span class="toastRed"></span>#}
                            <span class="toast toastEmail">등록되지 않은 이메일입니다.</span>
                            <span class="toast toastPassword hidden">비밀번호를 확인하시기 바랍니다.</span>
                            <img src="{{ staticUrl }}/assets/images/front/widget/icon/widgetToastCloseBtn.svg" alt="" id="widgetToastClose">
                        </div>
                    </div>

                    {# 간편 로그인 #}
                    <div id="widgetSnsLoginWrap" class="widgetSnsLoginWrap widgetShareWrap widgetPadding hide">
                        <div class="snsLoginTitle shareTitle">
                            <p class="title"><span>간편 로그인 수단을</span> 선택해주세요.</p>
                        </div>
                        <div class="closeBtn ">
                            <img src="{{ staticUrl }}/assets/images/front/widget/icon/widgetCloseBtn.svg" alt="위젯 로그인 닫기" class="closeBlack">
                        </div>
                        <div class="loginImg shareImg">
                            {# google #}
                            <input type="hidden" id="sGoogleSimpleApiKey" value="AIzaSyCyeHDPbjCtbjLT7718OOQmHarWo3kOLRE">
                            <input type="hidden" id="sGoogleClientId" value="993658457227-2mr318j8rbuml7ab7tiktnhrcohpfv0c.apps.googleusercontent.com">
                            <input type="hidden" id="sGoogleRedirectUrl" value="{{ protocol }}://{{ serviceUrl }}/common/authGoogle">

                            <a href="#" id="btnLoginGoogle"><img src="{{ staticUrl }}/assets/images/front/widget/icon/google.svg" alt="구글 로그인"></a>
                            <a href="#" id="btnLoginFacebook"><img src="{{ staticUrl }}/assets/images/front/widget/icon/facebook.svg" alt="페이스북 로그인"></a>
                            <a href="#" id="btnLoginTwitter"><img src="{{ staticUrl }}/assets/images/front/widget/icon/twitter.svg" alt="트위터 로그인"></a>
                            <a href="#" id="btnLoginKakao"><img src="{{ staticUrl }}/assets/images/front/widget/icon/kakaotalk.svg" alt="카카오톡 로그인"></a>
                            <a href="#" id="btnLoginNaver"><img src="{{ staticUrl }}/assets/images/front/widget/icon/naver.svg" alt="네이버 로그인"></a>
                        </div>
                    </div>

                    {# 리워드알림 #}
                    <div id="widgetRewardNotice" class="widgetRewardNotice widgetPadding hide">
                        <div class="closeBtn2">
                            <img src="{{ staticUrl }}/assets/images/front/widget/icon/closeWhiteBtn.svg" alt="위젯 로그인 닫기">
                        </div>
                        <div class="noticeTitle">
                            <p class="title">리워드 알림</p>
                        </div>
                        <form name="formReward" id="formReward">
                            <input type="hidden" name="widgetCode" value="{{ widgetCode }}">
                            <input type="hidden" name="clientId" value="{{ clientId }}">
                            <input type="hidden" name="activityId" value="">
                            <input type="hidden" name="rewardId" value="">
                            <p class="noticeText">적립할 수 있는 리워드가 있습니다. 리워드를 받으시겠습니까?</p>
                            <div class="noticeBtnWrap">
                                <button type="button" class="noticeBtnCancel">
                                    <span>닫기</span>
                                </button>
                                <button type="button" class="noticeBtnActive">
                                    <span>받기</span>
                                </button>
                            </div>
                        </form>
                        <img src="{{ staticUrl }}/assets/images/front/widget/img/widgetLMan.svg" alt="" class="rewardImg">
                    </div>

                    {# 메인 #}
                    <div id="widgetProfileWrap" class="widgetProfileWrap widgetPadding {% if clientSession != 'Y' %}hide{% endif %}">
                        {# 더보기버튼 #}
                        <div class="arrowArea">
                            <img src="{{ staticUrl }}/assets/images/front/widget/icon/widgetDropDown.svg" alt="더보기" class="downArrow">
                        </div>
                        <div class="userMoreWrap hide">
                            <div class="userMoreArea">
                                <ul class="userMore">
                                    <li>
                                        <a href="{{ protocol }}://{{ serviceUrl }}/user/index" target="_blank" id="myPage" class="myPage">마이페이지</a>
                                    </li>
                                    <li>
                                        <a href="#" id="btnDonation" class="btnDonation">언론사후원</a>
                                    </li>
                                    <li>
                                        <a href="#" id="btnShare" class="btnShare">기사공유</a>
                                    </li>
                                    <li>
                                        <a href="#" id="logOut" class="logOut">로그아웃</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        {# dim #}
                        <div class="dim hide"></div>
                        {# 컨텐츠 #}
                        <div class="profileInner">
                            {# 사용자 프로필 #}
                            <div class="profileArea">
                                <div class="profileImgWrap">
                                    {# ---------- fixme: 기본 프로필 > 사용자 닉네임? 2자리 ---------- #}
                                    {% if clientSession == 'Y' and clientInstance.getProfileId() >= 1 %}
                                        {# 프로필 있는 경우 #}
                                        <div class="profileImgArea ">
                                            <img src="{{  clientInstance.getProfile() }}" alt="" class="profileImg">
                                        </div>
                                    {% else %}
                                        <div class="profileBasicArea ">
                                            <strong class="basicName">{% if clientSession == 'Y' %}{{ clientInstance.getInformationInstance().getProfileName() }}{% endif %}</strong>
                                        </div>
                                    {% endif %}
                                </div>
                                <dl>
                                    <dt id="userName" class="userName">{% if clientSession == 'Y' %}{{ clientInstance.getInformationInstance().getName() }}{% endif %}</dt>
                                    <dd id="userEmail" class="userEmail">{% if clientSession == 'Y' %}{{ clientInstance.getEmail() }}{% endif %}</dd>
                                </dl>
                            </div>
                            {# 포인트 영역 #}
                            <div class="moreNPointWrap">
                                <div class="profileTableWrap">
                                    <div id="tbAsset" class="tbAsset">
                                        <dl>
                                            <dt class="news">P.Point</dt>
                                            <dd id="pPoint" class="ellipsis">{% if clientSession == 'Y' %}{{ ppoint }}{% endif %}</dd>
                                            {% if clientSession == 'Y' %}
                                                {% set psign = '' %}
                                                {% if pDifference > 0 %}
                                                    {% set psign = '+' %}
                                                {% elseif pDifference < 0 %}
                                                    {% set psign = '-' %}
                                                {% endif %}
                                            {% endif %}
                                            <dd id="pToday" class="today">{% if clientSession == 'Y' %}{{ psign }}{{ pDifference | abs }}{% endif %}</dd>
                                        </dl>
                                        <dl>
                                            <dt class="news">NEWS</dt>
                                            <dd id="news" class="ellipsis">{% if clientSession == 'Y' %}{{ news }}{% endif %}</dd>
                                            {% if clientSession == 'Y' %}
                                                {% set nsign = '' %}
                                                {% if nDifference > 0 %}
                                                    {% set nsign = '+' %}
                                                {% elseif nDifference < 0 %}
                                                    {% set nsign = '-' %}
                                                {% endif %}
                                            {% endif %}
                                            <dd id="nToday" class="today">{% if clientSession == 'Y' %}{{ nsign }}{{ nDifference | abs }}{% endif %}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {# 공유하기 #}
                    <div id="widgetShareWrap" class="widgetShareWrap widgetPadding hide">
                        <div class="shareTitle">
                            <p class="title">뉴스기사를<br>SNS에 공유해보세요.</p>
                        </div>
                        <div class="closeBtn ">
                            <img src="{{ staticUrl }}/assets/images/front/widget/icon/widgetCloseBtn.svg" alt="위젯 로그인 닫기" class="closeBlack">
                        </div>
                        <div class="shareImg">
                            <input type="hidden" name="linkShareUrl" id="copyShareUrl" value="" />
                            <input type="hidden" name="clientReferralCode" id="referralCode" value="" />
                            <ul class="snsList">
                                <li id="btnFacebook" class="btnShare">
                                    <img src="{{ staticUrl }}/assets/images/front/widget/icon/facebook.svg" alt="페이스북으로 공유하기">
                                </li>
                                <li id="btnTwitter" class="btnShare">
                                    <img src="{{ staticUrl }}/assets/images/front/widget/icon/twitter.svg" alt="트위터로 공유하기">
                                </li>
                                <li id="btnKakao">
                                    <img src="{{ staticUrl }}/assets/images/front/widget/icon/kakaotalk.svg" alt="카카오톡으로 공유하기">
                                </li>
                                <li id="btnNaver" class="btnShare">
                                    <img src="{{ staticUrl }}/assets/images/front/widget/icon/blog.svg" alt="네이버블로그로 공유하기">
                                </li>
                                <li id="btnTelegram" class="btnShare">
                                    <img src="{{ staticUrl }}/assets/images/front/widget/icon/telegram.svg" alt="텔레그램으로 공유하기">
                                </li>
                                <li id="btnLinkedIn" class="btnShare">
                                    <img src="{{ staticUrl }}/assets/images/front/widget/icon/linkedIn.svg" alt="링크드인으로 공유하기">
                                </li>
                                <li id="btnUrlCopy">
                                    <img src="{{ staticUrl }}/assets/images/front/widget/icon/url.svg" alt="뉴스기사 경로 복사하기">
                                    <input type="text" id="copyLinkHidden" value=""/>
                                </li>
                            </ul>
                            <div id="urlToast" class="urlToast widgetToast">
                                <span class="toast">URL이 복사되었습니다.</span>
                                <img src="{{ staticUrl }}/assets/images/front/widget/icon/widgetToastCloseBtn.svg" alt="" id="urlToastClose" class="toastClose">
                            </div>
                        </div>
                    </div>

                    {# 후원하기 & 완료 #}
                    <div id="widgetRewardWrap" class="widgetRewardWrap hide">
                        {# 후원하기 #}
                        <div id="widgetRewardArea" class="widgetRewardArea widgetPadding ">
                            <div class="closeBtn ">
                                <img src="{{ staticUrl }}/assets/images/front/widget/icon/widgetCloseBtn.svg" alt="위젯 로그인 닫기" class="closeBlack">
                            </div>
                            <div class="rewardTitle">
                                <p class="title">후원하기</p>
                            </div>
                            <form class="rewardForm" class="rewardForm" id="formDonate">
                                <input type="hidden" name="widgetCode" value="{{ widgetCode }}">
                                <input type="hidden" name="clientId" value="{{ clientId }}">
                                <input type="hidden" name="unit" value="ppoint">
                                <input type="hidden" name="clientPoint" value="">
                                <fieldset>
                                    <div class="rewardInputArea">
                                        <input type="number" name="donate" placeholder="후원금 입력" id="donate" autocomplete="off">
                                        <label class="ir_so">후원금 입력</label>
                                        <img src="{{ staticUrl }}/assets/images/front/widget/icon/textClose.svg" alt="후원금 금액 지우기" id="removeTxt" class="removeTxt hide">
                                    </div>
                                    {# ----------fixme:[to. zuri] 금액 초과시 widgetRewardWrap에 .excess가 붙습니다---------- #}
                                    <p class="excessNotice">후원 가능금액을 초과하였습니다.</p>
                                    <p class="rewardPoint">후원가능 P.Point : <span class="bold" id="pDonation">29,197,42459786</span>
                                    </p>
                                </fieldset>
                            </form>
                            <button type="button" id="rewardBtn" class="rewardBtn btnLight">
                                <span>후원</span>
                            </button>
                        </div>
                        {# 후원완료 #}
                        <div id="widgetRewardComplete" class="widgetRewardComplete widgetPadding ">
                            <div class="closeBtn ">
                                <img src="{{ staticUrl }}/assets/images/front/widget/icon/widgetCloseBtn.svg" alt="위젯 로그인 닫기" class="closeBlack">
                            </div>
                            <div class="rewardCompleteTitle">
                                <img src="{{ staticUrl }}/assets/images/front/widget/icon/circleActive.svg" alt="">
                                <strong>후원완료</strong>
                            </div>
                            <div class="rewardText">
                                <p><span class="rewardMedia" id="siteName">토큰포스트</span>에<br>
                                    <span class="bold" id="donated">1154 P.Point</span>를 후원하였습니다.
                                </p>
                            </div>
                            <button type="button" class="rewardCompleteBtn btnDark" id="btnDonateConfirm">
                                <span>확인</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    {# 상위 정보 #}
    <form id="formInfo" class="formInfo" action="" method="post">
        <input type="hidden" id="purl" name="purl" value="" />
        <input type="hidden" id="code" name="code" value="{{ widgetCode }}" />
    </form>
    {# 상위 정보 #}

    <div id="fb-root"></div>
    <div id="naver_id_login"></div>

    {# ===== SNS 연동 ===== #}
    <script src="https://apis.google.com/js/client:plusone.js" type="application/javascript"></script>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>
    {#<script src="//developers.kakao.com/sdk/js/kakao.js"></script>#}
    <script>
        function statusChangeCallback(response) {
            if (response.status === "connected") {
                ProceedAuth(response.authResponse.accessToken)
            }
        }

        window.fbAsyncInit = function() {
            FB.init({
                appId      : '593057295186118',
                cookie     : true,
                xfbml      : true,
                version    : 'v12.0'
            });

        };

        (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));


        function checkLoginState() {
            FB.login(function(response) {
                statusChangeCallback(response)
            }, { scope: "email,public_profile" });
        }

        function ProceedAuth(token) {
            $.ajax({
                url: "{{ protocol }}://{{ serviceUrl }}/common/ajax/authFacebook",
                data: {
                    "t" : token,
                    "state" : 2
                },
                success: function(oResult) {
                    var response = JSON.parse(oResult);
                    var url = "";
                    if (response.error == 0) {
                        if(response.type == 'new') {
                            {# 신규 가입 #}
                            {# 약관동의 페이지로 이동 #}
                            url = '/common/join';
                            window.open(url);
                        } else if (response.type == 'exist') {
                            url = '/user/index';
                            window.open(url);
                        } else {

                        }
                    }
                }, error: function(oResult) {
                }
            });
        }

    </script>
    <script xmlns="http://www.w3.org/1999/html">
        require(['base'], function () {
            require(['popup', 'kakao'], function (popup) {
                {# SNS 연동 로그인 #}

                {# 사용할 앱의 JavaScript 키를 설정해 주세요. #}
                Kakao.init('c4dc6ba40b08656b96583db45eafc1a5');
                {# 카카오링크 버튼을 생성합니다. 처음 한번만 호출하면 됩니다. #}
                Kakao.isInitialized();

                {# Google 시작 #}
                $('a#btnLoginGoogle').on('click', function () {
                    var oApiKey = document.getElementById("sGoogleSimpleApiKey");
                    var oClientId = document.getElementById("sGoogleClientId");
                    var oClientRedirectUrl = document.getElementById("sGoogleRedirectUrl");

                    if (oApiKey == null) {
                        alert("need input api key");
                        return false;
                    } else if (oClientId == null) {
                        alert("need input client id");
                        return false;
                    } else if (oClientRedirectUrl == null) {
                        alert("need input redirect url");
                        return false;
                    }

                    gapi.client.setApiKey(oApiKey.key);
                    gapi.client.load("plus", "v1", function() {});

                    var width = 450;
                    var height = 380;
                    var url = "https://accounts.google.com/o/oauth2/auth?";

                    {# 웹 = 1, 위젯 = 2 #}
                    var param = 2;

                    url += "scope=email profile";
                    url += "&state=" + param ;
                    url += "&redirect_uri=" + oClientRedirectUrl.value;
                    url += "&response_type=code";
                    url += "&client_id=" + oClientId.value;
                    var id = "googleLogin";
                    window.open(url, id, "width=" + width + ", height=" + height + ", scrollbars=yes");
                });
                {# Google 끝 #}

                {# Facebook 시작 #}
                $('a#btnLoginFacebook').on('click', function() {
                    checkLoginState();
                });
                {# Facebook 끝 #}

                {# Naver 시작 #}
                $('a#btnLoginNaver').on('click', function() {
                    var apiURL = "https://nid.naver.com/oauth2.0/authorize?response_type=code&client_id=eb90otHBjoBZsdjE09dp&redirect_uri=https%3A%2F%2Fd4.elmindev1.com%2Fcommon%2FauthNaver&state=2";
                    window.open(apiURL);
                });
                {# Naver 끝 #}

                {# Kakao 시작 #}
                $('a#btnLoginKakao').on('click', function () {
                    var apiURL = "https://kauth.kakao.com/oauth/authorize?response_type=code&client_id=ed08b75c9d6ecf7211e066f6dfab5dc1&redirect_uri=https%3A%2F%2Fd4.elmindev1.com%2Fcommon%2FauthKakao&state=2";
                    window.open(apiURL);
                });
                {# Kakao 끝 #}

                {# Twitter 시작 #}
                $('a#btnLoginTwitter').on('click', function () {
                    var apiURL = "{{ protocol }}://{{ serviceUrl }}/index/twitterOauth?state=2";
                    window.open(apiURL);
                });
                {# Twitter 끝 #}

                {# 카카오톡 링크 공유하기 시작 #}
                $('li#btnKakao').on('click', function() {
                    sendLink();
                });
                function sendLink() {
                    Kakao.Link.sendDefault({
                        objectType: 'text',
                        text:
                            '퍼블리시 링크 기사 공유하기',
                        link: {
                            mobileWebUrl: $('input#copyShareUrl').val(),
                            webUrl: $('input#copyShareUrl').val(),
                        },
                    })
                }
                {# 카카오톡 링크 공유하기 끝 #}
            });
        });
    </script>

{% endblock %}
