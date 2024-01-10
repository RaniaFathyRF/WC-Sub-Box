let cart_items_data = jQuery.parseJSON(wc_sub_box_checkout_blocks.cart_items_data);
/******/
(function (modules) { // webpackBootstrap
    /******/ 	// The module cache
    /******/
    var installedModules = {};
    /******/
    /******/ 	// The require function
    /******/
    function __webpack_require__(moduleId) {
        /******/
        /******/ 		// Check if module is in cache
        /******/
        if (installedModules[moduleId]) {
            /******/
            return installedModules[moduleId].exports;
            /******/
        }
        /******/ 		// Create a new module (and put it into the cache)
        /******/
        var module = installedModules[moduleId] = {
            /******/            i: moduleId,
            /******/            l: false,
            /******/            exports: {}
            /******/
        };
        /******/
        /******/ 		// Execute the module function
        /******/
        modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
        /******/
        /******/ 		// Flag the module as loaded
        /******/
        module.l = true;
        /******/
        /******/ 		// Return the exports of the module
        /******/
        return module.exports;
        /******/
    }

    /******/
    /******/
    /******/ 	// expose the modules object (__webpack_modules__)
    /******/
    __webpack_require__.m = modules;
    /******/
    /******/ 	// expose the module cache
    /******/
    __webpack_require__.c = installedModules;
    /******/
    /******/ 	// define getter function for harmony exports
    /******/
    __webpack_require__.d = function (exports, name, getter) {
        /******/
        if (!__webpack_require__.o(exports, name)) {
            /******/
            Object.defineProperty(exports, name, {enumerable: true, get: getter});
            /******/
        }
        /******/
    };
    /******/
    /******/ 	// define __esModule on exports
    /******/
    __webpack_require__.r = function (exports) {
        /******/
        if (typeof Symbol !== 'undefined' && Symbol.toStringTag) {
            /******/
            Object.defineProperty(exports, Symbol.toStringTag, {value: 'Module'});
            /******/
        }
        /******/
        Object.defineProperty(exports, '__esModule', {value: true});
        /******/
    };
    /******/
    /******/ 	// create a fake namespace object
    /******/ 	// mode & 1: value is a module id, require it
    /******/ 	// mode & 2: merge all properties of value into the ns
    /******/ 	// mode & 4: return value when already ns object
    /******/ 	// mode & 8|1: behave like require
    /******/
    __webpack_require__.t = function (value, mode) {
        /******/
        if (mode & 1) value = __webpack_require__(value);
        /******/
        if (mode & 8) return value;
        /******/
        if ((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
        /******/
        var ns = Object.create(null);
        /******/
        __webpack_require__.r(ns);
        /******/
        Object.defineProperty(ns, 'default', {enumerable: true, value: value});
        /******/
        if (mode & 2 && typeof value != 'string') for (var key in value) __webpack_require__.d(ns, key, function (key) {
            return value[key];
        }.bind(null, key));
        /******/
        return ns;
        /******/
    };
    /******/
    /******/ 	// getDefaultExport function for compatibility with non-harmony modules
    /******/
    __webpack_require__.n = function (module) {
        /******/
        var getter = module && module.__esModule ?
            /******/            function getDefault() {
                return module['default'];
            } :
            /******/            function getModuleExports() {
                return module;
            };
        /******/
        __webpack_require__.d(getter, 'a', getter);
        /******/
        return getter;
        /******/
    };
    /******/
    /******/ 	// Object.prototype.hasOwnProperty.call
    /******/
    __webpack_require__.o = function (object, property) {
        return Object.prototype.hasOwnProperty.call(object, property);
    };
    /******/
    /******/ 	// __webpack_public_path__
    /******/
    __webpack_require__.p = "";
    /******/
    /******/
    /******/ 	// Load entry module and return exports
    /******/
    return __webpack_require__(__webpack_require__.s = 29);
    /******/
})
    /************************************************************************/
    /******/ ({

    /***/ 25:
    /***/ (function (module, exports) {

        (function () {
            module.exports = window["wc"]["blocksCheckout"];
        }());

        /***/
    }),

    /***/ 29:
    /***/ (function (module, __webpack_exports__, __webpack_require__) {

        "use strict";
        __webpack_require__.r(__webpack_exports__);
        /* harmony import */
        var _woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(25);
        /* harmony import */
        var _woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_0__);
        /**
         * External dependencies
         */

        Object(_woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_0__["__experimentalRegisterCheckoutFilters"])('wc-sub-box', {

            itemName: (value, extensions, args) => {
                if (args.context !== 'cart' && args.context !== 'summary') {
                    return classlist;
                }
                let cart_id = args.cartItem.id;
                if (cart_items_data[cart_id]) {
                    if ((cart_items_data[cart_id]['is_parent']) || (cart_items_data[cart_id]['is_child']  && args.cartItem.key == null )) {
                        value += ' x' + args.cartItem.quantity;
                    }
                }


                return value;
            },
            cartItemClass: (classlist, extensions, args) => {
                if (args.context !== 'cart' && args.context !== 'summary') {
                    return classlist;
                }
                let classes = [];
                let cart_id = args.cartItem.id;
                if (cart_items_data[cart_id]) {
                    if (cart_items_data[cart_id]['is_parent']) {
                        classes.push('wc-sub-box-item-container');
                    } else if (cart_items_data[cart_id]['is_child'] && args.cartItem.key == null ) {
                        classes.push('wc-sub-box-item-child');
                    }
                }
                if (classes.length) {
                    classlist += ' ' + classes.join(' ');
                }
                return classlist;
            },
            // cartItemPrice: (value, extensions, args) => {
            //     if (args.context !== 'cart' && args.context !== 'summary') {
            //         return classlist;
            //     }
            //     let cart_id = args.cartItem.id;
            //     if (cart_items_data[cart_id]['is_parent']) {
            //         return 0;
            //     }
            //     return value;
            // },
            showRemoveItemLink: (value, extensions, args) => {
                if (args.context !== 'cart' && args.context !== 'summary') {
                    return classlist;
                }
                let cart_id = args.cartItem.id;
                if (cart_items_data[cart_id]) {
                    if (typeof cart_items_data[cart_id]['is_child'] !== 'undefined') {
                        return (cart_items_data[cart_id]['is_child']  && args.cartItem.key == null ) ? false : true;
                    }
                }
                return value;
            },
        });

        /***/
    })

    /******/
});
