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
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
    public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->templates = array();

        // Initialize settings
        add_action( 'admin_init', array( $this,'loginshield_include_files' ) );
		add_action( 'admin_init', array( $this,'loginshield_settings_register' ) );
        add_action( 'admin_init', array( $this,'loginshield_activation_redirect' ) );

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

		wp_enqueue_style( $this->plugin_name . 'snackbar', plugin_dir_url( __FILE__ ) . 'css/snackbar.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/loginshield-admin.css', array(), $this->version, 'all' );

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

		wp_enqueue_script( $this->plugin_name . 'snackbar', plugin_dir_url( __FILE__ ) . 'js/snackbar.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . 'realmClientBrowser', plugin_dir_url( __FILE__ ) . 'js/realm-client-browser.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . 'loginShieldAdmin', plugin_dir_url( __FILE__ ) . 'js/loginshield-admin.js', array( 'jquery' ), $this->version, false );

        wp_localize_script( $this->plugin_name . 'loginShieldAdmin', 'loginshieldSettingAjax', array(
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'site_url'  => get_site_url(),
            'api_base'  => esc_url_raw(rest_url()),
            'nonce'     => wp_create_nonce( 'wp_rest' )
        ));
	}
    /**
     * Inculdes files in plugin .
     *
     * @since    1.0.0
     */
    public function loginshield_include_files() {

        /**
         * Inculde admin section files
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/loginshield-option-fields.php';
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
        /**
         * The file contain plugin setting html form.
         *
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/loginshield-plugin-setting.php';

    }

    /**
     * LoginShield Settings Page. This is what users see when they edit their profile.
     *
     * @since 1.0.0
     */
    public function loginshield_show_user_profile($user) {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $isRegistered = get_user_meta($user_id, 'loginshield_is_registered', true);
        $isActivated = get_user_meta($user_id, 'loginshield_is_activated', true);
        $isConfirmed = get_user_meta($user_id, 'loginshield_is_confirmed', true);
        $loginshield_user_id = get_user_meta($user_id, 'loginshield_user_id', true);

        $mode = $variable = $_GET['mode'];
        $loginshield = $variable = $_GET['loginshield'];

        ?>
        <h2>LoginShield Management</h2>
		<table id="LoginShieldForm" class="form-table" <?php if ((!$isRegistered || !$isConfirmed) && isset($mode) && isset($loginshield)): ?>data-mode="<?php echo esc_attr($mode); ?>" data-loginshield="<?php echo esc_attr($loginshield); ?>"<?php endif; ?>>
            <tbody>
                <tr id="RegisterForm" <?php if ($isRegistered && $isConfirmed): ?>style="display: none;"<?php endif; ?>>
                    <th>
                        <label><?php esc_html_e('Register LoginShield', 'crf');?></label>
                    </th>
                    <td>
                        <button type="button" id="ActivateLoginShield" class="button button-primary"><?php esc_html_e('Register LoginShield', 'crf');?></button>
                        <div id="loginshield-content"></div>
                    </td>
                </tr>
                <tr id="ActivateForm" <?php if (!$isRegistered || !$isConfirmed): ?>style="display: none;"<?php endif; ?>>
                    <th>
                        <label><?php esc_html_e('Security', 'crf');?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="loginshield_active" name="loginshield_active" <?php if ($isActivated): ?>checked<?php endif; ?>>
                        <label for="loginshield_active"><?php esc_html_e('Protect this account with LoginShield', 'crf');?></label>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label><?php esc_html_e('Learn More', 'crf');?></label>
                    </th>
                    <td>
                        <a href="https://loginshield.com/article/one-tap-login/" target="_blank">https://loginshield.com/article/one-tap-login/</a>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label><?php esc_html_e('Get the free app', 'crf');?></label>
                    </th>
                    <td>
                        <a href="https://loginshield.com/software/" target="_blank">https://loginshield.com/software/</a>
                    </td>
                </tr>
                <?php if(current_user_can('edit_users') && ($isRegistered || $isConfirmed || $isActivated || $loginshield_user_id)): ?>
                <tr>
                    <th>
                        <label><?php esc_html_e('Reset LoginShield', 'crf');?></label>
                    </th>
                    <td>
                        <button type="button" id="ResetLoginShield" data-user-id="<?php echo esc_attr($user_id); ?>" class="button button-primary"><?php esc_html_e('Reset LoginShield', 'crf');?></button>
                        <p><?php esc_html_e('Reset will deactivate LoginShield for the user and delete the registration. The user will need to register again from their profile page.', 'crf');?></p>
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
        $isRegistered = get_user_meta($user_id, 'loginshield_is_registered', true);
        $isActivated = get_user_meta($user_id, 'loginshield_is_activated', true);
        $isConfirmed = get_user_meta($user_id, 'loginshield_is_confirmed', true);
        $loginshield_user_id = get_user_meta($user_id, 'loginshield_user_id', true);
        ?>
        <h2>LoginShield Management</h2>
		<table id="LoginShieldForm" class="form-table">
            <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Registered', 'crf');?>
                    </th>
                    <td>
                        <?php if($isRegistered && $loginshield_user_id): ?>
                        <?php esc_html_e('Yes', 'crf');?> (LoginShield realm-scoped user id: <?php echo esc_html($loginshield_user_id); ?>)
                        <?php else: ?>
                        <?php esc_html_e('No', 'crf');?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Enabled', 'crf');?>
                    </th>
                    <td>
                        <?php if($isActivated && isConfirmed): ?>
                        <?php esc_html_e('Yes', 'crf');?>
                        <?php else: ?>
                        <?php esc_html_e('No', 'crf');?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if($isRegistered || $isConfirmed || $isActivated || $loginshield_user_id): ?>
                <tr>
                    <th>
                        <label><?php esc_html_e('Reset LoginShield', 'crf');?></label>
                    </th>
                    <td>
                        <button type="button" id="ResetLoginShield" data-user-id="<?php echo esc_attr($user_id); ?>" class="button button-primary"><?php esc_html_e('Reset LoginShield', 'crf');?></button>
                        <p><?php esc_html_e('Reset will deactivate LoginShield for the user and delete the registration. The user will need to register again from their profile page.', 'crf');?></p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Register loginshield settings options.
     *
     * @since    1.0.0
     */
    public function loginshield_settings_register() {
        /**
         * Get loginshield option fields
         */
        $args = '';
        if(function_exists('loginshield_option_fields')){
            $args = loginshield_option_fields();
        }
        if(!empty($args)) :
            foreach($args as $key => $val) :
                register_setting( 'loginshield-settings', $val );
            endforeach;
        endif;
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

        $file = plugin_dir_path( __FILE__ ). get_post_meta( $post->ID, '_wp_page_template', true );

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
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/loginshield-login.php';

    }


    /**
     * Redirects the user after plugin activation.
     *
     * @since 1.0.4
     */
    public function loginshield_activation_redirect() {
        if ( intval( get_option( 'loginshield_activation_redirect', false ) ) === wp_get_current_user()->ID ) {
            delete_option( 'loginshield_activation_redirect' );
            wp_safe_redirect( admin_url( '/options-general.php?page=loginshield' ) );
            exit;
        }
    }
}
