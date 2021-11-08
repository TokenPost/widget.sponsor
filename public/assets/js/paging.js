function makingPaging(pagingContainer, dataSize, listSize, pageSize, pageNum, callbackFunction) {
    let html = '';

    let maxPage = Math.ceil(dataSize/listSize) - 1;
    if (maxPage < 0) maxPage = 0;
    let startPage = pageNum - (pageSize - 1)/2;
    let endPage = pageNum + (pageSize - 1)/2;
    if (startPage < 0) {
        endPage += Math.abs(startPage);
        startPage = 0;
    } else if (endPage > maxPage) {
        startPage -= endPage - maxPage;
        endPage = maxPage;
    }

    if (startPage < 0) startPage = 0;
    if (endPage > maxPage) endPage = maxPage;

    html += '<nav>';
        html += '<ul class="pagination pagination-sm">';

            if (pageNum != 0) {
                html += '<li class="page-item">';
                    html += '<a href="#" aria-label="Previous" data-page="' + (pageNum - 1) + '" class="pageingItem">';
                        html += '<span aria-hidden="true" class="page-link">&laquo;</span>';
                    html += '</a>';
                html += '</li>';
            }
            
            html += '<li class="page-link"><a href="#" data-page="' + 0 + '" class="pageingItem">';
                if (pageNum == 0) html += '<b>1</b>';
                else html += 1;
            html +=  '</a></li>';   
            if (startPage != 0) html += '<li class="page-link">...</li>';
            for (let i = startPage; i <= endPage ;i++) {
                if (i == 0 || i == maxPage) continue;

                html += '<li class="page-link"><a href="#" data-page="' + i + '" class="pageingItem">';
                    if (pageNum == i) html += '<b>' + (i + 1) + '</b>';
                    else html += (i + 1);
                html += '</a></li>';
            }
            if (endPage != maxPage)  html += '<li class="page-link">...</li>';
            if (maxPage != 0) {
                html += '<li class="page-link"><a href="#" data-page="' + maxPage + '" class="pageingItem">';
                    if (pageNum == maxPage) html += '<b>' + (maxPage + 1) + '</b>';
                    else html += (maxPage + 1);
                html += '</a></li>';
            }

            if (pageNum != maxPage) {
                html += '<li class="page-item">';
                    html += '<a href="#" aria-label="Next" data-page="' + (pageNum + 1) + '" class="pageingItem">';
                        html += '<span aria-hidden="true" class="page-link">&raquo;</span>';
                    html += '</a>';
                html += '</li>';
            }

        html += '</ul>';
    html += '</nav>';

    $(pagingContainer).html(html);

    if(callbackFunction != undefined) callbackFunction(pageNum);

    $('.pageingItem').click(function() {
        
        makingPaging(pagingContainer, dataSize, listSize, pageSize, parseInt($(this).attr("data-page")), callbackFunction);
    })  
}