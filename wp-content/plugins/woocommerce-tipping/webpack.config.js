const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );
const path = require( 'path' );

const requestToExternal = ( request ) => {
    const wcDepMap = {
        '@woocommerce/components': [ 'window', 'wc', 'components' ],
        '@woocommerce/csv-export': [ 'window', 'wc', 'csvExport' ],
        '@woocommerce/currency': [ 'window', 'wc', 'currency' ],
        '@woocommerce/date': [ 'window', 'wc', 'date' ],
        '@woocommerce/navigation': [ 'window', 'wc', 'navigation' ],
        '@woocommerce/number': [ 'window', 'wc', 'number' ],
        '@woocommerce/settings': [ 'window', 'wc', 'wcSettings' ],
        '@woocommerce/tracks': [ 'window', 'wc', 'tracks' ],
        '@woocommerce/blocks-checkout': [ 'wc', 'blocksCheckout' ],

    };
 
    if ( wcDepMap[ request ] ) {
        return wcDepMap[ request ];
    }
};
 
const requestToHandle = ( request ) => {
    const wcHandleMap = {
        '@woocommerce/components': 'wc-components',
        '@woocommerce/csv-export': 'wc-csv',
        '@woocommerce/currency': 'wc-currency',
        '@woocommerce/date': 'wc-date',
        '@woocommerce/navigation': 'wc-navigation',
        '@woocommerce/number': 'wc-number',
        '@woocommerce/settings': 'wc-settings',
        '@woocommerce/tracks': 'wc-tracks',
        '@woocommerce/blocks-checkout': 'wc-blocks-checkout',
   
    };
 
    if ( wcHandleMap[ request ] ) {
        return wcHandleMap[ request ];
    }
};

module.exports = {
    ...defaultConfig,
     entry: {
        index: path.resolve( __dirname, 'src', 'index.js' ),
        'blocks-integration': path.resolve( __dirname, 'src/blocks/', 'index.js' ),

        'blocks': path.resolve( __dirname, 'src/blocks/checkout-tipping-block/', 'index.js' ),
        'blocks-frontend': path.resolve( __dirname, 'src/blocks/checkout-tipping-block/', 'frontend.js' ),

    },
     output: {
        path: path.resolve( __dirname, 'build' ),
    },
    plugins: [
        ...defaultConfig.plugins.filter(
            ( plugin ) =>
                plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
        ),
        new DependencyExtractionWebpackPlugin( {
            injectPolyfill: true,
            requestToExternal,
            requestToHandle,
        } ),
    ],
};
