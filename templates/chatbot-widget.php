<?php
if (!defined('ABSPATH')) {
    exit;
}

$settings = AI_Website_Bot_Settings::get_all_settings();
$frontend = new AI_Website_Bot_Frontend();
?>

<div id="ai-chatbot-widget" class="ai-chatbot-widget" data-position="<?php echo esc_attr($settings['chatbot_position']); ?>">
    <div class="chat-bubble" id="chat-bubble">
        <div class="bubble-icon">
            <?php echo $frontend->get_chat_icon_svg($settings['chat_icon']); ?>
        </div>
        <div class="bubble-pulse"></div>
    </div>

    <div class="chat-window" id="chat-window">
        <div class="chat-header">
            <div class="bot-avatar">
                <div class="avatar-icon">
                    <?php echo $frontend->get_chat_icon_svg($settings['chat_icon']); ?>
                </div>
            </div>
            <div class="bot-info">
                <h4 class="bot-name"><?php echo esc_html($settings['bot_name']); ?></h4>
                <span class="bot-status">
                    <span class="status-dot"></span>
                    Online
                </span>
            </div>
            <div class="chat-controls">
                <button class="minimize-btn" id="minimize-chat">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19,13H5V11H19V13Z"/>
                    </svg>
                </button>
                <button class="close-btn" id="close-chat">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="chat-messages" id="chat-messages">
            <div class="message bot-message">
                <div class="message-avatar">
                    <?php echo $frontend->get_chat_icon_svg($settings['chat_icon']); ?>
                </div>
                <div class="message-content">
                    <div class="message-text"><?php echo esc_html($settings['welcome_message']); ?></div>
                    <div class="message-time"><?php echo current_time('H:i'); ?></div>
                </div>
            </div>
        </div>

        <?php if (!empty($settings['quick_actions'])): ?>
        <div class="quick-actions" id="quick-actions">
            <?php 
            $actions = explode("\n", $settings['quick_actions']);
            foreach ($actions as $action): 
                if (trim($action)):
            ?>
            <button class="quick-action-btn" data-action="<?php echo esc_attr(trim($action)); ?>">
                <?php echo esc_html(trim($action)); ?>
            </button>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>
        <?php endif; ?>

        <div class="chat-input">
            <div class="input-container">
                <textarea 
                    id="message-input" 
                    class="message-input" 
                    placeholder="Type your message..." 
                    rows="1"
                ></textarea>
                <button class="send-btn" id="send-message">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2,21L23,12L2,3V10L17,12L2,14V21Z"/>
                    </svg>
                </button>
            </div>
            <div class="typing-indicator" id="typing-indicator">
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span class="typing-text"><?php echo esc_html($settings['bot_name']); ?> is typing...</span>
            </div>
        </div>
        </div>
</div><style>
:root {
    --ai-bot-primary: <?php echo esc_attr($settings['primary_color']); ?>;
    --ai-bot-primary-dark: <?php echo esc_attr($this->darken_color($settings['primary_color'], 0.1)); ?>;
    --ai-bot-primary-light: <?php echo esc_attr($this->lighten_color($settings['primary_color'], 0.9)); ?>;
}
</style><?php if (!empty($settings['custom_css'])): ?>
<style><?php echo wp_strip_all_tags($settings['custom_css']); ?></style>
<?php endif; ?>