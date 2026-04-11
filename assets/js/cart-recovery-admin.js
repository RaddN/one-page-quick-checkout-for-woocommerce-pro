document.addEventListener("DOMContentLoaded", function () {
  const modal = document.querySelector(".onepaqucpro-cr-modal");
  const modalContent = modal ? modal.querySelector(".onepaqucpro-cr-modal__content") : null;
  const body = document.body;

  function formatChartValue(value, format) {
    if (format === "currency") {
      return "$" + Number(value || 0).toFixed(2);
    }

    return String(Number(value || 0));
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function getNiceMax(maxValue) {
    if (maxValue <= 1) {
      return 1;
    }

    const magnitude = 10 ** Math.floor(Math.log10(maxValue));
    const normalized = maxValue / magnitude;

    if (normalized <= 1) {
      return magnitude;
    }
    if (normalized <= 2) {
      return 2 * magnitude;
    }
    if (normalized <= 5) {
      return 5 * magnitude;
    }

    return 10 * magnitude;
  }

  function bindChartTooltip(container) {
    const tooltip = container.querySelector(".onepaqucpro-cr-chart-tooltip");
    const hits = container.querySelectorAll("[data-chart-label]");

    if (!tooltip || !hits.length) {
      return;
    }

    hits.forEach(function (hit) {
      hit.addEventListener("mouseenter", function () {
        tooltip.hidden = false;
        tooltip.innerHTML =
          "<strong>" +
          escapeHtml(hit.getAttribute("data-chart-label")) +
          "</strong>" +
          escapeHtml(hit.getAttribute("data-chart-value"));
      });

      hit.addEventListener("mousemove", function (event) {
        const bounds = container.getBoundingClientRect();
        tooltip.style.left = event.clientX - bounds.left + "px";
        tooltip.style.top = event.clientY - bounds.top + "px";
      });

      hit.addEventListener("mouseleave", function () {
        tooltip.hidden = true;
      });
    });
  }

  function renderLineChart(container, config) {
    const labels = Array.isArray(config.labels) ? config.labels : [];
    const values = Array.isArray(config.values) ? config.values : [];
    const width = 760;
    const height = 300;
    const padding = { top: 18, right: 18, bottom: 42, left: 44 };
    const safeValues = values.map(function (value) {
      return Number(value || 0);
    });
    const maxValue = getNiceMax(Math.max.apply(null, safeValues.concat([0])));
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;
    const stepX = labels.length > 1 ? chartWidth / (labels.length - 1) : 0;
    const points = safeValues.map(function (value, index) {
      const x = padding.left + stepX * index;
      const ratio = maxValue ? value / maxValue : 0;
      const y = padding.top + chartHeight - chartHeight * ratio;

      return { x: x, y: y, value: value, label: labels[index] };
    });
    const linePath = points
      .map(function (point, index) {
        return (index === 0 ? "M" : "L") + point.x + " " + point.y;
      })
      .join(" ");
    const areaPath =
      points.length > 1
        ? linePath +
          " L " +
          (padding.left + stepX * (points.length - 1)) +
          " " +
          (padding.top + chartHeight) +
          " L " +
          padding.left +
          " " +
          (padding.top + chartHeight) +
          " Z"
        : "";

    let svg = '<svg viewBox="0 0 ' + width + " " + height + '" aria-hidden="true">';

    for (let tick = 0; tick <= 4; tick += 1) {
      const y = padding.top + (chartHeight / 4) * tick;
      const tickValue = maxValue - (maxValue / 4) * tick;
      svg +=
        '<line class="grid-line" x1="' +
        padding.left +
        '" y1="' +
        y +
        '" x2="' +
        (width - padding.right) +
        '" y2="' +
        y +
        '"></line>';
      svg +=
        '<text class="axis-label" x="' +
        (padding.left - 10) +
        '" y="' +
        (y + 4) +
        '" text-anchor="end">' +
        escapeHtml(formatChartValue(tickValue, config.format)) +
        "</text>";
    }

    labels.forEach(function (label, index) {
      const x = padding.left + stepX * index;
      svg +=
        '<text class="axis-label" x="' +
        x +
        '" y="' +
        (height - 14) +
        '" text-anchor="middle">' +
        escapeHtml(label) +
        "</text>";
    });

    if (areaPath) {
      svg +=
        '<path class="area-path" d="' +
        areaPath +
        '" fill="' +
        escapeHtml(config.color || "#5d87ff") +
        '"></path>';
    }

    svg +=
      '<path class="line-path" d="' +
      linePath +
      '" stroke="' +
      escapeHtml(config.color || "#5d87ff") +
      '"></path>';

    points.forEach(function (point) {
      svg +=
        '<circle class="line-point" cx="' +
        point.x +
        '" cy="' +
        point.y +
        '" r="5" fill="#fff" stroke="' +
        escapeHtml(config.color || "#5d87ff") +
        '" stroke-width="3"></circle>';
      svg +=
        '<circle class="line-hit" cx="' +
        point.x +
        '" cy="' +
        point.y +
        '" r="14" data-chart-label="' +
        escapeHtml(point.label) +
        '" data-chart-value="' +
        escapeHtml(formatChartValue(point.value, config.format)) +
        '"></circle>';
    });

    svg += "</svg>";

    container.innerHTML =
      svg + '<div class="onepaqucpro-cr-chart-tooltip" hidden></div>';
    bindChartTooltip(container);
  }

  function renderBarChart(container, config) {
    const labels = Array.isArray(config.labels) ? config.labels : [];
    const values = Array.isArray(config.values) ? config.values : [];
    const colors = Array.isArray(config.colors) ? config.colors : [];
    const width = 760;
    const height = 320;
    const padding = { top: 18, right: 18, bottom: 64, left: 44 };
    const safeValues = values.map(function (value) {
      return Number(value || 0);
    });
    const maxValue = getNiceMax(Math.max.apply(null, safeValues.concat([0])));
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;
    const barGap = 18;
    const barWidth =
      labels.length > 0
        ? (chartWidth - Math.max(0, labels.length - 1) * barGap) / labels.length
        : 0;

    let svg = '<svg viewBox="0 0 ' + width + " " + height + '" aria-hidden="true">';

    for (let tick = 0; tick <= 4; tick += 1) {
      const y = padding.top + (chartHeight / 4) * tick;
      const tickValue = maxValue - (maxValue / 4) * tick;
      svg +=
        '<line class="grid-line" x1="' +
        padding.left +
        '" y1="' +
        y +
        '" x2="' +
        (width - padding.right) +
        '" y2="' +
        y +
        '"></line>';
      svg +=
        '<text class="axis-label" x="' +
        (padding.left - 10) +
        '" y="' +
        (y + 4) +
        '" text-anchor="end">' +
        escapeHtml(formatChartValue(tickValue, config.format)) +
        "</text>";
    }

    labels.forEach(function (label, index) {
      const value = safeValues[index] || 0;
      const ratio = maxValue ? value / maxValue : 0;
      const barHeight = chartHeight * ratio;
      const x = padding.left + index * (barWidth + barGap);
      const y = padding.top + chartHeight - barHeight;
      const color = colors[index] || "#5d87ff";

      svg +=
        '<rect x="' +
        x +
        '" y="' +
        y +
        '" width="' +
        barWidth +
        '" height="' +
        barHeight +
        '" rx="8" fill="' +
        escapeHtml(color) +
        '"></rect>';
      svg +=
        '<rect class="bar-hit" x="' +
        x +
        '" y="' +
        padding.top +
        '" width="' +
        barWidth +
        '" height="' +
        chartHeight +
        '" data-chart-label="' +
        escapeHtml(label) +
        '" data-chart-value="' +
        escapeHtml(formatChartValue(value, config.format)) +
        '"></rect>';
      svg +=
        '<text class="axis-label" x="' +
        (x + barWidth / 2) +
        '" y="' +
        (height - 14) +
        '" text-anchor="middle">' +
        escapeHtml(label) +
        "</text>";
    });

    svg += "</svg>";

    container.innerHTML =
      svg + '<div class="onepaqucpro-cr-chart-tooltip" hidden></div>';
    bindChartTooltip(container);
  }

  function renderCharts() {
    document.querySelectorAll(".onepaqucpro-cr-chart").forEach(function (chart) {
      const rawConfig = chart.getAttribute("data-chart-config");
      if (!rawConfig) {
        return;
      }

      try {
        const config = JSON.parse(rawConfig);
        if (config.type === "bar") {
          renderBarChart(chart, config);
        } else {
          renderLineChart(chart, config);
        }
      } catch (error) {
        chart.innerHTML = "";
      }
    });
  }

  function activateTab(tabButton, shouldFocus) {
    const tabsRoot = tabButton ? tabButton.closest("[data-cr-tabs]") : null;
    if (!tabsRoot) {
      return;
    }

    const targetPanel = tabButton.getAttribute("data-cr-tab-button");
    const tabButtons = tabsRoot.querySelectorAll("[data-cr-tab-button]");
    const tabPanels = tabsRoot.querySelectorAll("[data-cr-tab-panel]");

    tabButtons.forEach(function (button) {
      const isActive = button === tabButton;
      button.classList.toggle("is-active", isActive);
      button.setAttribute("aria-selected", isActive ? "true" : "false");
      button.setAttribute("tabindex", isActive ? "0" : "-1");
    });

    tabPanels.forEach(function (panel) {
      const isActive = panel.getAttribute("data-cr-tab-panel") === targetPanel;
      panel.classList.toggle("is-active", isActive);
      panel.hidden = !isActive;
    });

    if (shouldFocus) {
      tabButton.focus();
    }
  }

  function initializeModalTabs(scope) {
    if (!scope) {
      return;
    }

    scope.querySelectorAll("[data-cr-tabs]").forEach(function (tabsRoot) {
      const activeTab =
        tabsRoot.querySelector('[data-cr-tab-button][aria-selected="true"]') ||
        tabsRoot.querySelector("[data-cr-tab-button]");

      if (activeTab) {
        activateTab(activeTab, false);
      }
    });
  }

  function openModal(templateId) {
    if (!modal || !modalContent) {
      return;
    }

    const template = document.getElementById(templateId);
    if (!template) {
      return;
    }

    modalContent.innerHTML = template.innerHTML;
    modalContent
      .querySelectorAll(".onepaqucpro-cr-detail__identity > div")
      .forEach(function (content) {
        content
          .querySelectorAll("p:not(.onepaqucpro-cr-detail__eyebrow):not(.onepaqucpro-cr-detail__subtitle)")
          .forEach(function (duplicateLine) {
            duplicateLine.remove();
          });
      });
    initializeModalTabs(modalContent);
    modal.hidden = false;
    const dialog = modal.querySelector(".onepaqucpro-cr-modal__dialog");
    if (dialog) {
      dialog.scrollTop = 0;
    }
    body.classList.add("onepaqucpro-cr-modal-open");
  }

  function closeModal() {
    if (!modal || !modalContent) {
      return;
    }

    modal.hidden = true;
    modalContent.innerHTML = "";
    body.classList.remove("onepaqucpro-cr-modal-open");
  }

  function toggleCheckAll(checkbox) {
    const form = checkbox.closest("form");
    if (!form) {
      return;
    }

    form.querySelectorAll('tbody input[type="checkbox"]').forEach(function (item) {
      if (item !== checkbox) {
        item.checked = checkbox.checked;
      }
    });
  }

  function addTemplateRow() {
    const wrapper = document.querySelector("[data-cr-template-rows]");
    const template = document.getElementById("onepaqucpro-cr-template-row-template");

    if (!wrapper || !template) {
      return;
    }

    const nextIndex = Number(wrapper.getAttribute("data-template-index") || wrapper.children.length);
    const markup = template.innerHTML.replace(/__INDEX__/g, String(nextIndex));
    wrapper.insertAdjacentHTML("beforeend", markup);
    wrapper.setAttribute("data-template-index", String(nextIndex + 1));
  }

  function showNoteForm(button) {
    const formId = button ? button.getAttribute("data-cr-note-edit") : "";
    if (!formId) {
      return;
    }

    const form = document.getElementById(formId);
    if (!form) {
      return;
    }

    form.classList.remove("is-hidden");
    const noteField = form.querySelector('textarea[name="cart_notes"]');
    if (noteField) {
      noteField.focus();
    }
  }

  document.addEventListener("click", function (event) {
    const openButton = event.target.closest(".onepaqucpro-cr-open-modal");
    if (openButton) {
      event.preventDefault();
      openModal(openButton.getAttribute("data-template"));
      return;
    }

    if (event.target.closest("[data-cr-modal-close]")) {
      event.preventDefault();
      closeModal();
      return;
    }

    if (event.target.closest("[data-cr-template-add-row]")) {
      event.preventDefault();
      addTemplateRow();
      return;
    }

    const tabButton = event.target.closest("[data-cr-tab-button]");
    if (tabButton) {
      event.preventDefault();
      activateTab(tabButton, true);
      return;
    }

    const noteEditButton = event.target.closest("[data-cr-note-edit]");
    if (noteEditButton) {
      event.preventDefault();
      showNoteForm(noteEditButton);
      return;
    }

    const removeButton = event.target.closest("[data-cr-template-remove]");
    if (removeButton) {
      event.preventDefault();
      const row = removeButton.closest("tr");
      if (row) {
        row.remove();
      }
    }
  });

  document.addEventListener("change", function (event) {
    if (event.target.matches("[data-cr-check-all]")) {
      toggleCheckAll(event.target);
    }
  });

  document.addEventListener("submit", function (event) {
    if (!event.target.matches("[data-cr-note-delete]")) {
      return;
    }

    if (!window.confirm("Delete this saved note? Tags will be kept.")) {
      event.preventDefault();
    }
  });

  document.addEventListener("keydown", function (event) {
    if (event.target && event.target.matches("[data-cr-tab-button]")) {
      const tabsRoot = event.target.closest("[data-cr-tabs]");
      const tabButtons = tabsRoot ? Array.from(tabsRoot.querySelectorAll("[data-cr-tab-button]")) : [];
      const currentIndex = tabButtons.indexOf(event.target);

      if (tabButtons.length && ["ArrowLeft", "ArrowRight", "Home", "End"].includes(event.key)) {
        event.preventDefault();

        if (event.key === "Home") {
          activateTab(tabButtons[0], true);
          return;
        }

        if (event.key === "End") {
          activateTab(tabButtons[tabButtons.length - 1], true);
          return;
        }

        const direction = event.key === "ArrowLeft" ? -1 : 1;
        const nextIndex = (currentIndex + direction + tabButtons.length) % tabButtons.length;
        activateTab(tabButtons[nextIndex], true);
        return;
      }
    }

    if (event.key === "Escape") {
      closeModal();
    }
  });

  renderCharts();
});
