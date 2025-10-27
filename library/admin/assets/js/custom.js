(function ($) {
    "use strict";
    var mainApp = {
        dataTable_fun: function () {

            $('#dataTables-example').dataTable();

        },

    }
   
   
    $(document).ready(function () {
        mainApp.dataTable_fun();
    });
}(jQuery));


