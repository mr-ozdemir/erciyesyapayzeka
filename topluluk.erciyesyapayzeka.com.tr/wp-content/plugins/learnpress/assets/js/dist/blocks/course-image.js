/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/src/apps/js/blocks/course-elements/course-image/edit.js":
/*!************************************************************************!*\
  !*** ./assets/src/apps/js/blocks/course-elements/course-image/edit.js ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   edit: () => (/* binding */ edit)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);




const edit = props => {
  const {
    attributes,
    setAttributes,
    context
  } = props;
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.useBlockProps)();
  const {
    lpCourseData
  } = context;
  const courseImage = lpCourseData?.image || '<div class="course-img"><img src="/wp-content/plugins/learnpress/assets/images/no-image.png" alt="course thumbnail placeholder"</div>';
  const sizeOption = [{
    label: 'Thumbnail',
    value: 'thumbnail'
  }, {
    label: 'Medium',
    value: 'medium'
  }, {
    label: 'Large',
    value: 'large'
  }, {
    label: 'Full',
    value: 'full'
  }, {
    label: 'Custom',
    value: 'custom'
  }];
  const getStyledCourseImage = () => {
    if (lpCourseData?.image && attributes.size === 'custom' && attributes.customWidth === 500 && attributes.customHeight === 300) {
      return lpCourseData?.image;
    }
    let width = 2560;
    let height = 2560;
    if (attributes.size === 'thumbnail') {
      width = 150;
      height = 150;
    }
    if (attributes.size === 'medium') {
      width = 300;
      height = 300;
    }
    if (attributes.size === 'large') {
      width = 1024;
      height = 724;
    }
    if (attributes.size === 'full') {
      width = 2560;
      height = 2560;
    }
    if (attributes.size === 'custom') {
      if (attributes.customWidth && attributes.customWidth > 0) {
        width = attributes.customWidth;
      }
      if (attributes.customHeight && attributes.customHeight > 0) {
        height = attributes.customHeight;
      }
      if (attributes.customWidth == 0) {
        width = 2560;
      }
      if (attributes.customHeight == 0) {
        width = 2560;
      }
    }
    const ratio = width / height;
    return `<div class="course-img"><img src="/wp-content/plugins/learnpress/assets/images/no-image.png" width="${width}" height="${height}" style="aspect-ratio: ${ratio}; object-fit:cover;" alt="course thumbnail placeholder"</div>`;
  };
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.InspectorControls, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Settings', 'learnpress')
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Size', 'learnpress'),
    value: props.attributes.size,
    options: sizeOption,
    onChange: value => {
      if (value !== 'custom') {
        setAttributes({
          customWidth: 500,
          customHeight: 300
        });
      }
      setAttributes({
        size: value
      });
    }
  }), props.attributes.size === 'custom' ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      gap: '12px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.__experimentalInputControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Width', 'learnpress'),
    type: "number",
    min: 0,
    style: {
      flex: 1
    },
    placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Auto', 'learnpress'),
    suffix: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      style: {
        marginRight: '8px'
      }
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('px', 'learnpress')),
    value: props.attributes.customWidth,
    onChange: value => {
      const numValue = parseInt(value, 10);
      if (!isNaN(Number(numValue)) && numValue >= 0) {
        setAttributes({
          customWidth: Number(numValue)
        });
      } else {
        setAttributes({
          customWidth: 500
        });
      }
    }
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.__experimentalInputControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Height', 'learnpress'),
    type: "number",
    min: 0,
    style: {
      flex: 1
    },
    placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Auto', 'learnpress'),
    suffix: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      style: {
        marginRight: '8px'
      }
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('px', 'learnpress')),
    value: props.attributes.customHeight,
    onChange: value => {
      const numValue = parseInt(value, 10);
      if (!isNaN(Number(numValue)) && numValue >= 0) {
        setAttributes({
          customHeight: Number(numValue)
        });
      } else {
        setAttributes({
          customHeight: 300
        });
      }
    }
  })) : '', (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Make the image a link', 'learnpress'),
    checked: props.attributes.isLink ? true : false,
    onChange: value => {
      props.setAttributes({
        isLink: value ? true : false
      });
    }
  }), props.attributes.isLink ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Open is new tab', 'learnpress'),
    checked: props.attributes.target ? true : false,
    onChange: value => {
      props.setAttributes({
        target: value ? true : false
      });
    }
  }) : '')), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ...blockProps,
    dangerouslySetInnerHTML: {
      __html: getStyledCourseImage() || courseImage
    }
  })));
};

