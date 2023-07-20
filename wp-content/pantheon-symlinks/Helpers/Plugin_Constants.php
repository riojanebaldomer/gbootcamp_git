<?php

namespace PANTHEONSYMLINKS\Helpers;

use PANTHEONSYMLINKS\Abstracts\Abstract_Main_Plugin_Class;

if (!defined('ABSPATH')) exit; //Exit if accessed directly

/**
 * Model that houses all the plugin constants.
 *
 * @since 2.0.0
 */
class Plugin_Constants
{
    /**-----------------------------------------------------------------------------------------------------------------
     * Class Properties
     * -----------------------------------------------------------------------------------------------------------------*/

    private static $_instance;

    protected string $_MAIN_PLUGIN_FILE_PATH;
    protected string $_PLUGIN_DIR_PATH;
    protected string $_PLUGIN_DIR_URL;
    protected string $_PLUGIN_DIRNAME;
    protected string $_PLUGIN_BASENAME;
    protected string $_CSS_ROOT_URL;
    protected string $_IMAGES_ROOT_URL;
    protected string $_JS_ROOT_URL;
    protected string $_JS_ROOT_PATH;
    protected string $_VIEWS_ROOT_PATH;
    protected string $_ASSETS_URL;
    protected string $_ASSETS_PATH;

    //Plugin configuration constants
    const VERSION             = '2.0.0';
    const TOKEN               = 'pantheon-symlinks';
    const OPTION_NAME         = 'pantheon_symlink';
    const API_ROUTE           = 'pantheon/v1';
    const PLUGINS_LIST        = [
        'wordfence'            => 'wordfence/wordfence.php',
        'webp-express'         => 'webp-express/webp-express.php',
        'webp-express-plus'    => 'webp-express-plus/index.php',
        'fast-velocity-minify' => 'fast-velocity-minify/fvm.php',
        'wp-merge'             => 'wp-merge/wp-merge.php',
    ];
    const PLUGIN_IMAGE_SOURCE = [
        'wordfence'            => 'https://ps.w.org/wordfence/assets/icon.svg?rev=2070865',
        'webp-express'         => 'https://ps.w.org/webp-express/assets/icon.svg?rev=1918288',
        'webp-express-plus'    => 'https://ps.w.org/webp-express-plus/assets/icon-256x256.jpg?rev=2536116',
        'fast-velocity-minify' => 'https://ps.w.org/fast-velocity-minify/assets/icon-128x128.jpg?rev=1440946',
        'wp-merge'             => 'https://ps.w.org/wp-migrate-db/assets/icon-128x128.png?rev=2851356',
    ];

    /**-----------------------------------------------------------------------------------------------------------------
     * Class Methods
     * -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Class constructor
     *
     * @since 2.0.0
     * @access public
     */
    public function __construct(Abstract_Main_Plugin_Class $main_plugin)
    {
        // Path Constants
        $this->_MAIN_PLUGIN_FILE_PATH = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'pantheon-symlinks' . DIRECTORY_SEPARATOR . 'pantheon-symlinks.php';
        $this->_PLUGIN_DIR_PATH       = plugin_dir_path($this->_MAIN_PLUGIN_FILE_PATH);
        $this->_PLUGIN_DIR_URL        = plugin_dir_url($this->_MAIN_PLUGIN_FILE_PATH);
        $this->_PLUGIN_DIRNAME        = plugin_basename(dirname($this->_MAIN_PLUGIN_FILE_PATH));
        $this->_PLUGIN_BASENAME       = plugin_basename($this->_MAIN_PLUGIN_FILE_PATH);

        $this->_CSS_ROOT_URL    = $this->_PLUGIN_DIR_URL . 'css/';
        $this->_IMAGES_ROOT_URL = $this->_PLUGIN_DIR_URL . 'images/';
        $this->_JS_ROOT_URL     = $this->_PLUGIN_DIR_URL . 'js/';
        $this->_ASSETS_URL      = $this->_PLUGIN_DIR_URL . 'assets/';

        $this->_JS_ROOT_PATH    = $this->_PLUGIN_DIR_PATH . 'js/';
        $this->_VIEWS_ROOT_PATH = $this->_PLUGIN_DIR_PATH . 'views/';
        $this->_ASSETS_PATH     = $this->_PLUGIN_DIR_PATH . 'assets/';

        $main_plugin->add_to_public_helpers($this);

    }

    public static function get_instance(Abstract_Main_Plugin_Class $main_plugin)
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self($main_plugin);
        }

        return self::$_instance;
    }

    public function MAIN_PLUGIN_FILE_PATH()
    {
        return $this->_MAIN_PLUGIN_FILE_PATH;
    }

    public function PLUGIN_DIR_PATH()
    {
        return $this->_PLUGIN_DIR_PATH;
    }

    public function PLUGIN_DIR_URL()
    {
        return $this->_PLUGIN_DIR_URL;
    }

    public function PLUGIN_DIRNAME()
    {
        return $this->_PLUGIN_DIRNAME;
    }

    public function PLUGIN_BASENAME()
    {
        return $this->_PLUGIN_BASENAME;
    }

    public function CSS_ROOT_URL()
    {
        return $this->_CSS_ROOT_URL;
    }

    public function IMAGES_ROOT_URL()
    {
        return $this->_IMAGES_ROOT_URL;
    }

    public function JS_ROOT_URL()
    {
        return $this->_JS_ROOT_URL;
    }

    public function ASSET_ROOT_URL()
    {
        return $this->_ASSETS_URL;
    }

    public function JS_ROOT_PATH()
    {
        return $this->_JS_ROOT_PATH;
    }

    public function VIEWS_ROOT_PATH()
    {
        return $this->_VIEWS_ROOT_PATH;
    }

    public function ASSET_ROOT_PATH()
    {
        return $this->_ASSETS_PATH;
    }
}