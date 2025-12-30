/* @license GPL-2.0-or-later https://www.drupal.org/licensing/faq */
!function(){"use strict";
/**
   * @license
   * Copyright 2019 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */const t=window,e=t.ShadowRoot&&(void 0===t.ShadyCSS||t.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,i=Symbol(),o=new WeakMap;class s{constructor(t,e,o){if(this._$cssResult$=!0,o!==i)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=t,this.t=e}get styleSheet(){let t=this.o;const i=this.t;if(e&&void 0===t){const e=void 0!==i&&1===i.length;e&&(t=o.get(i)),void 0===t&&((this.o=t=new CSSStyleSheet).replaceSync(this.cssText),e&&o.set(i,t))}return t}toString(){return this.cssText}}const n=e?t=>t:t=>t instanceof CSSStyleSheet?(t=>{let e="";for(const i of t.cssRules)e+=i.cssText;return(t=>new s("string"==typeof t?t:t+"",void 0,i))(e)})(t):t
/**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */;var r;const a=window,l=a.trustedTypes,d=l?l.emptyScript:"",h=a.reactiveElementPolyfillSupport,c={toAttribute(t,e){switch(e){case Boolean:t=t?d:null;break;case Object:case Array:t=null==t?t:JSON.stringify(t)}return t},fromAttribute(t,e){let i=t;switch(e){case Boolean:i=null!==t;break;case Number:i=null===t?null:Number(t);break;case Object:case Array:try{i=JSON.parse(t)}catch(t){i=null}}return i}},p=(t,e)=>e!==t&&(e==e||t==t),u={attribute:!0,type:String,converter:c,reflect:!1,hasChanged:p};class m extends HTMLElement{constructor(){super(),this._$Ei=new Map,this.isUpdatePending=!1,this.hasUpdated=!1,this._$El=null,this.u()}static addInitializer(t){var e;this.finalize(),(null!==(e=this.h)&&void 0!==e?e:this.h=[]).push(t)}static get observedAttributes(){this.finalize();const t=[];return this.elementProperties.forEach(((e,i)=>{const o=this._$Ep(i,e);void 0!==o&&(this._$Ev.set(o,i),t.push(o))})),t}static createProperty(t,e=u){if(e.state&&(e.attribute=!1),this.finalize(),this.elementProperties.set(t,e),!e.noAccessor&&!this.prototype.hasOwnProperty(t)){const i="symbol"==typeof t?Symbol():"__"+t,o=this.getPropertyDescriptor(t,i,e);void 0!==o&&Object.defineProperty(this.prototype,t,o)}}static getPropertyDescriptor(t,e,i){return{get(){return this[e]},set(o){const s=this[t];this[e]=o,this.requestUpdate(t,s,i)},configurable:!0,enumerable:!0}}static getPropertyOptions(t){return this.elementProperties.get(t)||u}static finalize(){if(this.hasOwnProperty("finalized"))return!1;this.finalized=!0;const t=Object.getPrototypeOf(this);if(t.finalize(),void 0!==t.h&&(this.h=[...t.h]),this.elementProperties=new Map(t.elementProperties),this._$Ev=new Map,this.hasOwnProperty("properties")){const t=this.properties,e=[...Object.getOwnPropertyNames(t),...Object.getOwnPropertySymbols(t)];for(const i of e)this.createProperty(i,t[i])}return this.elementStyles=this.finalizeStyles(this.styles),!0}static finalizeStyles(t){const e=[];if(Array.isArray(t)){const i=new Set(t.flat(1/0).reverse());for(const t of i)e.unshift(n(t))}else void 0!==t&&e.push(n(t));return e}static _$Ep(t,e){const i=e.attribute;return!1===i?void 0:"string"==typeof i?i:"string"==typeof t?t.toLowerCase():void 0}u(){var t;this._$E_=new Promise((t=>this.enableUpdating=t)),this._$AL=new Map,this._$Eg(),this.requestUpdate(),null===(t=this.constructor.h)||void 0===t||t.forEach((t=>t(this)))}addController(t){var e,i;(null!==(e=this._$ES)&&void 0!==e?e:this._$ES=[]).push(t),void 0!==this.renderRoot&&this.isConnected&&(null===(i=t.hostConnected)||void 0===i||i.call(t))}removeController(t){var e;null===(e=this._$ES)||void 0===e||e.splice(this._$ES.indexOf(t)>>>0,1)}_$Eg(){this.constructor.elementProperties.forEach(((t,e)=>{this.hasOwnProperty(e)&&(this._$Ei.set(e,this[e]),delete this[e])}))}createRenderRoot(){var i;const o=null!==(i=this.shadowRoot)&&void 0!==i?i:this.attachShadow(this.constructor.shadowRootOptions);return((i,o)=>{e?i.adoptedStyleSheets=o.map((t=>t instanceof CSSStyleSheet?t:t.styleSheet)):o.forEach((e=>{const o=document.createElement("style"),s=t.litNonce;void 0!==s&&o.setAttribute("nonce",s),o.textContent=e.cssText,i.appendChild(o)}))})(o,this.constructor.elementStyles),o}connectedCallback(){var t;void 0===this.renderRoot&&(this.renderRoot=this.createRenderRoot()),this.enableUpdating(!0),null===(t=this._$ES)||void 0===t||t.forEach((t=>{var e;return null===(e=t.hostConnected)||void 0===e?void 0:e.call(t)}))}enableUpdating(t){}disconnectedCallback(){var t;null===(t=this._$ES)||void 0===t||t.forEach((t=>{var e;return null===(e=t.hostDisconnected)||void 0===e?void 0:e.call(t)}))}attributeChangedCallback(t,e,i){this._$AK(t,i)}_$EO(t,e,i=u){var o;const s=this.constructor._$Ep(t,i);if(void 0!==s&&!0===i.reflect){const n=(void 0!==(null===(o=i.converter)||void 0===o?void 0:o.toAttribute)?i.converter:c).toAttribute(e,i.type);this._$El=t,null==n?this.removeAttribute(s):this.setAttribute(s,n),this._$El=null}}_$AK(t,e){var i;const o=this.constructor,s=o._$Ev.get(t);if(void 0!==s&&this._$El!==s){const t=o.getPropertyOptions(s),n="function"==typeof t.converter?{fromAttribute:t.converter}:void 0!==(null===(i=t.converter)||void 0===i?void 0:i.fromAttribute)?t.converter:c;this._$El=s,this[s]=n.fromAttribute(e,t.type),this._$El=null}}requestUpdate(t,e,i){let o=!0;void 0!==t&&(((i=i||this.constructor.getPropertyOptions(t)).hasChanged||p)(this[t],e)?(this._$AL.has(t)||this._$AL.set(t,e),!0===i.reflect&&this._$El!==t&&(void 0===this._$EC&&(this._$EC=new Map),this._$EC.set(t,i))):o=!1),!this.isUpdatePending&&o&&(this._$E_=this._$Ej())}async _$Ej(){this.isUpdatePending=!0;try{await this._$E_}catch(t){Promise.reject(t)}const t=this.scheduleUpdate();return null!=t&&await t,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){var t;if(!this.isUpdatePending)return;this.hasUpdated,this._$Ei&&(this._$Ei.forEach(((t,e)=>this[e]=t)),this._$Ei=void 0);let e=!1;const i=this._$AL;try{e=this.shouldUpdate(i),e?(this.willUpdate(i),null===(t=this._$ES)||void 0===t||t.forEach((t=>{var e;return null===(e=t.hostUpdate)||void 0===e?void 0:e.call(t)})),this.update(i)):this._$Ek()}catch(t){throw e=!1,this._$Ek(),t}e&&this._$AE(i)}willUpdate(t){}_$AE(t){var e;null===(e=this._$ES)||void 0===e||e.forEach((t=>{var e;return null===(e=t.hostUpdated)||void 0===e?void 0:e.call(t)})),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(t)),this.updated(t)}_$Ek(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$E_}shouldUpdate(t){return!0}update(t){void 0!==this._$EC&&(this._$EC.forEach(((t,e)=>this._$EO(e,this[e],t))),this._$EC=void 0),this._$Ek()}updated(t){}firstUpdated(t){}}
/**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */
var g;m.finalized=!0,m.elementProperties=new Map,m.elementStyles=[],m.shadowRootOptions={mode:"open"},null==h||h({ReactiveElement:m}),(null!==(r=a.reactiveElementVersions)&&void 0!==r?r:a.reactiveElementVersions=[]).push("1.6.1");const v=window,f=v.trustedTypes,y=f?f.createPolicy("lit-html",{createHTML:t=>t}):void 0,_=`lit$${(Math.random()+"").slice(9)}$`,b="?"+_,w=`<${b}>`,$=document,x=(t="")=>$.createComment(t),k=t=>null===t||"object"!=typeof t&&"function"!=typeof t,A=Array.isArray,E=/<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g,S=/-->/g,C=/>/g,z=RegExp(">|[ \t\n\f\r](?:([^\\s\"'>=/]+)([ \t\n\f\r]*=[ \t\n\f\r]*(?:[^ \t\n\f\r\"'`<>=]|(\"|')|))|$)","g"),M=/'/g,P=/"/g,H=/^(?:script|style|textarea|title)$/i,B=(t=>(e,...i)=>({_$litType$:t,strings:e,values:i}))(1),D=Symbol.for("lit-noChange"),U=Symbol.for("lit-nothing"),N=new WeakMap,R=$.createTreeWalker($,129,null,!1),L=(t,e)=>{const i=t.length-1,o=[];let s,n=2===e?"<svg>":"",r=E;for(let e=0;e<i;e++){const i=t[e];let a,l,d=-1,h=0;for(;h<i.length&&(r.lastIndex=h,l=r.exec(i),null!==l);)h=r.lastIndex,r===E?"!--"===l[1]?r=S:void 0!==l[1]?r=C:void 0!==l[2]?(H.test(l[2])&&(s=RegExp("</"+l[2],"g")),r=z):void 0!==l[3]&&(r=z):r===z?">"===l[0]?(r=null!=s?s:E,d=-1):void 0===l[1]?d=-2:(d=r.lastIndex-l[2].length,a=l[1],r=void 0===l[3]?z:'"'===l[3]?P:M):r===P||r===M?r=z:r===S||r===C?r=E:(r=z,s=void 0);const c=r===z&&t[e+1].startsWith("/>")?" ":"";n+=r===E?i+w:d>=0?(o.push(a),i.slice(0,d)+"$lit$"+i.slice(d)+_+c):i+_+(-2===d?(o.push(void 0),e):c)}const a=n+(t[i]||"<?>")+(2===e?"</svg>":"");if(!Array.isArray(t)||!t.hasOwnProperty("raw"))throw Error("invalid template strings array");return[void 0!==y?y.createHTML(a):a,o]};class O{constructor({strings:t,_$litType$:e},i){let o;this.parts=[];let s=0,n=0;const r=t.length-1,a=this.parts,[l,d]=L(t,e);if(this.el=O.createElement(l,i),R.currentNode=this.el.content,2===e){const t=this.el.content,e=t.firstChild;e.remove(),t.append(...e.childNodes)}for(;null!==(o=R.nextNode())&&a.length<r;){if(1===o.nodeType){if(o.hasAttributes()){const t=[];for(const e of o.getAttributeNames())if(e.endsWith("$lit$")||e.startsWith(_)){const i=d[n++];if(t.push(e),void 0!==i){const t=o.getAttribute(i.toLowerCase()+"$lit$").split(_),e=/([.?@])?(.*)/.exec(i);a.push({type:1,index:s,name:e[2],strings:t,ctor:"."===e[1]?W:"?"===e[1]?q:"@"===e[1]?X:V})}else a.push({type:6,index:s})}for(const e of t)o.removeAttribute(e)}if(H.test(o.tagName)){const t=o.textContent.split(_),e=t.length-1;if(e>0){o.textContent=f?f.emptyScript:"";for(let i=0;i<e;i++)o.append(t[i],x()),R.nextNode(),a.push({type:2,index:++s});o.append(t[e],x())}}}else if(8===o.nodeType)if(o.data===b)a.push({type:2,index:s});else{let t=-1;for(;-1!==(t=o.data.indexOf(_,t+1));)a.push({type:7,index:s}),t+=_.length-1}s++}}static createElement(t,e){const i=$.createElement("template");return i.innerHTML=t,i}}function T(t,e,i=t,o){var s,n,r,a;if(e===D)return e;let l=void 0!==o?null===(s=i._$Co)||void 0===s?void 0:s[o]:i._$Cl;const d=k(e)?void 0:e._$litDirective$;return(null==l?void 0:l.constructor)!==d&&(null===(n=null==l?void 0:l._$AO)||void 0===n||n.call(l,!1),void 0===d?l=void 0:(l=new d(t),l._$AT(t,i,o)),void 0!==o?(null!==(r=(a=i)._$Co)&&void 0!==r?r:a._$Co=[])[o]=l:i._$Cl=l),void 0!==l&&(e=T(t,l._$AS(t,e.values),l,o)),e}class j{constructor(t,e){this.u=[],this._$AN=void 0,this._$AD=t,this._$AM=e}get parentNode(){return this._$AM.parentNode}get _$AU(){return this._$AM._$AU}v(t){var e;const{el:{content:i},parts:o}=this._$AD,s=(null!==(e=null==t?void 0:t.creationScope)&&void 0!==e?e:$).importNode(i,!0);R.currentNode=s;let n=R.nextNode(),r=0,a=0,l=o[0];for(;void 0!==l;){if(r===l.index){let e;2===l.type?e=new I(n,n.nextSibling,this,t):1===l.type?e=new l.ctor(n,l.name,l.strings,this,t):6===l.type&&(e=new Y(n,this,t)),this.u.push(e),l=o[++a]}r!==(null==l?void 0:l.index)&&(n=R.nextNode(),r++)}return s}p(t){let e=0;for(const i of this.u)void 0!==i&&(void 0!==i.strings?(i._$AI(t,i,e),e+=i.strings.length-2):i._$AI(t[e])),e++}}class I{constructor(t,e,i,o){var s;this.type=2,this._$AH=U,this._$AN=void 0,this._$AA=t,this._$AB=e,this._$AM=i,this.options=o,this._$Cm=null===(s=null==o?void 0:o.isConnected)||void 0===s||s}get _$AU(){var t,e;return null!==(e=null===(t=this._$AM)||void 0===t?void 0:t._$AU)&&void 0!==e?e:this._$Cm}get parentNode(){let t=this._$AA.parentNode;const e=this._$AM;return void 0!==e&&11===t.nodeType&&(t=e.parentNode),t}get startNode(){return this._$AA}get endNode(){return this._$AB}_$AI(t,e=this){t=T(this,t,e),k(t)?t===U||null==t||""===t?(this._$AH!==U&&this._$AR(),this._$AH=U):t!==this._$AH&&t!==D&&this.g(t):void 0!==t._$litType$?this.$(t):void 0!==t.nodeType?this.T(t):(t=>A(t)||"function"==typeof(null==t?void 0:t[Symbol.iterator]))(t)?this.k(t):this.g(t)}O(t,e=this._$AB){return this._$AA.parentNode.insertBefore(t,e)}T(t){this._$AH!==t&&(this._$AR(),this._$AH=this.O(t))}g(t){this._$AH!==U&&k(this._$AH)?this._$AA.nextSibling.data=t:this.T($.createTextNode(t)),this._$AH=t}$(t){var e;const{values:i,_$litType$:o}=t,s="number"==typeof o?this._$AC(t):(void 0===o.el&&(o.el=O.createElement(o.h,this.options)),o);if((null===(e=this._$AH)||void 0===e?void 0:e._$AD)===s)this._$AH.p(i);else{const t=new j(s,this),e=t.v(this.options);t.p(i),this.T(e),this._$AH=t}}_$AC(t){let e=N.get(t.strings);return void 0===e&&N.set(t.strings,e=new O(t)),e}k(t){A(this._$AH)||(this._$AH=[],this._$AR());const e=this._$AH;let i,o=0;for(const s of t)o===e.length?e.push(i=new I(this.O(x()),this.O(x()),this,this.options)):i=e[o],i._$AI(s),o++;o<e.length&&(this._$AR(i&&i._$AB.nextSibling,o),e.length=o)}_$AR(t=this._$AA.nextSibling,e){var i;for(null===(i=this._$AP)||void 0===i||i.call(this,!1,!0,e);t&&t!==this._$AB;){const e=t.nextSibling;t.remove(),t=e}}setConnected(t){var e;void 0===this._$AM&&(this._$Cm=t,null===(e=this._$AP)||void 0===e||e.call(this,t))}}class V{constructor(t,e,i,o,s){this.type=1,this._$AH=U,this._$AN=void 0,this.element=t,this.name=e,this._$AM=o,this.options=s,i.length>2||""!==i[0]||""!==i[1]?(this._$AH=Array(i.length-1).fill(new String),this.strings=i):this._$AH=U}get tagName(){return this.element.tagName}get _$AU(){return this._$AM._$AU}_$AI(t,e=this,i,o){const s=this.strings;let n=!1;if(void 0===s)t=T(this,t,e,0),n=!k(t)||t!==this._$AH&&t!==D,n&&(this._$AH=t);else{const o=t;let r,a;for(t=s[0],r=0;r<s.length-1;r++)a=T(this,o[i+r],e,r),a===D&&(a=this._$AH[r]),n||(n=!k(a)||a!==this._$AH[r]),a===U?t=U:t!==U&&(t+=(null!=a?a:"")+s[r+1]),this._$AH[r]=a}n&&!o&&this.j(t)}j(t){t===U?this.element.removeAttribute(this.name):this.element.setAttribute(this.name,null!=t?t:"")}}class W extends V{constructor(){super(...arguments),this.type=3}j(t){this.element[this.name]=t===U?void 0:t}}const F=f?f.emptyScript:"";class q extends V{constructor(){super(...arguments),this.type=4}j(t){t&&t!==U?this.element.setAttribute(this.name,F):this.element.removeAttribute(this.name)}}class X extends V{constructor(t,e,i,o,s){super(t,e,i,o,s),this.type=5}_$AI(t,e=this){var i;if((t=null!==(i=T(this,t,e,0))&&void 0!==i?i:U)===D)return;const o=this._$AH,s=t===U&&o!==U||t.capture!==o.capture||t.once!==o.once||t.passive!==o.passive,n=t!==U&&(o===U||s);s&&this.element.removeEventListener(this.name,this,o),n&&this.element.addEventListener(this.name,this,t),this._$AH=t}handleEvent(t){var e,i;"function"==typeof this._$AH?this._$AH.call(null!==(i=null===(e=this.options)||void 0===e?void 0:e.host)&&void 0!==i?i:this.element,t):this._$AH.handleEvent(t)}}class Y{constructor(t,e,i){this.element=t,this.type=6,this._$AN=void 0,this._$AM=e,this.options=i}get _$AU(){return this._$AM._$AU}_$AI(t){T(this,t)}}const K=v.litHtmlPolyfillSupport;null==K||K(O,I),(null!==(g=v.litHtmlVersions)&&void 0!==g?g:v.litHtmlVersions=[]).push("2.6.1");
/**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */
var J,Z;class G extends m{constructor(){super(...arguments),this.renderOptions={host:this},this._$Do=void 0}createRenderRoot(){var t,e;const i=super.createRenderRoot();return null!==(t=(e=this.renderOptions).renderBefore)&&void 0!==t||(e.renderBefore=i.firstChild),i}update(t){const e=this.render();this.hasUpdated||(this.renderOptions.isConnected=this.isConnected),super.update(t),this._$Do=((t,e,i)=>{var o,s;const n=null!==(o=null==i?void 0:i.renderBefore)&&void 0!==o?o:e;let r=n._$litPart$;if(void 0===r){const t=null!==(s=null==i?void 0:i.renderBefore)&&void 0!==s?s:null;n._$litPart$=r=new I(e.insertBefore(x(),t),t,void 0,null!=i?i:{})}return r._$AI(t),r})(e,this.renderRoot,this.renderOptions)}connectedCallback(){var t;super.connectedCallback(),null===(t=this._$Do)||void 0===t||t.setConnected(!0)}disconnectedCallback(){var t;super.disconnectedCallback(),null===(t=this._$Do)||void 0===t||t.setConnected(!1)}render(){return D}}G.finalized=!0,G._$litElement$=!0,null===(J=globalThis.litElementHydrateSupport)||void 0===J||J.call(globalThis,{LitElement:G});const Q=globalThis.litElementPolyfillSupport;null==Q||Q({LitElement:G}),(null!==(Z=globalThis.litElementVersions)&&void 0!==Z?Z:globalThis.litElementVersions=[]).push("3.2.2");
/**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */
const tt=(t,e)=>"method"===e.kind&&e.descriptor&&!("value"in e.descriptor)?{...e,finisher(i){i.createProperty(e.key,t)}}:{kind:"field",key:Symbol(),placement:"own",descriptor:{},originalKey:e.key,initializer(){"function"==typeof e.initializer&&(this[e.key]=e.initializer.call(this))},finisher(i){i.createProperty(e.key,t)}};
/**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */function et(t){return(e,i)=>void 0!==i?((t,e,i)=>{e.constructor.createProperty(i,t)})(t,e,i):tt(t,e)
/**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */}const it=({finisher:t,descriptor:e})=>(i,o)=>{var s;if(void 0===o){const o=null!==(s=i.originalKey)&&void 0!==s?s:i.key,n=null!=e?{kind:"method",placement:"prototype",key:o,descriptor:e(i.key)}:{...i,key:o};return null!=t&&(n.finisher=function(e){t(e,o)}),n}{const s=i.constructor;void 0!==e&&Object.defineProperty(i,o,e(o)),null==t||t(s,o)}}
/**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */;
/**
   * @license
   * Copyright 2021 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */
var ot;null===(ot=window.HTMLSlotElement)||void 0===ot||ot.prototype.assignedElements;
/**
   * @license
   * Copyright 2019 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */
var st,nt,rt,at=window&&window.__decorate||function(t,e,i,o){var s,n=arguments.length,r=n<3?e:null===o?o=Object.getOwnPropertyDescriptor(e,i):o;if("object"==typeof Reflect&&"function"==typeof Reflect.decorate)r=Reflect.decorate(t,e,i,o);else for(var a=t.length-1;a>=0;a--)(s=t[a])&&(r=(n<3?s(r):n>3?s(e,i,r):s(e,i))||r);return n>3&&r&&Object.defineProperty(e,i,r),r};!function(t){t.Idle="idle",t.Move="move",t.Resize="resize"}(st||(st={})),function(t){t.None="none",t.Top="top",t.Right="right",t.Bottom="bottom",t.Left="left"}(nt||(nt={})),function(t){t.None="none",t.N="n",t.NE="ne",t.E="e",t.SE="se",t.S="s",t.SW="sw",t.W="w",t.NW="nw"}(rt||(rt={}));let lt=class extends G{constructor(){super(...arguments),this.open=!1,this.modal=!1,this.moveable=!1,this.moveBtn=!1,this.dock="none",this.push=!1,this.dockable=!1,this.hideCloseButton=!1,this.backdropOpacity=1,this.returnValue="",this.height=400,this.width=400,this.resizable=!1,this._dialogInteraction=st.Idle,this.title="",this._dragStartX=0,this._dragStartY=0,this._dragStartHeight=0,this._dragStartWidth=0,this._offsetTop=0,this._offsetLeft=0,this._resizeDirection=rt.None,this.styles=document.createElement("style"),this._onDock=(t="right")=>async()=>{const e=await this._dialog;e.style.removeProperty("inset"),e.style.removeProperty("height"),e.style.removeProperty("width"),this.dock=t,document.documentElement.style.setProperty("--me-dialog-dock-height",`${this.height}px`),document.documentElement.style.setProperty("--me-dialog-dock-width",`${this.width}px`),this._pushBody()},this._onUnDock=async()=>{this.dock="none"},this._onDragMouseUp=()=>{this._dialogInteraction=st.Idle,document.removeEventListener("mouseup",this._onDragMouseUp),document.removeEventListener("mousemove",this._onDragMouseMove)},this._onDragMouseMove=async t=>{const e=await this._dialog,i=t.clientX-this._dragStartX,o=t.clientY-this._dragStartY;switch(this._dialogInteraction){case st.Move:e.style.left=`${Math.min(Math.max(0,this._offsetLeft+i),window.innerWidth-e.offsetWidth)}px`,e.style.top=`${this._offsetTop+o}px`,e.style.right="auto",e.style.bottom="auto";break;case st.Resize:switch(this._resizeDirection){case rt.N:this.height=Math.min(this._dragStartHeight-o,window.innerHeight),document.documentElement.style.setProperty("--me-dialog-dock-height",`${this.height}px`);break;case rt.E:this.width=Math.min(this._dragStartWidth+i,window.innerWidth),document.documentElement.style.setProperty("--me-dialog-dock-width",`${this.width}px`);break;case rt.S:this.height=Math.min(this._dragStartHeight+o,window.innerHeight),document.documentElement.style.setProperty("--me-dialog-dock-height",`${this.height}px`);break;case rt.W:this.width=Math.max(0,Math.min(this._dragStartWidth-i,window.innerWidth)),document.documentElement.style.setProperty("--me-dialog-dock-width",`${this.width}px`)}}},this._pushBody=()=>{const t={top:"",right:"",bottom:"",left:""};if(this.open&&this.push)switch(this.dock){case"top":t.top="padding-top: var(--me-dialog-offset-top, var(--me-dialog-dock-height)) !important;";break;case"right":t.right="padding-right: var(--me-dialog-offset-right, var(--me-dialog-dock-width)) !important;";break;case"bottom":t.bottom="padding-bottom: var(--me-dialog-offset-bottom, var(--me-dialog-dock-height)) !important;";break;case"left":t.left="padding-left: var(--me-dialog-offset-left, var(--me-dialog-dock-width)) !important;"}this.styles.innerHTML=`body {\n      transition: padding var(--me-dialog-duration, 200) var(--me-dialog-timing, ease-out);\n      ${Object.values(t).filter((t=>t)).join("\n")}\n    }`}}connectedCallback(){super.connectedCallback(),this.styles.setAttribute("class","mercury-dialog-styles"),document.head.appendChild(this.styles)}disconnectedCallback(){super.disconnectedCallback(),this.styles.remove()}_getResizeDirection(){switch(this.dock){case"top":return rt.S;case"right":return rt.W;case"bottom":return rt.N;case"left":return rt.E;default:return rt.None}}async _handleClose(){const t=await this._dialog;this.returnValue=t.returnValue,this.open=!1,this._pushBody(),this.dispatchEvent(new Event("close"))}async _handleCancel(){const t=await this._dialog;this.returnValue=t.returnValue,this.dispatchEvent(new Event("cancel"))}async _keydownHandler(t){if("Escape"===t.code){const t=this.getRootNode(),e=t.querySelector(`#${this.id}`);e&&!this.hideCloseButton&&e.contains(t.activeElement)&&this._handleClose()}}render(){return B`
      <dialog
        id="dialog"
        part="dialog"
        data-dock=${this.dock}
        @close=${this._handleClose}
        @cancel=${this._handleCancel}
        class=${[this.moveable&&"is-moveable",!this.resizable||"none"!==this.dock&&this.dock?"not-resizable":"is-resizable",`is-${this._dialogInteraction}`].join(" ")}
      >
        ${this.title||!this.hideCloseButton?B`<header @mousedown=${this._onMoveMouseDown}>
              ${this.title?B`<h2>${this.title}</h2>`:B``}
              <div class="buttons">
                ${this.moveable&&this.moveBtn?B`<button
                      @mousedown=${this._onMoveMouseDown}
                      part="drag-button"
                      id="dragButton"
                    >
                      <i></i>
                      <span>Drag</span>
                    </button>`:B``}
                ${this.dockable?"none"===this.dock?B`<button
                        @click=${this._onDock()}
                        part="dock-button"
                        id="dockButton"
                      >
                        <i></i>
                        <span>Dock</span>
                      </button>`:B`<button
                        @click=${this._onUnDock}
                        part="undock-button"
                        id="undockButton"
                      >
                      <i></i>
                      <span>Undock</span>
                      </button>`:""}
                <form method="dialog">
                  ${this.hideCloseButton?B``:B`<button
                      @click=${this._onCloseClick}
                      part="close-button"
                      id="closeButton"
                    >
                      <i></i>
                      <span>Close</span>
                    </button>`}
                </form>
              </div>
            </header>`:B``}
          ${this.resizable&&"none"!==this.dock?B`<button
            @mousedown=${this._onResizeMouseDown}
            id="resizeButton"
            data-resize-dir=${this._getResizeDirection()}
            >
              <span>Resize</span>
            </button>`:B``}
          <main>
            <slot></slot>
          </main>
          <footer @mousedown=${this._onMoveMouseDown}>
            <slot name="footer"></slot>
          </footer>
        </dialog>
    `}updated(t){(t.has("dock")||t.has("push")||t.has("modal"))&&this._pushBody(),this.open?this.getRootNode().addEventListener("keydown",(t=>this._keydownHandler(t))):this.getRootNode().removeEventListener("keydown",(t=>this._keydownHandler(t)))}async showModal(){this.modal=!0,this.open=!0,this.isDocked()&&this._onDock(),(await this._dialog).showModal(),this.dispatchEvent(new Event("open"))}async show(){this.modal=!1,this.open=!0,this.isDocked()&&await this._onDock(this.dock)(),(await this._dialog).show(),this.dispatchEvent(new Event("open"))}async close(){this.isDocked()&&await this._onDock(this.dock)(),(await this._dialog).close()}isDocked(){return this.dock&&"none"!==this.dock}_onCloseClick(){this.open=!1}async _onResizeMouseDown(t){if(this._dialogInteraction=st.Resize,t.target instanceof HTMLButtonElement){const e=t.target.getAttribute("data-resize-dir");this._resizeDirection=e}this._onDragMouseDown(t)}async _onMoveMouseDown(t){this.moveable&&(this._dialogInteraction=st.Move,this.dock="none",this._onDragMouseDown(t))}async _onDragMouseDown(t){const e=await this._dialog;this._dragStartX=t.clientX,this._dragStartY=t.clientY,this._dragStartHeight=this.height,this._dragStartWidth=this.width,this._offsetLeft=e.offsetLeft,this._offsetTop=e.offsetTop,document.addEventListener("mouseup",this._onDragMouseUp),document.addEventListener("mousemove",this._onDragMouseMove)}};lt.styles=((t,...e)=>{const o=1===t.length?t[0]:e.reduce(((e,i,o)=>e+(t=>{if(!0===t._$cssResult$)return t.cssText;if("number"==typeof t)return t;throw Error("Value passed to 'css' function must be a 'css' function result: "+t+". Use 'unsafeCSS' to pass non-literal values, but take care to ensure page security.")})(i)+t[o+1]),t[0]);return new s(o,t,i)})`
    :host {
      --me-resize-button-size: 8px;
      display: block;
      font-family: var(--me-font-family, sans-serif);
      z-index: var(--me-dialog-z-index, 1255);
      position: relative;
    }

    dialog {
      border-style: var(--me-border-style, solid);
      border-width: var(--me-border-width, 1px);
      border-color: var(--me-border-color, #e5e5e5);
      box-shadow: 0 1px 2px rgb(20 45 82 / 2%), 0 3px 4px rgb(20 45 82 / 3%), 0 5px 8px rgb(20 45 82 / 4%);
      padding: 0;
      position: fixed;
      margin: auto;
      inset: var(--me-dialog-position-top, 0px) var(--me-dialog-position-right, 0px) var(--me-dialog-position-bottom, 0px) var(--me-dialog-position-left, 0px);
      overflow: auto;
      width: var(--me-dialog-width, var(--me-dialog-width-default, fit-content));
      height: var(--me-dialog-height, var(--me-dialog-height-default, fit-content));
      min-width: var(--me-dialog-min-width, min-content);
      max-width: calc(100vw - var(--me-dialog-viewport-offset, 80px));
      max-height: calc(100vh - var(--me-dialog-viewport-offset, 80px));
      z-index: 1000;
    }

    dialog.is-resizable {
      resize: both;
    }

    dialog.is-resize ::slotted(*) {
      pointer-events: none;
    }

    dialog[open] {
      display: flex;
      flex-direction: column;
      justify-content: stretch;
    }

    dialog[data-dock='left'] {
      height: auto;
      inset: var(--me-dialog-position-top, 0px) var(--me-dialog-position-right, auto) var(--me-dialog-position-bottom, 0px) var(--me-dialog-position-left, 0px);
      margin: 0 auto 0 0;
      max-height: 100dvh;
      max-width: 100dvw;
      width: var(--me-dialog-dock-width, var(--me-dialog-width-default, 400px));
    }

    dialog[data-dock='right'] {
      height: auto;
      inset: var(--me-dialog-position-top, 0px) var(--me-dialog-position-right, 0px) var(--me-dialog-position-bottom, 0px) var(--me-dialog-position-left, auto);
      margin: 0 0 0 auto;
      max-height: 100dvh;
      max-width: 100dvw;
      width: var(--me-dialog-dock-width, var(--me-dialog-width-default, 400px));
    }

    dialog[data-dock='bottom'] {
      inset: var(--me-dialog-position-top, auto) var(--me-dialog-position-right, 0px) var(--me-dialog-position-bottom, 0px) var(--me-dialog-position-left, 0px);
      margin: auto 0 0 0;
      width: auto;
      max-width: 100dvw;
      height: var(--me-dialog-dock-height, var(--me-dialog-height-default, 400px));
    }

    dialog[data-dock='top'] {
      inset: var(--me-dialog-position-top, 0px) var(--me-dialog-position-right, 0px) var(--me-dialog-position-bottom, auto) var(--me-dialog-position-left, 0px);
      margin: 0 0 auto 0;
      width: auto;
      max-width: 100dvw;
      height: var(--me-dialog-dock-height, var(--me-dialog-height-default, 400px));
    }

    dialog::backdrop {
      background-color: black;
      opacity: 0.4;
    }

    #dragButton {
      cursor: move;
    }

    header {
      background-color: var(--me-dialog-header-background-color, #fff);
      display: flex;
      justify-content: space-between;
      padding: var(--me-dialog-header-space-inset-y, var(--me-space-inset-y, 5px)) var(--me-dialog-header-space-inset-x, var(--me-space-inset-x, 20px));
      border-bottom-style: var(--me-border-style, solid);
      border-bottom-width: var(--me-border-width, 1px);
      border-bottom-color: var(--me-border-color, #e5e5e5);
      position: sticky;
      top: 0;
    }

    dialog.is-moveable header {
      cursor: grab;
    }

    h2 {
      font-size: var(--me-label-font-size, 16px);
      line-height: var(--me-label-line-height, 1.1);
      margin-inline-end: var(--me-space-inset, .5em);
    }

    main {
      position: relative;
      background-color: var(--me-dialog-main-background-color, #fff);
      padding: var(--me-dialog-main-space-inset-y, var(--me-space-inset-y, 20px)) var(--me-dialog-main-space-inset-y, var(--me-space-inset-y, 20px));
      overflow-y: auto;
      flex-grow: 1;
      flex-shrink: 1;
      flex-basis: fit-content;
    }

    .buttons {
      display: flex;
      justify-content: flex-end;
      margin-inline-end: -10px;
    }

    form[method='dialog'] {
      align-items: stretch;
      display: flex;
    }

    button {
      height: var(--me-dialog-icon-button-height, 40px);
      width: var(--me-dialog-icon-button-width, 40px);
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      border-radius: var(--me-dialog-icon-button-radius, 3px);
      background-color: var(--me-dialog-icon-button-background-color, #fff);
      transition: all .2s ease-out;
    }

    button:hover {
      background-color: var(--me-dialog-icon-button-background-color-hover, #efefef);
    }

    button[data-resize-dir] {
      position: absolute;
      opacity: .5;
      border-radius: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      z-index: 10;
    }

    button[data-resize-dir]:after {
      content: "";
      display: block;
      border: 0 #888 solid;
    }

    button[data-resize-dir="e"],
    button[data-resize-dir="w"] {
      cursor: ew-resize;
      height: auto;
      width: var(--me-resize-button-size);
    }

    button[data-resize-dir="e"]:after,
    button[data-resize-dir="w"]:after {
      height: 2em;
      width: 3px;
      border-left-width: 1px;
      border-right-width: 1px;
    }

    button[data-resize-dir="e"] {
      inset: 0 0 0 auto;
      justify-content: flex-start;
    }

    button[data-resize-dir="w"] {
      inset: 0 auto 0 0;
      justify-content: flex-end;
    }

    button[data-resize-dir="n"],
    button[data-resize-dir="s"] {
      cursor: ns-resize;
      height: var(--me-resize-button-size);
      width: auto;
    }

    button[data-resize-dir="n"]:after,
    button[data-resize-dir="s"]:after {
      width: 2em;
      height: 3px;
      border-top-width: 1px;
      border-bottom-width: 1px;
    }

    button[data-resize-dir="n"] {
      inset: 0 0 auto 0;
      align-items: flex-end;
    }

    button[data-resize-dir="s"] {
      inset: auto 0 0 0;
      align-items: flex-start;
    }

    ::slotted([slot="footer"]) {
      background-color: var(--me-dialog-footer-background-color, #fff);
      padding: var(--me-dialog-footer-space-inset-y, var(--me-space-inset-y, 5px)) var(--me-dialog-footer-space-inset-x, var(--me-space-inset-x, 20px));
      border-top-style: var(--me-border-style, solid);
      border-top-width: var(--me-border-width, 1px);
      border-top-color: var(--me-border-color, #e5e5e5);
      position: sticky;
      bottom: 0;
    }

    footer ::slotted(button) {
    }
    dialog.is-moveable footer {
      cursor: grab;
    }

    button i {
      background-position: center;
      background-repeat: no-repeat;
      display: block;
      height: 50%;
      width: 50%;
    }

    button span {
      position: absolute !important;
      overflow: hidden;
      clip: rect(1px,1px,1px,1px);
      width: 1px;
      height: 1px;
      word-wrap: normal;
    }

    #closeButton i {
      background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc.--><path d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3l105.4 105.3c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256l105.3-105.4z"/></svg>');
    }

    #dragButton i {
      background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc.--><path d="M278.6 9.4c-12.5-12.5-32.8-12.5-45.3 0l-64 64c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l9.4-9.4V224H109.3l9.4-9.4c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-64 64c-12.5 12.5-12.5 32.8 0 45.3l64 64c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-9.4-9.4H224v114.8l-9.4-9.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l64 64c12.5 12.5 32.8 12.5 45.3 0l64-64c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-9.4 9.4V288h114.8l-9.4 9.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l64-64c12.5-12.5 12.5-32.8 0-45.3l-64-64c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l9.4 9.4H288V109.3l9.4 9.4c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-64-64z"/></svg>');
    }

    #dockButton i {
      background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc.--><path d="M7.724 65.49A64.308 64.308 0 0 1 32 40.56a63.55 63.55 0 0 1 25.46-8.23c2.15-.22 4.33-.33 6.54-.33h384c35.3 0 64 28.65 64 64v320c0 35.3-28.7 64-64 64H64c-35.35 0-64-28.7-64-64V96c0-2.21.112-4.39.33-6.54a63.634 63.634 0 0 1 7.394-23.97zM48 416c0 8.8 7.16 16 16 16h384c8.8 0 16-7.2 16-16V224H48v192z"/></svg>');
    }

    #undockButton i {
      background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc.--><path d="M432 48H208c-17.7 0-32 14.33-32 32v16h-48V80c0-44.18 35.8-80 80-80h224c44.2 0 80 35.82 80 80v224c0 44.2-35.8 80-80 80h-16v-48h16c17.7 0 32-14.3 32-32V80c0-17.67-14.3-32-32-32zm-112 80c35.3 0 64 28.7 64 64v256c0 35.3-28.7 64-64 64H64c-35.35 0-64-28.7-64-64V192c0-35.3 28.65-64 64-64h256zM64 464h256c8.8 0 16-7.2 16-16V256H48v192c0 8.8 7.16 16 16 16z"/></svg>');
    }
  `,at([et({type:Boolean,reflect:!0})],lt.prototype,"open",void 0),at([et({type:Boolean,reflect:!0})],lt.prototype,"modal",void 0),at([et({type:Boolean,reflect:!0})],lt.prototype,"moveable",void 0),at([et({type:Boolean,reflect:!0})],lt.prototype,"moveBtn",void 0),at([et({type:nt,reflect:!0})],lt.prototype,"dock",void 0),at([et({type:Boolean,reflect:!0})],lt.prototype,"push",void 0),at([et({type:Boolean,reflect:!0})],lt.prototype,"dockable",void 0),at([et({type:Boolean,reflect:!0,attribute:"hide-close-button"})],lt.prototype,"hideCloseButton",void 0),at([et({type:Number,reflect:!0})],lt.prototype,"backdropOpacity",void 0),at([et({type:String,attribute:!1})],lt.prototype,"returnValue",void 0),at([et({type:Number,reflect:!0})],lt.prototype,"height",void 0),at([et({type:Number,reflect:!0})],lt.prototype,"width",void 0),at([et({type:Boolean,reflect:!0})],lt.prototype,"resizable",void 0),at([et({type:st,attribute:!1})],lt.prototype,"_dialogInteraction",void 0),at([et({type:String,reflect:!0})],lt.prototype,"title",void 0),at([function(t){return it({descriptor:e=>({async get(){var e;return await this.updateComplete,null===(e=this.renderRoot)||void 0===e?void 0:e.querySelector(t)},enumerable:!0,configurable:!0})})}("#dialog")],lt.prototype,"_dialog",void 0),lt=at([(t=>e=>"function"==typeof e?((t,e)=>(customElements.define(t,e),e))(t,e):((t,e)=>{const{kind:i,elements:o}=e;return{kind:i,elements:o,finisher(e){customElements.define(t,e)}}})(t,e))("mercury-dialog")],lt)}();
