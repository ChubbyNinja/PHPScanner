/**
 * Created by Danny on 1/19/2016.
 */

window.onload = function(){

    if( document.querySelectorAll('.login-form input[type=text]').length ) {
        document.querySelector('.login-form input[type=text]').focus();
    }

    $(document).foundation();


    $('.ban-ip-address').on('click', function () {
        return confirm('Are you sure you want to block: ' + "\n\n" + $(this).attr('data-ip') +'?');
    });
};


/**
* Original snippet from Jason @ http://stackoverflow.com/a/987376/1563558
* Modified slightly to take element as this
* */
function selectText(element) {
    var doc = document
        , text = element
        , range, selection
        ;
    if (doc.body.createTextRange) {
        range = document.body.createTextRange();
        range.moveToElementText(text);
        range.select();
    } else if (window.getSelection) {
        selection = window.getSelection();
        range = document.createRange();
        range.selectNodeContents(text);
        selection.removeAllRanges();
        selection.addRange(range);
    }
}
