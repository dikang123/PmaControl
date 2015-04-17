/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 $(".clickable-row").click(function() {
 window.document.location = $(this).data("href");
 });
 */

$(document).ready(function () {
    $("tr.clickable-row").click(function () {

        var selected = $(this).hasClass("highlight");
        $("tr.clickable-row").removeClass("highlight");
        if (!selected)
            $(this).addClass("highlight");
    });
});