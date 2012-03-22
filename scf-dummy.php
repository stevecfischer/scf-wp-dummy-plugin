<?php
/**
 * Plugin Name: SCF Dummy Content
 *
 *
 */

/*!
 * @TODO
 * -create dummy taxonomy then related them to their respective CPT
 *  -steps to take
 *   get a list of registered taxonomies
 *   QUESTION DO IT RELATE POST TO TAX UPON CREATION OR AS A SEPERATE
 *   FUNCTION????????
 *
 *
 *
 *
 *
 *
 * \author Steve (3/20/2012)
 */


class  scf_dummy{

   public $_cpt = array('page', 'post', 'products' );
   public $_tax = array();
   public $_options = array(
         'title' => 'Post Number-4',
         'content' => 'content here',
         'post_type' => 'products'
      );

   public $_custom_options = array();

   function __construct(){
      add_action( 'admin_init', array($this,'requires_wordpress_version'), 1 );
      if (isset( $_POST['scf_execute']) ) {
         add_action( 'init', array($this,'get_options') );
         add_action( 'init', array($this,'active_cpt') );
         add_action( 'init', array($this,'set_taxonomies') );
      }
   }

   function create_posts($args){
      $local_options = $this->_custom_options;

      $title_helper_pattern = "/%%[\s\S]*?%%/";

      if( preg_match($title_helper_pattern, $local_options['title'], $matches) ){
         $title = preg_replace($title_helper_pattern, $args, $local_options['title']);
      }else{
         $title = $local_options['title'];
      }

      // Create post object
      for ($i=1; $i<=$local_options['num_post_create']; $i++) {
           $my_post = array(
              'post_title' => $title.' '.$i,
              'post_content' => $local_options['content'],
              'post_status' => 'publish',
              'post_type' => $args
           );

           if(! $new_id = wp_insert_post( $my_post ) ){
              die('cannot create post');
           }

            $filename = '2012/03/testdenver.jpg';

            $wp_filetype = wp_check_filetype(basename($filename), null );
            $wp_upload_dir = wp_upload_dir();
               $attachment = array(
                 'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $filename ),
                 'post_mime_type' => $wp_filetype['type'],
                 'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                 'post_content' => '',
                 'post_status' => 'inherit'
                  );
        $attach_id = wp_insert_attachment( $attachment, $filename, $new_id );
        // you must first include the image.php file
        // for the function wp_generate_attachment_metadata() to work
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        update_post_meta($new_id,'_thumbnail_id',$attach_id);
      }
   }

   function active_cpt(){
      $local_options = $this->_custom_options;
      foreach($local_options['cpt'] as $k => $v){
         $this->create_posts($k);
      }
   }

   function scf_get_registered_post_types(){
      $args=array(
         'public'   => true,
         '_builtin' => true
      );
      $output = 'objects'; // names or objects
      $post_types=get_post_types('',$output);
      foreach ($post_types as $post_type ) {
        echo '<p>'. $post_type->name . '</p>';
      }
   }

   function scf_registered_post_types(){
      $args=array(
         'public'   => true,
         '_builtin' => true
      );
      $output = 'objects'; // names or objects
      $post_types=get_post_types('',$output);
      return $post_types;
   }

   function get_options(){
      $this->_custom_options = $options = get_option('scfdc_options');
   }

   function create_featured_image(){
      $attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
      $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
      wp_update_attachment_metadata( $attach_id,  $attach_data );
   }

