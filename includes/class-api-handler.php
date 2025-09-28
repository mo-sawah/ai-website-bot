<?php
if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Bot_API_Handler {
    
    private $openrouter_endpoint = 'https://openrouter.ai/api/v1/chat/completions';
    
    public function process_chat_message($message) {
        $settings = AI_Website_Bot_Settings::get_all_settings();
        
        // Validate API key
        if (empty($settings['openrouter_api_key'])) {
            return array(
                'success' => false,
                'message' => 'API key not configured. Please contact administrator.'
            );
        }
        
        // Rate limiting check
        if (!$this->check_rate_limit()) {
            return array(
                'success' => false,
                'message' => 'Rate limit exceeded. Please try again later.'
            );
        }
        
        // Prepare system prompt
        $system_prompt = $this->build_system_prompt($settings);
        
        // Make API request
        $response = $this->make_openrouter_request($message, $system_prompt, $settings);
        
        if ($response['success']) {
            // Log interaction for analytics
            $this->log_interaction($message, $response['message']);
        }
        
        return $response;
    }
    
    private function build_system_prompt($settings) {
        $prompt = $settings['bot_personality'];
        
        $prompt .= "\n\nWebsite Information:";
        $prompt .= "\n- Website Name: " . $settings['website_name'];
        $prompt .= "\n- Website Type: " . $settings['website_type'];
        $prompt .= "\n- Location: " . $settings['website_location'];
        $prompt .= "\n- Available Content Types: " . implode(', ', $settings['content_types']);
        
        if (!empty($settings['bot_knowledge'])) {
            $prompt .= "\n\nAdditional Knowledge:\n" . $settings['bot_knowledge'];
        }
        
        $prompt .= "\n\nResponse Style: " . $settings['response_style'];
        $prompt .= "\n\nKeep responses helpful, concise, and relevant to the website context.";
        
        return $prompt;
    }
    
    private function make_openrouter_request($message, $system_prompt, $settings) {
        $headers = array(
            'Authorization' => 'Bearer ' . $settings['openrouter_api_key'],
            'Content-Type' => 'application/json',
            'HTTP-Referer' => home_url(),
            'X-Title' => get_bloginfo('name')
        );
        
        $body = array(
            'model' => $settings['ai_model'],
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $system_prompt
                ),
                array(
                    'role' => 'user',
                    'content' => $message
                )
            ),
            'temperature' => 0.7,
            'max_tokens' => 500,
            'stream' => false
        );
        
        $args = array(
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout' => $settings['response_timeout'],
            'method' => 'POST'
        );
        
        $response = wp_remote_request($this->openrouter_endpoint, $args);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'Connection error. Please try again.'
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            return array(
                'success' => true,
                'message' => $data['choices'][0]['message']['content']
            );
        }
        
        return array(
            'success' => false,
            'message' => 'Sorry, I couldn\'t process your request right now.'
        );
    }
    
    private function check_rate_limit() {
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $rate_limit = AI_Website_Bot_Settings::get_option('rate_limit', 50);
        
        $transient_key = 'ai_bot_rate_limit_' . md5($user_ip);
        $current_count = get_transient($transient_key);
        
        if ($current_count === false) {
            set_transient($transient_key, 1, HOUR_IN_SECONDS);
            return true;
        }
        
        if ($current_count >= $rate_limit) {
            return false;
        }
        
        set_transient($transient_key, $current_count + 1, HOUR_IN_SECONDS);
        return true;
    }
    
    private function log_interaction($user_message, $bot_response) {
        // Log for analytics if enabled
        if (AI_Website_Bot_Settings::get_option('analytics_tracking', true)) {
            $log_data = array(
                'timestamp' => current_time('mysql'),
                'user_message' => $user_message,
                'bot_response' => $bot_response,
                'user_ip' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            );
            
            // Store in option or database table
            $existing_logs = get_option('ai_bot_chat_logs', array());
            $existing_logs[] = $log_data;
            
            // Keep only last 1000 entries
            if (count($existing_logs) > 1000) {
                $existing_logs = array_slice($existing_logs, -1000);
            }
            
            update_option('ai_bot_chat_logs', $existing_logs);
        }
    }
}
?>