<!DOCTYPE html>
<html lang="{{ infoSite.getLanguageCodeIso() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title>PUBLISHlink</title>

    <meta name="Author" content="{{ infoSite.getTitleNative() }}"/>
    <meta name="Copyright" content="{{ infoSite.getTitleNative() }}"/>
    <meta name="Keywords" content="{{ metaKeyword }}"/>
    <meta name="Description" content="{{ metaContent }}"/>
    <meta name="news_keywords" content="{{ metaKeyword }}"/>

    {% if infoSite.getFaviconId() >= 1 and infoSite.getFaviconUrl() != '' %}
        <link rel="shortcut icon" href="{{ infoSite.getFaviconUrl() }}">
    {% endif %}

    {# edge에서 숫자 링크걸리는거 방지 메타태그 #}
    <meta name="format-detection" content="telephone=no"/>
    <meta name="viewport" content="width=device-width, maximum-scale=1.0">


    <meta property="og:site_name" content="{{ infoSite.getTitleNative() }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ metaUrl }}">
    <meta property="og:title" content="{{ metaTitle }}">
    <meta property="og:image" content="{{ metaImage }}">
    <meta property="og:description" content="{{ metaContent }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@">
    <meta name="twitter:creator" content="@">
    <meta name="twitter:url" content="{{ metaUrl }}">
    <meta name="twitter:image" content="{{ metaImage }}"/>
    <meta name="twitter:domain" content="{{ infoSite.getDomain() }}">
    <meta name="twitter:title" content="{{ metaTitle }}">


    {% if robot != 'Y' %}
        <meta name="robots" content="noindex" />
        <meta name="googlebot" content="noindex" />
    {% endif %}

    <script type="text/javascript">
        window.inAjax = 'N';
        window.inAjaxMsg = '이전 요청을 처리중입니다. 30초 동안 응답이 없을경우 페이지를 새로고침 해주세요.';
        window.baseUrl = '{{ staticUrl }}/assets/js';
        window.urlArgs = '{{ jsRevision }}';
        var requirejs = {
            baseUrl: window.baseUrl,
            urlArgs: window.urlArgs
        };
        var widgetChatServerAddr = '{{ chatServer }}';
    </script>

    <link rel="stylesheet" type="text/css" href="{{ staticUrl }}/assets/css/front/normalize.css?r={{ cssRevision }}"/>


    <link rel="stylesheet" type="text/css" href="{{ staticUrl }}/assets/css/widget/base.css?r={{ cssRevision }}"/>
    <link rel="stylesheet" type="text/css" href="/assets/css/font/font-awesome-4.6.3/css/font-awesome.min.css?r={{ cssRevision }}"/>



    <script data-main="apps/widget.js" src="{{ staticUrl }}/assets/js/require.js?r={{ jsRevision }}"></script>

    {% block embedStyle %}
    {% endblock %}
    {% if APPLICATION_ENV == 'production' %}
        {# 각 구글 태그 적용 #}

    {% endif %}

</head>
<!-- Centered page -->
<body>
<div id="wrap" class="wrap">
    <div class="container contentContainer clear">
        {% block content %}

        {% endblock %}
    </div>
</div>

<script>
    require(['base'], function () {
        require(['popup','jqueryui'], function (popup) {

        });
    });
</script>

{% block embedScript %}
{% endblock %}

{% if APPLICATION_ENV == 'production' %}
    <!-- Quantcast Tag -->
    <!-- End Quantcast tag -->
{% endif %}

</body>
</html>