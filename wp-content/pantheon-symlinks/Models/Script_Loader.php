<?php
namespace PANTHEONSYMLINKS\Models;

use PANTHEONSYMLINKS\Abstracts\Abstract_Main_Plugin_Class;
use PANTHEONSYMLINKS\Interfaces\Model_Interface;
use PANTHEONSYMLINKS\Helpers\Plugin_Constants;

if(!defined('ABSPATH')) {exit; } //Exit if accessed directly

/**
 * Model that houses the logic of loading plugin scripts.
 * Private Model
 * 
 * @since 2.0.0
 */
class Script_Loader implements Model_Interface
{
    /**-----------------------------------------------------------------------------------------------------------------
     * Class Properties
     -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Property that holds the single main instance of Bootstrap.
     *
     * @since 2.0
     * @access private
     * @var Bootstrap
     */
    private static $_instance;

    /**
     * Model that houses all the plugin constants.
     *
     * @since 2.0
     * @access private
     * @var Plugin_Constants
     */
    private $_constants;



    /**-----------------------------------------------------------------------------------------------------------------
     * Class Public Methods
     -----------------------------------------------------------------------------------------------------------------*/

     /**
     * Class constructor.
     *
     * @since 2.0
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     */
    public function __construct(Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants)
    {

        $this->_constants = $constants;
        $main_plugin->add_to_all_plugin_models($this);

    }    

    /**
     * Ensure that only one instance of this class is loaded or can be loaded ( Singleton Pattern ).
     *
     * @since 2.0
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @return Bootstrap
     */
    public static function get_instance(Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants)
    {

        if (!self::$_instance instanceof self) {
            self::$_instance = new self($main_plugin, $constants);
        }

        return self::$_instance;

    }

    /**
     * Load backend js and css scripts.
     *
     * @since 2.0
     * @access public
     *
     */
    public function load_backend_scripts()
    {
        //register all scripts required in the backend
        $this->_register_backend_scripts();

        $screen = get_current_screen();

        wp_enqueue_style('admin_styles');

        do_action('pantheon_after_load_backend_scripts', $screen);
    }   

    /**
     * Execute plugin script loader.
     *
     * @since 2.0
     * @access public
     * @inherit PANTHEONSYMLINKS\Interfaces\Model_Interface
     */
    public function run()
    {
        add_action('admin_enqueue_scripts', array($this, 'load_backend_scripts'), 10);
    }

    /**-----------------------------------------------------------------------------------------------------------------
     * Class Private Methods
     -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Register scripts to be used on the backend.
     *
     * @since 2.0
     * @access private
     */
     private function _register_backend_scripts()
     {
    
        $pantheon_backend_styles = apply_filters('pantheon_register_backend_styles', array(

            'admin_styles'=>[
                'src' => $this->_constants->CSS_ROOT_URL() . 'admin-styles.css',
                'deps' => [],
                'ver' => Plugin_Constants::VERSION,
                'media' => 'all',
            ]

        ));

        // register backend styles via a loop
        foreach($pantheon_backend_styles as $id => $style){
            wp_register_style($id, $style['src'], $style['deps'], $style['ver'], $style['media']);
        }
     }
}