<?php

require_once 'class-iworks-post-type.php';

class iWorks_Post_Type_Publication extends iWorks_Post_Type {

	private $post_type_name = 'opi_publication';

	public function __construct() {
		parent::__construct();
		add_action( 'init', array( $this, 'custom_post_type' ), 0 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save' ) );
		add_filter( 'the_content', array( $this, 'the_content' ) );
		add_action( 'pre_get_posts', array( $this, 'set_default_order' ) );
		/**
		 * Publications tab on main page
		 */
		add_filter( 'opi_pib_get_systems_publications', array( $this, 'get_random' ), 10, 2 );
		add_filter( 'opi_pib_theme_system_tab_button_more_url', array( $this, 'get_archive_page_url' ), 10, 4 );
	}

	/**
	 * Set default order
	 *
	 * @since 1.3.6
	 */
	public function set_default_order( $query ) {
		if ( is_admin() ) {
			return;
		}
		if ( $this->post_type_name !== $query->get( 'post_type' ) ) {
			return;
		}
		$query->set( 'meta_key', 'opi_publication_year' );
		$query->set(
			'orderby',
			array(
				'meta_value_num' => 'DESC',
				'title'          => 'ASC',
			)
		);
	}

	public function get_archive_page_url( $url, $type, $id, $language ) {
		if ( 'publications' !== $type ) {
			return $url;
		}
		return get_post_type_archive_link( $this->post_type_name );
	}

	public function get_random( $content, $args ) {
		$args                   = wp_parse_args(
			$args,
			array(
				'post_type'      => $this->post_type_name,
				'orderby'        => 'rand',
				'posts_per_page' => 1,
				'wp_doing_ajax'  => apply_filters( 'wp_doing_ajax', false ),
			)
		);
		$args['posts_per_page'] = max( 1, intval( $args['posts_per_page'] ) );
		$the_query              = new WP_Query( $args );
		if ( 'pl_PL' === get_locale() ) {
			$content .= '<span class="section-title">Publikacje Naukowe OPI PIB</span>';
		} else {
			$content .= sprintf( '<span class="section-title">%s</span>', esc_html__( 'Scientific publications of OPI PIB', 'THEME_SLUG' ) );
		}
		if ( $the_query->have_posts() ) {
			ob_start();
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				get_template_part( 'template-parts/single-content', 'publication', $args );
			}
			$content .= ob_get_contents();
			ob_end_clean();
		}
		return $content;
	}

	public function the_content( $content ) {
		if ( get_post_type() !== $this->post_type_name ) {
			return $content;
		}
		$post_ID = get_the_ID();
		$c       = '';
		/**
		 * Authors
		 */
		$value = get_post_meta( $post_ID, 'opi_publication_authors', true );
		if ( ! empty( $value ) ) {
			$c .= sprintf(
				'<p class="opi-publication-authors">%s</p>',
				$value
			);
		}
		/**
		 * Year & where
		 */
		$year  = get_post_meta( $post_ID, 'opi_publication_year', true );
		$value = get_post_meta( $post_ID, 'opi_publication_where', true );
		if ( ! empty( $year ) || ! empty( $value ) ) {
			$c .= '<p class="opi-publication-where">';
			if ( ! empty( $year ) ) {
				$c .= sprintf( '<span class="year">%d</span> ', $year );
			}
			if ( ! empty( $value ) ) {
				$c .= $value;
			}
			$c .= '</p>';
		}
		/**
		 * conference
		 */
		$value = get_post_meta( $post_ID, 'opi_publication_conference', true );
		if ( ! empty( $value ) ) {
			$c .= sprintf(
				'<p class="opi-publication-conference">%s</p>',
				$value
			);
		}
		/**
		 * Content
		 */
		$c .= $content;
		/**
		 * url
		 */
		$value = get_post_meta( $post_ID, 'opi_publication_url', true );
		if ( ! empty( $value ) ) {
			$c .= sprintf(
				'<p class="opi-publication-url"><a href="%1$s" target="_blank">%1$s</a></p>',
				$value
			);
		}
		return $c;
	}

