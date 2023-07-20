<?php
namespace PANTHEONSYMLINKS\Models;

use PANTHEONSYMLINKS\Abstracts\Abstract_Main_Plugin_Class;
use PANTHEONSYMLINKS\Helpers\Plugin_Constants;
use PANTHEONSYMLINKS\Interfaces\Activatable_Interface;
use PANTHEONSYMLINKS\Interfaces\Deactivatable_Interface;
use PANTHEONSYMLINKS\Interfaces\Initializable_Interface;
use PANTHEONSYMLINKS\Interfaces\Model_Interface;

if(!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Model that houses the logic of handling the interface of adding/editing the Pantheon Symllinks.
 * Public Model.
 * 
 * @since 2.0.0
 */
class Edit_Symlinks implements Model_Interface, Activatable_Interface
{
    /**-----------------------------------------------------------------------------------------------------------------
     * Class Properties
     -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Property that holds the single main instance of Edit_Symlinks.
     * 
     * @since 2.0.0
     * @access private
     * @var Edit_Symlinks
     */
    private static $_instance;

    /**
     * Model that houses all the plugin constants.
     *
     * @since 2.0.0
     * @access private
     * @var Plugin_Constants
     */
    private $_constants;

    /**-----------------------------------------------------------------------------------------------------------------
     * Class Public Methods
     -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Class constructor
     * 
     * @since 2.0.0
     * @access public
     * 
     * @param Abstract_Main_Plugin_Class    $main_plugin    Main plugin object.
     * @param Plugin_Constants              $constants      Plugin constant objects.
     */
    public function __construct(Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants)
    {
        $this->_constants = $constants;
        
        $main_plugin->add_to_all_plugin_models($this);
        $main_plugin->add_to_public_models($this);
    }

    /**
     * Ensure that only one instance of this class is loaded or can be loaded ( Singleton Pattern ).
     *
     * @since 2.0.0
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @return Edit_Symlinks
     */
    public static function get_instance(Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants)
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self($main_plugin, $constants);
        }

        return self::$_instance;
    }

    /**
     * Add "Pantheon" top level menu
     * 
     * @since 2.0.0
     * @access public
     * 
     * @global $submenu Global submenu list.
     */
    public function add_pantheon_admin_menus()
    {
        global $submenu;

        $toplevel_slug = 'pantheon-admin';
        $pantheon_icon = $this->_constants->IMAGES_ROOT_URL() . 'logo-pantheon-menu-icon.svg';

        add_menu_page(
            '',
            __('Pantheon', 'pantheon-symlinks'),
            'manage_options',
            $toplevel_slug,
            '',
            $pantheon_icon,
            '55.51'
        );

        add_submenu_page(
            $toplevel_slug,
            __('Symlink Settings','pantheon-symlinks'),
            __('Symlinks','pantheon-symlinks'),
            'manage_options',
            $toplevel_slug . '-symlink-settings',
            array($this, 'display_symlink_settings_page'),
            '',
        );

        // unset the first submenu entry created by add_menu_page.
        unset($submenu[$toplevel_slug][0]);

        do_action('pantheon_register_admin_submenus', $toplevel_slug);
    }

    /**
     * Display settings app.
     *
     * @since 2.0
     * @access public
     */
    public function display_symlink_settings_page()
    {
        echo '<div class="wrap">';
        echo '<hr class="wp-header-end">';
        echo '<div id="pantheon_admin_app"></div>';

        do_action('pantheon_admin_app');

        echo '</div>'; // end .wrap
    }

    public function activate()
    {

    }

    public function run()
    {
        add_action('admin_menu', array($this, 'add_pantheon_admin_menus'), 20);
    }

    /**-----------------------------------------------------------------------------------------------------------------
     * Class Private Methods
     -----------------------------------------------------------------------------------------------------------------*/
}