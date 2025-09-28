<?php
/**
 * Plugin Name: AI Website Bot
 * Description: Intelligent AI chatbot for enhanced user engagement and content discovery
 * Version: 1.0.5
 * Author: Mohamed Sawah
 * Author URI: https://sawahsolutions.com
 * Text Domain: ai-website-bot
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AI_WEBSITE_BOT_VERSION', '1.0.5');
define('AI_WEBSITE_BOT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AI_WEBSITE_BOT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Main plugin class
class AI_Website_Bot {
    
    public function __construct() {
        $this->init();
    }
    
    public function init() {
        // Load plugin components
        $this->load_dependencies();
        
        // Initialize hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_footer', array($this, 'render_chatbot'));
        
        // AJAX handlers - both logged in and logged out users
        add_action('wp_ajax_ai_bot_chat', array($this, 'handle_chat_request'));
        add_action('wp_ajax_nopriv_ai_bot_chat', array($this, 'handle_chat_request'));
        add_action('wp_ajax_ai_bot_track_event', array($this, 'handle_track_event'));
        add_action('wp_ajax_nopriv_ai_bot_track_event', array($this, 'handle_track_event'));
        
        // Add shortcode
        add_shortcode('ai_website_bot', array($this, 'chatbot_shortcode'));
    }
    
    private function load_dependencies() {
        require_once AI_WEBSITE_BOT_PLUGIN_DIR . 'includes/class-admin.php';
        require_once AI_WEBSITE_BOT_PLUGIN_DIR . 'includes/class-frontend.php';
        require_once AI_WEBSITE_BOT_PLUGIN_DIR . 'includes/class-api-handler.php';
        require_once AI_WEBSITE_BOT_PLUGIN_DIR . 'includes/class-settings.php';
        
        // Initialize classes
        new AI_Website_Bot_Admin();
        new AI_Website_Bot_Frontend();
    }
    
    public function enqueue_frontend_scripts() {
        if (AI_Website_Bot_Settings::get_option('enable_chatbot', true)) {
            wp_enqueue_style('aiwb-frontend', AI_WEBSITE_BOT_PLUGIN_URL . 'assets/css/frontend.css', array(), AI_WEBSITE_BOT_VERSION);
            wp_enqueue_script('aiwb-frontend', AI_WEBSITE_BOT_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), AI_WEBSITE_BOT_VERSION, true);
            
            wp_localize_script('aiwb-frontend', 'aiBotAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ai_bot_nonce'),
                'settings' => AI_Website_Bot_Settings::get_frontend_settings()
            ));
        }
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'toplevel_page_ai-website-bot') {
            wp_enqueue_style('aiwb-admin', AI_WEBSITE_BOT_PLUGIN_URL . 'assets/css/admin.css', array(), AI_WEBSITE_BOT_VERSION);
            wp_enqueue_script('aiwb-admin', AI_WEBSITE_BOT_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), AI_WEBSITE_BOT_VERSION, true);
            
            wp_localize_script('aiwb-admin', 'aiBotAdmin', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ai_bot_admin_nonce')
            ));
        }
    }
    
    public function render_chatbot() {
        if (AI_Website_Bot_Settings::get_option('enable_chatbot', true)) {
            $frontend = new AI_Website_Bot_Frontend();
            $frontend->render_chatbot_html();
        }
    }
    
    public function handle_chat_request() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ai_bot_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        // Check if message is provided
        if (empty($_POST['message'])) {
            wp_send_json_error('No message provided');
            return;
        }
        
        try {
            $api_handler = new AI_Website_Bot_API_Handler();
            $response = $api_handler->process_chat_message(sanitize_text_field($_POST['message']));
            
            if ($response['success']) {
                wp_send_json_success(array('message' => $response['message']));
            } else {
                wp_send_json_error($response['message']);
            }
        } catch (Exception $e) {
            error_log('AI Website Bot Error: ' . $e->getMessage());
            wp_send_json_error('An error occurred processing your request');
        }
    }
    
    public function handle_track_event() {
        // Verify nonce (optional for tracking)
        if (!wp_verify_nonce($_POST['nonce'], 'ai_bot_nonce')) {
            wp_send_json_success(); // Don't fail tracking for nonce issues
            return;
        }
        
        // Log event if analytics is enabled
        if (AI_Website_Bot_Settings::get_option('analytics_tracking', true)) {
            $event = sanitize_text_field($_POST['event']);
            $data = isset($_POST['data']) ? $_POST['data'] : array();
            
            // Store event (you can extend this to save to database)
            $events = get_option('ai_bot_events', array());
            $events[] = array(
                'event' => $event,
                'data' => $data,
                'timestamp' => current_time('mysql'),
                'ip' => $_SERVER['REMOTE_ADDR']
            );
            
            // Keep only last 1000 events
            if (count($events) > 1000) {
                $events = array_slice($events, -1000);
            }
            
            update_option('ai_bot_events', $events);
        }
        
        wp_send_json_success();
    }
    
    public function chatbot_shortcode($atts) {
        $frontend = new AI_Website_Bot_Frontend();
        return $frontend->render_inline_chatbot($atts);
    }
    
    // Plugin activation
    public static function activate() {
        // Set default settings
        if (!get_option('ai_website_bot_settings')) {
            update_option('ai_website_bot_settings', AI_Website_Bot_Settings::get_default_settings());
        }
    }
    
    // Plugin deactivation
    public static function deactivate() {
        // Clean up if needed
    }
}

// Plugin activation/deactivation hooks
register_activation_hook(__FILE__, array('AI_Website_Bot', 'activate'));
register_deactivation_hook(__FILE__, array('AI_Website_Bot', 'deactivate'));

// Initialize plugin
new AI_Website_Bot();
?>