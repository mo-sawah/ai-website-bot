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
        chat: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2ZM20 16H5.17L4 17.17V4H20V16Z"/></svg>',
        robot:
          '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7H20C19.4 7 19 6.6 19 6C19 5.4 19.4 5 20 5H21V3C21 1.9 20.1 1 19 1H5C3.9 1 3 1.9 3 3V5H4C4.6 5 5 5.4 5 6C5 6.6 4.6 7 4 7H3V9C3 10.1 3.9 11 5 11H8V13H7C6.4 13 6 13.4 6 14V20C6 20.6 6.4 21 7 21H17C17.6 21 18 20.6 18 20V14C18 13.4 17.6 13 17 13H16V11H19C20.1 11 21 10.1 21 9Z"/></svg>',
        help: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 19H11V17H13V19ZM15.07 11.25L14.17 12.17C13.45 12.9 13 13.5 13 15H11V14.5C11 13.4 11.45 12.4 12.17 11.67L13.41 10.41C13.78 10.05 14 9.55 14 9C14 7.9 13.1 7 12 7C10.9 7 10 7.9 10 9H8C8 6.79 9.79 5 12 5C14.21 5 16 6.79 16 9C16 9.88 15.64 10.68 15.07 11.25Z"/></svg>',
        support:
          '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1C18.08 1 23 5.92 23 12C23 18.08 18.08 23 12 23C10.5 23 9.04 22.65 7.74 22L1 23L2.73 17.74C2.05 16.44 1.67 14.97 1.67 13.44C1.67 7.36 6.59 2.44 12.67 2.44H12C12 1 12 1 12 1ZM12 3C7.04 3 3 7.04 3 12C3 13.54 3.5 14.94 4.36 16.09L3.5 19.5L7.09 18.64C8.24 19.5 9.64 20 11.18 20H12C16.96 20 21 15.96 21 11C21 6.04 16.96 2 12 2V3Z"/></svg>',
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
