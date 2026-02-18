const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const WooCommerceDependencyExtractionWebpackPlugin = require('@woocommerce/dependency-extraction-webpack-plugin');

module.exports = {
	...defaultConfig,
	entry: {
		index: path.resolve(process.cwd(), 'src', 'js', 'index.js'),
		'ka-gr-checkout-block': path.resolve(
			process.cwd(),
			'src',
			'js',
			'ka-gr-checkout-block',
			'index.js'
		),
		'ka-gr-checkout-block-frontend': path.resolve(
			process.cwd(),
			'src',
			'js',
			'ka-gr-checkout-block',
			'frontend.js'
		),
	},
	plugins: [
		...defaultConfig.plugins.filter(
			(plugin) =>
				plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
		),
		new WooCommerceDependencyExtractionWebpackPlugin()
	],
};
