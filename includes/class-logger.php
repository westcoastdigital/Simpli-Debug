<?php
/**
 * Logger class for Simpli Debug
 */

if (!defined('ABSPATH')) {
    exit;
}

class Simpli_Debug_Logger {
    
    /**
     * Initialize hooks
     */
    public static function init() {
        // Plugin/Theme update hooks
        add_action('upgrader_process_complete', array(__CLASS__, 'log_updates'), 10, 2);
        
        // Post/Page/CPT hooks
        add_action('post_updated', array(__CLASS__, 'log_post_update'), 10, 3);
        add_action('before_delete_post', array(__CLASS__, 'log_post_delete'), 10, 2);
        add_action('wp_trash_post', array(__CLASS__, 'log_post_trash'), 10, 1);
        add_action('untrash_post', array(__CLASS__, 'log_post_untrash'), 10, 1);
        
        // Plugin activation/deactivation
        add_action('activated_plugin', array(__CLASS__, 'log_plugin_activation'), 10, 2);
        add_action('deactivated_plugin', array(__CLASS__, 'log_plugin_deactivation'), 10, 2);
    }
    
    /**
     * Get current user info
     */
    private static function get_user_info() {
        $user_id = get_current_user_id();
        
        if ($user_id) {
            $user = get_userdata($user_id);
            $user_name = trim($user->first_name . ' ' . $user->last_name);
            
            if (empty($user_name)) {
                $user_name = $user->user_login;
            }
            
            return array(
                'user_id' => $user_id,
                'user_name' => $user_name
            );
        }
        
        // Auto-update or cron
        return array(
            'user_id' => 0,
            'user_name' => 'Auto Update'
        );
    }
    
    /**
     * Log plugin and theme updates
     */
    public static function log_updates($upgrader_object, $options) {
        $user_info = self::get_user_info();
        
        // Plugin updates
        if ($options['type'] === 'plugin') {
            $plugins = isset($options['plugins']) ? $options['plugins'] : array();
            
            foreach ($plugins as $plugin) {
                $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin, false, false);
                
                Simpli_Debug_Database::insert_log(array(
                    'log_type' => 'plugin',
                    'object_type' => 'plugin',
                    'object_id' => $plugin,
                    'object_title' => $plugin_data['Name'],
                    'action' => $options['action'], // 'update' or 'install'
                    'new_value' => $plugin_data['Version'],
                    'user_id' => $user_info['user_id'],
                    'user_name' => $user_info['user_name'],
                    'additional_data' => array(
                        'plugin_uri' => $plugin_data['PluginURI'],
                        'author' => $plugin_data['Author']
                    )
                ));
            }
        }
        
