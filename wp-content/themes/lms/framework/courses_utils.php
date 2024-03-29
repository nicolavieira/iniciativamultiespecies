<?php require_once("../../../../wp-load.php"); ?>
<?php 

$dtLMSAjaxNonce = $_REQUEST['dtLMSAjaxNonce'];
if(!isset($dtLMSAjaxNonce) || !wp_verify_nonce($dtLMSAjaxNonce, 'dtLMSAjaxNonce')) {
	echo '<div class="dt-sc-info-box">'.esc_html__('Ajax token missing!', 'lms').'</div>';
	exit();
}

$post_id = $_REQUEST['post_id'];

$course_page_type = isset($_REQUEST['course_page_type']) ? $_REQUEST['course_page_type'] : '';

if($course_page_type == 'archive' || $course_page_type == 'tax-archive') {
	
	$post_layout = dttheme_option('dt_course','archives-post-layout'); 
	$post_layout = !empty($post_layout) ? $post_layout : "one-half-column";
	
	$page_layout = dttheme_option('dt_course','archives-layout'); 
	$page_layout = !empty($page_layout) ? $page_layout : "content-full-width";
	
	$post_per_page = get_option('posts_per_page');
	
} else {
	
	$tpl_default_settings = get_post_meta( $post_id, '_tpl_default_settings', TRUE );
	$tpl_default_settings = is_array( $tpl_default_settings ) ? $tpl_default_settings  : array();
	
	$post_layout  = array_key_exists( "courses-post-layout", $tpl_default_settings ) ? $tpl_default_settings['courses-post-layout'] : "one-half-column";
	$post_per_page	=	isset($tpl_default_settings['courses-post-per-page']) ? $tpl_default_settings['courses-post-per-page'] : -1;
	
	if($GLOBALS['force_enable'] == true)
		$page_layout = $GLOBALS['page_layout'];
	else
		$page_layout  = array_key_exists( "layout", $tpl_default_settings ) ? $tpl_default_settings['layout'] : "content-full-width";

}

if(defined('ICL_LANGUAGE_CODE') && !empty($_REQUEST['lang']))
{
	global $sitepress;
	$sitepress->switch_lang($_REQUEST['lang'], true);
}

$grid_view = $list_view = $layout_class = $post_class = $post_thumbnail = "";

switch($post_layout):

	case 'one-half-column';
		$post_class = "column dt-sc-one-half";
		$firstcnt = 2;
		$grid_view = 'active';
		$post_thumbnail = 'blogcourse-two-column';
		if($page_layout == 'with-left-sidebar' || $page_layout == 'with-right-sidebar') $post_thumbnail = 'course-two-column';
		else $post_thumbnail = 'blogcourse-two-column';
	break;

	case 'one-third-column':
		$post_class = "column dt-sc-one-third";
		$firstcnt = 3;
		$grid_view = 'active';
		$post_thumbnail = 'blogcourse-three-column';
	break;

endswitch;

switch ( $page_layout ) {
	case 'with-left-sidebar':
	case 'with-right-sidebar':
		$post_thumbnail .= "-single-sidebar";
	break;

	case 'both-sidebar':
		$post_thumbnail .= "-both-sidebar";
	break;
}


$curr_page = isset($_REQUEST['curr_page']) ? $_REQUEST['curr_page'] : 1;
$offset = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0;

$view_type = isset($_REQUEST['view_type']) ? $_REQUEST['view_type'] : 'grid';
$price_type = isset($_REQUEST['price_type']) ? $_REQUEST['price_type'] : 'all';
$courses_type = isset($_REQUEST['courses_type']) ? $_REQUEST['courses_type'] : 'all';

$courses_category = isset($_REQUEST['courses_category']) ? $_REQUEST['courses_category'] : 'all';
$courses_category = (!is_array($courses_category) && strpos($courses_category, ',') !== false) ? explode(',', $courses_category) : $courses_category;
$courses_category_str = is_array($courses_category) ? implode(',', $courses_category) : $courses_category;

$filter_classes = isset($_REQUEST['filter_classes']) ? $_REQUEST['filter_classes'] : array();
$filter_classes = (!is_array($filter_classes) && strpos($filter_classes, ',') !== false) ? explode(',', $filter_classes) : $filter_classes;
$filter_classes = (!is_array($filter_classes)) ? array($filter_classes) : $filter_classes;
$filter_classes_str = is_array($filter_classes) ? implode(',', $filter_classes) : $filter_classes;

