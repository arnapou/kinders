
window.searchFilterReset = function () {
    $('#searchFilter').val('');
    $('#searchFilterForm').submit();
};

$.fn.select2.defaults.set("theme", "bootstrap");


$.fn.select2entityAjax = function (action) {
    var action = action || {};
    var template = function (item) {
        var img = item.file || null;
        if (!img) {
            if (item.element && item.element.dataset.file) {
                img = item.element.dataset.file;
            } else {
                return item.text;
            }
        }
        return $(
            '<span><img src="' + img + '" class="img-circle img-sm"> ' + item.text + '</span>'
        );
    };
    this.select2entity($.extend(action, {
        templateResult: template,
        templateSelection: template
    }));
    return this;
};


$(document).ready(function () {
    $("select.autocomplete").select2();
    $('.select2entity').select2entityAjax();

    $('td .action-delete').click(function (e) {
        var url = $(this).data('href');
        bootbox.confirm("Do you really want to delete this objet?", function (result) {
            if (result) {
                $.post(url, function () {
                    window.location.reload();
                });
            }
        });
        e.preventDefault();
        return false;
    });

    $('.custom-file-input').on('change', function () {
        var filename = $(this).val();
        var m = filename.match(/^.*[\/\\]([^\/\\]+)(\.[^\.]+)$/);
        if (m.length == 3) {
            $(this).parent().find('label').html(m[1] + '<span style="color: #cccccc">' + m[2] + '</span>');
        } else {
            $(this).parent().find('label').text(filename);
        }
    });

    $('input[name*="year"], input[name*="Year"]').click(function () {
        $(this).select();
    });
});
