jQuery(document).ready(function ($) {
    // Function to update the active TOC item on scroll
    function updateActiveMenuItem() {
        // Get all section elements
        const sections = $('.plugincy-section');
        // Get all TOC links
        const tocLinks = $('.plugincy-toc-list a');

        // Variables to track the current section
        let currentSectionId = '';
        let scrollPosition = $(window).scrollTop();

        // Add some offset to improve accuracy (consider fixed headers etc.)
        const scrollOffset = 100;

        // Find the current section based on scroll position
        sections.each(function () {
            const sectionTop = $(this).offset().top - scrollOffset;
            const sectionBottom = sectionTop + $(this).outerHeight();

            if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                currentSectionId = $(this).attr('id');
                return false; // Break the loop once we found the current section
            }
        });

        // Remove active class from all links
        tocLinks.removeClass('plugincy-active');

        // Add active class to the current section's link
        if (currentSectionId) {
            $('.plugincy-toc-list a[href="#' + currentSectionId + '"]').addClass('plugincy-active');

            // If the active link is a child link, also highlight its parent
            const activeLink = $('.plugincy-toc-list a[href="#' + currentSectionId + '"]');
            const parentLi = activeLink.parent().parent().parent();
            if (parentLi.is('li')) {
                parentLi.children('a').addClass('plugincy-active-parent');
            }
        }
    }

    $(document).on('click change keydown keyup keypress input paste cut mousedown', '.disabled', function (e) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    });

    // Run on page load
    updateActiveMenuItem();

    // Add smooth scrolling to TOC links
    $('.plugincy-toc-list a').on('click', function (e) {
        e.preventDefault();

        const target = $(this).attr('href');
        $('html, body').animate({
            scrollTop: $(target).offset().top - 50
        }, 500);
    });

    // Run on scroll with throttling for performance
    let scrollTimer;
    $(window).on('scroll', function () {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(function () {
            updateActiveMenuItem();
        }, 50);
    });
});

jQuery(document).ready(function ($) {
    if ($('.remove_checkout_fields').length) $('.remove_checkout_fields').select2({
        placeholder: 'Select fields to remove',
        allowClear: true,
        width: '100%'
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const tabs = document.querySelectorAll(".tab");
    const contents = document.querySelectorAll(".tab-content");
    const STORAGE_KEY = 'active_tab'; // Key for localStorage

    // Function to activate a specific tab
    function activateTab(tabIndex) {
        // Remove active class from all tabs and tab contents
        tabs.forEach(t => t.classList.remove("active"));
        contents.forEach(c => c.classList.remove("active"));

        // Add active class to the tab with the matching data-tab attribute
        const targetTab = document.querySelector(`.tab[data-tab="${tabIndex}"]`);
        if (targetTab) {
            targetTab.classList.add("active");
            const content = document.querySelector(`#tab-${tabIndex}`);
            if (content) {
                content.classList.add("active");
            }
        } else {
            // activate the first tab as default
            const firstTab = document.querySelector('.tab');
            if (firstTab) {
                const firstTabIndex = firstTab.dataset.tab;
                activateTab(firstTabIndex);
                saveActiveTab(firstTabIndex);
            }
        }
    }

    // Function to save active tab to localStorage
    function saveActiveTab(tabIndex) {
        try {
            localStorage.setItem(STORAGE_KEY, tabIndex);
        } catch (error) {
            console.warn('Failed to save tab to localStorage:', error);
        }
    }

    // Function to get active tab from localStorage
    function getActiveTab() {
        try {
            return localStorage.getItem(STORAGE_KEY);
        } catch (error) {
            console.warn('Failed to retrieve tab from localStorage:', error);
            return null;
        }
    }

    // Add click event listeners to tabs
    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            const tabIndex = tab.dataset.tab;

            // Save the selected tab to localStorage
            saveActiveTab(tabIndex);

            // Activate the selected tab
            activateTab(tabIndex);
        });
    });

    // Initialize the active tab on page load
    function initializeActiveTab() {
        // Check URL parameters first (for backwards compatibility or direct links)
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');

        let activeTabIndex = null;

        if (tabParam) {
            // If a tab parameter exists in the URL, use that and save it to localStorage
            activeTabIndex = tabParam;
            saveActiveTab(activeTabIndex);
        } else {
            // Otherwise, check localStorage for the previously active tab
            activeTabIndex = getActiveTab();
        }

        if (activeTabIndex) {
            // If we have a stored tab index, activate that tab
            activateTab(activeTabIndex);
        } else {
            // If no stored tab, activate the first tab as default
            const firstTab = document.querySelector('.tab');
            if (firstTab) {
                const firstTabIndex = firstTab.dataset.tab;
                activateTab(firstTabIndex);
                saveActiveTab(firstTabIndex);
            }
        }
    }

    // Initialize the active tab
    initializeActiveTab();
});

