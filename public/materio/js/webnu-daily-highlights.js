(function ($) {
    'use strict';

    var $rows = $('#wn-daily-highlights-rows');
    if (!$rows.length) {
        return;
    }

    var maxRows = 3;

    function rowCount() {
        return $rows.find('.wn-daily-highlight-row').length;
    }

    function reindexRows() {
        $rows.find('.wn-daily-highlight-row').each(function (i) {
            $(this).find('[name^="highlights["]').each(function () {
                var name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/highlights\[\d+]/, 'highlights[' + i + ']'));
                }
            });
        });
        $('#wn-add-highlight').prop('disabled', rowCount() >= maxRows);
    }

    function syncTextField($row) {
        var type = $row.find('.wn-highlight-type').val();
        var $wrap = $row.find('.wn-highlight-text-wrap').length
            ? $row.find('.wn-highlight-text-wrap')
            : $row.find('.col-12').last();
        var $field = $row.find('.wn-highlight-text');
        var val = $field.val() || '';
        var $label = $row.find('.wn-highlight-text-label');

        if (type === 'menu_del_dia') {
            $label.text('Platos (una línea cada uno)');
            if ($field.is('input')) {
                var $ta = $('<textarea class="form-control wn-highlight-text" rows="3" maxlength="2000"></textarea>');
                $ta.attr('name', $field.attr('name'));
                $ta.val(val);
                $field.replaceWith($ta);
            }
        } else {
            $label.text('Texto');
            if ($field.is('textarea')) {
                var lines = ($field.val() || '').split('\n')[0] || '';
                var $inp = $('<input type="text" class="form-control wn-highlight-text" maxlength="500">');
                $inp.attr('name', $field.attr('name'));
                $inp.val(lines);
                $field.replaceWith($inp);
            }
        }
    }

    $('#wn-add-highlight').on('click', function () {
        if (rowCount() >= maxRows) {
            return;
        }
        var tpl = $('#wn-daily-highlight-row-template').html();
        if (!tpl) {
            return;
        }
        var idx = rowCount();
        tpl = tpl.replace(/__INDEX__/g, String(idx));
        $rows.append(tpl);
        reindexRows();
    });

    $rows.on('click', '.wn-remove-highlight', function () {
        if (rowCount() <= 1) {
            $(this).closest('.wn-daily-highlight-row').find('input, textarea, select').val('');
            return;
        }
        $(this).closest('.wn-daily-highlight-row').remove();
        reindexRows();
    });

    $rows.on('change', '.wn-highlight-type', function () {
        syncTextField($(this).closest('.wn-daily-highlight-row'));
    });

    $rows.find('.wn-daily-highlight-row').each(function () {
        syncTextField($(this));
    });

    reindexRows();
})(jQuery);
