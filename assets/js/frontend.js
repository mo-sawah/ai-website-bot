(function ($) {
  "use strict";

  class AIWebsiteChatbot {
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
      this.initTheme();
    }

    initTheme() {
      const settings = aiBotAjax.settings;
      if (settings.autoTheme) {
        this.setupAutoTheme();
      } else {
        this.setTheme(settings.themeMode);
      }
    }

    setupAutoTheme() {
      const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
      this.setTheme(mediaQuery.matches ? "dark" : "light");

      mediaQuery.addEventListener("change", (e) => {
        this.setTheme(e.matches ? "dark" : "light");
      });
    }

    setTheme(theme) {
      const $widget = $("#aiwb-chatbot-widget");
      $widget.attr("data-aiwb-theme", theme);
    }

    bindEvents() {
      const $widget = $("#aiwb-chatbot-widget");
      const $bubble = $("#aiwb-chat-bubble");
      const $window = $("#aiwb-chat-window");
      const $closeBtn = $("#aiwb-close-chat");
      const $minimizeBtn = $("#aiwb-minimize-chat");
      const $sendBtn = $("#aiwb-send-message");
      const $input = $("#aiwb-message-input");
      const $quickActions = $(".aiwb-quick-action-btn");

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
      const $window = $("#aiwb-chat-window");
      const $bubble = $("#aiwb-chat-bubble");

      $window.addClass("aiwb-open");
      $bubble.hide();
      this.isOpen = true;
      this.isMinimized = false;

      // Focus input
      setTimeout(() => {
        $("#aiwb-message-input").focus();
      }, 300);

      // Scroll to bottom
      this.scrollToBottom();

      // Track event
      this.trackEvent("chat_opened");
    }

    closeChat() {
      const $window = $("#aiwb-chat-window");
      const $bubble = $("#aiwb-chat-bubble");

      $window.removeClass("aiwb-open");
      $bubble.show();
      this.isOpen = false;
      this.isMinimized = false;

      // Track event
      this.trackEvent("chat_closed");
    }

    minimizeChat() {
      const $window = $("#aiwb-chat-window");

      $window.removeClass("aiwb-open");
      this.isOpen = false;
      this.isMinimized = true;

      // Show notification on bubble
      this.showBubbleNotification();
    }

    sendMessage() {
      const $input = $("#aiwb-message-input");
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
      const $messages = $("#aiwb-chat-messages");
      const time = new Date().toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
      });

      const messageHtml = `
                <div class="aiwb-message aiwb-${sender}-message">
                    <div class="aiwb-message-avatar">
                        ${sender === "bot" ? this.getBotIcon() : "U"}
                    </div>
                    <div class="aiwb-message-content">
                        <div class="aiwb-message-text">${this.formatMessage(
                          text
                        )}</div>
                        <div class="aiwb-message-time">${time}</div>
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

    // Update the processMessage method in frontend.js:
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

          if (
            response &&
            response.success &&
            response.data &&
            response.data.message
          ) {
            this.addMessage(response.data.message, "bot");
            this.trackEvent("message_success");
          } else if (response && response.data) {
            // Handle error response from server
            const errorMessage =
              typeof response.data === "string"
                ? response.data
                : "Sorry, I encountered an error. Please try again.";
            this.addMessage(errorMessage, "bot");
            this.trackEvent("message_error", { error: "server_error" });
          } else {
            this.addMessage(
              "Sorry, I encountered an error. Please try again.",
              "bot"
            );
            this.trackEvent("message_error", { error: "invalid_response" });
          }
        },
        error: (xhr, status, error) => {
          this.hideTyping();
          let errorMessage =
            "Sorry, I'm having trouble connecting. Please try again.";

          if (status === "timeout") {
            errorMessage = "The response took too long. Please try again.";
          } else if (xhr.status === 400) {
            errorMessage =
              "There was an issue with your request. Please try again.";
          } else if (xhr.status === 500) {
            errorMessage = "Server error. Please try again later.";
          } else if (xhr.status === 0) {
            errorMessage =
              "Connection lost. Please check your internet and try again.";
          }

          this.addMessage(errorMessage, "bot");
          this.trackEvent("message_error", {
            error: error,
            status: status,
            xhr_status: xhr.status,
          });
        },
      });
    }

    showTyping() {
      this.isTyping = true;
      $("#aiwb-typing-indicator").addClass("aiwb-show");
      $("#aiwb-send-message").prop("disabled", true);
      this.scrollToBottom();
    }

    hideTyping() {
      this.isTyping = false;
      $("#aiwb-typing-indicator").removeClass("aiwb-show");
      $("#aiwb-send-message").prop("disabled", false);
    }

    hideQuickActions() {
      $("#aiwb-quick-actions").fadeOut();
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
        chat: '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22H2V12C2 6.48 6.48 2 12 2ZM12 4C7.58 4 4 7.58 4 12V20H12C16.42 20 20 16.42 20 12C20 7.58 16.42 4 12 4ZM8 10C8.55 10 9 10.45 9 11C9 11.55 8.55 12 8 12C7.45 12 7 11.55 7 11C7 10.45 7.45 10 8 10ZM12 10C12.55 10 13 10.45 13 11C13 11.55 12.55 12 12 12C11.45 12 11 11.55 11 11C11 10.45 11.45 10 12 10ZM16 10C16.55 10 17 10.45 17 11C17 11.55 16.55 12 16 12C15.45 12 15 11.55 15 11C15 10.45 15.45 10 16 10Z"/></svg>',

        robot:
          '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C12.55 2 13 2.45 13 3V4H16C17.1 4 18 4.9 18 6V8C19.66 8 21 9.34 21 11V16C21 17.66 19.66 19 18 19V20C18 21.1 17.1 22 16 22H8C6.9 22 6 21.1 6 20V19C4.34 19 3 17.66 3 16V11C3 9.34 4.34 8 6 8V6C6 4.9 6.9 4 8 4H11V3C11 2.45 11.45 2 12 2ZM8 6V18H16V6H8ZM10 9C10.55 9 11 9.45 11 10C11 10.55 10.55 11 10 11C9.45 11 9 10.55 9 10C9 9.45 9.45 9 10 9ZM14 9C14.55 9 15 9.45 15 10C15 10.55 14.55 11 14 11C13.45 11 13 10.55 13 10C13 9.45 13.45 9 14 9ZM9 13H15C15 14.66 13.66 16 12 16C10.34 16 9 14.66 9 13Z"/></svg>',

        assistant:
          '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L13.09 8.26L22 9L13.09 9.74L12 16L10.91 9.74L2 9L10.91 8.26L12 2ZM4 14L5 17L8 18L5 19L4 22L3 19L0 18L3 17L4 14ZM19 14L20 17L23 18L20 19L19 22L18 19L15 18L18 17L19 14Z"/></svg>',

        message:
          '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H6L2 24V6C2 4.9 2.9 4 4 4ZM4 6V19.17L5.17 18H20V6H4ZM6 8H18V10H6V8ZM6 12H16V14H6V12Z"/></svg>',

        help: '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2ZM12 4C7.58 4 4 7.58 4 12C4 16.42 7.58 20 12 20C16.42 20 20 16.42 20 12C20 7.58 16.42 4 12 4ZM12 6C13.38 6 14.5 7.12 14.5 8.5C14.5 9.88 13.38 11 12 11C11.45 11 11 11.45 11 12V13C11 13.55 11.45 14 12 14C12.55 14 13 13.55 13 13V12.5C14.83 12.24 16.24 10.54 16.24 8.5C16.24 6.15 14.35 4.26 12 4.26C9.65 4.26 7.76 6.15 7.76 8.5H9.5C9.5 7.12 10.62 6 12 6ZM12 16C11.45 16 11 16.45 11 17C11 17.55 11.45 18 12 18C12.55 18 13 17.55 13 17C13 16.45 12.55 16 12 16Z"/></svg>',

        support:
          '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L19 8L17 7V9C17 10.1 16.1 11 15 11V13L17 15V21H19V15L21 13V11C20.4 11 19.8 10.6 19.4 10.1L21 9ZM11 7H13V9C13 10.1 13.9 11 15 11H17V13H15C13.9 13 13 12.1 13 11V9H11V7ZM5 9V11C5 12.1 5.9 13 7 13H9V11H7C5.9 11 5 10.1 5 9ZM3 9L5 8L7 7V9C7 10.1 6.1 11 5 11V13L3 15V21H5V15L3 13V11C3.6 11 4.2 10.6 4.6 10.1L3 9Z"/></svg>',
      };

      return icons[settings.chatIcon] || icons.chat;
    }

    setupAutoResize() {
      const $input = $("#aiwb-message-input");

      $input.on("input", () => {
        this.resizeInput();
      });
    }

    resizeInput() {
      const $input = $("#aiwb-message-input");
      $input[0].style.height = "auto";
      $input[0].style.height = Math.min($input[0].scrollHeight, 120) + "px";
    }

    scrollToBottom() {
      const $messages = $("#aiwb-chat-messages");
      $messages.scrollTop($messages[0].scrollHeight);
    }

    getRecentHistory() {
      return this.messageHistory.slice(-10); // Last 10 messages for context
    }

    loadChatHistory() {
      try {
        const history = localStorage.getItem("aiwb_bot_history");
        if (history) {
          this.messageHistory = JSON.parse(history);
        }
      } catch (e) {
        console.log("Failed to load chat history:", e);
        this.messageHistory = [];
      }
    }

    saveChatHistory() {
      try {
        // Keep only last 50 messages
        if (this.messageHistory.length > 50) {
          this.messageHistory = this.messageHistory.slice(-50);
        }

        localStorage.setItem(
          "aiwb_bot_history",
          JSON.stringify(this.messageHistory)
        );
      } catch (e) {
        console.log("Failed to save chat history:", e);
      }
    }

    showBubbleNotification() {
      const $bubble = $("#aiwb-chat-bubble");
      $bubble.addClass("aiwb-has-notification");

      setTimeout(() => {
        $bubble.removeClass("aiwb-has-notification");
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

      // Send to WordPress (optional, don't fail if it doesn't work)
      try {
        $.post(aiBotAjax.ajaxurl, {
          action: "ai_bot_track_event",
          event: event,
          data: data,
          nonce: aiBotAjax.nonce,
        });
      } catch (e) {
        // Silently fail
      }
    }
  }

  // Initialize when DOM is ready
  $(document).ready(function () {
    if ($("#aiwb-chatbot-widget").length) {
      new AIWebsiteChatbot();
    }
  });
})(jQuery);