document.addEventListener("DOMContentLoaded", function () {
    const editor = document.getElementById("onepaqucpro-floating-cart-editor");

    if (!editor) {
        return;
    }

    const controls = editor.querySelectorAll("[data-floating-cart-control]");
    const panels = editor.querySelectorAll("[data-floating-preview-panel]");
    const modeButtons = editor.querySelectorAll("[data-floating-preview-mode]");
    const previewButton = editor.querySelector(".onepaqucpro-floating-preview-button");
    const previewStage = editor.querySelector(".onepaqucpro-floating-preview-stage");
    const actionBar = document.createElement("div");
    const actionToggle = document.createElement("button");
    const actionEdit = document.createElement("button");
    const editModal = document.createElement("div");
    const iconClassMap = {
        cart: "dashicons-cart",
        "shopping-bag": "dashicons-store",
        basket: "dashicons-products"
    };
    let activeVisualField = "";
    let activeVisualNode = null;
    let actionHideTimer = null;
    const textFallbacks = {};

    function getControl(field) {
        return editor.querySelector('[data-floating-cart-control="' + field + '"]');
    }

    function getControlValue(field) {
        const control = getControl(field);

        if (!control) {
            return "";
        }

        if (control.type === "checkbox") {
            return control.checked ? "1" : "0";
        }

        return control.value;
    }

    function getTextFallback(field) {
        const control = getControl(field);

        if (textFallbacks[field]) {
            return textFallbacks[field];
        }

        if (control && control.getAttribute("placeholder")) {
            return control.getAttribute("placeholder");
        }

        return "";
    }

    function setControlValue(field, value) {
        const control = getControl(field);

        if (!control || control.type === "checkbox" || control.disabled) {
            return;
        }

        control.value = value;
        control.dispatchEvent(new Event("input", { bubbles: true }));
    }

    actionBar.className = "onepaqucpro-floating-visual-actionbar";
    actionBar.hidden = true;
    actionToggle.type = "button";
    actionToggle.className = "onepaqucpro-floating-preview-toggle dashicons dashicons-visibility";
    actionToggle.setAttribute("aria-label", "Toggle selected cart element");
    actionEdit.type = "button";
    actionEdit.className = "onepaqucpro-floating-preview-edit dashicons dashicons-edit";
    actionEdit.setAttribute("aria-label", "Edit selected cart element");
    actionBar.appendChild(actionToggle);
    actionBar.appendChild(actionEdit);
    editor.querySelector(".onepaqucpro-floating-preview-stage").appendChild(actionBar);

    editModal.className = "onepaqucpro-floating-edit-modal";
    editModal.hidden = true;
    editModal.innerHTML = '<div class="onepaqucpro-floating-edit-modal__backdrop" data-floating-edit-close></div><div class="onepaqucpro-floating-edit-modal__panel" role="dialog" aria-modal="true" aria-label="Floating cart element settings"><div class="onepaqucpro-floating-edit-modal__header"><strong>Element Settings</strong><button type="button" class="dashicons dashicons-no-alt" data-floating-edit-close aria-label="Close element settings"></button></div><div class="onepaqucpro-floating-edit-modal__body"></div></div>';
    editor.appendChild(editModal);

    function positionActionBar(targetNode, field) {
        const control = getControl(field);
        const textNodes = collectTextNodes(targetNode);
        const hasToggleSetting = !!control && control.type === "checkbox";
        const hasPanelSettings = textNodes.length > 0 || targetNode.querySelector("[data-preview-icon]") || targetNode.matches("[data-preview-icon]");
        const hasEditableSettings = hasToggleSetting || hasPanelSettings;

        if (!hasEditableSettings) {
            hideActionBar();
            activeVisualField = "";
            activeVisualNode = null;
            return;
        }

        const stage = editor.querySelector(".onepaqucpro-floating-preview-stage");
        const stageRect = stage.getBoundingClientRect();
        const targetRect = targetNode.getBoundingClientRect();

        activeVisualField = field;
        activeVisualNode = targetNode;
        actionBar.hidden = false;
        actionBar.style.top = Math.max(8, targetRect.top - stageRect.top - 10) + "px";
        actionBar.style.left = Math.min(stageRect.width - 72, Math.max(8, targetRect.right - stageRect.left - 10)) + "px";
        actionToggle.hidden = !hasToggleSetting;
        actionEdit.hidden = !hasPanelSettings;

        if (hasToggleSetting) {
            actionToggle.classList.toggle("is-disabled", !control.checked);
            actionToggle.setAttribute("aria-pressed", control.checked ? "true" : "false");
            actionToggle.setAttribute("title", control.checked ? "Disable this element" : "Enable this element");
        }
    }

    function hideActionBar() {
        actionBar.hidden = true;
        activeVisualField = "";
        activeVisualNode = null;
    }

    function scheduleActionBarHide() {
        window.clearTimeout(actionHideTimer);
        actionHideTimer = window.setTimeout(function () {
            if (
                actionBar.matches(":hover") ||
                (activeVisualNode && activeVisualNode.matches(":hover")) ||
                (activeVisualNode && activeVisualNode.contains(document.activeElement))
            ) {
                return;
            }

            hideActionBar();
        }, 120);
    }

    function setPreviewVisibility(field) {
        const control = getControl(field);
        const parts = editor.querySelectorAll('[data-preview-part="' + field + '"]');

        if (!control || !parts.length || control.type !== "checkbox") {
            return;
        }

        parts.forEach(function (part) {
            part.classList.toggle("is-preview-hidden", !control.checked);
        });

        if (activeVisualField === field) {
            actionToggle.classList.toggle("is-disabled", !control.checked);
            actionToggle.setAttribute("aria-pressed", control.checked ? "true" : "false");
            actionToggle.setAttribute("title", control.checked ? "Disable this element" : "Enable this element");
        }
    }

    function updatePreviewText(field) {
        const nodes = editor.querySelectorAll('[data-preview-text="' + field + '"]');
        const rawValue = getControlValue(field);
        const value = rawValue || getTextFallback(field);

        nodes.forEach(function (node) {
            if (document.activeElement !== node) {
                node.textContent = value;
            }
        });
    }

    function collectTextNodes(targetNode) {
        if (!targetNode) {
            return [];
        }

        const nodes = [];

        if (targetNode.matches && targetNode.matches("[data-preview-text]")) {
            nodes.push(targetNode);
        }

        targetNode.querySelectorAll("[data-preview-text]").forEach(function (node) {
            if (nodes.indexOf(node) === -1) {
                nodes.push(node);
            }
        });

        return nodes;
    }

    function getFieldLabel(field) {
        const control = getControl(field);

        if (!control) {
            return field.replace(/^rmenu_floating_cart_/, "").replace(/^txt_/, "").replace(/_/g, " ");
        }

        const wrapper = control.closest("[data-floating-control-wrap]");
        const row = control.closest("tr");
        const label = wrapper ? wrapper.querySelector("label, .rmenupro-settings-label") : null;
        const rowLabel = row ? row.querySelector("th") : null;

        return ((label && label.textContent) || (rowLabel && rowLabel.textContent) || control.name || field)
            .replace(/\s+/g, " ")
            .replace(/\s+\?.*$/, "")
            .trim();
    }

    function buildTextSetting(body, node) {
        const field = node.getAttribute("data-preview-text");
        const row = document.createElement("label");
        const input = document.createElement("input");

        row.className = "onepaqucpro-floating-edit-field";
        row.innerHTML = "<span></span>";
        row.querySelector("span").textContent = getFieldLabel(field);
        input.type = "text";
        input.value = getControlValue(field) || getTextFallback(field);
        input.addEventListener("input", function () {
            setControlValue(field, input.value);
            updatePreviewText(field);
        });
        row.appendChild(input);
        body.appendChild(row);
    }

    function buildToggleSetting(body, field, labelText) {
        const control = getControl(field);

        if (!control || control.type !== "checkbox") {
            return;
        }

        const row = document.createElement("label");
        const input = document.createElement("input");
        const text = document.createElement("span");

        row.className = "onepaqucpro-floating-edit-toggle";
        input.type = "checkbox";
        input.checked = control.checked;
        input.disabled = control.disabled;
        text.textContent = labelText || "Show this element";
        input.addEventListener("change", function () {
            control.checked = input.checked;
            control.dispatchEvent(new Event("change", { bubbles: true }));
        });
        row.appendChild(input);
        row.appendChild(text);
        body.appendChild(row);
    }

    function buildIconSetting(body) {
        const control = getControl("rmenu_floating_cart_icon");

        if (!control) {
            return;
        }

        const row = document.createElement("label");
        const select = document.createElement("select");

        row.className = "onepaqucpro-floating-edit-field";
        row.innerHTML = "<span>Floating cart icon</span>";
        Array.prototype.forEach.call(control.options, function (option) {
            const clone = option.cloneNode(true);
            clone.selected = option.selected;
            select.appendChild(clone);
        });
        select.value = control.value;
        select.addEventListener("change", function () {
            control.value = select.value;
            control.dispatchEvent(new Event("change", { bubbles: true }));
        });
        row.appendChild(select);
        body.appendChild(row);
    }

    function buildControlSetting(body, field) {
        const control = getControl(field);

        if (!control) {
            return;
        }

        if (control.type === "checkbox") {
            buildToggleSetting(body, field, getFieldLabel(field));
            return;
        }

        const row = document.createElement("label");
        const fieldControl = control.tagName === "SELECT" ? document.createElement("select") : document.createElement("input");

        row.className = "onepaqucpro-floating-edit-field";
        row.innerHTML = "<span></span>";
        row.querySelector("span").textContent = getFieldLabel(field);

        if (control.tagName === "SELECT") {
            Array.prototype.forEach.call(control.options, function (option) {
                const clone = option.cloneNode(true);
                clone.selected = option.selected;
                fieldControl.appendChild(clone);
            });
        } else {
            fieldControl.type = control.type || "text";
        }

        fieldControl.value = control.value;
        fieldControl.disabled = control.disabled;
        fieldControl.addEventListener("input", function () {
            control.value = fieldControl.value;
            control.dispatchEvent(new Event("input", { bubbles: true }));
        });
        fieldControl.addEventListener("change", function () {
            control.value = fieldControl.value;
            control.dispatchEvent(new Event("change", { bubbles: true }));
        });
        row.appendChild(fieldControl);
        body.appendChild(row);
    }

    function buildSectionTitle(body, title) {
        const heading = document.createElement("h4");

        heading.className = "onepaqucpro-floating-edit-section-title";
        heading.textContent = title;
        body.appendChild(heading);
    }

    function openEditModal() {
        if (!activeVisualNode) {
            return;
        }

        const body = editModal.querySelector(".onepaqucpro-floating-edit-modal__body");
        const heading = editModal.querySelector(".onepaqucpro-floating-edit-modal__header strong");
        const textNodes = collectTextNodes(activeVisualNode);
        const isCartLauncher = activeVisualNode.classList.contains("onepaqucpro-floating-preview-button") || activeVisualNode.matches("[data-preview-icon]") || activeVisualNode.querySelector("[data-preview-icon]");
        const isCheckoutButton = activeVisualNode.classList.contains("onepaqucpro-floating-preview-checkout");

        body.innerHTML = "";
        heading.textContent = getFieldLabel(activeVisualField);
        buildToggleSetting(body, activeVisualField);

        if (isCartLauncher) {
            buildToggleSetting(body, "rmenu_enable_sticky_cart", "Enable sticky cart");
            buildIconSetting(body);
            buildControlSetting(body, "rmenu_hide_empty_cart_button");
            buildSectionTitle(body, "Position Settings");
            buildControlSetting(body, "rmenu_cart_top_position");
            buildControlSetting(body, "rmenu_cart_left_position");
        }

        if (isCheckoutButton) {
            buildControlSetting(body, "rmenu_cart_checkout_behavior");
        }

        textNodes.forEach(function (node) {
            buildTextSetting(body, node);
        });

        if (!body.children.length) {
            const empty = document.createElement("p");
            empty.className = "description";
            empty.textContent = "No editable settings are mapped to this element.";
            body.appendChild(empty);
        }

        editModal.hidden = false;
    }

    function updateCartIcon() {
        const value = getControlValue("rmenu_floating_cart_icon") || "cart";
        const iconClass = iconClassMap[value] || iconClassMap.cart;

        editor.querySelectorAll("[data-preview-icon='cart']").forEach(function (icon) {
            Object.keys(iconClassMap).forEach(function (key) {
                icon.classList.remove(iconClassMap[key]);
            });
            icon.classList.add(iconClass);
        });
    }

    function parsePreviewPosition(value, axisLength, fallbackPx, maxPx) {
        const raw = String(value || "").trim();
        let px = fallbackPx;

        if (raw.slice(-1) === "%") {
            px = axisLength * (parseFloat(raw) || 0) / 100;
        } else if (raw) {
            px = parseFloat(raw);
        }

        if (!Number.isFinite(px)) {
            px = fallbackPx;
        }

        return Math.max(8, Math.min(px, Math.max(8, maxPx)));
    }

    function updateLauncherPosition() {
        if (!previewButton || !previewStage) {
            return;
        }

        const enabled = getControlValue("rmenu_enable_sticky_cart") === "1";
        const stageRect = previewStage.getBoundingClientRect();
        const drawer = editor.querySelector(".onepaqucpro-floating-preview-drawer:not([hidden])");
        const drawerRect = drawer ? drawer.getBoundingClientRect() : null;
        const buttonRect = previewButton.getBoundingClientRect();
        const leftMax = drawerRect
            ? drawerRect.left - stageRect.left - buttonRect.width - 8
            : stageRect.width - buttonRect.width - 8;
        const topMax = stageRect.height - buttonRect.height - 8;
        const top = parsePreviewPosition(getControlValue("rmenu_cart_top_position"), stageRect.height, 34, topMax);
        const left = parsePreviewPosition(getControlValue("rmenu_cart_left_position"), stageRect.width, 18, leftMax);

        previewButton.style.top = top + "px";
        previewButton.style.left = left + "px";
        previewStage.classList.toggle("is-sticky-disabled", !enabled);
    }

    function updateButtonStyle() {
        if (!previewButton || !previewStage) {
            return;
        }

        const background = getControlValue("rmenu_cart_bg_color");
        const color = getControlValue("rmenu_cart_text_color");
        const hoverBackground = getControlValue("rmenu_cart_hover_bg");
        const hoverColor = getControlValue("rmenu_cart_hover_text");
        const radius = getControlValue("rmenu_cart_border_radius");

        if (background) {
            previewStage.style.setProperty("--onepaqucpro-floating-primary", background);
        }

        if (color) {
            previewStage.style.setProperty("--onepaqucpro-floating-primary-text", color);
        }

        if (hoverBackground) {
            previewStage.style.setProperty("--onepaqucpro-floating-primary-hover", hoverBackground);
        }

        if (hoverColor) {
            previewStage.style.setProperty("--onepaqucpro-floating-primary-hover-text", hoverColor);
        }

        if (radius) {
            previewStage.style.setProperty("--onepaqucpro-floating-button-radius", radius);
        }
    }

    function updateDependencyVisibility() {
        const itemSelectEnabled = getControlValue("rmenu_floating_cart_show_item_select") === "1";
        const selectBarParts = editor.querySelectorAll('[data-preview-part="rmenu_floating_cart_show_select_bar"]');

        selectBarParts.forEach(function (part) {
            const selectBarEnabled = getControlValue("rmenu_floating_cart_show_select_bar") === "1";
            part.classList.toggle("is-preview-hidden", !itemSelectEnabled || !selectBarEnabled);
        });

        const summaryEnabled = getControlValue("rmenu_floating_cart_show_summary") === "1";
        ["rmenu_floating_cart_show_subtotal", "rmenu_floating_cart_show_discount", "rmenu_floating_cart_show_total"].forEach(function (field) {
            editor.querySelectorAll('[data-preview-part="' + field + '"]').forEach(function (part) {
                part.classList.toggle("is-preview-hidden", !summaryEnabled || getControlValue(field) !== "1");
            });
        });
    }

    function syncPreview() {
        controls.forEach(function (control) {
            const field = control.getAttribute("data-floating-cart-control");

            if (control.type === "checkbox") {
                setPreviewVisibility(field);
            } else {
                updatePreviewText(field);
            }
        });

        updateButtonStyle();
        updateCartIcon();
        updateLauncherPosition();
        updateDependencyVisibility();
    }

    controls.forEach(function (control) {
        control.addEventListener("input", syncPreview);
        control.addEventListener("change", syncPreview);
        control.addEventListener("focus", function () {
            const field = control.getAttribute("data-floating-cart-control");

            editor.querySelectorAll(".is-preview-focused").forEach(function (node) {
                node.classList.remove("is-preview-focused");
            });

            editor.querySelectorAll('[data-preview-target="' + field + '"], [data-preview-part="' + field + '"]').forEach(function (node) {
                node.classList.add("is-preview-focused");
            });
        });
    });

    editor.querySelectorAll("[data-preview-text]").forEach(function (node) {
        const field = node.getAttribute("data-preview-text");
        const fallback = (node.textContent || "").trim();

        if (fallback) {
            textFallbacks[field] = fallback;
        }

        node.setAttribute("contenteditable", "true");
        node.setAttribute("spellcheck", "false");
        node.classList.add("is-visual-text-editor");
        node.setAttribute("title", "Click and type to edit this text");

        node.addEventListener("keydown", function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
                node.blur();
            }
        });

        node.addEventListener("input", function () {
            setControlValue(field, node.textContent.trim());
        });

        node.addEventListener("blur", function () {
            const value = node.textContent.trim();

            if (!value) {
                const fallbackValue = getTextFallback(field);
                node.textContent = fallbackValue;
                setControlValue(field, "");
                syncPreview();
                return;
            }

            setControlValue(field, value);
        });
    });

    actionToggle.addEventListener("click", function (event) {
        const control = getControl(activeVisualField);

        event.preventDefault();
        event.stopPropagation();

        if (!control || control.type !== "checkbox") {
            return;
        }

        if (control.disabled) {
            control.focus({ preventScroll: true });
            return;
        }

        control.checked = !control.checked;
        control.dispatchEvent(new Event("change", { bubbles: true }));
    });

    actionEdit.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
        openEditModal();
    });

    editModal.addEventListener("click", function (event) {
        if (event.target.closest("[data-floating-edit-close]")) {
            event.preventDefault();
            editModal.hidden = true;
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && !editModal.hidden) {
            editModal.hidden = true;
        }
    });

    editor.querySelectorAll("[data-preview-target], [data-preview-part]").forEach(function (node) {
        node.addEventListener("mouseenter", function () {
            const field = node.getAttribute("data-preview-part") || node.getAttribute("data-preview-target");
            positionActionBar(node, field);
        });

        node.addEventListener("mouseleave", scheduleActionBarHide);

        node.addEventListener("focus", function () {
            const field = node.getAttribute("data-preview-part") || node.getAttribute("data-preview-target");
            positionActionBar(node, field);
        }, true);

        node.addEventListener("focusout", scheduleActionBarHide, true);

        node.addEventListener("click", function (event) {
            if (event.target.closest(".onepaqucpro-floating-preview-toggle") || event.target.closest(".onepaqucpro-floating-preview-edit") || event.target.closest("[contenteditable]")) {
                return;
            }

            const field = node.getAttribute("data-preview-part") || node.getAttribute("data-preview-target");
            const control = getControl(field);
            const wrapper = editor.querySelector('[data-floating-control-wrap="' + field + '"]');

            if (!control) {
                return;
            }

            event.preventDefault();
            positionActionBar(node, field);
            control.focus({ preventScroll: true });
            control.scrollIntoView({ behavior: "smooth", block: "center" });

            if (wrapper) {
                wrapper.classList.add("is-control-focused");
                window.setTimeout(function () {
                    wrapper.classList.remove("is-control-focused");
                }, 1200);
            }
        });
    });

    actionBar.addEventListener("mouseenter", function () {
        window.clearTimeout(actionHideTimer);
    });

    actionBar.addEventListener("mouseleave", scheduleActionBarHide);

    modeButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const mode = button.getAttribute("data-floating-preview-mode");

            modeButtons.forEach(function (modeButton) {
                modeButton.classList.toggle("active", modeButton === button);
            });

            panels.forEach(function (panel) {
                panel.hidden = panel.getAttribute("data-floating-preview-panel") !== mode;
            });
        });
    });

    syncPreview();
    hideActionBar();
    window.addEventListener("resize", updateLauncherPosition);
});





