<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://loginshield.com/
 * @since      1.0.0
 *
 * @package    LoginShield
 * @subpackage LoginShield/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    LoginShield
 * @subpackage LoginShield/includes
 * @author     Luka Modric <lukamodric.world@gmail.com>
 */
class LoginShield {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      LoginShield_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The display name of this plugin.
	 *
	 * @since    1.0.8
	 * @access   protected
	 * @var      string    $plugin_display_name    The string used to display the name of this plugin.
	 */
	protected $plugin_display_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
        $this->version = loginshield_version();
		$this->plugin_name = loginshield_plugin_name();
        $this->plugin_display_name = loginshield_plugin_display_name();

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_rest_apis();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - LoginShield_Loader. Orchestrates the hooks of the plugin.
	 * - LoginShield_i18n. Defines internationalization functionality.
	 * - LoginShield_Admin. Defines all hooks for the admin area.
	 * - LoginShield_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once LOGINSHIELD_PLUGIN_PATH . 'includes/class-loginshield-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once LOGINSHIELD_PLUGIN_PATH . 'includes/class-loginshield-i18n.php';

        /**
         * The class responsible for defining all Rest APIs
         */
        require_once LOGINSHIELD_PLUGIN_PATH . 'includes/class-loginshield-restapi.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once LOGINSHIELD_PLUGIN_PATH . 'admin/class-loginshield-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once LOGINSHIELD_PLUGIN_PATH . 'public/class-loginshield-public.php';

        /**
         * The class responsible for 3rd party API integration
         */
        require_once LOGINSHIELD_PLUGIN_PATH . 'src/RealmClient.php';
        require_once LOGINSHIELD_PLUGIN_PATH . 'src/Webauthz.php';

		$this->loader = new LoginShield_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the LoginShield_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new LoginShield_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new LoginShield_Admin( $this->get_plugin_name(), $this->get_version(), $this->get_plugin_display_name() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		/**
        * Add LoginShield Plugin Menu
        */
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'loginshield_admin_menu' );

        /**
        * Create a new Table
        */
        $this->loader->add_action( 'show_user_profile', $plugin_admin, 'loginshield_show_user_profile' );
        $this->loader->add_action( 'edit_user_profile', $plugin_admin, 'loginshield_edit_user_profile' );        
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new LoginShield_Public( $this->get_plugin_name(), $this->get_version(), $this->get_plugin_display_name() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	private function define_rest_apis() {

	    $plugin_rest_api = new LoginShield_RestAPI( $this->get_plugin_name(), $this->get_version(), $this->get_plugin_display_name() );

	    $this->loader->add_action( 'rest_api_init', $plugin_rest_api, 'register_rest_api');
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The name of the plugin used to display to the user or administrator.
	 *
	 * @since     1.0.8
	 * @return    string    The display name of the plugin.
	 */
	public function get_plugin_display_name() {
		return $this->plugin_display_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    LoginShield_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	public function render_login_form($attributes, $content = null) {
        $default_attributes = array('shot_title' => false);
        $attributes = shortcode_atts($default_attributes, $attributes);
        $shot_title = $attributes['show_title'];

        if (is_user_logged_in()) {
            return __('You are already sign in.', 'personalize-login');
        }

        $attributes['redirect'] = isset($_REQUEST['redirect_to']) && wp_validate_redirect($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '';

        return $this->get_template_html('loginshield_login_form', $attributes);
    }

    private function get_template_html( $template_name, $attributes = null ) {
	    if (!$attributes) {
	        $attributes = array();
        }

	    ob_start();
	    do_action('personalize_login_before_'.$template_name);
	    require(LOGINSHIELD_PLUGIN_PATH . 'admin/partials/'.$template_name.'.php');
	    do_action('personalize_login_after_'.$template_name);
	    $html = ob_get_contents();
	    ob_end_clean();

	    return $html;
    }

    public function redirect_to_custom_login() {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $redirect_to = isset($_REQUEST['redirect_to']) && wp_validate_redirect($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '';
            $reauth = isset( $_REQUEST['reauth'] ) && filter_var($_REQUEST['reauth'], FILTER_VALIDATE_BOOLEAN );

            if (is_user_logged_in() && !$reauth) {
                $this->redirect_logged_in_user($redirect_to);
                exit;
            }

            $login_url = home_url('custom-login');
            if ($redirect_to) {
                $login_url = add_query_arg('redirect_to', $redirect_to, $login_url);
            }
            $login_url = add_query_arg( 't', time(), $login_url );

            wp_redirect($login_url);
            exit;
        }
    }

    private function redirect_logged_in_user( $redirect_to = null ) {
	    $user = wp_get_current_user();
        if ($redirect_to) {
            $redirect_to = add_query_arg( 't', time(), $redirect_to );
            wp_safe_redirect($redirect_to);
        } else {
            if (user_can($user, 'manage_options')) {
                wp_redirect(admin_url());
            } else {
                wp_redirect(get_dashboard_url());
            }
        }
    }
}
