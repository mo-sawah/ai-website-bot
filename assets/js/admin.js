(function ($) {
  "use strict";

  class AIChatbotAdmin {
    constructor() {
      this.currentTab = "general";
      this.settings = {};
      this.isDirty = false;

      this.init();
    }

    init() {
      this.bindEvents();
      this.loadSettings();
      this.initPreview();
      this.setupFormValidation();
    }

    bindEvents() {
      // Tab switching
      $(".tab-button").on("click", (e) => {
        const tab = $(e.currentTarget).data("tab");
        this.switchTab(tab);
      });

      // Settings changes
      $(".setting-input, .setting-textarea").on("input change", () => {
        this.markDirty();
        this.updatePreview();
      });

      // Toggle switches
      $(".toggle-switch input").on("change", (e) => {
        this.markDirty();
        this.updateToggleLabel($(e.target));
        this.updatePreview();
      });

      // Save settings
      $("#save-settings").on("click", () => {
        this.saveSettings();
      });

      // Reset settings
      $("#reset-settings").on("click", () => {
        this.resetSettings();
      });

      // Test API
      $("#test-api").on("click", () => {
        this.testAPI();
      });

      // Color picker changes
      $('input[type="color"]').on("change", () => {
        this.markDirty();
        this.updatePreview();
      });

      // Warn about unsaved changes
      window.addEventListener("beforeunload", (e) => {
        if (this.isDirty) {
          e.returnValue =
            "You have unsaved changes. Are you sure you want to leave?";
          return e.returnValue;
        }
      });
    }

    switchTab(tabName) {
      // Update buttons
      $(".tab-button").removeClass("active");
      $(`.tab-button[data-tab="${tabName}"]`).addClass("active");

      // Update panels
      $(".tab-panel").removeClass("active");
      $(`#${tabName}`).addClass("active");

      this.currentTab = tabName;
    }

    loadSettings() {
      // Settings are already loaded via PHP, just update preview
      this.updatePreview();
    }

    saveSettings() {
      const $saveBtn = $("#save-settings");
      const originalText = $saveBtn.html();

      // Show loading state
      $saveBtn
        .html(
          '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="animate-spin"><path d="M12,4V2A10,10 0 0,0 2,12H4A8,8 0 0,1 12,4Z"/></svg> Saving...'
        )
        .prop("disabled", true);

      // Collect all settings
      const settings = this.collectSettings();

      // Send AJAX request
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "save_ai_bot_settings",
          settings: settings,
          nonce: $("#ai-bot-nonce").val(),
        },
        success: (response) => {
          if (response.success) {
            this.showNotice("Settings saved successfully!", "success");
            this.isDirty = false;
          } else {
            this.showNotice("Error saving settings: " + response.data, "error");
          }
        },
        error: () => {
          this.showNotice("Connection error. Please try again.", "error");
        },
        complete: () => {
          $saveBtn.html(originalText).prop("disabled", false);
        },
      });
    }

    collectSettings() {
      const settings = {};

      // Collect all form inputs
      $(".setting-input, .setting-textarea").each(function () {
        const $input = $(this);
        const name = $input.attr("name");

        if (name) {
          if ($input.attr("type") === "checkbox") {
            settings[name] = $input.is(":checked");
          } else {
            settings[name] = $input.val();
          }
        }
      });

      // Collect toggle switches
      $(".toggle-switch input").each(function () {
        const $input = $(this);
        const name = $input.attr("name");

        if (name) {
          settings[name] = $input.is(":checked");
        }
      });

      return settings;
    }

    resetSettings() {
      if (
        confirm(
          "Are you sure you want to reset all settings to defaults? This cannot be undone."
        )
      ) {
        // Reset form to defaults
        location.reload();
      }
    }

    testAPI() {
      const $testBtn = $("#test-api");
      const $status = $("#api-status");
      const apiKey = $('input[name="openrouter_api_key"]').val();
      const model = $('input[name="ai_model"]').val();

      if (!apiKey.trim()) {
        $status
          .removeClass("success error loading")
          .addClass("error")
          .text("Please enter an API key first.")
          .show();
        return;
      }

      if (!model.trim()) {
        $status
          .removeClass("success error loading")
          .addClass("error")
          .text("Please enter a model name first.")
          .show();
        return;
      }

      // Show loading state
      $testBtn.prop("disabled", true);
      $status
        .removeClass("success error")
        .addClass("loading")
        .text("Testing connection...")
        .show();

      // Test API call
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "test_ai_bot_api",
          api_key: apiKey,
          model: model,
          nonce: $("#ai-bot-nonce").val(),
        },
        timeout: 15000,
        success: (response) => {
          if (response.success) {
            $status
              .removeClass("loading error")
              .addClass("success")
              .text("✓ API connection successful!");
          } else {
            $status
              .removeClass("loading success")
              .addClass("error")
              .text("✗ Error: " + response.data);
          }
        },
        error: (xhr, status, error) => {
          let errorMessage = "Connection failed";

          if (status === "timeout") {
            errorMessage = "Request timed out";
          } else if (xhr.status === 401) {
            errorMessage = "Invalid API key";
          } else if (xhr.status === 429) {
            errorMessage = "Rate limit exceeded";
          }

          $status
            .removeClass("loading success")
            .addClass("error")
            .text("✗ " + errorMessage);
        },
        complete: () => {
          $testBtn.prop("disabled", false);

          // Hide status after 5 seconds
          setTimeout(() => {
            $status.fadeOut();
          }, 5000);
        },
      });
    }

    updatePreview() {
      // Update preview based on current settings
      const botName = $('input[name="bot_name"]').val() || "AI Assistant";
      const primaryColor = $('input[name="primary_color"]').val() || "#2563eb";
      const welcomeMessage =
        $('textarea[name="welcome_message"]').val() ||
        "Hi! How can I help you today?";
      const chatIcon = $('select[name="chat_icon"]').val() || "chat";

      // Update preview elements
      $("#preview-bot-name").text(botName);
      $("#preview-welcome").text(welcomeMessage);

      // Update preview bubble color
      $(".preview-bubble").css(
        "background",
        `linear-gradient(135deg, ${primaryColor} 0%, ${this.darkenColor(
          primaryColor,
          0.1
        )} 100%)`
      );
      $(".preview-header").css(
        "background",
        `linear-gradient(135deg, ${primaryColor} 0%, ${this.darkenColor(
          primaryColor,
          0.1
        )} 100%)`
      );

      // Update icon
      this.updatePreviewIcon(chatIcon);

      // Update CSS variables for live preview
      $(":root").css("--ai-bot-primary", primaryColor);
    }

    updatePreviewIcon(iconType) {
      const icons = {
        chat: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22H2V12C2 6.48 6.48 2 12 2ZM12 4C7.58 4 4 7.58 4 12V20H12C16.42 20 20 16.42 20 12C20 7.58 16.42 4 12 4ZM8 10C8.55 10 9 10.45 9 11C9 11.55 8.55 12 8 12C7.45 12 7 11.55 7 11C7 10.45 7.45 10 8 10ZM12 10C12.55 10 13 10.45 13 11C13 11.55 12.55 12 12 12C11.45 12 11 11.55 11 11C11 10.45 11.45 10 12 10ZM16 10C16.55 10 17 10.45 17 11C17 11.55 16.55 12 16 12C15.45 12 15 11.55 15 11C15 10.45 15.45 10 16 10Z"/></svg>',

        robot:
          '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C12.55 2 13 2.45 13 3V4H16C17.1 4 18 4.9 18 6V8C19.66 8 21 9.34 21 11V16C21 17.66 19.66 19 18 19V20C18 21.1 17.1 22 16 22H8C6.9 22 6 21.1 6 20V19C4.34 19 3 17.66 3 16V11C3 9.34 4.34 8 6 8V6C6 4.9 6.9 4 8 4H11V3C11 2.45 11.45 2 12 2ZM8 6V18H16V6H8ZM10 9C10.55 9 11 9.45 11 10C11 10.55 10.55 11 10 11C9.45 11 9 10.55 9 10C9 9.45 9.45 9 10 9ZM14 9C14.55 9 15 9.45 15 10C15 10.55 14.55 11 14 11C13.45 11 13 10.55 13 10C13 9.45 13.45 9 14 9ZM9 13H15C15 14.66 13.66 16 12 16C10.34 16 9 14.66 9 13Z"/></svg>',

        assistant:
          '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L13.09 8.26L22 9L13.09 9.74L12 16L10.91 9.74L2 9L10.91 8.26L12 2ZM4 14L5 17L8 18L5 19L4 22L3 19L0 18L3 17L4 14ZM19 14L20 17L23 18L20 19L19 22L18 19L15 18L18 17L19 14Z"/></svg>',

        message:
          '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H6L2 24V6C2 4.9 2.9 4 4 4ZM4 6V19.17L5.17 18H20V6H4ZM6 8H18V10H6V8ZM6 12H16V14H6V12Z"/></svg>',

        help: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2ZM12 4C7.58 4 4 7.58 4 12C4 16.42 7.58 20 12 20C16.42 20 20 16.42 20 12C20 7.58 16.42 4 12 4ZM12 6C13.38 6 14.5 7.12 14.5 8.5C14.5 9.88 13.38 11 12 11C11.45 11 11 11.45 11 12V13C11 13.55 11.45 14 12 14C12.55 14 13 13.55 13 13V12.5C14.83 12.24 16.24 10.54 16.24 8.5C16.24 6.15 14.35 4.26 12 4.26C9.65 4.26 7.76 6.15 7.76 8.5H9.5C9.5 7.12 10.62 6 12 6ZM12 16C11.45 16 11 16.45 11 17C11 17.55 11.45 18 12 18C12.55 18 13 17.55 13 17C13 16.45 12.55 16 12 16Z"/></svg>',

        support:
          '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L19 8L17 7V9C17 10.1 16.1 11 15 11V13L17 15V21H19V15L21 13V11C20.4 11 19.8 10.6 19.4 10.1L21 9ZM11 7H13V9C13 10.1 13.9 11 15 11H17V13H15C13.9 13 13 12.1 13 11V9H11V7ZM5 9V11C5 12.1 5.9 13 7 13H9V11H7C5.9 11 5 10.1 5 9ZM3 9L5 8L7 7V9C7 10.1 6.1 11 5 11V13L3 15V21H5V15L3 13V11C3.6 11 4.2 10.6 4.6 10.1L3 9Z"/></svg>',
      };

      $("#preview-icon").html(icons[iconType] || icons.chat);
    }

    updateToggleLabel($input) {
      const $label = $input.closest(".toggle-container").find(".toggle-label");

      if ($input.is(":checked")) {
        $label.text($label.text().replace("Disabled", "Enabled"));
      } else {
        $label.text($label.text().replace("Enabled", "Disabled"));
      }
    }

    initPreview() {
      // Make preview bubble clickable
      $(".preview-bubble").on("click", function () {
        $(this).addClass("clicked");
        setTimeout(() => {
          $(this).removeClass("clicked");
        }, 200);
      });
    }

    setupFormValidation() {
      // API Key validation
      $('input[name="openrouter_api_key"]').on("blur", function () {
        const value = $(this).val();

        if (value && !value.startsWith("sk-or-")) {
          $(this).addClass("invalid");
          $(this).after(
            '<div class="validation-error">API key should start with "sk-or-"</div>'
          );
        } else {
          $(this).removeClass("invalid");
          $(this).siblings(".validation-error").remove();
        }
      });

      // Model name validation
      $('input[name="ai_model"]').on("blur", function () {
        const value = $(this).val();

        if (value && !value.includes("/")) {
          $(this).addClass("invalid");
          $(this).after(
            '<div class="validation-error">Model name should be in format "provider/model-name"</div>'
          );
        } else {
          $(this).removeClass("invalid");
          $(this).siblings(".validation-error").remove();
        }
      });
    }
    markDirty() {
      this.isDirty = true;

      // Add visual indicator
      if (!$(".unsaved-indicator").length) {
        $(".settings-footer").prepend(
          '<div class="unsaved-indicator">You have unsaved changes</div>'
        );
      }
    }

    showNotice(message, type = "success") {
      const $notice = $(
        `<div class="notice notice-${type}"><p>${message}</p></div>`
      );

      $(".settings-main").prepend($notice);

      // Auto-remove after 5 seconds
      setTimeout(() => {
        $notice.fadeOut(() => {
          $notice.remove();
        });
      }, 5000);

      // Clear dirty state on success
      if (type === "success") {
        this.isDirty = false;
        $(".unsaved-indicator").remove();
      }
    }

    darkenColor(color, factor) {
      // Convert hex to RGB
      const hex = color.replace("#", "");
      const r = parseInt(hex.substr(0, 2), 16);
      const g = parseInt(hex.substr(2, 2), 16);
      const b = parseInt(hex.substr(4, 2), 16);

      // Darken
      const newR = Math.round(r * (1 - factor));
      const newG = Math.round(g * (1 - factor));
      const newB = Math.round(b * (1 - factor));

      // Convert back to hex
      return `#${newR.toString(16).padStart(2, "0")}${newG
        .toString(16)
        .padStart(2, "0")}${newB.toString(16).padStart(2, "0")}`;
    }

    lightenColor(color, factor) {
      // Convert hex to RGB
      const hex = color.replace("#", "");
      const r = parseInt(hex.substr(0, 2), 16);
      const g = parseInt(hex.substr(2, 2), 16);
      const b = parseInt(hex.substr(4, 2), 16);

      // Lighten
      const newR = Math.round(r + (255 - r) * factor);
      const newG = Math.round(g + (255 - g) * factor);
      const newB = Math.round(b + (255 - b) * factor);

      // Convert back to hex
      return `#${newR.toString(16).padStart(2, "0")}${newG
        .toString(16)
        .padStart(2, "0")}${newB.toString(16).padStart(2, "0")}`;
    }
  }

  // Initialize when DOM is ready
  $(document).ready(function () {
    new AIChatbotAdmin();
  });
})(jQuery);
