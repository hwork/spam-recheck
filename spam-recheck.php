<?php
/*
Plugin Name: Spam Recheck
Plugin URI: http://www.techcrunch.com
Description: Checks all of your comments for spam, passing it through Akismet
Author: Henry Work
Version: 0.1
Author URI: http://www.henrywork.com
*/

add_action('admin_menu', 'recheck_add_config_page');

function recheck_add_config_page() {
    add_options_page('Spam Recheck', 'Spam Recheck', 8, 'recheck_import', 'recheck_config_page');
}

function recheck_config_page() {
	global $wpdb, $akismet_api_host, $akismet_api_port;	
	
	if ( isset($_POST['recheck_update']) ) {
		/* do import */
		//$posts = query_posts('showposts=500&offset=8500');	
		
		$comments_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments;");
		//echo 'user count is ' . $comments_count;
		
		$first_comment_id = $wpdb->get_var("SELECT comment_id FROM $wpdb->comments ORDER BY comment_id ASC LIMIT 1");
		echo 'first comment id is ' . $first_comment_id . "\n";

		$last_comment_id = $wpdb->get_var("SELECT comment_id FROM $wpdb->comments ORDER BY comment_id DESC LIMIT 1");
		echo 'last comment id is ' . $last_comment_id . "\n";

		//echo 'first comment id is ' . $first_comment_id;
		
		$j = 0;
		for ($i = 421740; $i < 421740+10; $i++) {
			$comment = $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_id = $i", ARRAY_A);
			if ($comment) {
				if ($comment['comment_approved'] != '1') {
					if ($comment['comment_approved'] == '0') {
						echo '<p>comment #' . $comment['comment_ID'] . ' is not approved, skipping</p>';
					}
					else if ($comment['comment_approved'] == 'spam') {
				}
				else {
					$j++;				
					echo '<p>comment #' . $comment['comment_ID'] . ' is going to akismet ... ';
					$comment['user_ip']    = $comment['comment_author_IP'];
					$comment['user_agent'] = $comment['comment_agent'];
					$comment['referrer']   = '';
					$comment['blog']       = 'http://www.techcrunch.com/';
					$id = (int) $comment['comment_ID'];

					$query_string = '';
					foreach ( $comment as $key => $data )
					$query_string .= $key . '=' . urlencode( stripslashes($data) ) . '&';

					$response = akismet_http_post($query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port);

					if ($response[1] == 'true') {
						echo 'and is INDEED SPAM!!!, marking as such';
					}
					else {
						echo 'and is not spam, continuing';
					}
					echo '</p>';

					usleep(500000);
				}
			}
		}
		
		//$moderation = $wpdb->get_results( "SELECT * FROM $wpdb->comments WHERE comment_approved = '1' LIMIT 10", ARRAY_A );
		//foreach ( (array) $moderation as $c ) {
		//	$c['user_ip']    = $c['comment_author_IP'];
		//	$c['user_agent'] = $c['comment_agent'];
		//	$c['referrer']   = '';
		//	$c['blog']       = get_option('home');
		//	$id = (int) $c['comment_ID'];
    //
		//	$query_string = '';
		//	foreach ( $c as $key => $data )
		//	$query_string .= $key . '=' . urlencode( stripslashes($data) ) . '&';
    //
		//	$response = akismet_http_post($query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port);
		//	
		//	echo $response[1];
		//	echo "\n";
		//
		//	usleep(500000);
		//}
    //
    //
		//	if ( 'true' == $response[1] ) {
		//		$wpdb->query( "UPDATE $wpdb->comments SET comment_approved = 'spam' WHERE comment_ID = $id" );
		//	}
		//}
		
		//foreach ( $posts as $post ) {
		//	if ($post->post_status == 'publish') {
		//		cb_publish_post($post->ID);
		//		usleep(200000);
		//	}
		//}
	?>
		<div id="message" class="updated fade">
			<p>Victory!</p>
		</div>
	<?php
	}
?>

<div class="wrap">
	<h2>Spam Recheck</h2>
	<form method="post" action="">
		<div style="width:100%; horizontal-align:right">
			<input type="submit" name="recheck_update" value="Check &raquo;"  style="float:right;" />
			<hr style="clear:both; visibility: hidden" />
		</div>
	</form>
</div>
<?php 

}

?>