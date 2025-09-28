(function ($) {
  "use strict";

  class AIChatbot {
    constructor() {
      this.isOpen = false;
      this.isMinimized = false;
      this.isTyping = false;
      this.messageHistory = [];

      this.init();
    }

    init() {
      this.bindEvents();
      this.loadChatHistory();
      this.setupAutoResize();
    }

    bindEvents() {
      const $widget = $("#ai-chatbot-widget");
      const $bubble = $("#chat-bubble");
      const $window = $("#chat-window");
      const $closeBtn = $("#close-chat");
      const $minimizeBtn = $("#minimize-chat");
      const $sendBtn = $("#send-message");
      const $input = $("#message-input");
      const $quickActions = $(".quick-action-btn");

      // Toggle chat window
      $bubble.on("click", () => this.toggleChat());
      $closeBtn.on("click", () => this.closeChat());
      $minimizeBtn.on("click", () => this.minimizeChat());

      // Send message
      $sendBtn.on("click", () => this.sendMessage());
      $input.on("keypress", (e) => {
        if (e.which === 13 && !e.shiftKey) {
          e.preventDefault();
          this.sendMessage();
        }
      });

      // Quick actions
      $quickActions.on("click", (e) => {
        const action = $(e.target).data("action");
        this.sendQuickAction(action);
      });

      // Close on outside click
      $(document).on("click", (e) => {
        if (this.isOpen && !$widget.has(e.target).length) {
          this.closeChat();
        }
      });

      // ESC key to close
      $(document).on("keydown", (e) => {
        if (e.key === "Escape" && this.isOpen) {
          this.closeChat();
        }
      });
    }

    toggleChat() {
      if (this.isOpen) {
        this.closeChat();
      } else {
        this.openChat();
      }
    }

    openChat() {
      const $window = $("#chat-window");
      const $bubble = $("#chat-bubble");

      $window.addClass("open");
      $bubble.hide();
      this.isOpen = true;
      this.isMinimized = false;

      // Focus input
      setTimeout(() => {
        $("#message-input").focus();
      }, 300);

      // Scroll to bottom
      this.scrollToBottom();

      // Track event
      this.trackEvent("chat_opened");
    }

    closeChat() {
      const $window = $("#chat-window");
      const $bubble = $("#chat-bubble");

      $window.removeClass("open");
      $bubble.show();
      this.isOpen = false;
      this.isMinimized = false;

      // Track event
      this.trackEvent("chat_closed");
    }

    minimizeChat() {
      const $window = $("#chat-window");

      $window.removeClass("open");
      this.isOpen = false;
      this.isMinimized = true;

      // Show notification on bubble
      this.showBubbleNotification();
    }

    sendMessage() {
      const $input = $("#message-input");
      const message = $input.val().trim();

      if (!message || this.isTyping) {
        return;
      }

      this.addMessage(message, "user");
      $input.val("");
      this.resizeInput();
      this.hideQuickActions();

      // Send to server
      this.processMessage(message);
    }

    sendQuickAction(action) {
      this.addMessage(action, "user");
      this.hideQuickActions();
      this.processMessage(action);
    }

    addMessage(text, sender) {
      const $messages = $("#chat-messages");
      const time = new Date().toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
      });

      const messageHtml = `
                <div class="message ${sender}-message">
                    <div class="message-avatar">
                        ${sender === "bot" ? this.getBotIcon() : "U"}
                    </div>
                    <div class="message-content">
                        <div class="message-text">${this.formatMessage(
                          text
                        )}</div>
                        <div class="message-time">${time}</div>
                    </div>
                </div>
            `;

      $messages.append(messageHtml);
      this.scrollToBottom();

      // Store in history
      this.messageHistory.push({
        text: text,
        sender: sender,
        timestamp: new Date().toISOString(),
      });

      this.saveChatHistory();
    }

    processMessage(message) {
      this.showTyping();

      const data = {
        action: "ai_bot_chat",
        message: message,
        nonce: aiBotAjax.nonce,
        history: this.getRecentHistory(),
      };

      $.ajax({
        url: aiBotAjax.ajaxurl,
        type: "POST",
        data: data,
        timeout: 30000,
        success: (response) => {
          this.hideTyping();

          if (response.success) {
            this.addMessage(response.data.message, "bot");
            this.trackEvent("message_success");
          } else {
            this.addMessage(
              response.data ||
                "Sorry, I encountered an error. Please try again.",
              "bot"
            );
            this.trackEvent("message_error");
          }
        },
        error: (xhr, status, error) => {
          this.hideTyping();
          let errorMessage =
            "Sorry, I'm having trouble connecting. Please try again.";

          if (status === "timeout") {
            errorMessage = "The response took too long. Please try again.";
          }

          this.addMessage(errorMessage, "bot");
          this.trackEvent("message_error", { error: error });
        },
      });
    }

    showTyping() {
      this.isTyping = true;
      $("#typing-indicator").addClass("show");
      $("#send-message").prop("disabled", true);
      this.scrollToBottom();
    }

    hideTyping() {
      this.isTyping = false;
      $("#typing-indicator").removeClass("show");
      $("#send-message").prop("disabled", false);
    }

    hideQuickActions() {
      $("#quick-actions").fadeOut();
    }

    formatMessage(text) {
      // Basic formatting
      text = text.replace(/\n/g, "<br>");

      // Make URLs clickable
      const urlRegex = /(https?:\/\/[^\s]+)/g;
      text = text.replace(
        urlRegex,
        '<a href="$1" target="_blank" rel="noopener">$1</a>'
      );

      // Basic markdown-like formatting
      text = text.replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>");
      text = text.replace(/\*(.*?)\*/g, "<em>$1</em>");

      return text;
    }

    getBotIcon() {
      const settings = aiBotAjax.settings;
      const icons = {
        chat: '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2ZM20 16H5.17L4 17.17V4H20V16Z"/></svg>',
        robot:
          '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7H20C19.4 7 19 6.6 19 6C19 5.4 19.4 5 20 5H21V3C21 1.9 20.1 1 19 1H5C3.9 1 3 1.9 3 3V5H4C4.6 5 5 5.4 5 6C5 6.6 4.6 7 4 7H3V9C3 10.1 3.9 11 5 11H8V13H7C6.4 13 6 13.4 6 14V20C6 20.6 6.4 21 7 21H17C17.6 21 18 20.6 18 20V14C18 13.4 17.6 13 17 13H16V11H19C20.1 11 21 10.1 21 9Z"/></svg>',
        help: '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 19H11V17H13V19ZM15.07 11.25L14.17 12.17C13.45 12.9 13 13.5 13 15H11V14.5C11 13.4 11.45 12.4 12.17 11.67L13.41 10.41C13.78 10.05 14 9.55 14 9C14 7.9 13.1 7 12 7C10.9 7 10 7.9 10 9H8C8 6.79 9.79 5 12 5C14.21 5 16 6.79 16 9C16 9.88 15.64 10.68 15.07 11.25Z"/></svg>',
        support:
          '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1C18.08 1 23 5.92 23 12C23 18.08 18.08 23 12 23C10.5 23 9.04 22.65 7.74 22L1 23L2.73 17.74C2.05 16.44 1.67 14.97 1.67 13.44C1.67 7.36 6.59 2.44 12.67 2.44H12C12 1 12 1 12 1ZM12 3C7.04 3 3 7.04 3 12C3 13.54 3.5 14.94 4.36 16.09L3.5 19.5L7.09 18.64C8.24 19.5 9.64 20 11.18 20H12C16.96 20 21 15.96 21 11C21 6.04 16.96 2 12 2V3Z"/></svg>',
      };

      return icons[settings.chatIcon] || icons.chat;
    }

    setupAutoResize() {
      const $input = $("#message-input");

      $input.on("input", () => {
        this.resizeInput();
      });
    }

    resizeInput() {
      const $input = $("#message-input");
      $input[0].style.height = "auto";
      $input[0].style.height = Math.min($input[0].scrollHeight, 120) + "px";
    }

    scrollToBottom() {
      const $messages = $("#chat-messages");
      $messages.scrollTop($messages[0].scrollHeight);
    }

    getRecentHistory() {
      return this.messageHistory.slice(-10); // Last 10 messages for context
    }

    loadChatHistory() {
      const history = localStorage.getItem("ai_bot_history");
      if (history) {
        try {
          this.messageHistory = JSON.parse(history);
        } catch (e) {
          this.messageHistory = [];
        }
      }
    }

    saveChatHistory() {
      // Keep only last 50 messages
      if (this.messageHistory.length > 50) {
        this.messageHistory = this.messageHistory.slice(-50);
      }

      localStorage.setItem(
        "ai_bot_history",
        JSON.stringify(this.messageHistory)
      );
    }

    showBubbleNotification() {
      const $bubble = $("#chat-bubble");
      $bubble.addClass("has-notification");

      setTimeout(() => {
        $bubble.removeClass("has-notification");
      }, 3000);
    }

    trackEvent(event, data = {}) {
      // Basic analytics tracking
      if (typeof gtag !== "undefined") {
        gtag("event", event, {
          event_category: "AI_Chatbot",
          ...data,
        });
      }

      // Send to WordPress
      $.post(aiBotAjax.ajaxurl, {
        action: "ai_bot_track_event",
        event: event,
        data: data,
        nonce: aiBotAjax.nonce,
      });
    }
  }

  // Initialize when DOM is ready
  $(document).ready(function () {
    if ($("#ai-chatbot-widget").length) {
      new AIChatbot();
    }
  });
})(jQuery);
