/******/ (() => { // webpackBootstrap
/******/ 	"use strict";

;// ./source/admin/js/ace-editor.js
/**
 * Ace Editor.
 * 
 * The settings page uses a CSS code editor for custom CSS.
 * Initiate the 'Ace Editor' so that it shows as CSS syntax.
 */
function ace_editor_init() {
  if (!jQuery('#styles_checkout_custom_css_ace_editor').length) {
    return;
  }
  var editor = ace.edit('styles_checkout_custom_css_ace_editor');
  var textarea = document.getElementById('styles_checkout_custom_css');
  editor.setTheme("ace/theme/chrome");
  editor.session.setMode("ace/mode/css");
  editor.setOptions({
    showPrintMargin: false
  });
  editor.getSession().setValue(textarea.value);
  editor.getSession().on('change', function () {
    textarea.value = editor.getSession().getValue();
  });
}
;// ./source/admin/js/dynamic-settings.js
/**
 * Header Type.
 * 
 * Show and hides controls based on the Header Type chosen.
 */
function header_type_init() {
  var header_type = document.getElementsByClassName('header-type');
  if (!header_type.length) {
    return;
  }
  Array.from(header_type).forEach(function (object) {
    var header_type_text_elements = object.closest('.wpsf-tab').getElementsByClassName('header-type--text');
    var header_type_image_elements = object.closest('.wpsf-tab').getElementsByClassName('header-type--image');
    if (!object.classList.contains('flux-field-init')) {
      object.classList.add('flux-field-init');
      object.addEventListener('change', function () {
        header_type_init();
        modern_colour_options_init();
      });
    }
    Array.from(header_type_text_elements).forEach(function (el) {
      el.closest('tr').style.display = object.value === 'text' ? '' : 'none';
    });
    Array.from(header_type_image_elements).forEach(function (el) {
      el.closest('tr').style.display = object.value === 'image' ? '' : 'none';
    });
  });
}

/**
 * Header Background.
 * 
 * Show and hides controls based on the Header Background chosen.
 */
function header_background_init() {
  var header_background = document.querySelectorAll('.header-background');
  var header_background_checked = document.querySelectorAll('.header-background:checked');
  if (!header_background_checked.length) {
    return;
  }
  Array.from(header_background).forEach(function (object) {
    if (!object.classList.contains('flux-field-init')) {
      object.classList.add('flux-field-init');
      object.addEventListener('change', function () {
        header_background_init();
        modern_colour_options_init();
      });
    }
  });
  Array.from(header_background_checked).forEach(function (object) {
    var header_background_custom_elements = object.closest('.wpsf-tab').getElementsByClassName('header-background--custom');
    var header_background_gradient_elements = object.closest('.wpsf-tab').getElementsByClassName('header-background--gradient');
    Array.from(header_background_custom_elements).forEach(function (el) {
      el.closest('tr').style.display = object.value === 'custom' ? '' : 'none';
    });
    Array.from(header_background_gradient_elements).forEach(function (el) {
      console.log(el.closest('tr'));
      console.log(object.value === 'gradient' ? '' : 'none');
      el.closest('tr').style.display = object.value === 'gradient' ? '' : 'none';
    });
  });
}

/**
 * Colour Type.
 * 
 * Show and hides controls based on the Colour Type chosen.
 */
function colour_type_init() {
  var colour_type = document.querySelectorAll('.colour-type');
  var colour_type_checked = document.querySelectorAll('.colour-type:checked');
  if (!colour_type_checked.length) {
    return;
  }

  // Add event listner only once.
  Array.from(colour_type).forEach(function (object) {
    if (!object.classList.contains('flux-field-init')) {
      object.classList.add('flux-field-init');
      object.addEventListener('change', function () {
        colour_type_init();
      });
    }
  });
  Array.from(colour_type_checked).forEach(function (object) {
    var colour_type_palette_elements = object.closest('.wpsf-tab').getElementsByClassName('colour-type--palette');
    var colour_type_primary_elements = object.closest('.wpsf-tab').getElementsByClassName('colour-type--primary');
    Array.from(colour_type_palette_elements).forEach(function (el) {
      el.closest('tr').style.display = object.value === 'mdl' ? '' : 'none';
    });
    Array.from(colour_type_primary_elements).forEach(function (el) {
      el.closest('tr').style.display = object.value === 'custom' ? '' : 'none';
    });
  });
}

/**
 * Modern Colour Options.
 * 
 * Alter the colour options available for the modern theme.
 */
function modern_colour_options_init() {
  let theme = jQuery('.flux-theme-type:checked').val();
  if ('modern' !== theme) {
    return;
  }
  document.getElementById('styles_header_header_font_family').closest('tr').style.display = 'none';
  document.getElementById('styles_header_header_font_size').closest('tr').style.display = 'none';
  document.getElementById('styles_header_header_font_colour').closest('tr').style.display = 'none';
  document.getElementById('styles_header_header_background_primary-color').closest('tr').style.display = 'none';
  document.getElementById('styles_header_background').closest('tr').style.display = 'none';
  document.getElementById('styles_checkout_primary_color_#f44336').closest('tr').style.display = 'none';
  document.getElementById('styles_checkout_accent_color_#f44336').closest('tr').style.display = 'none';
  document.getElementById('styles_checkout_custom_primary_color').closest('tr').style.display = 'none';
  document.getElementById('styles_checkout_custom_accent_color').closest('tr').style.display = 'none';
  document.getElementById('styles_checkout_modern_custom_placeholder_color').closest('tr').style.display = '';
  document.getElementById('styles_checkout_modern_custom_link_color').closest('tr').style.display = '';
  document.getElementById('styles_checkout_modern_custom_primary_button_color').closest('tr').style.display = '';
  document.getElementById('styles_checkout_modern_custom_secondary_button_color').closest('tr').style.display = '';
  document.getElementById('styles_header_cart_icon_color').closest('tr').style.display = 'none';
  document.getElementById('styles_checkout_use_custom_colors_custom').closest('tr').style.display = 'none';
  document.getElementById('styles_theme_show_sidebar').closest('tr').style.display = 'none';
}
function option_reset_init() {
  let theme = jQuery('.flux-theme-type:checked').val();
  if (!jQuery('.flux-theme-type').hasClass('flux-field-init')) {
    jQuery('.flux-theme-type').addClass('flux-field-init');
    jQuery('.flux-theme-type').change(option_reset_init);
    jQuery('.flux-theme-type').change(modern_colour_options_init);
  }
  if ('modern' === theme) {
    return;
  }
  document.getElementById('styles_header_header_font_family').closest('tr').style.display = '';
  document.getElementById('styles_header_header_font_size').closest('tr').style.display = '';
  document.getElementById('styles_header_header_font_colour').closest('tr').style.display = '';
  document.getElementById('styles_header_header_background_primary-color').closest('tr').style.display = '';
  document.getElementById('styles_header_background').closest('tr').style.display = '';
  document.getElementById('styles_checkout_primary_color_#f44336').closest('tr').style.display = '';
  document.getElementById('styles_checkout_accent_color_#f44336').closest('tr').style.display = '';
  document.getElementById('styles_checkout_custom_primary_color').closest('tr').style.display = '';
  document.getElementById('styles_checkout_custom_accent_color').closest('tr').style.display = '';
  document.getElementById('styles_checkout_modern_custom_placeholder_color').closest('tr').style.display = 'none';
  document.getElementById('styles_checkout_modern_custom_link_color').closest('tr').style.display = 'none';
  document.getElementById('styles_checkout_modern_custom_primary_button_color').closest('tr').style.display = 'none';
  document.getElementById('styles_checkout_modern_custom_secondary_button_color').closest('tr').style.display = 'none';
  document.getElementById('styles_header_cart_icon_color').closest('tr').style.display = '';
  document.getElementById('styles_checkout_use_custom_colors_custom').closest('tr').style.display = '';
  document.getElementById('styles_theme_show_sidebar').closest('tr').style.display = '';
}

/**
 * Initialize gradient picker field.
 */
function init_gradient_field() {
  const option_html = function (data) {
    var gradient = jQuery(data.element).attr('value');
    var css = `background: linear-gradient(to left, ${gradient} )`;
    return `<span class='flux-gradient-preview-dot' style='${css}'></span> ${data.text}`;
  };
  jQuery("#styles_header_background").select2({
    templateSelection: option_html,
    templateResult: option_html,
    escapeMarkup: function (m) {
      return m;
    }
  });
}
;// ./source/admin/js/checkout-elements-metabox.js
function checkout_elements_init() {
  var fce = Vue.createApp({
    el: ".flux-ce",
    data: function () {
      return {
        productOptions: [],
        categoryOptions: [],
        enable_rules: false,
        rule_condition: "show",
        rules: []
      };
    },
    mounted: function () {
      this.categoryOptions = jQuery('.flux-ce').data('categories');
      var settings = jQuery('.flux-ce').data('settings');
      if (settings) {
        this.rules = settings.rules ? settings.rules : this.rules;
        this.rule_condition = settings.rule_condition ? settings.rule_condition : this.rule_condition;
        this.enable_rules = settings.enable_rules ? settings.enable_rules : this.enable_rules;

        // Reset the error state.
        this.rules.forEach(rule => {
          rule.collapsed = true;
          rule.forEach(and_rule => {
            and_rule.error = false;
            and_rule.is_dirty = false;
          });
        });
      }
    },
    components: {
      vSelect: window["vue-select"]
    },
    methods: {
      /**
       * Add a new Rule.
       */
      addOrRule: function () {
        this.rules.push([this.getEmptyRule()]);
      },
      addAndRule: function (parentRule) {
        parentRule.push(this.getEmptyRule());
      },
      getEmptyRule: function () {
        return {
          id: this.generateRandomId(),
          object: 'user_role',
          condition: 'is',
          value: '',
          product_options: [],
          product_cat_options: [],
          collapsed: false,
          error: false
        };
      },
      /**
       * Delete rule.
       *
       * @param {object} ruleToDelete Rule to delete.
       */
      deleteOrRule: function (orIndex) {
        this.rules.splice(orIndex, 1);
      },
      /**
       * Delete rule.
       *
       * @param string ruleId 
       * @param int parentRuleIdx
       */
      deleteAndRule: function (ruleId, parentRuleIdx) {
        this.rules[parentRuleIdx] = this.rules[parentRuleIdx].filter(looprule => looprule.id != ruleId);
        if (this.rules[parentRuleIdx].length === 0) {
          this.rules.splice(parentRuleIdx, 1);
        }
      },
      /**
       * Check if the rule is the last rule.
       *
       * @param object parentRule
       * @returns 
       */
      isLastRule: function (parentRule) {
        return parentRule.length === 1;
      },
      /**
       * Generate a random ID.
       *
       * @returns string
       */
      generateRandomId: function () {
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = ' ';
        const charactersLength = characters.length;
        for (let i = 0; i < 20; i++) {
          result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
      },
      /**
       * Fetch products.
       *
       * @param {string}   search  Search term.
       * @param {function} loading Function to change the loading state.
       * @returns 
       */
      fetchProducts: function (search, loading) {
        if (!search || search.length < 3) {
          loading(false);
          return;
        }
        var self = this;
        var action = 'woocommerce_json_search_products';
        var nonce = window.iconic_flux_checkout.search_products_nonce;
        var data = {
          security: nonce,
          action: action,
          term: search
        };
        jQuery.get(window.iconic_flux_checkout.ajax_url, data).done(function (data) {
          var formattedOptions = [];
          if (!data) {
            return;
          }
          for (var i in data) {
            formattedOptions.push({
              code: i,
              label: data[i]
            });
          }
          self.productOptions = formattedOptions;
          loading(false);
        });
      },
      collapseRuleGroup: function (rule) {
        // Only run validation when the rule is expanded.
        if (!rule.collapsed) {
          this.markDirtyOrClean(rule, 'dirty');
          var hasError = this.validateOrRule(rule);
        }
        if (!hasError) {
          rule.collapsed = !rule.collapsed;
        }
      },
      /**
       * Validate the OR/Parent rule.
       * 
       * @param {array} rule The OR Rule.
       *
       * @returns 
       */
      validateOrRule: function (rule) {
        if (!rule) {
          return false;
        }
        var hasError = false;
        rule.forEach(and_rule => {
          let error = this.validateAndRule(and_rule);
          if (error) {
            and_rule.error = error;
            hasError = true;
          } else {
            and_rule.error = false;
          }
        });
        return hasError;
      },
      validateAndRule: function (rule) {
        if (rule.object === 'product' && rule.value.length === 0) {
          return iconic_flux_checkout.i18n.product_required;
        }
        if (rule.object === 'product_cat' && rule.value.length === 0) {
          return iconic_flux_checkout.i18n.product_cat_required;
        }
        if (rule.object === 'cart_total' && !rule.value) {
          return iconic_flux_checkout.i18n.cart_total_required;
        }
        if (rule.object === 'user_role' && !rule.value) {
          return iconic_flux_checkout.i18n.user_role_required;
        }
        return false;
      },
      expandRuleGroup: function (rule) {
        rule.collapsed = false;
      },
      getParentRuleSummary: function (rule) {
        let summary = [];
        for (var i = 0; i < rule.length; i++) {
          summary.push(this.getSingleRuleSummary(rule[i], i));
        }
        return summary;
      },
      getSingleRuleSummary: function (rule, index) {
        let summary = '',
          value = rule.value,
          object = '<strong>' + this.convert_literal_to_text(rule.object, rule) + '</strong>',
          and = index != 0 ? this.convert_literal_to_text('And', rule) + ' ' : '';
        if (rule.object === 'product' || rule.object === 'product_cat') {
          if (rule.value.length > 0) {
            value = rule.value.map(option => option.label).join(', ');
          }
        }
        summary = and + object + ' ' + this.convert_literal_to_text(rule.condition, rule) + ' ' + value;
        return summary;
      },
      /**
       * Convert literal to text. example: is_not to "is not"
       * 
       * @param string literal Literal to convert.
       * @param {*} rule       Rule object.
       *
       * @returns string
       */
      convert_literal_to_text: function (literal, rule) {
        const text_map = {
          'user_role': iconic_flux_checkout.literals.user_role,
          'product': iconic_flux_checkout.literals.product,
          'product_cat': iconic_flux_checkout.literals.product_cat,
          'cart_total': iconic_flux_checkout.literals.cart_total,
          'is': iconic_flux_checkout.literals.is,
          'is_not': iconic_flux_checkout.literals.is_not,
          '<': iconic_flux_checkout.literals.lt,
          '<=': iconic_flux_checkout.literals.lte,
          '>': iconic_flux_checkout.literals.gt,
          '>=': iconic_flux_checkout.literals.gte,
          'and': iconic_flux_checkout.literals.and
        };
        if (rule.object === 'product' || rule.object === 'product_cat') {
          text_map['is'] = iconic_flux_checkout.literals.in_cart;
          text_map['is_not'] = iconic_flux_checkout.literals.not_in_cart;
        }
        let text = text_map[literal];
        if (!text) {
          text = literal;
        }
        return text;
      },
      /**
       * Reset value when object is changed.
       *
       * @param {obj} rule 
       */
      objectChanged: function (rule) {
        rule.value = '';
        this.validateOrRule(rule);
        this.markDirtyOrClean(rule, 'clean');
      },
      /**
       * Mark the rule as clean or dirty.
       * 
       * @param {array} or_rule  OR Rule.
       * @param {string} state 'clean' or 'dirty'.
       */
      markDirtyOrClean: function (or_rule, markDirty = 'clean') {
        or_rule.forEach(and_rule => {
          and_rule.is_dirty = 'clean' === markDirty ? false : true;
        });
      }
    },
    computed: {
      settings: function () {
        var obj = {
          enable_rules: this.enable_rules,
          rule_condition: this.rule_condition,
          rules: this.rules
        };
        return JSON.stringify(obj);
      }
    }
  });
  fce.mount('.flux-ce');
}
/* harmony default export */ const checkout_elements_metabox = (checkout_elements_init);
;// external "React"
const external_React_namespaceObject = window["React"];
;// external ["wp","element"]
const external_wp_element_namespaceObject = window["wp"]["element"];
;// external ["wp","components"]
const external_wp_components_namespaceObject = window["wp"]["components"];
;// external ["wp","i18n"]
const external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// ./source/admin/js/onboarding/guide.js



const WelcomeGuide = props => {
  var img_url = iconic_flux_checkout.flux_url + 'images/elements/onboarding/';
  let document_description = (0,external_wp_i18n_namespaceObject.sprintf)((0,external_wp_i18n_namespaceObject.__)("Not quite sure where to start? We've got you covered! Check out our %1s documentation%2s to learn more about Flux Checkout Elements."), '<a href="https://iconicwp.com/docs/flux-checkout-for-woocommerce/how-to-use-checkout-elements/" target="_blank">', '</a>');
  return (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Guide, {
    className: "edit-post-welcome-guide",
    onFinish: props.onFinish,
    pages: [{
      image: (0,external_React_namespaceObject.createElement)("picture", {
        className: "edit-post-welcome-guide__image"
      }, (0,external_React_namespaceObject.createElement)("img", {
        src: img_url + 'onboarding-1.png'
      })),
      content: (0,external_React_namespaceObject.createElement)(external_React_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)("h1", {
        className: "edit-post-welcome-guide__heading"
      }, (0,external_wp_i18n_namespaceObject.__)('Welcome to Flux Checkout Elements!', 'flux-checkout')), (0,external_React_namespaceObject.createElement)("p", {
        className: "edit-post-welcome-guide__text"
      }, (0,external_wp_i18n_namespaceObject.__)('Checkout Elements allow you to spruce up your WooCommerce checkout process by adding tailored elements like Trust Badges, Testimonials, or anything else that compliments your brand and enhances customer experience.', 'flux-checkout')))
    }, {
      image: (0,external_React_namespaceObject.createElement)("picture", {
        className: "edit-post-welcome-guide__image"
      }, (0,external_React_namespaceObject.createElement)("img", {
        src: img_url + 'onboarding-2.png'
      })),
      content: (0,external_React_namespaceObject.createElement)(external_React_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)("h1", {
        className: "edit-post-welcome-guide__heading"
      }, (0,external_wp_i18n_namespaceObject.__)('Position Flexibility', 'flux-checkout')), (0,external_React_namespaceObject.createElement)("p", {
        className: "edit-post-welcome-guide__text"
      }, (0,external_wp_i18n_namespaceObject.__)(`Place the Element anywhere you like. With Flux Checkout Elements, your options are limitless. You can determine the positioning, whether it’s after the checkout header, before the footer, or anywhere in between.`, 'flux-checkout')))
    }, {
      image: (0,external_React_namespaceObject.createElement)("picture", {
        className: "edit-post-welcome-guide__image"
      }, (0,external_React_namespaceObject.createElement)("img", {
        src: img_url + 'onboarding-3.png'
      })),
      content: (0,external_React_namespaceObject.createElement)(external_React_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)("h1", {
        className: "edit-post-welcome-guide__heading"
      }, (0,external_wp_i18n_namespaceObject.__)('Add conditional rules', 'flux-checkout')), (0,external_React_namespaceObject.createElement)("p", {
        className: "edit-post-welcome-guide__text"
      }, (0,external_wp_i18n_namespaceObject.__)(`Show or hide specific Elements based on factors like cart products, categories, user roles, or purchase amount.`, 'flux-checkout')))
    }, {
      image: (0,external_React_namespaceObject.createElement)("picture", {
        className: "edit-post-welcome-guide__image"
      }, (0,external_React_namespaceObject.createElement)("img", {
        src: img_url + 'onboarding-4.png'
      })),
      content: (0,external_React_namespaceObject.createElement)(external_React_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)("h1", {
        className: "edit-post-welcome-guide__heading"
      }, (0,external_wp_i18n_namespaceObject.__)('Learn more about Checkout Elements', 'flux-checkout')), (0,external_React_namespaceObject.createElement)("p", {
        className: "edit-post-welcome-guide__text",
        dangerouslySetInnerHTML: {
          __html: document_description
        }
      }))
    }]
  });
};
/* harmony default export */ const guide = (WelcomeGuide);
;// ./source/admin/js/onboarding/button.js




