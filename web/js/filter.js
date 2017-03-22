document.addEventListener('DOMContentLoaded', function(event) {
    document.getElementById('search').style.display = 'block';
    var input = document.getElementById('search');
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
