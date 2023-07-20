<?php

namespace PANTHEONSYMLINKS\Models;

use http\Client\Request;
use PANTHEONSYMLINKS\Abstracts\Abstract_Main_Plugin_Class;
use PANTHEONSYMLINKS\Interfaces\Initializable_Interface;
use PANTHEONSYMLINKS\Interfaces\Model_Interface;
use PANTHEONSYMLINKS\Helpers\Plugin_Constants;
use PANTHEONSYMLINKS\Helpers\Helper_Functions;

if (!defined('ABSPATH')) exit; //Exit if accessed directly

class Settings_Api implements Initializable_Interface, Model_Interface
{
    /**-----------------------------------------------------------------------------------------------------------------
     * Class Properties
     * -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Property that holds the single main instance of Settings_App.
     *
     * @since 2.0.0
     * @access private
     * @var Settings_App
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

    /**
     * Property that houses all the helper functions of the plugin.
     *
     * @since 2.0
     * @access private
     * @var Helper_Functions
     */
    private $_helper_functions;

    /**-----------------------------------------------------------------------------------------------------------------
     * Public Class Methods
     * -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Class constructor
     *
     * @param Abstract_Main_Plugin_Class $main_plugin Main plugin object.
     * @param Plugin_Constants $constants Plugin constant objects.
     * @since 2.0.0
     * @access public
     *
     */
    public function __construct(Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_Functions)
    {
        $this->_constants        = $constants;
        $this->_helper_functions = $helper_Functions;

        $main_plugin->add_to_all_plugin_models($this);
        $main_plugin->add_to_public_models($this);
    }

