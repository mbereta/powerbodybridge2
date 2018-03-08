document.addEventListener(
    'DOMContentLoaded',
    function () {
        var checkboxes = document.querySelectorAll('.data-grid input[type=checkbox]');
        for(var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].addEventListener('click', function () {
                if (this.dataset.first == 1) {
                    for(var j = 0; j < checkboxes.length; j++) {
                        checkboxes[j].checked = this.checked;
                    }
                    return;
                }
                for(var j = 0; j < checkboxes.length; j++) {
                    if (checkboxes[j].dataset.parent == this.dataset.base) {
                        checkboxes[j].checked = this.checked;
                    }
                }
            });
        }
    },
    false
);
