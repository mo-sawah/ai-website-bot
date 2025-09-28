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
        chat: '<i class="fas fa-comments"></i>',
        robot: '<i class="fas fa-robot"></i>',
        help: '<i class="fas fa-question-circle"></i>',
        support: '<i class="fas fa-headset"></i>',
        message: '<i class="fas fa-envelope"></i>',
        assistant: '<i class="fas fa-magic"></i>',
        brain: '<i class="fas fa-brain"></i>',
        sparkles: '<i class="fas fa-sparkles"></i>',
      };

      $("#preview-icon").html(icons[iconType] || icons.robot);

      // Also update the admin preview if it exists
      if ($("#icon-preview-display").length) {
        $("#icon-preview-display").html(icons[iconType] || icons.robot);
      }
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

  function updateIconPreview(iconType) {
    const icons = {
      chat: '<i class="fas fa-comments"></i>',
      robot: '<i class="fas fa-robot"></i>',
      help: '<i class="fas fa-question-circle"></i>',
      support: '<i class="fas fa-headset"></i>',
      message: '<i class="fas fa-envelope"></i>',
      assistant: '<i class="fas fa-magic"></i>',
      brain: '<i class="fas fa-brain"></i>',
      sparkles: '<i class="fas fa-sparkles"></i>',
    };

    $("#icon-preview-display").html(icons[iconType] || icons.robot);
  }

  // Initialize when DOM is ready
  $(document).ready(function () {
    new AIChatbotAdmin();
  });
})(jQuery);
