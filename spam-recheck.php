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
		$comments = $wpdb->get_results("SELECT comment_ID FROM $wpdb->comments	WHERE comment_approved = '1'", ARRAY_A);

		$spam_found = 0;
		$comments_processed = 0;
		foreach ($comments as $c) {
			$i = $c['comment_ID'];
			$comment = $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_id = $i", ARRAY_A);
			$comment['user_ip']    = $comment['comment_author_IP'];
			$comment['user_agent'] = $comment['comment_agent'];
			$comment['referrer']   = '';
			$comment['blog']       = get_option('home');
			$id = (int) $comment['comment_ID'];					
      
			$query_string = '';
			foreach ( $comment as $key => $data )
			$query_string .= $key . '=' . urlencode( stripslashes($data) ) . '&';
      
			$response = akismet_http_post($query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port);
      
			if ($response[1] == 'true') {
				//echo '<p>comment #' . $comment['comment_ID'] . ' is SPAM!!!</p>';
				$wpdb->query( "UPDATE $wpdb->comments SET comment_approved = '0' WHERE comment_ID = $id" );
				$spam_found++;
			}
			$comments_processed++;
		}
		echo '<p>total spam found: ' . $spam_found . '</p>';
		echo '<p>total comments processed: ' . $comments_processed . '</p>';
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