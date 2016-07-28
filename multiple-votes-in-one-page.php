<?php
/**
 * Plugin Name: Multiple Votes in one page
 * Plugin URI: https://github.com/lequanghuylc/multiple-votes-in-one-page
 * Description: This plugin allow you to create multiple votes in one page
 * Version: 1.0 
 * Author: Huy Le 
 * Author URI: http://lequanghuy.xyz
 * License: GPLv2 or later
 */
?>
<?php

// add shortcode
function lqh_mul_votes_shortcode($val) {
	
	$countnumber = get_post_meta( $val['id'], '_vote', true );
    $content = '<div>
     <button id="lqh_mul_votes_'. $val['id'] .'" class="lqh_mul_votes_text">Vote</button>
     <button id="lqh_mul_votes_count_'. $val['id'] .'" class="lqh_mul_votes_val">' . $countnumber . '</button>
	 <form id="lqh_mul_votes_form_'. $val['id'] .'" method="post" action="">
	 <input type="hidden" value="'. $val['id'] .'" name="lqh_mul_votes_postid">
	 <input type="hidden" value="" name="lqh_mul_votes_countvalue" id="lqh_mul_votes_input_count_'. $val['id'] .'">
	 </form>
     <script>
    	jQuery(function($){
    		$("#lqh_mul_votes_'. $val['id'].'").click(function(){
	            var countNumber = Number($("#lqh_mul_votes_count_'. $val['id'] .'").html()) + 1;
	            $("#lqh_mul_votes_count_'. $val['id'] .'").html(countNumber);
	            $("#lqh_mul_votes_input_count_'. $val['id'] .'").val(countNumber);
	            $("#lqh_mul_votes_form_'. $val['id'] .'").submit();
	
	       });
	       $("#lqh_mul_votes_form_'. $val['id'] .'").on("submit", function(){
					$.ajax({
						type:"POST",
						url: "'.admin_url("admin-ajax.php", null).'",
						data: $(this).serialize() + "&action=lqh_ajax_form_handle_from_shortcode",
						success:function(data){
							$("#lqh_mul_votes_'. $val['id'].'").html("Voted");
							$("#lqh_mul_votes_'. $val['id'].'").attr("disabled", "true");
						}
					});
				return false;	
	       });
	   });
     </script>
     </div>';

    return $content;
}
add_shortcode( 'lqh_mul_votes', 'lqh_mul_votes_shortcode' );

// AJAX Handling Form from Shortcode

function lqh_ajax_form_handle_from_shortcode(){
	$lqh_post_id= absint(intval(sanitize_text_field($_POST['lqh_mul_votes_postid'])));
	$lqh_post_count_value = absint(intval(sanitize_text_field($_POST['lqh_mul_votes_countvalue'])));
	update_post_meta($lqh_post_id,'_vote', $lqh_post_count_value);
	die();
}
add_action('wp_ajax_lqh_ajax_form_handle_from_shortcode', 'lqh_ajax_form_handle_from_shortcode');
add_action('wp_ajax_nopriv_lqh_ajax_form_handle_from_shortcode', 'lqh_ajax_form_handle_from_shortcode');

