import $ from 'jquery';
import 'select2';

window.initSelect2 = function (scope = document) {
    $(scope).find('.select2').each(function () {
        const el = $(this);

        if (el.hasClass('select2-hidden-accessible')) return;

        el.select2({
            width: '100%',
            placeholder: el.data('placeholder') || 'Pilih data',
            allowClear: el.data('clear') ?? true,
            dropdownParent: el.closest('.modal').length
                ? el.closest('.modal')
                : $(document.body)
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initSelect2();
});
