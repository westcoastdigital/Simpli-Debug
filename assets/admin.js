jQuery(document).ready(function ($) {
  // ============================
  // DEBUG LOG FUNCTIONALITY
  // ============================

  // Copy code to clipboard
  $(".simpli-debug-copy-btn").on("click", function () {
    var $btn = $(this);
    var targetId = $btn.data("clipboard-target");
    var $target = $(targetId);

    // Create temporary textarea
    var $temp = $("<textarea>");
    $("body").append($temp);
    $temp.val($target.text()).select();
    document.execCommand("copy");
    $temp.remove();

    // Update button text
    var originalText = $btn.text();
    $btn.text("Copied!").addClass("copied");

    setTimeout(function () {
      $btn.text(originalText).removeClass("copied");
    }, 2000);
  });

  // Clear debug log
  $(".simpli-debug-clear-btn").on("click", function () {
    if (!confirm(simpliDebug.confirm_clear)) {
      return;
    }

    var $btn = $(this);

    $btn.prop("disabled", true).addClass("simpli-debug-loading");

    $.ajax({
      url: simpliDebug.ajax_url,
      type: "POST",
      data: {
        action: "simpli_debug_clear_log",
        nonce: simpliDebug.nonce,
      },
      success: function (response) {
        if (response.success) {
          location.reload();
        } else {
          alert(response.data.message || "Failed to clear log");
          $btn.prop("disabled", false).removeClass("simpli-debug-loading");
        }
      },
      error: function () {
        alert("An error occurred while clearing the log");
        $btn.prop("disabled", false).removeClass("simpli-debug-loading");
      },
    });
  });

  // Download debug log
  $(".simpli-debug-download-btn").on("click", function () {
    var downloadUrl =
      simpliDebug.ajax_url +
      "?action=simpli_debug_download_log&nonce=" +
      simpliDebug.nonce;

    window.location.href = downloadUrl;
  });

  // Refresh page
  $(".simpli-debug-refresh-btn").on("click", function () {
    location.reload();
  });

  // Enable alternative logging
  $(".simpli-enable-alternative-logging").on("click", function () {
    if (
      !confirm(
        "Enable alternative debug logging? This will create a log file at /wp-content/simpli-debug.log",
      )
    ) {
      return;
    }

    var $btn = $(this);
    $btn.prop("disabled", true).text("Enabling...");

    $.ajax({
      url: simpliDebug.ajax_url,
      type: "POST",
      data: {
        action: "simpli_enable_alternative_logging",
        nonce: simpliDebug.nonce,
      },
      success: function (response) {
        if (response.success) {
          location.reload();
        } else {
          alert(
            response.data.message || "Failed to enable alternative logging",
          );
          $btn.prop("disabled", false).text("Enable Alternative Debug Logging");
        }
      },
      error: function () {
        alert("An error occurred while enabling alternative logging");
        $btn.prop("disabled", false).text("Enable Alternative Debug Logging");
      },
    });
  });

  // Disable alternative logging
  $(".simpli-disable-alternative-logging").on("click", function () {
    if (
      !confirm(
        "Disable alternative debug logging? The log file will remain but new errors will not be logged.",
      )
    ) {
      return;
    }

    var $btn = $(this);
    $btn.prop("disabled", true).text("Disabling...");

    $.ajax({
      url: simpliDebug.ajax_url,
      type: "POST",
      data: {
        action: "simpli_disable_alternative_logging",
        nonce: simpliDebug.nonce,
      },
      success: function (response) {
        if (response.success) {
          location.reload();
        } else {
          alert(
            response.data.message || "Failed to disable alternative logging",
          );
          $btn.prop("disabled", false).text("Disable Alternative Logging");
        }
      },
      error: function () {
        alert("An error occurred while disabling alternative logging");
        $btn.prop("disabled", false).text("Disable Alternative Logging");
      },
    });
  });

  // Reset activity log (clear all entries)
  $(".simpli-reset-activity-log").on("click", function () {
    if (!confirm(simpliDebug.confirm_reset)) {
      return;
    }

    var $btn = $(this);
    $btn.prop("disabled", true);

    $.ajax({
      url: simpliDebug.ajax_url,
      type: "POST",
      data: {
        action: "simpli_reset_activity_logs",
        nonce: simpliDebug.nonce,
      },
      success: function (response) {
        if (response.success) {
          alert("Activity log has been reset successfully");
          location.reload();
        } else {
          alert(response.data.message || "Failed to reset activity log");
          $btn.prop("disabled", false);
        }
      },
      error: function () {
        alert("An error occurred while resetting the activity log");
        $btn.prop("disabled", false);
      },
    });
  });

  // ============================
  // ACTIVITY LOG FUNCTIONALITY
  // ============================

  if ($(".simpli-activity-wrap").length) {
    var currentPage = 1;
    var perPage = 50;
    var totalEntries = 0;

    // Load activity logs
    function loadActivityLogs() {
      var filters = {
        action: "simpli_get_activity_logs",
        nonce: simpliDebug.nonce,
        log_type: $("#filter-log-type").val(),
        action_filter: $("#filter-action").val(),
        post_type: $("#filter-post-type").val(),
        user_id: $("#filter-user").val(),
        date_from: $("#filter-date-from").val(),
        date_to: $("#filter-date-to").val(),
        per_page: perPage,
        offset: (currentPage - 1) * perPage,
      };

      var $tbody = $("#activity-log-tbody");
      $tbody.html(
        '<tr class="simpli-loading-row"><td colspan="7" class="simpli-loading"><span class="spinner is-active"></span> Loading activity log...</td></tr>',
      );

      $.ajax({
        url: simpliDebug.ajax_url,
        type: "POST",
        data: filters,
        success: function (response) {
          if (response.success) {
            totalEntries = response.data.total;
            renderActivityLogs(response.data.logs);
            updatePagination();
            updateStats();
          } else {
            $tbody.html(
              '<tr><td colspan="7" class="simpli-no-results">Error loading logs</td></tr>',
            );
          }
        },
        error: function () {
          $tbody.html(
            '<tr><td colspan="7" class="simpli-no-results">Error loading logs</td></tr>',
          );
        },
      });
    }

    // Render activity logs in table
    function renderActivityLogs(logs) {
      var $tbody = $("#activity-log-tbody");
      $tbody.empty();

      if (logs.length === 0) {
        $tbody.html(
          '<tr><td colspan="7" class="simpli-no-results">No activity logs found matching your filters.</td></tr>',
        );
        return;
      }

      $.each(logs, function (index, log) {
        var $row = $("<tr>");

        // Date/Time
        var date = new Date(log.created_at);
        var dateStr = date.toLocaleString();
        $row.append("<td>" + dateStr + "</td>");

        // Type
        var typeClass = "type-" + log.log_type;
        var typeLabel =
          log.log_type.charAt(0).toUpperCase() + log.log_type.slice(1);
        $row.append(
          '<td><span class="simpli-log-type ' +
            typeClass +
            '">' +
            typeLabel +
            "</span></td>",
        );

        // Action
        var actionClass = "action-" + log.action;
        var actionLabel =
          log.action.charAt(0).toUpperCase() + log.action.slice(1);
        $row.append(
          '<td><span class="simpli-action ' +
            actionClass +
            '">' +
            actionLabel +
            "</span></td>",
        );

        // Object
        var objectHtml = log.object_title || "-";
        if (log.log_type === "post" && log.object_id) {
          objectHtml =
            '<a href="post.php?post=' +
            log.object_id +
            '&action=edit" target="_blank">' +
            (log.object_title || "Post #" + log.object_id) +
            "</a>";
        }
        $row.append("<td>" + objectHtml + "</td>");

        // Post Type
        $row.append("<td>" + (log.post_type || "-") + "</td>");

        // User
        var userHtml = log.user_name || "-";
        if (log.user_id && log.user_id > 0) {
          userHtml =
            '<a href="user-edit.php?user_id=' +
            log.user_id +
            '" target="_blank">' +
            log.user_name +
            "</a>";
        }
        $row.append("<td>" + userHtml + "</td>");

        // Details
        var details = buildDetailsHtml(log);
        $row.append('<td class="simpli-details">' + details + "</td>");

        $tbody.append($row);
      });
    }

    // Build details HTML
    function buildDetailsHtml(log) {
      var html = "";

      if (log.old_value && log.new_value && log.old_value !== log.new_value) {
        html += '<div class="simpli-details-item">';
        html +=
          '<span class="simpli-details-change">' +
          log.old_value +
          " → " +
          log.new_value +
          "</span>";
        html += "</div>";
      } else if (log.new_value) {
        html += '<div class="simpli-details-item">';
        html += '<span class="simpli-details-label">Version:</span> ';
        html +=
          '<span class="simpli-details-value">' + log.new_value + "</span>";
        html += "</div>";
      }

      if (log.additional_data) {
        try {
          var additional =
            typeof log.additional_data === "string"
              ? JSON.parse(log.additional_data)
              : log.additional_data;

          if (
            additional.title &&
            additional.title.old &&
            additional.title.new
          ) {
            html += '<div class="simpli-details-item">';
            html += '<span class="simpli-details-label">Title changed</span>';
            html += "</div>";
          }

          if (additional.content) {
            html += '<div class="simpli-details-item">';
            html +=
              '<span class="simpli-details-label">Content modified</span>';
            html += "</div>";
          }
        } catch (e) {
          // Invalid JSON, skip
        }
      }

      return html || "-";
    }

    // Update pagination
    function updatePagination() {
      var totalPages = Math.ceil(totalEntries / perPage);

      $("#current-page").text(currentPage);
      $("#total-pages").text(totalPages);

      $(".simpli-prev-page").prop("disabled", currentPage <= 1);
      $(".simpli-next-page").prop("disabled", currentPage >= totalPages);
    }

    // Update stats
    function updateStats() {
      $("#total-entries").text(totalEntries);

      var start = (currentPage - 1) * perPage + 1;
      var end = Math.min(currentPage * perPage, totalEntries);

      if (totalEntries === 0) {
        $("#showing-entries").text("0");
      } else {
        $("#showing-entries").text(start + "-" + end);
      }
    }

    // Apply filters
    $(".simpli-apply-filters").on("click", function () {
      currentPage = 1;
      perPage = parseInt($("#filter-per-page").val());
      loadActivityLogs();
    });

    // Reset filters
    $(".simpli-reset-filters").on("click", function () {
      $(".simpli-filter").val("");
      $("#filter-per-page").val("50");
      currentPage = 1;
      perPage = 50;
      loadActivityLogs();
    });

    // Export logs
    $(".simpli-export-logs").on("click", function () {
      var params = {
        action: "simpli_export_activity_logs",
        nonce: simpliDebug.nonce,
        log_type: $("#filter-log-type").val(),
        action_filter: $("#filter-action").val(),
        post_type: $("#filter-post-type").val(),
        user_id: $("#filter-user").val(),
        date_from: $("#filter-date-from").val(),
        date_to: $("#filter-date-to").val(),
      };

      var queryString = $.param(params);
      window.location.href = simpliDebug.ajax_url + "?" + queryString;
    });

    // Pagination
    $(".simpli-prev-page").on("click", function () {
      if (currentPage > 1) {
        currentPage--;
        loadActivityLogs();
        $("html, body").animate({ scrollTop: 0 }, 300);
      }
    });

    $(".simpli-next-page").on("click", function () {
      var totalPages = Math.ceil(totalEntries / perPage);
      if (currentPage < totalPages) {
        currentPage++;
        loadActivityLogs();
        $("html, body").animate({ scrollTop: 0 }, 300);
      }
    });

    // Apply filters on Enter key
    $(".simpli-filter").on("keypress", function (e) {
      if (e.which === 13) {
        $(".simpli-apply-filters").click();
      }
    });

    // Initial load
    loadActivityLogs();
  }
});
