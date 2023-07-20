<?php
/**
 * Plugin Name: Pantheon Symlinks
 * Version: 2.0
 * Plugin URI: http://wordpress.org/
 * Description: Easy symlinking tool in WP. Best used for non-command line users. This can only track symlinks created within the application and excludes symlinks created from the filesystem and command line. Best used in Pantheon dev environments in SFTP mode.
 * Author: Gilbert Caro
 * Author URI: https://pantheon.io/
 * Requires at least: 4.9
 * Tested up to: 6.0.3
 *
 * Text Domain: pantheon-symlinks
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Gilbert Caro
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
} //Exit if accessed directly

use PANTHEONSYMLINKS\Abstracts\Abstract_Main_Plugin_Class;
use PANTHEONSYMLINKS\Helpers\Plugin_Constants;
use PANTHEONSYMLINKS\Helpers\Helper_Functions;
use PANTHEONSYMLINKS\Interfaces\Model_Interface;
use PANTHEONSYMLINKS\Models\Bootstrap;
use PANTHEONSYMLINKS\Models\Settings_App;
use PANTHEONSYMLINKS\Models\Settings_Api;
use PANTHEONSYMLINKS\Models\Edit_Symlinks;
use PANTHEONSYMLINKS\Models\Script_Loader;

/**
 * Register plugin autoloader.
 *
 * @param $class_name string Name of the class load.
 * @since 2.0.0
 *
 */
spl_autoload_register(function ($class_name) {

    if (strpos($class_name, 'PANTHEONSYMLINKS\\') === 0) {
        $class_file = str_replace(['\\', 'PANTHEONSYMLINKS' . DIRECTORY_SEPARATOR], [DIRECTORY_SEPARATOR, ''], $class_name) . '.php';

        require_once plugin_dir_path(__FILE__) . $class_file;
    }
});

/**
 * The main plugin class.
 */
class PANTHEONSYMLINKS extends Abstract_Main_Plugin_Class
{
    /**-----------------------------------------------------------------------------------------------------------------
     * Class Properties
     * -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Single main instance of Plugin PANTHEONSYMLINKS Plugin.
     *
     * @since 2.0
     * @access private
     * @var PANTHEONSYMLINKS
     */
    private static $_instance;

    /**-----------------------------------------------------------------------------------------------------------------
     * Class Public Methods
     * -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Constructor
     *
     * @since 2.0
     * @access public
     */
    public function __construct()
    {
        $this->_initialize_helpers();

        // run me baby
        $this->_initialize_plugin_components();
        $this->_run_plugin();
    }

    /**
     * Ensure that only one instance of Advanced Coupons for WooCommerce is loaded or can be loaded (Singleton Pattern).
     *
     * @return PANTHEONSYMLINKS
     * @since 2.0
     * @access public
     *
     */
    public static function get_instance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**-----------------------------------------------------------------------------------------------------------------
     * Class Private Methods
     * -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Initialize helper class instances.
     *
     * @since 2.0.0
     * @access private
     */
    private function _initialize_helpers()
    {
        Plugin_Constants::get_instance($this);
        Helper_Functions::get_instance($this, $this->Plugin_Constants);
    }

    /**
     * Initialize plugin components
     *
     * @since 2.0.0
     * @access private
     */
    private function _initialize_plugin_components()
    {
        // modules
        $edit_symlinks = Edit_Symlinks::get_instance($this, $this->Plugin_Constants);
        $settings      = Settings_App::get_instance($this, $this->Plugin_Constants, $this->Helper_Functions);
        $settings_api  = Settings_Api::get_instance($this, $this->Plugin_Constants, $this->Helper_Functions);

        //Bootstraps args
        $initiables     = array($settings_api);
        $activatables   = array($edit_symlinks, $settings, $settings_api);
        $deactivatables = array();

        Bootstrap::get_instance($this, $this->Plugin_Constants, $this->Helper_Functions, $activatables, $initiables, $deactivatables);
        Script_Loader::get_instance($this, $this->Plugin_Constants);
    }

    /**
     * Run the plugin. ( Runs the various plugin components ).
     *
     * @since 2.0
     * @access private
     */
    private function _run_plugin()
    {
        foreach ($this->_all_models as $model) {
            if ($model instanceof Model_Interface) {
                $model->run();
            }
        }

    }
}

/**
 * Returns the main instance of PANTHEONSYMLINKS to prevent the need to use globals.
 *
 * @return PANTHEONSYMLINKS Main instance of the plugin.
 * @since 2.0.0
 */
function PANTHEONSYMLINKS()
{
    return PANTHEONSYMLINKS::get_instance();
}

//Lets go!!
$GLOBALS['PANTHEONSYMLINKS'] = PANTHEONSYMLINKS();
