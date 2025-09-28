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
        chat: '<i class="fa-solid fa-comments"></i>',
        robot: '<i class="fa-solid fa-robot"></i>',
        help: '<i class="fa-solid fa-circle-question"></i>',
        support: '<i class="fa-solid fa-headset"></i>',
        message: '<i class="fa-solid fa-envelope"></i>',
        assistant: '<i class="fa-solid fa-wand-magic-sparkles"></i>',
        brain: '<i class="fa-solid fa-brain"></i>',
        sparkles: '<i class="fa-solid fa-sparkles"></i>',
      };

      return icons[settings.chatIcon] || icons.robot;
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
