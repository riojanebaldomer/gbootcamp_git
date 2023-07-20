<?php

namespace PANTHEONSYMLINKS\Models;

use PANTHEONSYMLINKS\Abstracts\Abstract_Main_Plugin_Class;
use PANTHEONSYMLINKS\Helpers\Plugin_Constants;
use PANTHEONSYMLINKS\Helpers\Helper_Functions;
use PANTHEONSYMLINKS\Interfaces\Model_Interface;
use PANTHEONSYMLINKS\Interfaces\Deactivatable_Interface;
use PANTHEONSYMLINKS\Interfaces\Initializable_Interface;
use PANTHEONSYMLINKS\Interfaces\Activatable_Interface;

if (!defined('ABSPATH')) {
    exit;
} //Exit if accessed directly

class Bootstrap implements Model_Interface
{

    /**-----------------------------------------------------------------------------------------------------------------
     * Class Properties
     * -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Property that holds the single main instance of Bootstrap.
     *
     * @since 2.0
     * @access private
     * @var Bootstrap
     */
    private static $_instance;

    /**
     * Array of models implementing the PANTHEONSYMLINKS\Interfaces\Activatable_Interface.
     *
     * @since 2.0
     * @access private
     * @var array
     */
    private $_activatables;

    /**
     * Array of models implementing the PANTHEONSYMLINKS\Interfaces\Initializable_Interface.
     *
     * @since 2.0
     * @access private
     * @var array
     */
    private $_initializables;

    /**
     * Array of models implementing the PANTHEONSYMLINKS\Interfaces\Deactivatable_Interface.
     *
     * @since 2.0
     * @access private
     * @var array
     */
    private $_deactivatables;

    /**
     * Model that houses all the plugin constants.
     *
     * @since 2.0
     * @access private
     * @var Plugin_Constants
     */
    private $_constants;

    /**
     * Property that houses all the helper functions of the plugin.
     *
     * @since 2.0
     * @access private
     * @var Helper_Functions
     */
    private $_helper_functions;

    /**-----------------------------------------------------------------------------------------------------------------
     * Class Public Methods
     * -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Class constructor
     *
     * @since 2.0
     * @access public
     */
    public function __construct(Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions, array $activatables = array(), array $initializables = array(), array $deactivatables = array())
    {
        $this->_constants        = $constants;
        $this->_activatables     = $activatables;
        $this->_initializables   = $initializables;
        $this->_deactivatables   = $deactivatables;
        $this->_helper_functions = $helper_functions;

        $main_plugin->add_to_all_plugin_models($this);
    }

