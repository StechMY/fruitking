$(".sidebar-menu li a").click(function () {
    $('.sidebar-menu li:not(.treeview) > a').on('click', function () {
        $("body").removeClass("sidebar-open");
    });

});