<?php

class DT_Walker_Category_Radio extends Walker {
	public $tree_type = 'category';
	public $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker:start_lvl()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see wp_terms_checklist()
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see wp_terms_checklist()
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 * @param int    $id       ID of the current term.
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		if ( empty( $args['taxonomy'] ) ) {
			$taxonomy = 'category';
		} else {
			$taxonomy = $args['taxonomy'];
		}

		/* RB4T mod: Replace default $name variable */
		$name = 'radio_tax_input['.$taxonomy.']';
		/* end */

		$args['popular_cats'] = empty( $args['popular_cats'] ) ? array() : $args['popular_cats'];
		$class = in_array( $category->term_id, $args['popular_cats'] ) ? ' class="popular-category"' : '';

		$args['selected_cats'] = empty( $args['selected_cats'] ) ? array() : $args['selected_cats'];

		/* RB4T mod: get first term object */
		$selected_term = ! empty( $args['selected_cats'] ) && ! is_wp_error( $args['selected_cats'] ) ? array_pop( $args['selected_cats'] ) : false;

		// if no term, match the 0 "no term" option
		$selected_id = ( $selected_term ) ? $selected_term : 0;
		/* end */

		if ( ! empty( $args['list_only'] ) ) {
			$aria_cheched = 'false';
			$inner_class = 'category';

			if ( in_array( $category->term_id, $args['selected_cats'] ) ) {
				$inner_class .= ' selected';
				$aria_cheched = 'true';
			}

			/** This filter is documented in wp-includes/category-template.php */
			$output .= "\n" . '<li' . $class . '>' .
				'<div class="' . $inner_class . '" data-term-id=' . $category->term_id .
				' tabindex="0" role="radio" aria-checked="' . $aria_cheched . '">' .
				esc_html( apply_filters( 'the_category', $category->name ) ) . '</div>';
		} else {
			/** This filter is documented in wp-includes/category-template.php */
			$output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" .
				'<label class="selectit"><input value="' . $category->term_id . '" type="radio" name="'.$name.'[]" id="in-'.$taxonomy.'-' . $category->term_id . '"' .
				checked( $category->term_id, $selected_id, false ) .
				disabled( empty( $args['disabled'] ), false, false ) . ' /> ' .
				esc_html( apply_filters( 'the_category', $category->name ) ) . '</label>';
		}
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @see Walker::end_el()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 */
	public function end_el( &$output, $category, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}
}