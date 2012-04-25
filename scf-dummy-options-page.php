<?php


// Delete options table entries ONLY when plugin deactivated AND deleted
function scfdc_delete_plugin_options() {
   delete_option('scfdc_options');
}

// Define default option settings
function scfdc_add_defaults() {
   $tmp = get_option('scfdc_options');
    if( !isset($tmp)||(!is_array($tmp))) {
       die('here');
      delete_option('scfdc_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
      $arr = array(
                  "content" => "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.",
                  "num_post_create" => "3",
         );
      update_option('scfdc_options', $arr);
   }
}

function scfdc_init(){
   register_setting( 'scfdc_plugin_options', 'scfdc_options', 'scfdc_validate_options' );
}

function scfdc_add_options_page() {
   add_options_page('SCF Dummy Content Options Page', 'SCF Dummy Content', 'manage_options', __FILE__, 'scfdc_render_form');
}




function scfdc_render_form() {
   ?>
   <div class="wrap">
      <?php
      $scfdc = new scf_dummy(); // call our class
      ?>
      <div class="icon32" id="icon-options-general"><br></div>
      <h2>SCF Dummy Content</h2>
      <p>Plugin will populate your site with dummy content</p>
      <form method="post" action="options.php">
         <?php settings_fields('scfdc_plugin_options'); ?>
         <?php $options = get_option('scfdc_options'); ?>
         <?php
            echo '<img src="'.$options['upload_image'].'" > ';
         ?>
         <table class="form-table">
            <tr valign="top">
            <th scope="row">Upload Image</th>
            <td><label for="upload_image">
            <input id="upload_image" type="text" size="36" name="scfdc_options[upload_image]" value="<?php echo $options['upload_image']; ?>" />
            <input id="upload_image_button" type="button" value="Upload Image" />
            <br />Enter an URL or upload an image for the banner.
            </label></td>
            </tr>
            <tr>
               <th scope="row">Content to be added</th>
               <td>
                  <?php
                     $args = array("textarea_name" => "scfdc_options[content]",'editor_class'=>'width:200px;');
                     wp_editor( $options['content'], "scfdc_options[content]", $args );
                  ?>
                  <br />
               </td>
            </tr>
            <tr>
               <th scope="row">Number of Posts to Create</th>
               <td>
                  <input type="text" size="57" name="scfdc_options[num_post_create]" value="<?php echo $options['num_post_create']; ?>" />
               </td>
            </tr>
            <tr>
               <th scope="row">Title to use</th>
               <td>
                  <input type="text" size="57" name="scfdc_options[title]" value="<?php echo $options['title']; ?>" />
                  <span style="color:#666666;margin-left:2px;">You can title your posts here.  Use %%cpt%% to add the custom post type to each post. Ex "My %%cpt%%" could be "My post" or "My page" </span>
               </td>
            </tr>
            <tr valign="top">
               <th scope="row">Custom Post Types</th>
               <td>
               <?php

                  $scf_post_types = $scfdc->scf_registered_post_types();
                  foreach ($scf_post_types as $scf_post_type ) {
                     if( $scf_post_type->labels->name == 'Posts' ){
                        $pt = 'post';
                     }else if($scf_post_type->labels->name == 'Pages'){
                        $pt = 'page';
                     }else{
                        $pt = $scf_post_type->rewrite[slug];
                     }
                        echo '<label>
                          <input name="scfdc_options[cpt]['.$pt.']"
                              type="checkbox"
                              value="1" ';

                              if(isset($options['cpt'][$pt])){
                                 checked('1',$options['cpt'][$pt]);
                              }

                           echo '/>
                             '.$scf_post_type->labels->name.'
                        </label>
                        <br />';
                     }
               ?>
               </td>
            </tr>
            <tr valign="top">
               <th scope="row">Custom Taxonomies</th>
               <td>
               <?php
                  $scf_taxonomies = $scfdc->get_list_of_taxonomies();
                  foreach ($scf_taxonomies as $scf_taxonomy ) {
                     echo '<label>
                          <input name="scfdc_options[tax]['.$scf_taxonomy.']"
                              type="checkbox"
                              value="1" ';

                              if(isset($options['tax'][$scf_taxonomy])){
                                 checked('1',$options['tax'][$scf_taxonomy]);
                              }

                           echo '/>
                             '.$scf_taxonomy.'
                        </label>
                        <br />';
                     }
               ?>
               </td>
            </tr>

            <tr><td colspan="2"><div style="margin-top:10px;"></div></td></tr>

         </table>
         <p class="submit">
         <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
         </p>
      </form>

      <form method="post" action="admin.php?page=scf-dummy/scf-dummy-options-page.php">
         <input type="hidden" name="execute" />
         <?php settings_fields('scfdc_plugin_options'); ?>
         <?php $options = get_option('scfdc_options'); ?>
         <p class="submit">
         <input type="submit" name="scf_execute" class="button-primary" value="<?php _e('Execute') ?>" />
         </p>
      </form>

     <!-- <form method="post" action="admin.php?page=scf-dummy/scf-dummy-options-page.php">
         <input type="hidden" name="delete" />
         <?php settings_fields('scfdc_plugin_options'); ?>
         <?php $options = get_option('scfdc_options'); ?>
         <p class="submit">
         <input type="submit" name="scf_delete" class="button-primary" value="<?php _e('Delete') ?>" />
         </p>
      </form> -->
   </div>
   <?php
}

