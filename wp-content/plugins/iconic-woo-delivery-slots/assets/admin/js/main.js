(function ($) {
  window.iconic_wds_admin = {
    init() {
      $(document.body).trigger('iconic_wds_admin_popup_init');
      $(document).on('click', '#iconic_wds_add_timeslot_btn, .wds-edit-order-item', function (e) {
        e.preventDefault();
        $(document.body).trigger('iconic_wds_admin_popup_show');
      });
      $(document).on('click', '.wds-delete-order-item', function (e) {
        e.preventDefault();
        if (confirm(iconic_wds_vars.strings.delete_slot)) {
          $(document.body).trigger('iconic_wds_admin_popup_delete_timeslot');
        }
      });
      iconic_wds_admin.watch_for_override_checkbox();
      iconic_wds_admin.display_shipping_method_ids();
      iconic_wds_admin.toggle_notice_for_specific_date();
      $('#group-timesettings-timesettings-timeslots').on('change', 'input[type=checkbox]', () => setTimeout(iconic_wds_admin.toggle_notice_for_specific_date, 100));
    },
    /**
     * Watch click on "Override" checkbox.
     */
    watch_for_override_checkbox() {
      $('.iconic_wds_days--override input').click(function () {
        const target_class = $(this).parent().data('override-target');
        $(target_class).each(function () {
          if (!$(this).hasClass('iconic_wds_days--override')) {
            $(this).toggle();
          }
        });
      });
    },
    /**
     * Display Shipping method Ids in WooCommerce shipping zone page.
     */
    display_shipping_method_ids() {
      if ('woocommerce_page_wc-settings' !== pagenow) {
        return;
      }
      const data = {
        action: 'iconic_wds_get_all_shipping_methods'
      };
      $.post(ajaxurl, data).done(function (response) {
        const shipping_methods = response.data;
        const result = {};
        for (const shipping_method in shipping_methods) {
          if ('any' === shipping_method) {
            continue;
          }
          const [type, id] = shipping_method.split(':');
          if (undefined !== id) {
            result[id] = shipping_method;
          }
        }
        const $tbody = $('.wc-shipping-zone-method-rows');
        $tbody.find('tr').each(function () {
          const this_id = $(this).data('id');
          if (result[this_id] === undefined) {
            return;
          }
          $(this).find('.wc-shipping-zone-method-title > a.wc-shipping-zone-method-settings').after('<div class="iconic-wds-shipping-zone-method-id"><small>' + result[this_id] + '</small></div>');
        });
      });
    },
    /**
     * Show notice when any specific date doesn't have any time slot.
     */
    toggle_notice_for_specific_date() {
      $('#group-datesettings-datesettings-specific-days .wpsf-group__row').each(function () {
        const date_id = $(this).find('.wpsf-group__row-id').val();
        const $specific_date = $(this);
        if (!iconic_wds_admin.is_timeslot_enabled_for_specific_date(date_id) && $specific_date.find('.datepicker').val()) {
          const no_timeslot = iconic_wds_vars.strings.select_timeslot_specific_date;
          iconic_wds_admin.show_specific_date_notice($specific_date, no_timeslot);
        } else {
          iconic_wds_admin.hide_specific_date_notice($specific_date);
        }
      });
    },
    /**
     * Display notice for specific date.
     *
     * @param {jQuery} $specific_date - The specific date element.
     * @param {string} message        - The notice message.
     */
    show_specific_date_notice($specific_date, message) {
      const $notice = $specific_date.find('.iconic-wds-notice');
      if ($notice.length) {
        $notice.show().text(message);
      } else {
        $specific_date.find('.wpsf-group__field-wrapper--date').prepend('<div class="iconic-wds-notice iconic-wds-notice--specific-date">' + message + '</div>');
      }
    },
    hide_specific_date_notice($specific_date) {
      $specific_date.find('.iconic-wds-notice').hide();
    },
    /**
     * Check if any time slot is enabled for specific date.
     *
     * @param {*} specific_date_id
     * @return
     */
    is_timeslot_enabled_for_specific_date(specific_date_id) {
      const $timeslot_rows = $('#group-timesettings-timesettings-timeslots .wpsf-group__row');
      let is_timeslot_enabled = false;
      $timeslot_rows.each(function () {
        if ($(this).find('input[value="' + specific_date_id + '"]').is(':checked')) {
          is_timeslot_enabled = true;
          return false;
        }
      });
      return is_timeslot_enabled;
    }
  };
  $(document).ready(iconic_wds_admin.init);
})(jQuery);
/**
 * Init function.
 */
