<?php
if (!defined('ABSPATH')) {
    exit;
}

$settings = AI_Website_Bot_Settings::get_all_settings();
$frontend = new AI_Website_Bot_Frontend();
?>

<div class="ai-chatbot-inline" style="width: <?php echo esc_attr($atts['width']); ?>; height: <?php echo esc_attr($atts['height']); ?>;">
    <div class="inline-chat-window">
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
        </div>

        <div class="chat-messages" id="inline-chat-messages">
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
        <div class="quick-actions">
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
                    class="message-input inline-message-input" 
                    placeholder="Type your message..." 
                    rows="1"
                ></textarea>
                <button class="send-btn inline-send-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2,21L23,12L2,3V10L17,12L2,14V21Z"/>
                    </svg>
                </button>
            </div>
            <div class="typing-indicator inline-typing-indicator">
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span class="typing-text"><?php echo esc_html($settings['bot_name']); ?> is typing...</span>
            </div>
        </div>
    </div>
</div>

<style>
.ai-chatbot-inline {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
    background: white;
    border: 1px solid #e2e8f0;
}

.inline-chat-window {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.ai-chatbot-inline .chat-messages {
    background: linear-gradient(180deg, #f9fafb 0%, #ffffff 100%);
}
</style>