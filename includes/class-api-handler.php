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
                // Check for search queries
                if (preg_match('/latest\s+(\d+)\s+articles?\s+about\s+(.+)/i', $message, $matches)) {
                    $count = intval($matches[1]);
                    $search_term = trim($matches[2]);
                    return $this->search_website_articles($search_term, $count);
                }
                
                // Check for general search queries
                if (preg_match('/search\s+for\s+(.+)/i', $message, $matches)) {
                    $search_term = trim($matches[1]);
                    return $this->search_website_articles($search_term, 5);
                }
                
                return null;
        }
    }

    private function search_website_articles($search_term, $count = 5) {
        // Enhanced search with multiple fields and better relevance
        $search_query = new WP_Query(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $count * 2,
            's' => $search_term,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_yoast_wpseo_metadesc',
                    'value' => $search_term,
                    'compare' => 'LIKE'
                )
            )
        ));
        
        $posts = $search_query->posts;
        
        if (empty($posts)) {
            $posts = $this->fuzzy_search($search_term, $count);
        }
        
        if (empty($posts)) {
            return "I couldn't find any articles about '{$search_term}' on our website. Try browsing our recent posts or contact us for specific information.";
        }
        
        $scored_posts = $this->score_search_results($posts, $search_term);
        $top_posts = array_slice($scored_posts, 0, $count);
        
        $response = "Here are the most relevant articles about '{$search_term}':\n\n";
        
        foreach ($top_posts as $index => $post_data) {
            $post = $post_data['post'];
            
            // Add article number for better organization
            $response .= "**" . ($index + 1) . ". " . esc_html($post->post_title) . "**\n";
            $response .= "📅 " . date('M j, Y', strtotime($post->post_date)) . "\n";
            
            // Add excerpt if available with better formatting
            if (!empty($post->post_excerpt)) {
                $excerpt = wp_trim_words(strip_tags($post->post_excerpt), 15);
                $response .= "📝 " . $excerpt . "\n";
            }
            
            $response .= "🔗 [Read Full Article](" . get_permalink($post->ID) . ")\n";
            
            // Add separator between articles (except for the last one)
            if ($index < count($top_posts) - 1) {
                $response .= "\n---\n\n";
            } else {
                $response .= "\n";
            }
        }
        
        return $response;
    }

    private function fuzzy_search($search_term, $count) {
        global $wpdb;
        
        // Split search term into individual words
        $words = explode(' ', $search_term);
        $word_conditions = array();
        
        foreach ($words as $word) {
            if (strlen(trim($word)) > 2) { // Skip very short words
                $word_conditions[] = $wpdb->prepare(
                    "(post_title LIKE %s OR post_content LIKE %s)",
                    '%' . $word . '%',
                    '%' . $word . '%'
                );
            }
        }
        
        if (empty($word_conditions)) {
            return array();
        }
        
        $where_clause = implode(' OR ', $word_conditions);
        
        $query = "
            SELECT * FROM {$wpdb->posts} 
            WHERE post_status = 'publish' 
            AND post_type = 'post'
            AND ({$where_clause})
            ORDER BY post_date DESC 
            LIMIT " . ($count * 2);
        
        return $wpdb->get_results($query);
    }

    private function score_search_results($posts, $search_term) {
        $scored_posts = array();
        $search_words = explode(' ', strtolower($search_term));
        
        foreach ($posts as $post) {
            $score = 0;
            $title_lower = strtolower($post->post_title);
            $content_lower = strtolower($post->post_content);
            
            foreach ($search_words as $word) {
                if (strlen(trim($word)) < 3) continue;
                
                // Title matches get highest score
                if (strpos($title_lower, $word) !== false) {
                    $score += 10;
                    
                    // Exact title match gets bonus
                    if ($title_lower === strtolower($search_term)) {
                        $score += 20;
                    }
                }
                
                // Content matches
                $content_matches = substr_count($content_lower, $word);
                $score += $content_matches * 2;
                
                // Recent posts get slight boost
                $days_old = (time() - strtotime($post->post_date)) / (60 * 60 * 24);
                if ($days_old < 30) {
                    $score += 3;
                } elseif ($days_old < 90) {
                    $score += 1;
                }
            }
            
            // Category relevance (if you have specific categories)
            $categories = get_the_category($post->ID);
            foreach ($categories as $category) {
                foreach ($search_words as $word) {
                    if (strpos(strtolower($category->name), $word) !== false) {
                        $score += 5;
                    }
                }
            }
            
            if ($score > 0) {
                $scored_posts[] = array(
                    'post' => $post,
                    'score' => $score
                );
            }
        }
        
        // Sort by score (highest first)
        usort($scored_posts, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        return $scored_posts;
    }
    
    private function get_recent_posts() {
        $recent_posts = wp_get_recent_posts(array(
            'numberposts' => 5,
            'post_status' => 'publish'
        ));
        
        if (empty($recent_posts)) {
            return 'No recent posts found.';
        }
        
        $response = "📰 **Latest Articles:**\n\n";
        
        foreach ($recent_posts as $index => $post) {
            $response .= "**" . ($index + 1) . ". " . esc_html($post['post_title']) . "**\n";
            $response .= "📅 " . date('M j, Y', strtotime($post['post_date'])) . "\n";
            
            // Add excerpt if available
            if (!empty($post['post_excerpt'])) {
                $excerpt = wp_trim_words(strip_tags($post['post_excerpt']), 15);
                $response .= "📝 " . $excerpt . "\n";
            }
            
            $response .= "🔗 [Read Article](" . get_permalink($post['ID']) . ")\n";
            
            // Add separator
            if ($index < count($recent_posts) - 1) {
                $response .= "\n---\n\n";
            } else {
                $response .= "\n";
            }
        }
        
        return $response;
    }

    private function get_popular_content() {
        $popular_posts = get_posts(array(
            'numberposts' => 5,
            'orderby' => 'comment_count',
            'order' => 'DESC',
            'post_status' => 'publish'
        ));
        
        if (empty($popular_posts)) {
            return 'No popular content found.';
        }
        
        $response = "🔥 **Most Popular Articles:**\n\n";
        
        foreach ($popular_posts as $index => $post) {
            $response .= "**" . ($index + 1) . ". " . esc_html($post->post_title) . "**\n";
            $response .= "📅 " . date('M j, Y', strtotime($post->post_date)) . "\n";
            
            // Add comment count for popular posts
            $comment_count = get_comments_number($post->ID);
            if ($comment_count > 0) {
                $response .= "💬 " . $comment_count . " comment" . ($comment_count != 1 ? 's' : '') . "\n";
            }
            
            $response .= "🔗 [Read Article](" . get_permalink($post->ID) . ")\n";
            
            // Add separator
            if ($index < count($popular_posts) - 1) {
                $response .= "\n---\n\n";
            } else {
                $response .= "\n";
            }
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