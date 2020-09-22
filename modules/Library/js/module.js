/*
Pupilsight, Flexible & Open School System
*/

function stopRKey(evt) {
    var evt = (evt) ? evt : ((event) ? event : null); var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); if ((evt.keyCode == 13) && (node.type == "text")) { return false; }
}

$.prototype.loadGoogleBookData = function (settings) {
    $(this).click(function () {
        var isbn = $("#fieldISBN10").val() ? $("#fieldISBN10").val() : $("#fieldISBN13").val();

        if (isbn) {
            $.get(("https://www.googleapis.com/books/v1/volumes?q=isbn:" + isbn), function (data) {
                var obj = (data.constructor === String) ? jQuery.parseJSON(data) : data;

                if (obj['totalItems'] == 0) {
                    alert(settings.notFound);
                } else {
                    // SET FIELDS
                    $("#name").val(obj['items'][0]['volumeInfo']['title']);
                    var authors = '';
                    for (var i = 0; i < obj['items'][0]['volumeInfo']['authors'].length; i++) {
                        authors = authors + obj['items'][0]['volumeInfo']['authors'][i] + ', ';
                    }
                    $("#producer").val(authors.substring(0, (authors.length - 2)));
                    $("#fieldPublisher").val(obj['items'][0]['volumeInfo']['publisher']);
                    if (obj['items'][0]['volumeInfo']['publishedDate'].length == 10) {
                        $("#fieldPublicationDate").val(obj['items'][0]['volumeInfo']['publishedDate'].substring(8, 10) + '/' + obj['items'][0]['volumeInfo']['publishedDate'].substring(5, 7) + '/' + obj['items'][0]['volumeInfo']['publishedDate'].substring(0, 4));
                    } else if (obj['items'][0]['volumeInfo']['publishedDate'].length == 7) {
                        $("#fieldPublicationDate").val(obj['items'][0]['volumeInfo']['publishedDate'].substring(5, 7) + '/' + obj['items'][0]['volumeInfo']['publishedDate'].substring(0, 4));
                    } else if (obj['items'][0]['volumeInfo']['publishedDate'].length == 4) {
                        $("#fieldPublicationDate").val(obj['items'][0]['volumeInfo']['publishedDate'].substring(0, 4));
                    }
                    $("#fieldDescription").val(obj['items'][0]['volumeInfo']['description']);
                    if (obj['items'][0]['volumeInfo']['industryIdentifiers'][0]['type'] == 'ISBN_10') {
                        $("#fieldISBN10").val(obj['items'][0]['volumeInfo']['industryIdentifiers'][0]['identifier']);
                    }
                    if (obj['items'][0]['volumeInfo']['industryIdentifiers'][1]['type'] == 'ISBN_13') {
                        $("#fieldISBN13").val(obj['items'][0]['volumeInfo']['industryIdentifiers'][1]['identifier']);
                    }
                    $("#fieldPageCount").val(obj['items'][0]['volumeInfo']['pageCount']);
                    var format = obj['items'][0]['volumeInfo']['printType'].toLowerCase();
                    format = format.charAt(0).toUpperCase() + format.slice(1);
                    $("#fieldFormat").val(format);
                    $("#fieldLink").val(obj['items'][0]['volumeInfo']['infoLink']);
                    var image = obj['items'][0]['volumeInfo']['imageLinks']['thumbnail'];
                    if (image) {
                        $("#imageType").val('Link');
                        $("#imageLink").val(image);
                        $("#imageType").change();
                    }
                    $("#fieldLanguage").val(obj['items'][0]['volumeInfo']['language']);
                    var subjects = '';
                    for (var i = 0; i < obj['items'][0]['volumeInfo']['categories'].length; i++) {
                        subjects = subjects + obj['items'][0]['volumeInfo']['categories'][i] + ', ';
                    }
                    $("#fieldSubjects").val(subjects.substring(0, (subjects.length - 2)));
                }
            });
        } else {
            alert(settings.dataRequired);
        }
    });
};