// add custom post type Vote and its taxonomy
function lqh_mul_votes_create_vote_post_type() {
	
	$labels = array(
			'name'               => __( 'Votes' ),
			'singular_name'      => __( 'Vote' ),
			'add_new'            => __( 'Add Vote' ),
			'add_new_item'       => __( 'Add Vote' ),
			'edit_item'          => __( 'Edit Vote' ),
			'new_item'           => __( 'New Vote' ),
			'view_item'          => __( 'View Vote' ),
			'search_items'       => __( 'Search Vote' ),
			'not_found'          => __( 'No votes found' ),
			'not_found_in_trash' => __( 'No votes in the trash' ),
		);
		$supports = array(
			'title'
		);
		$args = array(
			'labels'          => $labels,
			'supports'        => $supports,
			'public'          => true,
			'capability_type' => 'post',
			'rewrite'         => array( 'slug' => 'votes-iop-lqh', ),
			'menu_position'   => 30,
			'menu_icon'       => 'dashicons-thumbs-up',
			'register_meta_box_cb' => 'lqh_mul_votes_add_metaboxes'
		);
	
	
	register_post_type( 'multiplevotesioplqh', $args	);
	
	$labels2 = array(
			'name'                       => __( 'Vote Categories' ),
			'singular_name'              => __( 'Vote Category' ),
			'menu_name'                  => __( 'Vote Categories' ),
			'edit_item'                  => __( 'Edit Vote Category' ),
			'update_item'                => __( 'Update Vote Category' ),
			'add_new_item'               => __( 'Add New Vote Category' ),
			'new_item_name'              => __( 'New Voteeam Category Name' ),
			'parent_item'                => __( 'Parent Vote Category' ),
			'parent_item_colon'          => __( 'Parent Vote Category:' ),
			'all_items'                  => __( 'All Vote Categories' ),
			'search_items'               => __( 'Search Vote Categories' ),
			'popular_items'              => __( 'Popular Vote Categories' ),
			'separate_items_with_commas' => __( 'Separate Vote categories with commas' ),
			'add_or_remove_items'        => __( 'Add or remove Vote categories' ),
			'choose_from_most_used'      => __( 'Choose from the most used Vote categories' ),
			'not_found'                  => __( 'No Vote categories found.' ),
		);
		$args2 = array(
			'labels'            => $labels2,
			'public'            => true,
			'show_in_nav_menus' => true,
			'show_ui'           => true,
			'show_tagcloud'     => true,
			'hierarchical'      => true,
			'rewrite'           => array( 'slug' => 'votes-iop-lqh-category' ),
			'show_admin_column' => true,
			'query_var'         => true,
		);
		$args2 = apply_filters( 'vote_post_type_category_args', $args2 );
	
	register_taxonomy( 'votes-iop-lqh-categories', 'multiplevotesioplqh', $args2 );
	
	// Add the Vote Count Meta Boxes and show Shortcode
	function lqh_mul_votes_add_metaboxes() {
		add_meta_box('lqh-vote-count', 'Vote Count', 'lqh_mul_votes_add_metabox_count', 'multiplevotesioplqh', 'normal', 'high');
	}
	function lqh_mul_votes_add_metabox_count() {
		global $post;
	
		// Noncename needed to verify where the data originated
		echo '<input type="hidden" name="votemeta_noncename" id="votemeta_noncename" value="' . 
		wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	
		// Get the location data if its already been entered
		$votecount = get_post_meta($post->ID, '_vote', true);
		$votenewcount = empty($votecount) ? 0 : $votecount;
		// Echo out the field
		echo '<input type="text" name="_vote" value="' . $votenewcount  . '" class="widefat" />
		<p><em>Note: The default count value is 0 when creating vote and it will increase when viewers vote</em></p>
		<p><em>You can change its nature value by editting the field</em></p>
		<h4>Vote Shortcode</h4>
		<input id="shortcode-generate" type="text" value=\'[lqh_mul_votes id="' . $post->ID  . '"]\'  readonly/>
		<span id ="copy-shortcode" class="btn" data-clipboard-target="#shortcode-generate">Copy Shortcode</span>
		<script>
		window.onload=function(){
			new Clipboard(".btn");
			document.getElementById("copy-shortcode").onclick= function(){this.innerHTML = "Shortcode Copied";};
		};
		</script>
		<p><em>If you want to use your own style, customize class "<strong>lqh_mul_votes_text</strong>" (for button Vote) and "<strong>lqh_mul_votes_val</strong>" (for the Vote count ) in your custom CSS</em></p>';

	}
	
	// Save the Metabox Data
	function lqh_mul_votes_save_meta_content($post_id, $post) {
		
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( !wp_verify_nonce( $_POST['votemeta_noncename'], plugin_basename(__FILE__) )) {
		return $post->ID;
		}
	
		// Is the user allowed to edit the post or page?
		if ( !current_user_can( 'edit_post', $post->ID ))
			return $post->ID;
	
		// OK, we're authenticated: we need to find and save the data
		// We'll put it into an array to make it easier to loop though.
		
		$multiplevotesioplqh_meta['_vote'] = $_POST['_vote'];
		
		// Add values of $events_meta as custom fields
		
		foreach ($multiplevotesioplqh_meta as $key => $value) { // Cycle through the $multiplevotesioplqh_meta array!
			if( $post->post_type == 'revision' ) return; // Don't store custom data twice
			$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
			if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
				update_post_meta($post->ID, $key, $value);
			} else { // If the custom field doesn't have a value
				add_post_meta($post->ID, $key, $value);
			}
			
		}
	
	}

