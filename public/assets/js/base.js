var _PHPSESSID = 'PHPSESSID';

var console = window.console || {
        log: function () {
            // passes
        }
    };

// 콘솔 로그 앞에 시간 값 출력 추가
try {
    console.logCopy = console.log.bind(console);

    console.log = function (data) {
        //if (typeof _DEBUG == 'undefined' || _DEBUG != true) return;


        var timestamp = parseInt(Date.now(), 10);
        var ts = parseInt(timestamp / 1000, 10);
        var ms = timestamp % 1000;
        if (ms.length == 2) {
            ms += '0';
        }

        var timestamp = '[' + ts + '.' + ms + ']';

        try {
            this.logCopy(timestamp, data);
        } catch (e) {

        }
    };
} catch (e) {
    // passes
}

require.config({
    waitSeconds: 0,
    baseUrl: window.baseUrl,
    paths: {
        'jquery': 'lib/jquery-1.12.4.min',
        'jquery.mobile': 'jquery.mobile-1.4.5.min',
        'jqueryScroll':'lib/jquery.scrollbar.min',
        'jqueryui': 'lib/jquery-ui.min-1.12.1',
        'jqueryTouch': 'lib/jquery.ui.touch-punch.min',
        'jquery.scroll': 'lib/jquery.mCustomScrollbar.min',
        'jquery.scrollTo': 'jquery.scrollTo.2.1.2',
        'jquery.extended': 'jquery.extended',
        'jquery-mousewheel': 'lib/jquery.mousewheel.min',
        'jquery.socialjs':'socialjs/jquery.socialjs.min',
        'socialjs':'socialjs/socialjs-2.1.1',
        'socialjs2':'socialjs2/jssocials.min',

        'comment': 'modules/comment',
        'socket.io': 'lib/socket.io-2.2.0',
        'backbone': 'lib/backbone-min-1.3.3',
        'underscore': 'lib/underscore-min-1.9.1',
        'printThis': 'lib/jquery.printThis',
        'json2': 'lib/json2',
        'picker' : 'lib/jquery.datetimepicker.full.min',
        'barrating':'lib/jquery.barrating.min',
        'countUp':'lib/countUp',
        'lightGallery':'lib/lightgallery',
        'lightSlider':'lib/lightslider',
        'swiper':'lib/jquery.swiper.min',
        'swiper5':'lib/jquery.swiper.min.5.3.1',
        'slick': 'lib/slick',
        'slick-lightbox': 'lib/slick-lightbox',
        'bxslider' : 'lib/jquery.bxslider.min',
        'panelSlider': 'admin/jquery.panelslider.min.js',
        'highlightFade': 'jquery.highlightFade',
        'owl' : 'owl.carousel.min',

        /* range Slider (사용할 시 css도 같이 불러야함) */
        /*'rangeSlider': 'lib/ion.rangeSlider.min',*/

        'rangeslider' : 'lib/rangeslider.min',

        'mScrollConcat':'jquery.mCustomScrollbar.concat.min',

        'ckeditor-core': 'ckeditor/ckeditor',
        'ckeditor-jquery': 'ckeditor/adapters/jquery',
        'placeholder':'lib/jquery.placeholder.min',
        'wcolpick':'lib/wcolpick/wcolpick',
        'token': 'lib/jquery.tokeninput',
        'tokeninput': 'lib/jquery.tokeninput',
        'typewriter': 'lib/typewriter',
        'clipboard':'lib/clipboard.min',
        'kakao': "//developers.kakao.com/sdk/js/kakao.min",
        'cookie': 'jquery.cookie',
        //'socket.io': 'socket.io-1.3.5',
        'sumoSelect': 'admin/jquery.sumoselect.min',
        'roundabout': 'jquery.roundabout.min',
        'flipster': 'jquery.flipster',
        'moment': 'lib/moment.min-2.18.1',
        'moment-timezone': 'moment-timezone.min',
        'soundmanager2':'soundmanager2',


        'fullCalendar':'fullcalendar.min',
        'flipclock':'lib/flipclock.min',

        /**
         * am chart.
         */
        'amcharts4/core'            : 'lib/amcharts4/core',
        'amcharts4/charts'          : 'lib/amcharts4/charts',
        'amcharts4/themes/material' : 'lib/amcharts4/themes/material',
        'amcharts4/themes/animated' : 'lib/amcharts4/themes/animated',
        'amcharts4/themes/dark'     : 'lib/amcharts4/themes/dark',

        /*
        'am.charts':'lib/amcharts/amcharts',
        'am.stock':'lib/amcharts/amstock',
        'am.serial':'lib/amcharts/serial',
        'am.themes.dark':'lib/amcharts/themes/dark',*/


        /* cropper 사용할시 css도 같이 불러야함.*/
        'cropper': 'lib/cropper.min',

        'bigchat': 'lib/bigchat',
        'awesomeCloud': 'lib/jquery.awesomeCloud-0.2',
        'wordcloud2' : 'lib/wordcloud2',
        'progressBarTimer': 'lib/jquery.progressBarTimer',
        'jqmeter': 'lib/jqmeter.min',
        'datepicker': 'lib/jquery-ui',
        'daterangepicker': 'lib/daterangepicker.min-3.14.1',

        'bootstrap/perfect-scrollbar':'bootstrap/plugins/perfect-scrollbar/perfect-scrollbar.min',
        'bootstrap/hoverable-collapse':'bootstrap/hoverable-collapse',
        'bootstrap/bootstrap-table':'bootstrap/bootstrap-table',
        'bootstrap/chartjs':'bootstrap/plugins/chartjs/chart.min',
        'bootstrap/jquery.sparkline.min':'bootstrap/plugins/jquery-sparkline/jquery.sparkline.min',
        'bootstrap/misc':'bootstrap/misc',
        'bootstrap/settings':'bootstrap/settings',
        'bootstrap/off-canvas':'bootstrap/off-canvas',
    },
    shim: {
        "jquery.mobile": {
            "deps": [ "jquery"]
        },
        'jqueryui': {
            deps: [
                'jquery.scroll'
            ]
        },
        'jquery.scroll': {
            deps: [
                // 'jquery.mousewheel','mScrollConcat'
                'jquery-mousewheel'
            ]
        },
        'jquery-mousewheel': {
            deps: [
                'jquery'
            ]
        },
        'jquery.socialjs': {
            deps: [
                'jquery'
            ]
        },
        'jquery.scrollTo': {
            deps: [
                'jquery'
            ]
        },
        'socialjs': {
            deps: [
                'jquery'
            ],
            exports:"socialjs"
        },
        "socialjs2": {
            "deps": [ 'jquery' ]
        },
        'backbone': {
            deps: [
                'json2',
                'underscore',
                'jquery'
            ],
            exports: 'Backbone'
        },
        'underscore': {
            exports: '_'
        },
        'printThis': {
            deps: [
                'jquery'
            ]
        },
        'lightSlider':{
            deps: [
                'jquery'
            ],
            exports:"lightSlider"
        },
        'lightGallery':{
            deps: [
                'jquery'
            ],
            exports:"lightGallery"
        },
        'swiper':{
            deps: [
                'jquery'
            ]
        },
        'swiper5':{
            deps: [
                'jquery'
            ]
        },
        'slick': {
            deps: ['jquery'],
            exports: 'jQuery.fn.slick'
        },
        'slick-lightbox': {
            deps: [
                'jquery'
            ]
        },
        'bxslider': {
            deps: [
                'jquery'
            ]
        },
        'highlightFade': {
            deps: [
                'jquery'
            ]
        },
        'owl': {
            deps: [
                'jquery'
            ],
            exports: 'owlCarousel'
        },
        /*'rangeSlider':{
            exports:'rangeSlider'
        },*/
        'rangeslider':{
            deps: [
                'jquery'
            ],
            exports:'rangeslider'
        },

        'ckeditor-jquery': {
            deps: ['jquery', 'ckeditor-core']
        },
        'placeholder': {
            deps: [
                'jquery'
            ]
        },
        'wcolpick': {
            deps: [
                'jquery'
            ]
        },
        'token': {
            deps: [
                'jquery'
            ]
        },
        'tokeninput': {
            deps: [
                'jquery'
            ]
        },
        'typewriter': {
            deps: [
                'jquery'
            ]
        },
        'clipboard': {
            deps: [
                'jquery'
            ],
            exports: [
                'Clipboard'
            ]
        },
        "cookie": {
            "deps": [ "jquery"]
        },
        'kakao':{
            exports:"Kakao"
        },
        'socketio': {
            exports: 'io'
        },
        'picker': {
            deps: [
                'jquery',
                'jquery-mousewheel'
            ]
        },
        'roundabout': {
            deps: [
                'jquery'
            ]
        },
        'flipster': {
            deps: [
                'jquery'
            ]
        },
        'moment': {
            exports: [
                'moment'
            ]
        },
        'moment-timezone': {
            exports: [
                'moment'
            ]
        },
        'soundmanager2':{
            exports:"soundManager"
        },
        'fullCalendar':{
            deps: [
                'jquery',
                'moment'
            ]
        },
        'flipclock':{
            deps: [
                'jquery'
            ],
            exports:"flipclock"
        },

        'amcharts4/core': {
            init: function () {
                //AmCharts.isReady = true;
                return window.am4core;
            }
        },
        'amcharts4/charts': {
            deps: ['amcharts4/core'],
            exports: 'amcharts4/charts',
            init: function () {
                return window.am4charts;
            }
        },
        'amcharts4/themes/animated': {
            deps: ['amcharts4/core'],
            exports: 'amcharts4/themes/animated',
            init: function () {
                return window.am4themes_animated;
            }
        },
        'amcharts4/themes/dark': {
            deps: ['amcharts4/core'],
            exports: 'amcharts4/themes/dark',
            init: function () {
                return window.am4themes_dark ;
            }
        },
        'amcharts4/themes/material': {
            deps: ['amcharts4/core'],
            exports: 'amcharts4/themes/material',
            init: function () {
                return window.am4themes_material ;
            }
        },

        /*
         "am.themes.dark": {
         "deps": [ 'am.charts']
         },
         "am.serial": {
         "deps": [ 'am.charts']
         },

         "am.stock": {
         "deps": [ 'jquery','am.charts','am.serial','am.themes.dark'],
         exports: 'AmCharts',
         init: function() {
         AmCharts.isReady = true;
         }
         },*/

        'bigchat':{
            exports:"bigchat"
        },
        'awesomeCloud': {
            deps: [
                'jquery'
            ],
            exports:'awesomeCloud'
        },
        'wordcloud2':{
            deps: [
                'jquery'
            ],
            exports:'wordcloud'
        },
        'progressBarTimer': {
            deps: [
                'jquery'
            ]
        },
        'jqmeter': {
            exports: 'jqmeter'
        },
        'daterangepicker': {
            deps: [
                'jquery',
                'jqueryui',
                'moment'
            ],
            exports: 'daterangepicker'
        },

        'bootstrap/perfect-scrollbar':{
            exports:"bootstrap/perfect-scrollbar"
        },
        'bootstrap/hoverable-collapse':{
            deps: [
                'jquery'
            ],
            exports:"bootstrap/hoverable-collapse"
        },
        'bootstrap/bootstrap-table' :{
            deps: [
                'jquery'
            ],
            exports:"bootstrap/bootstrap-table"
        },
        'bootstrap/chartjs':{
            exports:"bootstrap/chartjs"
        },
        'bootstrap/jquery.sparkline.min':{
            exports:"bootstrap/jquery.sparkline.min"
        },
        
        'bootstrap/misc':{
            deps: [
                'jquery',
                'bootstrap/chartjs'
            ],
            exports:"bootstrap/misc"
        },
        'bootstrap/settings':{
            deps: [
                'jquery'
            ],
            exports:"bootstrap/settings"
        },
        'bootstrap/off-canvas':{
            deps: [
                'jquery'
            ],
            exports:"bootstrap/off-canvas"
        },
        'bootstrap/daterangepicker': {
            deps: [
                'jquery',
                'moment'
            ],
            exports: 'bootstrap/daterangepicker'
        },
    }
});


