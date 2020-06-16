<?php do_action( 'bp_before_directory_members_page' ); ?>
<div id="buddypress">

	<?php do_action( 'bp_before_directory_members' ); ?>
	<?php do_action( 'bp_before_directory_members_content' ); ?>

	<div id="members-dir-search" class="dir-search" role="search">
		<?php bp_directory_members_search_form(); ?>
	</div><!-- members-dir-search -->

	<div class="dt-sc-hr-invisible-small"></div>

	<?php do_action( 'bp_before_directory_members_tabs' ); ?>

	<div class="clear"> </div>

	<form action="" method="post" id="members-directory-form" class="dir-form">

		<div class="item-list-tabs" role="navigation">
			<ul>
				<li class="selected" id="members-all"><a href="<?php bp_members_directory_permalink(); ?>"><?php printf( esc_html__( 'All Members %s', 'lms' ), '<span>'.bp_get_total_member_count().'</span>' ); ?></a></li>

				<?php if ( is_user_logged_in() && bp_is_active( 'friends' ) && bp_get_total_friend_count( bp_loggedin_user_id() ) ) : ?>
					<li id="members-personal"><a href="<?php echo bp_loggedin_user_domain() . bp_get_friends_slug() . '/my-friends/'; ?>"><?php printf( esc_html__( 'My Friends %s', 'lms' ), '<span>'.bp_get_total_friend_count( bp_loggedin_user_id() ).'</span>' ); ?></a></li>
				<?php endif; ?>

				<?php do_action( 'bp_members_directory_member_types' ); ?>

			</ul>
		</div><!-- .item-list-tabs -->


		<div class="item-list-tabs" id="subnav" role="navigation">
			<ul>
				<?php do_action( 'bp_members_directory_member_sub_types' ); ?>

				<li id="members-order-select" class="last filter">
					<label for="members-order-by"><?php esc_html_e( 'Order By:', 'lms' ); ?></label>
					<select id="members-order-by">
						<option value="active"><?php esc_html_e( 'Last Active', 'lms' ); ?></option>
						<option value="newest"><?php esc_html_e( 'Newest Registered', 'lms' ); ?></option>

						<?php if ( bp_is_active( 'xprofile' ) ) : ?>
							<option value="alphabetical"><?php esc_html_e( 'Alphabetical', 'lms' ); ?></option>
						<?php endif; ?>

						<?php do_action( 'bp_members_directory_order_options' ); ?>
					</select>
				</li>
			</ul>
		</div>
		

		<div id="members-dir-list" class="members dir-list">
			<?php bp_get_template_part( 'members/members-loop' ); ?>
		</div>

			<?php do_action( 'bp_directory_members_content' ); ?>

			<?php wp_nonce_field( 'directory_members', '_wpnonce-member-filter' ); ?>

			<?php do_action( 'bp_after_directory_members_content' ); ?>

	</form>		
	<?php do_action( 'bp_after_directory_members' ); ?>
</div>		
<?php do_action( 'bp_after_directory_members_page' );?>