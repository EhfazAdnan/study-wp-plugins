/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
(function webpackUniversalModuleDefinition(root, factory) {
    if(typeof exports === 'object' && typeof module === 'object')
        module.exports = factory();
    else if(typeof define === 'function' && define.amd)
        define([], factory);
    else {
        var a = factory();
        for(var i in a) (typeof exports === 'object' ? exports : root)[i] = a[i];
    }
})(self, () => {
    return /******/ (() => { // webpackBootstrap
        /******/ 	"use strict";
        /******/ 	var __webpack_modules__ = ({

            /***/ "./main.js":
            /*!*****************!*\
              !*** ./main.js ***!
              \*****************/
            /***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

                eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   cbxReposition: () => (/* binding */ cbxReposition)\n/* harmony export */ });\n/* harmony import */ var nanopop__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! nanopop */ \"./node_modules/nanopop/dist/nanopop.mjs\");\n// import { computePosition } from \"@floating-ui/dom\";\n\n// const { button, tooltip, option } = abc();\n\n// computePosition(button, tooltip).then(({ x, y }) => {\n//   Object.assign(tooltip.style, option);\n// });\n\n// export const cbxComputePosition = (button, tooltip) => {\n//   return computePosition(button, tooltip);\n// };\n// export class cbx {\n//   constructor(button, tooltip) {\n//     console.log(\"abc\");\n//     computePosition(button, tooltip).then(({ x, y }) => {\n//       Object.assign(tooltip.style, option);\n//     });\n//   }\n// }\n\n\nvar cbxReposition = function cbxReposition(reference, popper) {\n  var option = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};\n  (0,nanopop__WEBPACK_IMPORTED_MODULE_0__.reposition)(reference, popper, option);\n};\n\n//# sourceURL=webpack://test/./main.js?");

                /***/ }),

            /***/ "./node_modules/nanopop/dist/nanopop.mjs":
            /*!***********************************************!*\
              !*** ./node_modules/nanopop/dist/nanopop.mjs ***!
              \***********************************************/
            /***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

                eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   createPopper: () => (/* binding */ D),\n/* harmony export */   defaults: () => (/* binding */ k),\n/* harmony export */   reposition: () => (/* binding */ q),\n/* harmony export */   version: () => (/* binding */ A)\n/* harmony export */ });\n/*! NanoPop 2.3.0 MIT | https://github.com/Simonwep/nanopop */\nconst A = \"2.3.0\", k = {\n  variantFlipOrder: { start: \"sme\", middle: \"mse\", end: \"ems\" },\n  positionFlipOrder: { top: \"tbrl\", right: \"rltb\", bottom: \"btrl\", left: \"lrbt\" },\n  position: \"bottom\",\n  margin: 8,\n  padding: 0\n}, q = (s, i, f) => {\n  const {\n    container: l,\n    arrow: a,\n    margin: e,\n    padding: c,\n    position: B,\n    variantFlipOrder: M,\n    positionFlipOrder: C\n  } = {\n    container: document.documentElement.getBoundingClientRect(),\n    ...k,\n    ...f\n  }, { left: F, top: K } = i.style;\n  i.style.left = \"0\", i.style.top = \"0\";\n  const t = s.getBoundingClientRect(), o = i.getBoundingClientRect(), S = {\n    t: t.top - o.height - e,\n    b: t.bottom + e,\n    r: t.right + e,\n    l: t.left - o.width - e\n  }, V = {\n    vs: t.left,\n    vm: t.left + t.width / 2 - o.width / 2,\n    ve: t.left + t.width - o.width,\n    hs: t.top,\n    hm: t.bottom - t.height / 2 - o.height / 2,\n    he: t.bottom - o.height\n  }, [$, E = \"middle\"] = B.split(\"-\"), R = C[$], j = M[E], { top: u, left: v, bottom: b, right: y } = l;\n  for (const p of R) {\n    const r = p === \"t\" || p === \"b\";\n    let n = S[p];\n    const [m, d] = r ? [\"top\", \"left\"] : [\"left\", \"top\"], [w, g] = r ? [o.height, o.width] : [o.width, o.height], [z, L] = r ? [b, y] : [y, b], [P, T] = r ? [u, v] : [v, u];\n    if (!(n < P || n + w + c > z))\n      for (const x of j) {\n        let h = V[(r ? \"v\" : \"h\") + x];\n        if (!(h < T || h + g + c > L)) {\n          if (h -= o[d], n -= o[m], i.style[d] = `${h}px`, i.style[m] = `${n}px`, a) {\n            const O = r ? t.width / 2 : t.height / 2, H = O * 2 < g ? t[d] + O : h + g / 2;\n            n < t[m] && (n += w), a.style[d] = `${H}px`, a.style[m] = `${n}px`;\n          }\n          return p + x;\n        }\n      }\n  }\n  return i.style.left = F, i.style.top = K, null;\n}, D = (s, i, f) => {\n  const l = typeof s == \"object\" && !(s instanceof HTMLElement) ? s : { reference: s, popper: i, ...f };\n  return {\n    /**\n     * Repositions the current popper.\n     * @param options Optional options which get merged with the current ones.\n     */\n    update(a = l) {\n      const { reference: e, popper: c } = Object.assign(l, a);\n      if (!c || !e)\n        throw new Error(\"Popper- or reference-element missing.\");\n      return q(e, c, l);\n    }\n  };\n};\n\n//# sourceMappingURL=nanopop.mjs.map\n\n\n//# sourceURL=webpack://test/./node_modules/nanopop/dist/nanopop.mjs?");

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
        /******/
        /******/ 	// startup
        /******/ 	// Load entry module and return exports
        /******/ 	// This entry module can't be inlined because the eval devtool is used.
        /******/ 	var __webpack_exports__ = __webpack_require__("./main.js");
        /******/
        /******/ 	return __webpack_exports__;
        /******/ })()
        ;
});