$class_courses = array();
if(is_array($filter_classes)) {
	foreach($filter_classes as $filter_class) {
		$class_courses_list = get_post_meta($filter_class, "dt-class-courses", true);
		$class_courses_list = (is_array($class_courses_list) && !empty($class_courses_list)) ? $class_courses_list : array();
		$class_courses = array_merge($class_courses, $class_courses_list);	
	}
}
$class_courses = array_unique($class_courses);

/* Change b/w list and grid view */
if( isset($view_type) && $view_type === "list" ) {
	$layout_class = "course-list-view";
	$firstcnt = 1;
	$list_view = 'active';
	$grid_view = '';
} elseif( isset($view_type) && $view_type === "grid" ) {
	$layout_class = '';
	$grid_view = 'active';
	$list_view = '';
} 

$payment_method = dttheme_option('general','payment-method');

/* Configured all datas here to access in ajax function */
echo '<span id="dt-course-datas" data-postid="'.$post_id.'" data-view_type="'.$view_type.'" data-postperpage="'.$post_per_page.'" data-curr_page="'.$curr_page.'" data-offset="'.$offset.'" data-price_type="'.$price_type.'" data-courses_type="'.$courses_type.'" data-courses_category="'.$courses_category_str.'" data-filter_classes="'.$filter_classes_str.'" style="display:none;"></span>';


