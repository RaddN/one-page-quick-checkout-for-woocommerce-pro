(function (blocks, element, components, blockEditor) {
    const { Fragment } = element;
    const { TextControl, SelectControl, RangeControl, PanelBody, TabPanel, ToggleControl } = components;
    const { InspectorControls, PanelColorSettings } = blockEditor;
    const el = element.createElement;
    const blockConfig = window.onepaqucproOnePageCheckoutBlock || {};
    const isLicenseActive = blockConfig.isLicenseActive !== false;
    const proTitle = blockConfig.proTitle || 'Pro version only.';
    const proMessage = blockConfig.proMessage || 'Multi Product One Page Checkout requires an active Pro license. Please activate your license to use this feature.';
    
    blocks.registerBlockType('plugincy/one-page-checkout', {
        title: 'Multi Product One Page Checkout',
        icon: 'onepaquc_one_page_cart',
        category: 'plugincy',
        keywords: [
            'Checkout',
            'One Page Checkout',
            'Plugincy',
            'WooCommerce',
            'Products',
        ],
        attributes: {
            product_ids: {
                type: 'string',
                default: '',
            },
            category: {
                type: 'string',
                default: '',
            },
            tags: {
                type: 'string',
                default: '',
            },
            attribute: {
                type: 'string',
                default: '',
            },
            terms: {
                type: 'string',
                default: '',
            },
            template: {
                type: 'string',
                default: 'product-tabs',
            },
            position: {
                type: 'string',
                default: 'after_description',
            },
            product_label: {
                type: 'string',
                default: 'Product',
            },
            variation_label: {
                type: 'string',
                default: 'Choose an option',
            },
            updating_selection_text: {
                type: 'string',
                default: 'Updating selection...',
            },
            show_images: {
                type: 'boolean',
                default: false,
            },
            product_layout: {
                type: 'string',
                default: 'select_dropdown',
            },
            // Style attributes
            borderRadius: {
                type: 'number',
                default: 4,
            },
            boxShadow: {
                type: 'boolean',
                default: false,
            },
            primaryColor: {
                type: 'string',
                default: '#4CAF50',
            },
            secondaryColor: {
                type: 'string',
                default: '#2196F3',
            },
            buttonStyle: {
                type: 'string',
                default: 'filled',
            },
            spacing: {
                type: 'number',
                default: 15,
            },
        },

        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { 
                product_ids, 
                category,
                tags,
                attribute,
                terms,
                template, 
                position,
                product_label,
                variation_label,
                updating_selection_text,
                show_images,
                product_layout,
                borderRadius, 
                boxShadow, 
                primaryColor, 
                secondaryColor, 
                buttonStyle, 
                spacing 
            } = attributes;

            // Template options
            const templateOptions = [
                { label: 'Product Table', value: 'product-table' },
                { label: 'Product List', value: 'product-list' },
                { label: 'Product Single', value: 'product-single' },
                { label: 'Product Slider', value: 'product-slider' },
                { label: 'Product Accordion', value: 'product-accordion' },
                { label: 'Product Tabs', value: 'product-tabs' },
                { label: 'Pricing Table', value: 'pricing-table' },
                { label: 'Product Selection', value: 'product-selection' },
            ];

            const positionOptions = [
                { label: 'After checkout description', value: 'after_description' },
                { label: 'Before order notes', value: 'before_order_notes' },
                { label: 'After checkout form', value: 'after_checkout' },
            ];

            const productLayoutOptions = [
                { label: 'Select Dropdown (Default)', value: 'select_dropdown' },
                { label: 'Cards in Dropdown', value: 'card_dropdown' },
                { label: 'Card & More', value: 'cards' },
            ];

            // Button style options
            const buttonStyleOptions = [
                { label: 'Filled', value: 'filled' },
                { label: 'Outlined', value: 'outlined' },
                { label: 'Text Only', value: 'text' },
            ];

            const colorSettings = [
                {
                    value: primaryColor,
                    onChange: (value) => setAttributes({ primaryColor: value || '#4CAF50' }),
                    label: 'Primary Color',
                },
                {
                    value: secondaryColor,
                    onChange: (value) => setAttributes({ secondaryColor: value || '#2196F3' }),
                    label: 'Secondary Color',
                },
            ];

            const renderCompactColorControls = () => el(
                PanelBody,
                {
                    title: 'Colors & Buttons',
                    initialOpen: false,
                },
                el(
                    'div',
                    {
                        className: 'plugincy-color-row',
                    },
                    el(
                        'span',
                        {
                            className: 'plugincy-color-row__swatch',
                            style: { backgroundColor: primaryColor || '#4CAF50' },
                            'aria-hidden': 'true',
                        }
                    ),
                    el(
                        TextControl,
                        {
                            label: 'Primary Color',
                            help: 'Main theme color for buttons and highlights.',
                            value: primaryColor || '',
                            onChange: (value) => setAttributes({ primaryColor: value }),
                        }
                    )
                ),
                el(
                    'div',
                    {
                        className: 'plugincy-color-row',
                    },
                    el(
                        'span',
                        {
                            className: 'plugincy-color-row__swatch',
                            style: { backgroundColor: secondaryColor || '#2196F3' },
                            'aria-hidden': 'true',
                        }
                    ),
                    el(
                        TextControl,
                        {
                            label: 'Secondary Color',
                            help: 'Accent color for secondary elements.',
                            value: secondaryColor || '',
                            onChange: (value) => setAttributes({ secondaryColor: value }),
                        }
                    )
                ),
                el(
                    SelectControl,
                    {
                        label: 'Button Style',
                        help: 'Choose the appearance of checkout buttons',
                        value: buttonStyle,
                        options: buttonStyleOptions,
                        onChange: (value) => setAttributes({ buttonStyle: value }),
                    }
                )
            );

            const renderColorControls = () => {
                if (PanelColorSettings) {
                    return el(
                        PanelColorSettings,
                        {
                            title: 'Colors & Buttons',
                            initialOpen: false,
                            colorSettings,
                        },
                        el(
                            SelectControl,
                            {
                                label: 'Button Style',
                                help: 'Choose the appearance of checkout buttons',
                                value: buttonStyle,
                                options: buttonStyleOptions,
                                onChange: (value) => setAttributes({ buttonStyle: value }),
                            }
                        )
                    );
                }

                return renderCompactColorControls();
            };

            // Generate shortcode preview
            const generateShortcode = () => {
                let shortcode = '[plugincy_one_page_checkout';
                
                if (product_ids) shortcode += ` product_ids="${product_ids}"`;
                if (category) shortcode += ` category="${category}"`;
                if (tags) shortcode += ` tags="${tags}"`;
                if (attribute) shortcode += ` attribute="${attribute}"`;
                if (terms) shortcode += ` terms="${terms}"`;
                if (template) shortcode += ` template="${template}"`;
                if (template === 'product-selection' && position) shortcode += ` position="${position}"`;
                if (template === 'product-selection' && product_label) shortcode += ` product_label="${product_label}"`;
                if (template === 'product-selection' && variation_label) shortcode += ` variation_label="${variation_label}"`;
                if (template === 'product-selection' && updating_selection_text) shortcode += ` updating_selection_text="${updating_selection_text}"`;
                if (template === 'product-selection' && show_images) shortcode += ' show_images="yes"';
                if (template === 'product-selection' && show_images && product_layout) shortcode += ` product_layout="${product_layout}"`;
                if (template === 'product-selection' && primaryColor) shortcode += ` primary_color="${primaryColor}"`;
                if (template === 'product-selection' && secondaryColor) shortcode += ` secondary_color="${secondaryColor}"`;
                if (template === 'product-selection' && borderRadius !== undefined) shortcode += ` border_radius="${borderRadius}"`;
                if (template === 'product-selection' && spacing !== undefined) shortcode += ` spacing="${spacing}"`;
                if (template === 'product-selection' && buttonStyle) shortcode += ` button_style="${buttonStyle}"`;
                
                shortcode += ']';
                return shortcode;
            };

            return el(
                Fragment,
                {},
                el(
                    InspectorControls,
                    {},
                    el(
                        TabPanel,
                        {
                            className: 'plugincy-tabs',
                            activeClass: 'active-tab',
                            tabs: [
                                {
                                    name: 'general',
                                    title: 'General',
                                    className: 'tab-general',
                                },
                                {
                                    name: 'products',
                                    title: 'Product Quary',
                                    className: 'tab-products',
                                },
                                {
                                    name: 'style',
                                    title: 'Style',
                                    className: 'tab-style',
                                },
                            ],
                        },
                        (tab) => {
                            if (tab.name === 'general') {
                                return el(
                                    Fragment,
                                    {},
                                    el(
                                        PanelBody,
                                        { 
                                            title: 'Template Settings',
                                            initialOpen: true 
                                        },
                                        el(
                                            SelectControl,
                                            {
                                                label: 'Display Template',
                                                help: 'Choose how products will be displayed on the checkout page',
                                                value: template,
                                                options: templateOptions,
                                                onChange: (value) => setAttributes({ template: value }),
                                            }
                                        ),
                                        template === 'product-selection' && el(
                                            SelectControl,
                                            {
                                                label: 'Product Selection Position',
                                                help: 'Choose where the product selector appears inside the checkout form.',
                                                value: position,
                                                options: positionOptions,
                                                onChange: (value) => setAttributes({ position: value }),
                                            }
                                        ),
                                        template === 'product-selection' && el(
                                            ToggleControl,
                                            {
                                                label: 'Show Product & Variation Images',
                                                help: 'Show the selected product image and each variation thumbnail.',
                                                checked: !!show_images,
                                                onChange: (value) => setAttributes({ show_images: !!value }),
                                            }
                                        ),
                                        template === 'product-selection' && show_images && el(
                                            SelectControl,
                                            {
                                                label: 'Product Layout',
                                                help: 'Choose how products appear when images are enabled.',
                                                value: product_layout,
                                                options: productLayoutOptions,
                                                onChange: (value) => setAttributes({ product_layout: value }),
                                            }
                                        )
                                    ),
                                    template === 'product-selection' && el(
                                        PanelBody,
                                        {
                                            title: 'Text Management',
                                            initialOpen: false
                                        },
                                        el(
                                            TextControl,
                                            {
                                                label: 'Product Label',
                                                help: 'Text shown above the product dropdown.',
                                                value: product_label,
                                                onChange: (value) => setAttributes({ product_label: value }),
                                            }
                                        ),
                                        el(
                                            TextControl,
                                            {
                                                label: 'Variation Label',
                                                help: 'Text shown above the variation options.',
                                                value: variation_label,
                                                onChange: (value) => setAttributes({ variation_label: value }),
                                            }
                                        ),
                                        el(
                                            TextControl,
                                            {
                                                label: 'Updating Selection Text',
                                                help: 'Status text shown while the selected product or variation is updating.',
                                                value: updating_selection_text,
                                                onChange: (value) => setAttributes({ updating_selection_text: value }),
                                            }
                                        )
                                    )
                                );
                            } else if (tab.name === 'products') {
                                return el(
                                    Fragment,
                                    {},
                                    el(
                                        PanelBody,
                                        { 
                                            title: 'By Product IDs',
                                            initialOpen: true 
                                        },
                                        el(
                                            TextControl,
                                            {
                                                label: 'Product IDs',
                                                help: 'Enter specific product IDs separated by commas (e.g., 152,153,151,142)',
                                                placeholder: '152,153,151,142',
                                                value: product_ids,
                                                onChange: (value) => setAttributes({ product_ids: value }),
                                            }
                                        )
                                    ),
                                    el(
                                        PanelBody,
                                        { 
                                            title: 'By Category & Tag',
                                            initialOpen: false 
                                        },
                                        el(
                                            TextControl,
                                            {
                                                label: 'Product Categories',
                                                help: 'Enter category slugs separated by commas (e.g., electronics,clothing)',
                                                placeholder: 'electronics,clothing',
                                                value: category,
                                                onChange: (value) => setAttributes({ category: value }),
                                            }
                                        ),
                                        el(
                                            TextControl,
                                            {
                                                label: 'Product Tags',
                                                help: 'Enter tag slugs separated by commas (e.g., featured,sale)',
                                                placeholder: 'featured,sale',
                                                value: tags,
                                                onChange: (value) => setAttributes({ tags: value }),
                                            }
                                        )
                                    ),
                                    el(
                                        PanelBody,
                                        { 
                                            title: ' By Attribute',
                                            initialOpen: false 
                                        },
                                        el(
                                            TextControl,
                                            {
                                                label: 'Product Attribute',
                                                help: 'Enter attribute name (e.g., color, size, brand)',
                                                placeholder: 'color',
                                                value: attribute,
                                                onChange: (value) => setAttributes({ attribute: value }),
                                            }
                                        ),
                                        el(
                                            TextControl,
                                            {
                                                label: 'Attribute Terms',
                                                help: 'Enter attribute terms separated by commas (e.g., red,blue,green)',
                                                placeholder: 'red,blue,green',
                                                value: terms,
                                                onChange: (value) => setAttributes({ terms: value }),
                                            }
                                        )
                                    )
                                );
                            } else if (tab.name === 'style') {
                                return el(
                                    Fragment,
                                    {},
                                    el(
                                        PanelBody,
                                        { 
                                            title: 'Layout & Spacing',
                                            initialOpen: true 
                                        },
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Border Radius',
                                                help: 'Adjust the rounded corners of elements',
                                                value: borderRadius,
                                                onChange: (value) => setAttributes({ borderRadius: value }),
                                                min: 0,
                                                max: 50,
                                                step: 1,
                                                marks: [
                                                    { value: 0, label: '0' },
                                                    { value: 25, label: '25' },
                                                    { value: 50, label: '50' }
                                                ],
                                            }
                                        ),
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Element Spacing',
                                                help: 'Control the spacing between elements',
                                                value: spacing,
                                                onChange: (value) => setAttributes({ spacing: value }),
                                                min: 0,
                                                max: 50,
                                                step: 1,
                                                marks: [
                                                    { value: 0, label: '0' },
                                                    { value: 25, label: '25' },
                                                    { value: 50, label: '50' }
                                                ],
                                            }
                                        ),
                                        el(
                                            ToggleControl,
                                            {
                                                label: 'Enable Box Shadow',
                                                help: 'Add subtle shadow effects to elements',
                                                checked: boxShadow,
                                                onChange: () => setAttributes({ boxShadow: !boxShadow }),
                                            }
                                        )
                                    ),
                                    renderColorControls()
                                );
                            }
                        }
                    )
                ),
                // Block Preview
                el(
                    'div',
                    { 
                        className: 'plugincy-block-preview',
                        style: {
                            padding: '20px',
                            backgroundColor: '#f8f9fa',
                            border: '1px solid #e9ecef',
                            borderRadius: borderRadius + 'px',
                            margin: '20px 0',
                            boxShadow: boxShadow ? '0 2px 8px rgba(0,0,0,0.1)' : 'none'
                        }
                    },                    
                    !isLicenseActive ? el(
                        'div',
                        {
                            style: {
                                backgroundColor: '#fff8e5',
                                border: '1px solid #f0c36d',
                                borderLeft: '4px solid #d97706',
                                borderRadius: '4px',
                                color: '#1f2937',
                                padding: '16px 18px'
                            }
                        },
                        el('strong', {
                            style: {
                                color: '#92400e',
                                display: 'block',
                                marginBottom: '6px'
                            }
                        }, proTitle),
                        el('span', {}, proMessage)
                    ) : el('div', {
                        style: {
                            backgroundColor: '#fff',
                            padding: '15px',
                            borderRadius: (borderRadius - 2) + 'px',
                            border: '1px solid #dee2e6',
                            fontSize: '13px',
                            fontFamily: 'monospace',
                            color: '#495057',
                            lineHeight: '1.4',
                            wordBreak: 'break-all'
                        }
                    }, generateShortcode()),                    
                )
            );
        },

        save: function(props) {
            const { attributes } = props;
            const { 
                product_ids, 
                category,
                tags,
                attribute,
                terms,
                template,
                position,
                product_label,
                variation_label,
                updating_selection_text,
                show_images,
                product_layout,
                borderRadius,
                primaryColor,
                secondaryColor,
                buttonStyle,
                spacing
            } = attributes;

            // Generate the shortcode with current attributes
            let shortcode = '[plugincy_one_page_checkout';
            
            if (product_ids) shortcode += ` product_ids="${product_ids}"`;
            if (category) shortcode += ` category="${category}"`;
            if (tags) shortcode += ` tags="${tags}"`;
            if (attribute) shortcode += ` attribute="${attribute}"`;
            if (terms) shortcode += ` terms="${terms}"`;
            if (template) shortcode += ` template="${template}"`;
            if (template === 'product-selection' && position) shortcode += ` position="${position}"`;
            if (template === 'product-selection' && product_label) shortcode += ` product_label="${product_label}"`;
            if (template === 'product-selection' && variation_label) shortcode += ` variation_label="${variation_label}"`;
            if (template === 'product-selection' && updating_selection_text) shortcode += ` updating_selection_text="${updating_selection_text}"`;
            if (template === 'product-selection' && show_images) shortcode += ' show_images="yes"';
            if (template === 'product-selection' && show_images && product_layout) shortcode += ` product_layout="${product_layout}"`;
            if (template === 'product-selection' && primaryColor) shortcode += ` primary_color="${primaryColor}"`;
            if (template === 'product-selection' && secondaryColor) shortcode += ` secondary_color="${secondaryColor}"`;
            if (template === 'product-selection' && borderRadius !== undefined) shortcode += ` border_radius="${borderRadius}"`;
            if (template === 'product-selection' && spacing !== undefined) shortcode += ` spacing="${spacing}"`;
            if (template === 'product-selection' && buttonStyle) shortcode += ` button_style="${buttonStyle}"`;
            
            shortcode += ']';

            // Return the shortcode as static content
            return shortcode;
        },
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor
);
