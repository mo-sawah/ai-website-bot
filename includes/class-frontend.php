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
            'chat' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22H2V12C2 6.48 6.48 2 12 2ZM12 4C7.58 4 4 7.58 4 12V20H12C16.42 20 20 16.42 20 12C20 7.58 16.42 4 12 4ZM8 10C8.55 10 9 10.45 9 11C9 11.55 8.55 12 8 12C7.45 12 7 11.55 7 11C7 10.45 7.45 10 8 10ZM12 10C12.55 10 13 10.45 13 11C13 11.55 12.55 12 12 12C11.45 12 11 11.55 11 11C11 10.45 11.45 10 12 10ZM16 10C16.55 10 17 10.45 17 11C17 11.55 16.55 12 16 12C15.45 12 15 11.55 15 11C15 10.45 15.45 10 16 10Z"/>
            </svg>',
            
            'robot' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C12.55 2 13 2.45 13 3V4H16C17.1 4 18 4.9 18 6V8C19.66 8 21 9.34 21 11V16C21 17.66 19.66 19 18 19V20C18 21.1 17.1 22 16 22H8C6.9 22 6 21.1 6 20V19C4.34 19 3 17.66 3 16V11C3 9.34 4.34 8 6 8V6C6 4.9 6.9 4 8 4H11V3C11 2.45 11.45 2 12 2ZM8 6V18H16V6H8ZM10 9C10.55 9 11 9.45 11 10C11 10.55 10.55 11 10 11C9.45 11 9 10.55 9 10C9 9.45 9.45 9 10 9ZM14 9C14.55 9 15 9.45 15 10C15 10.55 14.55 11 14 11C13.45 11 13 10.55 13 10C13 9.45 13.45 9 14 9ZM9 13H15C15 14.66 13.66 16 12 16C10.34 16 9 14.66 9 13Z"/>
            </svg>',
            
            'help' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2ZM12 4C7.58 4 4 7.58 4 12C4 16.42 7.58 20 12 20C16.42 20 20 16.42 20 12C20 7.58 16.42 4 12 4ZM12 6C13.38 6 14.5 7.12 14.5 8.5C14.5 9.88 13.38 11 12 11C11.45 11 11 11.45 11 12V13C11 13.55 11.45 14 12 14C12.55 14 13 13.55 13 13V12.5C14.83 12.24 16.24 10.54 16.24 8.5C16.24 6.15 14.35 4.26 12 4.26C9.65 4.26 7.76 6.15 7.76 8.5H9.5C9.5 7.12 10.62 6 12 6ZM12 16C11.45 16 11 16.45 11 17C11 17.55 11.45 18 12 18C12.55 18 13 17.55 13 17C13 16.45 12.55 16 12 16Z"/>
            </svg>',
            
            'support' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L19 8L17 7V9C17 10.1 16.1 11 15 11V13L17 15V21H19V15L21 13V11C20.4 11 19.8 10.6 19.4 10.1L21 9ZM11 7H13V9C13 10.1 13.9 11 15 11H17V13H15C13.9 13 13 12.1 13 11V9H11V7ZM5 9V11C5 12.1 5.9 13 7 13H9V11H7C5.9 11 5 10.1 5 9ZM3 9L5 8L7 7V9C7 10.1 6.1 11 5 11V13L3 15V21H5V15L3 13V11C3.6 11 4.2 10.6 4.6 10.1L3 9Z"/>
            </svg>',
            
            'message' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H6L2 24V6C2 4.9 2.9 4 4 4ZM4 6V19.17L5.17 18H20V6H4ZM6 8H18V10H6V8ZM6 12H16V14H6V12Z"/>
            </svg>',
            
            'assistant' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2L13.09 8.26L22 9L13.09 9.74L12 16L10.91 9.74L2 9L10.91 8.26L12 2ZM4 14L5 17L8 18L5 19L4 22L3 19L0 18L3 17L4 14ZM19 14L20 17L23 18L20 19L19 22L18 19L15 18L18 17L19 14Z"/>
            </svg>'
        );
        
        return isset($icons[$icon_type]) ? $icons[$icon_type] : $icons['chat'];
    }
    
    // Add the missing color methods
    public function darken_color($color, $factor) {
        // Remove # if present
        $color = ltrim($color, '#');
        
        // Convert hex to RGB
        if (strlen($color) == 3) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }
        
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));
        
        // Darken
        $r = max(0, min(255, $r * (1 - $factor)));
        $g = max(0, min(255, $g * (1 - $factor)));
        $b = max(0, min(255, $b * (1 - $factor)));
        
        // Convert back to hex
        return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
    }
    
    public function lighten_color($color, $factor) {
        // Remove # if present
        $color = ltrim($color, '#');
        
        // Convert hex to RGB
        if (strlen($color) == 3) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }
        
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));
        
        // Lighten
        $r = max(0, min(255, $r + (255 - $r) * $factor));
        $g = max(0, min(255, $g + (255 - $g) * $factor));
        $b = max(0, min(255, $b + (255 - $b) * $factor));
        
        // Convert back to hex
        return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
    }
}
?>