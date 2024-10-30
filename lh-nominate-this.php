<?php 
/**
 * Plugin Name: LH Nominate This
 * Plugin URI: https://lhero.org/portfolio/lh-nominate-this/
 * Description: Adds nominate this support to Wordpress
 * Author: Peter Shaw
 * Version: 2.00
 * Author URI: https://shawfactor.com
 * Text Domain: lh_nominate_this
 * Domain Path: /languages
*/

if (!class_exists('LH_Nominate_this_plugin')) {

class LH_Nominate_this_plugin {


var $post_type_field = 'lh_nominate_this-post_type';

private static $instance;


static function return_plugin_namespace(){

return 'lh_nominate_this';

}

static function return_option_name(){

return self::return_plugin_namespace().'-options';

}

static function return_page_id_field_name(){
    
return  self::return_plugin_namespace().'-page_id';   
    
}

static function return_nominated_url_field_name(){
    
return  self::return_plugin_namespace().'-nominated_url';   
    
}

static function return_post_type_field_name(){
    
return  self::return_plugin_namespace().'-post_type';   
    
}

static function isValidUrl($url) {
    $url = parse_url($url);
    if (!isset($url["host"])){
      
      return false;
      
    } else {
    
    return true;
    
    }
}

static function curpageurl() {
	$pageURL = 'http';

	if ((isset($_SERVER["HTTPS"])) && ($_SERVER["HTTPS"] == "on")){
		$pageURL .= "s";
}

	$pageURL .= "://";

	if (($_SERVER["SERVER_PORT"] != "80") and ($_SERVER["SERVER_PORT"] != "443")){
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];

	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

}

	return $pageURL;
}


static function readability_to_post_array($readability_data, $post_type = 'post'){
    
   $post_array = array(

				'post_title'		=>	$readability_data['title'],
				'post_status'		=>	'draft',
				'post_type'		=>	$post_type,
			);
    
        // If we've got Tidy, let's clean up input.
        // This step is highly recommended - PHP's default HTML parser
        // often doesn't do a great job and results in strange output.
        
        $html = $readability_data['body'];
        
if (function_exists('tidy_parse_string')) {
    
    $options = array('indent'=>true, 'show-body-only' => true);
	$tidy = tidy_parse_string($html, $options, 'UTF8');
	$tidy->cleanRepair();
	$html = $tidy->value;

}

$post_array['post_content'] = $html;

return  $post_array;
    
} 

static function return_basic_share_url(){
    
$options = get_option( self::return_option_name() );

    
if (!empty($options[ self::return_page_id_field_name() ]) && self::isValidUrl( get_permalink($options[ self::return_page_id_field_name() ]))){
    
    return get_permalink($options[ self::return_page_id_field_name() ]);
    
} else {
    
    return false;
    
}


    
    
}

static function return_bookmarklet_string(){
    
    $string = "javascript: (function() { window.location.href='".self::return_basic_share_url()."?lh_nominate_this-nominated_url=' + encodeURIComponent(location.href);})();";

return $string;
    
    
}



static function get_resolved_url($url){

if (!class_exists('URLResolver')) {

require_once 'includes/urlresolver/URLResolver.php';


}


$resolver = new mattwright\URLResolver();


$url_result = $resolver->resolveURL($url);


// Test to see if any error occurred while resolving the URL
// Otherwise, print out the resolved URL.  The [HTTP status code] will tell you
// additional information about the success/failure. For instance, if the
// link resulted in a 404 Not Found error, it would print '404: http://...'
// The successful status code is 200.

if ($url_result->didErrorOccur()) {

return new WP_Error('lh_nominate_this', 'there was an error resolving '.$url.' '.$url_result->getErrorMessageString());

} else {
$return['code'] = $url_result->getHTTPStatusCode();
$return['url'] = $url_result->getURL();

return $return;
}

}



	
static function check_if_nominated($url){
	
global $wpdb;

$sql = "SELECT 	posts.ID FROM ".$wpdb->posts." posts, ".$wpdb->postmeta." meta WHERE posts.ID = meta.post_id and meta_key = '_".self::return_plugin_namespace()."-original_url' and meta_value = '".$url."'  LIMIT 1";

$results = $wpdb ->get_results($sql);

if (isset($results[0]->ID)){

return $results[0]->ID;

} else {
    
return false;   
    
}
	
}


static function update_post_content($post_id, $new_url, $old_url){
    
$post_object = get_post($post_id);

$content = str_replace( $old_url, $new_url , $post_object->post_content );

$my_post = array(
      'ID'           => $post_id,
      'post_content' => $content,
  );

// Update the post into the database
  wp_update_post( $my_post );

    
    
}



static function return_posttypes_dropdown(){
    
    ?>
<fieldset>
<legend>What post type do you want to create?</legend>  
    <select name="<?php echo self::return_post_type_field_name(); ?>">
<?php foreach ( get_post_types( array('public'   => true ), 'names' ) as $posttype ) { ?>
<option value="<?php echo $posttype; ?>"><?php echo $posttype; ?></option>
<?php } ?>
</select>
</fieldset>
<?php   
    
}

static function return_nominated_url_input(){
    
if (isset($_POST[ self::return_nominated_url_field_name() ])){

$value = $_POST[self::return_nominated_url_field_name()];

} elseif (isset($_GET[self::return_nominated_url_field_name()])){

$value = urldecode($_GET[self::return_nominated_url_field_name()]);

}elseif (isset($_POST['lh_nominate_this-nominated_text'])){

$value = $_POST['lh_nominate_this-nominated_text'];   
    
} elseif (isset($_GET['lh_nominate_this-nominated_text'])){

$value = urldecode($_GET['lh_nominate_this-nominated_text']);   
    
}

   ?>  
    <input type="url" name="<?php echo self::return_nominated_url_field_name(); ?>" id="<?php echo self::return_nominated_url_field_name(); ?>" value="<?php  if (isset($value)){  echo $value; } ?>" size="60" />
    <?php
    
}

static function return_nominate_html(){
    
ob_start();

?>
<form method="post" action="">
<?php wp_nonce_field( self::return_plugin_namespace()."-nominate_tool_nonce", self::return_plugin_namespace()."-nominate_tool_nonce", false ); ?>
<table class="form-table">
<tr valign="top">
<th scope="row"><label for="<?php echo self::return_nominated_url_field_name(); ?>"><?php _e("Nominated URL;", self::return_plugin_namespace() ); ?></label></th>
<td><?php echo self::return_nominated_url_input(); ?></td>
</tr>
<tr valign="top">
<th scope="row"><label for="<?php echo self::return_post_type_field_name(); ?>"><?php _e("Post Type;", self::return_plugin_namespace() ); ?></label></th>
<td><?php echo self::return_posttypes_dropdown(); ?></td>
</tr>
</table>
<p class="submit">
<input type="submit" name="submit" id="submit" class="button button-primary" value="Get Content">
</p>
</form>
<?php

  if (!isset($value)){  

echo '<h4>'.__( 'Bookmarklet', self::return_plugin_namespace() ).'</h4>';

echo '<p>'.__( 'Drag the bookmarklet below to your bookmarks bar. Then, when you find a file online you want to upload, simply "Upload" it', self::return_plugin_namespace() ).'</p>';

echo '<p><a title="'.__( 'Bookmark this link', self::return_plugin_namespace() ).'" href="'.self::return_bookmarklet_string().'">'.__( 'Nominated article to ', self::return_plugin_namespace() ).get_bloginfo("name").'</a><br/>';

echo __( ' or edit your bookmarks and paste the below code', self::return_plugin_namespace() ).'<br/>';

echo self::return_bookmarklet_string().'</p>';




}

$return_string = ob_get_contents();
ob_end_clean();

return $return_string;
    
    
}

public function render_pages_dropdown($args) {  // Textbox Callback

$options = get_option( self::return_option_name() );

$dropdown_args = array(
    'selected'              => $options[ $args[0] ],
    'echo'                  => 1,
    'name'                  => self::return_option_name().'['.$args[0].']',
    'show_option_none'      => __( '&mdash; Select &mdash;' ) // string
); 

wp_dropdown_pages( $dropdown_args);

?>
<a href="<?php echo get_permalink($options[ $args[0] ]); ?>">Link</a>
<a href="<?php echo get_edit_post_link($options[ $args[0] ]); ?>">Edit</a>
<?php

}

public function validate_options( $input ) { 
    

    
$output = $input;

    // Return the array processing any additional functions filtered by this action
    return apply_filters( self::return_plugin_namespace().'_input_validation', $output, $input );


}



public function add_configuration_section(){
    
    add_settings_section(  
        self::return_option_name(), // Section ID 
        'Share', // Section Title
        self::return_plugin_namespace().'-writing_section', // Callback
        'writing' // What Page?  This makes the section show up on the General Settings Page
    );
    
            add_settings_field( // Option 1
        self::return_page_id_field_name(), // Option ID
        'Sharing Page', // Label
        array($this, 'render_pages_dropdown'), // !important - This is where the args go!
        'writing', // Page it will be displayed (General Settings)
        self::return_option_name(), // Name of our section
        array( // The $args
            self::return_page_id_field_name() // Should match Option ID
        )  
    ); 


    




    register_setting('writing',self::return_option_name(), array($this, 'validate_options'));
    
}















public function add_meta_boxes($post_type, $post) {

if (('post' === $post_type)) {


add_meta_box(self::return_plugin_namespace()."-scrape_info-div", "Scrape Info", array($this,"scraping_info_render"), $post_type, "side", "low");


}



}

public function scraping_info_render(){
    
    wp_nonce_field( self::return_plugin_namespace()."-post_edit-nonce", self::return_plugin_namespace()."-post_edit-nonce" );
    
    $url = get_post_meta( get_the_ID(), "_".self::return_plugin_namespace()."-original_url", true );

?>

<label class="screen-reader-text" id="<?php  echo self::return_plugin_namespace()."-original_url-prompt-text";  ?>" for="<?php  echo self::return_plugin_namespace()."-original_url";  ?>">Original URL</label>
<input type="url" name="<?php  echo self::return_plugin_namespace()."-original_url";  ?>" id="<?php  echo self::return_plugin_namespace()."-original_url";  ?>" value="<?php echo $url; ?>"  />


<?php
    
    
    
}


public function handle_meta_box_data( $post_id, $post, $update ) {
    
    
    if (isset($_POST[self::return_plugin_namespace()."-post_edit-nonce"]) and (wp_verify_nonce( $_POST[self::return_plugin_namespace()."-post_edit-nonce"], self::return_plugin_namespace()."-post_edit-nonce")) and current_user_can( 'edit_post', $post_id )){
        
        
        
        
if (isset($_POST[self::return_plugin_namespace()."-original_url"]) && (self::isValidUrl(trim($_POST[self::return_plugin_namespace()."-original_url"])))){

$url = $_POST[self::return_plugin_namespace()."-original_url"];

update_post_meta($post_id,  "_".self::return_plugin_namespace()."-original_url", $url);


} 
        
        
        
    }
    
    
    
    
}


public function extract_content(){
    
    	if( !empty($_POST[ self::return_plugin_namespace()."-nominate_tool_nonce" ]) && wp_verify_nonce($_POST[ self::return_plugin_namespace()."-nominate_tool_nonce" ], self::return_plugin_namespace()."-nominate_tool_nonce" )) {
	    
	   if (!empty($_POST[ self::return_nominated_url_field_name() ])){
	       

	       

	       
	       $resolved_url = self::get_resolved_url($_POST[ self::return_nominated_url_field_name() ]);
		   
		   
		$post_id = self::check_if_nominated($resolved_url['url']);   
		
		
		if  (!is_wp_error($post_id) and is_numeric($post_id)){
		    
	wp_safe_redirect( admin_url( 'post.php?post='.$post_id.'&action=edit') );
exit();         
		    
		    
		}


        
if (!class_exists('LH_Readability_parser_class')) {

require_once 'includes/lh-readability-parser-class.php';


}
        
$data = LH_Readability_parser_class::action_url($resolved_url['url']);

$post_array = self::readability_to_post_array($data);

$post_id = wp_insert_post($post_array);
            
if  (!is_wp_error($post_id) and is_numeric($post_id)){
    
	
add_post_meta($post_id, "_".self::return_plugin_namespace()."-original_url", $resolved_url['url'], true);

add_post_meta($post_id, "_".self::return_plugin_namespace()."-updated_stamp", current_time('mysql'), true);

wp_safe_redirect( admin_url( 'post.php?post='.$post_id.'&action=edit') );
exit();     

} 
 



	       
	   } else {
	       
	       
	       
	   }


}
    
    
}







public function manifest_json_filter($json){
    
if ($url = self::return_basic_share_url()){
   
    $json['share_target']['action'] = parse_url($url, PHP_URL_PATH);
    $json['share_target']['method'] = 'POST';
    $json['share_target']['enctype'] = 'multipart/form-data';
    $json['share_target']['params']['url'] = 'lh_nominate_this-nominated_url';
    $json['share_target']['params']['text'] = 'lh_nominate_this-nominated_text';
    $json['share_target']['params']['files'][0]['name'] = 'attachment';
    $json['share_target']['params']['files'][0]['accept'] = 'image/jpg';
    
}

    
    
    return $json;
}

public function form_shortcode_output($atts, $content) {
    
ob_start();

if (!is_user_logged_in()){
    
_e('In order to nominate this content you need to be logged in.', self::return_plugin_namespace() );
    
    $args = array(
        'echo' => true,
        'redirect' => self::curpageurl(),
        'form_id' => 'loginform',
        'label_username' => __( 'Username' ),
        'label_password' => __( 'Password' ),
        'label_remember' => __( 'Remember Me' ),
        'label_log_in' => __( 'Log In' ),
        'id_username' => 'user_login',
        'id_password' => 'user_pass',
        'id_remember' => 'rememberme',
        'id_submit' => 'wp-submit',
        'remember' => false,
        'value_username' => NULL,
        'value_remember' => true );
    
wp_login_form($args);
    
} else {
    
?>
<form method="post" action="">
<?php wp_nonce_field( self::return_plugin_namespace()."-nominate_tool_nonce", self::return_plugin_namespace()."-nominate_tool_nonce", false ); ?>

<p>
<label for="<?php echo self::return_nominated_url_field_name(); ?>"><?php _e("Nominated URL:", self::return_plugin_namespace() ); ?></label><?php
    
echo self::return_nominated_url_input().'</p>';

//echo self::return_posttypes_dropdown();

}
?>
<p class="submit">
<input type="submit" name="submit" id="submit" class="button button-primary" value="Get Content">
</p>
</form>
<?php

$return_string = ob_get_contents();
ob_end_clean();


return $return_string;
    
    
}

public function register_shortcodes(){
    
add_shortcode(self::return_plugin_namespace().'_share_form', array($this,'form_shortcode_output'));
    
}



public function add_nominate_button(){
    
    include ('partials/tools.php');
    
    
}


public function plugin_init(){
    
        //add a section to the reading settings
        add_action('admin_init', array($this,'add_configuration_section'));

        //Add the meta box to manage post object scraping
        add_action('add_meta_boxes', array($this,'add_meta_boxes'),10,2);

        //Handle the meta box data
        add_action( 'save_post', array($this,'handle_meta_box_data'),10,3);
  
        //intercept the admin page request if everything is okay
        add_action( 'wp_loaded', array($this,'extract_content'));

        //add share target to the manifest.json
        add_filter( 'lh_web_application_manifest_json_filter', array($this,'manifest_json_filter'), 10, 1);

        //add the share target shortcode
        add_action( 'init', array($this,'register_shortcodes'));
        
        //add a form to the tools screen
        add_action( 'tool_box', array($this,'add_nominate_button'));
    
}



    /**
     * Gets an instance of our plugin.
     *
     * using the singleton pattern
     */
    public static function get_instance(){
        if (null === self::$instance) {
            self::$instance = new self();
        }
 
        return self::$instance;
    }
    
    static function uninstall(){

    delete_option(self::return_opt_name());

}


public function __construct() {

    //run our hooks on plugins loaded to as we may need checks       
    add_action( 'plugins_loaded', array($this,'plugin_init'));	

}



}

$lh_nominate_this_instance = LH_Nominate_this_plugin::get_instance();
register_uninstall_hook( __FILE__, array('LH_Nominate_this_plugin','uninstall'));


}

?>