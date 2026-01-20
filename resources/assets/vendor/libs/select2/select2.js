import select2 from 'select2/dist/js/select2.full.min.js';
import 'select2/dist/js/i18n/id.js';


if (window.jQuery) {
            $('.select2').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#offcanvasAddUser')
            });
        }
