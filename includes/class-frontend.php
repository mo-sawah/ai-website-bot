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
            
            'robot' => '<svg width="24" height="24" viewBox="0 0 640 512" fill="currentColor"><path d="M352 64C352 46.3 337.7 32 320 32C302.3 32 288 46.3 288 64L288 128L192 128C139 128 96 171 96 224L96 448C96 501 139 544 192 544L448 544C501 544 544 501 544 448L544 224C544 171 501 128 448 128L352 128L352 64zM160 432C160 418.7 170.7 408 184 408L216 408C229.3 408 240 418.7 240 432C240 445.3 229.3 456 216 456L184 456C170.7 456 160 445.3 160 432zM280 432C280 418.7 290.7 408 304 408L336 408C349.3 408 360 418.7 360 432C360 445.3 349.3 456 336 456L304 456C290.7 456 280 445.3 280 432zM400 432C400 418.7 410.7 408 424 408L456 408C469.3 408 480 418.7 480 432C480 445.3 469.3 456 456 456L424 456C410.7 456 400 445.3 400 432zM224 240C250.5 240 272 261.5 272 288C272 314.5 250.5 336 224 336C197.5 336 176 314.5 176 288C176 261.5 197.5 240 224 240zM368 288C368 261.5 389.5 240 416 240C442.5 240 464 261.5 464 288C464 314.5 442.5 336 416 336C389.5 336 368 314.5 368 288z"/></svg>',
            
            'help' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,17A1.5,1.5 0 0,1 10.5,15.5A1.5,1.5 0 0,1 12,14A1.5,1.5 0 0,1 13.5,15.5A1.5,1.5 0 0,1 12,17M12,10.5C10.07,10.5 8.5,8.93 8.5,7C8.5,5.07 10.07,3.5 12,3.5C13.93,3.5 15.5,5.07 15.5,7C15.5,8.93 13.93,10.5 12,10.5M12,9A2,2 0 0,0 14,7A2,2 0 0,0 12,5A2,2 0 0,0 10,7A2,2 0 0,0 12,9Z"/></svg>',
            
            'support' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12,1C12,1 12,1 12,1C7.03,1 3,5.03 3,10V11.5C3,12.33 3.67,13 4.5,13H5V10C5,6.13 8.13,3 12,3C15.87,3 19,6.13 19,10V13H19.5C20.33,13 21,12.33 21,11.5V10C21,5.03 16.97,1 12,1M7.5,14C6.67,14 6,14.67 6,15.5V20.5C6,21.33 6.67,22 7.5,22H8.5C9.33,22 10,21.33 10,20.5V15.5C10,14.67 9.33,14 8.5,14H7.5M15.5,14C14.67,14 14,14.67 14,15.5V20.5C14,21.33 14.67,22 15.5,22H16.5C17.33,22 18,21.33 18,20.5V15.5C18,14.67 17.33,14 16.5,14H15.5Z"/></svg>',
            
            'message' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.11,4 20,4Z"/></svg>',
            
            'assistant' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12,2A7,7 0 0,1 19,9C19,11.38 17.81,13.47 16,14.74V17A1,1 0 0,1 15,18H9A1,1 0 0,1 8,17V14.74C6.19,13.47 5,11.38 5,9A7,7 0 0,1 12,2M9,21V20H15V21A1,1 0 0,1 14,22H10A1,1 0 0,1 9,21M12,4A5,5 0 0,0 7,9C7,11.05 8.23,12.81 10,13.58V16H14V13.58C15.77,12.81 17,11.05 17,9A5,5 0 0,0 12,4Z"/></svg>',
            
            'brain' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M21.33,12.91C21.42,14.46 20.71,15.95 19.44,16.86L20.21,18.35C20.44,18.8 20.47,19.33 20.27,19.8C20.08,20.27 19.69,20.64 19.21,20.8L18.42,21.05C18.25,21.11 18.06,21.14 17.88,21.14C17.37,21.14 16.89,20.91 16.56,20.5L14.44,18C13.55,17.85 12.8,17.7 12,17.7C11.2,17.7 10.45,17.85 9.56,18L7.44,20.5C7.11,20.91 6.63,21.14 6.12,21.14C5.94,21.14 5.75,21.11 5.58,21.05L4.79,20.8C4.31,20.64 3.92,20.27 3.73,19.8C3.53,19.33 3.56,18.8 3.79,18.35L4.56,16.86C3.29,15.95 2.58,14.46 2.67,12.91C2.75,11.35 3.63,9.96 5.06,9.18C5.1,9.15 5.15,9.12 5.2,9.1C5.15,8.8 5.12,8.5 5.12,8.18C5.12,5.26 7.5,2.83 10.5,2.83C12.94,2.83 15.04,4.77 15.5,7.31C16.83,7.86 17.83,9.26 17.88,10.83C19.31,11.61 20.19,13 20.27,14.56L21.33,12.91M17.5,9.5C17.5,8.67 16.83,8 16,8C15.17,8 14.5,8.67 14.5,9.5C14.5,10.33 15.17,11 16,11C16.83,11 17.5,10.33 17.5,9.5M12,15C13.66,15 15,13.66 15,12C15,10.34 13.66,9 12,9C10.34,9 9,10.34 9,12C9,13.66 10.34,15 12,15M9.5,9.5C9.5,8.67 8.83,8 8,8C7.17,8 6.5,8.67 6.5,9.5C6.5,10.33 7.17,11 8,11C8.83,11 9.5,10.33 9.5,9.5Z"/></svg>',
            
            'sparkles' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12,1L15.09,8.26L22,9L17,14.74L18.18,21.02L12,17.77L5.82,21.02L7,14.74L2,9L8.91,8.26L12,1M12,6.5L10.25,10.06L6.5,10.5L9.5,13.4L8.77,17.09L12,15.4L15.23,17.09L14.5,13.4L17.5,10.5L13.75,10.06L12,6.5Z"/></svg>'
        );
        
        return isset($icons[$icon_type]) ? $icons[$icon_type] : $icons['robot'];
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