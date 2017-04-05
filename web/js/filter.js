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
    document.querySelector('form.filter').addEventListener('submit', function(e) {
        e.preventDefault();
    });

    document.querySelector('form.filter input#filter').addEventListener('keyup', function(e) {
        var filter = this.value.toUpperCase();
        var entries = document.querySelectorAll('ul#disco li');
        entries.forEach(function(entry) {
            var displayName = entry.querySelector('form.entity button span').innerHTML;
            var keywords = entry.querySelector('form.entity button').dataset.keywords;

            if (displayName.toUpperCase().indexOf(filter) !== -1) {
                entry.style.display = 'list-item';
            } else if (keywords.toUpperCase().indexOf(filter) !== -1) {
                entry.style.display = 'list-item';
            } else {
                entry.style.display = 'none';
            }
        });
    });
});
