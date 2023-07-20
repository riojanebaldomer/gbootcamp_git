<?php

namespace PANTHEONSYMLINKS\Models;

use PANTHEONSYMLINKS\Abstracts\Abstract_Main_Plugin_Class;
use PANTHEONSYMLINKS\Interfaces\Model_Interface;
use PANTHEONSYMLINKS\Interfaces\Initializable_Interface;
use PANTHEONSYMLINKS\Helpers\Plugin_Constants;
use PANTHEONSYMLINKS\Helpers\Helper_Functions;

if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

/**
 * Model that houses the Settings_App module logic.
 * Public Model.
 *
 * @since 2.0
 */
class Settings_App implements Model_Interface, Initializable_Interface
{
    /**-----------------------------------------------------------------------------------------------------------------
     * Class Properties
     * -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Model that houses all the plugin constants.
     *
     * @since 2.o
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

    /**
     * Property that holds the single main instance.
     *
     * @since 2.0
     * @access private
     */
    private static $_instance;

    /**
     * Property that holds list of app pages.
     *
     * @since 2.0
     * @access private
     * @var string
     */
    private $_app_pages;

    /**-----------------------------------------------------------------------------------------------------------------
     * Class Public Methods
     * -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Class constructor.
     *
     * @param Abstract_Main_Plugin_Class $main_plugin Main plugin object.
     * @param Plugin_Constants $constants Plugin constants object.
     * @since 1.2
     * @access public
     *
     */
    public function __construct(Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions)
    {

        $this->_constants        = $constants;
        $this->_helper_functions = $helper_functions;

        $main_plugin->add_to_all_plugin_models($this);
        $main_plugin->add_to_public_models($this);

    }

    /**
     * Ensure that only one instance of this class is loaded or can be loaded ( Singleton Pattern ).
     *
     * @param Abstract_Main_Plugin_Class $main_plugin Main plugin object.
     * @param Plugin_Constants $constants Plugin constants object.
     * @since 1.2
     * @access public
     *
     */
    public static function get_instance(Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions)
    {

        if (!self::$_instance instanceof self) {
            self::$_instance = new self($main_plugin, $constants, $helper_functions);
        }

        return self::$_instance;

    }

