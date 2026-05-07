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
    const emptyIconSvgMap = {
        cart: '<svg class="onepaqucpro-empty-cart-icon-svg" xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 3h2.2l2.1 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.5L21 7H6.1"></path><circle cx="10" cy="20" r="1.4"></circle><circle cx="18" cy="20" r="1.4"></circle></svg>',
        basket: '<svg class="onepaqucpro-empty-cart-icon-svg" xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 10 10 4"></path><path d="m14 4 3 6"></path><path d="M4 10h16l-1.4 9a2 2 0 0 1-2 1.7H7.4a2 2 0 0 1-2-1.7L4 10Z"></path><path d="M9 14v3"></path><path d="M15 14v3"></path></svg>',
        "shopping-bag": '<svg class="onepaqucpro-empty-cart-icon-svg" xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6.5 7.5h11l1 12a2 2 0 0 1-2 2h-9a2 2 0 0 1-2-2l1-12Z"></path><path d="M9 7.5V6a3 3 0 0 1 6 0v1.5"></path></svg>',
        package: '<svg class="onepaqucpro-empty-cart-icon-svg" xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 2.8 20 7v10l-8 4.2L4 17V7l8-4.2Z"></path><path d="M4.5 7.3 12 11.4l7.5-4.1"></path><path d="M12 21V11.4"></path><path d="m8 4.8 8 4.3"></path></svg>',
        receipt: '<svg class="onepaqucpro-empty-cart-icon-svg" xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6 3h12v18l-2-1.2-2 1.2-2-1.2-2 1.2-2-1.2L6 21V3Z"></path><path d="M9 8h6"></path><path d="M9 12h6"></path><path d="M9 16h4"></path></svg>'
    };
    const variationMetaKey = "onepaqucpro_variations";
    let activeVisualField = "";
    let activeVisualNode = null;
    let actionHideTimer = null;
    let editModalDependencyTimer = null;
    const textFallbacks = {};
    const explicitTextFallbacks = {
        rmenu_floating_cart_related_add_to_cart_text: "Add to cart"
    };

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

        if (explicitTextFallbacks[field]) {
            return explicitTextFallbacks[field];
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
        const fieldPanelSettings = ["rmenu_floating_cart_show_product_title", "rmenu_floating_cart_show_item_meta", "rmenu_floating_cart_group_items", "rmenu_floating_cart_show_summary", "rmenu_floating_cart_show_total", "rmenu_floating_cart_show_empty_icon"].indexOf(field) !== -1;
        const hasPanelSettings = fieldPanelSettings || textNodes.length > 0 || targetNode.querySelector("[data-preview-icon], [data-preview-group-icon], [data-preview-empty-icon]") || targetNode.matches("[data-preview-icon], [data-preview-group-icon], [data-preview-empty-icon]");
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
        const rawValue = String(getControlValue(field) || "").trim();
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
        row.setAttribute("data-modal-control-wrap", field);
        row.innerHTML = "<span></span>";
        row.querySelector("span").textContent = getFieldLabel(field);
        input.type = "text";
        input.value = getControlValue(field) || getTextFallback(field);
        input.setAttribute("data-modal-control-field", field);
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
        row.setAttribute("data-modal-control-wrap", field);
        input.type = "checkbox";
        input.checked = control.checked;
        input.disabled = control.disabled;
        input.setAttribute("data-modal-control-field", field);
        text.textContent = labelText || "Show this element";
        input.addEventListener("change", function () {
            control.checked = input.checked;
            control.dispatchEvent(new Event("change", { bubbles: true }));
            updateEditModalDependencies(body);
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

        const sourceBuilder = control.closest("[data-meta-builder]");
        if (sourceBuilder) {
            const row = document.createElement("div");
            const label = document.createElement("span");
            const builder = sourceBuilder.cloneNode(true);
            const clonedHidden = builder.querySelector("[data-floating-cart-control]");

            row.className = "onepaqucpro-floating-edit-field onepaqucpro-floating-edit-field--wide";
            row.setAttribute("data-modal-control-wrap", field);
            label.textContent = getFieldLabel(field);
            row.appendChild(label);
            builder.removeAttribute("data-meta-builder-ready");
            builder.querySelectorAll("[name]").forEach(function (node) {
                node.removeAttribute("name");
            });
            if (clonedHidden) {
                clonedHidden.value = control.value;
                clonedHidden.setAttribute("data-modal-control-field", field);
            }
            row.appendChild(builder);
            body.appendChild(row);
            initMetaBuilders(row);
            if (clonedHidden) {
                clonedHidden.addEventListener("change", function () {
                    control.value = clonedHidden.value;
                    control.dispatchEvent(new Event("input", { bubbles: true }));
                    control.dispatchEvent(new Event("change", { bubbles: true }));
                    rebuildMetaBuilderRows(sourceBuilder, parseMetaBuilderValue(clonedHidden.value));
                    updateEditModalDependencies(body);
                });
            }
            return;
        }

        const sourcePicker = control.closest("[data-meta-picker]");
        if (sourcePicker) {
            const row = document.createElement("div");
            const label = document.createElement("span");
            const picker = sourcePicker.cloneNode(true);
            const clonedHidden = picker.querySelector("[data-floating-cart-control]");
            const clonedSelect = picker.querySelector("select");

            row.className = "onepaqucpro-floating-edit-field";
            row.setAttribute("data-modal-control-wrap", field);
            label.textContent = getFieldLabel(field);
            row.appendChild(label);
            picker.removeAttribute("data-meta-picker-ready");
            picker.querySelectorAll(".select2").forEach(function (node) {
                node.remove();
            });
            picker.querySelectorAll("[name]").forEach(function (node) {
                node.removeAttribute("name");
            });
            if (clonedHidden) {
                clonedHidden.value = control.value;
                clonedHidden.setAttribute("data-modal-control-field", field);
            }
            if (clonedSelect) {
                clonedSelect.classList.remove("select2-hidden-accessible");
                clonedSelect.removeAttribute("data-select2-id");
                clonedSelect.removeAttribute("aria-hidden");
                clonedSelect.removeAttribute("tabindex");
                const selected = control.value.split(",").map(function (item) { return item.trim(); });
                Array.prototype.forEach.call(clonedSelect.options, function (option) {
                    option.removeAttribute("data-select2-id");
                    option.selected = selected.indexOf(option.value) !== -1;
                });
            }
            row.appendChild(picker);
            body.appendChild(row);
            initMetaPickers(row);
            if (clonedHidden) {
                clonedHidden.addEventListener("change", function () {
                    control.value = clonedHidden.value;
                    control.dispatchEvent(new Event("input", { bubbles: true }));
                    control.dispatchEvent(new Event("change", { bubbles: true }));
                    updateEditModalDependencies(body);
                    const sourceSelect = sourcePicker.querySelector("select");
                    const selected = clonedHidden.value.split(",").map(function (item) { return item.trim(); });
                    if (sourceSelect) {
                        Array.prototype.forEach.call(clonedSelect.options, function (option) {
                            if (!sourceSelect.querySelector('option[value="' + option.value + '"]')) {
                                sourceSelect.appendChild(option.cloneNode(true));
                            }
                        });
                        Array.prototype.forEach.call(sourceSelect.options, function (option) {
                            option.selected = selected.indexOf(option.value) !== -1;
                        });
                    }
                });
            }
            return;
        }

        if (control.type === "checkbox") {
            buildToggleSetting(body, field, getFieldLabel(field));
            return;
        }

        const row = document.createElement("label");
        const fieldControl = control.tagName === "SELECT" ? document.createElement("select") : document.createElement("input");

        row.className = "onepaqucpro-floating-edit-field";
        row.setAttribute("data-modal-control-wrap", field);
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
        fieldControl.setAttribute("data-modal-control-field", field);
        fieldControl.addEventListener("input", function () {
            control.value = fieldControl.value;
            control.dispatchEvent(new Event("input", { bubbles: true }));
            updateEditModalDependencies(body);
        });
        fieldControl.addEventListener("change", function () {
            control.value = fieldControl.value;
            control.dispatchEvent(new Event("change", { bubbles: true }));
            updateEditModalDependencies(body);
        });
        row.appendChild(fieldControl);
        body.appendChild(row);
    }

    function syncMetaPicker(picker) {
        const hidden = picker.querySelector("[data-floating-cart-control]");
        const select = picker.querySelector("select");

        if (!hidden || !select) {
            return;
        }

        const values = Array.prototype.slice.call(select.selectedOptions).map(function (option) {
            return option.value;
        });

        hidden.value = values.join(",");
        hidden.dispatchEvent(new Event("input", { bubbles: true }));
        hidden.dispatchEvent(new Event("change", { bubbles: true }));
    }

    function initMetaPickers(root) {
        root.querySelectorAll("[data-meta-picker]").forEach(function (picker) {
            const select = picker.querySelector("select");
            const customInput = picker.querySelector(".onepaqucpro-meta-picker__custom input");
            const customButton = picker.querySelector(".onepaqucpro-meta-picker__custom button");
            const multiple = picker.getAttribute("data-meta-picker-multiple") === "1";

            if (!select || picker.getAttribute("data-meta-picker-ready") === "1") {
                return;
            }

            picker.setAttribute("data-meta-picker-ready", "1");
            select.addEventListener("change", function () {
                syncMetaPicker(picker);
            });

            if (customButton && customInput) {
                customButton.addEventListener("click", function () {
                    const value = customInput.value.trim();
                    const normalized = value.toLowerCase().replace(/[^a-z0-9_ -]/g, "").replace(/\s+/g, "-");

                    if (!normalized) {
                        return;
                    }

                    let option = select.querySelector('option[value="' + normalized + '"]');
                    if (!option) {
                        option = document.createElement("option");
                        option.value = normalized;
                        option.textContent = value;
                        select.appendChild(option);
                    }

                    if (!multiple) {
                        Array.prototype.forEach.call(select.options, function (item) {
                            item.selected = false;
                        });
                    }

                    option.selected = true;
                    customInput.value = "";
                    syncMetaPicker(picker);
                    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2 && select.getAttribute("data-use-select2") === "1") {
                        window.jQuery(select).trigger("change.select2");
                    }
                });
            }

            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2 && select.getAttribute("data-use-select2") === "1") {
                const $select = window.jQuery(select);
                if ($select.data("select2")) {
                    $select.select2("destroy");
                }
                window.jQuery(select).select2({
                    tags: true,
                    tokenSeparators: [","],
                    width: "100%",
                    placeholder: "Select meta keys",
                    dropdownParent: window.jQuery(select).closest(".onepaqucpro-floating-edit-modal__panel").length
                        ? window.jQuery(select).closest(".onepaqucpro-floating-edit-modal__panel")
                        : window.jQuery(document.body)
                }).on("change", function () {
                    syncMetaPicker(picker);
                });
            }
        });
    }

    function parseMetaBuilderOptions(builder) {
        try {
            return JSON.parse(builder.getAttribute("data-meta-builder-options") || "[]");
        } catch (error) {
            return [];
        }
    }

    function parseMetaBuilderValue(value) {
        const raw = String(value || "").trim();
        let rows = [];

        if (raw && (raw.charAt(0) === "[" || raw.charAt(0) === "{")) {
            try {
                rows = JSON.parse(raw);
            } catch (error) {
                rows = [];
            }
        } else if (raw) {
            rows = raw.split(",").map(function (item) {
                return { key: item.trim(), title: "" };
            });
        }

        if (rows && !Array.isArray(rows) && rows.key) {
            rows = [rows];
        }

        return (Array.isArray(rows) ? rows : []).map(function (row) {
            if (typeof row === "string") {
                row = { key: row, title: "" };
            }

            const key = String(row && row.key ? row.key : "").trim();
            const mode = row && row.mode === "combine" ? "combine" : "separate";

            return {
                key: key,
                mode: key === variationMetaKey ? mode : "separate",
                title: String(row && row.title ? row.title : "").trim()
            };
        }).filter(function (row) {
            return row.key && row.key !== "mulopimfwc_location";
        });
    }

    function isVariationMetaRow(rowOrKey) {
        const key = typeof rowOrKey === "string"
            ? rowOrKey
            : (rowOrKey && rowOrKey.querySelector("[data-meta-builder-key]")
                ? rowOrKey.querySelector("[data-meta-builder-key]").value
                : "");

        return key === variationMetaKey;
    }

    function getDefaultVariationMetaRule() {
        return {
            key: variationMetaKey,
            mode: "separate",
            title: "Variations"
        };
    }

    function getMetaBuilderOptionLabel(options, value) {
        for (let index = 0; index < options.length; index++) {
            if (options[index].value === value) {
                return options[index].label;
            }
        }

        return value;
    }

    function normalizeMetaBuilderTitle(label) {
        return String(label || "").replace(/\s+\([^)]+\)$/, "").trim();
    }

    function formatMetaLabel(value) {
        return normalizeMetaBuilderTitle(String(value || "")
            .replace(/^attribute_/, "")
            .replace(/[_-]+/g, " ")
            .replace(/\s+/g, " ")
            .trim())
            .replace(/\b\w/g, function (char) {
                return char.toUpperCase();
            });
    }

    function getPreviewMetaSampleValue(key) {
        const normalized = String(key || "").toLowerCase();

        if (normalized.indexOf("location") !== -1) {
            return "New-York";
        }

        if (normalized.indexOf("color") !== -1 || normalized.indexOf("colour") !== -1) {
            return "Black";
        }

        if (normalized.indexOf("size") !== -1) {
            return "S";
        }

        if (normalized.indexOf("brand") !== -1) {
            return "Plugincy";
        }

        if (normalized.indexOf("store") !== -1 || normalized.indexOf("warehouse") !== -1) {
            return "Main Store";
        }

        return "Sample value";
    }

    function fillMetaBuilderSelect(select, options, selectedValue) {
        select.innerHTML = "";
        options.forEach(function (optionData) {
            const option = document.createElement("option");
            option.value = optionData.value;
            option.textContent = optionData.label;
            option.selected = optionData.value === selectedValue;
            select.appendChild(option);
        });

        if (selectedValue && !select.querySelector('option[value="' + selectedValue + '"]')) {
            const option = document.createElement("option");
            option.value = selectedValue;
            option.textContent = selectedValue;
            option.selected = true;
            select.appendChild(option);
        }
    }

    function createMetaBuilderRow(builder, rowData) {
        const row = document.createElement("div");
        const drag = document.createElement("button");
        const dragIcon = document.createElement("span");
        const select = document.createElement("select");
        const mode = document.createElement("select");
        const title = document.createElement("input");
        const remove = document.createElement("button");
        const removeIcon = document.createElement("span");
        const options = parseMetaBuilderOptions(builder);
        const key = rowData && rowData.key ? rowData.key : (options[0] ? options[0].value : "");
        const label = getMetaBuilderOptionLabel(options, key);

        row.className = "onepaqucpro-meta-builder__row";
        row.setAttribute("data-meta-builder-row", "1");
        row.setAttribute("draggable", "true");

        drag.type = "button";
        drag.className = "onepaqucpro-meta-builder__drag";
        drag.setAttribute("data-meta-builder-drag", "1");
        drag.setAttribute("aria-label", "Reorder meta data");
        dragIcon.className = "dashicons dashicons-menu";
        drag.appendChild(dragIcon);

        select.setAttribute("data-meta-builder-key", "1");
        fillMetaBuilderSelect(select, options, key);

        mode.setAttribute("data-meta-builder-mode", "1");
        [
            { value: "separate", label: "Separate" },
            { value: "combine", label: "Combine" }
        ].forEach(function (modeData) {
            const option = document.createElement("option");
            option.value = modeData.value;
            option.textContent = modeData.label;
            option.selected = (rowData && rowData.mode === "combine" ? "combine" : "separate") === modeData.value;
            mode.appendChild(option);
        });

        title.type = "text";
        title.setAttribute("data-meta-builder-title", "1");
        title.placeholder = "Display title";
        title.value = rowData && rowData.title ? rowData.title : normalizeMetaBuilderTitle(label);

        remove.type = "button";
        remove.className = "onepaqucpro-meta-builder__remove";
        remove.setAttribute("data-meta-builder-remove", "1");
        remove.setAttribute("aria-label", "Remove meta data");
        removeIcon.className = "dashicons dashicons-no-alt";
        remove.appendChild(removeIcon);

        row.appendChild(drag);
        row.appendChild(select);
        row.appendChild(mode);
        row.appendChild(title);
        row.appendChild(remove);
        bindMetaBuilderRow(builder, row);

        return row;
    }

    function updateMetaBuilderRowState(row) {
        const select = row.querySelector("[data-meta-builder-key]");
        const mode = row.querySelector("[data-meta-builder-mode]");
        const title = row.querySelector("[data-meta-builder-title]");
        const remove = row.querySelector("[data-meta-builder-remove]");
        const isVariationRow = select && select.value === variationMetaKey;
        const forcedVariationRow = isVariationRow && getControlValue("rmenu_floating_cart_show_variation_in_title") !== "1";
        const combinedVariationRow = isVariationRow && mode && mode.value === "combine";

        row.classList.toggle("is-variation-meta-row", !!isVariationRow);

        if (mode) {
            mode.disabled = !isVariationRow;
            mode.title = isVariationRow ? "Choose how variation attributes are displayed" : "Mode is only available for variations";
        }

        if (title) {
            title.hidden = isVariationRow && !combinedVariationRow;
            title.disabled = isVariationRow && !combinedVariationRow;
            if (combinedVariationRow && !title.value.trim()) {
                title.value = "Variations";
            }
        }

        if (remove) {
            remove.disabled = forcedVariationRow;
            remove.title = forcedVariationRow ? "Variations are required while variation titles are disabled" : "";
        }
    }

    function syncMetaBuilder(builder) {
        const hidden = builder.querySelector("[data-floating-cart-control]");
        const rows = Array.prototype.slice.call(builder.querySelectorAll("[data-meta-builder-row]"));
        rows.forEach(updateMetaBuilderRowState);
        const data = rows.map(function (row) {
            const select = row.querySelector("[data-meta-builder-key]");
            const mode = row.querySelector("[data-meta-builder-mode]");
            const title = row.querySelector("[data-meta-builder-title]");
            const key = select ? select.value : "";
            const isVariationRow = key === variationMetaKey;

            return {
                key: key,
                mode: isVariationRow && mode && mode.value === "combine" ? "combine" : "separate",
                title: title && (!isVariationRow || (mode && mode.value === "combine")) ? title.value.trim() : ""
            };
        }).filter(function (row) {
            return row.key && row.key !== "mulopimfwc_location";
        });

        builder.classList.toggle("is-empty", data.length === 0);

        if (!hidden) {
            return;
        }

        hidden.value = JSON.stringify(data);
        hidden.dispatchEvent(new Event("input", { bubbles: true }));
        hidden.dispatchEvent(new Event("change", { bubbles: true }));
        updateMetaPreview();
        updateGroupTitle();
    }

    function bindMetaBuilderRow(builder, row) {
        const select = row.querySelector("[data-meta-builder-key]");
        const mode = row.querySelector("[data-meta-builder-mode]");
        const title = row.querySelector("[data-meta-builder-title]");
        const remove = row.querySelector("[data-meta-builder-remove]");
        const options = parseMetaBuilderOptions(builder);

        if (row.getAttribute("data-meta-builder-row-ready") === "1") {
            return;
        }

        row.setAttribute("data-meta-builder-row-ready", "1");
        updateMetaBuilderRowState(row);

        if (select) {
            select.addEventListener("change", function () {
                if (title && select.value === variationMetaKey) {
                    title.value = "Variations";
                } else if (title && !title.value.trim()) {
                    title.value = normalizeMetaBuilderTitle(getMetaBuilderOptionLabel(options, select.value));
                }
                updateMetaBuilderRowState(row);
                syncMetaBuilder(builder);
            });
        }

        if (mode) {
            mode.addEventListener("change", function () {
                updateMetaBuilderRowState(row);
                syncMetaBuilder(builder);
            });
        }

        if (title) {
            title.addEventListener("input", function () {
                syncMetaBuilder(builder);
            });
        }

        if (remove) {
            remove.addEventListener("click", function () {
                row.remove();
                syncMetaBuilder(builder);
            });
        }

        row.addEventListener("dragstart", function (event) {
            row.classList.add("is-dragging");
            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = "move";
            }
        });

        row.addEventListener("dragend", function () {
            row.classList.remove("is-dragging");
            syncMetaBuilder(builder);
        });
    }

    function getMetaBuilderDragAfterElement(container, y) {
        const rows = Array.prototype.slice.call(container.querySelectorAll("[data-meta-builder-row]:not(.is-dragging)"));

        return rows.reduce(function (closest, child) {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            }

            return closest;
        }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
    }

    function rebuildMetaBuilderRows(builder, rows) {
        const container = builder.querySelector("[data-meta-builder-rows]");

        if (!container) {
            return;
        }

        container.innerHTML = "";
        parseMetaBuilderValue(JSON.stringify(rows || [])).forEach(function (rowData) {
            container.appendChild(createMetaBuilderRow(builder, rowData));
        });
        syncMetaBuilder(builder);
    }

    function syncVariationMetaIncludeRequirement() {
        const control = getControl("rmenu_floating_cart_show_variation_in_title");
        const builder = editor.querySelector('[data-meta-builder="rmenu_floating_cart_meta_include"]');
        const container = builder ? builder.querySelector("[data-meta-builder-rows]") : null;

        if (!control || !builder || !container || getControlValue("rmenu_floating_cart_show_variation_in_title") === "1") {
            if (container) {
                Array.prototype.slice.call(container.querySelectorAll("[data-meta-builder-row]")).forEach(updateMetaBuilderRowState);
            }
            return;
        }

        const existing = Array.prototype.slice.call(container.querySelectorAll("[data-meta-builder-row]")).some(function (row) {
            return isVariationMetaRow(row);
        });

        if (!existing) {
            container.appendChild(createMetaBuilderRow(builder, getDefaultVariationMetaRule()));
            syncMetaBuilder(builder);
            return;
        }

        Array.prototype.slice.call(container.querySelectorAll("[data-meta-builder-row]")).forEach(updateMetaBuilderRowState);
    }

    function initMetaBuilders(root) {
        root.querySelectorAll("[data-meta-builder]").forEach(function (builder) {
            const container = builder.querySelector("[data-meta-builder-rows]");
            const add = builder.querySelector("[data-meta-builder-add]");
            const hidden = builder.querySelector("[data-floating-cart-control]");

            if (!container || builder.getAttribute("data-meta-builder-ready") === "1") {
                return;
            }

            builder.setAttribute("data-meta-builder-ready", "1");
            Array.prototype.slice.call(container.querySelectorAll("[data-meta-builder-row]")).forEach(function (row) {
                bindMetaBuilderRow(builder, row);
            });

            if (!container.querySelector("[data-meta-builder-row]") && hidden && hidden.value) {
                rebuildMetaBuilderRows(builder, parseMetaBuilderValue(hidden.value));
            }

            syncVariationMetaIncludeRequirement();

            container.addEventListener("dragover", function (event) {
                const dragging = container.querySelector(".is-dragging");
                if (!dragging) {
                    return;
                }

                event.preventDefault();
                const afterElement = getMetaBuilderDragAfterElement(container, event.clientY);
                if (afterElement === null) {
                    container.appendChild(dragging);
                } else {
                    container.insertBefore(dragging, afterElement);
                }
            });

            if (add) {
                add.addEventListener("click", function () {
                    container.appendChild(createMetaBuilderRow(builder, {}));
                    syncMetaBuilder(builder);
                });
            }

            syncMetaBuilder(builder);
        });
    }

    function buildSectionTitle(body, title) {
        const heading = document.createElement("h4");

        heading.className = "onepaqucpro-floating-edit-section-title";
        heading.textContent = title;
        body.appendChild(heading);
    }

    function getModalControlValue(body, field) {
        const modalControl = body.querySelector('[data-modal-control-field="' + field + '"]');

        if (modalControl) {
            if (modalControl.type === "checkbox") {
                return modalControl.checked ? "1" : "0";
            }

            return modalControl.value;
        }

        return getControlValue(field);
    }

    function setModalWrapVisible(body, field, visible) {
        body.querySelectorAll('[data-modal-control-wrap="' + field + '"]').forEach(function (wrap) {
            wrap.hidden = !visible;
        });
    }

    function updateEditModalDependencies(body) {
        const selectedEnabled = activeVisualField ? getModalControlValue(body, activeVisualField) === "1" : true;

        if (activeVisualField === "rmenu_floating_cart_show_item_meta") {
            setModalWrapVisible(body, "rmenu_floating_cart_meta_include", selectedEnabled);
        }

        if (activeVisualField === "rmenu_floating_cart_show_empty_icon") {
            setModalWrapVisible(body, "rmenu_floating_cart_empty_icon", selectedEnabled);
        }

        if (activeVisualField === "rmenu_floating_cart_show_summary" || activeVisualField === "rmenu_floating_cart_show_total") {
            const summaryCollapsible = getModalControlValue(body, "rmenu_floating_cart_summary_collapsible") === "1";
            setModalWrapVisible(body, "rmenu_floating_cart_summary_collapsible", selectedEnabled);
            setModalWrapVisible(body, "rmenu_floating_cart_summary_initially_collapsed", selectedEnabled && summaryCollapsible);
        }

        if (activeVisualField === "rmenu_floating_cart_group_items") {
            const groupByControl = body.querySelector('[data-modal-control-field="rmenu_floating_cart_group_by"]');
            const groupBy = groupByControl ? groupByControl.value : getModalControlValue(body, "rmenu_floating_cart_group_by");
            setModalWrapVisible(body, "rmenu_floating_cart_group_by", selectedEnabled);
            setModalWrapVisible(body, "rmenu_floating_cart_group_icon", selectedEnabled);
            setModalWrapVisible(body, "rmenu_floating_cart_group_meta_key", selectedEnabled && groupBy === "meta");
        }

        if (activeVisualField === "rmenu_floating_cart_show_shipping_options") {
            setModalWrapVisible(body, "rmenu_floating_cart_shipping_options_label", selectedEnabled);
        }

        if (activeVisualField === "rmenu_floating_cart_show_checkout") {
            setModalWrapVisible(body, "rmenu_cart_checkout_behavior", selectedEnabled);
            setModalWrapVisible(body, "txt_checkout", selectedEnabled);
        }
    }

    function syncModalControlsToSource(body) {
        body.querySelectorAll("[data-modal-control-field]").forEach(function (modalControl) {
            const field = modalControl.getAttribute("data-modal-control-field");
            const sourceControl = getControl(field);

            if (!sourceControl || sourceControl === modalControl || modalControl.disabled || sourceControl.disabled) {
                return;
            }

            if (modalControl.type === "checkbox") {
                if (sourceControl.type === "checkbox" && sourceControl.checked !== modalControl.checked) {
                    sourceControl.checked = modalControl.checked;
                    sourceControl.dispatchEvent(new Event("change", { bubbles: true }));
                }
                return;
            }

            if (sourceControl.value !== modalControl.value) {
                sourceControl.value = modalControl.value;
                sourceControl.dispatchEvent(new Event("input", { bubbles: true }));
                sourceControl.dispatchEvent(new Event("change", { bubbles: true }));
            }
        });
    }

    function refreshEditModalDependencies(body) {
        syncModalControlsToSource(body);
        updateEditModalDependencies(body);
    }

    function stopEditModalDependencyMonitor() {
        if (editModalDependencyTimer) {
            window.clearInterval(editModalDependencyTimer);
            editModalDependencyTimer = null;
        }
    }

    function startEditModalDependencyMonitor(body) {
        stopEditModalDependencyMonitor();
        refreshEditModalDependencies(body);
        editModalDependencyTimer = window.setInterval(function () {
            if (editModal.hidden) {
                stopEditModalDependencyMonitor();
                return;
            }

            refreshEditModalDependencies(body);
        }, 150);
    }

    function closeEditModal() {
        editModal.hidden = true;
        stopEditModalDependencyMonitor();
    }

    function bindEditModalDependencies(body) {
        body.querySelectorAll("[data-modal-control-field]").forEach(function (control) {
            control.addEventListener("input", function () {
                refreshEditModalDependencies(body);
            });
            control.addEventListener("change", function () {
                refreshEditModalDependencies(body);
                window.setTimeout(function () {
                    refreshEditModalDependencies(body);
                }, 0);
            });
        });
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
        const isProductTitle = activeVisualField === "rmenu_floating_cart_show_product_title";
        const isItemMeta = activeVisualField === "rmenu_floating_cart_show_item_meta";
        const isEmptyIcon = activeVisualField === "rmenu_floating_cart_show_empty_icon";
        const isSummary = activeVisualField === "rmenu_floating_cart_show_summary";
        const isTotalRow = activeVisualField === "rmenu_floating_cart_show_total";
        const isGroup = activeVisualField === "rmenu_floating_cart_group_items" || activeVisualNode.matches("[data-preview-group-icon]") || activeVisualNode.querySelector("[data-preview-group-icon]");

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

        if (isProductTitle) {
            buildControlSetting(body, "rmenu_floating_cart_show_variation_in_title");
        }

        if (isItemMeta) {
            buildControlSetting(body, "rmenu_floating_cart_meta_include");
        }

        if (isEmptyIcon) {
            buildControlSetting(body, "rmenu_floating_cart_empty_icon");
        }

        if (isSummary) {
            buildControlSetting(body, "rmenu_floating_cart_summary_collapsible");
            buildControlSetting(body, "rmenu_floating_cart_summary_initially_collapsed");
        }

        if (isGroup) {
            buildControlSetting(body, "rmenu_floating_cart_group_by");
            buildControlSetting(body, "rmenu_floating_cart_group_meta_key");
            buildControlSetting(body, "rmenu_floating_cart_group_icon");
        }

        textNodes.forEach(function (node) {
            buildTextSetting(body, node);
        });

        if (isTotalRow) {
            buildControlSetting(body, "rmenu_floating_cart_summary_collapsible");
            buildControlSetting(body, "rmenu_floating_cart_summary_initially_collapsed");
        }

        if (!body.children.length) {
            const empty = document.createElement("p");
            empty.className = "description";
            empty.textContent = "No editable settings are mapped to this element.";
            body.appendChild(empty);
        }

        editModal.hidden = false;
        bindEditModalDependencies(body);
        startEditModalDependencyMonitor(body);
        window.setTimeout(function () {
            refreshEditModalDependencies(body);
        }, 0);
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

    function updateEmptyIcon() {
        const value = getControlValue("rmenu_floating_cart_empty_icon") || "cart";
        const iconSvg = emptyIconSvgMap[value] || emptyIconSvgMap.cart;

        editor.querySelectorAll("[data-preview-empty-icon]").forEach(function (icon) {
            icon.innerHTML = iconSvg;
        });
    }

    function syncSummaryPreviewState() {
        const summary = editor.querySelector(".onepaqucpro-floating-preview-summary");

        if (!summary) {
            return;
        }

        const summaryEnabled = getControlValue("rmenu_floating_cart_show_summary") === "1";
        const collapsible = summaryEnabled && getControlValue("rmenu_floating_cart_summary_collapsible") === "1";
        const collapsed = collapsible && getControlValue("rmenu_floating_cart_summary_initially_collapsed") === "1";

        summary.classList.toggle("is-summary-collapsible", collapsible);
        summary.classList.toggle("is-summary-collapsed", collapsed);

        summary.querySelectorAll("[data-preview-summary-toggle]").forEach(function (toggle) {
            toggle.hidden = !summaryEnabled || getControlValue("rmenu_floating_cart_show_total") !== "1";
            toggle.setAttribute("aria-expanded", collapsed ? "false" : "true");
        });

        summary.querySelectorAll("[data-preview-summary-content]").forEach(function (content) {
            content.setAttribute("aria-hidden", collapsed ? "true" : "false");
        });
    }

    function updateGroupIcon() {
        const value = getControlValue("rmenu_floating_cart_group_icon") || "tag";
        const icons = {
            none: "",
            tag: "●",
            folder: "■",
            star: "★"
        };
        const locationIcon = '<svg class="onepaqucpro-floating-preview-group-icon-svg" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>';

        editor.querySelectorAll("[data-preview-group-icon]").forEach(function (icon) {
            if (value === "location") {
                icon.innerHTML = locationIcon;
                return;
            }

            icon.textContent = icons[value] || icons.tag;
        });
    }

    function updateGroupTitle() {
        const titleNodes = editor.querySelectorAll("[data-preview-group-title]");
        const groupBy = getControlValue("rmenu_floating_cart_group_by") || "category";
        let title = "Gaming Accessories";

        if (groupBy === "brand") {
            title = "Plugincy";
        } else if (groupBy === "meta") {
            const metaKey = getControlValue("rmenu_floating_cart_group_meta_key");
            title = metaKey ? getPreviewMetaSampleValue(metaKey) : "Choose a meta key";
        }

        titleNodes.forEach(function (node) {
            node.textContent = title;
        });
    }

    function variationInTitleEnabled() {
        const control = getControl("rmenu_floating_cart_show_variation_in_title");

        return !control || getControlValue("rmenu_floating_cart_show_variation_in_title") === "1";
    }

    function updateVariationPlacementPreview() {
        const showVariationInTitle = variationInTitleEnabled();

        editor.querySelectorAll("[data-preview-variation-title]").forEach(function (node) {
            const baseTitle = node.getAttribute("data-preview-title-base") || "B100 Pro Wireless Gaming Headset";
            const variationTitle = node.getAttribute("data-preview-title-variation") || "Black";

            node.textContent = showVariationInTitle && variationTitle ? baseTitle + " - " + variationTitle : baseTitle;
        });
    }

    function updateMetaPreview() {
        const metaLists = editor.querySelectorAll("[data-preview-meta-list]");
        const rules = parseMetaBuilderValue(getControlValue("rmenu_floating_cart_meta_include"));
        const itemMetaEnabled = getControlValue("rmenu_floating_cart_show_item_meta") === "1";
        const showVariationInTitle = variationInTitleEnabled();
        let previewRules = rules.filter(function (rule) {
            return !(showVariationInTitle && rule.key === variationMetaKey);
        });

        if (!showVariationInTitle && !previewRules.some(function (rule) { return rule.key === variationMetaKey; })) {
            previewRules.push(getDefaultVariationMetaRule());
        }

        metaLists.forEach(function (list) {
            list.innerHTML = "";
            list.classList.toggle("is-preview-hidden", !itemMetaEnabled);

            if (!itemMetaEnabled) {
                return;
            }

            if (!previewRules.length) {
                if (!showVariationInTitle) {
                    return;
                }

                const placeholder = document.createElement("div");
                placeholder.className = "onepaqucpro-floating-preview-meta-placeholder";
                placeholder.textContent = "No meta data selected.";
                list.appendChild(placeholder);
                return;
            }

            previewRules.forEach(function (rule) {
                if (rule.key === variationMetaKey) {
                    if (showVariationInTitle) {
                        return;
                    }

                    if (rule.mode === "combine") {
                        const row = document.createElement("div");
                        const dt = document.createElement("dt");
                        const dd = document.createElement("dd");

                        row.setAttribute("data-preview-variation-meta", "");
                        dt.textContent = rule.title || "Variations";
                        dd.textContent = "Black, S";
                        row.appendChild(dt);
                        row.appendChild(dd);
                        list.appendChild(row);
                        return;
                    }

                    [
                        { key: "Color", value: "Black" },
                        { key: "Size", value: "S" }
                    ].forEach(function (variationRule) {
                        const row = document.createElement("div");
                        const dt = document.createElement("dt");
                        const dd = document.createElement("dd");

                        row.setAttribute("data-preview-variation-meta", "");
                        dt.textContent = variationRule.key;
                        dd.textContent = variationRule.value;
                        row.appendChild(dt);
                        row.appendChild(dd);
                        list.appendChild(row);
                    });
                    return;
                }

                const row = document.createElement("div");
                const dt = document.createElement("dt");
                const dd = document.createElement("dd");
                const title = rule.title || formatMetaLabel(rule.key);

                dt.textContent = title;
                dd.textContent = getPreviewMetaSampleValue(rule.key);
                row.appendChild(dt);
                row.appendChild(dd);
                list.appendChild(row);
            });
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
        function setControlWrapVisible(field, visible) {
            editor.querySelectorAll('[data-floating-control-wrap="' + field + '"]').forEach(function (wrap) {
                wrap.hidden = !visible;
                wrap.classList.toggle("is-dependency-hidden", !visible);
            });
        }

        const itemSelectEnabled = getControlValue("rmenu_floating_cart_show_item_select") === "1";
        const selectBarParts = editor.querySelectorAll('[data-preview-part="rmenu_floating_cart_show_select_bar"]');

        selectBarParts.forEach(function (part) {
            const selectBarEnabled = getControlValue("rmenu_floating_cart_show_select_bar") === "1";
            part.classList.toggle("is-preview-hidden", !itemSelectEnabled || !selectBarEnabled);
        });

        const summaryEnabled = getControlValue("rmenu_floating_cart_show_summary") === "1";
        const summaryCollapsible = getControlValue("rmenu_floating_cart_summary_collapsible") === "1";
        setControlWrapVisible("rmenu_floating_cart_summary_collapsible", summaryEnabled);
        setControlWrapVisible("rmenu_floating_cart_summary_initially_collapsed", summaryEnabled && summaryCollapsible);

        ["rmenu_floating_cart_show_subtotal", "rmenu_floating_cart_show_discount", "rmenu_floating_cart_show_shipping_total", "rmenu_floating_cart_show_tax_total", "rmenu_floating_cart_show_total"].forEach(function (field) {
            setControlWrapVisible(field, summaryEnabled);
            editor.querySelectorAll('[data-preview-part="' + field + '"]').forEach(function (part) {
                part.classList.toggle("is-preview-hidden", !summaryEnabled || getControlValue(field) !== "1");
            });
        });

        const itemMetaEnabled = getControlValue("rmenu_floating_cart_show_item_meta") === "1";
        setControlWrapVisible("rmenu_floating_cart_meta_include", itemMetaEnabled);

        const emptyIconEnabled = getControlValue("rmenu_floating_cart_show_empty_icon") === "1";
        setControlWrapVisible("rmenu_floating_cart_empty_icon", emptyIconEnabled);

        const groupingEnabled = getControlValue("rmenu_floating_cart_group_items") === "1";
        const groupByMeta = getControlValue("rmenu_floating_cart_group_by") === "meta";
        setControlWrapVisible("rmenu_floating_cart_group_by", groupingEnabled);
        setControlWrapVisible("rmenu_floating_cart_group_icon", groupingEnabled);
        setControlWrapVisible("rmenu_floating_cart_group_meta_key", groupingEnabled && groupByMeta);

        const shippingOptionsEnabled = getControlValue("rmenu_floating_cart_show_shipping_options") === "1";
        setControlWrapVisible("rmenu_floating_cart_shipping_options_label", shippingOptionsEnabled);

        const shippingTotalEnabled = summaryEnabled && getControlValue("rmenu_floating_cart_show_shipping_total") === "1";
        const taxTotalEnabled = summaryEnabled && getControlValue("rmenu_floating_cart_show_tax_total") === "1";
        setControlWrapVisible("rmenu_floating_cart_shipping_label", shippingTotalEnabled);
        setControlWrapVisible("rmenu_floating_cart_tax_label", taxTotalEnabled);
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
        updateEmptyIcon();
        syncSummaryPreviewState();
        updateGroupIcon();
        updateGroupTitle();
        updateVariationPlacementPreview();
        syncVariationMetaIncludeRequirement();
        updateMetaPreview();
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

    initMetaPickers(editor);
    initMetaBuilders(editor);

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
            closeEditModal();
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && !editModal.hidden) {
            closeEditModal();
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
