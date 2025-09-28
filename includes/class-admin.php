<?php
if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Bot_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_save_ai_bot_settings', array($this, 'save_settings'));
        add_action('wp_ajax_test_ai_bot_api', array($this, 'test_api'));
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
        return 'data:image/svg+xml;base64,' . base64_encode('<svg width="20" height="20" viewBox="0 0 24 24" fill="#9DA3AF"><path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7H20C19.4 7 19 6.6 19 6C19 5.4 19.4 5 20 5H21V3C21 1.9 20.1 1 19 1H5C3.9 1 3 1.9 3 3V5H4C4.6 5 5 5.4 5 6C5 6.6 4.6 7 4 7H3V9C3 10.1 3.9 11 5 11H8V13H7C6.4 13 6 13.4 6 14V20C6 20.6 6.4 21 7 21H17C17.6 21 18 20.6 18 20V14C18 13.4 17.6 13 17 13H16V11H19C20.1 11 21 10.1 21 9Z"/></svg>');
    }
    
    public function admin_page() {
        $settings = AI_Website_Bot_Settings::get_all_settings();
        include AI_WEBSITE_BOT_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    public function save_settings() {
        check_ajax_referer('ai_bot_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $settings = $_POST['settings'];
        
        // Sanitize settings
        $sanitized_settings = $this->sanitize_settings($settings);
        
        AI_Website_Bot_Settings::update_settings($sanitized_settings);
        
        wp_send_json_success('Settings saved successfully');
    }
    
    public function test_api() {
        check_ajax_referer('ai_bot_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $api_key = sanitize_text_field($_POST['api_key']);
        $model = sanitize_text_field($_POST['model']);
        
        if (empty($api_key) || empty($model)) {
            wp_send_json_error('API key and model are required');
        }
        
        // Test API call
        $response = wp_remote_post('https://openrouter.ai/api/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => home_url(),
                'X-Title' => get_bloginfo('name')
            ),
            'body' => json_encode(array(
                'model' => $model,
                'messages' => array(
                    array('role' => 'user', 'content' => 'Hello, this is a test message.')
                ),
                'max_tokens' => 10
            )),
            'timeout' => 15
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Connection failed: ' . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($response_code === 200 && isset($data['choices'][0])) {
            wp_send_json_success('API connection successful');
        } else {
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown error';
            wp_send_json_error('API Error: ' . $error_message);
        }
    }
    
    private function sanitize_settings($settings) {
        return array(
            'enable_chatbot' => (bool) ($settings['enable_chatbot'] ?? false),
            'bot_name' => sanitize_text_field($settings['bot_name'] ?? 'AI Assistant'),
            'website_name' => sanitize_text_field($settings['website_name'] ?? get_bloginfo('name')),
            'website_location' => sanitize_text_field($settings['website_location'] ?? ''),
            'website_type' => sanitize_text_field($settings['website_type'] ?? 'blog'),
            'content_types' => array_map('sanitize_text_field', $settings['content_types'] ?? array()),
            'openrouter_api_key' => sanitize_text_field($settings['openrouter_api_key'] ?? ''),
            'ai_model' => sanitize_text_field($settings['ai_model'] ?? 'mistralai/mistral-7b-instruct:free'),
            'bot_personality' => sanitize_textarea_field($settings['bot_personality'] ?? ''),
            'bot_knowledge' => sanitize_textarea_field($settings['bot_knowledge'] ?? ''),
            'response_style' => sanitize_text_field($settings['response_style'] ?? 'conversational'),
            'chatbot_position' => sanitize_text_field($settings['chatbot_position'] ?? 'bottom-right'),
            'primary_color' => sanitize_hex_color($settings['primary_color'] ?? '#2563eb'),
            'chat_icon' => sanitize_text_field($settings['chat_icon'] ?? 'chat'),
            'welcome_message' => sanitize_textarea_field($settings['welcome_message'] ?? 'Hi! How can I help you today?'),
            'quick_actions' => sanitize_textarea_field($settings['quick_actions'] ?? ''),
            'chat_window_size' => sanitize_text_field($settings['chat_window_size'] ?? 'medium'),
            'rate_limit' => intval($settings['rate_limit'] ?? 50),
            'response_timeout' => intval($settings['response_timeout'] ?? 30),
            'auto_suggestions' => (bool) ($settings['auto_suggestions'] ?? true),
            'search_integration' => (bool) ($settings['search_integration'] ?? true),
            'analytics_tracking' => (bool) ($settings['analytics_tracking'] ?? true),
            'fallback_response' => sanitize_text_field($settings['fallback_response'] ?? 'search'),
            'custom_css' => wp_strip_all_tags($settings['custom_css'] ?? ''),
            'blocked_words' => sanitize_textarea_field($settings['blocked_words'] ?? '')
        );
    }
}
?>