{% extends 'base.v3.volt' %}


{% block embedStyle %}
    <link rel="stylesheet" type="text/css" href="{{ staticUrl }}/assets/css/widget/base.v3.css?r={{ cssRevision }}"/>
    {#    <link rel="stylesheet" type="text/css" href="{{ staticUrl }}/assets/css/widget/index.v3.css?r={{ cssRevision }}"/>#}
    <link rel="stylesheet" type="text/css" href="{{ staticUrl }}/assets/css/widget/horizontal.v3.css?r={{ cssRevision }}"/>
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
        var testInterval;
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
                    purl = $('form#formInfo input[name=purl]').val();

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
                                return false;
                            } else {
                                if(response.display == 'Y') {
                                    $('div.widgetWrap').removeClass('hide');
                                    $('form#formLogin input#parentUrl').val(purl);

                                    {# 회원정보 갱신 #}
                                    {% if clientId >= 1 %}
                                    $.memberInfo(clientId);
                                    {% endif %}
                                }
                                return false;
                            }
                        },
                        error: function (xhr) {
                            if (xhr.status > 0) {
                                alert('서버 처리 중 오류가 발생하였습니다.');
                                window.location.reload();
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
                                if (response.error == 1) {
                                    if(response.message.indexOf("code:1") == -1) {
                                        alert(response.message);
                                        return;
                                    }
                                }
                                $('#widgetToast').fadeIn(800).delay(800).fadeOut(800);
                                return;
                            } else {
                                if(response.clientId > 0) {
                                    clientId = response.clientId;
                                    {# 회원정보 갱신 #}
                                    {% if clientId >= 1 %}
                                    $.memberInfo(clientId);
                                    {% endif %}
                                    $('.widgetInner').removeClass('on');
                                } else {
                                    $('.widgetInner').addClass('on');
                                }
                            }
                        },
                        error: function (xhr) {
                            if (xhr.status > 0) {
                                alert('서버 처리 중 오류가 발생하였습니다.');
                                window.location.reload();
                                return false;
                            }
                        }
                    });
                    return false;
                });
                {# 로그인 끝 #}

                {# 로그인 후 정보 가져오기 시작 #}
                $.memberInfo = function(memberId) {
                    if(memberId == 0 || memberId != clientId || clientId == '' || memberId == '' || clientId == 0) {
                        $('div.widgetPadding2').addClass('hide');
                        $('div.widgetIntro').removeClass('hide');
                        return false;
                    }

                    window.inAjax = 'Y';

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
                                return false;
                            } else {
                                $("div.widgetPadding2").addClass('hide');
                                $("div.widgetIntro").addClass('hide');
                                $("div.widgetProfileWrap").removeClass('hide');
                                $('.horizonClose').addClass('hide');

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
                                $('dl dd#pPoint span.ellipsis').text(response.item.pPoint);
                                $('dl dd#news span.ellipsis').text(response.item.news);
                                {# 하루 적립내역 #}
                                var nsign = '';
                                var nDifference = parseFloat(response.item.nDifference.replace(',', ''));
                                if(nDifference > 0) {
                                    nsign = '+';
                                } else if(nDifference < 0) {
                                    nsign = '-';
                                }
                                $('dl dd#nToday span#nDifference').text(nsign + Math.abs(nDifference));

                                var psign = '';
                                var pDifference = parseFloat(response.item.pDifference.replace(',', ''));
                                if(pDifference > 0) {
                                    psign = '+';
                                } else if(pDifference < 0) {
                                    psign = '-';
                                    psign = '-';
                                }
                                $('dl dd#pToday span#pDifference').text(psign + Math.abs(pDifference));

                                {# 후원하기 부분에 ppoint 표출 #}
                                $('p.rewardPoint span#pDonation').text(response.item.pPoint);
                                $('form#formDonate input[name=clientPoint]').val(response.item.pPoint);
                                $('form#formDonate input[name=clientId]').val(memberId);

                                {# 리워드알림 - 토스트 알림 시작 #}
                                if(response.item.toastDisplay == 'Y') {
                                    if($('#widgetRewardNotice').hasClass('hide')) {
                                        $('#widgetRewardNotice').removeClass('hide');
                                        $('#widgetRewardNotice').css('top', '150px');
                                        $('#widgetRewardNotice').animate({top:0}, 0, 'linear');
                                        if($('#widgetRewardNotice').removeClass('hide')){
                                            $('.widgetInner').removeClass('on');
                                        }
                                        $('form#formReward input[name=activityId]').val(response.item.activityId);
                                        $('form#formReward input[name=clientId]').val(memberId);
                                        $('form#formReward input[name=rewardId]').val(response.item.rewardId);
                                    }
                                } else {
                                    $('.widgetRewardNotice').addClass('hide');
                                }
                                {# 리워드알림 - 토스트 알림 끝 #}

                                {# 기사 공유하기 #}
                                purl = $('#formInfo input#purl').val();
                                $('input#referralCode').val(response.item.referralCode);
                                if(purl.indexOf('?wg_ref=') != -1) {
                                    purl = purl.split('?wg_ref=')[0];
                                }
                                $('div.shareImg input#copyShareUrl').val(purl + '?wg_ref=' + response.item.referralCode);
                            }
                        },
                        error: function (xhr) {
                            if (xhr.status > 0) {
                                alert('서버 처리 중 오류가 발생하였습니다.');
                                window.location.reload();
                                return false;
                            }
                        }
                    });
                    return false;
                };
                {# 로그인 후 정보 가져오기 끝 #}

                {# document ready 시작 #}
                $(document).ready(function() {
                    if(!$('#widgetIntro').hasClass('hide')){
                        $('.widgetInner').removeClass('on');
                    }
                    if(!$('#widgetLoginWrap').hasClass('hide')){
                        $('.widgetInner').addClass('on');
                    }
                    if(!$('#widgetSnsLoginWrap').hasClass('hide')){
                        $('.widgetInner').removeClass('on');
                    }
                    if(!$('#widgetRewardNotice').hasClass('hide')){
                        $('.widgetInner').removeClass('on');
                    }
                    if(!$('#widgetProfileWrap').hasClass('hide')){
                        $('.widgetInner').removeClass('on');
                    }
                    if(!$('#widgetShareWrap').hasClass('hide')){
                        $('.widgetInner').removeClass('on');
                    }
                    if(!$('.widgetRewardArea').hasClass('hide')){
                        $('.widgetInner').addClass('on');
                    }
                    if(!$('.widgetRewardComplete').hasClass('hide')){
                        $('.widgetInner').removeClass('on');
                    }

                    {# 로그인 상태인지 체크 #}
                    if(clientId == 0) {
//                        testInterval = setInterval(function() {
//                            $.ajax({
//                                url: "/index/ajax/loginSessionCheck",
//                                type: 'post',
//                                data: {
//                                    widgetCode : $('form#formInfo input#code').val(),
//                                    purl : $('form#formInfo input#purl').val()
//                                },
//                                dataType: 'json',
//                                success: function (response) {
//                                    if (response.error != 0) {
//                                        alert(response.message);
//                                        return false;
//                                    } else {
//                                        if(response.id >= 1) {
//                                            clientId = response.id;
//                                            clearTimeout(testInterval);
//                                            window.location.reload();
//                                        }
//                                    }
//                                },
//                                error: function (xhr) {
//                                    if (xhr.status > 0) {
//                                        alert('서버 처리 중 오류가 발생하였습니다.');
//                                        window.location.reload();
//                                        return false;
//                                    }
//                                }
//                            });
//                            return false;
//                        }, 3000);
                    }

                    {# 저장된 이메일 있으면 가져오기 #}
                    var savedEmail = localStorage.getItem('publishLinkEmailSaveCheck');
                    if(savedEmail != '' && savedEmail != null && savedEmail != undefined) {
                        $('div.idArea input#widgetId').val(savedEmail);
                        $("input#saveEmail").attr("checked", true);
                        $('div.saveEmailArea img.checkBox').attr('src', '/assets/images/front/widget/icon/checkBoxActive.svg');
                    } else {
                        $('div.saveEmailArea img.checkBox').attr('src', '/assets/images/front/widget/icon/checkBoxBasic.svg');
                        $("input#saveEmail").attr("checked", false);
                    }
                });
                {# document ready 끝 #}

                {# 초기화면 - 로그인 화면 이동 시작 #}
                $('#loginBtn').on('click', function (){
                    $('#widgetIntro').addClass('hide');
                    $('#widgetLoginWrap').removeClass('hide');
                    $('.widgetInner').addClass('on');
                });
                {# 초기화면 - 로그인 화면 이동 끝 #}

                if(!$('#widgetLoginWrap').hasClass('hide')){
                    $('.widgetInner').addClass('on');
                } else if($('#widgetLoginWrap').hasClass('hide')) {
                    $('.widgetInner').removeClass('on');
                }

                {# 로그인 실패시 알람 toast X버튼 시작 #}
                $('.toastClose').on('click', function () {
                    $("#widgetToast, .urlToast").hide();
                });

                // $('#urlToastClose').on('')
                {# 로그인 실패시 알람 toast X버튼 끝 #}


                {# 로그인 - 이메일 저장 체크박스 시작 #}
                {# 가로형 #}
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
                {# 프로필 - 더보기 버튼 끝 #}

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
                        $('#donate').css('border-bottom', '1px solid #FF4343');
                        return false;
                    } else {
                        $('.excessNotice').css('display', 'none');
                    }
                    return true;
                };

                {# 후원하기 - 입력값 삭제 X아이콘 표출 시작 #}
                let donate = $('#donate');
                donate.on('keyup', function (){
                    $(this).siblings($('#removeTxt').removeClass('hide'));
                    $(this).css('border-bottom', '1px solid #5D5FEF');
                });
                $('#removeTxt').on('click', function (){
                    donate.css('color', '#5D5FEF');
                    $('#widgetWrap .horizonType .widgetRewardWrap .rewardInputArea.excess input').css('border-bottom', '1px solid #5D5FEF');
                    $('.excessNotice').css('display', 'none');
                    donate.val("");
                    $('#removeTxt').addClass('hide');
                    $('.rewardBtn').addClass('btnLight');
                    $('.rewardBtn').removeClass('btnDark');
                });
                {# 후원하기 - 입력값 삭제 X아이콘 표출 끝 #}

                {# 후원하기 - 버튼 활성화 시작 #}
                $('#donate').on('keyup', function (){
                    if($.donateInputCheck()){
                        $('.widgetRewardArea .rewardBtn').removeClass('btnLight');
                        $('.widgetRewardArea .rewardBtn').addClass('btnDark');
                    } else {
                        $('.widgetRewardArea .rewardBtn').addClass('btnLight');
                        $('.widgetRewardArea .rewardBtn').removeClass('btnDark');
                    }
                });
                {# 후원하기 - 버튼 활성화 끝 #}

                {# 후원하기 화면 전환 시작 #}
                $('li a#btnDonation').on('click', function() {
                    $('input#donate').val('');

                    $('div.widgetRewardWrap').removeClass('hide');
                    $('div.widgetRewardArea').removeClass('hide');
                    $('div.widgetRewardComplete.widgetPadding2').addClass('hide');

                    $('div.userMoreWrap').addClass('hide');

                    $('div.dim').addClass('hide');
                    $('div.widgetProfileWrap').addClass('hide');
                    $('img.downArrow').attr('src', '/assets/images/front/widget/icon/widgetDropDown.svg');
                    $('img.downArrow').css('transform', 'rotate(360deg)');

                    $('.widgetInner').addClass('on');
                    $('.horizonClose').removeClass('hide');
                    $('.horizonClose').on('click', function (){
                        $('div.widgetRewardWrap').addClass('hide');
                    });
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
                        $('.excessNotice').css('display', 'block');
                        $('div.rewardInputArea input[name=donate]').css('color', '#ff4343');
                        return false;
                    }

                    window.inAjax = 'Y';

                    $.ajax({
                        url: "/index/ajax/addDonate",
                        type: 'post',
                        data: $('form#formDonate').serialize(),
                        dataType: 'json',
                        success: function (response) {
                            if (response.error != 0) {
                                alert(response.message);
                                return false;
                            } else {
                                $('div.widgetRewardArea').addClass('hide');
                                $('div.widgetRewardComplete').removeClass('hide');

                                {# 사이트명 변경 #}
                                $('div.widgetRewardComplete p.rewardText span#siteName').text(response.item.siteName);
                                {# 포인트 변경 #}
                                if(response.item.tokenType == 1) {
                                    $('div.widgetRewardComplete p.rewardText span#donatedHorizon').text(response.item.amount + " P.Point");
                                }
                            }
                        },
                        error: function (xhr) {
                            if (xhr.status > 0) {
                                alert('서버 처리 중 오류가 발생하였습니다.');
                                window.location.reload();
                                return false;
                            }
                        }
                    });
                    return false;
                });
                {# 후원하기 이벤트 끝 #}

                {# 후원하기 완료 - 확인버튼 시작 #}
                $('button#btnDonateConfirm').on('click', function() {
                    $('#donate').val('');
                    if($('div.inner.widgetInner').hasClass('on')){
                        $('div.inner.widgetInner').removeClass('on');
                    }
                    $('div.widgetPadding2').addClass('hide');
                    $('div.widgetRewardWrap').addClass('hide');
                    $('div.widgetRewardArea.widgetPadding2').removeClass('hide');
                    $('div.widgetProfileWrap.widgetPadding2').removeClass('hide');
                    $('div.closeBtn').addClass('hide');
                    $('#widgetShareWrap div.closeBtn').removeClass('hide');
                    $('#widgetSnsLoginWrap div.closeBtn').removeClass('hide');

                    {# 회원정보 갱신 #}
                    {# 회원정보 갱신 #}
                    {% if clientId >= 1 %}
                    $.memberInfo(clientId);
                    {% endif %}
                });
                {# 후원하기 완료 - 확인버튼 끝 #}
                {# 후원하기 - 끝 #}

                {# SNS 로그인 화면 전환 시작 #}
                $('button#btnSnsLogin').on('click', function(){
                    $('div.widgetLoginWrap').addClass('hide');
                    $('div.widgetSnsLoginWrap').removeClass('hide');
                    if($('#widgetWrap div.inner.widgetInner').hasClass('on')){
                        $('#widgetWrap div.inner.widgetInner').removeClass('on');
                    }

                    $('div.widgetSnsLoginWrap div.closeBtn').on('click', function (){
                        $('div.widgetSnsLoginWrap').addClass('hide');
                        $('div.widgetLoginWrap').removeClass('hide');
                        $('.widgetInner').addClass('on');
                        if(!$('#widgetIntro').hasClass('hide')){
                            $('.widgetInner').removeClass('on');
                        }
                    });
                });

                {# X버튼 클릭 이벤트 시작 #}
                $('div.closeBtn').on('click', function (){
                    $('div.widgetPadding2').addClass('hide');
                    $('div.widgetRewardWrap').addClass('hide');
                    $('.widgetInner').removeClass('on');

                    {# 회원정보 갱신 #}
                    {% if clientId >= 1 %}
                    $.memberInfo(clientId);
                    {% endif %}
                });
                {# X버튼 클릭 이벤트 끝 #}

                if($('#rewardBtn').hasClass('btnLight')) {
                    $('#removeTxt').addClass('hide');
                }

                {# 리워드알림 - 창 닫기 시작 #}
                $('#widgetRewardNotice .closeBtn2').on('click', function (){
                    $('#widgetRewardNotice').addClass('hide');
                });
                $('#widgetRewardNotice .noticeBtnCancel').on('click', function (){
                    $('#widgetRewardNotice').addClass('hide');
                });
                {# 리워드알림 - 창 닫기 끝 #}

                {# 리워드알림 - 받기 시작 #}
                $('#widgetRewardNotice button.noticeBtnActive').on('click', function (){

                    window.inAjax = 'Y';

                    $.ajax({
                        url: "/index/ajax/rewardReceive",
                        type: 'post',
                        data: $('form#formReward').serialize(),
                        dataType: 'json',
                        success: function (response) {
                            if (response.error != 0) {
                                alert(response.message);
                                return false;
                            } else {
                                // 토스트 닫기
                                $('#widgetRewardNotice').addClass('hide');

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
                                alert('서버 처리 중 오류가 발생하였습니다.');
                                window.location.reload();
                                return false;
                            }
                        }
                    });
                    return false;
                });
                {# 리워드알림 - 받기 끝 #}

                {# 공유하기 - 화면 전환 시작 #}
                $('li a#btnShare').on('click', function() {
                    $('div#widgetShareWrap').removeClass('hide');
                    $('div.widgetProfileWrap').addClass('hide');

                    $('div.userMoreWrap').addClass('hide');
                    $('div.dim').addClass('hide');
                    $('img.downArrow').attr('src', '/assets/images/front/widget/icon/widgetDropDown.svg');
                    $('img.downArrow').css('transform', 'rotate(360deg)');
                });
                {# 공유하기 - 화면 전환 끝 #}

                {# 공유하기 시작 #}
                $("div.shareImg ul.snsList li.btnShare").on('click', function(){
                    var snsCode = $(this).attr('id');
                    if(snsCode == 'btnKakao') return false; // 카카오톡 공유일 경우는 return

                    var cUrl = $('input#copyShareUrl').val();
                    switch(snsCode){
                        case"btnTwitter":
                            cUrl = 'https://twitter.com/intent/tweet?text=퍼블리시링크공유하기:&url='+cUrl;
                            break;
                        case"btnTelegram":
                            cUrl = 'https://telegram.me/share/url?url='+cUrl;
                            break;
                        case"btnFacebook":
                            cUrl = 'http://www.facebook.com/sharer/sharer.php?u='+cUrl;
                            break;
                        case"btnNaver":
                            cUrl= "https://share.naver.com/web/shareView?url=" + cUrl + "&title=퍼블리시링크공유하기";
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
                    let copy = $('#copyShareUrl');
                    copy.show();
                    copy.select();
                    document.execCommand("Copy");
                    copy.hide();
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
                            {# 클라이언트 아이디 초기화 #}
                            clientId = 0;
                            window.location.reload();
                            return false;

                            {# 저장된 이메일 있으면 가져오기 #}
                            var savedEmail = localStorage.getItem('publishLinkEmailSaveCheck');
                            if(savedEmail != '' && savedEmail != null && savedEmail != undefined) {
                                $('div.idArea input#widgetId').val(savedEmail);
                                $("input#saveEmail").attr("checked", true);
                                $('div.saveEmailArea img.checkBox').attr('src', '/assets/images/front/widget/icon/checkBoxActive.svg');
                            } else {
                                $('div.saveEmailArea img.checkBox').attr('src', '/assets/images/front/widget/icon/checkBoxBasic.svg');
                                $("input#saveEmail").attr("checked", false);
                            }
                            {# 화면 show/hide #}
                            $('div.widgetPadding2').addClass('hide');
                            $('div.widgetIntro').removeClass('hide');
                            {# 더보기 영역 hide #}
                            if($('div.userMoreWrap').hasClass('hide') == false){
                                $('div.userMoreWrap').addClass('hide');
                                $('div.userMoreWrap').siblings('.dim').addClass('hide');
                            }
                            $('div.widgetProfileWrap.widgetPadding2').children('.arrowArea').children('.downArrow').attr('src', '/assets/images/front/widget/icon/widgetDropDown.svg');
                            $('div.widgetProfileWrap.widgetPadding2').children('.arrowArea').children('.downArrow').css('transform', 'rotate(360deg)');

                            window.location.reload();
                        },
                        error: function (xhr) {
                            if (xhr.status > 0) {
                                alert('서버 처리 중 오류가 발생하였습니다.');
                                window.location.reload();
                                return false;
                            }
                        }
                    });
                    return false;
                });
                {# 로그아웃 끝 #}


                {# 비회원 후원하기 페이지로 이동 시작 #}
                $('#introBtn').on('click', function (){
                    window.open('{{ protocol }}://{{ serviceUrl }}/index/nonmemberSponsor');
                });
                {# 비회원 후원하기 페이지로 이동 끝 #}

            });
        });
    </script>
{% endblock %}

{% block content %}
    <div id="widgetWrap" class="widgetWrap horizonWrap {% if clientSession != 'Y' %}hide{% endif %}">
        <div class="closeBtn horizonClose hide">
            <img src="{{ staticUrl }}/assets/images/front/widget/icon/widgetCloseBtn.svg" alt="닫기" class="closeBlack">
        </div>
        <div class="inner widgetInner">
            <div class="horizonType ">
                <div class="widgetArea">
                    {# 초기화면 #}
                    <div id="widgetIntro" class="widgetIntro {% if clientId >= 1 %}hide{% endif %}">
                        <div class="introTitle">
                            <p class="subTitle">뉴스 기사 읽고 혜택 받자!</p>
                            <p class="mainTitle">여러분의 후원이 깨끗한 뉴스를 만듭니다!</p>
                        </div>
                        <img src="{{ staticUrl }}/assets/images/front/widget/img/widgetIntro.svg" alt="" class="introImg">
                        <div class="introBtnArea">
                            <button type="button" id="introBtn" class="introBtn btnLight2">
                                <span>후원하기</span>
                            </button>
                            <button type="button" id="loginBtn" class="introBtn btnDark2">
                                <span>로그인</span>
                            </button>
                        </div>
                    </div>

                    {# 로그인 #}
                    <div id="widgetLoginWrap" class="widgetLoginWrap widgetPadding2 hide">
                        <div class="widgetLoginInner">
                            {# 로고 #}
                            <div class="logoArea">
                                {% if hostName == 'supportw.publishdemo.com'  %}
                                <img src="{{ staticUrl }}/assets/images/front/widget/common/logo/widgetTpLogo.png" alt="토큰포스트">
                                {% else %}
                                <img src="{{ staticUrl }}/assets/images/front/widget/common/logo/widgetLogoV3.svg" alt="퍼블리시 링크">
                                {% endif %}
                            </div>
                            <div class="loginArea">
                            <form id="formLogin" name="formLogin">
                                <input type="hidden" name="widgetCode" value="{{ widget.getCode() }}" />
                                <input type="hidden" id="parentUrl" name="parentUrl" value="" />
                                <input type="hidden" name="rdUrl" value="{{ rdUrl }}">
                                <fieldset>
                                    <legend class="ir_so">위젯 로그인</legend>
                                    <div class="loginInput">
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
                                        <div class="widgetBtn loginBtnArea">
                                            <button type="button" class="loginBtn btnStroke" id="btnSnsLogin">
                                                <span class="loginTxt">간편 로그인</span>
                                            </button>
                                            <button class="loginBtn btnLight" id="btnLogin">
                                                <span class="loginTxt">로그인</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="loginSubArea">
                                        <div class="saveEmailArea">
                                            <img src="{{ staticUrl }}/assets/images/front/widget/icon/checkBoxBasic.svg" alt="" class="checkBox">
                                            <input type="checkbox" id="saveEmail" name="email" value="">
                                            <label for="saveEmail">이메일 저장</label>
                                        </div>
                                        <div class="findArea">
                                            <div class="findAndJoin">
                                                <a href="{{ protocol }}://{{ serviceUrl }}/common/findInfo" class="id" target="_blank">아이디찾기</a>
                                                <a href="{{ protocol }}://{{ serviceUrl }}/common/findPassword" class="pw" target="_blank">비밀번호찾기</a>
                                                <a href="{{ protocol }}://{{ serviceUrl }}/common/socialJoin" target="_blank">회원가입</a>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                            <div id="widgetToast" class="widgetToast">
                                {# <span class="toastRed"></span>#}
                                <span class="toast">회원정보가 일치하지 않습니다.</span>
                                <img src="{{ staticUrl }}/assets/images/front/widget/icon/widgetToastCloseBtn.svg" alt="" id="widgetToastClose" class="toastClose">
                            </div>
                        </div>
                        </div>
                    </div>

                    {# 간편 로그인 #}
                    <div id="widgetSnsLoginWrap" class="widgetSnsLoginWrap widgetShareWrap widgetPadding2 hide">
                        <div class="snsLoginTitle shareTitle">
                            <p class="title">간편 로그인 수단을 선택해주세요.</p>
                        </div>
                        <div class="closeBtn">
                            <img src="{{ staticUrl }}/assets/images/front/widget/icon/widgetCloseBtn.svg" alt="위젯 로그인 닫기" class="closeBlack">
                        </div>
                        <div class="loginImg shareImg">
                            {# google #}
                            <input type="hidden" id="sGoogleSimpleApiKey" value="AIzaSyCyeHDPbjCtbjLT7718OOQmHarWo3kOLRE">
                            <input type="hidden" id="sGoogleClientId" value="993658457227-2mr318j8rbuml7ab7tiktnhrcohpfv0c.apps.googleusercontent.com">
                            <input type="hidden" id="sGoogleRedirectUrl" value="{{ staticUrl }}/common/authGoogle">
                            {#<input type="hidden" id="sGoogleRedirectUrl" value="{{ protocol }}://d4.elmindev1.com/common/authGoogle">#}

                            <img src="{{ staticUrl }}/assets/images/front/widget/icon/google.svg" alt="구글로 로그인하기" id="btnLoginGoogle">
                            <img src="{{ staticUrl }}/assets/images/front/widget/icon/facebook.svg" alt="페이스북으로 로그인하기" id="btnLoginFacebook">
                            <img src="{{ staticUrl }}/assets/images/front/widget/icon/twitter.svg" alt="트위터로 로그인하기" id="btnLoginTwitter">
                            <img src="{{ staticUrl }}/assets/images/front/widget/icon/kakaotalk.svg" alt="카카오톡으로 로그인하기" id="btnLoginKakao">
                            <img src="{{ staticUrl }}/assets/images/front/widget/icon/naver.svg" alt="네이버블로그로 로그인하기" id="btnLoginNaver">
                            {% if hostName == 'widget.publishdemo.com' %}
                            <img src="{{ staticUrl }}/assets/images/front/widget/common/icon/iconPublishIdDefault.png" alt="퍼블리시아이디로 로그인하기" id="btnLoginPublish">
                            {% endif %}
                        </div>
                    </div>

                    {# 리워드알림 #}
                    <div id="widgetRewardNotice" class="widgetRewardNotice widgetPadding2 hide">
                        <div class="closeBtn2">
                            <img src="{{ staticUrl }}/assets/images/front/widget/icon/closeWhiteBtn.svg" alt="위젯 로그인 닫기" class="closeWhite">
                        </div>
                        <div class="widgetRewardInner">
                            <div class="noticeTitle">
                                <p class="title">리워드 알림</p>
                            </div>
                            <form name="formReward" id="formReward">
                                <input type="hidden" name="widgetCode" value="{{ widgetCode }}">
                                <input type="hidden" name="clientId" value="{{ clientId }}">
                                <input type="hidden" name="activityId" value="">
                                <input type="hidden" name="rewardId" value="">
                                <p class="noticeText">적립할 수 있는 리워드가 있습니다.<br>리워드를 받으시겠습니까?</p>
                                <div class="noticeBtnWrap">
                                    <button type="button" class="noticeBtnCancel">
                                        <span>닫기</span>
                                    </button>
                                    <button type="button" class="noticeBtnActive">
                                        <span>받기</span>
                                    </button>
                                </div>
                            </form>
                            <img src="{{ staticUrl }}/assets/images/front/widget/img/widgetSMan.svg" alt="" id="rewardImg" class="rewardImg">
                        </div>
                    </div>

                    {# 메인 #}
                    <div id="widgetProfileWrap" class="widgetProfileWrap widgetPadding2 {% if clientSession != 'Y' %}hide{% endif %}">
                        {# 더보기버튼 #}
                        <div class="arrowArea">
                            <img src="{{ staticUrl }}/assets/images/front/widget/icon/widgetDropDown.svg" alt="더보기" class="downArrow">
                        </div>
                        <div class="userMoreWrap hide">
                            <div class="userMoreArea">
                                <ul class="userMore ">
                                    <li>
                                        <a href="{{ protocol }}://{{ serviceUrl }}/mypage/index" target="_blank" id="myPage" class="myPage">마이페이지</a>
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
                            <div class="userInfoArea ">
                                {# 사용자 프로필 #}
                                <div class="userProfileArea">
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
                                    <div class="userInfo">
                                        <dl>
                                            <dt id="userName" class="userName">{% if clientSession == 'Y' %}{{ clientInstance.getInformationInstance().getName() }}{% endif %}</dt>
                                            <dd id="userEmail" class="userEmail">{% if clientSession == 'Y' %}{{ clientInstance.getEmail() }}{% endif %}</dd>
                                        </dl>
                                    </div>
                                </div>
                                {# 포인트 영역 #}
                                <div class="moreNPointWrap">
                                    <div class="profileTableWrap">
                                        <div id="tbAsset" class="tbAsset">
                                            <dl>
                                                <dt class="news">P.Point</dt>
                                                <dd id="pPoint" ><span class="ellipsis">{% if clientSession == 'Y' %}{{ ppoint }}{% endif %}</span></dd>
                                                {% if clientSession == 'Y' %}
                                                    {% set psign = '' %}
                                                    {% if pDifference > 0 %}
                                                        {% set psign = '+' %}
                                                    {% elseif pDifference < 0 %}
                                                        {% set psign = '-' %}
                                                    {% endif %}
                                                {% endif %}
                                                <dd id="pToday" class="today">
                                                    <span class="todayPlus">일일증감량 </span>
                                                    <span id="pDifference">{% if clientSession == 'Y' %}{{ psign }}{{ pDifference | abs }}{% endif %}</span>
                                                </dd>
                                            </dl>
                                            <dl>
                                                <dt class="news">NEWS</dt>
                                                <dd id="news" ><span class="ellipsis">{% if clientSession == 'Y' %}{{ news }}{% endif %}</span></dd>
                                                {% if clientSession == 'Y' %}
                                                    {% set nsign = '' %}
                                                    {% if nDifference > 0 %}
                                                        {% set nsign = '+' %}
                                                    {% elseif nDifference < 0 %}
                                                        {% set nsign = '-' %}
                                                    {% endif %}
                                                {% endif %}
                                                <dd id="nToday" class="today">
                                                    <span class="todayPlus">일일증감량 </span>
                                                    <span id="nDifference">{% if clientSession == 'Y' %}{{ nsign }}{{ nDifference | abs }}{% endif %}</span>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {# 공유하기 #}
                    <div id="widgetShareWrap" class="widgetShareWrap widgetPadding2 hide">
                        <div class="shareTitle">
                            <p class="title">뉴스기사를 SNS에 공유해보세요.</p>
                        </div>
                        <div class="closeBtn">
                            <img src="{{ staticUrl }}/assets/images/front/widget/icon/widgetCloseBtn.svg" alt="위젯 로그인 닫기" class="closeBlack">
                        </div>
                        <div class="shareImg">
                            <input name="linkShareUrl" id="copyShareUrl" value="" />
                            <input type="hidden" name="clientReferralCode" id="referralCode" value="" />
                            <ul class="snsList">
                                <li id="btnFacebook" class="btnShare">
                                    <img src="{{ staticUrl }}/assets/images/front/widget/icon/facebook.svg" alt="페이스북으로 공유하기">
                                </li>
                                <li id="btnTwitter" class="btnShare">
                                    <img src="{{ staticUrl }}/assets/images/front/widget/icon/twitter.svg" alt="트위터로 공유하기">
                                </li>
                                <li id="btnKakao" class="btnShare">
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
                        <div class="widgetRewardArea widgetPadding2">
                            <div class="widgetRewardInner">
                                <div class="rewardTitle">
                                    <p class="title">후원하기</p>
                                </div>
                                <form name="formDonate" id="formDonate" class="rewardForm">
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
                                        <p class="excessNotice hide">후원 가능금액을 초과하였습니다.</p>
                                        <p class="rewardPoint">후원가능 P.Point :
                                            <span class="bold" id="pDonation">29,197</span>
                                        </p>
                                    </fieldset>
                                </form>
                                <button type="button" id="rewardBtn" class="rewardBtn btnLight">
                                    <span>후원</span>
                                </button>
                            </div>
                        </div>
                        {# 후원완료 #}
                        <div class="widgetRewardComplete widgetPadding2">
                            <div class="widgetRewardInner">
                                <div class="rewardCompleteTitle">
                                    <img src="{{ staticUrl }}/assets/images/front/widget/icon/circleActive.svg" alt="">
                                    <strong>후원완료</strong>
                                </div>
                                <p class="rewardText">
                                    <span class="rewardMedia" id="siteName">토큰포스트에</span>
                                    <span class="bold" id="donatedHorizon">1154 P.Point</span>를 후원하였습니다.
                                </p>
                                <button type="button" class="rewardBtn btnDark" id="btnDonateConfirm">
                                    <span>확인</span>
                                </button>
                            </div>
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
            }, { scope: "public_profile, email" });
        }

        function ProceedAuth(token) {

            $.ajax({
                url: "/common/ajax/authFacebook",
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
                            url = '{{ protocol }}://{{ serviceUrl }}/common/join';
                            window.open(url);
                            window.location.reload();
                        } else if (response.type == 'exist') {
                            {# 이미 가입한 회원 #}
                            clientId = response.snsLoginId;
                            window.location.reload();
                        } else {
                            {# 같은 이메일로 다른 SNS로 가입한 경우 #}
                            alert('다른 간편 로그인으로 가입된 이메일입니다.');
                        }
                    }
                }, error: function(oResult) {
                }
            });
            return false;
        }

    </script>
    <script xmlns="http://www.w3.org/1999/html">
        require(['base'], function () {
            require(['popup', 'kakao'], function (popup) {
                {# SNS 연동 로그인 #}

                {# 사용할 앱의 JavaScript 키를 설정해 주세요. #}
                Kakao.init('c4dc6ba40b08656b96583db45eafc1a5');
                // 중복되는 초기화를 막기 위해 isInitialized()로 SDK 초기화 여부를 판단
                Kakao.isInitialized();

                {# Google 시작 #}
                $('img#btnLoginGoogle').on('click', function () {
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
//                    var param = 2;

                    url += "scope=email profile";
//                    url += "&state=" + param ;
                    url += "&redirect_uri=" + oClientRedirectUrl.value;
                    url += "&response_type=code";
                    url += "&client_id=" + oClientId.value;
                    var id = "googleLogin";
                    window.open(url, id, "width=" + width + ", height=" + height + ", scrollbars=yes");
                });
                {# Google 끝 #}

                {# Facebook 시작 #}
                $('img#btnLoginFacebook').on('click', function() {
                    checkLoginState();
                });
                {# Facebook 끝 #}

                var encodeUrl = encodeURIComponent("{{ staticUrl }}");

                {# Naver 시작 #}
                $('img#btnLoginNaver').on('click', function() {
                    var apiURL = "https://nid.naver.com/oauth2.0/authorize?response_type=code&client_id=eb90otHBjoBZsdjE09dp&redirect_uri="+encodeUrl+"%2Fcommon%2FauthNaver";
                    window.open(apiURL);
                });
                {# Naver 끝 #}

                {# Kakao 시작 #}
                $('img#btnLoginKakao').on('click', function () {
                    var apiURL = "https://kauth.kakao.com/oauth/authorize?response_type=code&client_id=ed08b75c9d6ecf7211e066f6dfab5dc1&redirect_uri="+encodeUrl+"%2Fcommon%2FauthKakao";
                    window.open(apiURL);
                });
                {# Kakao 끝 #}

                {# Twitter 시작 #}
                $('img#btnLoginTwitter').on('click', function () {
                    var apiURL = "{{ staticUrl }}/index/twitterOauth";
                    window.open(apiURL);
                });
                {# Twitter 끝 #}

                {# 카카오톡 링크 공유하기 시작 #}
                $('li#btnKakao').on('click', function() {
                    try {
                        if(Kakao) {
                            Kakao.init('c4dc6ba40b08656b96583db45eafc1a5');
                            Kakao.isInitialized();
                        }
                    } catch (e) {};

                    Kakao.Link.sendDefault({
                        objectType: 'text',
                        text:
                            '기사 공유하기',
                        link: {
                            mobileWebUrl: $('input#copyShareUrl').val(),
                            webUrl: $('input#copyShareUrl').val(),
                        },
                    });
                });
                {# 카카오톡 링크 공유하기 끝 #}
            });
        });
    </script>
{% endblock %}
