<?php
if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Bot_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_save_ai_bot_settings', array($this, 'save_settings'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'AI Website Bot',
            'AI Website Bot',
            'manage_options',
            'ai-website-bot',
            array($this, 'admin_page'),
            $this->get_menu_icon(),
            30
        );
    }
    
    private function get_menu_icon() {
        return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzlEQTNBRiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTIwIDJIMTYuNzJMMTUuMjEgMC41OUMxNC44NSAwLjIxIDE0LjM5IDAgMTMuODMgMEgxMC4xN0M5LjYxIDAgOS4xNSAwLjIxIDguNzkgMC41OUw3LjI4IDJINEMyLjkgMiAyIDIuOSAyIDRWMTZDMiAxNy4xIDIuOSAxOCA0IDE4SDE2LjA5TDE5LjY2IDIxLjY2QzIwLjA2IDIyLjA1IDIwLjY2IDIyLjA1IDIxLjA2IDIxLjY2QzIxLjQ2IDIxLjI2IDIxLjQ2IDIwLjY2IDIxLjA2IDIwLjI2TDIwIDJaTTEzLjU1IDEyLjJMMTIuNDUgMTEuMUwxMS4zNSAxMi4ySDEwTDExLjU1IDEwLjY1TDEwLjkgMTBIMTBMMTEuNTUgMTEuNTVMMTAgMTNWMTJIMTEuMzVMMTIuNDUgMTIuOUwxMy41NSAxMi4yWiIvPgo8L3N2Zz4K';
    }
    
    public function admin_page() {
        $settings = AI_Website_Bot_Settings::get_all_settings();
        include AI_WEBSITE_BOT_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    public function save_settings() {
        check_ajax_referer('ai_bot_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $settings = $_POST['settings'];
        
        // Sanitize settings
        $sanitized_settings = $this->sanitize_settings($settings);
        
        AI_Website_Bot_Settings::update_settings($sanitized_settings);
        
        wp_send_json_success('Settings saved successfully');
    }
    
    private function sanitize_settings($settings) {
        return array(
            'enable_chatbot' => (bool) $settings['enable_chatbot'],
            'bot_name' => sanitize_text_field($settings['bot_name']),
            'website_name' => sanitize_text_field($settings['website_name']),
            'website_location' => sanitize_text_field($settings['website_location']),
            'website_type' => sanitize_text_field($settings['website_type']),
            'content_types' => array_map('sanitize_text_field', $settings['content_types']),
            'openrouter_api_key' => sanitize_text_field($settings['openrouter_api_key']),
            'ai_model' => sanitize_text_field($settings['ai_model']),
            'bot_personality' => sanitize_textarea_field($settings['bot_personality']),
            'bot_knowledge' => sanitize_textarea_field($settings['bot_knowledge']),
            'response_style' => sanitize_text_field($settings['response_style']),
            'chatbot_position' => sanitize_text_field($settings['chatbot_position']),
            'primary_color' => sanitize_hex_color($settings['primary_color']),
            'chat_icon' => sanitize_text_field($settings['chat_icon']),
            'welcome_message' => sanitize_textarea_field($settings['welcome_message']),
            'quick_actions' => sanitize_textarea_field($settings['quick_actions']),
            'chat_window_size' => sanitize_text_field($settings['chat_window_size']),
            'rate_limit' => intval($settings['rate_limit']),
            'response_timeout' => intval($settings['response_timeout']),
            'auto_suggestions' => (bool) $settings['auto_suggestions'],
            'search_integration' => (bool) $settings['search_integration'],
            'analytics_tracking' => (bool) $settings['analytics_tracking'],
            'fallback_response' => sanitize_text_field($settings['fallback_response']),
            'custom_css' => wp_strip_all_tags($settings['custom_css']),
            'blocked_words' => sanitize_textarea_field($settings['blocked_words'])
        );
    }
}
?>