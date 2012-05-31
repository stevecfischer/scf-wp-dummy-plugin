jQuery(document).ready(function($) {

     jQuery('#upload_image_button').click(function() {
          formfield = jQuery('#upload_image').attr('name');
          tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
          return false;
     });

     window.send_to_editor = function(html) {
          imgurl = jQuery('img',html).attr('src');
          jQuery('#upload_image').val(imgurl);
          tb_remove();
     }

     $('#scf-execute-submit').click(function(e){
          var answer = confirm ("Are you sure?");
          if (answer)
               return true;
          else
               return false;
     });

     $('#scf-delete-submit').click(function(e){
          var answer = confirm ("You are about to permanently delete all posts terms created by this plugin.  Are you sure?");
          if (answer)
               return true;
          else
               return false;
     });
});
