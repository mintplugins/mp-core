<?php
/**
 * Template tag which displays comments in theme
 *
 * mp_core_comments_template
 */
if ( ! function_exists( 'mp_core_comments_template' ) ) {
	function mp_core_comments_template() {
	
	// If comments are open or we have at least one comment, load up the comment template
	if ( comments_open() || '0' != get_comments_number() )
					
		/*
		 * If the current post is protected by a password and
		 * the visitor has not yet entered the password we will
		 * return early without loading the comments.
		 */
		if ( post_password_required() )
			return;
	?>
	
		<div id="comments" class="comments-area">
	
		<?php // You can start editing here -- including this comment! ?>
	
		<?php if ( have_comments() ) : ?>
			<h2 class="comments-title">
				<?php
					printf( _nx( 'One thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', get_comments_number(), 'comments title', 'mp_core' ),
						number_format_i18n( get_comments_number() ), '<span>' . get_the_title() . '</span>' );
				?>
			</h2>
	
			<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
			<nav id="comment-nav-above" class="navigation-comment" role="navigation">
				<h1 class="screen-reader-text"><?php _e( 'Comment navigation', 'temp' ); ?></h1>
				<div class="previous"><?php previous_comments_link( __( '&larr; Older Comments', 'temp' ) ); ?></div>
				<div class="next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'temp' ) ); ?></div>
			</nav><!-- #comment-nav-before -->
			<?php endif; // check for comment navigation ?>
	
			<ol class="comment-list">
				<?php
					/* Loop through and list the comments. Tell wp_list_comments()
					 * to use mp_core_comment() to format the comments.
					 * If you want to overload this in a child theme then you can
					 * define mp_core_comment() and that will be used instead.
					 * See mp_core_comment() in inc/template-tags.php for more.
					 */
					wp_list_comments( array( 'callback' => 'mp_core_comment' ) );
				?>
			</ol><!-- .comment-list -->
	
			<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
			<nav id="comment-nav-below" class="navigation-comment" role="navigation">
				<h1 class="screen-reader-text"><?php _e( 'Comment navigation', 'temp' ); ?></h1>
				<div class="previous"><?php previous_comments_link( __( '&larr; Older Comments', 'temp' ) ); ?></div>
				<div class="next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'temp' ) ); ?></div>
			</nav><!-- #comment-nav-below -->
			<?php endif; // check for comment navigation ?>
	
		<?php endif; // have_comments() ?>
	
		<?php
			// If comments are closed and there are comments, let's leave a little note, shall we?
			if ( ! comments_open() && '0' != get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
		?>
			<p class="no-comments"><?php _e( 'Comments are closed.', 'temp' ); ?></p>
		<?php endif; 
		
		mp_core_comment_form();
		
		?>
	
	</div><!-- #comments -->

	<?php 
	}
}
/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 */
if ( ! function_exists( 'mp_core_comment' ) ) {
	function mp_core_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case 'pingback' :
			case 'trackback' :
		?>
		<li class="post pingback">
			<p><?php _e( 'Pingback:', 'temp' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( 'Edit', 'temp' ), '<span class="edit-link">', '<span>' ); ?></p>
		<?php
				break;
			default :
		?>
		<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
			<article id="comment-<?php comment_ID(); ?>" class="comment">
				<footer>
					<div class="comment-author vcard">
						<?php echo get_avatar( $comment, 40 ); ?>
						<?php printf( __( '%s <span class="says">says:</span>', 'temp' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
					</div><!-- .comment-author .vcard -->
					<?php if ( $comment->comment_approved == '0' ) : ?>
						<em><?php _e( 'Your comment is awaiting moderation.', 'temp' ); ?></em>
						<br />
					<?php endif; ?>
	
					<div class="comment-meta commentmetadata">
						<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>"><time datetime="<?php comment_time( 'c' ); ?>">
						<?php printf( _x( '%1$s at %2$s', '1: date, 2: time', 'temp' ), get_comment_date(), get_comment_time() ); ?>
						</time></a>
						<?php edit_comment_link( __( 'Edit', 'temp' ), '<span class="edit-link">', '<span>' ); ?>
					</div><!-- .comment-meta .commentmetadata -->
				</footer>
	
				<div class="comment-content"><?php comment_text(); ?></div>
	
				<div class="reply">
					<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
				</div><!-- .reply -->
			</article><!-- #comment-## -->
	
		<?php
				break;
		endswitch;
	}
}; // ends check for mp_core_comment()

/**
 * Displays and allows customization of the comment form
 *
 * Filter mp_core_comments_args
 */
function mp_core_comment_form(){
	
	//Get current user info
	global $current_user, $user_identity;
	get_currentuserinfo();
	
	//Variables
	$commenter = wp_get_current_commenter();
	$req = get_option( 'require_name_email' );
	$aria_req = ( $req ? " aria-required='true'" : '' );

	//Comment Form Args
	$args = array(
		'id_form' => 'commentform',
		'id_submit' => 'submit',
		'title_reply' => __( 'Leave a Reply', 'mp_core' ),
		'title_reply_to' => __( 'Leave a Reply to %s', 'mp_core' ),
		'cancel_reply_link' => __( 'Cancel Reply', 'mp_core' ),
		'label_submit' => __( 'Post Comment', 'mp_core' ),
		'comment_field' => '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>',
		
		'must_log_in' => '<p class="must-log-in">' .  sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( ) ) ) ) . '</p>',
		
		'logged_in_as' => '<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>' ), admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( ) ) ) ) . '</p>',
		
		'comment_notes_before' => '<p class="comment-notes">' . __( 'Your email address will not be published.' ) . '</p>',
		
		'comment_notes_after' => '<p class="form-allowed-tags">' . sprintf( __( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s' ), ' <code>' . allowed_tags() . '</code>' ) . '</p>',
		
		'fields' => apply_filters( 'comment_form_default_fields', array(
		
			'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Name', 'mp_core' ) . '</label> ' . ( $req ? '<span class="required">*</span>' : '' ) . '<input id="author" name="author" placeholder="' . __('Name', 'mp_core') . '" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
			
			'email' => '<p class="comment-form-email"><label for="email">' . __( 'Email', 'mp_core' ) . '</label> ' . ( $req ? '<span class="required">*</span>' : '' ) . '<input id="email" name="email" placeholder="' . __('Email', 'mp_core') . '" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>',
			
			'url' => '<p class="comment-form-url"><label for="url">' . __( 'Website', 'mp_core' ) . '</label>' . '<input id="url" name="url" placeholder="' . __('Website', 'mp_core') . '" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>' 
			) 
		) 
	);
	
	//Filter for custom args
	$args = has_filter('mp_core_comment_form_args') ? apply_filters('mp_core_comment_form_args', $args, $commenter, $req, $aria_req) : $args;
	
	//Call the comment_form function
	comment_form($args); 
		
}