add_action('save_post', 'lqh_mul_votes_save_meta_content', 1, 2); // save the custom fields
}
add_action( 'init', 'lqh_mul_votes_create_vote_post_type' );

// add column to show count content in admin page
add_filter( 'manage_edit-multiplevotesioplqh_columns', 'lqh_mul_votes_add_columns_admin' ) ;

function lqh_mul_votes_add_columns_admin( $columns ) {

	$columns = array_slice($columns, 0, 2, true) +
    array('count' => __( 'Count Number' ), 'short' => __( 'Shortcode' )) +
    array_slice($columns, 2, count($columns)-2, true);
	return $columns;
}

add_action( 'manage_multiplevotesioplqh_posts_custom_column', 'lqh_mul_votes_manage_columns_admin', 10, 2 );

function lqh_mul_votes_manage_columns_admin( $column, $post_id ) {
	global $post;

	switch( $column ) {

		
		case 'count' :

			$countnumber = get_post_meta( $post_id, '_vote', true );
			printf($countnumber );
			break;
		case 'short' :
			printf('[lqh_mul_votes id="'. $post_id .'"]');
			break;

		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}

add_filter( 'manage_edit-multiplevotesioplqh_sortable_columns', 'lqh_mul_votes_make_columns_sortable' );

function lqh_mul_votes_make_columns_sortable( $columns ) {

	$columns['count'] = '_vote';

	return $columns;
}
add_action( 'pre_get_posts', 'lqh_mul_votes_handle_sorting', 1 );
function lqh_mul_votes_handle_sorting( $query ) {

   if ( $query->is_main_query() && ( $orderby = $query->get( 'orderby' ) ) ) {

      switch( $orderby ) {

         // If we're ordering by metakey '_vote'
         case '_vote':

            // set our query's meta_key, which is used for custom fields
            $query->set( 'meta_key', '_vote' );
			
            $query->set( 'orderby', 'meta_value_num' );
				
            break;

      }

   }

}

//add filter by categories to admin
add_action('restrict_manage_posts', 'lqh_mul_votes_add_filter_by_categories');
function lqh_mul_votes_add_filter_by_categories() {
	global $typenow;
	$post_type = 'multiplevotesioplqh'; // change to your post type
	$taxonomy  = 'votes-iop-lqh-categories'; // change to your taxonomy
	if ($typenow == $post_type) {
		$selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
		$info_taxonomy = get_taxonomy($taxonomy);
		wp_dropdown_categories(array(
			'show_option_all' => __("Show All {$info_taxonomy->label}"),
			'taxonomy'        => $taxonomy,
			'name'            => $taxonomy,
			'orderby'         => 'name',
			'selected'        => $selected,
			'show_count'      => true,
			'hide_empty'      => true,
		));
	};
}
add_filter('parse_query', 'lqh_mul_votes_handle_filtering');
function lqh_mul_votes_handle_filtering($query) {
	global $pagenow;
	$post_type = 'multiplevotesioplqh'; // change to your post type
	$taxonomy  = 'votes-iop-lqh-categories'; // change to your taxonomy
	$q_vars    = &$query->query_vars;
	if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
		$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
		$q_vars[$taxonomy] = $term->slug;
	}
}

//add style and script to front
add_action( 'wp_head', 'lqh_mul_votes_style_and_script_front' );
function lqh_mul_votes_style_and_script_front() {
  wp_enqueue_script('jquery');
  wp_enqueue_style('lqh_mul_votes_style_front_style', plugins_url('/assets/style.css', __FILE__) );
}

// adding style and script to admin
add_action('admin_head', 'lqh_mul_votes_style_and_script_admin');
function lqh_mul_votes_style_and_script_admin() {

    global $post_type;

    if ($post_type == 'multiplevotesioplqh') {
    	wp_enqueue_style('lqh_mul_votes_style_front_style', plugins_url('/assets/style.css', __FILE__) );
    	wp_enqueue_script('lqh_mul_votes_script_clipboard', plugins_url('/assets/clipboard.min.js', __FILE__) );
    }
}

?>