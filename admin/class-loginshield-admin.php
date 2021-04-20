<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://loginshield.com/
 * @since      1.0.0
 *
 * @package    LoginShield
 * @subpackage LoginShield/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    LoginShield
 * @subpackage LoginShield/admin
 * @author     Luka Modric <lukamodric.world@gmail.com>
 */
class LoginShield_Admin {

	/**
	 * The unique ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The unique ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The display name of this plugin.
	 *
	 * @since    1.0.8
	 * @access   private
	 * @var      string    $plugin_display_name    The display name of this plugin.
	 */
	private $plugin_display_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * The array of templates that this plugin tracks.
     *
     * @since    1.0.3
     * @var string
     */
    protected $templates;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The unique name of this plugin.
	 * @param      string    $version    The version of this plugin.
     * @param      string    $plugin_display_name       The display name of this plugin.
	 */
    public function __construct( $plugin_name, $version, $plugin_display_name ) {

		$this->plugin_name = $plugin_name;
		$this->plugin_display_name = $plugin_display_name;
		$this->version = $version;
        $this->templates = array();

        // Initialize settings
		add_action( 'admin_init', array( $this,'loginshield_settings_register' ) );
        add_action( 'admin_init', array( $this,'loginshield_activation_redirect' ) );
        
        // Add settings link in plugins page
        add_filter( 'plugin_action_links_loginshield/loginshield.php', array( $this, 'loginshield_admin_setting_link' ) );

        // Add custom template
        add_filter( 'theme_page_templates', array( $this, 'add_new_template' ) );
        add_filter(	'wp_insert_post_data', array( $this, 'register_project_templates' ) );
        add_filter( 'template_include', array( $this, 'view_project_template') );

        // Add shortcodes for Login page
        add_shortcode('loginshield_login_page', array( $this, 'loginshield_login_page'));

        $this->templates = array(
            'templates/loginshield-empty.php' => esc_html__('LoginShield Template', 'loginshield')
        );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in LoginShield_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The LoginShield_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name . 'snackbar', LOGINSHIELD_PLUGIN_URL . 'admin/css/snackbar.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, LOGINSHIELD_PLUGIN_URL . 'admin/css/loginshield-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in LoginShield_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The LoginShield_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name . 'snackbar', LOGINSHIELD_PLUGIN_URL . 'admin/js/snackbar.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . 'realmClientBrowser', LOGINSHIELD_PLUGIN_URL . 'admin/js/realm-client-browser.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . 'loginShieldAdmin', LOGINSHIELD_PLUGIN_URL . 'admin/js/loginshield-admin.js', array( 'jquery' ), $this->version, false );