define('popup', ['jquery'], function ($) {



    return {
        popupIdx: 0,
        popupRef: {},

        me: function (obj) {
            var _popup = obj.parent().parent().parent().attr('id');

            if(typeof _popup === 'undefined' ){
                _popup = obj.parent().parent().attr('id');
            }


            if (_popup == 'popup') {
                return this.popupRef[0];
            } else {
                var idx = parseInt(_popup.replace('popup', ''), 10);
                return this.popupRef[idx];
            }
        },

        create: function (popupIdx) {
            $popup = this;

            if (typeof popupIdx != 'number') {
                popupIdx = ++this.popupIdx;
            }

            var popupId = '#popup' + this.popupIdx;
            var parentId = '#parentDisable' + this.popupIdx;

            if ($(parentId).length < 1) {
                $('body').append('<div id="' + popupId.replace('#', '') + '" class="popupWrap"><div id="' + parentId.replace('#', '') + '" class="popupDisabled"></div></div>');
                $('body').css({overflow:'auto'});

                $(popupId).append('<div class="loading"><img src="/assets/images/loading_2c2c2c.gif" alt="loading" /></div>');
                $(popupId).append('<div class="content popup"></div>');

                $(popupId).css('z-index', (10000 + popupIdx));
                $(parentId).css('z-index', (10000));
            }

            var objPopup = {
                parentId: null,
                popupId: null,
                idx: null,
                events: {},

                on: function (eventName, callback) {
                    this.events[eventName] = callback;
                },

                getPopupId: function () {
                    return this.popupId;
                },

                setPopupId: function (idx) {
                    this.idx = idx;
                    this.parentId = '#parentDisable' + idx;
                    this.popupId = '#popup' + idx;
                },

                open: function (url, data) {
                    if (typeof data == 'undefined') data = {};
                    if (typeof data.options == 'undefined') data.options = {};
                    if (typeof data.success == 'undefined') data.success = function () {
                        // nothing
                    };
                    if (typeof data.error == 'undefined') data.error = function () {
                        // nothing
                    };

                    $(this.popupId + ' > .content').hide();
                    $(this.popupId + ' > .loading').show();
                    $(this.popupId).css('display', 'block');
                    $(this.parentId).css('display', 'block');

                    var _this = this;

                    var options = $.extend(data.options, {
                        url: url,
                        dataType: 'json',
                        success: function (response) {
                            if (response.error != 0) {
                                $(_this.popupId).hide();
                                $(_this.popupId + ' > .loading').hide();
                                $(_this.parentId).hide();

                                $('body').css({overflow:'auto'});
                                data.error(response.message);
                                return;
                            }

                            $(_this.popupId + ' > .content').html(response.html);

                            // $(_this.popupId).css('marginTop', -parseInt($(_this.popupId + ' > .content').height() / 2, 10));
                            $(_this.popupId + ' > .content').css('marginLeft', -parseInt($(_this.popupId + ' > .content').width() / 2, 10));
                            // $(_this.popupId + ' > .content').css('marginTop', -parseInt($(_this.popupId + ' > .content').height() / 2, 10));
                            // $(_this.popupId + ' > .content').css('marginTop', -parseInt($(_this.popupId + ' > .content').height() / 2, 10));

                            $(_this.popupId + ' > .loading').hide();
                            $(_this.popupId + ' > .content').show();

                            data.success(response);
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            $(_this.popupId).hide();
                            $(_this.parentId).hide();

                            if (xhr.status > 0) {
                                data.error('server error');
                            } else {
                                // passes
                            }
                        }
                    });

                    $.ajax(options);
                    $.checkWindowLeft();
                },

                showProgress: function () {
                    $(this.parentId).show();
                    $(this.popupId).show();
                    $(this.popupId + ' > .loading').show();
                    $(this.popupId + ' > .content').hide();
                },

                show: function () {
                    $(this.parentId).show();
                    $(this.popupId + ' > .content').show();
                    $(this.popupId).show();
                },

                hide: function () {
                    $(this.popupId).hide();
                    $(this.parentId).hide();
                },

                controlHeight: function(){
                    $(this.popupId + ' > .content').css('marginLeft', -parseInt($(this.popupId + ' > .content').width() / 2, 10));
                    $(this.popupId + ' > .content').css('marginTop', -parseInt($(this.popupId + ' > .content').height() / 2, 10)+100);
                },

                destroy: function () {
                    if (typeof this.events['destroy'] == 'function') {
                        this.events['destroy']();
                        delete $popup.popupRef[this.idx];

                        if ($popup.popupIdx == this.idx) {
                            $popup.popupIdx--;
                        }
                    }

                    $(this.popupId).remove();
                    $(this.parentId).remove();
                    $('body').css({overflow:'auto'});
                },

                clear: function () {
                    $(this.popupId + ' > .content').html('');
                }
            }

            objPopup.setPopupId(popupIdx);
            this.popupRef[popupIdx] = objPopup;

            return objPopup;
        }
    }
});

define('util', ['popup'], function (popup) {
    return {
        preload: function (list) {
            $(list).each(function () {
                $('<img/>')[0].src = this;
            });
        },

        /**
         * @deprecated
         *
         * @param url
         * @param data
         */
        popup: function (url, data) {
            var pop = popup.create(0);
            pop.open(url, data);
        },

        /**
         * @deprecated
         */
        popupShowProgress: function () {
            var pop = popup.create(0);
            pop.showProgress();
        },

        /**
         * @deprecated
         */
        popupShow: function () {
            var pop = popup.create(0);
            pop.show();
        },

        /**
         * @deprecated
         */
        popupClose: function () {
            var pop = popup.create(0);
            pop.hide();
        },

        /**
         * @deprecated
         */
        popupDestroy: function () {
            var pop = popup.create(0);
            pop.destroy();
        }
    }
});

String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
};
