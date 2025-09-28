(function ($) {
  ("use strict");

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
          '<svg width="24" height="24" viewBox="0 0 640 512" fill="currentColor"><path d="M352 64C352 46.3 337.7 32 320 32C302.3 32 288 46.3 288 64L288 128L192 128C139 128 96 171 96 224L96 448C96 501 139 544 192 544L448 544C501 544 544 501 544 448L544 224C544 171 501 128 448 128L352 128L352 64zM160 432C160 418.7 170.7 408 184 408L216 408C229.3 408 240 418.7 240 432C240 445.3 229.3 456 216 456L184 456C170.7 456 160 445.3 160 432zM280 432C280 418.7 290.7 408 304 408L336 408C349.3 408 360 418.7 360 432C360 445.3 349.3 456 336 456L304 456C290.7 456 280 445.3 280 432zM400 432C400 418.7 410.7 408 424 408L456 408C469.3 408 480 418.7 480 432C480 445.3 469.3 456 456 456L424 456C410.7 456 400 445.3 400 432zM224 240C250.5 240 272 261.5 272 288C272 314.5 250.5 336 224 336C197.5 336 176 314.5 176 288C176 261.5 197.5 240 224 240zM368 288C368 261.5 389.5 240 416 240C442.5 240 464 261.5 464 288C464 314.5 442.5 336 416 336C389.5 336 368 314.5 368 288z"/></svg>',
        help: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,17A1.5,1.5 0 0,1 10.5,15.5A1.5,1.5 0 0,1 12,14A1.5,1.5 0 0,1 13.5,15.5A1.5,1.5 0 0,1 12,17M12,10.5C10.07,10.5 8.5,8.93 8.5,7C8.5,5.07 10.07,3.5 12,3.5C13.93,3.5 15.5,5.07 15.5,7C15.5,8.93 13.93,10.5 12,10.5M12,9A2,2 0 0,0 14,7A2,2 0 0,0 12,5A2,2 0 0,0 10,7A2,2 0 0,0 12,9Z"/></svg>',
        support:
          '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12,1C12,1 12,1 12,1C7.03,1 3,5.03 3,10V11.5C3,12.33 3.67,13 4.5,13H5V10C5,6.13 8.13,3 12,3C15.87,3 19,6.13 19,10V13H19.5C20.33,13 21,12.33 21,11.5V10C21,5.03 16.97,1 12,1M7.5,14C6.67,14 6,14.67 6,15.5V20.5C6,21.33 6.67,22 7.5,22H8.5C9.33,22 10,21.33 10,20.5V15.5C10,14.67 9.33,14 8.5,14H7.5M15.5,14C14.67,14 14,14.67 14,15.5V20.5C14,21.33 14.67,22 15.5,22H16.5C17.33,22 18,21.33 18,20.5V15.5C18,14.67 17.33,14 16.5,14H15.5Z"/></svg>',
        message:
          '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.11,4 20,4Z"/></svg>',
        assistant:
          '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12,2A7,7 0 0,1 19,9C19,11.38 17.81,13.47 16,14.74V17A1,1 0 0,1 15,18H9A1,1 0 0,1 8,17V14.74C6.19,13.47 5,11.38 5,9A7,7 0 0,1 12,2M9,21V20H15V21A1,1 0 0,1 14,22H10A1,1 0 0,1 9,21M12,4A5,5 0 0,0 7,9C7,11.05 8.23,12.81 10,13.58V16H14V13.58C15.77,12.81 17,11.05 17,9A5,5 0 0,0 12,4Z"/></svg>',
        brain:
          '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M21.33,12.91C21.42,14.46 20.71,15.95 19.44,16.86L20.21,18.35C20.44,18.8 20.47,19.33 20.27,19.8C20.08,20.27 19.69,20.64 19.21,20.8L18.42,21.05C18.25,21.11 18.06,21.14 17.88,21.14C17.37,21.14 16.89,20.91 16.56,20.5L14.44,18C13.55,17.85 12.8,17.7 12,17.7C11.2,17.7 10.45,17.85 9.56,18L7.44,20.5C7.11,20.91 6.63,21.14 6.12,21.14C5.94,21.14 5.75,21.11 5.58,21.05L4.79,20.8C4.31,20.64 3.92,20.27 3.73,19.8C3.53,19.33 3.56,18.8 3.79,18.35L4.56,16.86C3.29,15.95 2.58,14.46 2.67,12.91C2.75,11.35 3.63,9.96 5.06,9.18C5.1,9.15 5.15,9.12 5.2,9.1C5.15,8.8 5.12,8.5 5.12,8.18C5.12,5.26 7.5,2.83 10.5,2.83C12.94,2.83 15.04,4.77 15.5,7.31C16.83,7.86 17.83,9.26 17.88,10.83C19.31,11.61 20.19,13 20.27,14.56L21.33,12.91M17.5,9.5C17.5,8.67 16.83,8 16,8C15.17,8 14.5,8.67 14.5,9.5C14.5,10.33 15.17,11 16,11C16.83,11 17.5,10.33 17.5,9.5M12,15C13.66,15 15,13.66 15,12C15,10.34 13.66,9 12,9C10.34,9 9,10.34 9,12C9,13.66 10.34,15 12,15M9.5,9.5C9.5,8.67 8.83,8 8,8C7.17,8 6.5,8.67 6.5,9.5C6.5,10.33 7.17,11 8,11C8.83,11 9.5,10.33 9.5,9.5Z"/></svg>',
        sparkles:
          '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12,1L15.09,8.26L22,9L17,14.74L18.18,21.02L12,17.77L5.82,21.02L7,14.74L2,9L8.91,8.26L12,1M12,6.5L10.25,10.06L6.5,10.5L9.5,13.4L8.77,17.09L12,15.4L15.23,17.09L14.5,13.4L17.5,10.5L13.75,10.06L12,6.5Z"/></svg>',
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

  // Add this function for the admin preview
  // Replace the global updateIconPreview function:
  function updateIconPreview(iconType) {
    const icons = {
      chat: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2ZM20 16H5.17L4 17.17V4H20V16Z"/></svg>',
      robot:
        '<svg width="24" height="24" viewBox="0 0 640 512" fill="currentColor"><path d="M352 64C352 46.3 337.7 32 320 32C302.3 32 288 46.3 288 64L288 128L192 128C139 128 96 171 96 224L96 448C96 501 139 544 192 544L448 544C501 544 544 501 544 448L544 224C544 171 501 128 448 128L352 128L352 64zM160 432C160 418.7 170.7 408 184 408L216 408C229.3 408 240 418.7 240 432C240 445.3 229.3 456 216 456L184 456C170.7 456 160 445.3 160 432zM280 432C280 418.7 290.7 408 304 408L336 408C349.3 408 360 418.7 360 432C360 445.3 349.3 456 336 456L304 456C290.7 456 280 445.3 280 432zM400 432C400 418.7 410.7 408 424 408L456 408C469.3 408 480 418.7 480 432C480 445.3 469.3 456 456 456L424 456C410.7 456 400 445.3 400 432zM224 240C250.5 240 272 261.5 272 288C272 314.5 250.5 336 224 336C197.5 336 176 314.5 176 288C176 261.5 197.5 240 224 240zM368 288C368 261.5 389.5 240 416 240C442.5 240 464 261.5 464 288C464 314.5 442.5 336 416 336C389.5 336 368 314.5 368 288z"/></svg>',
      help: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,17A1.5,1.5 0 0,1 10.5,15.5A1.5,1.5 0 0,1 12,14A1.5,1.5 0 0,1 13.5,15.5A1.5,1.5 0 0,1 12,17M12,10.5C10.07,10.5 8.5,8.93 8.5,7C8.5,5.07 10.07,3.5 12,3.5C13.93,3.5 15.5,5.07 15.5,7C15.5,8.93 13.93,10.5 12,10.5M12,9A2,2 0 0,0 14,7A2,2 0 0,0 12,5A2,2 0 0,0 10,7A2,2 0 0,0 12,9Z"/></svg>',
      support:
        '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12,1C12,1 12,1 12,1C7.03,1 3,5.03 3,10V11.5C3,12.33 3.67,13 4.5,13H5V10C5,6.13 8.13,3 12,3C15.87,3 19,6.13 19,10V13H19.5C20.33,13 21,12.33 21,11.5V10C21,5.03 16.97,1 12,1M7.5,14C6.67,14 6,14.67 6,15.5V20.5C6,21.33 6.67,22 7.5,22H8.5C9.33,22 10,21.33 10,20.5V15.5C10,14.67 9.33,14 8.5,14H7.5M15.5,14C14.67,14 14,14.67 14,15.5V20.5C14,21.33 14.67,22 15.5,22H16.5C17.33,22 18,21.33 18,20.5V15.5C18,14.67 17.33,14 16.5,14H15.5Z"/></svg>',
      message:
        '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.11,4 20,4Z"/></svg>',
      assistant:
        '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12,2A7,7 0 0,1 19,9C19,11.38 17.81,13.47 16,14.74V17A1,1 0 0,1 15,18H9A1,1 0 0,1 8,17V14.74C6.19,13.47 5,11.38 5,9A7,7 0 0,1 12,2M9,21V20H15V21A1,1 0 0,1 14,22H10A1,1 0 0,1 9,21M12,4A5,5 0 0,0 7,9C7,11.05 8.23,12.81 10,13.58V16H14V13.58C15.77,12.81 17,11.05 17,9A5,5 0 0,0 12,4Z"/></svg>',
      brain:
        '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M21.33,12.91C21.42,14.46 20.71,15.95 19.44,16.86L20.21,18.35C20.44,18.8 20.47,19.33 20.27,19.8C20.08,20.27 19.69,20.64 19.21,20.8L18.42,21.05C18.25,21.11 18.06,21.14 17.88,21.14C17.37,21.14 16.89,20.91 16.56,20.5L14.44,18C13.55,17.85 12.8,17.7 12,17.7C11.2,17.7 10.45,17.85 9.56,18L7.44,20.5C7.11,20.91 6.63,21.14 6.12,21.14C5.94,21.14 5.75,21.11 5.58,21.05L4.79,20.8C4.31,20.64 3.92,20.27 3.73,19.8C3.53,19.33 3.56,18.8 3.79,18.35L4.56,16.86C3.29,15.95 2.58,14.46 2.67,12.91C2.75,11.35 3.63,9.96 5.06,9.18C5.1,9.15 5.15,9.12 5.2,9.1C5.15,8.8 5.12,8.5 5.12,8.18C5.12,5.26 7.5,2.83 10.5,2.83C12.94,2.83 15.04,4.77 15.5,7.31C16.83,7.86 17.83,9.26 17.88,10.83C19.31,11.61 20.19,13 20.27,14.56L21.33,12.91M17.5,9.5C17.5,8.67 16.83,8 16,8C15.17,8 14.5,8.67 14.5,9.5C14.5,10.33 15.17,11 16,11C16.83,11 17.5,10.33 17.5,9.5M12,15C13.66,15 15,13.66 15,12C15,10.34 13.66,9 12,9C10.34,9 9,10.34 9,12C9,13.66 10.34,15 12,15M9.5,9.5C9.5,8.67 8.83,8 8,8C7.17,8 6.5,8.67 6.5,9.5C6.5,10.33 7.17,11 8,11C8.83,11 9.5,10.33 9.5,9.5Z"/></svg>',
      sparkles:
        '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12,1L15.09,8.26L22,9L17,14.74L18.18,21.02L12,17.77L5.82,21.02L7,14.74L2,9L8.91,8.26L12,1M12,6.5L10.25,10.06L6.5,10.5L9.5,13.4L8.77,17.09L12,15.4L15.23,17.09L14.5,13.4L17.5,10.5L13.75,10.06L12,6.5Z"/></svg>',
    };

    $("#icon-preview-display").html(icons[iconType] || icons.robot);
  }

  // Make it globally accessible
  window.updateIconPreview = updateIconPreview;

  // Initialize when DOM is ready
  $(document).ready(function () {
    new AIChatbotAdmin();
  });
})(jQuery);
