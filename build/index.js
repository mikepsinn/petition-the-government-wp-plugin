!function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)n.d(r,o,function(t){return e[t]}.bind(null,o));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=4)}([function(e,t){e.exports=window.wp.element},function(e,t){e.exports=window.wp.components},function(e,t){e.exports=window.wp.blocks},function(e,t){e.exports=window.wp.blockEditor},function(e,t,n){"use strict";n.r(t);var r=n(0),o=n(2),i=n(1),l=n(3);Object(o.registerBlockType)("petition-the-government/petition-form",{title:"Petition Form",icon:"admin-users",category:"widgets",edit:function(e){var t=e.setAttributes,n=e.attributes;return Object(r.createElement)(r.Fragment,null,Object(r.createElement)(l.InspectorControls,null,Object(r.createElement)(i.PanelBody,{title:"Form Settings"},Object(r.createElement)(i.ToggleControl,{label:"Display organization field?",checked:n.displayOrganization,onChange:function(e){return t({displayOrganization:e})}}))),Object(r.createElement)("div",null,Object(r.createElement)("p",null,"Petition form will be displayed here.")))},save:function(){return null}})}]);