        // Theme updates
        if ($options['type'] === 'theme') {
            $themes = isset($options['themes']) ? $options['themes'] : array();
            
            foreach ($themes as $theme_slug) {
                $theme = wp_get_theme($theme_slug);
                
                Simpli_Debug_Database::insert_log(array(
                    'log_type' => 'theme',
                    'object_type' => 'theme',
                    'object_id' => $theme_slug,
                    'object_title' => $theme->get('Name'),
                    'action' => $options['action'], // 'update' or 'install'
                    'new_value' => $theme->get('Version'),
                    'user_id' => $user_info['user_id'],
                    'user_name' => $user_info['user_name'],
                    'additional_data' => array(
                        'theme_uri' => $theme->get('ThemeURI'),
                        'author' => $theme->get('Author')
                    )
                ));
            }
        }
    }
    
    /**
     * Log post updates
     */
    public static function log_post_update($post_id, $post_after, $post_before) {
        // Skip auto-saves and revisions
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        // Skip if nothing actually changed
        if ($post_after->post_modified === $post_before->post_modified) {
            return;
        }
        
        // Skip if being trashed - we have a dedicated hook for that
        if ($post_after->post_status === 'trash' && $post_before->post_status !== 'trash') {
            return;
        }
        
        // Skip if being restored from trash - we have a dedicated hook for that
        if ($post_before->post_status === 'trash' && $post_after->post_status !== 'trash') {
            return;
        }
        
        $user_info = self::get_user_info();
        
        // Determine what changed
        $changes = array();
        
        if ($post_after->post_title !== $post_before->post_title) {
            $changes['title'] = array(
                'old' => $post_before->post_title,
                'new' => $post_after->post_title
            );
        }
        
        if ($post_after->post_status !== $post_before->post_status) {
            $changes['status'] = array(
                'old' => $post_before->post_status,
                'new' => $post_after->post_status
            );
        }
        
        if ($post_after->post_content !== $post_before->post_content) {
            $changes['content'] = 'modified';
        }
        
        Simpli_Debug_Database::insert_log(array(
            'log_type' => 'post',
            'object_type' => 'post',
            'object_id' => $post_id,
            'object_title' => $post_after->post_title,
            'post_type' => $post_after->post_type,
            'action' => 'update',
            'old_value' => $post_before->post_status,
            'new_value' => $post_after->post_status,
            'user_id' => $user_info['user_id'],
            'user_name' => $user_info['user_name'],
            'additional_data' => $changes
        ));
    }
    
    /**
     * Log post deletion
     */
    public static function log_post_delete($post_id, $post) {
        // Skip revisions
        if (wp_is_post_revision($post_id)) {
            return;
        }
        
        $user_info = self::get_user_info();
        
        Simpli_Debug_Database::insert_log(array(
            'log_type' => 'post',
            'object_type' => 'post',
            'object_id' => $post_id,
            'object_title' => $post->post_title,
            'post_type' => $post->post_type,
            'action' => 'delete',
            'old_value' => $post->post_status,
            'user_id' => $user_info['user_id'],
            'user_name' => $user_info['user_name'],
            'additional_data' => array(
                'post_date' => $post->post_date,
                'post_author' => $post->post_author
            )
        ));
    }
    
    /**
     * Log post trash
     */
    public static function log_post_trash($post_id) {
        $post = get_post($post_id);
        
        if (!$post || wp_is_post_revision($post_id)) {
            return;
        }
        
        $user_info = self::get_user_info();
        
        Simpli_Debug_Database::insert_log(array(
            'log_type' => 'post',
            'object_type' => 'post',
            'object_id' => $post_id,
            'object_title' => $post->post_title,
            'post_type' => $post->post_type,
            'action' => 'trash',
            'old_value' => $post->post_status,
            'new_value' => 'trash',
            'user_id' => $user_info['user_id'],
            'user_name' => $user_info['user_name']
        ));
    }
    
    /**
     * Log post untrash
     */
    public static function log_post_untrash($post_id) {
        $post = get_post($post_id);
        
        if (!$post || wp_is_post_revision($post_id)) {
            return;
        }
        
        $user_info = self::get_user_info();
        
        Simpli_Debug_Database::insert_log(array(
            'log_type' => 'post',
            'object_type' => 'post',
            'object_id' => $post_id,
            'object_title' => $post->post_title,
            'post_type' => $post->post_type,
            'action' => 'restore',
            'old_value' => 'trash',
            'new_value' => $post->post_status,
            'user_id' => $user_info['user_id'],
            'user_name' => $user_info['user_name']
        ));
    }
    
    /**
     * Log plugin activation
     */
    public static function log_plugin_activation($plugin, $network_wide) {
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin, false, false);
        $user_info = self::get_user_info();
        
        Simpli_Debug_Database::insert_log(array(
            'log_type' => 'plugin',
            'object_type' => 'plugin',
            'object_id' => $plugin,
            'object_title' => $plugin_data['Name'],
            'action' => 'activate',
            'new_value' => $plugin_data['Version'],
            'user_id' => $user_info['user_id'],
            'user_name' => $user_info['user_name'],
            'additional_data' => array(
                'network_wide' => $network_wide
            )
        ));
    }
    
    /**
     * Log plugin deactivation
     */
    public static function log_plugin_deactivation($plugin, $network_wide) {
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin, false, false);
        $user_info = self::get_user_info();
        
        Simpli_Debug_Database::insert_log(array(
            'log_type' => 'plugin',
            'object_type' => 'plugin',
            'object_id' => $plugin,
            'object_title' => $plugin_data['Name'],
            'action' => 'deactivate',
            'old_value' => $plugin_data['Version'],
            'user_id' => $user_info['user_id'],
            'user_name' => $user_info['user_name'],
            'additional_data' => array(
                'network_wide' => $network_wide
            )
        ));
    }
}