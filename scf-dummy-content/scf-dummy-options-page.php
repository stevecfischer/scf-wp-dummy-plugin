<?php
// Delete options table entries ONLY when plugin deactivated AND deleted
function scfdc_delete_plugin_options() {
   delete_option('scfdc_options');
}

// Define default option settings
function scfdc_add_defaults() {
   $tmp = get_option('scfdc_options');
    if( !isset($tmp)||(!is_array($tmp))) {
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
      <div class="icon32" id="icon-options-general"><br /></div>
      <h2>SCF Dummy Content</h2>
      <p>Plugin to quickly populate your site with dummy content.</p>
      <p>How to use:<br />
        <ol>
            <li>First upload an image if you want to see featured images.</li>
            <li>Next either use the default editor text or insert your own.</li>
            <li>Next enter the number of posts you want to create for each post type.</li>
            <li>Then create the default title for each post. (** NOTE: the number of each post will be automatically added to each post title.)</li>
            <li>Lastly choose which taxonomies to create terms for and which post types to create posts for.</li>
            <li>Save the options before you execute.</li>
        </ol>
      </p>
      <form method="post" action="options.php">
         <?php settings_fields('scfdc_plugin_options'); ?>
         <?php $options = get_option('scfdc_options'); ?>

         <table class="form-table">
            <tr valign="top">
                 <td scope="row">Upload Image</td>
                 <td><label for="upload_image">
                 <input id="upload_image" type="text" size="36" name="scfdc_options[upload_image]" value="<?php echo $options['upload_image']; ?>" />
                 <input id="upload_image_button" type="button" value="Upload Image" />
                 <br />Enter an URL or upload an image for the banner.
                 </label></td>
            </tr>
          <tr>
               <td></td>
               <td>
               <?php
               echo '<img width="100px" src="'.$options['upload_image'].'" /> ';
               ?>
               </td>
          </tr>
          <tr>
               <td scope="row">Content to be added. <br /><span class="italic-link"><a rel="nofollow" href="http://html-ipsum.com">HTML Ipsum Generator</a></span></td>
               <td>
                  <?php
                     $args = array("textarea_name" => "scfdc_options[content]");
                     wp_editor( $options['content'], "scfdc_options[content]", $args );
                  ?>
                  <br />
               </td>
            </tr>
            <tr>
               <td scope="row">Number of Posts to Create</td>
               <td>
                  <input type="text" size="57" name="scfdc_options[num_post_create]" value="<?php echo $options['num_post_create']; ?>" />
               </td>
            </tr>
            <tr>
               <td scope="row">Post Title to use</td>
               <td>
                  <input type="text" size="57" name="scfdc_options[title]" value="<?php echo $options['title']; ?>" />
                  <span style="color:#666666;margin-left:2px;"><br />
                    Defaults to: "post_type name + number of post". Ex: "Page 1", "Page 2", "Page 3".<br />
                    Shortcode Option:<br />
                        - %%cpt%% <br />
                    Ex: "Project SCF %%cpt%%" results in "Project SCF Post 1", "Project SCF Post 2".</span>
               </td>
            </tr>
          <tr>
               <td scope="row">Taxonomy Title to use</td>
               <td>
                  <input type="text" size="57" name="scfdc_options[title_tax]" value="<?php echo $options['title_tax']; ?>" />
                    <span style="color:#666666;margin-left:2px;"><br />
                    Defaults to: "taxonomy name + number of term". Ex: "Category 1", "Category 2".<br />
                    Shortcode Option:<br />
                        - %%tax%% <br />
                    Ex: "Project SCF %%tax%%" results in "Project SCF Category 1", "Project SCF Category 2".</span>
               </td>
            </tr>
            <tr valign="top">
               <td scope="row">Custom Post Types</td>
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

                if(
                $scf_post_type->labels->name == 'Media'  ||
                $scf_post_type->labels->name == 'Revisions'  ||
                $scf_post_type->labels->name == 'Navigation Menu Items'
                  ){
                  // do nothing. I hate doing it this way!!!
                  }else{
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
                     }
               ?>
               </td>
            </tr>
            <tr valign="top">
               <td scope="row">Custom Taxonomies</td>
               <td>
               <?php
                  $scf_taxonomies = $scfdc->get_list_of_taxonomies();
                  foreach ($scf_taxonomies as $scf_taxonomy ) {
               if(
               $scf_taxonomy == 'post_tag' ||
               $scf_taxonomy == 'nav_menu' ||
               $scf_taxonomy == 'link_category' ||
               $scf_taxonomy == 'post_format'
               ){
               // do nothing. I hate doing it this way!!!
               }else{
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

      <form method="post" action="admin.php?page=scf-dummy-content/scf-dummy-options-page.php">
         <input type="hidden" name="execute" />
         <?php settings_fields('scfdc_plugin_options'); ?>
         <?php $options = get_option('scfdc_options'); ?>
         <p class="submit">
         <input type="submit" id="scf-execute-submit" name="scf_execute" class="button-primary" value="<?php _e('Execute') ?>" />
         </p>
      </form>

      <form method="post" action="admin.php?page=scf-dummy-content/scf-dummy-options-page.php">
         <input type="hidden" name="delete" />
         <?php settings_fields('scfdc_plugin_options'); ?>
         <?php $options = get_option('scfdc_options'); ?>
         <p class="submit">
         <input type="submit" id="scf-delete-submit" name="scf_delete" class="button-primary" value="<?php _e('Delete') ?>" />
         </p>
      </form>
   </div>
   <?php
}

