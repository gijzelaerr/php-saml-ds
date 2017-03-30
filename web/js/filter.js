/**
 * Copyright 2017 François Kooman <fkooman@tuxed.net>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
