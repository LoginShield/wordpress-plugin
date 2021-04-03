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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */

        public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        
        add_action( 'admin_init', array( $this,'loginshield_include_files' ) );
		add_action( 'admin_init', array( $this,'loginshield_settings_register' ) );

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in LoginShield_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The LoginShield_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/loginshield-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in LoginShield_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The LoginShield_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/loginshield-admin.js', array( 'jquery' ), $this->version, false );
        wp_localize_script($this->plugin_name, 'loginshieldSettingAjax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'site_url' => get_site_url() ) );
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
            'LoginShield Setting',
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

    public function create_new_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . "login_shield";

        $login_shield_table = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL auto_increment,
            realm_id varchar(255) NOT NULL,
            authorization_token varchar(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($login_shield_table);
    }

    public function crf_show_extra_profile_fields($user) {
        $status = get_the_author_meta('login_shield_status', $user->ID);
        ?>
        <h2>Login Shield Management</h2>
		<table class="form-table">
            <tbody>
                <tr>
                    <th>
                        <label><?php esc_html_e('Activate/Deactivate Login Shield', 'crf');?></label>
                    </th>
                    <td>
                        <?php
                            if ($status == 1) {
                                ?>
                                <button type="button" class="button button-secondary"><?php esc_html_e('Deactivate LoginShield', 'crf');?></button> <?php
                            } else {
                                ?>
                                <button type="button" class="button button-primary"><?php esc_html_e('Activate LoginShield', 'crf');?></button> <?php
                            }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label><?php esc_html_e('Learn More', 'crf');?></label>
                    </th>
                    <td>
                        <a href="https://loginshield.com/article/one-tap-login/" target="_blank">https://loginshield.com/article/one-tap-login</a>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label><?php esc_html_e('Get the free app', 'crf');?></label>
                    </th>
                    <td>
                        <a href=" https://loginshield.com/software/" target="_blank">https://loginshield.com/software</a>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Register loginshield setting options.
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
}
