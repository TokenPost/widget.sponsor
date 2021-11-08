//

Date.prototype.format = function (f) {
    if (!this.valueOf()) return " ";

    var weekKorName = ["일요일", "월요일", "화요일", "수요일", "목요일", "금요일", "토요일"];
    var weekKorShortName = ["일", "월", "화", "수", "목", "금", "토"];
    var weekEngName = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    var weekEngShortName = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    var monthEngNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    var monthEngShortNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var d = this;

    return f.replace(/(yyyy|yy|MM|MNS|MN|dd|KS|KL|ES|EL|HH|hh|mm|ss|a\/p)/gi, function ($1) {
        switch ($1) {
            case "yyyy": return d.getFullYear(); // 년 (4자리)
            case "yy": return (d.getFullYear() % 1000).zf(2); // 년 (2자리)
            case "MM": return (d.getMonth() + 1).zf(2); // 월 (2자리)
            case "MNS": return monthEngShortNames[d.getMonth()]; // 영문 월 이름  짧은(3자리)
            case "MN": return monthEngNames[d.getMonth()]; // 영문 월 이름 (2자리)
            case "dd": return d.getDate().zf(2); // 일 (2자리)
            case "KS": return weekKorShortName[d.getDay()]; // 요일 (짧은 한글)
            case "KL": return weekKorName[d.getDay()]; // 요일 (긴 한글)
            case "ES": return weekEngShortName[d.getDay()]; // 요일 (짧은 영어)
            case "EL": return weekEngName[d.getDay()]; // 요일 (긴 영어)
            case "HH": return d.getHours().zf(2); // 시간 (24시간 기준, 2자리)
            case "hh": return ((h = d.getHours() % 12) ? h : 12).zf(2); // 시간 (12시간 기준, 2자리)
            case "mm": return d.getMinutes().zf(2); // 분 (2자리)
            case "ss": return d.getSeconds().zf(2); // 초 (2자리)
            case "a/p": return d.getHours() < 12 ? "오전" : "오후"; // 오전/오후 구분
            default: return $1;
        }
    });
};

String.prototype.string = function (len) { var s = '', i = 0; while (i++ < len) { s += this; } return s; };
String.prototype.zf = function (len) { return "0".string(len - this.length) + this; };
Number.prototype.zf = function (len) { return this.toString().zf(len); };

require(['base'], function () {
    require(['popup', 'kakao'], function () {


        $.nl2br = function(str){
            return str.replace(/\n/g, "<br />");
        };

        //@fixme: kakao 코드? 확인 필요. 아래 부분 이슈 발생
        // Kakao.init('61693bd6c0f07ba6fd41b303746d4dbf');

        $.kakaoSendLink = function (title, description, imageUrl, webUrl, mobileWebUrl) {
            Kakao.Link.sendDefault({
                objectType: 'feed',
                content: {
                    title: title,
                    description: description,
                    imageUrl: imageUrl,
                    link: {
                        mobileWebUrl: mobileWebUrl,
                        webUrl: webUrl
                    }
                }
            });
        };

        $.checkWindowLeft = function () {
            $.each($('.popupWrap > .content'), function (idx, item) {
                //var offset = $(this).offset();
                var position = $(this).position();
                if(position.left < ($(this).width() / 2) ){
                    $(this).css('margin-left', position.left * -1);
                } else {
                    $(this).css('margin-left', $(this).width() / 2 * -1);
                }
            });
        };

        /*$( window ).resize( function() {
            $.checkWindowLeft();
        });*/



        $.onElementHeightChange = function(element, callback){
            var lastHeight = element.clientHeight, newHeight;
            (function run(){
                newHeight = element.clientHeight;
                if( lastHeight != newHeight )
                    callback();
                lastHeight = newHeight;

                if( element.onElementHeightChangeTimer )
                    clearTimeout(element.onElementHeightChangeTimer);

                element.onElementHeightChangeTimer = setTimeout(run, 300);
            })();
        };

        $(document).ready(function () {

        });

    });
});