;
(function(){'use strict';(function($,Drupal,drupalSettings){function dispatchDialogEvent(eventType,dialog,element,settings){if(typeof DrupalDialogEvent==='undefined')$(window).trigger(`dialog:${eventType}`,[dialog,$(element),settings]);else{const event=new DrupalDialogEvent(eventType,dialog,settings||{});element.dispatchEvent(event);}}Drupal.mercuryDialog=function(element,options){var undef;var $element=$(element);$element.dialog=()=>$element;var dialogElement;var dialog={open:false,returnValue:undef};function onDockResize(entries){for(const entry of entries){const element=entry.target;const dockDirection=element.getAttribute('data-dock');if(!element.open||!dockDirection||dockDirection=='none')return;const resizeEvent=new CustomEvent('mercury:dockResize',{detail:{width:entry.contentRect.width,height:entry.contentRect.height},bubbles:true});dialogElement.setAttribute('width',entry.contentRect.width);dialogElement.dispatchEvent(resizeEvent);}}const dockObserver=new ResizeObserver(onDockResize);function onDialogMutate(records){dockObserver.observe(dialogElement.shadowRoot.querySelector('dialog'));}const dialogMutationObserver=new MutationObserver(onDialogMutate);function getCSSLength(value){if(typeof value==='undefined'||!/^\d+$/.test(value))return value;return `${value}px`;}function applyOptions(dialogElement,options){const attributeOptions=['title','modal','dock','push','resizable','moveable'];attributeOptions.forEach((option)=>{if(typeof options[option]!=='undefined')dialogElement.setAttribute(option,options[option]);});if(options.dialogClass)dialogElement.classList.add(...options.dialogClass.split(' '));if(dialogElement.id==='me-edit-screen'){let isTrayCollapsed=localStorage.getItem('mercury-dialog-dock-collapsed')==='true';let savedWidth=localStorage.getItem('mercury-dialog-dock-width');let savedHeight=localStorage.getItem('mercury-dialog-dock-height');if(!isTrayCollapsed){let dialogWidth=savedWidth||options.width;let dialogHeight=savedHeight||options.height;if(dialogWidth){dialogElement.setAttribute('width',dialogWidth);document.documentElement.style.setProperty('--me-dialog-dock-width',getCSSLength(dialogWidth));}if(dialogHeight){dialogElement.setAttribute('height',dialogHeight);document.documentElement.style.setProperty('--me-dialog-dock-height',getCSSLength(dialogHeight));}}else{dialogElement.setAttribute('width','10');document.documentElement.style.setProperty('--me-dialog-dock-width','10px');}}else{if(options.width){dialogElement.setAttribute('width',options.width);document.documentElement.style.setProperty('--me-dialog-width',getCSSLength(options.width));}if(options.height){dialogElement.setAttribute('height',options.height);document.documentElement.style.setProperty('--me-dialog-height',getCSSLength(options.height));}}if(options.drupalAutoButtons&&!options.buttons)options.buttons=Drupal.behaviors.mercuryDialog.prepareDialogButtons($(dialogElement));if(options.buttons&&options.buttons.length)_createButtonPane(options.buttons);return dialogElement;}function _appendTo(){var element=options.appendTo;if(element&&(element.jquery||element.nodeType))return $(element);return $(document).find(element||"body").eq(0);}function _createButtonPane(buttons){const existing=dialogElement.querySelector('.me-dialog__buttonpane');if(existing)existing.remove();const uiDialogButtonPane=document.createElement('div');uiDialogButtonPane.setAttribute('slot','footer');uiDialogButtonPane.classList.add('me-dialog__buttonpane');dialogElement.appendChild(uiDialogButtonPane);if($.isEmptyObject(buttons)||(Array.isArray(buttons)&&!buttons.length))return;buttons.forEach((props)=>{const button=document.createElement('button');button.classList=props.class;button.classList.add('button');button.appendChild(document.createTextNode(props.text));button.addEventListener('click',props.click);uiDialogButtonPane.appendChild(button);});}function init(settings){if(element.tagName!=='MERCURY-DIALOG'){const wrapper=$('<mercury-dialog>').append($element).appendTo(_appendTo());dialogElement=wrapper[0];}else dialogElement=element;applyOptions(dialogElement,settings);}function openDialog(settings){settings={...drupalSettings.dialog,...drupalSettings.mercuryEditor,...options,...settings};dispatchDialogEvent('beforecreate',dialog,$element.get(0),settings);init(settings);dialogElement[settings.modal?'showModal':'show']();const originalResizeSetting=settings.autoResize;settings.autoResize=false;dispatchDialogEvent('aftercreate',dialog,$element.get(0),settings);settings.autoResize=originalResizeSetting;dialogMutationObserver.observe(dialogElement,{childList:true,attributes:true});dialogElement.addEventListener('close',function(){closeDialog();});}function closeDialog(value){dispatchDialogEvent('beforeclose',dialog,$element.get(0));dockObserver.disconnect();dialogMutationObserver.disconnect();Drupal.detachBehaviors(element,null,'unload');element.close();dialog.returnValue=value;dispatchDialogEvent('afterclose',dialog,$element.get(0));$element.remove();}dialog.show=function(){openDialog({modal:false});};dialog.showModal=function(){openDialog({modal:true});};dialog.applyOptions=function(options){init(options);};dialog.close=closeDialog;return dialog;};Drupal.behaviors.mercuryDialog={attach:function(context,settings){if(!$('#drupal-mercury-dialog').length)$('<mercury-dialog id="drupal-mercury-dialog"></mercury-dialog>').appendTo('body');const $dialog=$(context).closest('mercury-dialog');if($dialog.length)$dialog.trigger('dialogButtonsChange');},prepareDialogButtons:function prepareDialogButtons($dialog){var buttons=[];var $buttons=$dialog.find('.form-actions').last().find('input[type=submit], a.button, a.action-link');$buttons.each(function(){var $originalButton=$(this).css({display:'none'});buttons.push({text:$originalButton.html()||$originalButton.attr('value'),class:$originalButton.attr('class'),click:function click(e){if($originalButton.is('a'))$originalButton[0].click();else{$originalButton.trigger('mousedown').trigger('mouseup').trigger('click');e.preventDefault();}}});});return buttons;}};function moveFormButtonsToDialog(event,dialog,$dialog){if($dialog[0].tagName!=='MERCURY-DIALOG')return;if($dialog.attr('id').indexOf('lpb-dialog-')===0){const buttons=Drupal.behaviors.mercuryDialog.prepareDialogButtons($dialog);if(buttons.length)Drupal.mercuryDialog($dialog[0]).applyOptions({buttons});}}$(window).on('dialog:aftercreate',moveFormButtonsToDialog);function onBodyResize(iframe){return (entries)=>{if(iframe&&entries.length){iframe.style.height=`${entries[0].borderBoxSize[0].blockSize+1}px`;iframe.style.width=`${entries[0].borderBoxSize[0].inlineSize+1}px`;}};}function setFrameBodyMaxWidth(iframe,framedBody){const dialogStyles=window.getComputedStyle(iframe.closest('mercury-dialog').shadowRoot.querySelector('dialog'));const dialogMainStyles=window.getComputedStyle(iframe.closest('mercury-dialog').shadowRoot.querySelector('main'));framedBody.style.maxWidth=`calc(${dialogStyles.getPropertyValue('max-width')} - ${dialogMainStyles.getPropertyValue('padding-left')} - ${dialogMainStyles.getPropertyValue('padding-right')} - 2px)`;}function resizeIframe(iframe){const framedBody=iframe.contentWindow.document.body;framedBody.style.width='max-content';framedBody.style.height='fit-content';setFrameBodyMaxWidth(iframe,framedBody);new ResizeObserver(onBodyResize(iframe)).observe(framedBody,{box:'border-box'});}function updateIframeSize(event,dialog,$dialog){if($dialog[0].tagName!=='MERCURY-DIALOG')return;const iframe=$dialog[0].querySelector('iframe');if(!iframe)return;$dialog[0].style.setProperty('--me-dialog-height-default','fit-content');iframe.onload=function(){resizeIframe(iframe);setFrameBodyMaxWidth(iframe,framedBody);};const framedBody=iframe?.contentWindow?.document?.body;if(framedBody)window.addEventListener('resize',function(){setFrameBodyMaxWidth(iframe,framedBody);});}$(window).on('dialog:aftercreate',updateIframeSize);const modalStack=[];function addModalToStack(event,dialog,$dialog){if($dialog[0].tagName!=='MERCURY-DIALOG')return;if($dialog[0].hasAttribute('modal')&&$dialog[0].getAttribute('modal')!=='false')modalStack.push($dialog);}$(window).on('dialog:aftercreate',addModalToStack);function removeModalFromStack(event,dialog,$dialog){if($dialog[0].tagName!=='MERCURY-DIALOG')return;if($dialog[0].hasAttribute('modal')&&$dialog[0].getAttribute('modal')!=='false'){const index=modalStack.indexOf($dialog);if(index>-1)modalStack.splice(index,1);}}$(window).on('dialog:beforeclose',removeModalFromStack);function nestDialogInModal(event,dialog,$dialog){if($dialog[0].tagName!=='MERCURY-DIALOG')return;if(modalStack.length>0){const $parent=$dialog.parent('.ui-dialog');const $overlay=$parent.next('.ui-widget-overlay');modalStack.slice(-1)[0].append([$parent,$overlay]);}}$(window).on('dialog:aftercreate',nestDialogInModal);})(jQuery,Drupal,drupalSettings);})();;
(function($,Drupal,once){if(once('drupal-dialog-deprecation-listener','html').length){const eventSpecial={handle($event){const $element=$($event.target);const event=$event.originalEvent;const dialog=event.dialog;const dialogArguments=[$event,dialog,$element,event?.settings];$event.handleObj.handler.apply(this,dialogArguments);}};$.event.special['dialog:beforecreate']=eventSpecial;$.event.special['dialog:aftercreate']=eventSpecial;$.event.special['dialog:beforeclose']=eventSpecial;$.event.special['dialog:afterclose']=eventSpecial;const listenDialogEvent=(event)=>{const windowEvents=$._data(window,'events');const isWindowHasDialogListener=windowEvents[event.type];if(isWindowHasDialogListener)Drupal.deprecationError({message:`jQuery event ${event.type} is deprecated in 10.3.0 and is removed from Drupal:12.0.0. See https://www.drupal.org/node/3422670`});};['dialog:beforecreate','dialog:aftercreate','dialog:beforeclose','dialog:afterclose'].forEach((e)=>window.addEventListener(e,listenDialogEvent));}})(jQuery,Drupal,once);;
class DrupalDialogEvent extends Event{constructor(type,dialog,settings=null){super(`dialog:${type}`,{bubbles:true});this.dialog=dialog;this.settings=settings;}}(function($,Drupal,drupalSettings,bodyScrollLock){drupalSettings.dialog={autoOpen:true,dialogClass:'',buttonClass:'button',buttonPrimaryClass:'button--primary',close(event){Drupal.dialog(event.target).close();Drupal.detachBehaviors(event.target,null,'unload');}};Drupal.dialog=function(element,options){let undef;const $element=$(element);const domElement=$element.get(0);const dialog={open:false,returnValue:undef};function openDialog(settings){settings=$.extend({},drupalSettings.dialog,options,settings);const event=new DrupalDialogEvent('beforecreate',dialog,settings);domElement.dispatchEvent(event);$element.dialog(event.settings);dialog.open=true;if(event.settings.modal)bodyScrollLock.lock(domElement);domElement.dispatchEvent(new DrupalDialogEvent('aftercreate',dialog,event.settings));}function closeDialog(value){domElement.dispatchEvent(new DrupalDialogEvent('beforeclose',dialog));bodyScrollLock.clearBodyLocks();$element.dialog('close');dialog.returnValue=value;dialog.open=false;domElement.dispatchEvent(new DrupalDialogEvent('afterclose',dialog));}dialog.show=()=>{openDialog({modal:false});};dialog.showModal=()=>{openDialog({modal:true});};dialog.close=closeDialog;return dialog;};})(jQuery,Drupal,drupalSettings,bodyScrollLock);;
(function($,Drupal,drupalSettings,debounce,displace){drupalSettings.dialog=$.extend({autoResize:true,maxHeight:'95%'},drupalSettings.dialog);function resetPosition(options){const offsets=displace.offsets;const left=offsets.left-offsets.right;const top=offsets.top-offsets.bottom;const leftString=`${(left>0?'+':'-')+Math.abs(Math.round(left/2))}px`;const topString=`${(top>0?'+':'-')+Math.abs(Math.round(top/2))}px`;options.position={my:`center${left!==0?leftString:''} center${top!==0?topString:''}`,of:window};return options;}function resetSize(event){const positionOptions=['width','height','minWidth','minHeight','maxHeight','maxWidth','position'];let adjustedOptions={};let windowHeight=$(window).height();let option;let optionValue;let adjustedValue;for(let n=0;n<positionOptions.length;n++){option=positionOptions[n];optionValue=event.data.settings[option];if(optionValue)if(typeof optionValue==='string'&&optionValue.endsWith('%')&&/height/i.test(option)){windowHeight-=displace.offsets.top+displace.offsets.bottom;adjustedValue=parseInt(0.01*parseInt(optionValue,10)*windowHeight,10);if(option==='height'&&Math.round(event.data.$element.parent().outerHeight())<adjustedValue)adjustedValue='auto';adjustedOptions[option]=adjustedValue;}}if(!event.data.settings.modal)adjustedOptions=resetPosition(adjustedOptions);event.data.$element.dialog('option',adjustedOptions);event.data.$element?.get(0)?.dispatchEvent(new CustomEvent('dialogContentResize',{bubbles:true}));}window.addEventListener('dialog:aftercreate',(e)=>{const autoResize=debounce(resetSize,20);const $element=$(e.target);const {settings}=e;const eventData={settings,$element};if(settings.autoResize===true||settings.autoResize==='true'){const uiDialog=$element.dialog('option',{resizable:false,draggable:false}).dialog('widget');uiDialog[0].style.position='fixed';$(window).on('resize.dialogResize scroll.dialogResize',eventData,autoResize).trigger('resize.dialogResize');$(document).on('drupalViewportOffsetChange.dialogResize',eventData,autoResize);}});window.addEventListener('dialog:beforeclose',()=>{$(window).off('.dialogResize');$(document).off('.dialogResize');});})(jQuery,Drupal,drupalSettings,Drupal.debounce,Drupal.displace);;
(function($,{tabbable,isTabbable}){$.widget('ui.dialog',$.ui.dialog,{options:{buttonClass:'button',buttonPrimaryClass:'button--primary'},_createButtons(){const opts=this.options;let primaryIndex;let index;const il=opts.buttons.length;for(index=0;index<il;index++)if(opts.buttons[index].primary&&opts.buttons[index].primary===true){primaryIndex=index;delete opts.buttons[index].primary;break;}this._super();const $buttons=this.uiButtonSet.children().addClass(opts.buttonClass);if(typeof primaryIndex!=='undefined')$buttons.eq(index).addClass(opts.buttonPrimaryClass);},_focusTabbable(){let hasFocus=this._focusedElement?this._focusedElement.get(0):null;if(!hasFocus)hasFocus=this.element.find('[autofocus]').get(0);if(!hasFocus){const $elements=[this.element,this.uiDialogButtonPane];for(let i=0;i<$elements.length;i++){const element=$elements[i].get(0);if(element){const elementTabbable=tabbable(element);hasFocus=elementTabbable.length?elementTabbable[0]:null;}if(hasFocus)break;}}if(!hasFocus){const closeBtn=this.uiDialogTitlebarClose.get(0);hasFocus=closeBtn&&isTabbable(closeBtn)?closeBtn:null;}if(!hasFocus)hasFocus=this.uiDialog.get(0);$(hasFocus).eq(0).trigger('focus');}});})(jQuery,window.tabbable);;
(($)=>{$.widget('ui.dialog',$.ui.dialog,{_allowInteraction(event){if(event.target.classList===undefined)return this._super(event);return event.target.classList.contains('ck')||this._super(event);}});})(jQuery);;
(function($,Drupal,{focusable}){Drupal.behaviors.dialog={attach(context,settings){const $context=$(context);if(!$('#drupal-modal').length)$('<div id="drupal-modal" class="ui-front"></div>').hide().appendTo('body');const $dialog=$context.closest('.ui-dialog-content');if($dialog.length){if($dialog.dialog('option','drupalAutoButtons'))$dialog.trigger('dialogButtonsChange');setTimeout(function(){if(!$dialog[0].contains(document.activeElement)){$dialog.dialog('instance')._focusedElement=null;$dialog.dialog('instance')._focusTabbable();}},0);}const originalClose=settings.dialog.close;settings.dialog.close=function(event,...args){originalClose.apply(settings.dialog,[event,...args]);const $element=$(event.target);const ajaxContainer=$element.data('uiDialog')?$element.data('uiDialog').opener.closest('[data-drupal-ajax-container]'):[];if(ajaxContainer.length&&(document.activeElement===document.body||$(document.activeElement).not(':visible'))){const focusableChildren=focusable(ajaxContainer[0]);if(focusableChildren.length>0)setTimeout(()=>{focusableChildren[0].focus();},0);}$(event.target).remove();};},prepareDialogButtons($dialog){const buttons=[];const $buttons=$dialog.find('.form-actions input[type=submit], .form-actions a.button, .form-actions a.action-link');$buttons.each(function(){const $originalButton=$(this);this.style.display='none';buttons.push({text:$originalButton.html()||$originalButton.attr('value'),class:$originalButton.attr('class'),'data-once':$originalButton.data('once'),click(e){if($originalButton[0].tagName==='A')$originalButton[0].click();else $originalButton.trigger('mousedown').trigger('mouseup').trigger('click');e.preventDefault();}});});return buttons;}};Drupal.AjaxCommands.prototype.openDialog=function(ajax,response,status){if(!response.selector)return false;let $dialog=$(response.selector);if(!$dialog.length)$dialog=$(`<div id="${response.selector.replace(/^#/,'')}" class="ui-front"></div>`).appendTo('body');if(!ajax.wrapper)ajax.wrapper=$dialog.attr('id');response.command='insert';response.method='html';ajax.commands.insert(ajax,response,status);response.dialogOptions=response.dialogOptions||{};if(typeof response.dialogOptions.drupalAutoButtons==='undefined')response.dialogOptions.drupalAutoButtons=true;else if(response.dialogOptions.drupalAutoButtons==='false')response.dialogOptions.drupalAutoButtons=false;else response.dialogOptions.drupalAutoButtons=!!response.dialogOptions.drupalAutoButtons;if(!response.dialogOptions.buttons&&response.dialogOptions.drupalAutoButtons)response.dialogOptions.buttons=Drupal.behaviors.dialog.prepareDialogButtons($dialog);$dialog.on('dialogButtonsChange',()=>{const buttons=Drupal.behaviors.dialog.prepareDialogButtons($dialog);$dialog.dialog('option','buttons',buttons);});response.dialogOptions=response.dialogOptions||{};const dialog=Drupal.dialog($dialog.get(0),response.dialogOptions);if(response.dialogOptions.modal)dialog.showModal();else dialog.show();$dialog.parent().find('.ui-dialog-buttonset').addClass('form-actions');};Drupal.AjaxCommands.prototype.closeDialog=function(ajax,response,status){const $dialog=$(response.selector);if($dialog.length){Drupal.dialog($dialog.get(0)).close();if(!response.persist)$dialog.remove();}$dialog.off('dialogButtonsChange');};Drupal.AjaxCommands.prototype.setDialogOption=function(ajax,response,status){const $dialog=$(response.selector);if($dialog.length)$dialog.dialog('option',response.optionName,response.optionValue);};window.addEventListener('dialog:aftercreate',(event)=>{const $element=$(event.target);const dialog=event.dialog;$element.on('click.dialog','.dialog-cancel',(e)=>{dialog.close('cancel');e.preventDefault();e.stopPropagation();});});window.addEventListener('dialog:beforeclose',(e)=>{const $element=$(e.target);$element.off('.dialog');});Drupal.AjaxCommands.prototype.openModalDialogWithUrl=function(ajax,response){const dialogOptions=response.dialogOptions||{};const elementSettings={progress:{type:'throbber'},dialogType:'modal',dialog:dialogOptions,url:response.url,httpMethod:'GET'};Drupal.ajax(elementSettings).execute();};})(jQuery,Drupal,window.tabbable);;
(function(){'use strict';(function($,Drupal){Drupal.AjaxCommands.prototype.openMercuryDialog=function(ajax,response,status){if(!response.selector)return false;$(response.selector).remove();const $dialog=$(`<mercury-dialog id="${response.selector.replace(/^#/,'')}" class="ui-front"></mercury-dialog>`).appendTo('body');if(!ajax.wrapper)ajax.wrapper=$dialog.attr('id');response.command='insert';response.method='html';ajax.commands.insert(ajax,response,status);if(typeof response.dialogOptions.drupalAutoButtons==='undefined')response.dialogOptions.drupalAutoButtons=true;else if(response.dialogOptions.drupalAutoButtons==='false')response.dialogOptions.drupalAutoButtons=false;else response.dialogOptions.drupalAutoButtons=!!response.dialogOptions.drupalAutoButtons;if(!response.dialogOptions.buttons&&response.dialogOptions.drupalAutoButtons)response.dialogOptions.buttons=Drupal.behaviors.mercuryDialog.prepareDialogButtons($dialog);$dialog.on('dialogButtonsChange',()=>{if($dialog[0].tagName!=='MERCURY-DIALOG')return;const buttons=Drupal.behaviors.mercuryDialog.prepareDialogButtons($dialog);Drupal.mercuryDialog($dialog[0]).applyOptions({buttons});});response.dialogOptions=response.dialogOptions||{};const dialogElement=$dialog.get(0);const dialog=Drupal.mercuryDialog(dialogElement,response.dialogOptions);if(response.dialogOptions.width==='auto')response.dialogOptions.width='fit-content';const open=dialogElement.getAttribute('open');if(response.dialogOptions.modal&&!open)dialog.showModal();else dialog.show();};Drupal.AjaxCommands.prototype.closeMercuryDialog=function(ajax,response,status){if(!response.selector)return false;let dialog=document.querySelector(response.selector);if(!dialog)dialog=window.parent.document.querySelector(response.selector);if(dialog){Drupal.mercuryDialog(dialog).close();if(!response.persist)dialog.remove();}};Drupal.AjaxCommands.prototype.coreCloseDialog=Drupal.AjaxCommands.prototype.closeDialog;Drupal.AjaxCommands.prototype.closeDialog=function(ajax,response,status){const dialog=document.querySelector(response.selector);if(dialog.tagName==='MERCURY-DIALOG')return Drupal.AjaxCommands.prototype.closeMercuryDialog(ajax,response,status);return Drupal.AjaxCommands.prototype.coreCloseDialog(ajax,response,status);};})(jQuery,Drupal);})();;
(($,Drupal,debounce,dragula,once)=>{const idAttr='data-lpb-id';function attachUiElements($container,settings){const id=$container.attr('data-lpb-ui-id');const lpbBuilderSettings=settings.lpBuilder||{};const uiElements=lpbBuilderSettings.uiElements||{};const containerUiElements=uiElements[id]||[];Object.values(containerUiElements).forEach((uiElement)=>{const {element,method}=uiElement;$container[method]($(element).addClass('js-lpb-ui'));});}function repositionDialog(intervalId){const $dialogs=$('.lpb-dialog');if($dialogs.length===0){clearInterval(intervalId);return;}$dialogs.each((i,dialog)=>{const bounding=dialog.getBoundingClientRect();const viewPortHeight=window.innerHeight||document.documentElement.clientHeight;if(bounding.bottom>viewPortHeight){const $dialog=$('.ui-dialog-content',dialog);const height=viewPortHeight-200;$dialog.dialog('option','height',height);$dialog.css('overscroll-behavior','contain');if($dialog.data('lpOriginalHeight')!==height){$dialog.data('lpOriginalHeight',height);const bounding=dialog.getBoundingClientRect();const viewPortHeight=window.innerHeight||document.documentElement.clientHeight;if(bounding.bottom>viewPortHeight){const pos=$dialog.dialog('option','position');$dialog.dialog('option','position',pos);}}}});}function doReorderComponents($element){const id=$element.attr(idAttr);const order=$('.js-lpb-component',$element).get().map((item)=>{const $item=$(item);return {uuid:$item.attr('data-uuid'),parentUuid:$item.parents('.js-lpb-component').first().attr('data-uuid')||null,region:$item.parents('.js-lpb-region').first().attr('data-region')||null};});Drupal.ajax({url:`${drupalSettings.path.baseUrl}${drupalSettings.path.pathPrefix}layout-paragraphs-builder/${id}/reorder`,submit:{components:JSON.stringify(order)},error:()=>{}}).execute();}const reorderComponents=debounce(doReorderComponents);function acceptsErrors(settings,el,target,source,sibling){return Drupal._lpbMoveErrors.accepts.map((validator)=>validator.apply(null,[settings,el,target,source,sibling])).filter((errors)=>errors!==false&&errors!==undefined);}function movesErrors(settings,el,source,handle){return Drupal._lpbMoveErrors.moves.map((validator)=>validator.apply(null,[settings,el,source,handle])).filter((errors)=>errors!==false&&errors!==undefined);}function updateMoveButtons($element){const lpbBuilderElements=Array.from($element[0].querySelectorAll('.js-lpb-component-list, .js-lpb-region'));const lpbBuilderComponent=lpbBuilderElements.filter((el)=>el.querySelector('.js-lpb-component'));$element[0].querySelectorAll('.lpb-up, .lpb-down').forEach((el)=>{el.setAttribute('tabindex','0');});lpbBuilderComponent.forEach((el)=>{const components=Array.from(el.children).filter((n)=>n.classList.contains('js-lpb-component'));components[0].querySelector('.lpb-up')?.setAttribute('tabindex','-1');components[components.length-1].querySelector('.lpb-down')?.setAttribute('tabindex','-1');});}function hideEmptyRegionButtons($element){$element.find('.js-lpb-region').each((i,e)=>{const $e=$(e);if($e.find('.js-lpb-component').length===0)$e.find('.lpb-btn--add.center').css('display','block');else $e.find('.lpb-btn--add.center').css('display','none');});}function updateUi($element){reorderComponents($element);updateMoveButtons($element);hideEmptyRegionButtons($element);}function move($moveItem,direction){const $sibling=direction===1?$moveItem.nextAll('.js-lpb-component').first():$moveItem.prevAll('.js-lpb-component').first();const method=direction===1?'after':'before';const {scrollY}=window;const destScroll=scrollY+$sibling.outerHeight()*direction;if($sibling.length===0)return false;const animateProp=$sibling[0].getBoundingClientRect().top==$moveItem[0].getBoundingClientRect().top?'translateX':'translateY';const dimmensionProp=animateProp==='translateX'?'offsetWidth':'offsetHeight';const siblingDest=$moveItem[0][dimmensionProp]*direction*-1;const itemDest=$sibling[0][dimmensionProp]*direction;const distance=Math.abs(Math.max(siblingDest,itemDest));const duration=distance*.25;const siblingKeyframes=[{transform:`${animateProp}(0)`},{transform:`${animateProp}(${siblingDest}px)`}];const itemKeyframes=[{transform:`${animateProp}(0)`},{transform:`${animateProp}(${itemDest}px)`}];const timing={duration,iterations:1};const anim1=$moveItem[0].animate(itemKeyframes,timing);anim1.onfinish=()=>{$moveItem.css({transform:'none'});$sibling.css({transform:'none'});$sibling[method]($moveItem);$moveItem.closest(`[${idAttr}]`).trigger('lpb-component:move',[$moveItem.attr('data-uuid')]);};$sibling[0].animate(siblingKeyframes,timing);if(animateProp==='translateY')window.scrollTo({top:destScroll,behavior:'smooth'});}function nav($item,dir,settings){const $element=$item.closest(`[${idAttr}]`);$item.addClass('lpb-active-item');if(dir===-1)$('.js-lpb-region .lpb-btn--add.center, .lpb-layout:not(.lpb-active-item)',$element).before('<div class="lpb-shim"></div>');else{if(dir===1){$('.js-lpb-region',$element).prepend('<div class="lpb-shim"></div>');$('.lpb-layout:not(.lpb-active-item)',$element).after('<div class="lpb-shim"></div>');}}const targets=$('.js-lpb-component, .lpb-shim',$element).toArray().filter((i)=>!$.contains($item[0],i)).filter((i)=>i.className.indexOf('lpb-layout')===-1||i===$item[0]);const currentElement=$item[0];let pos=targets.indexOf(currentElement);while(targets[pos+dir]!==undefined&&acceptsErrors(settings,$item[0],targets[pos+dir].parentNode,null,$item.next().length?$item.next()[0]:null).length>0)pos+=dir;if(targets[pos+dir]!==undefined)$(targets[pos+dir])[dir===1?'after':'before']($item);$('.lpb-shim',$element).remove();$item.removeClass('lpb-active-item').focus();$item.closest(`[${idAttr}]`).trigger('lpb-component:move',[$item.attr('data-uuid')]);}function startNav($item){const $msg=$(`<div id="lpb-navigating-msg" class="lpb-tooltiptext lpb-tooltiptext--visible js-lpb-tooltiptext">${Drupal.t('Use arrow keys to move. Press Return or Tab when finished.')}</div>`);$item.closest('.lp-builder').addClass('is-navigating').find('.is-navigating').removeClass('is-navigating');$item.attr('aria-describedby','lpb-navigating-msg').addClass('is-navigating').prepend($msg);$item.before('<div class="lpb-navigating-placeholder"></div>');}function stopNav($item){$item.removeClass('is-navigating').attr('aria-describedby','').find('.js-lpb-tooltiptext').remove();$item.closest(`[${idAttr}]`).removeClass('is-navigating').find('.lpb-navigating-placeholder').remove();}function cancelNav($item){const $builder=$item.closest(`[${idAttr}]`);$builder.find('.lpb-navigating-placeholder').replaceWith($item);updateUi($builder);stopNav($item);}function preventLostChanges($element){const events=['lpb-component:insert.lpb','lpb-component:update.lpb','lpb-component:move.lpb','lpb-component:drop.lpb'].join(' ');$element.on(events,(e)=>{$(e.currentTarget).addClass('is_changed');});window.addEventListener('beforeunload',(e)=>{if($(`.is_changed[${idAttr}]`).length){e.preventDefault();e.returnValue='';}});$('.form-actions').find('input[type="submit"], a').click(()=>{$element.removeClass('is_changed');});}function attachEventListeners($element,settings){preventLostChanges($element);$element.on('click.lp-builder','.lpb-up',(e)=>{move($(e.target).closest('.js-lpb-component'),-1);return false;});$element.on('click.lp-builder','.lpb-down',(e)=>{move($(e.target).closest('.js-lpb-component'),1);return false;});$element.on('click.lp-builder','.js-lpb-component',(e)=>{$(e.currentTarget).focus();});$element.on('click.lp-builder','.lpb-drag',(e)=>{const $btn=$(e.currentTarget);startNav($btn.closest('.js-lpb-component'));});$(document).off('keydown');$(document).on('keydown',(e)=>{const $item=$('.js-lpb-component.is-navigating');if($item.length)switch(e.code){case 'ArrowUp':case 'ArrowLeft':nav($item,-1,settings);break;case 'ArrowDown':case 'ArrowRight':nav($item,1,settings);break;case 'Enter':case 'Tab':stopNav($item);break;case 'Escape':cancelNav($item);break;default:break;}});}function initDragAndDrop($element,settings){const containers=once('is-dragula-enabled','.js-lpb-component-list, .js-lpb-region',$element[0]);const drake=dragula(containers,{accepts:(el,target,source,sibling)=>acceptsErrors(settings,el,target,source,sibling).length===0,moves:(el,source,handle)=>movesErrors(settings,el,source,handle).length===0});drake.on('drop',(el)=>{const $el=$(el);if($el.prev().is('a'))$el.insertBefore($el.prev());$element.trigger('lpb-component:drop',[$el.attr('data-uuid')]);});drake.on('drag',(el)=>{$element.addClass('is-dragging');if(el.className.indexOf('lpb-layout')>-1)$element.addClass('is-dragging-layout');else $element.addClass('is-dragging-item');$element.trigger('lpb-component:drag',[$(el).attr('data-uuid')]);});drake.on('dragend',()=>{$element.removeClass('is-dragging').removeClass('is-dragging-layout').removeClass('is-dragging-item');});drake.on('over',(el,container)=>{$(container).addClass('drag-target');});drake.on('out',(el,container)=>{$(container).removeClass('drag-target');});return drake;}Drupal._lpbMoveErrors={'accepts':[],'moves':[]};Drupal.registerLpbMoveError=(f,c='accepts')=>{Drupal._lpbMoveErrors[c].push(f);};Drupal.registerLpbMoveError((settings,el,target)=>{if(el.classList.contains('lpb-layout')&&$(target).parents('.lpb-layout').length>settings.nesting_depth)return Drupal.t('Exceeds nesting depth of @depth.',{'@depth':settings.nesting_depth});});Drupal.registerLpbMoveError((settings,el,target)=>{if(settings.require_layouts)if(el.classList.contains('js-lpb-component')&&!el.classList.contains('lpb-layout')&&!target.classList.contains('js-lpb-region'))return Drupal.t('Components must be added inside sections.');});Drupal.registerLpbMoveError((settings,el,source,handle)=>{if(!handle.closest('.lpb-drag')&&handle.closest('.lpb-controls'))return 'Disable dragging for controls elements other than drag handle.';},'moves');Drupal.AjaxCommands.prototype.LayoutParagraphsEventCommand=(ajax,response)=>{const {layoutId,componentUuid,eventName}=response;const $element=$(`[data-lpb-id="${layoutId}"]`);$element.trigger(`lpb-${eventName}`,[componentUuid]);};function updateDialogButtons(context){const $lpDialog=$(context).closest('.ui-dialog-content');if(!$lpDialog)return;const buttons=[];const $buttons=$lpDialog.find('.layout-paragraphs-component-form > .form-actions input[type=submit], .layout-paragraphs-component-form > .form-actions a.button');if($buttons.length===0)return;$buttons.each((_i,el)=>{const $originalButton=$(el).css({display:'none'});buttons.push({text:$originalButton.html()||$originalButton.attr('value'),class:$originalButton.attr('class'),click(e){if($originalButton.is('a'))$originalButton[0].click();else{$originalButton.trigger('mousedown').trigger('mouseup').trigger('click');e.preventDefault();}}});});$lpDialog.dialog('option','buttons',buttons);}Drupal.behaviors.layoutParagraphsBuilder={attach:function attach(context,settings){const jsUiElements=once('lpb-ui-elements','[data-has-js-ui-element]');jsUiElements.forEach((el)=>{attachUiElements($(el),settings);});once('lpb-events','[data-lpb-id]').forEach((el)=>{$(el).on('lpb-builder:init.lpb lpb-component:insert.lpb lpb-component:update.lpb lpb-component:move.lpb lpb-component:drop.lpb lpb-component:delete.lpb',(e)=>{const $element=$(e.currentTarget);updateUi($element);});});once('lpb-enabled','[data-lpb-id].has-components').forEach((el)=>{const $element=$(el);const id=$element.attr(idAttr);const lpbSettings=settings.lpBuilder[id];$element.data('drake',initDragAndDrop($element,lpbSettings));attachEventListeners($element,lpbSettings);$element.trigger('lpb-builder:init');});once('is-dragula-enabled','.js-lpb-region').forEach((c)=>{const builderElement=c.closest('[data-lpb-id]');const drake=$(builderElement).data('drake');drake.containers.push(c);});if(jsUiElements.length)Drupal.attachBehaviors(context,settings);updateDialogButtons(context);}};let lpDialogInterval;const handleAfterDialogCreate=(event,dialog,$dialog)=>{const $element=$dialog||jQuery(event.target);if($element.attr('id').startsWith('lpb-dialog-')){updateDialogButtons($element);clearInterval(lpDialogInterval);lpDialogInterval=setInterval(repositionDialog.bind(null,lpDialogInterval),500);}};if(typeof DrupalDialogEvent==='undefined')$(window).on('dialog:aftercreate',handleAfterDialogCreate);else window.addEventListener('dialog:aftercreate',handleAfterDialogCreate);})(jQuery,Drupal,Drupal.debounce,dragula,once);;
(function($,Drupal){Drupal.behaviors.menuUiDetailsSummaries={attach(context){$(context).find('.menu-link-form').drupalSetSummary((context)=>{const $context=$(context);if($context.find('.js-form-item-menu-enabled input:checked').length)return Drupal.checkPlain($context.find('.js-form-item-menu-title input')[0].value);return Drupal.t('Not in menu');});}};Drupal.behaviors.menuUiLinkAutomaticTitle={attach(context){const $context=$(context);$context.find('.menu-link-form').each(function(){const $this=$(this);const $checkbox=$this.find('.js-form-item-menu-enabled input');const $linkTitle=$context.find('.js-form-item-menu-title input');const $title=$this.closest('form').find('.js-form-item-title-0-value input');if(!($checkbox.length&&$linkTitle.length&&$title.length))return;if($checkbox[0].checked&&$linkTitle[0].value.length)$linkTitle.data('menuLinkAutomaticTitleOverridden',true);$linkTitle.on('keyup',()=>{$linkTitle.data('menuLinkAutomaticTitleOverridden',true);});$checkbox.on('change',()=>{if($checkbox[0].checked){if(!$linkTitle.data('menuLinkAutomaticTitleOverridden'))$linkTitle[0].value=$title[0].value;}else{$linkTitle[0].value='';$linkTitle.removeData('menuLinkAutomaticTitleOverridden');}$checkbox.closest('.vertical-tabs-pane').trigger('summaryUpdated');$checkbox.trigger('formUpdated');});$title.on('keyup',()=>{if(!$linkTitle.data('menuLinkAutomaticTitleOverridden')&&$checkbox[0].checked){$linkTitle[0].value=$title[0].value;$linkTitle.trigger('formUpdated');}});});}};})(jQuery,Drupal);;
(function($,Drupal){Drupal.behaviors.entityContentDetailsSummaries={attach(context){const $context=$(context);$context.find('.entity-content-form-revision-information').drupalSetSummary((context)=>{const $revisionContext=$(context);const revisionCheckbox=$revisionContext.find('.js-form-item-revision input');if((revisionCheckbox.length&&revisionCheckbox[0].checked)||(!revisionCheckbox.length&&$revisionContext.find('.js-form-item-revision-log textarea').length))return Drupal.t('New revision');return Drupal.t('No revision');});$context.find('details.entity-translation-options').drupalSetSummary((context)=>{const $translationContext=$(context);let translate;let $checkbox=$translationContext.find('.js-form-item-translation-translate input');if($checkbox.length)translate=$checkbox[0].checked?Drupal.t('Needs to be updated'):Drupal.t('Does not need to be updated');else{$checkbox=$translationContext.find('.js-form-item-translation-retranslate input');translate=$checkbox[0]?.checked?Drupal.t('Flag other translations as outdated'):Drupal.t('Do not flag other translations as outdated');}return translate;});}};})(jQuery,Drupal);;
(function($,Drupal,drupalSettings){Drupal.behaviors.nodeDetailsSummaries={attach(context){const $context=$(context);$context.find('.node-form-author').drupalSetSummary((context)=>{const nameElement=context.querySelector('.field--name-uid input');const name=nameElement&&nameElement.value;const dateElement=context.querySelector('.field--name-created input');const date=dateElement&&dateElement.value;if(name&&date)return Drupal.t('By @name on @date',{'@name':name,'@date':date});if(name)return Drupal.t('By @name',{'@name':name});if(date)return Drupal.t('Authored on @date',{'@date':date});});$context.find('.node-form-options').drupalSetSummary((context)=>{const $optionsContext=$(context);const values=[];if($optionsContext.find('input:checked').length){$optionsContext.find('input:checked').next('label').each(function(){values.push(Drupal.checkPlain(this.textContent.trim()));});return values.join(', ');}return Drupal.t('Not promoted');});}};})(jQuery,Drupal,drupalSettings);;
(function($,Drupal){Drupal.behaviors.pathDetailsSummaries={attach(context){$(context).find('.path-form').drupalSetSummary((context)=>{const pathElement=document.querySelector('.js-form-item-path-0-alias input');const path=pathElement&&pathElement.value;return path?Drupal.t('Alias: @alias',{'@alias':path}):Drupal.t('No alias');});}};})(jQuery,Drupal);;
(function($,Drupal,drupalSettings){const pathInfo=drupalSettings.path;const escapeAdminPath=sessionStorage.getItem('escapeAdminPath');const windowLocation=window.location;if(!pathInfo.currentPathIsAdmin&&!/destination=/.test(windowLocation.search))sessionStorage.setItem('escapeAdminPath',windowLocation);Drupal.behaviors.escapeAdmin={attach(){const toolbarEscape=once('escapeAdmin','[data-toolbar-escape-admin]');if(toolbarEscape.length&&pathInfo.currentPathIsAdmin&&escapeAdminPath!==null)$(toolbarEscape).attr('href',escapeAdminPath);}};})(jQuery,Drupal,drupalSettings);;
