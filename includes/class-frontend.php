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
            'chat' => '<i class="fa-solid fa-comments"></i>',
            'robot' => '<i class="fa-solid fa-robot"></i>',
            'help' => '<i class="fa-solid fa-circle-question"></i>',
            'support' => '<i class="fa-solid fa-headset"></i>',
            'message' => '<i class="fa-solid fa-envelope"></i>',
            'assistant' => '<i class="fa-solid fa-wand-magic-sparkles"></i>',
            'brain' => '<i class="fa-solid fa-brain"></i>',
            'sparkles' => '<i class="fa-solid fa-sparkles"></i>'
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