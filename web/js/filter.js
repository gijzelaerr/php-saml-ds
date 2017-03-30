document.addEventListener('DOMContentLoaded', function(event) {
    // disable "classic" form submit when JS is enabled
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
    }, true);

    document.getElementById('filter').addEventListener('keyup', function(e) {
        var filter = this.value.toUpperCase();
        // still clean this mess...
        var lis = document.getElementById('disco').getElementsByTagName('li');
        for (var i = 0; i < lis.length; i++) {
            var name = false;
            if(lis[i].getElementsByClassName('name')) {
                name = lis[i].getElementsByClassName('name')[0].innerHTML;
            }
            var keywords = lis[i].getElementsByClassName('name')[0].dataset.keywords;
            if (name && name.toUpperCase().indexOf(filter) != -1) {
                lis[i].style.display = 'list-item';
            } else if(keywords && keywords.toUpperCase().indexOf(filter) != -1) {
                lis[i].style.display = 'list-item';
            } else {
                lis[i].style.display = 'none';
            }
        }
    }, true);
});
