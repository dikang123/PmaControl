/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$("#compare_main-id_mysql_server__original").change(function () {
    data = $(this).val();
    $("#compare_main-database__original").load(GLIAL_LINK+"compare/getDatabaseByServer/" + data + "/ajax>true/");
});

$("#compare_main-id_mysql_server__compare").change(function () {
    data = $(this).val();
    $("#compare_main-database__compare").load(GLIAL_LINK+"compare/getDatabaseByServer/" + data + "/ajax>true/");
});



/*
$("#compare_main-database").change(function () {
    data = $(this).val();

    var optionSelected = $(this).find("option:selected");
    var valueSelected = optionSelected.val();
    //var textSelected   = optionSelected.text();

    $(".table").find("select.schema").each(function () {
        $(this).val(valueSelected);
    });
    
    server = $("#compare_main-id_mysql_server").val();
    schema = data;
    
    $(".table").find("select.tables").each(function () {
        $(this).load(GLIAL_LINK+"compare/getTableByDatabase/" + schema + "/id_mysql_server:" + server + "/ajax>true/");
    });
    $("#compare_main-main_table").load(GLIAL_LINK+"compare/getTableByDatabase/" + schema + "/id_mysql_server:" + server + "/ajax>true/");
});

$(".table").on('change','.constraint.schema', function() {
    data = $(this).val();
    server = $("#compare_main-id_mysql_server").val();

    $(this).parents('.compare-line').find(".constraint.tables").load(GLIAL_LINK+"compare/getTableByDatabase/" + data + "/id_mysql_server:" + server + "/ajax>true/");
});

$(".table").on('change','.constraint.tables', function() {
    table = $(this).val();
    server = $("#compare_main-id_mysql_server").val();
    schema = $(this).parents('.compare-line').find(".constraint.schema").val();
    $(this).parents('.compare-line').find(".constraint.column").load(GLIAL_LINK+"compare/getColumnByTable/" + table + "/id_mysql_server:" + server + "/schema:" + schema + "/ajax>true/");
});

$(".table").on('change','.referenced.schema', function() {
    data = $(this).val();
    server = $("#compare_main-id_mysql_server").val();

    $(this).parents('.compare-line').find(".referenced.tables").load(GLIAL_LINK+"compare/getTableByDatabase/" + data + "/id_mysql_server:" + server + "/ajax>true/");
});

$(".table").on('change','.referenced.tables', function() {
    data = $(this).val();
    server = $("#compare_main-id_mysql_server").val();
    schema = $(this).parents('.compare-line').find(".referenced.schema").val();
    $(this).parents('.compare-line').find(".referenced.column").load(GLIAL_LINK+"compare/getColumnByTable/" + data + "/id_mysql_server:" + server + "/schema:" + schema + "/ajax>true/");
});

$("#compare_foreign_key-referenced_table").change(function () {
    data = $(this).val();
    server = $("#compare_main-id_mysql_server").val();
    schema = $("#compare_foreign_key-referenced_schema").val();
    $("#compare_foreign_key-referenced_column").load(GLIAL_LINK+"compare/getColumnByTable/" + data + "/id_mysql_server:" + server + "/schema:" + schema + "/ajax>true/");
});
*/