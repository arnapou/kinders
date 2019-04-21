/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/admin.css');
require('../css/bootstrap.scss');
require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');
require('select2/dist/js/select2.full.js');
require('select2/dist/css/select2.css');
require('select2-bootstrap-theme/dist/select2-bootstrap.css');
require('../../public/bundles/tetranzselect2entity/js/select2entity.js');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = require('jquery');

// the bootstrap module doesn't export/return anything
require('bootstrap');

// window.$ = $;
// window.jQuery = $;

window.searchFilterReset = function () {
    $('#searchFilter').val('');
    $('#searchFilterForm').submit();
};

$.fn.select2.defaults.set( "theme", "bootstrap" );


$.fn.select2entityAjax = function(action) {
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
    $( "select.autocomplete" ).select2();
    $('.select2entity').select2entityAjax();
});

