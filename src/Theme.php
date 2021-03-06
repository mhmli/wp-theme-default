<?php

namespace TOPLEVELNAMESPACE\THEMENAMESPACE;

class Theme {

	use Taxonomy;

	/**
	 * the instance of the object, used for singelton check
	 * @var object
	 */
	private static $instance;

	public $themeoptions = [];
	public $version      = '';
	public $themedata    = [];

	/**
	 * Debug helper function. You probably don't need this in a production-quality Theme.
	 */
	public function dump( $var, $die = false ) {
		echo '<pre>' . print_r( $var, 1 ) . '</pre>';
		if ( $die ) {
			die();
		}
	}

	/**
	 * This function will be run when the class is initialized.
	 * Add your hook and filter references here.
	 */
	public function __construct() {
		$this->themeoptions = get_option( 'themeoptions_TEXT_DOMAIN' );
		$this->themedata    = wp_get_theme();
		$this->version      = $this->themedata->Version; // from style.css in the theme root folder
	}

	public function run() {

		/**
		 * Add functionality support rules for this Theme
		 */
		add_action( 'after_setup_theme', [ $this, 'themeSupports' ] );

		/*
		 * Add the CSS files and JavaScripts for the website output.
		 */
		add_action( 'wp_enqueue_scripts', [ $this, 'addFrontendScripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'addFrontendStyles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'dequeueDashicons' ] );
	}

	/**
	 * Creates an instance if one isn't already available,
	 * then return the current instance.
	 * @return object       The class instance.
	 */
	public static function getInstance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Theme ) ) {
			self::$instance          = new Theme;
			self::$instance->name    = self::$instance->themedata->name;
			self::$instance->version = self::$instance->themedata->version;
			self::$instance->prefix  = 'sht';
			self::$instance->error   = __( 'An unexpected error occured.', 'sht' );
			self::$instance->debug   = true;
			if ( ! isset( $_SERVER['HTTP_HOST'] ) || strpos( $_SERVER['HTTP_HOST'], '.hello' ) === false && ! in_array( $_SERVER['REMOTE_ADDR'], [ '127.0.0.1', '::1' ] ) ) {
				self::$instance->debug = false;
			}
		}
		return self::$instance;
	}

	public function themeSupports() {
		/*
		 * Add default posts and comments RSS feed links to head.
		 */
		add_theme_support( 'automatic-feed-links' );

		/*
		 * This theme supports post thumbnails by default.
		 */
		add_theme_support( 'post-thumbnails' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			[
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			]
		);
	}

	/**
	 * Adds the CSS files to the frontend page header using wp_head.
	 */
	public function addFrontendStyles() {
		wp_enqueue_style( 'css-reset', get_template_directory_uri() . '/assets/dist/styles/css-reset.css', null, $this->version );
		wp_enqueue_style( 'theme', get_stylesheet_uri(), [ 'css-reset' ], $this->version, 'all' );
	}

	/**
	 * Adds the JavaScript files to the frontend page header using wp_head.
	 */
	public function addFrontendScripts() {
		// In header
		wp_enqueue_script( 'jquery' ); // Use WordPress core version

		// In footer
		wp_enqueue_script( 'ui', get_template_directory_uri() . '/assets/dist/scripts/ui.js', [ 'jquery' ], $this->version, true );

		/*
		 * Your value for TEXT_DOMAIN_JS must be suitable for use
		 * as a JavaScript object name. i.e. underscores instead
		 * of dashes and no special characters.
		 *
		 * If you don't need to pass translations to JavaScript, then you can delete this.
		 */
		wp_localize_script(
			'ui',
			'TEXT_DOMAIN_JS',
			[
				'translations' => [
					'Hello world' => __( 'Hello world', 'TEXT_DOMAIN' ),
				],
			]
		);
	}

	public function dequeueDashicons() {
		if ( ! is_user_logged_in() ) {
			wp_deregister_style( 'dashicons' );
		}
	}
}

new Theme();