        wp_localize_script( $this->plugin_name . 'loginShieldAdmin', 'loginshieldSettingAjax', array(
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'site_url'  => get_site_url(),
            'api_base'  => esc_url_raw(rest_url()),
            'nonce'     => wp_create_nonce( 'wp_rest' )
        ));
	}

    public function loginshield_admin_menu(){
        // add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
        add_submenu_page(
            'options-general.php',
            'LoginShield Settings',
            'LoginShield',
            'manage_options',
            'loginshield',
            array( $this, 'loginshield_admin_setting' ) );
    }

    public function loginshield_admin_setting(){
        
        require_once LOGINSHIELD_PLUGIN_PATH . 'includes/util.php';

        /**
         * The file contain plugin setting html form.
         *
         */
        require_once LOGINSHIELD_PLUGIN_PATH . 'admin/partials/loginshield-plugin-setting.php';

    }
    
    /**
     * Add a link to plugin settings from the plugin's row in the all plugins admin page.
     *
     * @since 1.0.7
    */
    public function loginshield_admin_setting_link($links) {
        // Build and escape the URL.
        $url = add_query_arg(
            'page',
            $this->plugin_name,
            admin_url('/options-general.php')
        );
        // Create the link.
        $settings_link = '<a href="' . esc_url($url) . '">' . __( 'Settings' ) . '</a>';
        // Adds the link to the end of the array.
        array_push(
            $links,
            $settings_link
        );
        return $links;
    }

    /**
     * LoginShield Settings Page. This is what users see when they edit their profile.
     *
     * @since 1.0.0
     */
    public function loginshield_show_user_profile($user) {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $isRegistered = get_boolean_user_meta($user_id, 'loginshield_is_registered');
        $isActivated = get_boolean_user_meta($user_id, 'loginshield_is_activated');
        $isConfirmed = get_boolean_user_meta($user_id, 'loginshield_is_confirmed');
        $loginshield_user_id = get_string_user_meta($user_id, 'loginshield_user_id');

        $mode = isset($_GET['mode']) ? sanitize_key($_GET['mode']) : '';
        $loginshield = isset($_GET['loginshield']) && wp_http_validate_url($_GET['loginshield']) ? $_GET['loginshield'] : '';

        ?>
        <h2>LoginShield Management</h2>
        <p>Protect your account with LoginShield</p>
		<table id="LoginShieldForm" class="form-table" <?php if ((!$isRegistered || !$isConfirmed) && isset($mode) && isset($loginshield)): ?>data-mode="<?php echo esc_attr($mode); ?>" data-loginshield="<?php echo esc_attr($loginshield); ?>"<?php endif; ?>>
            <tbody>
                <tr id="RegisterForm" <?php if ($isRegistered && $isConfirmed): ?>style="display: none;"<?php endif; ?>>
                    <th>
                        <label><?php esc_html_e('Security', 'loginshield');?></label>
                    </th>
                    <td>
                        <button type="button" id="ActivateLoginShield" class="button button-primary"><?php esc_html_e('Activate LoginShield', 'loginshield');?></button>
                        <div id="loginshield-content"></div>
                    </td>
                </tr>
                <tr id="ActivateForm" <?php if (!$isRegistered || !$isConfirmed): ?>style="display: none;"<?php endif; ?>>
                    <th>
                        <label><?php esc_html_e('Security', 'loginshield');?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="loginshield_active" name="loginshield_active" <?php if ($isActivated): ?>checked<?php endif; ?>>
                        <label for="loginshield_active"><?php esc_html_e('Protect this account with LoginShield', 'loginshield');?></label>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label><?php esc_html_e('Learn More', 'loginshield');?></label>
                    </th>
                    <td>
                        <a href="https://loginshield.com/article/one-tap-login/" target="_blank">https://loginshield.com/article/one-tap-login/</a>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label><?php esc_html_e('Get the free app', 'loginshield');?></label>
                    </th>
                    <td>
                        <a href="https://loginshield.com/software/" target="_blank">https://loginshield.com/software/</a>
                    </td>
                </tr>
                <?php if(current_user_can('edit_users') && ($isRegistered || $isConfirmed || $isActivated || $loginshield_user_id)): ?>
                <tr>
                    <th>
                        <label><?php esc_html_e('Reset LoginShield', 'loginshield');?></label>
                    </th>
                    <td>
                        <button type="button" id="ResetLoginShield" data-user-id="<?php echo esc_attr($user_id); ?>" class="button button-primary"><?php esc_html_e('Reset LoginShield', 'loginshield');?></button>
                        <p><?php esc_html_e('Reset will deactivate LoginShield for the user and delete the registration. The user will need to register again from their profile page.', 'loginshield');?></p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * LoginShield Settings Page. This is what administrators see when they edit a user's profile.
     *
     * @since 1.0.0
     */
    public function loginshield_edit_user_profile($user) {
        $user_id = $user->ID;
        $isRegistered = get_boolean_user_meta($user_id, 'loginshield_is_registered');
        $isActivated = get_boolean_user_meta($user_id, 'loginshield_is_activated');
        $isConfirmed = get_boolean_user_meta($user_id, 'loginshield_is_confirmed');
        $loginshield_user_id = get_string_user_meta($user_id, 'loginshield_user_id');
        ?>
        <h2>LoginShield Management</h2>
		<table id="LoginShieldForm" class="form-table">
            <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Registered', 'loginshield');?>
                    </th>
                    <td>
                        <?php if($isRegistered && $loginshield_user_id): ?>
                        <?php esc_html_e('Yes', 'loginshield');?> (LoginShield realm-scoped user id: <?php echo esc_html($loginshield_user_id); ?>)
                        <?php else: ?>
                        <?php esc_html_e('No', 'loginshield');?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Enabled', 'loginshield');?>
                    </th>
                    <td>
                        <?php if($isActivated && isConfirmed): ?>
                        <?php esc_html_e('Yes', 'loginshield');?>
                        <?php else: ?>
                        <?php esc_html_e('No', 'loginshield');?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if($isRegistered || $isConfirmed || $isActivated || $loginshield_user_id): ?>
                <tr>
                    <th>
                        <label><?php esc_html_e('Reset LoginShield', 'loginshield');?></label>
                    </th>
                    <td>
                        <button type="button" id="ResetLoginShield" data-user-id="<?php echo esc_attr($user_id); ?>" class="button button-primary"><?php esc_html_e('Reset LoginShield', 'loginshield');?></button>
                        <p><?php esc_html_e('Reset will deactivate LoginShield for the user and delete the registration. The user will need to register again from their profile page.', 'loginshield');?></p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }


    /**
     * Add LoginShield template to the page dropdown (v4.7+)
     *
     */
    public function add_new_template( $posts_templates ) {

        $posts_templates = array_merge( $posts_templates, $this->templates );
        return $posts_templates;
    }

    /**
     * Add LoginShield template to the pages cache in order to trick WordPress
     * into thinking the template file exists where it doens't really exist.
     */
    public function register_project_templates( $atts ) {

        // Create the key used for the themes cache.
        $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

        // Retrieve the cache list.
        // If it doesn't exist, or it's empty prepare an array.
        $templates = wp_get_theme()->get_page_templates();
        if ( empty( $templates ) ) {
            $templates = array();
        }

        // New cache, therefore remove the old one
        wp_cache_delete( $cache_key , 'themes');

        // Now add our template to the list of templates by merging our templates
        // with the existing templates array from the cache.
        $templates = array_merge( $templates, $this->templates );

        // Add the modified cache to allow WordPress to pick it up for listing
        // available templates
        wp_cache_add( $cache_key, $templates, 'themes', 1800 );

        return $atts;

    }

    /**
     * Checks if the template is assigned to the page.
     */
    public function view_project_template( $template ) {

        // Get global post
        global $post;

        // Return template if post is empty
        if ( ! $post ) {
            return $template;
        }

        // Return default template if we don't have a custom one defined
        if ( ! isset( $this->templates[ get_post_meta( $post->ID, '_wp_page_template', true ) ] ) ) {
            return $template;
        }

        $file = LOGINSHIELD_PLUGIN_PATH . 'admin/' . get_post_meta( $post->ID, '_wp_page_template', true );

        // Just to be safe, we check if the file exist first
        if ( file_exists( $file ) ) {
            return $file;
        } else {
            echo $file;
        }

        // Return template
        return $template;

    }


    /**
     * LoginShield Login Page Template
     *
     * @since 1.0.3
     */
    public function loginshield_login_page() {
        /**
         * The file contain plugin login page html
         *
         */
        require_once LOGINSHIELD_PLUGIN_PATH . 'admin/partials/loginshield-login.php';

    }


    /**
     * Redirects the user after plugin activation. The redirect happens only under the
     * following conditions:
     *
     * 1. the plugin was recently activated
     * 2. the current admin user is the one who activated the plugin (we don't redirect someone else)
     * 3. the plugin was activated alone (not as part of a bulk activation of multiple plugins)
     *
     * @since 1.0.4
     */
    public function loginshield_activation_redirect() {
        $activationUserId = get_option( 'loginshield_activation_redirect', false );
        $isMultiPluginActivation = isset($_GET['activate-multi']) && filter_var($_GET['activate-multi'], FILTER_VALIDATE_BOOLEAN );
        if ( is_numeric($activationUserId) && intval( $activationUserId ) === wp_get_current_user()->ID && !$isMultiPluginActivation ) {
            delete_option( 'loginshield_activation_redirect' );
            $url = add_query_arg(
                'page',
                $this->plugin_name,
                admin_url('/options-general.php')
            );
            wp_safe_redirect( $url );
            exit;
        }
    }
    
}
