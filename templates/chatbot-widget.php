<?php
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
?>

<div id="aiwb-chatbot-widget" class="aiwb-chatbot-widget" data-position="<?php echo esc_attr($settings['chatbot_position']); ?>" data-aiwb-theme="<?php echo esc_attr($theme_mode); ?>">
    <div class="aiwb-chat-bubble" id="aiwb-chat-bubble">
        <div class="aiwb-bubble-icon">
            <?php echo $frontend->get_chat_icon_svg($settings['chat_icon']); ?>
        </div>
        <div class="aiwb-bubble-pulse"></div>
    </div>

    <div class="aiwb-chat-window" id="aiwb-chat-window">
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

        <?php if (!empty($settings['quick_actions'])): ?>
        <div class="aiwb-quick-actions" id="aiwb-quick-actions">
            <?php 
            $actions = explode("\n", $settings['quick_actions']);
            foreach ($actions as $action): 
                if (trim($action)):
            ?>
            <button class="aiwb-quick-action-btn" data-action="<?php echo esc_attr(trim($action)); ?>">
                <?php echo esc_html(trim($action)); ?>
            </button>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>
        <?php endif; ?>

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