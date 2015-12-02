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



$( "#vers" ).click(function() {
  $( ".vers2" ).toggle();
  $( ".vers1" ).toggle();
  

});



