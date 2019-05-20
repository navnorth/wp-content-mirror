jQuery(document).ready(function ($) {
   $('#csv_upload').click(function() {
   $(".results_table").hide();
   $(".page_count").text();
   $('#page_result > tbody > tr > td').parent('tr').empty();
    if( document.getElementById("fileToUpload").files.length == 0 ){
      $(".oese-csv-importer .error_message").text("Please select a csv file to upload")
    }
    else{
        $(".ajaxload").show();
        $(".error_message").text("");
        var file_data = $('#fileToUpload').prop('files')[0];   
        var form_data = new FormData();                  
        form_data.append('file', file_data);
        form_data.append('action', 'my_action');   
        $.ajax({
                  type : "POST", 
                  url: ajaxurl,
                  data:form_data,
                  contentType: false,
                  enctype: 'multipart/form-data',
                  processData: false,
                  success:function(data) {
                    console.log(data);
                    $(".ajaxload").hide();
                    if(data){  
                      $(".results_table").show();
                      $(".page_count").text(data.length+" pages has been created");
                      data.forEach(function(value) {
                        $("#page_result").append("<tr><td>"+value.page_title+"</td><td><a href="+value.edit_link+">Edit</a></td></tr>")
                      });
                    }  
                  },
                  error: function(errorThrown){
                      console.log(errorThrown);
                  }
              });
      }
    
    });

});