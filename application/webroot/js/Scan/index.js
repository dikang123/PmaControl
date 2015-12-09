$(document).ready(function () {
    $("#check-all").click(function () {
        $(".check-box").prop('checked', $(this).prop('checked'));
    });
});