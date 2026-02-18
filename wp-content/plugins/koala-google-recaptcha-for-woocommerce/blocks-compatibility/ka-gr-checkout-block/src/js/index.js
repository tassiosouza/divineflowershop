/**
 * External dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

const render = () => {};

registerPlugin('ka-gr-checkout-block', {
	render,
	scope: 'woocommerce-checkout',
});