    /**
     * Ensure that only one instance of this class is loaded or can be loaded ( Singleton Pattern ).
     *
     * @since 2.0
     * @access public
     *
     */
    public static function get_instance(Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions, array $activatables = array(), array $initializables = array(), array $deactivatables = array())
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self($main_plugin, $constants, $helper_functions, $activatables, $initializables, $deactivatables);
        }

        return self::$_instance;
    }

    /**
     * Method that houses the logic relating to activating the plugin
     *
     * @param boolean $network_wide Flags that determines whether the plugin has been activated network id (onmultisite environment) or not.
     * @since 2.0.0
     * @access public
     *
     */
    public function activate_plugin($network_wide)
    {
        global $wpdb;

        if (is_multisite()) {

            if ($network_wide) {

                // get ids of all sites
                $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

                foreach ($blog_ids as $blog_id) {
                    switch_to_blog($blog_id);
                    $this->_activate_plugin($blog_id);
                }
                restore_current_blog();

            } else {
                $this->_activate_plugin($wpdb->blogid);
            }
        } else {
            $this->_activate_plugin($wpdb->blogid);
        }
    }

    /**
     * Method that houses the logic relating to deactivating the plugin.
     *
     * @param boolean $network_wide Flag that determines whether the plugin has been activated network wid ( on multi site environment ) or not.
     * @global wpdb $wpdb Object that contains a set of functions used to interact with a database.
     *
     * @since 2.0
     * @access public
     *
     */
    public function deactivate_plugin($network_wide)
    {
        global $wpdb;

        if (is_multisite()) {

            if ($network_wide) {

                // get ids of all sites
                $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

                foreach ($blog_ids as $blog_id) {
                    switch_to_blog($blog_id);
                    $this->_deactivate_plugin($blog_id);
                }
                restore_current_blog();

            } else {
                $this->_deactivate_plugin($wpdb->blogid);
            }
        } else {
            $this->_deactivate_plugin($wpdb->blogid);
        }
    }

    /**
     * Method to initialize a newly created site in a multi site set up
     *
     * @param int $blogid Blog ID of the created blog.
     * @param int $user_id User ID of the user creating the blog.
     * @param string $domain Domain used for the new blog.
     * @param string $path Path to the new blog.
     * @param int $site_id Site ID. Only relevant on multi-network installs.
     * @param array $meta Meta data. Used to set initial site options.
     * @since 2.0
     * @access public
     *
     */
    public function new_mu_site_init($blog_id, $user_id, $domain, $path, $site_id, $meta)
    {
        if (is_plugin_active_for_network('pantheon-symlinks/pantheon-symlinks.php')) {

            switch_to_blog($blog_id);
            $this->_activate_plugin($blog_id);
            restore_current_blog();
        }
    }

    /**
     * Method that houses codes to be executed on init hook.
     *
     * @since 2.0
     * @access public
     */
    public function initialize()
    {

        if (!function_exists('is_plugin_active_for_network')) {
            require_once ABSPATH . '/wp-admin/includes/plugin.php';
        }

        $network_wide = is_plugin_active_for_network('pantheon-symlinks/pantheon-symlinks.php');
        $this->activate_plugin($network_wide);

        // Execute 'initialize' contract of models implementing PANTHEONSYMLINKS\Interfaces\Initializable_Interface
        foreach ($this->_initializables as $initializable) {
            if ($initializable instanceof Initializable_Interface) {
                $initializable->initialize();
            }
        }

    }

    /**
     * Add settings link to plugin actions links.
     *
     * @param $links Plugin action links
     * @return array Filtered plugin action links
     * @since 2.0
     * @access public
     *
     */
    public function plugin_settings_action_link($links)
    {

        $href = admin_url('admin.php?page=pantheon-admin-symlink-settings');

        $settings_link = '<a href="' . $href . '">' . __('Settings', 'pantheon-symlinks') . '</a>';
        array_unshift($links, $settings_link);

        return $links;

    }

    /**
     * Execute plugin bootstrap codes.
     *
     * @inherit PANTHEONSYMLINKS\Interfaces\Model_Interfaces
     *
     * @since 2.0.0
     * @access public
     */
    public function run()
    {

        // Execute plugin activation/deactivation
        register_activation_hook($this->_constants->MAIN_PLUGIN_FILE_PATH(), array($this, 'activate_plugin'));
        register_deactivation_hook($this->_constants->MAIN_PLUGIN_FILE_PATH(), array($this, 'deactivate_plugin'));

        // Execute plugin initialization ( plugin activation ) on every newly created site in a multi site set up
        add_action('wpmu_new_blog', array($this, 'new_mu_site_init'), 10, 6);

        // Execute codes that need to run on 'init' hook
        add_action('init', array($this, 'initialize'));

        // Add settings link to plugin action links
        add_filter('plugin_action_links_' . $this->_constants->PLUGIN_BASENAME(), array($this, 'plugin_settings_action_link'), 10);
    }

    /**-----------------------------------------------------------------------------------------------------------------
     * Class Private Methods
     * -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Actual function that houses the code to execute on plugin activation
     *
     * @param int $blogid Blog ID of the created blog.
     * @since 2.0
     * @access private
     *
     */
    private function _activate_plugin($blogid)
    {
        // initialize settings options
        $this->_initialize_plugins_settings_options();

        // Execute 'activate' contract of models implementing PANTHEONSYMLINKS\Interfaces\Activatable_Interface
        foreach ($this->_activatables as $activatable) {
            if ($activatable instanceof Activatable_Interface) {
                $activatable->activate();
            }
        }

        // This is brute force rewriting of rules
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    /**
     * Actual function that houses the code to execute on plugin activation
     *
     * @param int $blogid Blog ID of the created blog.
     * @since 2.0
     * @access private
     *
     */
    private function _deactivate_plugin($blogid)
    {
        // Execute 'deactivate' contract of models implementing PANTHEONSYMLINKS\Interfaces\Deactivatable_Interface
        foreach ($this->_deactivatables as $deactivatable) {
            if ($deactivatable instanceof Deactivatable_Interface) {
                $deactivatable->deactivate();
            }
        }

        // This is brute force rewriting of rules
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    /**
     * This will create the plugins settings options if not yet created.
     *
     * @since 2.0
     * @access private
     */
    private function _initialize_plugins_settings_options()
    {
        if (!get_option(Plugin_Constants::OPTION_NAME)) {
            add_option(Plugin_Constants::OPTION_NAME);
        }

    }
}