/***/ }),

/***/ "./assets/src/apps/js/blocks/course-elements/course-image/save.js":
/*!************************************************************************!*\
  !*** ./assets/src/apps/js/blocks/course-elements/course-image/save.js ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   save: () => (/* binding */ save)
/* harmony export */ });
const save = props => null;

/***/ }),

/***/ "./assets/src/apps/js/blocks/utilBlock.js":
/*!************************************************!*\
  !*** ./assets/src/apps/js/blocks/utilBlock.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkTemplatesCanLoadBlock: () => (/* binding */ checkTemplatesCanLoadBlock)
/* harmony export */ });
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_1__);
/**
 * Check if the block can be loaded in the current template.
 *
 * @since 4.2.8.4
 * @version 1.0.0
 */


let currentPostIdOld = null;
const checkTemplatesCanLoadBlock = (templates, metadata, callBack) => {
  (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.subscribe)(() => {
    const metaDataNew = {
      ...metadata
    };
    const store = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.select)('core/editor') || null;
    if (!store || typeof store.getCurrentPostId !== 'function' || !store.getCurrentPostId()) {
      return;
    }
    const currentPostId = store.getCurrentPostId();
    if (currentPostId === null) {
      return;
    }
    if (currentPostIdOld === currentPostId) {
      return;
    }
    currentPostIdOld = currentPostId;
    if ((0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.getBlockType)(metaDataNew.name)) {
      (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.unregisterBlockType)(metaDataNew.name);
      if (templates.includes(currentPostId)) {
        metaDataNew.ancestor = null;
        callBack(metaDataNew);
      } else {
        if (!metaDataNew.ancestor) {
          metaDataNew.ancestor = [];
        }
        callBack(metaDataNew);
      }
    }
  });
};


/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ ((module) => {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "./assets/src/apps/js/blocks/course-elements/course-image/block.json":
/*!***************************************************************************!*\
  !*** ./assets/src/apps/js/blocks/course-elements/course-image/block.json ***!
  \***************************************************************************/
/***/ ((module) => {

module.exports = /*#__PURE__*/JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"learnpress/course-image","title":"Course Image","category":"learnpress-course-elements","icon":"format-image","description":"Renders template Image Course PHP templates.","textdomain":"learnpress","keywords":["image single course","learnpress"],"ancestor":["learnpress/single-course","learnpress/course-item-template"],"usesContext":["lpCourseData"],"attributes":{"isLink":{"type":"boolean","default":true},"target":{"type":"boolean","default":false},"size":{"type":"string","default":"custom"},"customWidth":{"type":"number","default":500},"customHeight":{"type":"number","default":300}},"supports":{"multiple":true,"html":false,"shadow":true,"__experimentalBorder":{"color":true,"radius":true,"width":true,"__experimentalDefaultControls":{"width":false,"color":false,"radius":false}}}}');

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
/*!*************************************************************************!*\
  !*** ./assets/src/apps/js/blocks/course-elements/course-image/index.js ***!
  \*************************************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./edit */ "./assets/src/apps/js/blocks/course-elements/course-image/edit.js");
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./save */ "./assets/src/apps/js/blocks/course-elements/course-image/save.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./block.json */ "./assets/src/apps/js/blocks/course-elements/course-image/block.json");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _utilBlock_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../../utilBlock.js */ "./assets/src/apps/js/blocks/utilBlock.js");
/**
 * Register block course image.
 */






const templatesName = ['learnpress/learnpress//single-lp_course', 'learnpress/learnpress//single-lp_course-offline'];
(0,_utilBlock_js__WEBPACK_IMPORTED_MODULE_4__.checkTemplatesCanLoadBlock)(templatesName, _block_json__WEBPACK_IMPORTED_MODULE_2__, metadataNew => {
  (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_3__.registerBlockType)(metadataNew.name, {
    ...metadataNew,
    edit: _edit__WEBPACK_IMPORTED_MODULE_0__.edit,
    save: _save__WEBPACK_IMPORTED_MODULE_1__.save
  });
});
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_3__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_2__.name, {
  ..._block_json__WEBPACK_IMPORTED_MODULE_2__,
  edit: _edit__WEBPACK_IMPORTED_MODULE_0__.edit,
  save: _save__WEBPACK_IMPORTED_MODULE_1__.save
});
/******/ })()
;
//# sourceMappingURL=course-image.js.map