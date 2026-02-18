<?php
/**
 * In version 2.7.0, we started using PS4-autoloader.
 * This file is to maintain compatibility with the old classes
 * for the third-party developers who are still using the old classes.
 *
 * @package Iconic_WDS
 */

// phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound, Squiz.Commenting.ClassComment.Missing


class Iconic_WDS extends Iconic_WDS\Iconic_WDS {}

class Iconic_WDS_Checkout extends Iconic_WDS\Checkout {}

class Iconic_WDS_Ajax extends Iconic_WDS\Ajax {}

class Iconic_WDS_Dates extends Iconic_WDS\Dates {}

class Iconic_WDS_Helpers extends Iconic_WDS\Helpers {}

class Iconic_WDS_API extends Iconic_WDS\Api {}

class Iconic_WDS_Order extends Iconic_WDS\Order {}

/**
 * Compatibility classes.
 */
class Iconic_WDS_Compat_Bootstrap_Date extends Iconic_WDS\Compatibility\BootstrapDate {}

class Iconic_WDS_Compat_Flexible_Shipping extends Iconic_WDS\Compatibility\FlexibleShipping {}

class Iconic_WDS_Compat_Lead_Time extends Iconic_WDS\Compatibility\LeadTime {}

class Iconic_WDS_Compat_Multistep_Checkout extends Iconic_WDS\Compatibility\MultistepCheckout {}

class Iconic_WDS_Compat_Pdf_Invoices_Packing_Slips extends Iconic_WDS\Compatibility\PdfInvoicesPackingSlips {}

class Iconic_WDS_Compat_Table_Rate_Shipping extends Iconic_WDS\Compatibility\TableRateShipping {}

class Iconic_WDS_Compat_Woo_Paypal_Payments extends Iconic_WDS\Compatibility\WooPaypalPayments {}

class Iconic_WDS_Compat_Woocommerce_Advanced_Shipping extends Iconic_WDS\Compatibility\WoocommerceAdvancedShipping {}
