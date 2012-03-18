<?php
/**
 * Plugin Name: SCF Dummy Content
 * Description: Now On GIT
 *
 */

 
this is the trunk edition i think
class  scf_dummy{ffffff

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

      }
   }

   function create_posts($args){

      $local_options = $this->_custom_options;

      // Create post object
      for ($i=1; $i<=$local_options['num_post_create']; $i++) {
           $my_post = array(
              'post_title' => $local_options['title'].' '.$i,
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
      }
   }

   function active_cpt(){
      $local_cpt = $this->_cpt;

      foreach($local_cpt as $cpt){
         $this->create_posts($cpt);
      }

   }

   function get_options(){
      $this->_custom_options = $options = get_option('posk_options');
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
}/*
===============================
END OF CLASS
===============================*/



// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:
// ------------------------------------------------------------------------
// HOOKS TO SETUP DEFAULT PLUGIN OPTIONS, HANDLE CLEAN-UP OF OPTIONS WHEN
// PLUGIN IS DEACTIVATED AND DELETED, INITIALISE PLUGIN, ADD OPTIONS PAGE.
// ------------------------------------------------------------------------

// Set-up Action and Filter Hooks
register_activation_hook(__FILE__, 'posk_add_defaults');
register_uninstall_hook(__FILE__, 'posk_delete_plugin_options');
add_action('admin_init', 'posk_init' );
add_action('admin_menu', 'posk_add_options_page');
add_filter( 'plugin_action_links', 'posk_plugin_action_links', 10, 2 );

// --------------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__, 'posk_delete_plugin_options')
// --------------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE USER DEACTIVATES AND DELETES THE PLUGIN. IT SIMPLY DELETES
// THE PLUGIN OPTIONS DB ENTRY (WHICH IS AN ARRAY STORING ALL THE PLUGIN OPTIONS).
// --------------------------------------------------------------------------------------

// Delete options table entries ONLY when plugin deactivated AND deleted
function posk_delete_plugin_options() {
   delete_option('posk_options');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_activation_hook(__FILE__, 'posk_add_defaults')
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE PLUGIN IS ACTIVATED. IF THERE ARE NO THEME OPTIONS
// CURRENTLY SET, OR THE USER HAS SELECTED THE CHECKBOX TO RESET OPTIONS TO THEIR
// DEFAULTS THEN THE OPTIONS ARE SET/RESET.
//
// OTHERWISE, THE PLUGIN OPTIONS REMAIN UNCHANGED.
// ------------------------------------------------------------------------------

// Define default option settings
function posk_add_defaults() {
   $tmp = get_option('posk_options');
    if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
      delete_option('posk_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
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
      update_option('posk_options', $arr);
   }
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_init', 'posk_init' )
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_init' HOOK FIRES, AND REGISTERS YOUR PLUGIN
// SETTING WITH THE WORDPRESS SETTINGS API. YOU WON'T BE ABLE TO USE THE SETTINGS
// API UNTIL YOU DO.
// ------------------------------------------------------------------------------

// Init plugin options to white list our options
function posk_init(){
   register_setting( 'posk_plugin_options', 'posk_options', 'posk_validate_options' );
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_menu', 'posk_add_options_page');
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_menu' HOOK FIRES, AND ADDS A NEW OPTIONS
// PAGE FOR YOUR PLUGIN TO THE SETTINGS MENU.
// ------------------------------------------------------------------------------

// Add menu page
function posk_add_options_page() {
   add_options_page('Plugin Options Starter Kit Options Page', 'Plugin Options Starter Kit', 'manage_options', __FILE__, 'posk_render_form');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION SPECIFIED IN: add_options_page()
// ------------------------------------------------------------------------------
// THIS FUNCTION IS SPECIFIED IN add_options_page() AS THE CALLBACK FUNCTION THAT
// ACTUALLY RENDER THE PLUGIN OPTIONS FORM AS A SUB-MENU UNDER THE EXISTING
// SETTINGS ADMIN MENU.
// ------------------------------------------------------------------------------

// Render the Plugin options form
function posk_render_form() {
   ?>
   <div class="wrap">

      <!-- Display Plugin Icon, Header, and Description -->
      <div class="icon32" id="icon-options-general"><br></div>
      <h2>Plugin Options Starter Kit</h2>
      <p>Below is a collection of sample controls you can use in your own Plugins. Or, you can analyse the code and learn how all the most common controls can be added to a Plugin options form. See the code for more details, it is fully commented.</p>

      <!-- Beginning of the Plugin Options Form -->
      <form method="post" action="options.php">
         <?php settings_fields('posk_plugin_options'); ?>
         <?php $options = get_option('posk_options'); ?>

         <!-- Table Structure Containing Form Controls -->
         <!-- Each Plugin Option Defined on a New Table Row -->
         <table class="form-table">
            <!-- Text Area Using the Built-in WP Editor -->
            <tr>
               <th scope="row">Content to be added</th>
               <td>
                  <?php
                     $args = array("textarea_name" => "posk_options[content]");
                     wp_editor( $options['content'], "posk_options[content]", $args );
                  ?>
                  <br /><span style="color:#666666;margin-left:2px;">Add a comment here to give extra information to Plugin users</span>
               </td>
            </tr>
            <tr>
               <th scope="row">Number of Posts to Create</th>
               <td>
                  <input type="text" size="57" name="posk_options[num_post_create]" value="<?php echo $options['num_post_create']; ?>" />
               </td>
            </tr>
            <tr>
               <th scope="row">Title to use</th>
               <td>
                  <input type="text" size="57" name="posk_options[title]" value="<?php echo $options['title']; ?>" />
               </td>
            </tr>

            <!-- Checkbox Buttons -->
            <tr valign="top">
               <th scope="row">Group of Checkboxes</th>
               <td>
                  <!-- First checkbox button -->
                  <label><input name="posk_options[chk_button1]" type="checkbox" value="1" <?php if (isset($options['chk_button1'])) { checked('1', $options['chk_button1']); } ?> /> Checkbox #1</label><br />

                  <!-- Second checkbox button -->
                  <label><input name="posk_options[chk_button2]" type="checkbox" value="1" <?php if (isset($options['chk_button2'])) { checked('1', $options['chk_button2']); } ?> /> Checkbox #2 <em>(useful extra information can be added here)</em></label><br />

                  <!-- Third checkbox button -->
                  <label><input name="posk_options[chk_button3]" type="checkbox" value="1" <?php if (isset($options['chk_button3'])) { checked('1', $options['chk_button3']); } ?> /> Checkbox #3 <em>(useful extra information can be added here)</em></label><br />

                  <!-- Fourth checkbox button -->
                  <label><input name="posk_options[chk_button4]" type="checkbox" value="1" <?php if (isset($options['chk_button4'])) { checked('1', $options['chk_button4']); } ?> /> Checkbox #4 </label><br />

                  <!-- Fifth checkbox button -->
                  <label><input name="posk_options[chk_button5]" type="checkbox" value="1" <?php if (isset($options['chk_button5'])) { checked('1', $options['chk_button5']); } ?> /> Checkbox #5 </label>
               </td>
            </tr>

            <tr><td colspan="2"><div style="margin-top:10px;"></div></td></tr>

         </table>
         <p class="submit">
         <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
         </p>
      </form>



      <form method="post" action="admin.php?page=scf_dummy/scf_dummy.php">
         <input type="hidden" name="execute" />
         <?php settings_fields('posk_plugin_options'); ?>
         <?php $options = get_option('posk_options'); ?>
         <p class="submit">
         <input type="submit" name="scf_execute" class="button-primary" value="<?php _e('Execute') ?>" />
         </p>
      </form>
   </div>
   <?php
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function posk_validate_options($input) {
    // strip html from textboxes
   $input['textarea_one'] =  wp_filter_nohtml_kses($input['textarea_one']); // Sanitize textarea input (strip html tags, and escape characters)
   $input['txt_one'] =  wp_filter_nohtml_kses($input['txt_one']); // Sanitize textbox input (strip html tags, and escape characters)
   return $input;
}

// Display a Settings link on the main Plugins page
function posk_plugin_action_links( $links, $file ) {

   if ( $file == plugin_basename( __FILE__ ) ) {
      $posk_links = '<a href="'.get_admin_url().'options-general.php?page=plugin-options-starter-kit/plugin-options-starter-kit.php">'.__('Settings').'</a>';
      // make the 'Settings' link appear first
      array_unshift( $links, $posk_links );
   }

   return $links;
}

// ------------------------------------------------------------------------------
// SAMPLE USAGE FUNCTIONS:
// ------------------------------------------------------------------------------
// THE FOLLOWING FUNCTIONS SAMPLE USAGE OF THE PLUGINS OPTIONS DEFINED ABOVE. TRY
// CHANGING THE DROPDOWN SELECT BOX VALUE AND SAVING THE CHANGES. THEN REFRESH
// A PAGE ON YOUR SITE TO SEE THE UPDATED VALUE.
// ------------------------------------------------------------------------------

// As a demo let's add a paragraph of the select box value to the content output
add_filter( "the_content", "posk_add_content" );
function posk_add_content($text) {
   $options = get_option('posk_options');
   $select = $options['drp_select_box'];
   $text = "<p style=\"color: #777;border:1px dashed #999; padding: 6px;\">Select box Plugin option is: {$select}</p>{$text}";
   return $text;
}


$scf_dummy = new scf_dummy(); // call our class