    /**
     * Enqueue settings react app styles and scripts.
     *
     * @param WP_Screen $screen Current screen object.
     * @since 2.0
     * @access public
     *
     */
    public function register_react_scripts($screen)
    {
        // Important: Must enqueue this script in order to use WP REST API via JS
        wp_enqueue_script('wp-api');

        wp_localize_script('wp-api', 'pantheon_settings',
            array(
                'is_env_writable'                 => false,
                'environment_check'               => $this->_helper_functions->is_pantheon_environment_writable(),
                'env_warning'                     => __('This plugin cannot be used in Test and Live Read-only Environments in Pantheon', 'pantheon-symlinks'),
                'about_write_access'              => __('Know more about write access on environments.', 'pantheon-symlinks'),
                'admin_url'                       => admin_url(),
                'home_path'                       => $this->_helper_functions->get_wp_homepath(),
                'root_path'                       => is_dir(dirname($this->_helper_functions->get_wp_homepath()) . '/files'),
                'api_url'                         => home_url('/wp-json'),
                'nonce'                           => wp_create_nonce('wp_rest'),
                'title'                           => __('Symlink Settings', 'pantheon-symlinks'),
                'screen_id'                       => substr($screen->id, strpos($screen->id, '_') + 6),
                'divider_title_default_plugin'    => __('List of default plugins that needs symlinks', 'pantheon-symlinks'),
                'divider_title_create_symlink'    => __('Create Symlink', 'pantheon-symlinks'),
                'divider_title_untracked_symlink' => __('Untracked Symlinks', 'pantheon-symlinks'),
                'symlinks'                        => [
                    'protip_title'       => __('Protip', 'pantheon-symlinks'),
                    'symlink_doc'        => __('To know more about symlink and assume write access, click ', 'pantheon-symlinks'),
                    'link'               => 'https://docs.pantheon.io/symlinks-assumed-write-access',
                    'target_description' => __('This should be existing, non-version controlled and in a writable path by your host like the wp-content/uploads. This should be a relative path to where your link is created.', 'pantheon-symlinks'),
                    'link_description'   => __('This should be non-existing as this one will be created. If the folder is existing, contents should be moved to the target first before symlinking.', 'pantheon-symlinks'),
                ],
                'columns'                         => [
                    'header' => [
                        'info_target' => __("This should be existing, non-version controlled and in a writable path by your host like the wp-content/uploads. This should be a relative path to where your link is created. <br> ./uploads/cache if link is from /wp-content/cache <br> ./wp-content/uploads/rootfolder if link is from /rootfolder ", 'pantheon-symlinks'),
                        'info_link'   => __('This should be non-existing as this one will be created. If the folder is existing, contents should be moved to the target first before symlinking.', 'pantheon-symlinks'),
                    ],
                ],
                'form'                            => [
                    'input' => [
                        'target_required' => __('Target field is required.', 'pantheon-symlinks'),
                        'link_required'   => __('Link field is required.', 'pantheon-symlinks'),
                        'desc_required'   => __('Plugin name or description is required.', 'pantheon-symlinks'),
                    ],
                ],
                'untracked'                       => [
                    'add_link'       => __('Add', 'pantheon-symlinks'),
                    'untracked_info' => __('Untracked symlinks are symlinks that are created manually using CLI (Command Line Interface), these symlinks are not stored on database for tracking and management. To tracked the untracked symlinks, simply click "Add" button.', 'pantheon-symlinks'),
                ],
            ),
        );

        $app_js_path  = $this->_constants->JS_ROOT_PATH() . '/app/symlinks/build/static/js/';
        $app_css_path = $this->_constants->JS_ROOT_PATH() . '/app/symlinks/build/static/css/';
        $app_js_url   = $this->_constants->JS_ROOT_URL() . 'app/symlinks/build/static/js/';
        $app_css_url  = $this->_constants->JS_ROOT_URL() . 'app/symlinks/build/static/css/';

        if (\file_exists($app_js_path)) {
            if ($js_files = \scandir($app_js_path)) {
                foreach ($js_files as $key => $js_file) {
                    if (strpos($js_file, '.js') !== false && strpos($js_file, '.js.map') === false && strpos($js_file, '.js.LICENSE.txt') === false) {
                        $handle = Plugin_Constants::TOKEN . $key;
                        wp_enqueue_script($handle, $app_js_url . $js_file, array('wp-api'), Plugin_Constants::VERSION, true);
                    }
                }
            }
        }

        if (\file_exists($app_css_path)) {
            if ($css_files = \scandir($app_css_path)) {
                foreach ($css_files as $key => $css_file) {
                    if (strpos($css_file, '.css') !== false && strpos($css_file, '.css.map') === false) {
                        wp_enqueue_style(Plugin_Constants::TOKEN . $key, $app_css_url . $css_file, array(), Plugin_Constants::VERSION, 'all');
                    }
                }
            }
        }
    }

    public function remove_admin_notice($screen)
    {
        global $wp_filter;
        $current_page = substr($screen->id, strpos($screen->id, '_') + 6);

        // if($current_page === 'pantheon-admin-symlink-settings'){
        //     remove_action( 'admin_notices', 'display_admin_notice');
        // }


        if ($current_page === 'pantheon-admin-symlink-settings') {
            unset($wp_filter['user_admin_notices']);
            unset($wp_filter['admin_notices']);
            unset($wp_filter['all_admin_notices']);

        }

    }

    /**
     * Execute codes that needs to run plugin activation.
     *
     * @since 2.0
     * @access public
     * @implements \PANTHEONSYMLINKS\Interfaces\Initializable_Interface
     */
    public function initialize()
    {

    }

    /**
     * Execute Admin_App class.
     *
     * @since 2.0
     * @access public
     * @inherit PANTHEONSYMLINKS\Interfaces\Model_Interface
     */
    public function run()
    {
        add_action('pantheon_after_load_backend_scripts', array($this, 'register_react_scripts'), 10, 1);
        //add_action('pantheon_after_load_backend_scripts', array($this, 'remove_admin_notice'), 10, 1);
    }

    /**-----------------------------------------------------------------------------------------------------------------
     * Class Private Methods
     * -----------------------------------------------------------------------------------------------------------------*/
}