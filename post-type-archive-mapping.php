<?php
/*
Plugin Name: Post Type Archive Mapping
Plugin URI: https://bigwing.com
Description: Map your post type archives to a page
Author: ronalfy
Version: 1.0.0
Requires at least: 4.4
Author URI: https://www.ronalfy.com
Contributors: ronalfy
Text Domain: post-type-archive-mapping
Domain Path: /languages
*/ 

class PostTypeArchiveMapping {
	private static $instance = null;
		
	/**
	 * Return an instance of the class
	 *
	 * Return an instance of the PostTypeArchiveMapping Class.
	 *
	 * @since 1.0.0
	 * @access public static
	 *
	 * @return PostTypeArchiveMapping class instance.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	} //end get_instance
	
	/**
	 * Class constructor.
	 *
	 * Initialize plugin and load text domain for internationalization 
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'init' ), 9 );
		
	} //end constructor

	
	/**
	 * Main plugin initialization
	 *
	 * Initialize admin menus, options,and scripts
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see __construct
	 *
	 */
	public function init() {
				
		//Admin Settings
		add_action( 'admin_init', array( $this, 'init_admin_settings' ) );
		
		add_action( 'pre_get_posts', array( $this, 'maybe_override_archive' ) );
			
	} //end init
	
	public function maybe_override_archive( $query ) {
		$post_types = get_option( 'post-type-archive-mapping', array() );
		if ( empty( $post_types ) || is_admin() ) {
			return;
		}
		foreach( $post_types as $post_type => $post_id ) {
			if ( is_post_type_archive( $post_type ) && 'default' != $post_id ) {
				$post_id = absint( $post_id );
				$query->set( 'post_type', 'page' );
				$query->set( 'page_id', $post_id );
				$query->is_archive = false;
				$query->is_single = true;			
			}
		}
	}
	
	/**
	 * Initialize options 
	 *
	 * Initialize page settings, fields, and sections and their callbacks
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see init
	 *
	 */
	public function init_admin_settings() {
		register_setting(
			'reading',
			'post-type-archive-mapping'
		);
		
		add_settings_section( 'post-type-archive-mapping', _x( 'Post Type Archive Mapping', 'plugin settings heading' , 'post-type-archive-mapping' ), array( $this, 'settings_section' ), 'reading' );
		
		
		add_settings_field( 
			'post-type-archive-mapping', 
			_x( 'Post Type Archive Mapping', 'post-type-archive-mapping' ), 
			array( $this, 'add_settings_post_types' ), 
			'reading', 
			'post-type-archive-mapping'
		);
		
	}
	
	function add_settings_post_types( $args ) {
		$output = get_option( 'post-type-archive-mapping', array() );
		$posts = get_posts( array(
			'post_status' => array( 'publish', 'private' ),
			'posts_per_page' => 1000,
			'post_type' => 'page',
			'orderby' => 'name',
			'order' => 'ASC'	
		) );
		$post_types = get_post_types(
			array(
				'public' => true,
				'has_archive' => true
			)
		);
		foreach( $post_types as $index => $post_type ) {
			$selection = 'default';
			if ( isset( $output[ $post_type ] ) ) {
				$selection = $output[ $post_type ];
			}
			?>
			<p><strong><?php echo esc_html( $post_type ); ?></strong></p>
			<select name="post-type-archive-mapping[<?php echo esc_html( $post_type ); ?>]">
				<option value="default"><?php esc_html_e( 'Default', 'post-type-archive-mapping' ); ?></option>
				<?php
				foreach( $posts as $post ) {
					printf( '<option value="%d" %s>%s</option>', absint( $post->ID ), selected( $output[ $post_type ], $post->ID, false ),esc_html( $post->post_title ) );
				}	
				?>
			</select>
			<?php
		}
		?>
		<?php	
	}
	
	/**
	 * Output settings HTML
	 *
	 * Output any HTML required to go into a settings section
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see init_admin_settings
	 *
	 */
	public function settings_section() {
	}
		
}

add_action( 'plugins_loaded', function() {
	PostTypeArchiveMapping::get_instance();
} );