	/**
	 * Register Custom Post Type
	 *
	 * @since 1.3.9
	 */
	public function custom_post_type() {
		$labels = array(
			'name'                  => _x( 'Publications', 'Post Type General Name', 'THEME_SLUG' ),
			'singular_name'         => _x( 'Publication', 'Post Type Singular Name', 'THEME_SLUG' ),
			'menu_name'             => __( 'Publications', 'THEME_SLUG' ),
			'name_admin_bar'        => __( 'Publications', 'THEME_SLUG' ),
			'archives'              => __( 'Publications', 'THEME_SLUG' ),
			'all_items'             => __( 'Publications', 'THEME_SLUG' ),
			'add_new_item'          => __( 'Add New Publication', 'THEME_SLUG' ),
			'add_new'               => __( 'Add New', 'THEME_SLUG' ),
			'new_item'              => __( 'New Publication', 'THEME_SLUG' ),
			'edit_item'             => __( 'Edit Publication', 'THEME_SLUG' ),
			'update_item'           => __( 'Update Publication', 'THEME_SLUG' ),
			'view_item'             => __( 'View Publication', 'THEME_SLUG' ),
			'view_items'            => __( 'View Publication', 'THEME_SLUG' ),
			'search_items'          => __( 'Search Publication', 'THEME_SLUG' ),
			'not_found'             => __( 'Not found', 'THEME_SLUG' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'THEME_SLUG' ),
			'items_list'            => __( 'Publication list', 'THEME_SLUG' ),
			'items_list_navigation' => __( 'Publication list navigation', 'THEME_SLUG' ),
			'filter_items_list'     => __( 'Filter items list', 'THEME_SLUG' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Publication', 'THEME_SLUG' ),
			'exclude_from_search' => true,
			'has_archive'         => true,
			'hierarchical'        => false,
			'label'               => __( 'Publications', 'THEME_SLUG' ),
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-businessperson',
			'public'              => true,
			'show_in_admin_bar'   => true,
			'show_in_menu'        => apply_filters( 'opi_post_type_show_in_menu' . $this->post_type_name, 'edit.php' ),
			'show_in_nav_menus'   => true,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor', 'excerpt' ),
		);
		register_post_type( $this->post_type_name, $args );
	}

	/**
	 * Add meta boxes
	 *
	 * @since 1.3.6
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'opi-publication-data',
			__( 'Publication data', 'THEME_SLUG' ),
			array( $this, 'html_data' ),
			$this->post_type_name,
			'normal',
			'default'
		);
	}

	/**
	 * HTML for metabox
	 *
	 * @since 1.3.6
	 */
	public function html_data( $post ) {
		wp_nonce_field( __CLASS__, '_publication_nonce' );
		?>
<p>
	<label><?php esc_attr_e( 'Language', 'THEME_SLUG' ); ?><br />
		<?php
		wp_dropdown_languages(
			array(
				'selected' => get_post_meta( $post->ID, 'opi_publication_language', true ),
				'name'     => 'opi_publication_language',
			)
		);
		?>
	</label>
</p>
<p>
	<label><?php esc_attr_e( 'Year', 'THEME_SLUG' ); ?><br />
		<select name="opi_publication_year">
			<?php $value = intval( get_post_meta( $post->ID, 'opi_publication_year', true ) ); ?>
			<option value=""><?php esc_html_e( '-- select year --', 'THEME_SLUG' ); ?></option>
		<?php
		for ( $i = date( 'Y' );$i > 2000; $i-- ) {
			printf( '<option value="%1$d" %2$s>%1$d</option>', $i, selected( $value, $i, false ) );
		}
		?>
		</select>
	</label>
</p>
<p>
	<label><?php esc_attr_e( 'Authors', 'THEME_SLUG' ); ?><br />
		<?php $value = get_post_meta( $post->ID, 'opi_publication_authors', true ); ?>
		<input type="text" class="large-text" value="<?php echo esc_attr( $value ); ?>" name="opi_publication_authors" />
	</label>
</p>
<p>
	<label><?php esc_attr_e( 'Where', 'THEME_SLUG' ); ?><br />
		<?php $value = get_post_meta( $post->ID, 'opi_publication_where', true ); ?>
		<input type="text" class="large-text" value="<?php echo esc_attr( $value ); ?>" name="opi_publication_where" />
	</label>
</p>
<p>
	<label><?php esc_attr_e( 'URL', 'THEME_SLUG' ); ?><br />
		<?php $value = get_post_meta( $post->ID, 'opi_publication_url', true ); ?>
		<input type="url" class="large-text" value="<?php echo esc_attr( $value ); ?>" name="opi_publication_url" />
	</label>
</p>
<p>
	<label><?php esc_attr_e( 'Conference', 'THEME_SLUG' ); ?> <small class="description"><?php esc_html_e( '(only when needed)', 'THEME_SLUG' ); ?></small><br />
		<?php $value = get_post_meta( $post->ID, 'opi_publication_conference', true ); ?>
		<input type="text" class="large-text" value="<?php echo esc_attr( $value ); ?>" name="opi_publication_conference" />
	</label>
</p>
		<?php

	}

	/**
	 * Save Publication data.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $post_id Post ID.
	 */
	public function save( $post_ID ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$nonce = filter_input( INPUT_POST, '_publication_nonce' );
		if ( ! wp_verify_nonce( $nonce, __CLASS__ ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_ID ) ) {
			return;
		}
		$this->update_meta( $post_ID, 'opi_publication_language', filter_input( INPUT_POST, 'opi_publication_language' ) );
		$this->update_meta( $post_ID, 'opi_publication_year', filter_input( INPUT_POST, 'opi_publication_year', FILTER_SANITIZE_NUMBER_INT ) );
		$this->update_meta( $post_ID, 'opi_publication_authors', filter_input( INPUT_POST, 'opi_publication_authors' ) );
		$this->update_meta( $post_ID, 'opi_publication_where', filter_input( INPUT_POST, 'opi_publication_where' ) );
		$this->update_meta( $post_ID, 'opi_publication_url', filter_input( INPUT_POST, 'opi_publication_url', FILTER_SANITIZE_URL ) );
		$this->update_meta( $post_ID, 'opi_publication_conference', filter_input( INPUT_POST, 'opi_publication_conference' ) );
	}
}