const {
  __
} = wp.i18n;
const WelcomeGuideButton = props => {
  const [isOpen, setOpen] = (0,external_wp_element_namespaceObject.useState)(props.defaultOpenPopup);

  /**
   * Mark the Welcome Guide as seen.
   */
  const markWelcomeGuideAsSeen = () => {
    const data = {
      action: 'flux_elements_welcome_guide_seen',
      nonce: iconic_flux_checkout.nonce
    };
    jQuery.post(iconic_flux_checkout.ajax_url, data);

    // Mark the global variable as false so the popup doesn't show again.
    window.fce_show_welcome_screen = false;
    setOpen(false);
  };
  return (0,external_React_namespaceObject.createElement)(external_React_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)("div", {
    className: "fce-welcome-guide-button"
  }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Button, {
    variant: "default",
    onClick: () => setOpen(true)
  }, __('Welcome Guide', 'flux-checkout')), isOpen && (0,external_React_namespaceObject.createElement)(guide, {
    ...props,
    onFinish: markWelcomeGuideAsSeen
  })));
};
/* harmony default export */ const onboarding_button = (WelcomeGuideButton);
;// ./source/admin/js/onboarding/index.js




/**
 * Register the Welcome Guide in the Gutenberg document sidebar.
 */
function onboardingSidebarInit() {
  if ('checkout_elements' !== pagenow || !jQuery('body').hasClass('block-editor-page')) {
    return;
  }
  const {
    PluginDocumentSettingPanel
  } = wp.editPost;
  const WelcomeGuidePluginDocumentSettingPanel = () => {
    return 'checkout_elements' !== pagenow ? null : (0,external_React_namespaceObject.createElement)(PluginDocumentSettingPanel, {
      name: "flux-welcome-guide",
      title: (0,external_wp_i18n_namespaceObject.__)("Flux Checkout Elements", 'flux-checkout'),
      className: "welcome-guide"
    }, (0,external_React_namespaceObject.createElement)(onboarding_button, {
      defaultOpenPopup: window.fce_show_welcome_screen
    }));
  };
  if (wp.plugins.registerPlugin) {
    const {
      registerPlugin
    } = wp.plugins;
    registerPlugin('flux-checkout-elements-onboarding-welcome-guide', {
      render: WelcomeGuidePluginDocumentSettingPanel,
      icon: 'welcome-view-site'
    });
  }
}
onboardingSidebarInit();
;// ./source/admin/js/main.js




function settings_page_init() {
  if (!pagenow.includes("iconic-flux-settings")) {
    return;
  }
  ace_editor_init();
  option_reset_init();
  header_type_init();
  header_background_init();
  colour_type_init();
  modern_colour_options_init();
  init_gradient_field();
}
document.addEventListener('DOMContentLoaded', function () {
  settings_page_init();
  checkout_elements_metabox();
});
/******/ })()
;