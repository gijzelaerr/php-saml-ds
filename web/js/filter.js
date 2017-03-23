document.addEventListener('DOMContentLoaded', function(event) {
    // XXX disable standard form submit for filter
    // XXX clean this mess up, use modern ways to bind events

    var input = document.getElementById('filter');
    input.onkeyup = function () {
        var filter = input.value.toUpperCase();
        var lis = document.getElementsByTagName('li');
        for (var i = 0; i < lis.length; i++) {
            var name = lis[i].getElementsByClassName('name')[0].innerHTML;
            var keywords = lis[i].getElementsByClassName('name')[0].dataset.keywords;
            if (name.toUpperCase().indexOf(filter) != -1) {
                lis[i].style.display = 'list-item';
            } else if(keywords.toUpperCase().indexOf(filter) != -1) {
                lis[i].style.display = 'list-item';
            } else {
                lis[i].style.display = 'none';
            }
        }
    }
});
