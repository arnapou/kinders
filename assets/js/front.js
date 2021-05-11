
$(document).ready(function () {
    $("#menu .more_menu_items").click(function(e){
        e.preventDefault();
        $(this).parent().hide();
        $(this).parent().nextAll().show();
        return false;
    });
});