<?php

namespace PANTHEONSYMLINKS\Helpers;

use PANTHEONSYMLINKS\Abstracts\Abstract_Main_Plugin_Class;
use PANTHEONSYMLINKS\Helpers\Plugin_Constants;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Model that houses all the helper functions of the plugin.
 *
 * 1.0.0
 */
class Helper_Functions
{

    /**-----------------------------------------------------------------------------------------------------------------
     * Class Properties
     * -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Property that holds the single main instance of Helper_Functions.
     *
     * @since 2.0
     * @access private
     * @var Helper_Functions
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
     * Public Class Methods
     * -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Class constructor.
     *
     * @param Plugin_Constants $constants Plugin constants object.
     * @since 2.0
     * @access public
     *
     */
    public function __construct(Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants)
    {
        $this->_constants = $constants;
        $main_plugin->add_to_public_helpers($this);

    }

    /**
     * Ensure that only one instance of this class is loaded or can be loaded ( Singleton Pattern ).
     *
     * @param Plugin_Constants $constants Plugin constants object.
     * @return Helper_Functions
     * @since 2.0
     * @access public
     *
     */
    public static function get_instance(Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants)
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self($main_plugin, $constants);
        }

        return self::$_instance;

    }

    /**-----------------------------------------------------------------------------------------------------------------
     * Helper Functions
     * -----------------------------------------------------------------------------------------------------------------*/

    /**
     * Utility function that determines if a plugin is active or not.
     *
     * @param string $plugin_basename Plugin base name. Ex. woocommerce/woocommerce.php
     * @return boolean True if active, false otherwise.
     * @since 2.0
     * @access public
     *
     */
    public function is_plugin_active($plugin_basename)
    {
        // Makes sure the plugin is defined before trying to use it
        if (!function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active($plugin_basename);
    }

    /**
     * Utility function that determines if a plugin is installed or not.
     *
     * @param string $plugin_basename Plugin base name. Ex. woocommerce/woocommerce.php
     * @return boolean True if active, false otherwise.
     * @since 2.0
     * @access public
     *
     */
    public function is_plugin_installed($plugin_basename)
    {
        $is_file_exists = false;

        if (!empty($plugin_basename)) {
            $plugin_file_path = trailingslashit(WP_PLUGIN_DIR) . plugin_basename($plugin_basename);
            $is_file_exists   = file_exists($plugin_file_path);
        }

        return $is_file_exists;
    }

    /**
     * Get the plugin image
     *
     * @param string $plugin_name Base name of the plugin
     * @since 2.0
     * @access public
     *
     */
    public function get_plugin_image($plugin_name)
    {
        $plugin_image_source = Plugin_Constants::PLUGIN_IMAGE_SOURCE;

        foreach ($plugin_image_source as $key => $image_source) {
            if ($key === $plugin_name) {
                return $image_source;
            }
        }

    }

    /**
     * This will get the plugins that is listed with Pantheon that has know issue with the platform, regarding to write access
     *
     * @return array
     */
    public function get_plugins_for_symlinked()
    {
        $filename      = $this->_constants->ASSET_ROOT_PATH() . 'default-plugin-data.json';
        $file_contents = file_get_contents($filename);
        $parsed        = json_decode($file_contents, true);
        $data          = [];

        foreach ($parsed as $plugins) {
            foreach ($plugins as $plugin) {
                $data['plugins'][] = [
                    'plugin_name'  => $plugin['name'],
                    'is_active'    => $this->is_plugin_active($plugin['details']),
                    'is_installed' => $this->is_plugin_installed($plugin['details']),
                    'img_src'      => $plugin['image'],
                ];
            }
        }

        return $data;

    }

    /**
     * Get home path of the root WP
     *
     * @since 1.0       from easy-symlink
     * @access public
     */
    public function get_wp_homepath()
    {
        if (!function_exists('get_home_path')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        return get_home_path();
    }

    public function get_files_dir($home_path)
    {

    }

    /**
     * Check if filesystem is writable
     *
     * @param string $path If not supplied, the default path would be WP Home path
     * @return boolean  Returns true if is writable, otherwise false if not.
     * @since 2.0
     * @access public
     *
     */
    public function is_filesystem_writable($path = '')
    {
        $path = empty($path) ? $this->get_wp_homepath() : $path;
        return is_writable($path);
    }

    /**
     * Check if filesystem is writable on Pantheon Environments (dev, test, live)
     *
     * @return array
     * @since 2.0
     * @access public
     *
     */
    public function is_pantheon_environment_writable()
    {
        $response = [];

        if (isset($_ENV['PANTHEON_ENVIRONMENT']) && in_array($_ENV['PANTHEON_ENVIRONMENT'], ['test', 'live'], true)) {
            $response['environment_check'] = [
                'error'  => __('Not In Writable Environment', 'pantheon-symlinks'),
                'status' => false,
            ];
        } else if (isset($_ENV['PANTHEON_ENVIRONMENT']) && $_ENV['PANTHEON_ENVIRONMENT'] === 'dev') {
            if ($this->is_filesystem_writable()) {
                $response['environment_check'] = [
                    'error'  => __('In Writable Environment', 'pantheon-symlinks'),
                    'status' => true,
                ];
            } else {
                $response['environment_check'] = [
                    'error'  => __('Root folder not writable. Please check if your environment is in Git mode or switch SFTP mode.', 'pantheon-symlinks'),
                    'status' => false,
                ];
            }
        } else {
            if ($this->is_filesystem_writable()) {
                $response['environment_check'] = [
                    'error'  => __('In Writable Environment', 'pantheon-symlinks'),
                    'status' => true,
                ];
            } else {
                $response['environment_check'] = [
                    'error'  => __('Root folder not writable. Please check your filesystem if it is writable.', 'pantheon-symlinks'),
                    'status' => false,
                ];
            }
        }

        return $response;
    }

    /**
     * Get symlinks
     * This function will retrieve all stored symlink from wp_options under option_name "pantheon_symlink"
     *
     * @return array
     * @since 2.0
     * @access public
     */
    public function get_symlinks()
    {
        $existing_value = [];

        if (get_option(Plugin_Constants::OPTION_NAME)) {
            $existing_value = get_option(Plugin_Constants::OPTION_NAME);
        }

        return $existing_value;
    }

    /**
     * Store symlink
     * This function will store/add symlink in wp_options under pantheon_symlink option name
     *
     * @param array $symlink This contains array of data for symlinks
     * @since 2.0
     * @access public
     *
     */
    public function store_symlink($symlink = [])
    {
        $found   = false;
        $success = false;

        //we need to get first the existing value
        $existing_values = $this->get_symlinks();

        // Un-serialize the retrieved data to get the original array
        $existing_values = maybe_unserialize($existing_values);

        //if not empty we proceed
        if (!empty($symlink)) {

            if (!empty($existing_values)) {

                // now we check if there is already an existing link
                $search_value = $symlink['link'];
                $found        = $this->search_in_multidimensional_array($search_value, $existing_values);

            }

            if (!$found) {

                // create a multi-dimensional array to store our symlinks
                $existing_values[] = $symlink;

                // we may need this to serialize before storing
                $serialize_data = maybe_serialize($existing_values);

                //update the option pantheon_symlink with the modified array
                $success = update_option(Plugin_Constants::OPTION_NAME, $serialize_data);

                //update symlink json file
                $this->update_symlink_file();
            }
        }

        return $success;
    }

    /**
     * This will update the current symlinks.
     * - If target or link have changes, its existing symlink will be deleted and a new one will be created.
     *
     * @param $symlink
     * @return bool
     */
    public function update_symlink($symlink = [])
    {
        $success = false;

        // get existing symlinks from wp_options
        $existing_symlinks = maybe_unserialize($this->get_symlinks());

        // search the index of the array to update
        $index = $this->find_array_index_by_key($existing_symlinks, 'id', $symlink['id']);

        // if found or has index
        if ($index !== false) {
            $position   = implode('.', $index);
            $new_link   = $this->get_wp_homepath() . $symlink['link'];
            $old_link   = '';
            $target     = $symlink['target'];
            $has_change = false;

            // check if changes is in description
            if ($existing_symlinks[$position]['description'] !== $symlink['description']) {
                $existing_symlinks[$position]['description'] = $symlink['description'];
            }

            // check if changes from target or link
            if ($existing_symlinks[$position]['target'] !== $symlink['target']) {
                $existing_symlinks[$position]['target'] = $symlink['target'];
                $has_changes_in_target                  = true;
            } elseif ($existing_symlinks[$position]['link'] !== $symlink['link']) {
                $old_link                             = $existing_symlinks[$position]['link'];
                $existing_symlinks[$position]['link'] = $symlink['link'];
                $has_changes_in_link                  = true;
            }

            // unlink symlink if has changes, then create a new one
            if ($has_changes_in_target) {
                if ($this->delete_symlink($symlink['link'])) {
                    $this->create_symlink($target, $new_link);
                }
            } elseif ($has_changes_in_link) {
                if ($this->delete_symlink($old_link)) {
                    $this->create_symlink($target, $new_link);
                }
            }

            //update db
            $serialize_data = maybe_serialize($existing_symlinks);
            $success        = update_option(Plugin_Constants::OPTION_NAME, $serialize_data);

            //update symlink file
            $this->update_symlink_file();

        }

        return $success;
    }

    /**
     * Search in multi-dimensional array
     *
     * @param string $needle Value to search in the array
     * @param array $haystack The multi-dimensional array to search
     *
     * @return boolean Will return true if found, otherwise false.
     *
     * @since 2.0
     * @access public
     *
     */
    public function search_in_multidimensional_array(string $needle, array $haystack): bool
    {
        foreach ($haystack as $item) {
            if (is_array($item)) {
                $result = $this->search_in_multidimensional_array($needle, $item);
                if ($result !== false) {
                    return $result;
                }
            } else {
                if ($item === $needle) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Search in multidimensional array index by key
     *
     * @param array $array The array which we want to perform search
     * @param string $search_key The KEY which we want to match the $search_value
     * @param mixed $search_value The value to match
     * @param array $indexes The array of indexes indicating the position of the matching value
     * @return array|false|int[]|string[]  If there is a matching value, we return the value, otherwise we return false
     *
     * @since 2.0
     * @access public
     */
    public function find_array_index_by_key($array, $search_key, $search_value, $indexes = array())
    {
        foreach ($array as $index => $value) {
            if (is_array($value)) {
                if (array_key_exists($search_key, $value) && $value[$search_key] == $search_value) {
                    return array_merge($indexes, array($index));
                }

                $result = $this->find_array_index_by_key($value, $search_key, $search_value, array_merge($indexes, array($index)));

                if ($result) {
                    return $result;
                }
            }
        }

        return false;
    }

    /**
     * Create symlink
     *
     * @param $target
     * @param $link
     * @return void
     *
     * @since 2.0
     * @access public
     */
    public function create_symlink($target, $link)
    {
        // create symlink
        $is_symlink_created = symlink($target, $link);

        //TODO: add additional checking here
        // 1. Check if already created
        // 2. Check if successfully added
        // 3. Fail error.

        // create folder
        $is_folder_created = $this->create_folder($target);
    }

    /**
     * Create folder
     *
     * @param string $target The folder to be created
     * @return boolean $status Returns true if folder is created, otherwise false.
     */
    public function create_folder(string $target)
    {
        $status    = false;
        $home_path = $this->get_wp_homepath();

        // Get the target folder name.
        if (preg_match('/\/uploads\/\W?\K.*/', $target, $matches)) {

            // Create target folder under uploads folder.
            $status = mkdir($home_path . '/wp-content/uploads/' . $matches[0], 0777, true);
        }

        return $status;
    }

    /**
     * Remove symlink
     *
     * @param $link
     * @return bool
     */
    public function delete_symlink($link)
    {
        $link = $this->get_wp_homepath() . $link;

        if (is_link($link)) {
            if (unlink($link)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update symlink json file based on the stored symlink in wp_options
     * - If update is success return true otherwise, false
     *
     * @return bool|int
     */
    public function update_symlink_file()
    {
        $filename = $this->_constants->ASSET_ROOT_PATH() . 'symlink-data.json';

        if ($this->filename_exists($filename)) {
            $file_content     = file_get_contents($filename);
            $data['symlinks'] = maybe_unserialize($this->get_symlinks());
            $updated_data     = json_encode($data, JSON_PRETTY_PRINT);

            return file_put_contents($filename, $updated_data);
        }

        return false;
    }

    /**
     * Checks if the file exists
     * - If the file exists return true
     * - If it does not exist create it and write the default content and return true
     *
     * @param $filename_with_path
     * @return bool|int
     */
    public function filename_exists($filename_with_path)
    {
        if (!file_exists($filename_with_path)) {
            $data['symlinks'] = [];
            $update_content   = json_encode($data, JSON_PRETTY_PRINT);

            return file_put_contents($filename_with_path, $update_content);
        }

        return true;
    }

    /**
     * Generate 4 int random number based on current time
     *
     * @return int
     * @throws \Exception
     */
    public function generate_random_id()
    {

        // Get the current time as a Unix timestamp
        $currentTimestamp = time();

        // Use the last 4 digits of the timestamp as the seed for the random number generator
        $seed = intval(substr($currentTimestamp, -4));

        // Generate a random 4-digit integer between 1000 and 9999
        $randomNumber = random_int(1000, 9999);

        // Combine the random number with the seed to get the final result
        $finalResult = $randomNumber + $seed;

        // Output the random 4-digit integer
        return $finalResult;

    }

    /**
     * Get symlink that is not yet stored on database
     * @return object
     * @throws \Exception
     */
    public function get_untracked_symlink()
    {
        $data      = [];
        $obj_array = new \stdClass();
        $field     = new \stdClass();
        $link      = $target = '';

        // get directory to read
        $target_directory = $this->get_wp_homepath() . '/wp-content';

        // get the list of files and directories inside the target directory
        $entries = scandir($target_directory);

        // get existing symlink
        $existing_symlink = maybe_unserialize($this->get_symlinks());

        // loop through each entry
        foreach ($entries as $entry) {
            // set to empty so that we avoid duplicate entry
            $link = $target = '';

            // skip the special entries '.' (current directory) abd '..' (parent directory)
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            // create the full path to the entry
            $full_path = $target_directory . DIRECTORY_SEPARATOR . $entry;

            // check if the entry is a symlink
            if (is_link($full_path)) {

                // read the target of the symlink
                $target = readlink($full_path);

                // get the link (symlink) itself
                $link = DIRECTORY_SEPARATOR . 'wp-content' . DIRECTORY_SEPARATOR . basename($full_path);

            }

            // skip empty target and link
            if (empty($target) || empty($link)) {
                continue;
            }

            if (isset($existing_symlink)) {
                $index = $this->find_array_index_by_key($existing_symlink, 'link', $link);
                if ($index === false) {

                    $field              = new \stdClass();
                    $field->id          = $this->generate_random_id();
                    $field->target      = $target;
                    $field->link        = $link;
                    $field->description = '';

                    // store symlink details in array
                    $data[] = $field;
                }
            }

        }

        $obj_array->untracked = $data;

        return $obj_array;
    }

}