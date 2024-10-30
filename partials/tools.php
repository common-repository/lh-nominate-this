<div class="card">
<h2 class="title">Nominate This</h2>

<?php

if (isset($_POST[ self::return_nominated_url_field_name() ])){

$value = $_POST[self::return_nominated_url_field_name()];
 
} elseif (isset($_GET[self::return_nominated_url_field_name()])){

$value = urldecode($_GET[self::return_nominated_url_field_name()]);

}

?>


<form method="post" action="">
<?php wp_nonce_field( self::return_plugin_namespace()."-nominate_tool_nonce", self::return_plugin_namespace()."-nominate_tool_nonce", false ); ?>
<table class="form-table">
<tr valign="top">
<th scope="row"><label for="<?php echo self::return_nominated_url_field_name(); ?>"><?php _e("URL;", self::return_plugin_namespace() ); ?></label></th>
<td><input type="url" name="<?php echo self::return_nominated_url_field_name(); ?>" id="<?php echo self::return_nominated_url_field_name(); ?>" value="<?php  if (isset($value)){  echo $value; } ?>" size="60" /></td>
</tr>
<tr valign="top">
<th scope="row"><label for="<?php echo $this->post_type_field; ?>"><?php _e("Post Type;", self::return_plugin_namespace() ); ?></label></th>
<td>
<fieldset>
<legend>What post type do you want to create?</legend>
<select name="<?php echo $this->post_type_field; ?>">
<?php foreach ( get_post_types( array('public'   => true ), 'names' ) as $posttype ) { ?>
<option value="<?php echo $posttype; ?>"><?php echo $posttype; ?></option>
<?php } ?>
</select>
</fieldset></td>
</tr>
</table>
<?php submit_button( 'Get Content' ); ?>
</form>
<?php

  if (!isset($value)){  

echo '<h4>'.__( 'Bookmarklet', self::return_plugin_namespace() ).'</h4>';

echo '<p>'.__( 'Drag the bookmarklet below to your bookmarks bar. Then, when you find a file online you want to upload, simply "Upload" it', self::return_plugin_namespace() ).'</p>';

echo '<p><a title="'.__( 'Bookmark this link', self::return_plugin_namespace() ).'" href="'.self::return_bookmarklet_string().'">'.__( 'Nominated article to ', self::return_plugin_namespace() ).get_bloginfo("name").'</a><br/>';

echo __( ' or edit your bookmarks and paste the below code', self::return_plugin_namespace() ).'<br/>';

echo self::return_bookmarklet_string().'</p>';


}
?>
</div>