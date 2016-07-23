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
function mul_votes_button($mul_votes_val) {
	
	$countnumber = get_post_meta( $mul_votes_val['id'], '_vote', true );
    $mul_votes_content = '<div>
     <button id="mul_votes_'. $mul_votes_val['id'] .'" class="mul_votes_text">Vote</button>
     <button id="mul_count_'. $mul_votes_val['id'] .'" class="mul_votes_val">' . $countnumber . '</button>
     <iframe name="hidden_iframe" id="hidden_iframe" style="display:none;"onload="if(submitted)";}">
	 </iframe>
	 <form id="mul_votes_form_'. $mul_votes_val['id'] .'" action="'. plugins_url() .'/multiple-votes/form.php" method="post" target="hidden_iframe" onsubmit="submitted=true;">
	 <input type="hidden" value="'. $mul_votes_val['id'] .'" name="postid">
	 <input type="hidden" value="" name="countvalue" id="mul_votes_count_'. $mul_votes_val['id'] .'">
	 </form>
     <script>
        document.getElementById("mul_votes_'. $mul_votes_val['id'].'").onclick = function(){
            var countNumber = document.getElementById("mul_count_'. $mul_votes_val['id'] .'");
            countNumber.innerHTML = Number(countNumber.innerHTML) + 1;
            this.setAttribute("disabled", "true");
            this.innerHTML = "Voted";
            document.getElementById("mul_votes_count_'. $mul_votes_val['id'] .'").value= countNumber.innerHTML;
            document.getElementById("mul_votes_form_'. $mul_votes_val['id'] .'").submit();
        }
     </script>
     
     </div>';

    return $mul_votes_content;

}
add_shortcode( 'mul_votes', 'mul_votes_button' );


// add custom post type Vote and Vote taxonomy
function create_posttype() {
	
	$labels = array(
			'name'               => __( 'Votes', 'vote-type' ),
			'singular_name'      => __( 'Vote', 'vote-type' ),
			'add_new'            => __( 'Add Vote', 'vote-type' ),
			'add_new_item'       => __( 'Add Vote', 'vote-type' ),
			'edit_item'          => __( 'Edit Vote', 'vote-type' ),
			'new_item'           => __( 'New Vote', 'vote-type' ),
			'view_item'          => __( 'View Vote', 'vote-type' ),
			'search_items'       => __( 'Search Vote', 'vote-type' ),
			'not_found'          => __( 'No votes found', 'vote-type' ),
			'not_found_in_trash' => __( 'No votes in the trash', 'vote-type' ),
		);
		$supports = array(
			'title'
		);
		$args = array(
			'labels'          => $labels,
			'supports'        => $supports,
			'public'          => true,
			'capability_type' => 'post',
			'rewrite'         => array( 'slug' => 'vote', ),
			'menu_position'   => 30,
			'menu_icon'       => 'dashicons-thumbs-up',
			'register_meta_box_cb' => 'add_votes_metaboxes'
		);
	
	
	register_post_type( 'votes', $args	);
	
	$labels2 = array(
			'name'                       => __( 'Vote Categories', 'vote-type' ),
			'singular_name'              => __( 'Vote Category', 'vote-type' ),
			'menu_name'                  => __( 'Vote Categories', 'vote-type' ),
			'edit_item'                  => __( 'Edit Vote Category', 'vote-type' ),
			'update_item'                => __( 'Update Vote Category', 'vote-type' ),
			'add_new_item'               => __( 'Add New Vote Category', 'vote-type' ),
			'new_item_name'              => __( 'New Voteeam Category Name', 'vote-type' ),
			'parent_item'                => __( 'Parent Vote Category', 'vote-type' ),
			'parent_item_colon'          => __( 'Parent Vote Category:', 'vote-type' ),
			'all_items'                  => __( 'All Vote Categories', 'vote-type' ),
			'search_items'               => __( 'Search Vote Categories', 'vote-type' ),
			'popular_items'              => __( 'Popular Vote Categories', 'vote-type' ),
			'separate_items_with_commas' => __( 'Separate Vote categories with commas', 'vote-type' ),
			'add_or_remove_items'        => __( 'Add or remove Vote categories', 'vote-type' ),
			'choose_from_most_used'      => __( 'Choose from the most used Vote categories', 'vote-type' ),
			'not_found'                  => __( 'No Vote categories found.', 'vote-type' ),
		);
		$args2 = array(
			'labels'            => $labels2,
			'public'            => true,
			'show_in_nav_menus' => true,
			'show_ui'           => true,
			'show_tagcloud'     => true,
			'hierarchical'      => true,
			'rewrite'           => array( 'slug' => 'vote-category' ),
			'show_admin_column' => true,
			'query_var'         => true,
		);
		$args2 = apply_filters( 'vote_post_type_category_args', $args2 );
	
	register_taxonomy( 'vote-categories', 'votes', $args2 );
	
	// Add the Vote Count Meta Boxes and show Shortcode
	function add_votes_metaboxes() {
		add_meta_box('vote-count', 'Vote Count', 'mul_vote_count', 'votes', 'normal', 'high');
	}
	function mul_vote_count() {
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
		<input id="shortcode-generate" type="text" value=\'[mul_votes id="' . $post->ID  . '"]\'  readonly/>
		<span id ="copy-shortcode" class="btn" data-clipboard-target="#shortcode-generate">Copy Shortcode</span>
		<script>new Clipboard(".btn");
		document.getElementById("copy-shortcode").onclick= function(){this.innerHTML = "Shortcode Copied";};
		</script>
		<p><em>If you want to use your own style, customize class "<strong>mul_votes_text</strong>" (for button Vote) and "<strong>mul_votes_val</strong>" (for the Vote count ) in your custom CSS</em></p>';

	}
	
	// Save the Metabox Data
	function wpt_save_votes_meta($post_id, $post) {
		
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
		
		$votes_meta['_vote'] = $_POST['_vote'];
		
		// Add values of $events_meta as custom fields
		
		foreach ($votes_meta as $key => $value) { // Cycle through the $votes_meta array!
			if( $post->post_type == 'revision' ) return; // Don't store custom data twice
			$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
			if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
				update_post_meta($post->ID, $key, $value);
			} else { // If the custom field doesn't have a value
				add_post_meta($post->ID, $key, $value);
			}
			
		}
	
	}

