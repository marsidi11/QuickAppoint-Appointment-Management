/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./js/src/main.js":
/*!************************!*\
  !*** ./js/src/main.js ***!
  \************************/
/***/ (() => {

eval("window.addEventListener('load', function () {\n  //tabs variables\n  var tabs = document.querySelectorAll('ul.nav-tabs > li');\n  for (var i = 0; i < tabs.length; i++) {\n    tabs[i].addEventListener('click', switchTab);\n  }\n  function switchTab(e) {\n    e.preventDefault();\n    document.querySelector('ul.nav-tabs li.active').classList.remove('active');\n    document.querySelector('.tab-pane.active').classList.remove('active');\n    var clickedTab = e.currentTarget;\n    var anchor = e.target;\n    var activePaneID = anchor.getAttribute('href');\n    clickedTab.classList.add('active');\n    document.querySelector(activePaneID).classList.add('active');\n  }\n});\n\n//# sourceURL=webpack://booking-management/./js/src/main.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./js/src/main.js"]();
/******/ 	
/******/ })()
;