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
            error_log('AI Website Bot: API key not configured');
            return array(
                'success' => false,
                'message' => 'Chatbot is not properly configured. Please contact the administrator.'
            );
        }
        
        // Rate limiting check
        if (!$this->check_rate_limit()) {
            error_log('AI Website Bot: Rate limit exceeded for IP: ' . $_SERVER['REMOTE_ADDR']);
            return array(
                'success' => false,
                'message' => 'Too many requests. Please wait a moment and try again.'
            );
        }
        
        // Handle special commands
        $special_response = $this->handle_special_commands($message, $settings);
        if ($special_response) {
            return array(
                'success' => true,
                'message' => $special_response
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
    
    private function handle_special_commands($message, $settings) {
        $message_lower = strtolower(trim($message));
        
        // Handle quick actions
        switch ($message_lower) {
            case 'recent posts':
                return $this->get_recent_posts();
            
            case 'popular content':
                return $this->get_popular_content();
            
            case 'contact info':
                return $this->get_contact_info($settings);
            
            case 'search help':
                return $this->get_search_help();
            
            default:
                return null;
        }
    }
    
    private function get_recent_posts() {
        $recent_posts = wp_get_recent_posts(array(
            'numberposts' => 5,
            'post_status' => 'publish'
        ));
        
        if (empty($recent_posts)) {
            return 'No recent posts found.';
        }
        
        $response = "Here are our latest posts:\n\n";
        foreach ($recent_posts as $post) {
            $response .= "• " . $post['post_title'] . "\n";
            $response .= "  " . get_permalink($post['ID']) . "\n\n";
        }
        
        return $response;
    }
    
    private function get_popular_content() {
        // Get posts with most comments as "popular"
        $popular_posts = get_posts(array(
            'numberposts' => 5,
            'orderby' => 'comment_count',
            'order' => 'DESC',
            'post_status' => 'publish'
        ));
        
        if (empty($popular_posts)) {
            return 'No popular content found.';
        }
        
        $response = "Here's our most popular content:\n\n";
        foreach ($popular_posts as $post) {
            $response .= "• " . $post->post_title . "\n";
            $response .= "  " . get_permalink($post->ID) . "\n\n";
        }
        
        return $response;
    }
    
    private function get_contact_info($settings) {
        $contact_info = "You can reach us through:\n\n";
        $contact_info .= "Website: " . home_url() . "\n";
        
        // Add admin email
        $admin_email = get_option('admin_email');
        if ($admin_email) {
            $contact_info .= "Email: " . $admin_email . "\n";
        }
        
        $contact_info .= "\nFeel free to contact us if you have any questions!";
        
        return $contact_info;
    }
    
    private function get_search_help() {
        return "I can help you find information on our website! Try asking me about:\n\n" .
               "• Recent articles and posts\n" .
               "• Popular content\n" .
               "• Specific topics you're interested in\n" .
               "• Contact information\n\n" .
               "Just type your question and I'll do my best to help!";
    }
    
    private function build_system_prompt($settings) {
        $prompt = $settings['bot_personality'];
        
        if (empty($prompt)) {
            $prompt = "You are a helpful AI assistant for " . $settings['website_name'] . ". Be friendly, professional, and informative.";
        }
        
        $prompt .= "\n\nWebsite Information:";
        $prompt .= "\n- Website Name: " . $settings['website_name'];
        $prompt .= "\n- Website Type: " . $settings['website_type'];
        
        if (!empty($settings['website_location'])) {
            $prompt .= "\n- Location: " . $settings['website_location'];
        }
        
        if (!empty($settings['content_types'])) {
            $prompt .= "\n- Available Content Types: " . implode(', ', $settings['content_types']);
        }
        
        if (!empty($settings['bot_knowledge'])) {
            $prompt .= "\n\nAdditional Knowledge:\n" . $settings['bot_knowledge'];
        }
        
        $prompt .= "\n\nResponse Style: " . $settings['response_style'];
        $prompt .= "\n\nKeep responses helpful, concise, and relevant to the website context. If you don't know something specific about the website, be honest about it.";
        
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
            'timeout' => intval($settings['response_timeout']),
            'method' => 'POST'
        );
        
        $response = wp_remote_request($this->openrouter_endpoint, $args);
        
        if (is_wp_error($response)) {
            error_log('AI Website Bot API Error: ' . $response->get_error_message());
            return array(
                'success' => false,
                'message' => 'Unable to connect to AI service. Please try again later.'
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body_response = wp_remote_retrieve_body($response);
        
        if ($response_code !== 200) {
            error_log('AI Website Bot API Error: HTTP ' . $response_code . ' - ' . $body_response);
            
            switch ($response_code) {
                case 401:
                    return array(
                        'success' => false,
                        'message' => 'Authentication failed. Please contact the administrator.'
                    );
                case 429:
                    return array(
                        'success' => false,
                        'message' => 'Service is temporarily busy. Please try again in a moment.'
                    );
                case 500:
                    return array(
                        'success' => false,
                        'message' => 'AI service is temporarily unavailable. Please try again later.'
                    );
                default:
                    return array(
                        'success' => false,
                        'message' => 'Service temporarily unavailable. Please try again.'
                    );
            }
        }
        
        $data = json_decode($body_response, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            return array(
                'success' => true,
                'message' => trim($data['choices'][0]['message']['content'])
            );
        }
        
        error_log('AI Website Bot: Invalid API response - ' . $body_response);
        return array(
            'success' => false,
            'message' => 'Sorry, I couldn\'t process your request right now. Please try again.'
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
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
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