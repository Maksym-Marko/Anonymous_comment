<?php
/**
* @package mxAnonymousComment
*/

/*
Plugin Name: Anonymous comment
Plugin URI: https://github.com/Maxim-us/Anonymous_comment
Description: Allows users to anonymously insert comments. But in the admin panel, the administrator can see who added this entry.
Author: Marko Maksym
Version: 1.0
Author URI: https://github.com/Maxim-us
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class MxAnonymousPost
{

	public function activate()
	{
		// rewrite rules
		flush_rewrite_rules();
	}

	public function deactivate()
	{
		// rewrite rules
		flush_rewrite_rules();
	}

	public function uninstall()
	{
		// rewrite rules
		flush_rewrite_rules();
	}

	/*************************
	* filters by comment form
	**************************/ 
	// new field
	public function add_action_to_comment_form()
	{

		add_filter('comment_form_logged_in_after', array( $this, 'add_new_field_to_comment_form' ) );

		add_action( 'comment_post', array( $this, 'save_comment_meta_data' ) );

	}

	// get metadata
	public function get_meta_data_anonymous_comment()
	{

		add_filter( 'get_comment_author_link', array( $this, 'attach_anonymous_to_author' ) );

	}

	// change template comment
	public function change_template_comment()
	{

		add_filter( 'wp_list_comments_args', array( $this, 'new_template_comment' ) );

	}

	// set data into admin panel
	public function set_data_into_table()
	{

		add_filter( 'manage_edit-comments_columns', array( $this, 'add_column_table_header_comment' ) );

		add_filter( 'manage_comments_custom_column', array( $this, 'new_comment_column' ), 10, 2 );
		
	}
	
	/*************************
	* actions and filters
	**************************/ 
	public function add_new_field_to_comment_form()
	{

	   	echo '<p class="comment-form-anonymous">
	        <label for="field_anonymous">Опубликовать:</label>

	        <select name="field_anonymous" id="field_anonymous">
				<option value="">От своего имени</option>
				<option value="anonymous">Анонимно</option>
	        </select>
	    </p>';

    }

    public function save_comment_meta_data( $comment_id ) {

		add_comment_meta( $comment_id, 'field_anonymous', $_POST[ 'field_anonymous' ] );

	}

	public function attach_anonymous_to_author( $author )
	{

		$field_anonymous = get_comment_meta( get_comment_ID(), 'field_anonymous', true );
	    if ( $field_anonymous ){	    	
	        $author .= " ($field_anonymous)";
	    }

	    return $author;
	}

	public function new_template_comment( $r )
	{

		$r['type'] = 'comment';
		$r['callback'] = array( $this, 'new_theme' );

		return $r;

	}

	public function new_theme( $comment, $args, $depth )
	{

		$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
?>
	<<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $this->has_children ? 'parent' : '', $comment ); ?>>
		<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
			<footer class="comment-meta">

				<?php $field_anonymous = get_comment_meta( get_comment_ID(), 'field_anonymous', true );
				if( !$field_anonymous ){ 
						echo $field_anonymous;
					?>

					<div class="comment-author vcard">
						<?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
						<?php
							/* translators: %s: comment author link */
							printf( __( '%s <span class="says">says:</span>' ),
								sprintf( '<b class="fn">%s</b>', get_comment_author_link( $comment ) )
							);
						?>
					</div><!-- .comment-author -->

				<?php } else{ ?>

					<img alt="" src="<?php echo plugins_url( 'img/anonymous.jpg', __FILE__ );?>" class="avatar avatar-32 gravatar" height="32" width="32">

					<b class="fn"><a href="#" rel="external nofollow" onclick="return false;" class="url">Аноним</a></b>

				<?php }?>

				<div class="comment-metadata">
					<a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
						<time datetime="<?php comment_time( 'c' ); ?>">
							<?php
								/* translators: 1: comment date, 2: comment time */
								printf( __( '%1$s at %2$s' ), get_comment_date( '', $comment ), get_comment_time() );
							?>
						</time>
					</a>
					<?php edit_comment_link( __( 'Edit' ), '<span class="edit-link">', '</span>' ); ?>
				</div><!-- .comment-metadata -->

				<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.' ); ?></p>
				<?php endif; ?>
			</footer><!-- .comment-meta -->

			<div class="comment-content">
				<?php comment_text(); ?>
			</div><!-- .comment-content -->

			<?php
			comment_reply_link( array_merge( $args, array(
				'add_below' => 'div-comment',
				'depth'     => $depth,
				'max_depth' => $args['max_depth'],
				'before'    => '<div class="reply">',
				'after'     => '</div>'
			) ) );
			?>
		</article><!-- .comment-body -->

<?php
	}

 	public function add_column_table_header_comment( $columns )
 	{

 		$columns['field_anonymous'] = 'Анонимный комментарий';
 		return $columns;
 	}

 	public function new_comment_column( $column, $comment_ID )
 	{

 		if ( 'field_anonymous' == $column ) {
	       if ( $meta = get_comment_meta( $comment_ID, 'field_anonymous', true ) ) {
	            echo 'Опубликовано анонимно';
	        }
	    }

 	}

}

// initialize
if ( class_exists( 'MxAnonymousPost' ) ) {

	$anonymousClass = new MxAnonymousPost();

	// add field
	$anonymousClass->add_action_to_comment_form();

	// get meta data
	$anonymousClass->get_meta_data_anonymous_comment();

	// change template of comments
	$anonymousClass->change_template_comment();

	// add column into comment table
	$anonymousClass->set_data_into_table();

}

// activation
register_activation_hook( __FILE__, array( 'MxAnonymousPost', 'activate' ) );

// deactivation
register_deactivation_hook( __FILE__, array( 'MxAnonymousPost', 'deactivate' ) );

// uninstall
register_uninstall_hook( __FILE__, array( 'MxAnonymousPost', 'uninstall' ) );