function iconic_wds_admin_popup_init() {
  if (!jQuery('#wds-admin-popup-wrap').length) {
    return;
  }
  window.iconic_wds_admin_popup = new Vue({
    el: '#wds-admin-popup-wrap',
    data: {
      showPopup: false,
      hasAddress: false,
      overrideRules: false,
      shippingMethods: [],
      selectedShippingMethod: '',
      timeslotSetupEnable: '1' === iconic_wds_vars.settings.timesettings_timesettings_setup_enable,
      availableDates: [],
      availableTimeslots: {},
      formattedDestination: '',
      selectedDate: null,
      selectedSlot: '',
      selectedDateTimeslots: [],
      timeslotFocus: false,
      address: {
        country: '',
        state: '',
        city: '',
        postcode: ''
      }
    },
    components: {},
    /**
     * Mounted.
     */
    mounted() {
      const initData = jQuery('#wds-admin-popup-wrap').data('init');
      if (initData.shipping_method) {
        this.selectedShippingMethod = initData.shipping_method;
        this.selectedDate = this.getDateFromYmd(initData.ymd);
        this.overrideRules = 'true' === initData.override_rules;
        if (this.overrideRules) {
          this.selectedDateTimeslots = iconic_wds_vars.timeslot;
          this.selectedSlot = initData.timeslot_id;
        }
        Vue.nextTick(() => {
          this.handleShippingMethodChange(initData.ymd, initData.timeslot_id);
        });
      }
      jQuery(document.body).on('iconic_wds_admin_popup_show', () => {
        this.popup(true);
        if (this.hasAddress) {
          this.fetchShippingMethods(this.selectedShippingMethod);
        }
      });
      jQuery(document).on('change', '#_shipping_city, #_shipping_postcode, #_shipping_country, #_shipping_state', this.updateAddress);
      jQuery(document.body).on('iconic_wds_admin_popup_delete_timeslot', this.deleteTimeslot);
      this.updateAddress();
    },
    computed: {
      /**
       * Array which is passed to <v-date-picker>.
       *
       * @return array.
       */
      enabledDatesForDatepicker() {
        if (this.overrideRules) {
          return [];
        }
        if (0 === this.availableDates.length) {
          return [];
        }
        return this.availableDates.map(date => {
          const y = date.ymd.slice(0, 4);
          const m = date.ymd.slice(4, 6);
          const d = date.ymd.slice(6, 8);
          return {
            start: new Date(`${y}/${m}/${d}`),
            end: new Date(`${y}/${m}/${d}`)
          };
        });
      },
      /**
       * Disable all dates when available dates in empty.
       * The v-calendar datepicker shows all dates as available otherwise.
       *
       * @return object
       */
      disabledDates() {
        if (this.overrideRules || this.availableDates.length) {
          return false;
        }
        return {
          weekdays: [1, 2, 3, 4, 5, 6, 7]
        };
      },
      /**
       * Attributes passed to datepicker.
       *
       * @return
       */
      datepickerAttributes() {
        let dates = [];
        if (this.availableDates && this.availableDates.length && !this.overrideRules) {
          dates = this.availableDates.map(date => {
            const y = date.ymd.slice(0, 4);
            const m = date.ymd.slice(4, 6);
            const d = date.ymd.slice(6, 8);
            const dateObj = new Date(`${y}/${m}/${d}`);
            const fee = this.getDayFee(dateObj);
            if (fee) {
              return dateObj;
            }
          });
        } else {
          // Show popover for these dates.
          current = new Date(new Date().getTime() - 30 * 24 * 60 * 60 * 1000); // start from today-60 days
          for (i = 0; i < 365; i++) {
            current = new Date(current.getTime() + 24 * 60 * 60 * 1000); // add 1 day until next 365 days.
            const fee = this.getDayFee(current);
            if (fee) {
              dates.push(current);
            }
          }
        }
        return [{
          key: 'bookdable-dates',
          popover: {
            label: 'date'
          },
          dates
        }];
      },
      /**
       * Formated selected date.
       *
       * @return string
       */
      formattedDate() {
        if (!this.selectedDate || 'function' !== typeof this.selectedDate.format || !this.selectedDate.isValid()) {
          return '';
        }
        return this.formatDate(this.selectedDate);
      },
      /**
       * Selected Timeslot object.
       *
       * @return object
       */
      selectedSlotObj() {
        if (!this.selectedSlot || !this.selectedDateTimeslots) {
          return null;
        }
        return Object.values(this.selectedDateTimeslots).find(timeslot => this.selectedSlot === timeslot.value);
      },
      /**
       * Calculated fee based on the selected date and time.
       *
       * @return
       */
      calculatedFee() {
        if (!this.selectedDate || !this.selectedDateTimeslots) {
          return null;
        }
        if (this.overrideRules) {
          var dayFee = 0,
            timeslotFee = 0;
          dayFee = this.getDayFee(this.selectedDate);
          if (this.selectedSlotObj && this.selectedSlotObj.fee && this.selectedSlotObj.fee.value) {
            timeslotFee = this.selectedSlotObj.fee.value;
          }
          return parseFloat(dayFee) + parseFloat(timeslotFee);
        }
        let total = 0;
        var dayFee = this.getDayFee(this.selectedDate);
        if (null === dayFee) {
          return;
        }
        total += dayFee;
        if (!this.timeslotSetupEnable || !this.selectedSlot) {
          return;
        }
        const timeslotObj = this.selectedDateTimeslots.find(timeslot => timeslot.value === this.selectedSlot);
        if (!timeslotObj) {
          return total;
        }
        if (timeslotObj && timeslotObj.fee && timeslotObj.fee.value) {
          total += parseFloat(timeslotObj.fee.value);
        }
        return total;
      },
      /**
       * Selected Date label.
       *
       * @return string.
       */
      selectedDateLabel() {
        if (!this.selectedDate) {
          return '';
        }
        let str = this.formatDate(this.selectedDate);
        const fee = this.getDayFee(this.selectedDate);
        if (fee) {
          str = str + ' (' + accounting.formatMoney(fee, iconic_wds_vars.currency) + ')';
        }
        return str;
      }
    },
    /**
     * Methods.
     */
    methods: {
      /**
       * Show/hide popup.
       *
       * @param bool show Whether to show or hide the popup.
       * @param show
       */
      popup(show) {
        this.showPopup = show;
      },
      /**
       * Popover for datepicker.
       *
       * @param {Date} date Date object.
       * @return
       */
      datePopover(date) {
        let fee, fee_formatted;
        if (!this.overrideRules && this.availableDates) {
          const ymd = this.getYMD(date.date);
          for (date of this.availableDates) {
            if (ymd === date.ymd) {
              fee = date.fee;
            }
          }
        } else {
          fee = this.getDayFee(date.date);
        }
        if (!fee) {
          return;
        }
        fee_formatted = this.decodeHtml(accounting.formatMoney(fee, iconic_wds_vars.currency));
        return `+${fee_formatted}`;
      },
      /**
       * Handle shipping method change.
       *
       * @param {string} ymd
       * @param {string} timeslot
       */
      handleShippingMethodChange(ymd, timeslot) {
        if (!this.overrideRules) {
          this.fetchDates(ymd, timeslot);
        }
      },
      /**
       * Handle change to override rules checkbox.
       */
      handleOverrideChange() {
        this.selectedDateTimeslots = [];
        if (this.overrideRules) {
          // select date and timeslot.
          this.handleSelectDate();
        } else {
          this.selectedDate = '';
          this.selectedDateTimeslots = [];
        }
      },
      /**
       * Hide Datepicker.
       */
      dateTimeBlur() {
        setTimeout(() => {
          this.timeslotFocus = false;
        }, 100);
      },
      /**
       * Block - enable spinner.
       */
      block() {
        jQuery('.wds-admin-popup-body').block({
          message: null,
          overlayCSS: {
            background: '#fff',
            opacity: 0.6
          }
        });
      },
      /**
       * Unblock.
       */
      unblock() {
        jQuery('.wds-admin-popup-body').unblock();
      },
      /**
       * Update address.
       */
      updateAddress() {
        this.address = {
          city: jQuery('#_shipping_city').val(),
          postcode: jQuery('#_shipping_postcode').val(),
          country: jQuery('#_shipping_country').val(),
          state: jQuery('#_shipping_state').val()
        };
        if (this.address.city || this.address.postcode || this.address.country || this.address.state) {
          this.hasAddress = true;
        } else {
          this.hasAddress = false;
        }
      },
      /**
       * Fetch shipping methods.
       *
       * @param initShippingMethod
       */
      fetchShippingMethods(initShippingMethod) {
        const data = {
          action: 'iconic_wds_get_address_shipping_methods',
          calc_shipping_country: this.address.country,
          calc_shipping_state: this.address.state,
          calc_shipping_city: this.address.city,
          calc_shipping_postcode: this.address.postcode,
          nonce: window.iconic_wds_vars.ajax_nonce
        };
        const self = this;
        self.block();
        jQuery.post(window.ajaxurl, data, function (response) {
          if (!response.success && false === self.overrideRules) {
            alert(iconic_wds_vars.strings.no_shiping_methods);
            return;
          }
          self.shippingMethods = response.data.shipping_methods;
          self.formattedDestination = response.data.formatted_destination;

          // unset dates, timeslots.
          self.availableDates = [];
          self.availableTimeslots = {};

          // If there is only one shipping method then fetch dates for it.
          const shippingMethodKeys = Object.keys(self.shippingMethods);
          if (1 === shippingMethodKeys.length) {
            self.selectedShippingMethod = shippingMethodKeys[0];
            Vue.nextTick(() => {
              self.fetchDates();
            });
          }

          // If initial shipping method is set (on page load), set it and fetch dates.
          if (initShippingMethod) {
            self.selectedShippingMethod = initShippingMethod;
            self.fetchDates();
          }
        }).always(function () {
          self.unblock();
        });
      },
      /**
       * Fetch Dates.
       *
       * @param string   dateYmd Date in YMD format.
       * @param dateYmd
       * @param timeslot
       */
      fetchDates(dateYmd, timeslot) {
        const data = {
          action: 'iconic_wds_get_reservation_table_data',
          shipping_method: this.selectedShippingMethod,
          context: 'admin',
          nonce: window.iconic_wds_vars.ajax_nonce
        };
        if ('string' === typeof dateYmd) {
          data.ymd = dateYmd;
        }
        if (this.overrideRules) {
          data.skip_shipping_method_check = '1';
        }
        const self = this;
        self.block();
        jQuery.post(window.ajaxurl, data, function (response) {
          if (!response.success) {
            return;
          }
          self.availableDates = response.data.dates;
          if (!self.availableDates || 0 === self.availableDates.length) {
            return;
          }
          for (const ymd in response.data.timeslots) {
            self.$set(self.availableTimeslots, ymd, response.data.timeslots[ymd]);
          }
          if ('string' === typeof dateYmd) {
            self.selectedDate = self.getDateFromYmd(dateYmd);
            self.selectedDateTimeslots = self.availableTimeslots[dateYmd];
          }
          if ('string' === typeof timeslot) {
            self.selectedSlot = timeslot;
          }
        }).always(function () {
          self.unblock();
        });
      },
      /**
       * Is the given date bookable?
       *
       * @param Date date Date.
       * @param date
       * @return bool
       */
      isDateAllowed(date) {
        if (this.overrideRules) {
          return true;
        }
        const ymd = this.getYMD(date);
        for (const loopDate of this.availableDates) {
          if (ymd === loopDate.ymd) {
            return true;
          }
        }
        return false;
      },
      /**
       * Get Ymd from the JS Date Object.
       *
       * @param Date date Date.
       * @param date
       * @return Ymd
       */
      getYMD(date) {
        if (!date || 'function' !== typeof date.getMonth) {
          return '';
        }
        const y = this.padDigits(date.getFullYear(), 4);
        const m = this.padDigits(date.getMonth() + 1, 2);
        const d = this.padDigits(date.getDate(), 2);
        return y + m + d;
      },
      /**
       * Add 0 padding to the digits.
       *
       * @param int    number
       * @param int    digits
       * @param number
       * @param digits
       * @return
       */
      padDigits(number, digits) {
        return Array(Math.max(digits - String(number).length + 1, 0)).join(0) + number;
      },
      /**
       * Select a date.
       *
       * @param obj  date Date.
       * @param date
       */
      handleSelectDate(date) {
        if (this.overrideRules) {
          this.selectedDateTimeslots = Object.values(window.iconic_wds_vars.timeslot);

          // select first timeslot.
          if (this.selectedDate) {
            this.selectedSlot = this.selectedDateTimeslots && this.selectedDateTimeslots[0] ? this.selectedDateTimeslots[0].value : null;
          }
          window.setTimeout(() => {
            this.timeslotFocus = false;
          });
          return;
        }
        const ymd = this.getYMD(date);
        if ('undefined' === typeof this.availableTimeslots[ymd] && !this.overrideRules) {
          this.fetchDates(ymd);
        }
        this.selectedDateTimeslots = this.availableTimeslots[ymd];

        // select first timeslot.
        this.selectedSlot = this.selectedDateTimeslots && this.selectedDateTimeslots[0] ? this.selectedDateTimeslots[0].value : '';
        window.setTimeout(() => {
          this.timeslotFocus = false;
        });
      },
      /**
       * Save timeslot.
       */
      saveSlot() {
        this.block();
        const self = this;
        const data = {
          action: 'iconic_wds_update_order_slot',
          order_id: woocommerce_admin_meta_boxes.post_id,
          security: iconic_wds_vars.ajax_nonce,
          date_ymd: this.getYMD(this.selectedDate),
          timeslot: this.selectedSlot,
          shipping_method: [this.selectedShippingMethod],
          override_rules: this.overrideRules,
          fee: this.calculatedFee,
          context: 'admin'
        };
        jQuery.post(ajaxurl, data, function (response) {
          if (response.success) {
            jQuery('#woocommerce-order-items').find('.inside').empty();
            jQuery('#woocommerce-order-items').find('.inside').append(response.data.html);
            jQuery('#woocommerce-order-items').trigger('wc_order_items_reloaded');
            self.unblock();
            self.popup(false);
          } else {
            window.alert(response.data.error);
          }
          self.unblock();
        });
      },
      /**
       * Delete timeslot.
       */
      deleteTimeslot() {
        const self = this;
        const data = {
          action: 'iconic_wds_admin_delete_order_slot',
          order_id: woocommerce_admin_meta_boxes.post_id,
          security: woocommerce_admin_meta_boxes.order_item_nonce
        };
        jQuery.post(ajaxurl, data, function (response) {
          if (response.success) {
            jQuery('#woocommerce-order-items').find('.inside').empty();
            jQuery('#woocommerce-order-items').find('.inside').append(response.data.html);
            jQuery('#woocommerce-order-items').trigger('wc_order_items_reloaded');
            self.unblock();
            self.popup(false);
          } else {
            window.alert(response.data.error);
          }
          self.unblock();
        });
      },
      /**
       * Is date time enabled for shipping method?
       *
       * @param string         shippingMethod Shiping method id.
       * @param shippingMethod
       * @return bool
       */
      dateTimeEnabledForSM(shippingMethod) {
        if (!window.iconic_wds_vars.settings.general_setup_shipping_methods || !jQuery.isArray(window.iconic_wds_vars.settings.general_setup_shipping_methods)) {
          return false;
        }
        const enabled = window.iconic_wds_vars.settings.general_setup_shipping_methods.includes('any') || window.iconic_wds_vars.settings.general_setup_shipping_methods.includes(shippingMethod);
        return wp.hooks.applyFilters('iconic_wds_datetime_enabled_for_shipping_method', enabled, shippingMethod);
      },
      /**
       * Get date object from ymd.
       *
       * @param string ymd Date in YYYYMMDD format.
       *
       * @param ymd
       * @return Date
       */
      getDateFromYmd(ymd) {
        const y = ymd.slice(0, 4);
        const m = ymd.slice(4, 6);
        const d = ymd.slice(6, 8);
        return new Date(`${y}/${m}/${d}`);
      },
      /**
       * Format the date with the settings format.
       *
       * @param Date date Date
       * @param date
       * @return string
       */
      formatDate(date) {
        if (!date) {
          return;
        }
        return jQuery.datepicker.formatDate(iconic_wds_vars.settings.datesettings_datesettings_dateformat, date);
      },
      /**
       * Decode HTML
       *
       * @param string str
       *
       * @param str
       * @return
       */
      decodeHtml(str) {
        const txt = document.createElement('textarea');
        txt.innerHTML = str;
        return txt.value;
      },
      /**
       * Get Day fee.
       *
       * @param {*} date
       * @return
       */
      getDayFee(date) {
        if (this.overrideRules) {
          const fee = window.iconic_wds_vars.settings.datesettings_fees_days[date.getDay()];
          return fee ? parseFloat(fee) : 0;
        }
        const dateObj = this.availableDates.find(loop_date => loop_date.ymd === this.getYMD(date));
        if (!dateObj) {
          return null;
        }
        return dateObj.fee;
      }
    } // Methods.
  });
}
Vue.directive('click-outside', {
  bind(el, binding, vnode) {
    el.clickOutsideEvent = function (event) {
      // here we check that click was outside the el and his children
      if (!(el === event.target || el.contains(event.target))) {
        // and if it did, call method provided in attribute value
        vnode.context[binding.expression](event);
      }
    };
    document.body.addEventListener('click', el.clickOutsideEvent);
  },
  unbind(el) {
    document.body.removeEventListener('click', el.clickOutsideEvent);
  }
});
Date.prototype.isValid = function () {
  // An invalid date object returns NaN for getTime() and NaN is the only
  // object not strictly equal to itself.
  return this.getTime() === this.getTime();
};
jQuery(document.body).on('iconic_wds_admin_popup_init', iconic_wds_admin_popup_init);
(function ($, document) {
  var iconic_wds_settings = {
    on_ready() {
      iconic_wds_settings.setup_checkbox_lists();
      iconic_wds_settings.watch_checkbox_lists();
    },
    /**
     * Setup checkbox lists on page load.
     */
    setup_checkbox_lists() {
      const $lists = $('.wpsf-list--checkboxes');
      if ($lists.length <= 0) {
        return;
      }
      $lists.each(function (index, list) {
        iconic_wds_settings.toggle_checkbox_lists($(list).find('input[value="any"]'));
      });
    },
    /**
     * Watch for checkbox changes.
     */
    watch_checkbox_lists() {
      $(document.body).on('change', '.wpsf-list--checkboxes input[value="any"]', function () {
        iconic_wds_settings.toggle_checkbox_lists($(this));
      });
    },
    /**
     * Toggle checkbox items visibility.
     *
     * @param $any_checkbox
     */
    toggle_checkbox_lists($any_checkbox) {
      if ($any_checkbox.length <= 0) {
        return;
      }
      const $list = $any_checkbox.closest('.wpsf-list--checkboxes'),
        any_selected = $any_checkbox.prop('checked');
      if ($list.length <= 0) {
        return;
      }
      if (any_selected) {
        const $any_item = $any_checkbox.closest('li');
        $list.find('li').not($any_item).hide();
      } else {
        $list.find('li').show();
      }
    }
  };
  $(document).on('ready', iconic_wds_settings.on_ready);
})(jQuery, document);