    /**
     * Ensure that only one instance of this class is loaded or can be loaded ( Singleton Pattern ).
     *
     * @param Abstract_Main_Plugin_Class $main_plugin Main plugin object.
     * @param Plugin_Constants $constants Plugin constants object.
     * @param Helper_Functions $helper_functions Helper functions object.
     * @return Edit_Symlinks
     * @since 2.0.0
     * @access public
     *
     */
    public static function get_instance(Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_Functions)
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self($main_plugin, $constants, $helper_Functions);
        }

        return self::$_instance;
    }

    /**
     * Create settings routes
     *
     * @since 2.0
     * @access public
     */
    public function create_rest_routes()
    {
        // this will get all the default plugins that are needed to create a symlink for pantheon platform
        register_rest_route(Plugin_Constants::API_ROUTE, '/plugins', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_plugins'],
            'permission_callback' => [$this, 'get_permissions']
        ]);

        // create symlink
        register_rest_route(Plugin_Constants::API_ROUTE, '/symlink', [
            'methods'             => 'POST',
            'callback'            => [$this, 'store_symlink'],
            'permission_callback' => [$this, 'get_permissions']
        ]);

        // get symlinks
        register_rest_route(Plugin_Constants::API_ROUTE, '/symlink', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_symlinks'],
            'permission_callback' => [$this, 'get_permissions']
        ]);

        // get untracked symlinks
        register_rest_route(Plugin_Constants::API_ROUTE, '/symlink/untracked', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_untrack_symlinks'],
            'permission_callback' => [$this, 'get_permissions']
        ]);

        // update symlinks
        register_rest_route(Plugin_Constants::API_ROUTE, '/symlink', [
            'methods'             => 'PUT',
            'callback'            => [$this, 'update_symlink'],
            'permission_callback' => [$this, 'get_permissions'],
        ]);

        register_rest_route(Plugin_Constants::API_ROUTE, '/symlink/tracked', [
            'methods'             => 'POST',
            'callback'            => [$this, 'tracked_symlink'],
            'permission_callback' => [$this, 'get_permissions'],
        ]);

        // delete symlinks
        register_rest_route(Plugin_Constants::API_ROUTE, '/symlink', [
            'methods'             => 'DELETE',
            'callback'            => [$this, 'delete_symlink'],
            'permission_callback' => [$this, 'get_permissions'],
        ]);
    }

    /**
     * Get installed and activated plugins that needs symlinks
     * wp-json/pantheon/v1/plugins
     *
     * @since 2.0
     * @access public
     */
    public function get_plugins()
    {
        return rest_ensure_response($this->_helper_functions->get_plugins_for_symlinked());
    }

    /**
     * Get untracked symlinks
     *
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     * @throws \Exception
     *
     * @since 2.0
     * @access public
     */
    public function get_untrack_symlinks()
    {
        return rest_ensure_response($this->_helper_functions->get_untracked_symlink());
    }

    /**
     * Store Symlink
     *
     * @param string $request This contains parameters
     * @since 2.0
     * @access public
     *
     */
    public function store_symlink($request)
    {

        $data      = json_decode($request['symlinks'], true);
        $home_path = $this->_helper_functions->get_wp_homepath();
        $target    = $data['target'];
        $link      = $home_path . $data['link'];
        $is_new    = $data['is_new'];
        $success   = false;

        if ($is_new) {
            $data = [
                'id'          => $data['id'],
                'target'      => $data['target'],
                'link'        => $data['link'],
                'description' => $data['description'],
            ];

            // save symlink to database
            $success = $this->_helper_functions->store_symlink($data);
            if ($success) {
                // create symlink
                $this->_helper_functions->create_symlink($target, $link);
            }
        } else {
            $success = $this->_helper_functions->update_symlink($data);
        }

        $response           = new \stdClass();
        $response->symlinks = [
            'id'         => $data['id'],
            'target'     => $data['target'],
            'link'       => $data['link'],
            'is_success' => $success,
        ];

        return rest_ensure_response($response);
    }

    public function update_symlink($request)
    {
        $data = json_decode($request['symlinks'], true);
    }

    public function tracked_symlink($request)
    {
        $data = json_decode($request['untracked'], true);
        $data = [
            'id'          => (string)$data['id'],
            'target'      => $data['target'],
            'link'        => $data['link'],
            'description' => $data['description'],
        ];

        $success           = $this->_helper_functions->store_symlink($data);
        $response          = new \stdClass();
        $response->tracked = [
            'id'         => $data['id'],
            'target'     => $data['target'],
            'link'       => $data['link'],
            'is_success' => $success,
        ];

    }

    public function delete_symlink($request)
    {
        $id           = $request['id'];
        $search_key   = 'id';
        $search_value = $id;

        // get array to search
        $array = maybe_unserialize($this->_helper_functions->get_symlinks());

        // search for index position
        $index = $this->_helper_functions->find_array_index_by_key($array, $search_key, $search_value);

        if ($index !== false) {
            $index_position = implode('.', $index);

            if (isset($array[$index_position])) {

                $link = $array[$index_position]['link'];
                if ($this->_helper_functions->delete_symlink($link)) {
                    // remove array
                    unset($array[$index_position]);

                    // serialize our updated array
                    $serialize_data = maybe_serialize($array);

                    // update
                    $success = update_option(Plugin_Constants::OPTION_NAME, $serialize_data);

                    if ($success) {
                        //update symlink json file
                        $this->_helper_functions->update_symlink_file();
                    }

                    // update
                    return $success;
                }

                return false;
            }
        }

        return rest_ensure_response($index);
    }

    /**
     * This will retrieve all the symlinks
     *
     * @since 2.0
     * @access public
     */
    public function get_symlinks()
    {
        // initialize variable
        $data      = [];
        $obj_array = new \stdClass();

        // get symlinks from pantheon_symlinks from wp_options
        $options = $this->_helper_functions->get_symlinks();
        if ($options) {
            // un-serialize array if it's serialize
            $unserialize_options = maybe_unserialize($options);

            foreach ($unserialize_options as $key => $options) {
                $field              = new \stdClass();
                $field->id          = $options['id'];
                $field->target      = $options['target'];
                $field->link        = $options['link'];
                $field->description = $options['description'];

                $data[] = $field;
            }

            $obj_array->symlinks = $data;
        }

        return rest_ensure_response($obj_array);
    }

    /**
     * Get permissions to call routes
     *
     * @since 2.0
     * @access public
     */
    public function get_permissions()
    {
        return current_user_can('manage_options');
    }

    /**
     * Execute codes that needs to run plugin activation.
     *
     * @since 2.0
     * @access public
     * @implements PANTHEONSYMLIONKS\Interfaces\Initializable_Interface
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
        add_action('rest_api_init', [$this, 'create_rest_routes']);
    }

    /**-----------------------------------------------------------------------------------------------------------------
     * Private Class Methods
     * -----------------------------------------------------------------------------------------------------------------*/
}