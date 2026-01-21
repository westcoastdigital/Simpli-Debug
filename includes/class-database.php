<?php
/**
 * Database class for Simpli Debug activity logging
 */

if (!defined('ABSPATH')) {
    exit;
}

class Simpli_Debug_Database {
    
    const TABLE_NAME = 'simpli_activity_log';
    
    /**
     * Create the activity log table
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            log_type varchar(50) NOT NULL,
            object_type varchar(50) DEFAULT NULL,
            object_id varchar(255) DEFAULT NULL,
            object_title text DEFAULT NULL,
            post_type varchar(50) DEFAULT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            user_name varchar(255) DEFAULT NULL,
            action varchar(50) NOT NULL,
            old_value text DEFAULT NULL,
            new_value text DEFAULT NULL,
            additional_data longtext DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY log_type (log_type),
            KEY object_type (object_type),
            KEY post_type (post_type),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Update version
        update_option('simpli_debug_db_version', SIMPLI_DEBUG_VERSION);
    }
    
    /**
     * Get table name with prefix
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_NAME;
    }
    
    /**
     * Insert a log entry
     */
    public static function insert_log($data) {
        global $wpdb;
        
        $defaults = array(
            'log_type' => '',
            'object_type' => null,
            'object_id' => null,
            'object_title' => null,
            'post_type' => null,
            'user_id' => null,
            'user_name' => null,
            'action' => '',
            'old_value' => null,
            'new_value' => null,
            'additional_data' => null,
            'created_at' => current_time('mysql')
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Serialize additional data if it's an array
        if (is_array($data['additional_data'])) {
            $data['additional_data'] = maybe_serialize($data['additional_data']);
        }
        
        $result = $wpdb->insert(
            self::get_table_name(),
            $data,
            array(
                '%s', // log_type
                '%s', // object_type
                '%s', // object_id
                '%s', // object_title
                '%s', // post_type
                '%d', // user_id
                '%s', // user_name
                '%s', // action
                '%s', // old_value
                '%s', // new_value
                '%s', // additional_data
                '%s'  // created_at
            )
        );
        
        return $result !== false;
    }
    
    /**
     * Get log entries with filters
     */
    public static function get_logs($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'log_type' => '',
            'object_type' => '',
            'post_type' => '',
            'user_id' => '',
            'action' => '',
            'date_from' => '',
            'date_to' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 50,
            'offset' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = self::get_table_name();
        $where = array('1=1');
        $where_values = array();
        
        // Build WHERE clause
        if (!empty($args['log_type'])) {
            $where[] = 'log_type = %s';
            $where_values[] = $args['log_type'];
        }
        
        if (!empty($args['object_type'])) {
            $where[] = 'object_type = %s';
            $where_values[] = $args['object_type'];
        }
        
        if (!empty($args['post_type'])) {
            $where[] = 'post_type = %s';
            $where_values[] = $args['post_type'];
        }
        
        if (!empty($args['user_id'])) {
            $where[] = 'user_id = %d';
            $where_values[] = $args['user_id'];
        }
        
        if (!empty($args['action'])) {
            $where[] = 'action = %s';
            $where_values[] = $args['action'];
        }
        
        if (!empty($args['date_from'])) {
            $where[] = 'created_at >= %s';
            $where_values[] = $args['date_from'] . ' 00:00:00';
        }
        
        if (!empty($args['date_to'])) {
            $where[] = 'created_at <= %s';
            $where_values[] = $args['date_to'] . ' 23:59:59';
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Validate orderby
        $allowed_orderby = array('id', 'created_at', 'log_type', 'action', 'user_id');
        if (!in_array($args['orderby'], $allowed_orderby)) {
            $args['orderby'] = 'created_at';
        }
        
        // Validate order
        $args['order'] = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
        
        // Build query
        $query = "SELECT * FROM $table_name WHERE $where_clause ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d";
        $where_values[] = $args['limit'];
        $where_values[] = $args['offset'];
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get total count of logs with filters
     */
    public static function get_logs_count($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'log_type' => '',
            'object_type' => '',
            'post_type' => '',
            'user_id' => '',
            'action' => '',
            'date_from' => '',
            'date_to' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = self::get_table_name();
        $where = array('1=1');
        $where_values = array();
        
        // Build WHERE clause (same as get_logs)
        if (!empty($args['log_type'])) {
            $where[] = 'log_type = %s';
            $where_values[] = $args['log_type'];
        }
        
        if (!empty($args['object_type'])) {
            $where[] = 'object_type = %s';
            $where_values[] = $args['object_type'];
        }
        
        if (!empty($args['post_type'])) {
            $where[] = 'post_type = %s';
            $where_values[] = $args['post_type'];
        }
        
        if (!empty($args['user_id'])) {
            $where[] = 'user_id = %d';
            $where_values[] = $args['user_id'];
        }
        
        if (!empty($args['action'])) {
            $where[] = 'action = %s';
            $where_values[] = $args['action'];
        }
        
        if (!empty($args['date_from'])) {
            $where[] = 'created_at >= %s';
            $where_values[] = $args['date_from'] . ' 00:00:00';
        }
        
        if (!empty($args['date_to'])) {
            $where[] = 'created_at <= %s';
            $where_values[] = $args['date_to'] . ' 23:59:59';
        }
        
        $where_clause = implode(' AND ', $where);
        
        $query = "SELECT COUNT(*) FROM $table_name WHERE $where_clause";
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }
        
        return (int) $wpdb->get_var($query);
    }
    
    /**
     * Clear old logs (optional cleanup function)
     */
    public static function clear_old_logs($days = 90) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table_name WHERE created_at < %s",
                $date
            )
        );
    }

    /**
     * Drop the activity log table
     */
    public static function drop_table() {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        
        // Remove version option
        delete_option('simpli_debug_db_version');
    }

    /**
     * Truncate the activity log table (clear all entries)
     */
    public static function truncate_table() {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        return $wpdb->query("TRUNCATE TABLE $table_name");
    }

}