   function requires_wordpress_version() {
      global $wp_version;
      $plugin = plugin_basename( __FILE__ );
      $plugin_data = get_plugin_data( __FILE__, false );

      if ( version_compare($wp_version, "3.3", "<" ) ) {
         if( is_plugin_active($plugin) ) {
            deactivate_plugins( $plugin );
            wp_die( "'".$plugin_data['Name']."' requires WordPress 3.3 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
         }
      }
   }

   function get_list_of_taxonomies(){
      $tax_list = get_taxonomies();

      foreach($tax_list as $tax){
         $parent_term = term_exists( 'dave' ); // array is returned if taxonomy is given
         print_r($parent_term);
         $parent_term_id = $parent_term['term_id']; // get numeric term id
        // echo 'hhh'  ;
        // echo $parent_term_id;
      }
      return $tax_list;
   }

   function set_taxonomies(){
      /*#

        Notes
        Hooks Used

         This function calls five different hooks:

             create_term
             create_taxonomy
             term_id_filter
             created_term
             created_$taxonomy

         All five hooks are passed the term id and taxonomy id as parameters.

        */



      $parent_term = term_exists( 'types' ); // array is returned if taxonomy is given
      $parent_term_id = $parent_term['term_id']; // get numeric term id
      wp_insert_term(
        'Apple', // the term
        'product', // the taxonomy
        array(
          'description'=> 'A yummy apple.',
          'slug' => 'apple',
          'parent'=> $parent_term_id
        )
      );
   }

}
/*===============================
END OF CLASS
===============================*/


// Set-up Action and Filter Hooks
register_activation_hook(__FILE__, 'scfdc_add_defaults');
register_uninstall_hook(__FILE__, 'scfdc_delete_plugin_options');
add_action('admin_init', 'scfdc_init' );
add_action('admin_menu', 'scfdc_add_options_page');
add_filter( 'plugin_action_links', 'scfdc_plugin_action_links', 10, 2 );

// Delete options table entries ONLY when plugin deactivated AND deleted
function scfdc_delete_plugin_options() {
   delete_option('scfdc_options');
}

// Define default option settings
function scfdc_add_defaults() {
   $tmp = get_option('scfdc_options');
    if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
      delete_option('scfdc_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
      $arr = array(  "chk_button1" => "1",
                  "chk_button3" => "1",
                  "textarea_one" => "This type of control allows a large amount of information to be entered all at once. Set the 'rows' and 'cols' attributes to set the width and height.",
                  "textarea_two" => "This text area control uses the TinyMCE editor to make it super easy to add formatted content.",
                  "textarea_three" => "Another TinyMCE editor! It is really easy now in WordPress 3.3 to add one or more instances of the built-in WP editor.",
                  "txt_one" => "Enter whatever you like here..",
                  "drp_select_box" => "four",
                  "chk_default_options_db" => "",
                  "rdo_group_one" => "one",
                  "rdo_group_two" => "two"
      );
      update_option('scfdc_options', $arr);
   }
}

// Init plugin options to white list our options
function scfdc_init(){
   register_setting( 'scfdc_plugin_options', 'scfdc_options', 'scfdc_validate_options' );
}

// Add menu page
function scfdc_add_options_page() {
   add_options_page('SCF Dummy Content Options Page', 'SCF Dummy Content', 'manage_options', __FILE__, 'scfdc_render_form');
}

// Render the Plugin options form
function scfdc_render_form() {
   ?>
   <div class="wrap">
      <?php
      $scfdc = new scf_dummy(); // call our class
      ?>
      <!-- Display Plugin Icon, Header, and Description -->
      <div class="icon32" id="icon-options-general"><br></div>
      <h2>SCF Dummy Content</h2>
      <p>Plugin will populate your site with dummy content</p>
      <?php
         echo '<img src="' .plugins_url( 'testdenver.jpg' , __FILE__ ). '" > ';
      ?>
      <!-- Beginning of the Plugin Options Form -->
      <form method="post" action="options.php">
         <?php settings_fields('scfdc_plugin_options'); ?>
         <?php $options = get_option('scfdc_options'); ?>

         <!-- Table Structure Containing Form Controls -->
         <!-- Each Plugin Option Defined on a New Table Row -->
         <table class="form-table">
            <!-- Text Area Using the Built-in WP Editor -->
            <tr>
               <th scope="row">Content to be added</th>
               <td>
                  <?php
                     $args = array("textarea_name" => "scfdc_options[content]");
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

            <!-- Checkbox Buttons -->
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

            <!-- Checkbox Buttons -->
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

      <form method="post" action="admin.php?page=scf-dummy/scf-dummy.php">
         <input type="hidden" name="execute" />
         <?php settings_fields('scfdc_plugin_options'); ?>
         <?php $options = get_option('scfdc_options'); ?>
         <p class="submit">
         <input type="submit" name="scf_execute" class="button-primary" value="<?php _e('Execute') ?>" />
         </p>
      </form>
   </div>
   <?php
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function scfdc_validate_options($input) {
    // strip html from textboxes
   $input['textarea_one'] =  wp_filter_nohtml_kses($input['textarea_one']); // Sanitize textarea input (strip html tags, and escape characters)
   $input['txt_one'] =  wp_filter_nohtml_kses($input['txt_one']); // Sanitize textbox input (strip html tags, and escape characters)
   return $input;
}

// Display a Settings link on the main Plugins page
function scfdc_plugin_action_links( $links, $file ) {
   if ( $file == plugin_basename( __FILE__ ) ) {
      $scfdc_links = '<a href="'.get_admin_url().'options-general.php?page=scf-dummy/scf-dummy.php">'.__('Settings').'</a>';
      // make the 'Settings' link appear first
      array_unshift( $links, $scfdc_links );
   }
   return $links;
}

function scfdc_add_content($text) {
   $options = get_option('scfdc_options');
   $select = $options['drp_select_box'];
   $text = "<p style=\"color: #777;border:1px dashed #999; padding: 6px;\">Select box Plugin option is: {$select}</p>{$text}";
   return $text;
}
add_filter( "the_content", "scfdc_add_content" );

$scf_dummy = new scf_dummy(); // call our class