(function () {
    // Inject CSS styles
    const style = document.createElement('style');
    style.textContent = `
    .modal-overlay-notice {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(4px);
      z-index: 9999;
      animation: fadeIn 0.3s ease;
    }

    .modal-overlay-notice.active {
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .modal-notice {
      background: white;
      border-radius: 16px;
      padding: 0;
      max-width: 400px;
      width: 90%;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
      animation: slideUp 0.3s ease;
      overflow: hidden;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    }

    .modal-notice-header {
      padding: 24px 24px 16px;
      text-align: center;
    }

    .modal-notice-icon {
      width: 56px;
      height: 56px;
      margin: 0 auto 16px;
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
    }

    .modal-notice-title {
      font-size: 20px;
      font-weight: 600;
      color: #dc2626;
      margin: 0 0 8px 0;
    }

    .modal-notice-body {
      padding: 0 24px 24px;
      text-align: center;
    }

    .modal-notice-message {
      font-size: 15px;
      color: #666;
      line-height: 1.6;
      margin: 0;
    }

    .modal-notice-footer {
      display: flex;
      gap: 12px;
      padding: 16px 24px 24px;
    }

    .modal-notice-btn {
      flex: 1;
      padding: 12px 24px;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }

    .modal-notice-btn-cancel {
      background: #f1f3f5;
      color: #495057;
    }

    .modal-notice-btn-cancel:hover {
      background: #e9ecef;
    }

    .modal-notice-btn-confirm {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: white;
    }

    .modal-notice-btn-confirm:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes slideUp {
      from {
        transform: translateY(30px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }
  `;
    document.head.appendChild(style);

    // Create modal HTML
    const modalHTML = `
    <div class="modal-overlay-notice" id="modalOverlayNotice">
      <div class="modal-notice">
        <div class="modal-notice-header">
          <div class="modal-notice-icon"><svg width="18" height="18" viewBox="0 0 0.54 0.54" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M.27.332A.02.02 0 0 1 .253.315V.202Q.255.187.27.185C.285.183.287.193.287.202v.112A.02.02 0 0 1 .27.331m0 .074H.266L.262.404.258.402.255.399Q.248.392.248.383q0-.012.007-.016L.258.364.262.362.266.361h.009l.004.001.004.002.003.003q.007.007.007.016 0 .012-.007.016L.283.402.279.404.275.405H.271" fill="#fff"/><path d="M.406.499H.134Q.067.499.039.454.013.409.046.35L.182.104Q.217.042.27.041C.323.04.335.063.358.104l.136.245q.032.059.007.104Q.474.497.406.498M.27.075Q.237.075.212.12L.076.366Q.053.408.069.437c.016.029.034.028.065.028h.273q.049 0 .065-.028C.488.409.48.394.465.366L.328.121Q.303.077.27.076" fill="#fff"/></svg></div>
          <h3 class="modal-notice-title">Confirm Action</h3>
        </div>
        <div class="modal-notice-body">
          <p class="modal-notice-message" id="modalNoticeMessage"></p>
        </div>
        <div class="modal-notice-footer">
          <button class="modal-notice-btn modal-notice-btn-cancel" id="btnNoticeCancel">Cancel</button>
          <button class="modal-notice-btn modal-notice-btn-confirm" id="btnNoticeConfirm">Confirm</button>
        </div>
      </div>
    </div>
  `;

    // Inject modal into body
    document.addEventListener('DOMContentLoaded', function () {
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        let checkboxes = document.querySelectorAll('input[type="checkbox"][data-notice]');
        checkboxes = Array.from(checkboxes).filter(checkbox => checkbox.dataset.notice !== '');
        const modalOverlay = document.getElementById('modalOverlayNotice');
        const modalMessage = document.getElementById('modalNoticeMessage');
        const btnCancel = document.getElementById('btnNoticeCancel');
        const btnConfirm = document.getElementById('btnNoticeConfirm');

        let currentCheckbox = null;
        let previousState = {};

        // Initialize previous states
        checkboxes.forEach(checkbox => {
            previousState[checkbox.name] = checkbox.checked;
        });

        // Handle checkbox changes
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function (e) {
                if (previousState[this.name] === true && this.checked === false) {
                    const noticeMessage = this.getAttribute('data-notice');
                    currentCheckbox = this;

                    // Show modal
                    modalMessage.textContent = noticeMessage;
                    modalOverlay.classList.add('active');

                    // Prevent default behavior
                    this.checked = true;
                } else {
                    previousState[this.name] = this.checked;
                }
            });
        });

        // Cancel button
        btnCancel.addEventListener('click', function () {
            modalOverlay.classList.remove('active');
            if (currentCheckbox) {
                currentCheckbox.checked = true;
                previousState[currentCheckbox.name] = true;
            }
            currentCheckbox = null;
        });

        // Confirm button
        btnConfirm.addEventListener('click', function () {
            modalOverlay.classList.remove('active');
            if (currentCheckbox) {
                currentCheckbox.checked = false;
                previousState[currentCheckbox.name] = false;
            }
            currentCheckbox = null;
        });

        // Close on overlay click
        modalOverlay.addEventListener('click', function (e) {
            if (e.target === modalOverlay) {
                btnCancel.click();
            }
        });
    });
})();
