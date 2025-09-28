<?php
if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Bot_Frontend {
    
    public function render_chatbot_html() {
        $settings = AI_Website_Bot_Settings::get_all_settings();
        
        if (!$settings['enable_chatbot']) {
            return;
        }
        
        include AI_WEBSITE_BOT_PLUGIN_DIR . 'templates/chatbot-widget.php';
    }
    
    public function render_inline_chatbot($atts) {
        $settings = AI_Website_Bot_Settings::get_all_settings();
        
        $atts = shortcode_atts(array(
            'height' => '500px',
            'width' => '100%'
        ), $atts);
        
        ob_start();
        include AI_WEBSITE_BOT_PLUGIN_DIR . 'templates/inline-chatbot.php';
        return ob_get_clean();
    }
    
    public function get_chat_icon_svg($icon_type) {
        $icons = array(
            'chat' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2ZM20 16H5.17L4 17.17V4H20V16Z"/></svg>',
            'robot' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7H20C19.4 7 19 6.6 19 6C19 5.4 19.4 5 20 5H21V3C21 1.9 20.1 1 19 1H5C3.9 1 3 1.9 3 3V5H4C4.6 5 5 5.4 5 6C5 6.6 4.6 7 4 7H3V9C3 10.1 3.9 11 5 11H8V13H7C6.4 13 6 13.4 6 14V20C6 20.6 6.4 21 7 21H17C17.6 21 18 20.6 18 20V14C18 13.4 17.6 13 17 13H16V11H19C20.1 11 21 10.1 21 9Z"/></svg>',
            'help' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 19H11V17H13V19ZM15.07 11.25L14.17 12.17C13.45 12.9 13 13.5 13 15H11V14.5C11 13.4 11.45 12.4 12.17 11.67L13.41 10.41C13.78 10.05 14 9.55 14 9C14 7.9 13.1 7 12 7C10.9 7 10 7.9 10 9H8C8 6.79 9.79 5 12 5C14.21 5 16 6.79 16 9C16 9.88 15.64 10.68 15.07 11.25Z"/></svg>',
            'support' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1C18.08 1 23 5.92 23 12C23 18.08 18.08 23 12 23C10.5 23 9.04 22.65 7.74 22L1 23L2.73 17.74C2.05 16.44 1.67 14.97 1.67 13.44C1.67 7.36 6.59 2.44 12.67 2.44H12C12 1 12 1 12 1ZM12 3C7.04 3 3 7.04 3 12C3 13.54 3.5 14.94 4.36 16.09L3.5 19.5L7.09 18.64C8.24 19.5 9.64 20 11.18 20H12C16.96 20 21 15.96 21 11C21 6.04 16.96 2 12 2V3Z"/></svg>'
        );
        
        return isset($icons[$icon_type]) ? $icons[$icon_type] : $icons['chat'];
    }
}
?>