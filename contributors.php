<?php

/*
   Plugin Name: Contributors
   Description: Displays users along with an image and bio
   Author: Lauren Pully
   Author URI:  laurenepully.com
   Version: 1.0
   License: GPL2
   */
   
add_shortcode('contributors', 'get_contributors');
add_action('init', 'register_script');

function register_script(){
	wp_register_style( 'contributors', plugins_url() .'/contributors/style.css');
	wp_enqueue_style('contributors'); 
	wp_enqueue_media();
	wp_register_script('contributors-js', plugins_url() . '/contributors/contributors.js', array('jquery'));
	wp_enqueue_script('contributors-js');
}

/* Overwrite avatar function with our custom url, if it exists */
add_filter( 'get_avatar' , 'my_custom_avatar', 1, 5);
function my_custom_avatar( $avatar, $id_or_email, $size, $default, $alt) {
    $user = false;
        
    if ( is_numeric( $id_or_email ) ) {
        $id = (int) $id_or_email;
        $user = get_user_by( 'id' , $id );
        
        if(get_the_author_meta('custom_avatar_url', $id) != ''){
			$url = wp_upload_dir()['baseurl'] . '/' . get_the_author_meta('custom_avatar_url', $id);
			if (@getimagesize($url)){
		        $avatar = "<img alt='{$alt}' src='{$url}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";				
			}
	    }
    }

    return $avatar;
}
   
/* Main function script */   
function get_contributors($atts) {?>
	<div id="contributors">
	<?php
	$users = get_users();
	foreach($users as $user){
		if($user->show_user == 'on'){?>
			<div id="user">
				<?php echo get_avatar($user->id);?> 
				<h1><?php echo $user->user_firstname . ' ' . $user->user_lastname;?></h1>
				<?php 
					if(the_author_meta('description', $user->id) != ''){
						echo the_author_meta('description', $user->id) . '<br>';
					}
					
					if($user->twitter != ''){ ?>
						<a href="https://twitter.com/<?php echo $user->twitter;?>" target="_blank">@<?php echo $user->twitter;?></a>					
					<?php } ?>
			</div>
		<?php }
	}?>
	</div>
	<?php	
}

/* Add additional fields for social media */
function modify_user_contact_methods( $user_contact ) {

	// Add user contact methods
	$user_contact['twitter'] = __( 'Twitter Username' );

	return $user_contact;
}
add_filter( 'user_contactmethods', 'modify_user_contact_methods' );


function tm_additional_profile_fields( $user ) { 

?>
    <h3>Additional profile information</h3>
    <?php
		$checked = '';
		if (get_the_author_meta('show_user', $user->ID) == 'on'){
			$checked = 'checked';
		}
	?>

    <table class="form-table">
   	 <tr>
   		 <th><label for="show_user">Show user</label></th>
   		 <td>
   			 <input type="checkbox" id="show_user" name="show_user" <?php echo $checked;?>></input>
   		 </td>
   	 </tr>
   	 <tr>
   		 <th><label for="custom_avatar_url">Custom profile picture</label>
   		 </th>
   		 <td>
   		 	<input id="custom_avatar_url" type="text" name="custom_avatar_url" />
   		 	<input id="upload-button" type="button" class="button" value="Upload Image" />
   		 </td>
   	 </tr>
    </table>
    <?php
}

add_action( 'show_user_profile', 'tm_additional_profile_fields' );
add_action( 'edit_user_profile', 'tm_additional_profile_fields' );


function tm_save_profile_fields( $user_id ) {
		
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
   		return false;
    }

    if ( !empty( $_POST['show_user'] ) ) {
	   	 update_usermeta( $user_id, 'show_user', $_POST['show_user'] );
    } 
    
    if ( !empty( $_POST['custom_avatar_url'] ) ) {
	     global $wpdb;
	     $image_url = $_POST['custom_avatar_url'];
	     $wpdb->insert( 'images', array( 'image_url' => $image_url ), array( '%s' ) );
	     $image_url = str_replace(wp_upload_dir()['baseurl'], '', $image_url);
	   	 update_usermeta( $user_id, 'custom_avatar_url', $image_url);
    } 
}

add_action( 'personal_options_update', 'tm_save_profile_fields' );
add_action( 'edit_user_profile_update', 'tm_save_profile_fields' );

?>