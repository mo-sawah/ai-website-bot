<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="ai-bot-admin-container">
    <div class="admin-header">
        <div class="header-content">
            <div class="header-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="#2563eb">
                    <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7H20C19.4 7 19 6.6 19 6C19 5.4 19.4 5 20 5H21V3C21 1.9 20.1 1 19 1H5C3.9 1 3 1.9 3 3V5H4C4.6 5 5 5.4 5 6C5 6.6 4.6 7 4 7H3V9C3 10.1 3.9 11 5 11H8V13H7C6.4 13 6 13.4 6 14V20C6 20.6 6.4 21 7 21H17C17.6 21 18 20.6 18 20V14C18 13.4 17.6 13 17 13H16V11H19C20.1 11 21 10.1 21 9Z"/>
                </svg>
            </div>
            <div class="header-text">
                <h1>AI Website Bot</h1>
                <p>Configure your intelligent chatbot for enhanced user engagement</p>
            </div>
        </div>
    </div>

    <div class="settings-layout">
        <div class="settings-main">
            <div class="settings-panel">
                <div class="tab-navigation">
                    <button class="tab-button active" data-tab="general">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.22,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.22,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.68 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"/>
                        </svg>
                        General
                    </button>
                    <button class="tab-button" data-tab="ai-settings">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,2A2,2 0 0,1 14,4C14,4.74 13.6,5.39 13,5.73V7A1,1 0 0,0 14,8H16A1,1 0 0,0 17,7V5.73C16.4,5.39 16,4.74 16,4A2,2 0 0,1 18,2A2,2 0 0,1 20,4C20,4.74 19.6,5.39 19,5.73V7A3,3 0 0,1 16,10H14A3,3 0 0,1 11,7V5.73C10.4,5.39 10,4.74 10,4A2,2 0 0,1 12,2M6,7A2,2 0 0,1 8,9A2,2 0 0,1 6,11A2,2 0 0,1 4,9A2,2 0 0,1 6,7M6,12A2,2 0 0,1 8,14A2,2 0 0,1 6,16A2,2 0 0,1 4,14A2,2 0 0,1 6,12M6,17A2,2 0 0,1 8,19A2,2 0 0,1 6,21A2,2 0 0,1 4,19A2,2 0 0,1 6,17M18,12A2,2 0 0,1 20,14A2,2 0 0,1 18,16A2,2 0 0,1 16,14A2,2 0 0,1 18,12M18,17A2,2 0 0,1 20,19A2,2 0 0,1 18,21A2,2 0 0,1 16,19A2,2 0 0,1 18,17Z"/>
                        </svg>
                        AI Settings
                    </button>
                    <button class="tab-button" data-tab="appearance">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,3C7.58,3 4,6.58 4,11C4,14.08 6.19,16.63 9.07,17.68L8.86,18.32C8.7,18.92 9.09,19.5 9.71,19.5H14.29C14.91,19.5 15.3,18.92 15.14,18.32L14.93,17.68C17.81,16.63 20,14.08 20,11C20,6.58 16.42,3 12,3M12,5C15.31,5 18,7.69 18,11C18,13.5 16.43,15.63 14.21,16.44L13.86,17.5H10.14L9.79,16.44C7.57,15.63 6,13.5 6,11C6,7.69 8.69,5 12,5M8,11A2,2 0 0,1 10,9A2,2 0 0,1 12,11A2,2 0 0,1 10,13A2,2 0 0,1 8,11M14,11A2,2 0 0,1 16,9A2,2 0 0,1 18,11A2,2 0 0,1 16,13A2,2 0 0,1 14,11Z"/>
                        </svg>
                        Appearance
                    </button>
                    <button class="tab-button" data-tab="advanced">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9,2V8H7V10H9V16H7V18H9V24H11V18H13V16H11V10H13V8H11V2H9M19,2V4H21V6H19V8H21V10H19V12H21V14H19V16H21V18H19V20H21V22H19V24H17V22H15V20H17V18H15V16H17V14H15V12H17V10H15V8H17V6H15V4H17V2H19M5,4V6H3V8H5V10H3V12H5V14H3V16H5V18H3V20H5V22H3V24H1V22H-1V20H1V18H-1V16H1V14H-1V12H1V10H-1V8H1V6H-1V4H1V2H3V4H5Z"/>
                        </svg>
                        Advanced
                    </button>
                </div>

                <div class="tab-content">
                    <!-- General Tab -->
                    <div id="general" class="tab-panel active">
                        <div class="setting-group">
                            <label class="setting-label">Enable Chatbot</label>
                            <p class="setting-description">Turn the chatbot on or off across your website</p>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="enable_chatbot" <?php checked($settings['enable_chatbot']); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Chatbot Status</span>
                            </div>
                        </div>

                        <div class="setting-group">
                            <label class="setting-label">Bot Name</label>
                            <p class="setting-description">The name your chatbot will use when introducing itself</p>
                            <input type="text" name="bot_name" class="setting-input" value="<?php echo esc_attr($settings['bot_name']); ?>" placeholder="e.g., AI Assistant, NewsBot">
                        </div>

                        <div class="setting-group">
                            <label class="setting-label">Website Name</label>
                            <p class="setting-description">Your website/publication name for context</p>
                            <input type="text" name="website_name" class="setting-input" value="<?php echo esc_attr($settings['website_name']); ?>" placeholder="e.g., Your Company Name">
                        </div>

                        <div class="setting-group">
                            <label class="setting-label">Website Location</label>
                            <p class="setting-description">Geographic location to provide relevant local context</p>
                            <input type="text" name="website_location" class="setting-input" value="<?php echo esc_attr($settings['website_location']); ?>" placeholder="e.g., New York, NY">
                        </div>

                        <div class="setting-group">
                            <label class="setting-label">Website Type</label>
                            <p class="setting-description">Select the primary type of your website</p>
                            <select name="website_type" class="setting-input">
                                <option value="news" <?php selected($settings['website_type'], 'news'); ?>>News Website</option>
                                <option value="company" <?php selected($settings['website_type'], 'company'); ?>>Company Website</option>
                                <option value="blog" <?php selected($settings['website_type'], 'blog'); ?>>Blog</option>
                                <option value="ecommerce" <?php selected($settings['website_type'], 'ecommerce'); ?>>E-commerce</option>
                                <option value="portfolio" <?php selected($settings['website_type'], 'portfolio'); ?>>Portfolio</option>
                                <option value="other" <?php selected($settings['website_type'], 'other'); ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- AI Settings Tab -->
                    <div id="ai-settings" class="tab-panel">
                        <div class="info-box">
                            <div class="info-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M11,9H13V7H11M12,20C7.59,20 4,16.41 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,16.41 16.41,20 12,20M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M11,17H13V11H11V17Z"/>
                                </svg>
                            </div>
                            <div class="info-content">
                                <h4>OpenRouter API Configuration</h4>
                                <p>Get your API key from <a href="https://openrouter.ai" target="_blank">openrouter.ai</a> to enable AI functionality.</p>
                            </div>
                        </div>

                        <div class="setting-group">
                            <label class="setting-label">OpenRouter API Key</label>
                            <p class="setting-description">Your OpenRouter API key for accessing AI models</p>
                            <input type="password" name="openrouter_api_key" class="setting-input" value="<?php echo esc_attr($settings['openrouter_api_key']); ?>" placeholder="sk-or-v1-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        </div>

                        <div class="setting-group">
                            <label class="setting-label">AI Model</label>
                            <p class="setting-description">
                                Enter your preferred AI model from OpenRouter. 
                                <br><strong>Recommended free models:</strong> mistralai/mistral-7b-instruct:free, google/gemini-flash-1.5, anthropic/claude-3-haiku
                                <br><a href="https://openrouter.ai/models" target="_blank">Browse all available models →</a>
                            </p>
                            <input type="text" name="ai_model" class="setting-input" value="<?php echo esc_attr($settings['ai_model']); ?>" placeholder="e.g., mistralai/mistral-7b-instruct:free">
                        </div>

                        <div class="setting-group">
                            <label class="setting-label">Bot Personality</label>
                            <p class="setting-description">Define how your bot should behave and communicate</p>
                            <textarea name="bot_personality" class="setting-textarea" rows="4" placeholder="You are a helpful assistant..."><?php echo esc_textarea($settings['bot_personality']); ?></textarea>
                        </div>

                        <div class="setting-group">
                            <label class="setting-label">Bot Knowledge Base</label>
                            <p class="setting-description">Additional information about your website, company, or content</p>
                            <textarea name="bot_knowledge" class="setting-textarea" rows="4" placeholder="Include key information about your company..."><?php echo esc_textarea($settings['bot_knowledge']); ?></textarea>
                        </div>

                        <div class="api-test-section">
                            <h4>Test API Connection</h4>
                            <p>Verify your OpenRouter API configuration</p>
                            <button type="button" class="btn btn-secondary" id="test-api">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9Z"/>
                                </svg>
                                Test Connection
                            </button>
                            <div id="api-status" class="api-status"></div>
                        </div>
                    </div>

                    <!-- Appearance Tab -->
                    <div id="appearance" class="tab-panel">
                        <div class="setting-group">
                            <label class="setting-label">Chatbot Position</label>
                            <p class="setting-description">Where should the chatbot appear on your website?</p>
                            <select name="chatbot_position" class="setting-input">
                                <option value="bottom-right" <?php selected($settings['chatbot_position'], 'bottom-right'); ?>>Bottom Right</option>
                                <option value="bottom-left" <?php selected($settings['chatbot_position'], 'bottom-left'); ?>>Bottom Left</option>
                                <option value="sidebar" <?php selected($settings['chatbot_position'], 'sidebar'); ?>>Sidebar Widget</option>
                                <option value="inline" <?php selected($settings['chatbot_position'], 'inline'); ?>>Inline (Shortcode)</option>
                            </select>
                        </div>

                        <div class="setting-group">
                            <label class="setting-label">Primary Color</label>
                            <p class="setting-description">Main color for the chatbot interface</p>
                            <input type="color" name="primary_color" class="color-input" value="<?php echo esc_attr($settings['primary_color']); ?>">
                        </div>

                        <div class="setting-group">
                            <label class="setting-label">Chat Icon</label>
                            <p class="setting-description">Choose an icon for the chat bubble</p>
                            <select name="chat_icon" class="setting-input">
                                <option value="chat" <?php selected($settings['chat_icon'], 'chat'); ?>>Chat Bubble</option>
                                <option value="robot" <?php selected($settings['chat_icon'], 'robot'); ?>>Robot</option>
                                <option value="help" <?php selected($settings['chat_icon'], 'help'); ?>>Help</option>
                                <option value="support" <?php selected($settings['chat_icon'], 'support'); ?>>Support</option>
                            </select>
                        </div>

                        <div class="setting-group">
                            <label class="setting-label">Welcome Message</label>
                            <p class="setting-description">First message shown when users open the chat</p>
                            <textarea name="welcome_message" class="setting-textarea" rows="3"><?php echo esc_textarea($settings['welcome_message']); ?></textarea>
                        </div>

                        <div class="setting-group">
                            <label class="setting-label">Quick Actions</label>
                            <p class="setting-description">Pre-defined buttons for common queries (one per line)</p>
                            <textarea name="quick_actions" class="setting-textarea" rows="4"><?php echo esc_textarea($settings['quick_actions']); ?></textarea>
                        </div>
                    </div>

                    <!-- Advanced Tab -->
                    <div id="advanced" class="tab-panel">
                        <div class="setting-group">
                            <label class="setting-label">Rate Limiting</label>
                            <p class="setting-description">Maximum messages per user per hour</p>
                            <input type="number" name="rate_limit" class="setting-input" value="<?php echo esc_attr($settings['rate_limit']); ?>" min="1" max="1000">
                        </div>

                        <div class="setting-group">
                            <label class="setting-label">Response Timeout</label>
                            <p class="setting-description">Maximum time to wait for AI response (seconds)</p>
                            <input type="number" name="response_timeout" class="setting-input" value="<?php echo esc_attr($settings['response_timeout']); ?>" min="5" max="120">
                        </div>

                        <div class="setting-group">
                            <label class="setting-label">Search Integration</label>
                            <p class="setting-description">Allow bot to search your website content</p>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="search_integration" <?php checked($settings['search_integration']); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="setting-group">
                            <label class="setting-label">Analytics Tracking</label>
                            <p class="setting-description">Track chatbot usage and popular queries</p>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="analytics_tracking" <?php checked($settings['analytics_tracking']); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="setting-group">
                            <label class="setting-label">Custom CSS</label>
                            <p class="setting-description">Additional CSS to customize the chatbot appearance</p>
                            <textarea name="custom_css" class="setting-textarea code-textarea" rows="6" placeholder=".ai-chatbot-widget { /* Your custom styles */ }"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="settings-footer">
                    <button type="button" class="btn btn-primary" id="save-settings">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15,9H5V5H15M12,19A3,3 0 0,1 9,16A3,3 0 0,1 12,13A3,3 0 0,1 15,16A3,3 0 0,1 12,19M17,3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V7L17,3Z"/>
                        </svg>
                        Save Settings
                    </button>
                    <button type="button" class="btn btn-secondary" id="reset-settings">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M4,2H20A2,2 0 0,1 22,4V16A2,2 0 0,1 20,18H6L2,22V4A2,2 0 0,1 4,2M5,4V17.17L6.17,16H20V4H5Z"/>
                        </svg>
                        Reset to Defaults
                    </button>
                </div>
            </div>
        </div>

        <div class="settings-sidebar">
            <div class="preview-panel">
                <h4>Live Preview</h4>
                <div class="chatbot-preview">
                    <div class="preview-bubble">
                        <div class="bubble-icon" id="preview-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2ZM20 16H5.17L4 17.17V4H20V16Z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="preview-window">
                        <div class="preview-header">
                            <span id="preview-bot-name">AI Assistant</span>
                            <div class="status-indicator"></div>
                        </div>
                        <div class="preview-messages">
                            <div class="preview-message bot">
                                <span id="preview-welcome">Hi! How can I help you today?</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-panel">
                <h4>Usage Statistics</h4>
                <div class="stat-item">
                    <span class="stat-label">Total Conversations</span>
                    <span class="stat-value">0</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">This Month</span>
                    <span class="stat-value">0</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Success Rate</span>
                    <span class="stat-value">-%</span>
                </div>
            </div>

            <div class="info-panel">
                <h4>Quick Start</h4>
                <div class="info-content">
                    <p>1. Add your OpenRouter API key</p>
                    <p>2. Choose an AI model</p>
                    <p>3. Customize appearance</p>
                    <p>4. Test and save settings</p>
                </div>
                <p class="shortcode-info">
                    <strong>Shortcode:</strong> <code>[ai_website_bot]</code>
                </p>
            </div>
        </div>
    </div>
    <input type="hidden" id="ai-bot-nonce" value="<?php echo wp_create_nonce('ai_bot_admin_nonce'); ?>">
</div>

<script>
jQuery(document).ready(function($) {
    $('#save-settings').on('click', function() {
        // Settings save functionality will be handled by admin.js
    });
});
</script>