
$('.sidebar-menu li:not(.treeview) > a').on('click', function () {
    console.log('anc');
    $("body").removeClass("sidebar-open");
});