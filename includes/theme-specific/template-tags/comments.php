<?php 
/**
 * Custom template tag functions specifically used in themes.
 * 
 * @link http://mintplugins.com/doc/move-plugins-core-api/
 *
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Theme Specific Functions
 *
 * @copyright  Copyright (c) 2014, Mint Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */
 
/**
 * Template tag which displays comments in theme
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_comments_template/
 * @see      comments_open()
 * @see      get_comments_number()
 * @see      post_password_required()
 * @see      have_comments()
 * @see      get_comments_number()
 * @see      number_format_i18n()
 * @see      get_the_title()
 * @see      get_comment_pages_count()
 * @see      get_option()
 * @see      _e()
 * @see      previous_comments_link()
 * @see      next_comments_link()
 * @see      wp_list_comments()
 * @see      mp_core_comment()
 * @see      comments_open()
 * @see      post_type_supports()
 * @see      get_post_type()
 * @see      mp_core_comment_form() 
 * @return   void
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
 * 
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_comment/
 * @see      comment_author_link()
 * @see      edit_comment_link()
 * @see      comment_class()
 * @see      comment_ID()
 * @see      has_filter()
 * @see      apply_filters()
 * @see      mp_core_get_avatar()
 * @see      get_comment_author_link()
 * @see      esc_url()
 * @see      get_comment_link()
 * @see      comment_time()
 * @see      get_comment_date()
 * @see      get_comment_time()
 * @see      comment_text()
 * @see      comment_reply_link()
 * @see      edit_comment_link()
 * @param    object $comment Comment data object.
 * @param    array $args
 * @param    int $depth The number of replies deep this comment is.
 * @return   void
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
				</li>
				<?php
						break;
					default :
				?>
				<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
					<article id="comment-<?php comment_ID(); ?>" class="comment">
						
						<?php do_action('mp_core_comment_prepend', $comment, $args, $depth); ?>
										
						<?php do_action('mp_core_comment_append', $comment, $args, $depth); ?>
						
					</article><!-- #comment-## -->
				</li>
				<?php
			break;
		endswitch;
	}
}; // ends check for mp_core_comment()

function mp_core_default_comment( $comment, $args, $depth ){
    
	//Avatar Hooks here
    do_action( 'mp_core_comment_avatar', $comment, $args, $depth );
	
	//Comment Meta Hooks here
    do_action( 'mp_core_comment_meta', $comment, $args, $depth );
	
	//Comment Content Hooks here
    do_action( 'mp_core_comment_content', $comment, $args, $depth );
                	
}
add_action( 'mp_core_comment_prepend', 'mp_core_default_comment', 10, 3 );

/**
 * Displays the avatar in the mp core default comment
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_comment_form/
 * @param    object $comment Comment data object.
 * @param    array $args
 * @param    int $depth The number of replies deep this comment is.
 * @return   void
 */
function mp_core_comment_avatar_callback( $comment, $args, $depth ){
	?>
    <div class="mp-core-comment-avatar">
		<?php
        
        //Filter hook for avatar image size
        $size = has_filter( 'mp_core_comment_avatar_size' ) ? apply_filters( 'mp_core_comment_avatar_size', 96 ) : 96;
        
        echo mp_core_get_avatar( $comment, $size ); 
        
        ?>
    </div>	
    <?php
}
add_action( 'mp_core_comment_avatar', 'mp_core_comment_avatar_callback', 10, 3 );

/**
 * Displays the meta in the mp core default comment
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_comment_form/
 * @param    object $comment Comment data object.
 * @param    array $args
 * @param    int $depth The number of replies deep this comment is.
 * @return   void
 */
function mp_core_comment_meta_callback( $comment, $args, $depth ){
 	?>
	<div class="mp-core-comment-meta">
             
        <div class="comment-author vcard">                    
            <?php printf( sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
        </div><!-- .comment-author .vcard -->
        
        <?php if ( $comment->comment_approved == '0' ) : ?>
            <em><?php _e( 'Your comment is awaiting moderation.', 'temp' ); ?></em>
            <br />
        <?php endif; ?>

        <div class="comment-meta commentmetadata">
        	
            <div class="mp-core-comment-time" datetime="<?php comment_time( 'c' ); ?>">
                <a class="mp-core-comment-time-link" href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
                	<?php printf( _x( '%1$s at %2$s', '1: date, 2: time', 'temp' ), get_comment_date(), get_comment_time() ); ?>
                </a>
            </div>
            
            <div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
                <?php edit_comment_link( __( 'Edit', 'temp' ), '<span class="edit-link">', '<span>' ); ?>
            </div><!-- .reply -->
    
        </div><!-- .comment-meta .commentmetadata -->
        
    </div>
    <?php
}
add_action( 'mp_core_comment_meta', 'mp_core_comment_meta_callback', 10, 3 );

/**
 * Displays the avatar in the mp core default comment
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_comment_form/
 * @param    object $comment Comment data object.
 * @param    array $args
 * @param    int $depth The number of replies deep this comment is.
 * @return   void
 */
function mp_core_comment_content_callback( $comment, $args, $depth ){
	?><div class="comment-content"><?php comment_text(); ?></div><?php
}
add_action( 'mp_core_comment_content', 'mp_core_comment_content_callback', 10, 3 );

/**
 * Displays and allows customization of the comment form
 *
 * Filter mp_core_comments_args
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_comment_form/
 * @see      wp_get_current_commenter()
 * @see      get_option()
 * @see      wp_login_url()
 * @see      has_filter()
 * @see      apply_filters()
 * @see      get_permalink()
 * @see      admin_url()
 * @see      wp_logout_url()
 * @see      allowed_tags()
 * @see      esc_attr()
 * @see      comment_form()
 * @see      paginate_comments_links()
 * @param    object $comment Comment data object.
 * @param    array $args
 * @param    int $depth The number of replies deep this comment is.
 * @return   void
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
	
	//Paginate the comment links
	paginate_comments_links();
		
}