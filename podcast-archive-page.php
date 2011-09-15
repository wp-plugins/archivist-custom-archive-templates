<?php
/*
Plugin Name: Podcast Archive Page
Plugin URI: 
Description: Shortcode Plugin to display a Podcast Archive by category
Version: 0.1
Author: Eric Teubert
Author URI: ericteubert@googlemail.com
*/

function podcast_archive_page_options()
{
?>
	<div class="wrap">
	  <h2><?php echo __( 'Podcast Archive Options', 'podcast_archive' ) ?></h2>

	  

	</div>
<?php
}

if ( ! class_exists( 'podcast_archive_page' ) ) {
 
	if ( function_exists( 'add_action' ) && function_exists( 'register_activation_hook' ) ) {
		add_action( 'plugins_loaded', array( 'podcast_archive_page', 'get_object' ) );
		register_activation_hook( __FILE__, array( 'podcast_archive_page', 'activation_hook' ) );
	}

	class podcast_archive_page {
 
		static private $classobj = NULL;
		public $textdomain = 'textdomain-podcast_archive_page';
 
		public function __construct() {
			$this->load_textdomain();
			add_shortcode( 'podcast-archive-page', array( $this, 'shortcode' ) );
			add_action( 'admin_menu', array( $this, 'add_menu_entry' ) );
		}
		
		public function shortcode( $atts )
		{
			extract( shortcode_atts( array(
				'category' => 'podcast',
			), $atts ) );

			$this->display_by_category($category);
		}
		
		public function add_menu_entry()
		{
			add_submenu_page( 'options-general.php', 'Podcast Archive', 'Podcast Archive', 'edit_post', 'podcast-archive', 'podcast_archive_page_options' );
		}
		
		public function display_by_category( $category )
		{
			global $post;
			
			$parameters = array(
				'posts_per_page'	=> -1,
				'category_name'		=> $category_name
			);
			$query = new WP_Query( $parameters );
			
			?>
			<table>
				<tr>
					<th></th>
					<th>Titel / Beschreibung</th>
					<th>Dauer</th>
				</tr>
				<?php while ( $query->have_posts() ) : ?>
					<?php $query->the_post(); ?>
					<tr>
						<td>
							<?php if ( has_post_thumbnail() ): ?>
								<?php the_post_thumbnail( array( 75, 75 ) ); ?>
							<?php endif ?>
						</td>
						<td>
							<a href="<?php the_permalink() ?>"><?php the_title(); ?></a>
						</td>
						<td>
							<?php if ( get_post_meta( $post->ID, 'duration', true ) ): ?>
								<?php echo get_post_meta( $post->ID, 'duration', true ); ?>								
							<?php else: ?>
								???
							<?php endif ?>
						</td>
					</tr>
				<?php endwhile; ?>
			</table>
			<?php
			
			wp_reset_postdata();
		}
 
		public function get_object() {
			if ( NULL === self::$classobj ) {
				self::$classobj = new self;
			}
			return self::$classobj;
		}
 
		public function get_textdomain() {
			return $this->textdomain;
		}
 
		public function load_textdomain() {
			load_plugin_textdomain( $this->get_textdomain(), FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}
 
		private function get_plugin_data( $value = 'Version' ) {
			$plugin_data = get_plugin_data( __FILE__ );
			return $plugin_data[ $value ];
		}
 
		static public function activation_hook() {
			global $wp_version;
 
			// Load Text-Domain
			$obj = podcast_archive_page::get_object();
			$obj->load_textdomain();
 
			// check wp version
			if ( ! version_compare( $wp_version, '3.0', '>=' ) ) {
				deactivate_plugins( __FILE__ );
				wp_die( wp_sprintf( '&lt;strong>%s:&lt;/strong> ' . __( 'Sorry, This plugin requires WordPress 3.0+', $obj->get_textdomain() ),  self::get_plugin_data( 'Name' ) ) );
			}
 
			// check php version
			if ( ! version_compare( PHP_VERSION, '5.2.9', '>=' ) ) {
				deactivate_plugins( __FILE__ ); // Deactivate ourself
				wp_die( wp_sprintf( '&lt;strong>%1s:&lt;/strong> ' . __( 'Sorry, This plugin has taken a bold step in requiring PHP 5.2.9+, Your server is currently running PHP %2s, Please bug your host to upgrade to a recent version of PHP which is less bug-prone. At last count, &lt;strong>over 80%% of WordPress installs are using PHP 5.2+&lt;/strong>.', $obj->get_textdomain() ), self::get_plugin_data( 'Name' ), PHP_VERSION ) );
			}
		}
	}
}