<?php
if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Bot_Settings {
    
    private static $option_name = 'ai_website_bot_settings';
    
    public static function get_default_settings() {
        return array(
            'enable_chatbot' => true,
            'bot_name' => 'AI Assistant',
            'website_name' => get_bloginfo('name'),
            'website_location' => '',
            'website_type' => 'blog',
            'content_types' => array('posts', 'pages'),
            'openrouter_api_key' => '',
            'theme_mode' => 'light', // Add this line
            'auto_theme' => false, // Add this line
            'ai_model' => 'mistralai/mistral-7b-instruct:free',
            'bot_personality' => 'You are a helpful AI assistant for this website. Be friendly, professional, and informative.',
            'bot_knowledge' => '',
            'response_style' => 'conversational',
            'chatbot_position' => 'bottom-right',
            'primary_color' => '#2563eb',
            'chat_icon' => 'chat',
            'welcome_message' => 'Hi! How can I help you today?',
            'quick_actions' => "Recent Posts\nPopular Content\nContact Info\nSearch Help",
            'chat_window_size' => 'medium',
            'rate_limit' => 50,
            'response_timeout' => 30,
            'auto_suggestions' => true,
            'search_integration' => true,
            'analytics_tracking' => true,
            'fallback_response' => 'search',
            'custom_css' => '',
            'blocked_words' => ''
        );
    }
    
    public static function get_option($key, $default = null) {
        $settings = get_option(self::$option_name, self::get_default_settings());
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
    
    public static function update_option($key, $value) {
        $settings = get_option(self::$option_name, self::get_default_settings());
        $settings[$key] = $value;
        update_option(self::$option_name, $settings);
    }
    
    public static function update_settings($new_settings) {
        $settings = get_option(self::$option_name, self::get_default_settings());
        $settings = array_merge($settings, $new_settings);
        update_option(self::$option_name, $settings);
    }
    
    public static function get_all_settings() {
        return get_option(self::$option_name, self::get_default_settings());
    }
    
    public static function get_frontend_settings() {
        $settings = self::get_all_settings();
        return array(
            'botName' => $settings['bot_name'],
            'websiteName' => $settings['website_name'],
            'primaryColor' => $settings['primary_color'],
            'position' => $settings['chatbot_position'],
            'welcomeMessage' => $settings['welcome_message'],
            'quickActions' => explode("\n", $settings['quick_actions']),
            'windowSize' => $settings['chat_window_size'],
            'chatIcon' => $settings['chat_icon'],
            'themeMode' => $settings['theme_mode'], // Add this line
            'autoTheme' => $settings['auto_theme'] // Add this line
        );
    }
}
?>