if($courses_type != 'popular') {
	
	$args = array( 'offset'=>$offset, 'paged' => $curr_page ,'posts_per_page' => $post_per_page, 'post__in' => $class_courses, 'post_type' => 'dt_courses','meta_query'=>array(), 'tax_query'=>array(), 'orderby' => 'menu_order', 'order' => 'ASC');

	if($payment_method == 'woocommerce') {
		
		if($price_type == 'paid') {
			
			$args['meta_query'][] = array(
							'key'     => 'dt-course-product-id',
							'value'   => 0,
							'type'    => 'numeric',
							'compare' => '>'
							);
							
		} else if($price_type == 'free') {
			
			$args['meta_query'][] = array(
							'key'     => 'dt-course-product-id',
							'compare' => 'NOT EXISTS'
							);
							
		} 
	
	} else {
		
		if($price_type == 'paid') {
			
			$args['meta_query'][] = array(
							'key'     => 'starting-price',
							'value'   => 0,
							'type'    => 'numeric',
							'compare' => '>'
							);
							
		} else if($price_type == 'free') {
			
			$args['meta_query'][] = array(
							'key'     => 'starting-price',
							'compare' => 'NOT EXISTS'
							);
							
		} 
	
	}
	
	if($courses_type == 'featured') {
		
		$args['meta_query'][] = array(
						'key'     => 'featured-course',
						'compare' => 'EXISTS'
						);
							
	}
	
	if($course_page_type == 'tax-archive') {

		$args['tax_query'][] = array( 'taxonomy' => 'course_category',
						'field' => 'id',
						'terms' => $post_id,
						'operator' => 'IN'
						);
						
	} else if($courses_category != 'all' && $courses_category != '') {
		
		$args['tax_query'][] = array( 'taxonomy' => 'course_category',
						'field' => 'id',
						'terms' => $courses_category,
						'operator' => 'IN'
						);
		
	}
			
	$pholder = dttheme_option('general', 'disable-placeholder-images');
		
	$wp_query->query( $args );
	if( $wp_query->have_posts() ):  
	
		echo '<div class="dt-sc-results-found">'.esc_html__('Records Found', 'lms').' : <span>'.$wp_query->found_posts.'</span></div>';
		
		while( $wp_query->have_posts() ): 
			$wp_query->the_post();
	
			$s2_level = "access_s2member_ccap_cid_{$post->ID}";
			
			$firstcls = $temp_class = '';
			$no = $wp_query->current_post+1;
			
			if(($no%$firstcnt) == 1){ $firstcls = ' first'; }
			$temp_class = 'class="'.$post_class.' '.$firstcls.'"';
			
			$course_settings = get_post_meta(get_the_ID(), '_course_settings');
			
			if( $grid_view == 'active' ) {
			echo '<div '.$temp_class.'>';
			}
					
			?>
			<article id="post-<?php echo get_the_ID(); ?>" class="<?php echo implode(" ", get_post_class("dt-sc-custom-course-type {$layout_class}", get_the_ID())); ?>">
			
				<div class="dt-sc-course-thumb">
					<a href="<?php echo the_permalink(); ?>" >
						<?php
						if(has_post_thumbnail()):
							$attachment_id = get_post_thumbnail_id(get_the_id());
							$img_attributes = wp_get_attachment_image_src($attachment_id, $post_thumbnail);
							echo "<img src='".$img_attributes[0]."' width='".$img_attributes[1]."' height='".$img_attributes[2]."' />";
						 elseif($pholder != 'on'): ?>
							<img src="http<?php echo dttheme_ssl(); ?>://placehold.it/1170x822&text=<?php echo get_the_title(); ?>" alt="<?php echo get_the_title(); ?>" />
						<?php endif; ?>
					 </a>
					<div class="dt-sc-course-overlay">
						<a title="<?php echo get_the_title(); ?>" href="<?php echo the_permalink(); ?>" class="dt-sc-button small white"> <?php echo esc_html__('View Course', 'lms'); ?> </a>
					</div>
				</div>			
				
				<?php
				$lesson_args = array('post_type' => 'dt_lessons', 'posts_per_page' => -1, 'meta_key' => 'dt_lesson_course', 'meta_value' => get_the_ID() );
				$lessons_array = get_pages( $lesson_args );
				
				$count = $duration = 0;
				if(count($lessons_array) > 0) {
					foreach($lessons_array as $lesson) {
						$lesson_data = get_post_meta($lesson->ID, '_lesson_settings');
						if(isset($lesson_data[0]['lesson-duration'])) $duration = $duration + $lesson_data[0]['lesson-duration'];
						$count++;
					}
				}
				
				if($duration > 0) {
					$hours = floor($duration/60); 
					$mins = $duration % 60; 
					if(strlen($mins) == 1) $mins = '0'.$mins;
					if(strlen($hours) == 1) $hours = '0'.$hours;
					if($hours == 0) {
						$duration = '00 : '.$mins;
					} else {
						$duration = $hours . ' : ' . $mins; 				
					}
				}
				?>
				
				<div class="dt-sc-course-details">	
				
					<?php if($list_view == 'active') { ?>
					
                           <h5><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h5>
                           
                            <?php
							if(dttheme_check_if_course_exists_in_class(get_the_ID())) {
								echo '<div class="dt-sc-course-class"><i class="fa fa-institution"></i>'.dttheme_get_course_classes_links(get_the_ID()).'</div>';
							}
							?>
                        
							<?php
							if($payment_method == 'woocommerce') {
								
								echo dttheme_get_course_details_linked_with_products( get_the_ID(), 'archive' );
								
							} else {
								
								echo dttheme_get_course_details_linked_with_s2member( get_the_ID(), 'archive' );
	
							}
							?>
					<?php } else { ?>
							
							<?php
							
							if($payment_method == 'woocommerce') {
								
								echo dttheme_get_course_details_linked_with_products( get_the_ID(), 'archive' );
								
							} else {
								
								echo dttheme_get_course_details_linked_with_s2member( get_the_ID(), 'archive' );
							
							}
							?>
	
							<h5><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h5>
                            
                            <?php
							if(dttheme_check_if_course_exists_in_class(get_the_ID())) {
								echo '<div class="dt-sc-course-class"><i class="fa fa-institution"></i>'.dttheme_get_course_classes_links(get_the_ID()).'</div>';
							}
							?>
					
					<?php } ?>
					
					<?php
					$enable_certificate = get_post_meta(get_the_ID(), 'enable-certificate', true);
					$enable_badge = get_post_meta(get_the_ID(), 'enable-badge', true);
					
					if($enable_certificate == 'true' || $enable_badge == 'true') {
						echo '<div class="dt-sc-certificate-badge">';
							if($enable_certificate == 'true') {
								echo '<span class="certificate"></span>';	
							}
							if($enable_badge == 'true') {
								echo '<span class="badge"></span>';	
							}
						echo '</div>';
					}
					?>
	
					<div class="dt-sc-course-meta">
						<p> <?php the_terms(get_the_ID(), 'course_category', ' ', ', ', ' '); ?> </p>
						<p> <?php echo "{$count}".'&nbsp;'.esc_html__('Lessons', 'lms'); ?> </p>
					</div>
					
					<?php if($list_view == 'active') { ?>
						<div class="dt-sc-course-desc">
							<?php echo get_the_excerpt(); ?>
						</div>
					<?php } ?>
					
					<div class="dt-sc-course-data">
						<div class="dt-sc-course-duration">
							<i class="fa fa-clock-o"> </i>
							<span> <?php echo "{$duration}"; ?> </span>
						</div>
						<?php echo do_shortcode('[ratings id="'.get_the_ID().'"]'); ?>
					</div>
														
				</div>
			
			</article>
			<?php
			if( $grid_view == 'active' ) {
			echo '</div>';
			}
			?>    
		
		<?php 
		endwhile; 
	else:
		echo '<div class="dt-sc-info-box">'.esc_html__('No Courses Found!', 'lms').'</div>';
	endif; 
	
	$total_posts = $wp_query->found_posts;

} else {
	
	/* Manually queried to list the popular courses based on wp-postratings(plugin) */
	
	global $wpdb;
		
		$table1 = $wpdb->prefix . "ratings";
		$table2 = $wpdb->prefix . "posts";
		$table3 = $wpdb->prefix . "term_relationships";
		$table4 = $wpdb->prefix . "term_taxonomy";
		
		if($course_page_type == 'tax-archive') {
			
			$cp_qry1 = "SELECT b.* FROM $table1 a, $table2 b, $table3 tr, $table4 tt  WHERE a.rating_postid = b.ID and b.post_type='dt_courses' and b.post_status = 'publish' AND tt.term_id = {$post_id} AND tr.term_taxonomy_id = tt.term_taxonomy_id and b.ID = tr.object_id group by a.rating_postid order by avg(a.rating_rating) desc";
			
			$cs_cnt = 0;
			$wp_course_cnt = $wpdb->get_results( $cp_qry1 );
			$cs_cnt = count($wp_course_cnt);
			
			if($post_per_page == -1 ) $post_per_page = $cs_cnt;
			
			$cp_qry2 = "SELECT b.* FROM $table1 a, $table2 b, $table3 tr, $table4 tt  WHERE a.rating_postid = b.ID and b.post_type='dt_courses' and b.post_status = 'publish' AND tt.term_id = {$post_id} AND tr.term_taxonomy_id = tt.term_taxonomy_id and b.ID =  tr.object_id group by a.rating_postid order by avg(a.rating_rating) desc LIMIT $offset, $post_per_page";
			
		} else {
			
			$cp_qry1 = "SELECT a.* FROM $table1 a, $table2 b WHERE a.rating_postid = b.ID and b.post_type='dt_courses' and b.post_status = 'publish' group by a.rating_postid order by avg(a.rating_rating) desc";
			
			$cs_cnt = 0;
			$wp_course_cnt = $wpdb->get_results( $cp_qry1 );
			$cs_cnt = count($wp_course_cnt);
			
			if($post_per_page == -1 ) $post_per_page = $cs_cnt;
			
			$cp_qry2 = "SELECT a.*, b.* FROM $table1 a, $table2 b WHERE a.rating_postid = b.ID and b.post_type='dt_courses' and b.post_status = 'publish' group by a.rating_postid order by avg(a.rating_rating) desc LIMIT $offset, $post_per_page";		
		
		}
	
	
	

		
	
	$pholder = dttheme_option('general', 'disable-placeholder-images');
	
	$wp_course_qry = $wpdb->get_results( $cp_qry2 );
	$cs_num = 0;
	if(!empty($wp_course_qry)) {
		
		echo '<div class="dt-sc-results-found">'.esc_html__('Records Found', 'lms').' : <span>'.$cs_cnt.'</span></div>';
		
		foreach($wp_course_qry as $course_item) :
			
			$course_item_id = $course_item -> ID;
			
		    $s2_level = "access_s2member_ccap_cid_{$course_item_id}";
			
			$firstcls = $temp_class = '';
			$no = $cs_num + 1;
			
			if(($no%$firstcnt) == 1){ $firstcls = ' first'; }
			$temp_class = 'class="'.$post_class.' '.$firstcls.'"';
			
			$course_settings = get_post_meta($course_item_id, '_course_settings');
			
			if( $grid_view == 'active' ) {
			echo '<div '.$temp_class.'>';
			}
			?>
			<article id="post-<?php echo esc_attr( $course_item_id ); ?>" class="<?php echo implode(" ", get_post_class("dt-sc-custom-course-type {$layout_class}", $course_item_id)); ?>">
			
				<div class="dt-sc-course-thumb">
					<a href="<?php echo get_permalink($course_item_id); ?>" >
						<?php
						if(has_post_thumbnail($course_item_id)):
							$attachment_id = get_post_thumbnail_id($course_item_id);
							$img_attributes = wp_get_attachment_image_src($attachment_id, $post_thumbnail);
							echo "<img src='".$img_attributes[0]."' width='".$img_attributes[1]."' height='".$img_attributes[2]."' />";
						elseif($pholder != 'on'): ?>
							<img src="http<?php echo dttheme_ssl(); ?>://placehold.it/1170x822&text=<?php echo get_the_title(); ?>" alt="<?php echo esc_attr( $course_item->post_title ); ?>" />
						<?php endif; ?>
					 </a>
                    <div class="dt-sc-course-overlay">
                        <a title="<?php echo esc_attr( $course_item->post_title ); ?>" href="<?php echo get_permalink($course_item_id); ?>" class="dt-sc-button small white"> <?php echo esc_html__('View Course', 'lms'); ?> </a>
                    </div>
				</div>
				
				<?php
				$lesson_args = array('post_type' => 'dt_lessons', 'posts_per_page' => -1, 'meta_key' => 'dt_lesson_course', 'meta_value' => $course_item_id );
				$lessons_array = get_pages( $lesson_args );
				
				$count = $duration = 0;
				if(count($lessons_array) > 0) {
					foreach($lessons_array as $lesson) {
						$lesson_data = get_post_meta($lesson->ID, '_lesson_settings');
						if(isset($lesson_data[0]['lesson-duration'])) $duration = $duration + $lesson_data[0]['lesson-duration'];
						$count++;
					}
				}
				
				if($duration > 0) {
					$hours = floor($duration/60); 
					$mins = $duration % 60; 
					if(strlen($mins) == 1) $mins = '0'.$mins;
					if(strlen($hours) == 1) $hours = '0'.$hours;
					if($hours == 0) {
						$duration = '00 : '.$mins;
					} else {
						$duration = $hours . ' : ' . $mins; 				
					}
				}
				?>
				
				<div class="dt-sc-course-details">	
                
                	<?php if($list_view == 'active') { ?>
                    
                        <h5><a href="<?php echo get_permalink($course_item_id); ?>" title="<?php echo esc_attr( $course_item->post_title ); ?>"><?php echo esc_html( $course_item->post_title ); ?></a></h5>
						
                        <?php
						
						if($payment_method == 'woocommerce') {
							
							echo dttheme_get_course_details_linked_with_products( $course_item_id, 'archive' );
							
						} else {
							
							echo dttheme_get_course_details_linked_with_s2member( $course_item_id, 'archive' );
							
						}
						?>
                                        
                    <?php } else { ?>
                    	
                        <?php
						
						if($payment_method == 'woocommerce') {
							
							echo dttheme_get_course_details_linked_with_products( $course_item_id, 'archive' );
														
						} else {
                    	
							echo dttheme_get_course_details_linked_with_s2member( $course_item_id, 'archive' );

						}
						?>
                        
                        <h5><a href="<?php echo get_permalink($course_item_id); ?>" title="<?php echo esc_attr( $course_item->post_title ); ?>"><?php echo esc_html( $course_item->post_title ); ?></a></h5>
                    
                    <?php } ?>
                    
					<?php
                    $enable_certificate = get_post_meta(get_the_ID(), 'enable-certificate', true);
                    $enable_badge = get_post_meta(get_the_ID(), 'enable-badge', true);
                    
                    if($enable_certificate == 'true' || $enable_badge == 'true') {
                        echo '<div class="dt-sc-certificate-badge">';
							if($enable_certificate == 'true') {
								echo '<span class="certificate"></span>';	
							}
							if($enable_badge == 'true') {
								echo '<span class="badge"></span>';	
							}
                        echo '</div>';
                    }
                    ?>
                    
                    <div class="dt-sc-course-meta">
                        <p> <?php the_terms($course_item_id, 'course_category', ' ', ', ', ' '); ?> </p>
                        <p> <?php echo "{$count}".'&nbsp;'.esc_html__('Lessons', 'lms'); ?> </p>
                    </div>
                
					<?php if($list_view == 'active') { ?>
                        <div class="dt-sc-course-desc">
                            <?php echo "{$course_item->post_excerpt}"; ?>
                        </div>
                    <?php } ?>
                
                    <div class="dt-sc-course-data">
                        <div class="dt-sc-course-duration">
                            <i class="fa fa-clock-o"> </i>
                            <span> <?php echo "{$duration}"; ?> </span>
                        </div>
                        <?php echo do_shortcode('[ratings id="'.$course_item_id.'"]'); ?>
                    </div>
                
				</div>
			
			</article>
			<?php
			if( $grid_view == 'active' ) {
			echo '</div>';
			}
			
			$cs_num++;
			
		endforeach;
		
	} else {
		echo '<div class="dt-sc-info-box">'.esc_html__('No Courses Found!', 'lms').'</div>';
	}


	$total_posts = $cs_cnt;

}

/* Pagination to work with ajax */
echo dtthemes_ajax_pagination($post_per_page, $curr_page, $total_posts, $post_id);
?>