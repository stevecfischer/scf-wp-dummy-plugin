<?php
/*!
 * Notes: finished adding function to delete all posts and terms my plugin
 * creates.  so basically it inflates and deflates a theme.
 * the problem is how to handle things when it gets ran multiple times.
 * maybe check for the option. if option(_scf_new_posts) isset add to it else
 * just populate it....???
 */
class  scf_dummy{

   public $_arr_new_post_id = array();
   public $_arr_new_term_id = array();
   public $_active_taxonomies = array();
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
         add_action( 'admin_init', array($this,'get_options') );
         add_action( 'admin_init', array($this,'active_tax') );
         add_action( 'admin_init', array($this,'active_cpt') );
         add_action( 'admin_init', array($this,'scf_log_new_posts') );
         add_action( 'admin_init', array($this,'relate_post_to_tax') );
         add_action( 'admin_init', array($this,'scf_log_new_terms') );
      }

      if( isset($_POST['scf_delete']) ) {
         add_action( 'admin_init', array($this,'scf_clean_up_posts') );
      }
   }

   function create_posts($args){
      global $wpdb;
      global $wp_rewrite;
      global $post;
      $local_options = $this->_custom_options;

      $title_helper_pattern = "/%%[\s\S]*?%%/";

      if( preg_match($title_helper_pattern, $local_options['title'], $matches) ){
         $title = preg_replace($title_helper_pattern, $args, $local_options['title']);
      }elseif(empty($local_options['title']) ){
         $title = $args;
      }else{
         $title = $local_options['title'];
      }
      $cpt_log = array();
      // Create post object
      for ($i=1; $i<=$local_options['num_post_create']; $i++) {
         if( ! post_exists($title.' '.$i) ){
              $my_post = array(
                 'post_title' => $title.' '.$i,
                 'post_content' => $local_options['content'],
                 'post_status' => 'publish',
                 'post_type' => $args
              );

              if(! $new_id = wp_insert_post( $my_post ) ){
                 die('cannot create post');
              }


               $filename = $local_options['upload_image'];

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


           $this->relate_post_to_tax($new_id,$args);

           array_push($cpt_log,$new_id);
         }//END IF
      }//END FOREACH
      $this->_arr_new_post_id[] = array($args => $cpt_log);
   }


   function relate_post_to_tax( $scf_new_id, $scf_cpt = null ){
      $tax_log = array();
      foreach ( get_object_taxonomies( $scf_cpt ) as $tax_name ) {
         $scf_terms = get_terms( $tax_name,array('hide_empty'=>false) );
         foreach($scf_terms as $scf_term){
            if( ! $new_tax_id = wp_set_object_terms( $scf_new_id, $scf_term->name, $tax_name,true ) ){
               die('could not set insareas');
            }
         }
      }
   }


   function active_cpt(){
      $local_options = $this->_custom_options;
      foreach($local_options['cpt'] as $k => $v){
         $this->create_posts($k);
      }
   }

   function active_tax(){
      $local_options = $this->_custom_options;
      $active_taxonomies = array();
      foreach($local_options['tax'] as $k => $v){
         $this->set_taxonomies($k);
         array_push($active_taxonomies, $k);
      }
      $this->_active_taxonomies = $active_taxonomies;

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
      return $tax_list;
   }

   function set_taxonomies($tax){
      $term_log = array();
      $local_options = $this->_custom_options;
      for ($i=1; $i<=$local_options['num_post_create']; $i++) {
        $title_helper_pattern = "/%%[\s\S]*?%%/";
        if( preg_match($title_helper_pattern, $local_options['title_tax'], $matches) ){
            $title = preg_replace($title_helper_pattern, ucwords($tax), $local_options['title_tax']);
            //$new_term = wp_insert_term( $title.'&nbsp;'.$i, $tax );
        }elseif(empty($local_options['title_tax']) ){
            $title = ucwords($tax);
            //$new_term = wp_insert_term( ucwords($tax).'&nbsp;'.$i, $tax );
        }else{
            $title = $local_options['title_tax'];
            //$new_term = wp_insert_term( $local_options['title_tax'].'&nbsp;'.$i, $tax );
        }

         $term = term_exists( $title.'ddd&nbsp;'.$i, $tax);

         if ($term === 0) {
           //term exists so skip the rest
            $new_term = "$term -- term exists";
         }else{
           $new_term = wp_insert_term( $title.'&nbsp;'.$i, $tax );
         }

        array_push($term_log,$new_term);
     }
      $this->_arr_new_term_id[] = array($tax => $term_log);
   }

   function scf_log_new_posts(){
      update_option('_scf_new_posts',$this->_arr_new_post_id);
   }

   function scf_log_new_terms(){
      update_option('_scf_new_terms',$this->_arr_new_term_id);
   }

   function scf_clean_up_posts(){
      /*
      @TODO: need to make this function more efficent. too many loops.
      */
      $created_terms = get_option('_scf_new_terms');
      foreach($created_terms as $tax_key => $tax_val){
         foreach($tax_val as $term_key => $term_val){
            foreach($term_val as $term_id){
               if(! wp_delete_term( $term_id['term_id'], $term_key) ){
                  die('Error deleting Term');
               }
            }
         }
      }


      $created_posts = get_option('_scf_new_posts');
      foreach($created_posts as $created_post){
         foreach($created_post as $cpt_arr){
            foreach($cpt_arr as $postid){
               if(! wp_delete_post( $postid, false )){
                  die('Error deleting Post');
               }
            }
         }
      }

   }//EOF
}
/*===============================
END OF CLASS
===============================*/