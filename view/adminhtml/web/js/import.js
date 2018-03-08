document.addEventListener(
    'DOMContentLoaded',
    function () {
        document.getElementById('select_deselect')
            .addEventListener('click', function () {
                var checkboxes = document.querySelectorAll('.data-grid input[type=checkbox]');
                if (this.dataset.type !== 'select') {
                    for(var i = 0; i < checkboxes.length; i++) {
                        checkboxes[i].checked = false;
                    }
                    this.dataset.type = 'select';
                    this.getElementsByTagName('span')[0].innerHTML = 'Select all';
                } else {
                    for(var i = 0; i < checkboxes.length; i++) {
                        checkboxes[i].checked = true;
                    }
                    this.dataset.type = 'deselect';
                    this.getElementsByTagName('span')[0].innerHTML = 'Deselect all';
                }
            });
    },
    false
);
