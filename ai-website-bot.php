<?php
/**
 * Plugin Name: AI Website Bot
 * Description: Intelligent AI chatbot for enhanced user engagement and content discovery
 * Version: 1.0.3
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
define('AI_WEBSITE_BOT_VERSION', '1.0.3');
define('AI_WEBSITE_BOT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AI_WEBSITE_BOT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Main plugin class
class AI_Website_Bot {
    
    public function __init() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Load plugin components
        $this->load_dependencies();
        
        // Initialize hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_footer', array($this, 'render_chatbot'));
        add_action('wp_ajax_ai_bot_chat', array($this, 'handle_chat_request'));
        add_action('wp_ajax_nopriv_ai_bot_chat', array($this, 'handle_chat_request'));
        
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
        new AI_Website_Bot_API_Handler();
    }
    
    public function enqueue_frontend_scripts() {
        if (AI_Website_Bot_Settings::get_option('enable_chatbot', true)) {
            wp_enqueue_style('ai-bot-frontend', AI_WEBSITE_BOT_PLUGIN_URL . 'assets/css/frontend.css', array(), AI_WEBSITE_BOT_VERSION);
            wp_enqueue_script('ai-bot-frontend', AI_WEBSITE_BOT_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), AI_WEBSITE_BOT_VERSION, true);
            
            wp_localize_script('ai-bot-frontend', 'aiBotAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ai_bot_nonce'),
                'settings' => AI_Website_Bot_Settings::get_frontend_settings()
            ));
        }
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'toplevel_page_ai-website-bot') {
            wp_enqueue_style('ai-bot-admin', AI_WEBSITE_BOT_PLUGIN_URL . 'assets/css/admin.css', array(), AI_WEBSITE_BOT_VERSION);
            wp_enqueue_script('ai-bot-admin', AI_WEBSITE_BOT_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), AI_WEBSITE_BOT_VERSION, true);
        }
    }
    
    public function render_chatbot() {
        if (AI_Website_Bot_Settings::get_option('enable_chatbot', true)) {
            $frontend = new AI_Website_Bot_Frontend();
            $frontend->render_chatbot_html();
        }
    }
    
    public function handle_chat_request() {
        check_ajax_referer('ai_bot_nonce', 'nonce');
        
        $api_handler = new AI_Website_Bot_API_Handler();
        $response = $api_handler->process_chat_message($_POST['message']);
        
        wp_send_json($response);
    }
    
    public function chatbot_shortcode($atts) {
        $frontend = new AI_Website_Bot_Frontend();
        return $frontend->render_inline_chatbot($atts);
    }
}

// Plugin activation/deactivation hooks
register_activation_hook(__FILE__, array('AI_Website_Bot', 'activate'));
register_deactivation_hook(__FILE__, array('AI_Website_Bot', 'deactivate'));

// Initialize plugin
$ai_website_bot = new AI_Website_Bot();
$ai_website_bot->__init();
?>