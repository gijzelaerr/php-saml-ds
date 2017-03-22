document.addEventListener('DOMContentLoaded', function(event) {
    var options = {
        valueNames: [ 
            'name',
           { attr: 'data-keywords', name: 'keywords' } 
        ]
    };

    var entityList = new List('entity-list', options);
});