add_action('save_post', 'wpt_save_votes_meta', 1, 2); // save the custom fields
}
add_action( 'init', 'create_posttype' );

// remove permark link, add some style
add_action('admin_head', 'remove_vote_permarklink');
function remove_vote_permarklink() {

    global $post_type;

    if ($post_type == 'votes') {
    	echo '<link rel="stylesheet" type="text/css" href="'. plugins_url(). '/multiple-votes/assets/style.css' . '">';
        echo '<script src="'. plugins_url() .'/multiple-votes/assets/clipboard.min.js"></script>';
    }
}

// add column to show count content in admin page
add_filter( 'manage_edit-votes_columns', 'edit_vote_columns' ) ;

function edit_vote_columns( $columns ) {

	$columns = array_slice($columns, 0, 2, true) +
    array('count' => __( 'Count Number' ), 'short' => __( 'Shortcode' )) +
    array_slice($columns, 2, count($columns)-2, true);
	return $columns;
}

add_action( 'manage_votes_posts_custom_column', 'manage_votes_columns', 10, 2 );

function manage_votes_columns( $column, $post_id ) {
	global $post;

	switch( $column ) {

		
		case 'count' :

			$countnumber = get_post_meta( $post_id, '_vote', true );
			printf($countnumber );
			break;
		case 'short' :
			printf('[mul_votes id="'. $post_id .'"]');
			break;

		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}

add_filter( 'manage_edit-votes_sortable_columns', 'votes_sortable_columns' );

function votes_sortable_columns( $columns ) {

	$columns['count'] = '_vote';

	return $columns;
}
add_action( 'pre_get_posts', 'manage_wp_posts_be_qe_pre_get_posts', 1 );
function manage_wp_posts_be_qe_pre_get_posts( $query ) {

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

//filter by categories
add_action('restrict_manage_posts', 'filter_votes_by_category');
function filter_votes_by_category() {
	global $typenow;
	$post_type = 'votes'; // change to your post type
	$taxonomy  = 'vote-categories'; // change to your taxonomy
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
/**
 * Filter posts by taxonomy in admin
 * @author  Mike Hemberger
 * @link http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
 */
add_filter('parse_query', 'tsm_convert_id_to_term_in_query');
function tsm_convert_id_to_term_in_query($query) {
	global $pagenow;
	$post_type = 'votes'; // change to your post type
	$taxonomy  = 'vote-categories'; // change to your taxonomy
	$q_vars    = &$query->query_vars;
	if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
		$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
		$q_vars[$taxonomy] = $term->slug;
	}
}

//add link tag in header
add_action( 'wp_head', 'mul_votes_style' );
function mul_votes_style() {
  echo '<link rel="stylesheet" type="text/css" href="'. plugins_url(). '/multiple-votes/assets/style.css' . '">';
}
?>