<?php
// Update your chatbot-widget.php template:

if (!defined('ABSPATH')) {
    exit;
}

$settings = AI_Website_Bot_Settings::get_all_settings();
$frontend = new AI_Website_Bot_Frontend();

// Determine theme mode
$theme_mode = $settings['theme_mode'];
if ($settings['auto_theme']) {
    $theme_mode = 'auto';
}

// Check if we're on an article page
$is_article_page = is_single() && get_post_type() === 'post';
?>

<div id="aiwb-chatbot-widget" class="aiwb-chatbot-widget" data-position="<?php echo esc_attr($settings['chatbot_position']); ?>" data-aiwb-theme="<?php echo esc_attr($theme_mode); ?>">
    <!-- Keep your existing chat bubble -->
    <div class="aiwb-chat-bubble" id="aiwb-chat-bubble">
        <div class="aiwb-bubble-icon">
            <?php echo $frontend->get_chat_icon_svg($settings['chat_icon']); ?>
        </div>
        <div class="aiwb-bubble-pulse"></div>
    </div>

    <div class="aiwb-chat-window" id="aiwb-chat-window">
        <!-- Keep your existing chat header -->
        <div class="aiwb-chat-header">
            <div class="aiwb-bot-avatar">
                <div class="aiwb-avatar-icon">
                    <?php echo $frontend->get_chat_icon_svg($settings['chat_icon']); ?>
                </div>
            </div>
            <div class="aiwb-bot-info">
                <h4 class="aiwb-bot-name"><?php echo esc_html($settings['bot_name']); ?></h4>
                <span class="aiwb-bot-status">
                    <span class="aiwb-status-dot"></span>
                    Online
                </span>
            </div>
            <div class="aiwb-chat-controls">
                <button class="aiwb-minimize-btn" id="aiwb-minimize-chat">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19,13H5V11H19V13Z"/>
                    </svg>
                </button>
                <button class="aiwb-close-btn" id="aiwb-close-chat">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Keep your existing messages area -->
        <div class="aiwb-chat-messages" id="aiwb-chat-messages">
            <div class="aiwb-message aiwb-bot-message">
                <div class="aiwb-message-avatar">
                    <?php echo $frontend->get_chat_icon_svg($settings['chat_icon']); ?>
                </div>
                <div class="aiwb-message-content">
                    <div class="aiwb-message-text"><?php echo esc_html($settings['welcome_message']); ?></div>
                    <div class="aiwb-message-time"><?php echo current_time('H:i'); ?></div>
                </div>
            </div>
        </div>

        <!-- NEW: Improved Action Section -->
        <div class="aiwb-action-section" id="aiwb-action-section">
            <!-- General Actions -->
            <div class="aiwb-action-group">
                <div class="aiwb-group-header">
                    <div class="aiwb-group-label">Browse</div>
                </div>
                <div class="aiwb-action-buttons">
                    <button class="aiwb-action-btn aiwb-primary" data-action="Recent Posts">
                        <svg class="aiwb-btn-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M12,6A6,6 0 0,1 18,12A6,6 0 0,1 12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6Z"/>
                        </svg>
                        Recent
                    </button>
                    <button class="aiwb-action-btn aiwb-primary" data-action="Popular Content">
                        <svg class="aiwb-btn-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M16,6L18.29,8.29L13.41,13.17L9.41,9.17L2,16.59L3.41,18L9.41,12L13.41,16L19.71,9.71L22,12V6H16Z"/>
                        </svg>
                        Popular
                    </button>
                    <button class="aiwb-action-btn aiwb-primary" data-action="Search Help">
                        <svg class="aiwb-btn-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/>
                        </svg>
                        Search
                    </button>
                    <button class="aiwb-action-btn aiwb-primary" data-action="Contact Info">
                        <svg class="aiwb-btn-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22,4C22,2.89 21.1,2 20,2H4A2,2 0 0,0 2,4V16A2,2 0 0,0 4,18H18L22,22V4Z"/>
                        </svg>
                        Contact
                    </button>
                </div>
            </div>

            <?php if ($is_article_page): ?>
            <!-- Article-specific actions -->
            <div class="aiwb-action-group">
                <div class="aiwb-group-header">
                    <div class="aiwb-group-label">This Article</div>
                    <div class="aiwb-group-badge">LIVE</div>
                </div>
                <div class="aiwb-action-buttons">
                    <button class="aiwb-action-btn aiwb-context" data-action="Summarize">
                        <svg class="aiwb-btn-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                        Summarize
                        <span class="aiwb-context-indicator"></span>
                    </button>
                    <button class="aiwb-action-btn aiwb-context" data-action="Key Points">
                        <svg class="aiwb-btn-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3,5H9V11H3V5M5,7V9H7V7H5M11,7H21V9H11V7M11,15H21V17H11V15M5,20L1.5,16.5L2.91,15.09L5,17.17L9.59,12.59L11,14L5,20Z"/>
                        </svg>
                        Key Points
                    </button>
                    <button class="aiwb-action-btn aiwb-context" data-action="Related Articles">
                        <svg class="aiwb-btn-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10.59,13.41C11,13.8 11,14.4 10.59,14.81C10.2,15.2 9.6,15.2 9.19,14.81L7.05,12.67L9.19,10.53C9.6,10.12 10.2,10.12 10.59,10.53C11,10.94 11,11.54 10.59,11.95L10.59,13.41M14.41,13.41L14.41,11.95C14,11.54 14,10.94 14.41,10.53C14.8,10.12 15.4,10.12 15.81,10.53L17.95,12.67L15.81,14.81C15.4,15.2 14.8,15.2 14.41,14.81C14,14.4 14,13.8 14.41,13.41M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z"/>
                        </svg>
                        Related
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Keep your existing chat input -->
        <div class="aiwb-chat-input">
            <div class="aiwb-input-container">
                <textarea 
                    id="aiwb-message-input" 
                    class="aiwb-message-input" 
                    placeholder="Type your message..." 
                    rows="1"
                ></textarea>
                <button class="aiwb-send-btn" id="aiwb-send-message">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2,21L23,12L2,3V10L17,12L2,14V21Z"/>
                    </svg>
                </button>
            </div>
            <div class="aiwb-typing-indicator" id="aiwb-typing-indicator">
                <div class="aiwb-typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span class="aiwb-typing-text"><?php echo esc_html($settings['bot_name']); ?> is typing...</span>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --aiwb-primary: <?php echo esc_attr($settings['primary_color']); ?>;
    --aiwb-primary-dark: <?php echo esc_attr($frontend->darken_color($settings['primary_color'], 0.1)); ?>;
    --aiwb-primary-light: <?php echo esc_attr($frontend->lighten_color($settings['primary_color'], 0.9)); ?>;
}
</style>
<?php if (!empty($settings['custom_css'])): ?>
<style><?php echo wp_strip_all_tags($settings['custom_css']); ?></style>
<?php endif; ?>