
/*! dustjs-linkedin - v2.7.2
* http://dustjs.com/
* Copyright (c) 2015 Aleksander Williams; Released under the MIT License */
!function(a,b){"function"==typeof define&&define.amd&&define.amd.dust===!0?define("dust.core",[],b):"object"==typeof exports?module.exports=b():a.dust=b()}(this,function(){function getTemplate(a,b){return a?"function"==typeof a&&a.template?a.template:dust.isTemplateFn(a)?a:b!==!1?dust.cache[a]:void 0:void 0}function load(a,b,c){if(!a)return b.setError(new Error("No template or template name provided to render"));var d=getTemplate(a,dust.config.cache);return d?d(b,Context.wrap(c,d.templateName)):dust.onLoad?b.map(function(b){function d(a,d){var f;if(a)return b.setError(a);if(f=getTemplate(d,!1)||getTemplate(e,dust.config.cache),!f){if(!dust.compile)return b.setError(new Error("Dust compiler not available"));f=dust.loadSource(dust.compile(d,e))}f(b,Context.wrap(c,f.templateName)).end()}var e=a;3===dust.onLoad.length?dust.onLoad(e,c.options,d):dust.onLoad(e,d)}):b.setError(new Error("Template Not Found: "+a))}function Context(a,b,c,d,e){void 0===a||a instanceof Stack||(a=new Stack(a)),this.stack=a,this.global=b,this.options=c,this.blocks=d,this.templateName=e}function getWithResolvedData(a,b,c){return function(d){return a.push(d)._get(b,c)}}function Stack(a,b,c,d){this.tail=b,this.isObject=a&&"object"==typeof a,this.head=a,this.index=c,this.of=d}function Stub(a){this.head=new Chunk(this),this.callback=a,this.out=""}function Stream(){this.head=new Chunk(this)}function Chunk(a,b,c){this.root=a,this.next=b,this.data=[],this.flushable=!1,this.taps=c}function Tap(a,b){this.head=a,this.tail=b}var dust={version:"2.7.2"},NONE="NONE",ERROR="ERROR",WARN="WARN",INFO="INFO",DEBUG="DEBUG",EMPTY_FUNC=function(){};dust.config={whitespace:!1,amd:!1,cjs:!1,cache:!0},dust._aliases={write:"w",end:"e",map:"m",render:"r",reference:"f",section:"s",exists:"x",notexists:"nx",block:"b",partial:"p",helper:"h"},function(){var a,b,c={DEBUG:0,INFO:1,WARN:2,ERROR:3,NONE:4};"undefined"!=typeof console&&console.log?(a=console.log,b="function"==typeof a?function(){a.apply(console,arguments)}:function(){a(Array.prototype.slice.apply(arguments).join(" "))}):b=EMPTY_FUNC,dust.log=function(a,d){d=d||INFO,c[d]>=c[dust.debugLevel]&&b("[DUST:"+d+"]",a)},dust.debugLevel=NONE,"undefined"!=typeof process&&process.env&&/\bdust\b/.test(process.env.DEBUG)&&(dust.debugLevel=DEBUG)}(),dust.helpers={},dust.cache={},dust.register=function(a,b){a&&(b.templateName=a,dust.config.cache!==!1&&(dust.cache[a]=b))},dust.render=function(a,b,c){var d=new Stub(c).head;try{load(a,d,b).end()}catch(e){d.setError(e)}},dust.stream=function(a,b){var c=new Stream,d=c.head;return dust.nextTick(function(){try{load(a,d,b).end()}catch(c){d.setError(c)}}),c},dust.loadSource=function(source){return eval(source)},dust.isArray=Array.isArray?Array.isArray:function(a){return"[object Array]"===Object.prototype.toString.call(a)},dust.nextTick=function(){return function(a){setTimeout(a,0)}}(),dust.isEmpty=function(a){return 0===a?!1:dust.isArray(a)&&!a.length?!0:!a},dust.isEmptyObject=function(a){var b;if(null===a)return!1;if(void 0===a)return!1;if(a.length>0)return!1;for(b in a)if(Object.prototype.hasOwnProperty.call(a,b))return!1;return!0},dust.isTemplateFn=function(a){return"function"==typeof a&&a.__dustBody},dust.isThenable=function(a){return a&&"object"==typeof a&&"function"==typeof a.then},dust.isStreamable=function(a){return a&&"function"==typeof a.on&&"function"==typeof a.pipe},dust.filter=function(a,b,c,d){var e,f,g,h;if(c)for(e=0,f=c.length;f>e;e++)g=c[e],g.length&&(h=dust.filters[g],"s"===g?b=null:"function"==typeof h?a=h(a,d):dust.log("Invalid filter `"+g+"`",WARN));return b&&(a=dust.filters[b](a,d)),a},dust.filters={h:function(a){return dust.escapeHtml(a)},j:function(a){return dust.escapeJs(a)},u:encodeURI,uc:encodeURIComponent,js:function(a){return dust.escapeJSON(a)},jp:function(a){return JSON?JSON.parse(a):(dust.log("JSON is undefined; could not parse `"+a+"`",WARN),a)}},dust.makeBase=dust.context=function(a,b){return new Context(void 0,a,b)},Context.wrap=function(a,b){return a instanceof Context?a:new Context(a,{},{},null,b)},Context.prototype.get=function(a,b){return"string"==typeof a&&("."===a[0]&&(b=!0,a=a.substr(1)),a=a.split(".")),this._get(b,a)},Context.prototype._get=function(a,b){var c,d,e,f,g,h=this.stack||{},i=1;if(d=b[0],e=b.length,a&&0===e)f=h,h=h.head;else{if(a)h&&(h=h.head?h.head[d]:void 0);else{for(;h&&(!h.isObject||(f=h.head,c=h.head[d],void 0===c));)h=h.tail;h=void 0!==c?c:this.global&&this.global[d]}for(;h&&e>i;){if(dust.isThenable(h))return h.then(getWithResolvedData(this,a,b.slice(i)));f=h,h=h[b[i]],i++}}return"function"==typeof h?(g=function(){try{return h.apply(f,arguments)}catch(a){throw dust.log(a,ERROR),a}},g.__dustBody=!!h.__dustBody,g):(void 0===h&&dust.log("Cannot find reference `{"+b.join(".")+"}` in template `"+this.getTemplateName()+"`",INFO),h)},Context.prototype.getPath=function(a,b){return this._get(a,b)},Context.prototype.push=function(a,b,c){return void 0===a?(dust.log("Not pushing an undefined variable onto the context",INFO),this):this.rebase(new Stack(a,this.stack,b,c))},Context.prototype.pop=function(){var a=this.current();return this.stack=this.stack&&this.stack.tail,a},Context.prototype.rebase=function(a){return new Context(a,this.global,this.options,this.blocks,this.getTemplateName())},Context.prototype.clone=function(){var a=this.rebase();return a.stack=this.stack,a},Context.prototype.current=function(){return this.stack&&this.stack.head},Context.prototype.getBlock=function(a){var b,c,d;if("function"==typeof a&&(a=a(new Chunk,this).data.join("")),b=this.blocks,!b)return dust.log("No blocks for context `"+a+"` in template `"+this.getTemplateName()+"`",DEBUG),!1;for(c=b.length;c--;)if(d=b[c][a])return d;return dust.log("Malformed template `"+this.getTemplateName()+"` was missing one or more blocks."),!1},Context.prototype.shiftBlocks=function(a){var b,c=this.blocks;return a?(b=c?c.concat([a]):[a],new Context(this.stack,this.global,this.options,b,this.getTemplateName())):this},Context.prototype.resolve=function(a){var b;return"function"!=typeof a?a:(b=(new Chunk).render(a,this),b instanceof Chunk?b.data.join(""):b)},Context.prototype.getTemplateName=function(){return this.templateName},Stub.prototype.flush=function(){for(var a=this.head;a;){if(!a.flushable)return a.error?(this.callback(a.error),dust.log("Rendering failed with error `"+a.error+"`",ERROR),void(this.flush=EMPTY_FUNC)):void 0;this.out+=a.data.join(""),a=a.next,this.head=a}this.callback(null,this.out)},Stream.prototype.flush=function(){for(var a=this.head;a;){if(!a.flushable)return a.error?(this.emit("error",a.error),this.emit("end"),dust.log("Streaming failed with error `"+a.error+"`",ERROR),void(this.flush=EMPTY_FUNC)):void 0;this.emit("data",a.data.join("")),a=a.next,this.head=a}this.emit("end")},Stream.prototype.emit=function(a,b){var c,d,e=this.events||{},f=e[a]||[];if(!f.length)return dust.log("Stream broadcasting, but no listeners for `"+a+"`",DEBUG),!1;for(f=f.slice(0),c=0,d=f.length;d>c;c++)f[c](b);return!0},Stream.prototype.on=function(a,b){var c=this.events=this.events||{},d=c[a]=c[a]||[];return"function"!=typeof b?dust.log("No callback function provided for `"+a+"` event listener",WARN):d.push(b),this},Stream.prototype.pipe=function(a){if("function"!=typeof a.write||"function"!=typeof a.end)return dust.log("Incompatible stream passed to `pipe`",WARN),this;var b=!1;return"function"==typeof a.emit&&a.emit("pipe",this),"function"==typeof a.on&&a.on("error",function(){b=!0}),this.on("data",function(c){if(!b)try{a.write(c,"utf8")}catch(d){dust.log(d,ERROR)}}).on("end",function(){if(!b)try{a.end(),b=!0}catch(c){dust.log(c,ERROR)}})},Chunk.prototype.write=function(a){var b=this.taps;return b&&(a=b.go(a)),this.data.push(a),this},Chunk.prototype.end=function(a){return a&&this.write(a),this.flushable=!0,this.root.flush(),this},Chunk.prototype.map=function(a){var b=new Chunk(this.root,this.next,this.taps),c=new Chunk(this.root,b,this.taps);this.next=c,this.flushable=!0;try{a(c)}catch(d){dust.log(d,ERROR),c.setError(d)}return b},Chunk.prototype.tap=function(a){var b=this.taps;return this.taps=b?b.push(a):new Tap(a),this},Chunk.prototype.untap=function(){return this.taps=this.taps.tail,this},Chunk.prototype.render=function(a,b){return a(this,b)},Chunk.prototype.reference=function(a,b,c,d){return"function"==typeof a?(a=a.apply(b.current(),[this,b,null,{auto:c,filters:d}]),a instanceof Chunk?a:this.reference(a,b,c,d)):dust.isThenable(a)?this.await(a,b,null,c,d):dust.isStreamable(a)?this.stream(a,b,null,c,d):dust.isEmpty(a)?this:this.write(dust.filter(a,c,d,b))},Chunk.prototype.section=function(a,b,c,d){var e,f,g,h=c.block,i=c["else"],j=this;if("function"==typeof a&&!dust.isTemplateFn(a)){try{a=a.apply(b.current(),[this,b,c,d])}catch(k){return dust.log(k,ERROR),this.setError(k)}if(a instanceof Chunk)return a}if(dust.isEmptyObject(c))return j;if(dust.isEmptyObject(d)||(b=b.push(d)),dust.isArray(a)){if(h){if(f=a.length,f>0){for(g=b.stack&&b.stack.head||{},g.$len=f,e=0;f>e;e++)g.$idx=e,j=h(j,b.push(a[e],e,f));return g.$idx=void 0,g.$len=void 0,j}if(i)return i(this,b)}}else{if(dust.isThenable(a))return this.await(a,b,c);if(dust.isStreamable(a))return this.stream(a,b,c);if(a===!0){if(h)return h(this,b)}else if(a||0===a){if(h)return h(this,b.push(a))}else if(i)return i(this,b)}return dust.log("Section without corresponding key in template `"+b.getTemplateName()+"`",DEBUG),this},Chunk.prototype.exists=function(a,b,c){var d=c.block,e=c["else"];if(dust.isEmpty(a)){if(e)return e(this,b)}else{if(d)return d(this,b);dust.log("No block for exists check in template `"+b.getTemplateName()+"`",DEBUG)}return this},Chunk.prototype.notexists=function(a,b,c){var d=c.block,e=c["else"];if(dust.isEmpty(a)){if(d)return d(this,b);dust.log("No block for not-exists check in template `"+b.getTemplateName()+"`",DEBUG)}else if(e)return e(this,b);return this},Chunk.prototype.block=function(a,b,c){var d=a||c.block;return d?d(this,b):this},Chunk.prototype.partial=function(a,b,c,d){var e;return void 0===d&&(d=c,c=b),dust.isEmptyObject(d)||(c=c.clone(),e=c.pop(),c=c.push(d).push(e)),dust.isTemplateFn(a)?this.capture(a,b,function(a,b){c.templateName=a,load(a,b,c).end()}):(c.templateName=a,load(a,this,c))},Chunk.prototype.helper=function(a,b,c,d,e){var f,g=this,h=d.filters;if(void 0===e&&(e="h"),!dust.helpers[a])return dust.log("Helper `"+a+"` does not exist",WARN),g;try{return f=dust.helpers[a](g,b,c,d),f instanceof Chunk?f:("string"==typeof h&&(h=h.split("|")),dust.isEmptyObject(c)?g.reference(f,b,e,h):g.section(f,b,c,d))}catch(i){return dust.log("Error in helper `"+a+"`: "+i.message,ERROR),g.setError(i)}},Chunk.prototype.await=function(a,b,c,d,e){return this.map(function(f){a.then(function(a){f=c?f.section(a,b,c):f.reference(a,b,d,e),f.end()},function(a){var d=c&&c.error;d?f.render(d,b.push(a)).end():(dust.log("Unhandled promise rejection in `"+b.getTemplateName()+"`",INFO),f.end())})})},Chunk.prototype.stream=function(a,b,c,d,e){var f=c&&c.block,g=c&&c.error;return this.map(function(h){var i=!1;a.on("data",function(a){i||(f?h=h.map(function(c){c.render(f,b.push(a)).end()}):c||(h=h.reference(a,b,d,e)))}).on("error",function(a){i||(g?h.render(g,b.push(a)):dust.log("Unhandled stream error in `"+b.getTemplateName()+"`",INFO),i||(i=!0,h.end()))}).on("end",function(){i||(i=!0,h.end())})})},Chunk.prototype.capture=function(a,b,c){return this.map(function(d){var e=new Stub(function(a,b){a?d.setError(a):c(b,d)});a(e.head,b).end()})},Chunk.prototype.setError=function(a){return this.error=a,this.root.flush(),this};for(var f in Chunk.prototype)dust._aliases[f]&&(Chunk.prototype[dust._aliases[f]]=Chunk.prototype[f]);Tap.prototype.push=function(a){return new Tap(a,this)},Tap.prototype.go=function(a){for(var b=this;b;)a=b.head(a),b=b.tail;return a};var HCHARS=/[&<>"']/,AMP=/&/g,LT=/</g,GT=/>/g,QUOT=/\"/g,SQUOT=/\'/g;dust.escapeHtml=function(a){return"string"==typeof a||a&&"function"==typeof a.toString?("string"!=typeof a&&(a=a.toString()),HCHARS.test(a)?a.replace(AMP,"&amp;").replace(LT,"&lt;").replace(GT,"&gt;").replace(QUOT,"&quot;").replace(SQUOT,"&#39;"):a):a};var BS=/\\/g,FS=/\//g,CR=/\r/g,LS=/\u2028/g,PS=/\u2029/g,NL=/\n/g,LF=/\f/g,SQ=/'/g,DQ=/"/g,TB=/\t/g;return dust.escapeJs=function(a){return"string"==typeof a?a.replace(BS,"\\\\").replace(FS,"\\/").replace(DQ,'\\"').replace(SQ,"\\'").replace(CR,"\\r").replace(LS,"\\u2028").replace(PS,"\\u2029").replace(NL,"\\n").replace(LF,"\\f").replace(TB,"\\t"):a},dust.escapeJSON=function(a){return JSON?JSON.stringify(a).replace(LS,"\\u2028").replace(PS,"\\u2029").replace(LT,"\\u003c"):(dust.log("JSON is undefined; could not escape `"+a+"`",WARN),a)},dust}),"function"==typeof define&&define.amd&&define.amd.dust===!0&&define('dust',["require","dust.core"],function(require,dust){return dust.onLoad=function(a,b){require([a],function(){b()})},dust});
/*! dustjs-helpers - v1.7.2
* http://dustjs.com/
* Copyright (c) 2015 Aleksander Williams; Released under the MIT License */
!function(a,b){"function"==typeof define&&define.amd&&define.amd.dust===!0?define('dust-helpers',["dust.core"],b):"object"==typeof exports?module.exports=b(require("dustjs-linkedin")):b(a.dust)}(this,function(dust){function a(a,b,c){c=c||"INFO",a=a?"{@"+a+"}: ":"",dust.log(a+b,c)}function b(b){k[b]||(a(b,"Deprecation warning: "+b+" is deprecated and will be removed in a future version of dustjs-helpers","WARN"),a(null,"For help and a deprecation timeline, see https://github.com/linkedin/dustjs-helpers/wiki/Deprecated-Features#"+b.replace(/\W+/g,""),"WARN"),k[b]=!0)}function c(a){return a.stack.tail&&a.stack.tail.head&&"undefined"!=typeof a.stack.tail.head.__select__}function d(a){return c(a)&&a.get("__select__")}function e(a,b){var c,d=a.stack.head,e=a.rebase();a.stack&&a.stack.tail&&(e.stack=a.stack.tail);var f={isPending:!1,isResolved:!1,isDeferredComplete:!1,deferreds:[]};for(c in b)f[c]=b[c];return e.push({__select__:f}).push(d,a.stack.index,a.stack.of)}function f(a){var b,c;if(a.deferreds.length)for(a.isDeferredComplete=!0,b=0,c=a.deferreds.length;c>b;b++)a.deferreds[b]()}function g(a,b){return"function"==typeof b?b.toString().replace(/(^\s+|\s+$)/gm,"").replace(/\n/gm,"").replace(/,\s*/gm,", ").replace(/\)\{/gm,") {"):b}function h(a,b){return function(c,d,e,f){return i(c,d,e,f,a,b)}}function i(b,c,e,f,g,h){var i,k,l,m,n=e.block,o=e["else"],p=d(c)||{};if(p.isResolved)return b;if(f.hasOwnProperty("key"))k=f.key;else{if(!p.hasOwnProperty("key"))return a(g,"No key specified","WARN"),b;k=p.key}return m=f.type||p.type,k=j(c.resolve(k),m),l=j(c.resolve(f.value),m),h(k,l)?(p.isPending||(i=!0,p.isPending=!0),n&&(b=b.render(n,c)),i&&(p.isResolved=!0)):o&&(b=b.render(o,c)),b}function j(a,b){switch(b&&(b=b.toLowerCase()),b){case"number":return+a;case"string":return String(a);case"boolean":return a="false"===a?!1:a,Boolean(a);case"date":return new Date(a)}return a}var k={},l={tap:function(a,c,d){return b("tap"),d.resolve(a)},sep:function(a,b,c){var d=c.block;return b.stack.index===b.stack.of-1?a:d?d(a,b):a},first:function(a,b,c){return 0===b.stack.index?c.block(a,b):a},last:function(a,b,c){return b.stack.index===b.stack.of-1?c.block(a,b):a},contextDump:function(b,c,d,e){var f,h,i=c.resolve(e.to),j=c.resolve(e.key);switch(j){case"full":f=c.stack;break;default:f=c.stack.head}switch(h=JSON.stringify(f,g,2),i){case"console":a("contextDump",h);break;default:h=h.replace(/</g,"\\u003c"),b=b.write(h)}return b},math:function(b,c,g,h){var i,j=h.key,k=h.method,l=h.operand,m=h.round;if(!h.hasOwnProperty("key")||!h.method)return a("math","`key` or `method` was not provided","ERROR"),b;switch(j=parseFloat(c.resolve(j)),l=parseFloat(c.resolve(l)),k){case"mod":0===l&&a("math","Division by 0","ERROR"),i=j%l;break;case"add":i=j+l;break;case"subtract":i=j-l;break;case"multiply":i=j*l;break;case"divide":0===l&&a("math","Division by 0","ERROR"),i=j/l;break;case"ceil":case"floor":case"round":case"abs":i=Math[k](j);break;case"toint":i=parseInt(j,10);break;default:a("math","Method `"+k+"` is not supported","ERROR")}return"undefined"!=typeof i&&(m&&(i=Math.round(i)),g&&g.block?(c=e(c,{key:i}),b=b.render(g.block,c),f(d(c))):b=b.write(i)),b},select:function(b,c,g,h){var i=g.block,j={};return h.hasOwnProperty("key")&&(j.key=c.resolve(h.key)),h.hasOwnProperty("type")&&(j.type=h.type),i?(c=e(c,j),b=b.render(i,c),f(d(c))):a("select","Missing body block","WARN"),b},eq:h("eq",function(a,b){return a===b}),ne:h("ne",function(a,b){return a!==b}),lt:h("lt",function(a,b){return b>a}),lte:h("lte",function(a,b){return b>=a}),gt:h("gt",function(a,b){return a>b}),gte:h("gte",function(a,b){return a>=b}),any:function(b,c,e,f){var g=d(c);return g?g.isDeferredComplete?a("any","Must not be nested inside {@any} or {@none} block","ERROR"):b=b.map(function(a){g.deferreds.push(function(){g.isResolved&&(a=a.render(e.block,c)),a.end()})}):a("any","Must be used inside a {@select} block","ERROR"),b},none:function(b,c,e,f){var g=d(c);return g?g.isDeferredComplete?a("none","Must not be nested inside {@any} or {@none} block","ERROR"):b=b.map(function(a){g.deferreds.push(function(){g.isResolved||(a=a.render(e.block,c)),a.end()})}):a("none","Must be used inside a {@select} block","ERROR"),b},size:function(a,b,c,d){var e,f,g=d.key;if(g=b.resolve(d.key),g&&g!==!0)if(dust.isArray(g))e=g.length;else if(!isNaN(parseFloat(g))&&isFinite(g))e=g;else if("object"==typeof g){e=0;for(f in g)g.hasOwnProperty(f)&&e++}else e=(g+"").length;else e=0;return a.write(e)}};for(var m in l)dust.helpers[m]=l[m];return dust});
!function(t){function e(t,e){return t.w('<div class="modal fade my-files-selector-modal"><div class="modal-dialog" style="width: 80%;"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4 class="modal-title">Select from My Files</h4></div><div class="modal-body"><div class="text-center"><i class="fa fa-spinner fa-4x fa-spin"></i></div></div><div class="modal-footer"><a class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</a><a class="btn btn-primary my-files-selector-select-selected" disabled="disabled" data-loading-text="<i class=&quot;fa fa-spinner fa-spin&quot;></i> Downloading data..."><i class="fa fa-check"></i> Select</a></div></div></div></div>')}return t.register("myFilesSelector",e),e.__dustBody=!0,e}(dust),function(t){function e(t,e){return t.w('<div class="modal fade recorder-modal"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4 class="modal-title">Recorder</h4></div><div class="modal-body"><ul class="nav nav-tabs" role="tablist"><li class="active"><a href=".recorder-normal" role="tab" data-toggle="tab">Normal</a></li><li><a href=".recorder-interp" role="tab" data-toggle="tab">Interpretation</a></li></ul><div class="tab-content" style="padding-top: 15px;"><div class="tab-pane active recorder-normal recorder-container-record"><div class="tt-media-preview-title"><input class="recorder-normal-title" type="text" value="My Recording"/></div><video class="recorder-normal-video" width="100%" preload="auto" muted="muted"></video></div><div class="tab-pane recorder-interp recorder-container-record"><div style="border: 2px dashed #ccc; line-height: 200px; height: 200px; text-align: center; display: none;"><button class="btn btn-primary btn-lg recorder-interp-select">Select a video to interpret</button></div><div class="row recorder-interp-main" style="display: none;"><div class="col-md-offset-6 col-md-6"><div class="tt-media-preview-title"><input class="recorder-interp-title" type="text" value="My Recording"/></div></div><div class="col-md-6"><video class="recorder-interp-video-p" width="100%" preload="auto"muted="muted"></video></div><div class="col-md-6"><video class="recorder-interp-video-r" width="100%" preload="auto"muted="muted"></video></div></div></div><div class="recorder-controls recorder-container-record"></div><div class="recorder-container-upload" style="display: none;"><label>Uploading...</label><div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0"aria-valuemax="100" style="width: 0%;">0%</div></div></div></div></div></div></div></div>')}return t.register("recorder",e),e.__dustBody=!0,e}(dust),function(t){function e(t,e){return t.s(e.get(["resources"],!1),e,{block:a},{})}function a(t,e){return t.w('<source src="').h("generateUrl",e,{},{path:e.get(["web_path"],!1)},"h").w('" />')}return t.register("recorder_source",e),e.__dustBody=!0,a.__dustBody=!0,e}(dust),function(t){function e(t,e){return t.h("select",e,{block:a},{key:e.getPath(!1,["media","type"])},"h")}function a(t,e){return t.h("eq",e,{block:i},{value:1},"h").h("eq",e,{block:u},{value:2},"h").h("eq",e,{block:p},{value:0},"h").h("eq",e,{block:_},{value:9},"h")}function i(t,e){return t.x(e.getPath(!1,["media","is_interpretation"]),e,{block:s},{}).w('<video class="tt-media-video"').x(e.get(["enable_controls"],!1),e,{block:o},{}).x(e.getPath(!1,["media","thumbnail_path"]),e,{block:l},{}).w('preload="none">').s(e.getPath(!1,["media","resources"]),e,{block:c},{}).w("</video>")}function s(t,e){return t.w('<video class="tt-media-pip-video"').x(e.get(["enable_controls"],!1),e,{block:r},{}).x(e.getPath(!1,["media","source","thumbnail_path"]),e,{block:d},{}).w('preload="none">').s(e.getPath(!1,["media","source","resources"]),e,{block:n},{}).w("</video>")}function r(t,e){return t.w(" controls ")}function d(t,e){return t.w(' poster="').h("generateUrl",e,{},{path:e.getPath(!1,["media","source","thumbnail_path"])},"h").w('" ')}function n(t,e){return t.w('<source src="').h("generateUrl",e,{},{path:e.get(["web_path"],!1)},"h").w('" />')}function o(t,e){return t.w(" controls ")}function l(t,e){return t.w(' poster="').h("generateUrl",e,{},{path:e.getPath(!1,["media","thumbnail_path"])},"h").w('" ')}function c(t,e){return t.w('<source src="').h("generateUrl",e,{},{path:e.get(["web_path"],!1)},"h").w('" />')}function u(t,e){return t.w('<audio class="tt-media-audio"').x(e.get(["enable_controls"],!1),e,{block:f},{}).w(">").s(e.getPath(!1,["media","resources"]),e,{block:h},{}).w("</audio>")}function f(t,e){return t.w(" controls")}function h(t,e){return t.w('<source src="').h("generateUrl",e,{},{path:e.get(["web_path"],!1)},"h").w('" />')}function p(t,e){return t.w('<img class="img-responsive center-block tt-media-img" src="').h("generateUrl",e,{},{path:e.getPath(!1,["media","source_resource","web_path"])},"h").w('" title="').f(e.get(["title"],!1),e,"h").w('" alt="').f(e.get(["title"],!1),e,"h").w('" />')}function _(t,e){return t.w('<div class="row"><div class="col-md-3 thumbnail text-center"><a href="').h("generateUrl",e,{},{path:e.getPath(!1,["media","source_resource","web_path"])},"h").w('"><p><i class="fa fa-file fa-5x"></i></p><div>').f(e.get(["title"],!1),e,"h").w("</div></a></div></div>")}return t.register("media_element",e),e.__dustBody=!0,a.__dustBody=!0,i.__dustBody=!0,s.__dustBody=!0,r.__dustBody=!0,d.__dustBody=!0,n.__dustBody=!0,o.__dustBody=!0,l.__dustBody=!0,c.__dustBody=!0,u.__dustBody=!0,f.__dustBody=!0,h.__dustBody=!0,p.__dustBody=!0,_.__dustBody=!0,e}(dust),function(t){function e(t,e){return t.w('<div class="tt-gallery-prev disabled gallery-prev"><i class="fa fa-chevron-left fa-3x"></i></div><div class="tt-gallery-item gallery-item"></div><div class="tt-gallery-next disabled gallery-next"><i class="fa fa-chevron-right fa-3x next"></i></div><div class="tt-gallery-carousel gallery-carousel"><div class="tt-gallery-left gallery-left"><i class="fa fa-chevron-left fa-2x"></i></div><div class="tt-gallery-thumbs gallery-thumbs"><div><ul></ul></div> </div><div class="tt-gallery-right gallery-right"><i class="fa fa-chevron-right fa-2x right"></i></div><div class="clear"></div></div><div class="tt-gallery-action gallery-action"><a title="Cut" data-action="4"><i class="fa fa-scissors fa-2x"></i></a><a title="Interpret" data-action="3"><i class="fa fa-video-camera fa-2x"></i></a><a data-action="2"><i class="fa fa-expand fa-2x"></i></a><a data-action="1"><i class="fa fa-times fa-2x"></i></a></div>')}return t.register("gallery_common",e),e.__dustBody=!0,e}(dust),function(t){function e(t,e){return t.w('<div id="').f(e.get(["identifier"],!1),e,"h").w('" class="tt-gallery-inline gallery-inner">').p("gallery_common",e,e,{}).w("</div>")}return t.register("gallery_inline",e),e.__dustBody=!0,e}(dust),function(t){function e(t,e){return t.w('<div id="').f(e.get(["identifier"],!1),e,"h").w('" class="tt-gallery-background gallery-inner"></div><div id="').f(e.get(["identifier"],!1),e,"h").w('" class="tt-gallery-preview gallery-inner">').p("gallery_common",e,e,{}).w("</div>")}return t.register("gallery_preview",e),e.__dustBody=!0,e}(dust),function(t){function e(t,e){return t.s(e.get(["media"],!1),e,{block:a},{})}function a(t,e){return t.w('<li data-mid="').f(e.getPath(!1,["data","id"]),e,"h").w('">').x(e.get(["can_edit"],!1),e,{block:i},{}).w("<div>").x(e.getPath(!1,["data","thumbnail_path"]),e,{block:s},{}).w("</div> </li>")}function i(t,e){return t.w('<span class="gallery-thumb-remove" title="Remove" data-mid="').f(e.getPath(!1,["data","id"]),e,"h").w('"><i class="fa fa-times-circle"></i></span>')}function s(t,e){return t.w('<img src="').h("generateUrl",e,{},{path:e.getPath(!1,["data","thumbnail_path"])},"h").w('" />')}return t.register("gallery_thumbnail",e),e.__dustBody=!0,a.__dustBody=!0,i.__dustBody=!0,s.__dustBody=!0,e}(dust),function(t){function e(t,e){return t.w('<div class="col-md-3"><div class="thumbnail tt-grid-div-body"><div class="tt-grid-div-item"><input type="checkbox" class="table-component-item" data-mid="').f(e.getPath(!1,["media","id"]),e,"h").w('" /></div><div class="tt-grid-div-thumbnail my-files-selector-file" data-mid="').f(e.getPath(!1,["media","id"]),e,"h").w('" disabled><div><span class="fa-stack fa-lg"><i class="fa fa-file-o fa-stack-2x"></i><i class="fa fa-play-circle fa-stack-1x"></i></span></div></div><div class="tt-grid-div-primary"><span class="edit-title my-files-selector-file" title="').f(e.get(["previewTitle"],!1),e,"h").w('" data-mid="').f(e.getPath(!1,["media","id"]),e,"h").w('" data-type="text" data-title="').f(e.get(["editTitle"],!1),e,"h").w('" disabled><i class="fa ').f(e.getPath(!1,["mediaIcon","icon"]),e,"h").w(' fa-2x" title="').f(e.getPath(!1,["mediaIcon","text"]),e,"h").w('"></i>').f(e.getPath(!1,["media","title"]),e,"h").w('</span><span><button class="btn btn-default edit-title"><i class="fa fa-pencil"></i></button><div class="btn-group"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-download"></i> <span class="caret"></span></button><ul class="dropdown-menu" role="menu">').s(e.getPath(!1,["media","resources"]),e,{block:a},{}).h("if",e,{block:i},{cond:s},"h").x(e.getPath(!1,["media","source_resource"]),e,{block:r},{}).w('</ul></div><a class="btn btn-default" target="_blank" href="').f(e.get(["shareUrl"],!1),e,"h").w('" title="').f(e.get(["shareTitle"],!1),e,"h").w('"><i class="fa fa-share-square-o"></i></a></span></div></div></div>')}function a(t,e){return t.w('<li><a href="').h("generateUrl",e,{},{path:e.get(["web_path"],!1)},"h").w(' download="').f(e.getPath(!1,["media","title"]),e,"h").w(".").f(e.get(["path"],!1),e,"h").w('">').f(e.get(["path"],!1),e,"h").w("</a></li>\n")}function i(t,e){return t.w('<li class="divider"></li>')}function s(t,e){return t.w("'").f(e.getPath(!1,["media","resources"]),e,"h").w("'.length > 0 && '").f(e.getPath(!1,["media","source_resource"]),e,"h").w("'.length")}function r(t,e){return t.w('<li><a href="').h("generateUrl",e,{},{path:e.getPath(!1,["media","source_resource","web_path"])},"h").w('" download="').f(e.getPath(!1,["media","title"]),e,"h").w(".").f(e.getPath(!1,["media","source_resource","path"]),e,"h").w('">Original</a></li>')}return t.register("myFilesGridElement",e),e.__dustBody=!0,a.__dustBody=!0,i.__dustBody=!0,s.__dustBody=!0,r.__dustBody=!0,e}(dust),function(t){function e(t,e){return t.w('<tr><td class="tt-list-table-col-item"><input type="checkbox" class="table-component-item" data-mid="').f(e.getPath(!1,["media","id"]),e,"h").w('" /></td><td class="tt-list-table-col-thumbnail my-files-selector-file" data-mid="').f(e.getPath(!1,["media","id"]),e,"h").w('" disabled ><div><span class="fa-stack fa-lg"><i class="fa fa-file-o fa-stack-2x"></i><i class="fa fa-play-circle fa-stack-1x"></i></span></div></td><td class="tt-list-table-col-primary tt-myFiles-nameColumn"><span class="edit-title my-files-selector-file" title="').f(e.get(["previewTitle"],!1),e,"h").w('" data-mid="').f(e.getPath(!1,["media","id"]),e,"h").w('" data-type="text" data-title="').f(e.get(["editTitle"],!1),e,"h").w('" disabled >').f(e.getPath(!1,["media","title"]),e,"h").w('</span><span><button class="btn btn-default edit-title"><i class="fa fa-pencil"></i></button><div class="btn-group"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-download"></i> <span class="caret"></span></button><ul class="dropdown-menu" role="menu">').s(e.getPath(!1,["media","resources"]),e,{block:a},{}).h("if",e,{block:i},{cond:s},"h").x(e.getPath(!1,["media","source_resource"]),e,{block:r},{}).w('</ul></div><a class="btn btn-default" target="_blank" href="').f(e.get(["shareUrl"],!1),e,"h").w('" title="').f(e.get(["shareTitle"],!1),e,"h").w('"><i class="fa fa-share-square-o"></i></a></span></td><td>').f(e.get(["timeUploaded"],!1),e,"h").w('</td><td><i class="fa ').f(e.getPath(!1,["mediaIcon","icon"]),e,"h").w(' fa-2x" title="').f(e.getPath(!1,["mediaIcon","text"]),e,"h").w('"></i></td><td>').x(e.get(["spinner"],!1),e,{"else":d,block:n},{}).w("</td></tr>")}function a(t,e){return t.w('<li><a href="').h("generateUrl",e,{},{path:e.get(["web_path"],!1)},"h").w(' download="').f(e.getPath(!1,["media","title"]),e,"h").w(".").f(e.get(["path"],!1),e,"h").w('">').f(e.get(["path"],!1),e,"h").w("</a></li>\n")}function i(t,e){return t.w('<li class="divider"></li>')}function s(t,e){return t.w("'").f(e.getPath(!1,["media","resources"]),e,"h").w("'.length > 0 && '").f(e.getPath(!1,["media","source_resource"]),e,"h").w("'.length")}function r(t,e){return t.w('<li><a href="').h("generateUrl",e,{},{path:e.getPath(!1,["media","source_resource","web_path"])},"h").w('" download="').f(e.getPath(!1,["media","title"]),e,"h").w(".").f(e.getPath(!1,["media","source_resource","path"]),e,"h").w('">Original</a></li>')}function d(t,e){return t.f(e.get(["formattedSize"],!1),e,"h")}function n(t,e){return t.w('<i class="fa fa-spinner fa-spin fa-large"> </i>')}return t.register("myFilesListElement",e),e.__dustBody=!0,a.__dustBody=!0,i.__dustBody=!0,s.__dustBody=!0,r.__dustBody=!0,d.__dustBody=!0,n.__dustBody=!0,e}(dust),function(t){function e(t,e){return t.s(e.get(["user"],!1),e,{block:a},{})}function a(t,e){return t.w('<a href="').h("generateRouteUrl",e,{},{name:"imdc_profile_user",str_params:"userName:username"},"h").w('"><img class="img-responsive center-block tt-avatar" src="').x(e.getPath(!1,["profile","avatar"]),e,{"else":i,block:s},{}).w('" /></a>')}function i(t,e){return t.h("generateUrl",e,{},{path:"bundles/imdcterptube/img/no_avatar.jpg"},"h")}function s(t,e){return t.h("generateUrl",e,{},{path:e.getPath(!1,["profile","avatar","source_resource","web_path"])},"h")}return t.register("user_avatar",e),e.__dustBody=!0,a.__dustBody=!0,i.__dustBody=!0,s.__dustBody=!0,e}(dust),function(t){function e(t,e){return t.s(e.get(["user"],!1),e,{block:a},{})}function a(t,e){return t.w('<ul class="list-unstyled list-inline"><li>').h("isLoggedInUser",e,{"else":i,block:s},{id:e.get(["id"],!1)},"h").w("</li>&nbsp; <!-- match spaces that are still present between tags when rendering from twig --><li>").h("isLoggedInUser",e,{"else":r,block:o},{id:e.get(["id"],!1)},"h").w("</li>").h("isNotLoggedInUser",e,{block:l},{id:e.get(["id"],!1)},"h").w("</ul>")}function i(t,e){return t.w('<a href="').h("generateRouteUrl",e,{},{name:"imdc_message_new",str_params:"userid:username"},"h").w('" title="Send a message to ').f(e.get(["username"],!1),e,"h").w('"><i class="fa fa-envelope-o"></i></a>')}function s(t,e){return t.w('<i class="fa fa-envelope-o" style="color: lightgray;"></i>')}function r(t,e){return t.h("isUserOnList",e,{"else":d,block:n},{list:"Friends",username:e.get(["username"],!1)},"h")}function d(t,e){return t.w('<a href="').h("generateRouteUrl",e,{},{name:"imdc_member_friend_add",str_params:"userid:id"},"h").w('" title="Click to add ').f(e.get(["username"],!1),e,"h").w(' to your friends list"><i class="fa fa-plus"></i></a>')}function n(t,e){return t.w('<a href="').h("generateRouteUrl",e,{},{name:"imdc_member_friend_remove",str_params:"userid:id"},"h").w('" title="Remove ').f(e.get(["username"],!1),e,"h").w(' from your friends list"><i class="fa fa-minus"></i></a>')}function o(t,e){return t.w('<i class="fa fa-user" style="color: lightgray;" title="This is you"></i>')}function l(t,e){return t.w("&nbsp;<li>").h("isUserOnList",e,{"else":c,block:h},{list:"Mentor",username:e.get(["username"],!1)},"h").w("</li>&nbsp;<li>").h("isUserOnList",e,{"else":p,block:m},{list:"Mentee",username:e.get(["username"],!1)},"h").w("</li>")}function c(t,e){return t.h("isUserOnList",e,{"else":u,block:f},{list:"InvitedMentor",username:e.get(["username"],!1)},"h")}function u(t,e){return t.w('<a href="').h("generateRouteUrl",e,{},{name:"imdc_invitation_mentor",str_params:"id:id"},"h").w('" title="Invite user to be your mentor"><i class="fa fa-inbox"></i></a>')}function f(t,e){return t.w('<i class="fa fa-inbox" title="Invitation for mentorship already sent"></i>')}function h(t,e){return t.w('<i class="fa fa-star" title="This user is your mentor"></i>')}function p(t,e){return t.h("isUserOnList",e,{"else":_,block:g},{list:"InvitedMentee",username:e.get(["username"],!1)},"h")}function _(t,e){return t.w('<a href="').h("generateRouteUrl",e,{},{name:"imdc_invitation_mentee",str_params:"id:id"},"h").w('" title="Invite user to be your mentee"><i class="fa fa-star-o"></i></a>')}function g(t,e){return t.w('<i class="fa fa-star-o" title="Invitation for menteeship already sent"></i>')}function m(t,e){return t.w('<i class="fa fa-star" title="This user is your mentee"></i>')}return t.register("user_connect_tools",e),e.__dustBody=!0,a.__dustBody=!0,i.__dustBody=!0,s.__dustBody=!0,r.__dustBody=!0,d.__dustBody=!0,n.__dustBody=!0,o.__dustBody=!0,l.__dustBody=!0,c.__dustBody=!0,u.__dustBody=!0,f.__dustBody=!0,h.__dustBody=!0,p.__dustBody=!0,_.__dustBody=!0,g.__dustBody=!0,m.__dustBody=!0,e}(dust),function(t){function e(t,e){return t.s(e.get(["user"],!1),e,{block:a},{})}function a(t,e){return t.w('<a href="').h("generateRouteUrl",e,{},{name:"imdc_profile_user",str_params:"userName:username"},"h").w('" title="').f(e.get(["username"],!1),e,"h").w('"><span class="tt-username">').f(e.get(["username"],!1),e,"h").w("</span></a>")}return t.register("user_username",e),e.__dustBody=!0,a.__dustBody=!0,e}(dust),function(t){function e(t,e){return e=e.shiftBlocks(r),t.p("post_view",e,e,{})}function a(t,e){return e=e.shiftBlocks(r),t.w('<div class="row"><div class="col-md-12">').f(e.get(["form"],!1),e,"h",["s"]).w("</div></div>")}function i(t,e){return e=e.shiftBlocks(r),t}function s(t,e){return e=e.shiftBlocks(r),t}t.register("post_edit",e);var r={post_content:a,post_tools:i,post_reply:s};return e.__dustBody=!0,a.__dustBody=!0,i.__dustBody=!0,s.__dustBody=!0,e}(dust),function(t){function e(t,e){return t.w('<div class="row post-container').x(e.get(["is_post_reply"],!1),e,{block:a},{}).w('" data-pid="').f(e.get(["id"],!1),e,"h").w('"><div class="').x(e.get(["is_post_reply"],!1),e,{"else":i,block:s},{}).w('">').x(e.get(["is_post_reply"],!1),e,{block:r},{}).f(e.get(["form"],!1),e,"h",["s"]).x(e.get(["is_post_reply"],!1),e,{block:d},{}).w("</div></div>")}function a(t,e){return t.w(' tt-post-container tt-post-reply-container" style="margin-bottom: 15px;')}function i(t,e){return t.w("col-md-12")}function s(t,e){return t.w("col-md-offset-1 col-md-11")}function r(t,e){return t.w('<div class="row tt-post-container-inner tt-post-reply-container-inner"><div class="col-md-12">')}function d(t,e){return t.w("</div></div>")}return t.register("post_new",e),e.__dustBody=!0,a.__dustBody=!0,i.__dustBody=!0,s.__dustBody=!0,r.__dustBody=!0,d.__dustBody=!0,e}(dust),function(t){function e(t,e){return t.w('<div class="row post-container tt-post-container').x(e.get(["is_post_reply"],!1),e,{block:a},{}).w('" style="margin-bottom: 15px;" data-pid="').f(e.get(["id"],!1),e,"h").w('"').x(e.get(["is_post_reply"],!1),e,{block:i},{}).w('><div class="').x(e.get(["is_post_reply"],!1),e,{"else":s,block:r},{}).w('"><div class="row tt-post-container-inner').x(e.get(["is_post_reply"],!1),e,{block:d},{}).w('"><div class="col-md-2 text-center"><div class="row"><div class="col-md-12">').p("user_avatar",e,e,{user:e.get(["author"],!1)}).p("user_username",e,e,{user:e.get(["author"],!1)}).w('</div></div><div class="row"><div class="col-md-12">').p("user_connect_tools",e,e,{user:e.get(["author"],!1)}).w('</div></div></div><div class="col-md-9 tt-post-content-container">').b(e.getBlock("post_content"),e,{block:n},{}).w('</div><div class="col-md-1 pull-right">').b(e.getBlock("post_tools"),e,{block:u},{}).w("</div></div>").nx(e.get(["is_post_reply"],!1),e,{block:p},{}).w("</div></div>")}function a(t,e){return t.w(" tt-post-reply-container")}function i(t,e){return t.w(' data-ppid="').f(e.get(["parent_post_id"],!1),e,"h").w('"')}function s(t,e){return t.w("col-md-12")}function r(t,e){return t.w("col-md-offset-1 col-md-11")}function d(t,e){return t.w(" tt-post-reply-container-inner")}function n(t,e){return t.w('<div class="row"><div class="col-md-12"><div class="text-right tt-date">').f(e.get(["created"],!1),e,"h",["date"]).w("&nbsp;").x(e.get(["edited_by"],!1),e,{block:o},{}).w('</div></div><div class="col-md-12">').x(e.get(["is_temporal"],!1),e,{block:l},{}).h("gt",e,{block:c},{key:e.getPath(!1,["ordered_media","length"]),value:0},"h").w("<div><p>").f(e.get(["content"],!1),e,"h",["nl2br","s"]).w("</p></div></div></div>")}function o(t,e){return t.w("(edited ").f(e.get(["edited_at"],!1),e,"h",["date"]).w(" by ").f(e.getPath(!1,["edited_by","username"]),e,"h").w(")")}function l(t,e){return t.w('<div class="tt-post-timeline-container"><div class="tt-post-timeline"><div class="tt-post-timeline-keypoint post-timeline-keypoint"></div></div></div>')}function c(t,e){return t.w('<div class="row"><div class="col-md-12"><div class="post-gallery" style="margin-bottom: 10px; height: 400px; width: 100%;"></div></div></div>')}function u(t,e){return t.w('<ul class="list-unstyled">').x(e.get(["is_temporal"],!1),e,{block:f},{}).h("isCurrentUser",e,{block:h},{id:e.getPath(!1,["author","id"])},"h").w('</ul><div class="modal fade post-delete-modal" data-pid="').f(e.get(["id"],!1),e,"h").w('"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4 class="modal-title">Delete this post</h4></div><div class="modal-body text-center"><p>This post will be permanently deleted.<br />This action cannot be undone.<br />Are you sure?</p></div><div class="modal-footer"><a class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</a><a class="btn btn-danger post-delete" data-loading-text="<i class=&quot;fa fa-spinner fa-spin&quot;></i> Deleting..."><i class="fa fa-trash-o"></i> Delete</a></div></div></div></div>')}function f(t,e){return t.w('<li><a class="post-timeline-keypoint" href="#" title="Jump to selected timeline region"><i class="fa fa-clock-o"></i></a></li>')}function h(t,e){return t.w('<li><a class="post-edit" href="#" title="Edit this post"><i class="fa fa-pencil"></i></a></li><li><a href="#" title="Delete this post" data-toggle="modal" data-target=".post-delete-modal[data-pid=').f(e.get(["id"],!1),e,"h").w(']"><i class="fa fa-trash-o"></i></a></li>')}function p(t,e){return t.b(e.getBlock("post_reply"),e,{block:_},{})}function _(t,e){return t.w('<div class="row"><div class="col-md-1 col-md-offset-11"><p><a class="post-new" href="#" title="Reply to this post"><i class="fa fa-reply fa-rotate-180 fa-lg"></i></a></p></div></div>')}return t.register("post_view",e),e.__dustBody=!0,a.__dustBody=!0,i.__dustBody=!0,s.__dustBody=!0,r.__dustBody=!0,d.__dustBody=!0,n.__dustBody=!0,o.__dustBody=!0,l.__dustBody=!0,c.__dustBody=!0,u.__dustBody=!0,f.__dustBody=!0,h.__dustBody=!0,p.__dustBody=!0,_.__dustBody=!0,e}(dust),function(t){function e(t,e){return t.w('<div class="row"><div class="col-md-12">').s(e.get(["posts"],!1),e,{"else":a,block:i},{}).w('<div class="thread-reply-spacer"></div></div></div>')}function a(t,e){return t.w('<div class="lead text-center" style="padding-top: 30px;">No replies yet!</div>')}function i(t,e){return t.nx(e.get(["is_post_reply"],!1),e,{block:s},{})}function s(t,e){return t.p("post_view",e,e,{post:e.getPath(!0,[])}).s(e.get(["replies"],!1),e,{block:r},{})}function r(t,e){return t.p("post_view",e,e,{post:e.getPath(!0,[])})}return t.register("thread_view_posts",e),e.__dustBody=!0,a.__dustBody=!0,i.__dustBody=!0,s.__dustBody=!0,r.__dustBody=!0,e}(dust);
define("templates", ["dust-helpers"], function(){});

Function.prototype.extend = (function () {
    /*for (var p in parent)
        this[p] = parent[p];

    for (var p in parent.prototype)
        this.prototype[p] = parent.prototype[p];

    this.prototype.constructor = this;
    this.prototype.parent = parent.prototype;

    return this;*/

    return function (parent) {
        var _self = this;
        Object.keys(parent).forEach(function (key) {
            var value = parent[key];
            if (_.isObject(value)) { //TODO add support for arrays or refactor all classes to use only simple types
                _self[key] = _.clone(value);
            } else {
                _self[key] = value;
            }
        });

        this.prototype = Object.create(parent.prototype);
        this.prototype.constructor = this;
    }
})();

define("extra", function(){});

//     Underscore.js 1.8.3
//     http://underscorejs.org
//     (c) 2009-2015 Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
//     Underscore may be freely distributed under the MIT license.
(function(){function n(n){function t(t,r,e,u,i,o){for(;i>=0&&o>i;i+=n){var a=u?u[i]:i;e=r(e,t[a],a,t)}return e}return function(r,e,u,i){e=b(e,i,4);var o=!k(r)&&m.keys(r),a=(o||r).length,c=n>0?0:a-1;return arguments.length<3&&(u=r[o?o[c]:c],c+=n),t(r,e,u,o,c,a)}}function t(n){return function(t,r,e){r=x(r,e);for(var u=O(t),i=n>0?0:u-1;i>=0&&u>i;i+=n)if(r(t[i],i,t))return i;return-1}}function r(n,t,r){return function(e,u,i){var o=0,a=O(e);if("number"==typeof i)n>0?o=i>=0?i:Math.max(i+a,o):a=i>=0?Math.min(i+1,a):i+a+1;else if(r&&i&&a)return i=r(e,u),e[i]===u?i:-1;if(u!==u)return i=t(l.call(e,o,a),m.isNaN),i>=0?i+o:-1;for(i=n>0?o:a-1;i>=0&&a>i;i+=n)if(e[i]===u)return i;return-1}}function e(n,t){var r=I.length,e=n.constructor,u=m.isFunction(e)&&e.prototype||a,i="constructor";for(m.has(n,i)&&!m.contains(t,i)&&t.push(i);r--;)i=I[r],i in n&&n[i]!==u[i]&&!m.contains(t,i)&&t.push(i)}var u=this,i=u._,o=Array.prototype,a=Object.prototype,c=Function.prototype,f=o.push,l=o.slice,s=a.toString,p=a.hasOwnProperty,h=Array.isArray,v=Object.keys,g=c.bind,y=Object.create,d=function(){},m=function(n){return n instanceof m?n:this instanceof m?void(this._wrapped=n):new m(n)};"undefined"!=typeof exports?("undefined"!=typeof module&&module.exports&&(exports=module.exports=m),exports._=m):u._=m,m.VERSION="1.8.3";var b=function(n,t,r){if(t===void 0)return n;switch(null==r?3:r){case 1:return function(r){return n.call(t,r)};case 2:return function(r,e){return n.call(t,r,e)};case 3:return function(r,e,u){return n.call(t,r,e,u)};case 4:return function(r,e,u,i){return n.call(t,r,e,u,i)}}return function(){return n.apply(t,arguments)}},x=function(n,t,r){return null==n?m.identity:m.isFunction(n)?b(n,t,r):m.isObject(n)?m.matcher(n):m.property(n)};m.iteratee=function(n,t){return x(n,t,1/0)};var _=function(n,t){return function(r){var e=arguments.length;if(2>e||null==r)return r;for(var u=1;e>u;u++)for(var i=arguments[u],o=n(i),a=o.length,c=0;a>c;c++){var f=o[c];t&&r[f]!==void 0||(r[f]=i[f])}return r}},j=function(n){if(!m.isObject(n))return{};if(y)return y(n);d.prototype=n;var t=new d;return d.prototype=null,t},w=function(n){return function(t){return null==t?void 0:t[n]}},A=Math.pow(2,53)-1,O=w("length"),k=function(n){var t=O(n);return"number"==typeof t&&t>=0&&A>=t};m.each=m.forEach=function(n,t,r){t=b(t,r);var e,u;if(k(n))for(e=0,u=n.length;u>e;e++)t(n[e],e,n);else{var i=m.keys(n);for(e=0,u=i.length;u>e;e++)t(n[i[e]],i[e],n)}return n},m.map=m.collect=function(n,t,r){t=x(t,r);for(var e=!k(n)&&m.keys(n),u=(e||n).length,i=Array(u),o=0;u>o;o++){var a=e?e[o]:o;i[o]=t(n[a],a,n)}return i},m.reduce=m.foldl=m.inject=n(1),m.reduceRight=m.foldr=n(-1),m.find=m.detect=function(n,t,r){var e;return e=k(n)?m.findIndex(n,t,r):m.findKey(n,t,r),e!==void 0&&e!==-1?n[e]:void 0},m.filter=m.select=function(n,t,r){var e=[];return t=x(t,r),m.each(n,function(n,r,u){t(n,r,u)&&e.push(n)}),e},m.reject=function(n,t,r){return m.filter(n,m.negate(x(t)),r)},m.every=m.all=function(n,t,r){t=x(t,r);for(var e=!k(n)&&m.keys(n),u=(e||n).length,i=0;u>i;i++){var o=e?e[i]:i;if(!t(n[o],o,n))return!1}return!0},m.some=m.any=function(n,t,r){t=x(t,r);for(var e=!k(n)&&m.keys(n),u=(e||n).length,i=0;u>i;i++){var o=e?e[i]:i;if(t(n[o],o,n))return!0}return!1},m.contains=m.includes=m.include=function(n,t,r,e){return k(n)||(n=m.values(n)),("number"!=typeof r||e)&&(r=0),m.indexOf(n,t,r)>=0},m.invoke=function(n,t){var r=l.call(arguments,2),e=m.isFunction(t);return m.map(n,function(n){var u=e?t:n[t];return null==u?u:u.apply(n,r)})},m.pluck=function(n,t){return m.map(n,m.property(t))},m.where=function(n,t){return m.filter(n,m.matcher(t))},m.findWhere=function(n,t){return m.find(n,m.matcher(t))},m.max=function(n,t,r){var e,u,i=-1/0,o=-1/0;if(null==t&&null!=n){n=k(n)?n:m.values(n);for(var a=0,c=n.length;c>a;a++)e=n[a],e>i&&(i=e)}else t=x(t,r),m.each(n,function(n,r,e){u=t(n,r,e),(u>o||u===-1/0&&i===-1/0)&&(i=n,o=u)});return i},m.min=function(n,t,r){var e,u,i=1/0,o=1/0;if(null==t&&null!=n){n=k(n)?n:m.values(n);for(var a=0,c=n.length;c>a;a++)e=n[a],i>e&&(i=e)}else t=x(t,r),m.each(n,function(n,r,e){u=t(n,r,e),(o>u||1/0===u&&1/0===i)&&(i=n,o=u)});return i},m.shuffle=function(n){for(var t,r=k(n)?n:m.values(n),e=r.length,u=Array(e),i=0;e>i;i++)t=m.random(0,i),t!==i&&(u[i]=u[t]),u[t]=r[i];return u},m.sample=function(n,t,r){return null==t||r?(k(n)||(n=m.values(n)),n[m.random(n.length-1)]):m.shuffle(n).slice(0,Math.max(0,t))},m.sortBy=function(n,t,r){return t=x(t,r),m.pluck(m.map(n,function(n,r,e){return{value:n,index:r,criteria:t(n,r,e)}}).sort(function(n,t){var r=n.criteria,e=t.criteria;if(r!==e){if(r>e||r===void 0)return 1;if(e>r||e===void 0)return-1}return n.index-t.index}),"value")};var F=function(n){return function(t,r,e){var u={};return r=x(r,e),m.each(t,function(e,i){var o=r(e,i,t);n(u,e,o)}),u}};m.groupBy=F(function(n,t,r){m.has(n,r)?n[r].push(t):n[r]=[t]}),m.indexBy=F(function(n,t,r){n[r]=t}),m.countBy=F(function(n,t,r){m.has(n,r)?n[r]++:n[r]=1}),m.toArray=function(n){return n?m.isArray(n)?l.call(n):k(n)?m.map(n,m.identity):m.values(n):[]},m.size=function(n){return null==n?0:k(n)?n.length:m.keys(n).length},m.partition=function(n,t,r){t=x(t,r);var e=[],u=[];return m.each(n,function(n,r,i){(t(n,r,i)?e:u).push(n)}),[e,u]},m.first=m.head=m.take=function(n,t,r){return null==n?void 0:null==t||r?n[0]:m.initial(n,n.length-t)},m.initial=function(n,t,r){return l.call(n,0,Math.max(0,n.length-(null==t||r?1:t)))},m.last=function(n,t,r){return null==n?void 0:null==t||r?n[n.length-1]:m.rest(n,Math.max(0,n.length-t))},m.rest=m.tail=m.drop=function(n,t,r){return l.call(n,null==t||r?1:t)},m.compact=function(n){return m.filter(n,m.identity)};var S=function(n,t,r,e){for(var u=[],i=0,o=e||0,a=O(n);a>o;o++){var c=n[o];if(k(c)&&(m.isArray(c)||m.isArguments(c))){t||(c=S(c,t,r));var f=0,l=c.length;for(u.length+=l;l>f;)u[i++]=c[f++]}else r||(u[i++]=c)}return u};m.flatten=function(n,t){return S(n,t,!1)},m.without=function(n){return m.difference(n,l.call(arguments,1))},m.uniq=m.unique=function(n,t,r,e){m.isBoolean(t)||(e=r,r=t,t=!1),null!=r&&(r=x(r,e));for(var u=[],i=[],o=0,a=O(n);a>o;o++){var c=n[o],f=r?r(c,o,n):c;t?(o&&i===f||u.push(c),i=f):r?m.contains(i,f)||(i.push(f),u.push(c)):m.contains(u,c)||u.push(c)}return u},m.union=function(){return m.uniq(S(arguments,!0,!0))},m.intersection=function(n){for(var t=[],r=arguments.length,e=0,u=O(n);u>e;e++){var i=n[e];if(!m.contains(t,i)){for(var o=1;r>o&&m.contains(arguments[o],i);o++);o===r&&t.push(i)}}return t},m.difference=function(n){var t=S(arguments,!0,!0,1);return m.filter(n,function(n){return!m.contains(t,n)})},m.zip=function(){return m.unzip(arguments)},m.unzip=function(n){for(var t=n&&m.max(n,O).length||0,r=Array(t),e=0;t>e;e++)r[e]=m.pluck(n,e);return r},m.object=function(n,t){for(var r={},e=0,u=O(n);u>e;e++)t?r[n[e]]=t[e]:r[n[e][0]]=n[e][1];return r},m.findIndex=t(1),m.findLastIndex=t(-1),m.sortedIndex=function(n,t,r,e){r=x(r,e,1);for(var u=r(t),i=0,o=O(n);o>i;){var a=Math.floor((i+o)/2);r(n[a])<u?i=a+1:o=a}return i},m.indexOf=r(1,m.findIndex,m.sortedIndex),m.lastIndexOf=r(-1,m.findLastIndex),m.range=function(n,t,r){null==t&&(t=n||0,n=0),r=r||1;for(var e=Math.max(Math.ceil((t-n)/r),0),u=Array(e),i=0;e>i;i++,n+=r)u[i]=n;return u};var E=function(n,t,r,e,u){if(!(e instanceof t))return n.apply(r,u);var i=j(n.prototype),o=n.apply(i,u);return m.isObject(o)?o:i};m.bind=function(n,t){if(g&&n.bind===g)return g.apply(n,l.call(arguments,1));if(!m.isFunction(n))throw new TypeError("Bind must be called on a function");var r=l.call(arguments,2),e=function(){return E(n,e,t,this,r.concat(l.call(arguments)))};return e},m.partial=function(n){var t=l.call(arguments,1),r=function(){for(var e=0,u=t.length,i=Array(u),o=0;u>o;o++)i[o]=t[o]===m?arguments[e++]:t[o];for(;e<arguments.length;)i.push(arguments[e++]);return E(n,r,this,this,i)};return r},m.bindAll=function(n){var t,r,e=arguments.length;if(1>=e)throw new Error("bindAll must be passed function names");for(t=1;e>t;t++)r=arguments[t],n[r]=m.bind(n[r],n);return n},m.memoize=function(n,t){var r=function(e){var u=r.cache,i=""+(t?t.apply(this,arguments):e);return m.has(u,i)||(u[i]=n.apply(this,arguments)),u[i]};return r.cache={},r},m.delay=function(n,t){var r=l.call(arguments,2);return setTimeout(function(){return n.apply(null,r)},t)},m.defer=m.partial(m.delay,m,1),m.throttle=function(n,t,r){var e,u,i,o=null,a=0;r||(r={});var c=function(){a=r.leading===!1?0:m.now(),o=null,i=n.apply(e,u),o||(e=u=null)};return function(){var f=m.now();a||r.leading!==!1||(a=f);var l=t-(f-a);return e=this,u=arguments,0>=l||l>t?(o&&(clearTimeout(o),o=null),a=f,i=n.apply(e,u),o||(e=u=null)):o||r.trailing===!1||(o=setTimeout(c,l)),i}},m.debounce=function(n,t,r){var e,u,i,o,a,c=function(){var f=m.now()-o;t>f&&f>=0?e=setTimeout(c,t-f):(e=null,r||(a=n.apply(i,u),e||(i=u=null)))};return function(){i=this,u=arguments,o=m.now();var f=r&&!e;return e||(e=setTimeout(c,t)),f&&(a=n.apply(i,u),i=u=null),a}},m.wrap=function(n,t){return m.partial(t,n)},m.negate=function(n){return function(){return!n.apply(this,arguments)}},m.compose=function(){var n=arguments,t=n.length-1;return function(){for(var r=t,e=n[t].apply(this,arguments);r--;)e=n[r].call(this,e);return e}},m.after=function(n,t){return function(){return--n<1?t.apply(this,arguments):void 0}},m.before=function(n,t){var r;return function(){return--n>0&&(r=t.apply(this,arguments)),1>=n&&(t=null),r}},m.once=m.partial(m.before,2);var M=!{toString:null}.propertyIsEnumerable("toString"),I=["valueOf","isPrototypeOf","toString","propertyIsEnumerable","hasOwnProperty","toLocaleString"];m.keys=function(n){if(!m.isObject(n))return[];if(v)return v(n);var t=[];for(var r in n)m.has(n,r)&&t.push(r);return M&&e(n,t),t},m.allKeys=function(n){if(!m.isObject(n))return[];var t=[];for(var r in n)t.push(r);return M&&e(n,t),t},m.values=function(n){for(var t=m.keys(n),r=t.length,e=Array(r),u=0;r>u;u++)e[u]=n[t[u]];return e},m.mapObject=function(n,t,r){t=x(t,r);for(var e,u=m.keys(n),i=u.length,o={},a=0;i>a;a++)e=u[a],o[e]=t(n[e],e,n);return o},m.pairs=function(n){for(var t=m.keys(n),r=t.length,e=Array(r),u=0;r>u;u++)e[u]=[t[u],n[t[u]]];return e},m.invert=function(n){for(var t={},r=m.keys(n),e=0,u=r.length;u>e;e++)t[n[r[e]]]=r[e];return t},m.functions=m.methods=function(n){var t=[];for(var r in n)m.isFunction(n[r])&&t.push(r);return t.sort()},m.extend=_(m.allKeys),m.extendOwn=m.assign=_(m.keys),m.findKey=function(n,t,r){t=x(t,r);for(var e,u=m.keys(n),i=0,o=u.length;o>i;i++)if(e=u[i],t(n[e],e,n))return e},m.pick=function(n,t,r){var e,u,i={},o=n;if(null==o)return i;m.isFunction(t)?(u=m.allKeys(o),e=b(t,r)):(u=S(arguments,!1,!1,1),e=function(n,t,r){return t in r},o=Object(o));for(var a=0,c=u.length;c>a;a++){var f=u[a],l=o[f];e(l,f,o)&&(i[f]=l)}return i},m.omit=function(n,t,r){if(m.isFunction(t))t=m.negate(t);else{var e=m.map(S(arguments,!1,!1,1),String);t=function(n,t){return!m.contains(e,t)}}return m.pick(n,t,r)},m.defaults=_(m.allKeys,!0),m.create=function(n,t){var r=j(n);return t&&m.extendOwn(r,t),r},m.clone=function(n){return m.isObject(n)?m.isArray(n)?n.slice():m.extend({},n):n},m.tap=function(n,t){return t(n),n},m.isMatch=function(n,t){var r=m.keys(t),e=r.length;if(null==n)return!e;for(var u=Object(n),i=0;e>i;i++){var o=r[i];if(t[o]!==u[o]||!(o in u))return!1}return!0};var N=function(n,t,r,e){if(n===t)return 0!==n||1/n===1/t;if(null==n||null==t)return n===t;n instanceof m&&(n=n._wrapped),t instanceof m&&(t=t._wrapped);var u=s.call(n);if(u!==s.call(t))return!1;switch(u){case"[object RegExp]":case"[object String]":return""+n==""+t;case"[object Number]":return+n!==+n?+t!==+t:0===+n?1/+n===1/t:+n===+t;case"[object Date]":case"[object Boolean]":return+n===+t}var i="[object Array]"===u;if(!i){if("object"!=typeof n||"object"!=typeof t)return!1;var o=n.constructor,a=t.constructor;if(o!==a&&!(m.isFunction(o)&&o instanceof o&&m.isFunction(a)&&a instanceof a)&&"constructor"in n&&"constructor"in t)return!1}r=r||[],e=e||[];for(var c=r.length;c--;)if(r[c]===n)return e[c]===t;if(r.push(n),e.push(t),i){if(c=n.length,c!==t.length)return!1;for(;c--;)if(!N(n[c],t[c],r,e))return!1}else{var f,l=m.keys(n);if(c=l.length,m.keys(t).length!==c)return!1;for(;c--;)if(f=l[c],!m.has(t,f)||!N(n[f],t[f],r,e))return!1}return r.pop(),e.pop(),!0};m.isEqual=function(n,t){return N(n,t)},m.isEmpty=function(n){return null==n?!0:k(n)&&(m.isArray(n)||m.isString(n)||m.isArguments(n))?0===n.length:0===m.keys(n).length},m.isElement=function(n){return!(!n||1!==n.nodeType)},m.isArray=h||function(n){return"[object Array]"===s.call(n)},m.isObject=function(n){var t=typeof n;return"function"===t||"object"===t&&!!n},m.each(["Arguments","Function","String","Number","Date","RegExp","Error"],function(n){m["is"+n]=function(t){return s.call(t)==="[object "+n+"]"}}),m.isArguments(arguments)||(m.isArguments=function(n){return m.has(n,"callee")}),"function"!=typeof/./&&"object"!=typeof Int8Array&&(m.isFunction=function(n){return"function"==typeof n||!1}),m.isFinite=function(n){return isFinite(n)&&!isNaN(parseFloat(n))},m.isNaN=function(n){return m.isNumber(n)&&n!==+n},m.isBoolean=function(n){return n===!0||n===!1||"[object Boolean]"===s.call(n)},m.isNull=function(n){return null===n},m.isUndefined=function(n){return n===void 0},m.has=function(n,t){return null!=n&&p.call(n,t)},m.noConflict=function(){return u._=i,this},m.identity=function(n){return n},m.constant=function(n){return function(){return n}},m.noop=function(){},m.property=w,m.propertyOf=function(n){return null==n?function(){}:function(t){return n[t]}},m.matcher=m.matches=function(n){return n=m.extendOwn({},n),function(t){return m.isMatch(t,n)}},m.times=function(n,t,r){var e=Array(Math.max(0,n));t=b(t,r,1);for(var u=0;n>u;u++)e[u]=t(u);return e},m.random=function(n,t){return null==t&&(t=n,n=0),n+Math.floor(Math.random()*(t-n+1))},m.now=Date.now||function(){return(new Date).getTime()};var B={"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#x27;","`":"&#x60;"},T=m.invert(B),R=function(n){var t=function(t){return n[t]},r="(?:"+m.keys(n).join("|")+")",e=RegExp(r),u=RegExp(r,"g");return function(n){return n=null==n?"":""+n,e.test(n)?n.replace(u,t):n}};m.escape=R(B),m.unescape=R(T),m.result=function(n,t,r){var e=null==n?void 0:n[t];return e===void 0&&(e=r),m.isFunction(e)?e.call(n):e};var q=0;m.uniqueId=function(n){var t=++q+"";return n?n+t:t},m.templateSettings={evaluate:/<%([\s\S]+?)%>/g,interpolate:/<%=([\s\S]+?)%>/g,escape:/<%-([\s\S]+?)%>/g};var K=/(.)^/,z={"'":"'","\\":"\\","\r":"r","\n":"n","\u2028":"u2028","\u2029":"u2029"},D=/\\|'|\r|\n|\u2028|\u2029/g,L=function(n){return"\\"+z[n]};m.template=function(n,t,r){!t&&r&&(t=r),t=m.defaults({},t,m.templateSettings);var e=RegExp([(t.escape||K).source,(t.interpolate||K).source,(t.evaluate||K).source].join("|")+"|$","g"),u=0,i="__p+='";n.replace(e,function(t,r,e,o,a){return i+=n.slice(u,a).replace(D,L),u=a+t.length,r?i+="'+\n((__t=("+r+"))==null?'':_.escape(__t))+\n'":e?i+="'+\n((__t=("+e+"))==null?'':__t)+\n'":o&&(i+="';\n"+o+"\n__p+='"),t}),i+="';\n",t.variable||(i="with(obj||{}){\n"+i+"}\n"),i="var __t,__p='',__j=Array.prototype.join,"+"print=function(){__p+=__j.call(arguments,'');};\n"+i+"return __p;\n";try{var o=new Function(t.variable||"obj","_",i)}catch(a){throw a.source=i,a}var c=function(n){return o.call(this,n,m)},f=t.variable||"obj";return c.source="function("+f+"){\n"+i+"}",c},m.chain=function(n){var t=m(n);return t._chain=!0,t};var P=function(n,t){return n._chain?m(t).chain():t};m.mixin=function(n){m.each(m.functions(n),function(t){var r=m[t]=n[t];m.prototype[t]=function(){var n=[this._wrapped];return f.apply(n,arguments),P(this,r.apply(m,n))}})},m.mixin(m),m.each(["pop","push","reverse","shift","sort","splice","unshift"],function(n){var t=o[n];m.prototype[n]=function(){var r=this._wrapped;return t.apply(r,arguments),"shift"!==n&&"splice"!==n||0!==r.length||delete r[0],P(this,r)}}),m.each(["concat","join","slice"],function(n){var t=o[n];m.prototype[n]=function(){return P(this,t.apply(this._wrapped,arguments))}}),m.prototype.value=function(){return this._wrapped},m.prototype.valueOf=m.prototype.toJSON=m.prototype.value,m.prototype.toString=function(){return""+this._wrapped},"function"==typeof define&&define.amd&&define("underscore",[],function(){return m})}).call(this);
//# sourceMappingURL=underscore-min.map;
define('bootstrap',[
    'underscore'
], function () {
    

    var bootstrap = function (model, controller, view, options) {
        if (_.isString(controller))
            controller = require('controller/' + controller + 'Controller');

        if (_.isString(view))
            view = require('views/' + view + 'View');

        var c = new controller(model, options);
        var v = new view(c, options);

        if (_.isFunction(v.loadView)) //TODO implement in all views
            v.loadView();
        c.onViewLoaded();
    };

    return bootstrap;
});

define('service',['require'],function (require) {
    

    var Service = function () {

    };

    Service.get = function (name) {
        if (!$tt._services[name]) {
            var service = require('service/' + name + 'Service');
            $tt._services[name] = new service();
        }

        return $tt._services[name];
    };

    return Service;
});

define('core/dust',[
    'underscore'
], function () {
    

    var helpers = {
        generateUrl: function (chunk, context, bodies, params) {
            if (!params || !params.hasOwnProperty('path'))
                return chunk;

            // ignore full and blob urls
            if (params.path.indexOf('://') > 0 || params.path.indexOf('blob:') == 0)
                return chunk.write(params.path);

            return chunk.write(
                (Routing.getBaseUrl() + '/').replace(/\w+\.php\/$/gi, '') + params.path);
        },
        generateRouteUrl: function (chunk, context, bodies, params) {
            if (!params || !params.hasOwnProperty('name') || !params.hasOwnProperty('str_params'))
                return chunk;

            var str_params = params.str_params.split('|');
            var opt_params = {};

            str_params.forEach(function (element, index, array) {
                var keyContext = element.split(':');
                opt_params[keyContext[0]] = context.get(keyContext[1]);
            });

            return chunk.write(Routing.generate(params.name, opt_params, params.absolute));
        },
        // TODO unused. consider dropping
        sizeOf: function (chunk, context, bodies, params) {
            // pass a dummy chunk object so that @size doesn't write the resulting value,
            // so it doesn't render if bodies.block is present but its context(s) return(s) false
            var result;
            this.size({
                write: function (value) {
                    result = value;
                }
            }, context, bodies, params);
            if (bodies && bodies.block) {
                chunk.render(bodies.block, context.push({key: result}));
            } else {
                chunk = chunk.write(result);
            }
            return chunk;
        },
        isLoggedInUser: function (chunk, context, bodies, params) {
            if (!params || !params.hasOwnProperty('id') || !window.user)
                return chunk;

            var result = params.id == window.user.get('id');
            if (params.inverse)
                result = !result;
            if (bodies) {
                if (result && bodies.block)
                    chunk.render(bodies.block, context);
                if (!result && bodies.else)
                    chunk.render(bodies.else, context);
            } else {
                chunk = chunk.write(result ? 'Yes' : 'No');
            }
            return chunk;
        },
        isNotLoggedInUser: function (chunk, context, bodies, params) {
            params.inverse = true;
            return this.isLoggedInUser(chunk, context, bodies, params);
        },
        isUserOnList: function (chunk, context, bodies, params) {
            if (!params || !params.hasOwnProperty('list') || !params.hasOwnProperty('username') || !window.user)
                return chunk;

            var result = window.user['isUserOn' + params.list + 'List'](params.username);
            if (bodies) {
                if (result && bodies.block)
                    chunk.render(bodies.block, context);
                if (!result && bodies.else)
                    chunk.render(bodies.else, context);
            } else {
                chunk = chunk.write(result ? 'Yes' : 'No');
            }
            return chunk;
        },
        isCurrentUser: function (chunk, context, bodies, params) {
            if (!params || !params.hasOwnProperty('id') || !window.user)
                return chunk;
            var result = params.id == window.user.get('id');
            if (params.inverse)
                result = !result;
            if (bodies) {
                if (result && bodies.block)
                    chunk.render(bodies.block, context);
                if (!result && bodies.else)
                    chunk.render(bodies.else, context);
            } else {
                chunk = chunk.write(result ? 'Yes' : 'No');
            }
            return chunk;
        }
    };

    var filters = {
        nl2br: function (value) {
            return value.replace(/(?:\r\n|\r|\n)/g, '<br />');
        },
        date: function (value) {
            var dateTime = new Date(value);
            if (_.isNaN(dateTime.getTime()))
                return '';

            var amPM = (dateTime.getHours() > 11) ? 'pm' : 'am';
            var hours = dateTime.getHours() % 12;
            var minutes = dateTime.getMinutes();
            var dateString = dateTime.toDateString();

            if (hours == 0)
                hours = 12;
            if (hours < 10)
                hours = '0' + hours;
            if (minutes < 10)
                minutes = '0' + minutes;

            dateString = dateString.substring(dateString.indexOf(' ') + 1);

            return dateString + ' ' + hours + ':' + minutes + ' ' + amPM;
        }
    };

    var Dust = function () {
    };

    Dust.inject = function () {
        _.each(helpers, function (value, key, list) {
            dust.helpers[key] = value;
        });

        _.each(filters, function (value, key, list) {
            dust.filters[key] = value;
        });
    };

    return Dust;
})
;

define('core/helper',[],function () {
    

    var Helper = function () {

    };

    Helper.TAG = "Helper";

    Helper.MEDIA_TYPE_IMAGE = 0;
    Helper.MEDIA_TYPE_VIDEO = 1;
    Helper.MEDIA_TYPE_AUDIO = 2;
    Helper.MEDIA_TYPE_OTHER = 9;

    Helper.isFullscreen = function () {
        return (document.fullscreenElement ||
        document.mozFullScreenElement ||
        document.webkitFullscreenElement ||
        document.msFullscreenElement) ? true : false;
    };

    Helper.toggleFullScreen = function (element) {
        console.log("%s: %s", Helper.TAG, "toggleFullScreen");

        var htmlElelemnt = element[0];
        if (!Helper.isFullscreen()) {
            if (htmlElelemnt.requestFullscreen) {
                htmlElelemnt.requestFullscreen();
            } else if (htmlElelemnt.mozRequestFullScreen) {
                htmlElelemnt.mozRequestFullScreen();
            } else if (htmlElelemnt.webkitRequestFullscreen) {
                htmlElelemnt.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
            } else if (htmlElelemnt.msRequestFullscreen) {
                htmlElelemnt.msRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    };

    Helper.updateProgressBar = function (element, percentComplete) {
        var progressBar = element.find(".progress-bar");
        progressBar.attr("aria-valuenow", percentComplete);
        progressBar.css("width", percentComplete + "%");
        progressBar.html(percentComplete + "%");
    };

    //TODO move to core/dust as filter
    Helper.formatSize = function (size) {
        if (size == -1)
            return size;
        return (Number(size) / 1024 / 1024).toFixed(2) + " MB";
    };

    //TODO move to core/dust as two separate filters
    Helper.getIconForMediaType = function (type) {
        var icon;
        var text;
        switch (type) {
            case Helper.MEDIA_TYPE_IMAGE:
                icon = 'fa-file-photo-o';
                text = 'Image';
                break;
            case Helper.MEDIA_TYPE_VIDEO:
                icon = 'fa-file-video-o';
                text = 'Video';
                break;
            case Helper.MEDIA_TYPE_AUDIO:
                icon = 'fa-file-audio-o';
                text = 'Audio';
                break;
            case Helper.MEDIA_TYPE_OTHER:
                icon = 'fa-file-o';
                text = 'Other';
                break;
        }
        return {icon: icon, text: text};
    };

    Helper.autoSize = function () {
        // make all elements with class 'autosize' expand to fit its contents
        $(".autosize").autosize({append: ''});
    };

    return Helper;
});

define('core/subscriber',['underscore'], function () {
    

    var Subscriber = function () {
        this.subscriptions = {};
    };

    Subscriber.prototype._dispatch = function (event, args) {
        var e = {
            type: event
        };

        if (_.isObject(args) && !_.isArray(args) && !_.isFunction(args)) {
            // add the extra args for this event to the event object
            _.each(args, function (value, key, list) {
                e[key] = value;
            });
        }

        // loop through the callbacks
        _.each(this.subscriptions[event], function (element, index, list) {
            if (_.isFunction(element))
                element.call(this, e);
        }, this);
    };

    Subscriber.prototype.subscribe = function (event, callback) {
        if (!_.has(this.subscriptions, event)) {
            this.subscriptions[event] = [];
        }

        if (!_.contains(this.subscriptions[event], callback)) {
            this.subscriptions[event].push(callback);
        }
    };

    Subscriber.prototype.unsubscribe = function (callback) {
        _.each(this.subscriptions, function (callbacks, key, list) {
            if (_.contains(callbacks, callback)) {
                var cbIndex = _.indexOf(callbacks, callback);
                callbacks.splice(cbIndex, 1);
            }
        });
    };

    return Subscriber;
});

define('factory/contactFactory',[],function () {
    

    var ContactFactory = {};

    ContactFactory.delete = function (userIds, contactList) {
        var deferred = $.Deferred();
        var settings = {
            method: 'DELETE',
            url: Routing.generate('imdc_delete_contact'),
            data: {
                userIds: userIds,
                contactList: contactList
            }
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    return ContactFactory;
});

define('factory/forumFactory',[],function () {
    

    var ForumFactory = {};

    ForumFactory.delete = function (model) {
        var deferred = $.Deferred();
        var settings = {
            method: 'DELETE',
            url: Routing.generate('imdc_delete_forum', {forumId: model.get('id')})
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    return ForumFactory;
});

define('factory/groupFactory',[],function () {
    

    var GroupFactory = {};

    GroupFactory.delete = function (model) {
        var deferred = $.Deferred();
        var settings = {
            method: 'DELETE',
            url: Routing.generate('imdc_delete_user_group', {groupId: model.get('id')})
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    return GroupFactory;
});

define('model/model',[
    'core/subscriber',
    'extra',
    'underscore'
], function (Subscriber) {
    

    var Model = function (data) {
        Subscriber.prototype.constructor.apply(this);

        data = data || {};
        if (!_.isObject(data) || _.isArray(data) || _.isFunction(data)) {
            throw new Error('data must be an object');
        }

        this.data = data;
    };

    Model.extend(Subscriber);

    Model.Event = {
        CHANGE: 'eventChange'
    };

    Model._stringToKeyPath = function (str) {
        return str.split('.');
    };

    Model.prototype._findKeyPath = function (list, keyPath) {
        var path = Model._stringToKeyPath(keyPath);

        while (path.length !== 0) {
            var key = path.shift();
            var index = parseInt(key, 10);
            key = _.isNumber(index) && !_.isNaN(index) ? index : key;
            // _.has and _.contains are ?? under phantomjs
            if ((_.isObject(list) && (_.has(list, key) && list.hasOwnProperty(key))) ||
                (_.isArray(list) && (_.contains(list, key) && (list[key] !== undefined)))) {
                list = list[key];
            } else {
                return undefined;
            }
        }

        return list;
    };

    Model.prototype._setKeyPath = function (list, keyPath, value) {
        var path = Model._stringToKeyPath(keyPath);

        while (path.length > 1) {
            var key = path.shift();
            var index = parseInt(key, 10);
            key = _.isNumber(index) && !_.isNaN(index) ? index : key;
            // _.has and _.contains are ?? under phantomjs
            if ((_.isObject(list) && (!_.has(list, key) && !list.hasOwnProperty(key))) ||
                (_.isArray(list) && (!_.contains(list, key) && (list[key] === undefined))) ||
                (_.isNull(list[key]))) {
                console.log('defining key: ' + key);
                var nextKey = parseInt(path[0], 10); // check the next key to predict type
                list[key] = _.isNumber(nextKey) && !_.isNaN(nextKey) ? [] : {};
            }
            list = list[key];
        }

        list[path.shift()] = value;
    };

    Model.prototype._dispatch = function (event, keyPath, args) {
        args = _.extend(args || {}, {
            keyPath: keyPath || '',
            model: this
        });

        Subscriber.prototype._dispatch.call(this, event, args);
    };

    Model.prototype.get = function (keyPath, defaultValue) {
        var result = this._findKeyPath(this.data, keyPath);
        return typeof result !== 'undefined' ? result : defaultValue;
    };

    Model.prototype.set = function (keyPath, value, doDispatch) {
        doDispatch = typeof doDispatch !== 'undefined' ? doDispatch : true;
        var result = this._findKeyPath(this.data, keyPath);

        this._setKeyPath(this.data, keyPath, value);

        if (doDispatch && result !== value) {
            this._dispatch(Model.Event.CHANGE, keyPath);
        }
    };

    Model.prototype.update = function (data, keyPath) {
        _.each(data, function (value, key, list) {
            var cKeyPath = keyPath ? (keyPath + '.' + key) : key;

            if (_.isObject(value) || _.isArray(value)) {
                console.log('update: ' + cKeyPath);
                this.update(value, cKeyPath);
            } else {
                console.log('set: ' + cKeyPath + ' to:' + value);
                this.set(cKeyPath, value);
            }
        }, this);
    };

    Model.prototype.forceChange = function (keyPath, args) {
        this._dispatch(Model.Event.CHANGE, keyPath, args);
    };

    //TODO move to collection
    Model.prototype.find = function (value, keyPath, collection) {
        return _.findIndex(collection, function (model) {
            return model.get(keyPath) == value;
        });
    };

    return Model;
});

define('model/mediaModel',[
    'model/model',
    'extra'
], function (Model) {
    

    var MediaModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);
    };

    MediaModel.extend(Model);

    return MediaModel;
});

define('factory/mediaFactory',[
    'model/mediaModel',
    'underscore'
], function (MediaModel) {
    

    var MediaFactory = {};

    MediaFactory.list = function (ids) {
        var deferred = $.Deferred();
        var settings = {
            url: Routing.generate('imdc_cget_media'),
            data: {}
        };

        if (ids) {
            settings.data.id = ids.join();
        }

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                data.media.forEach(function (element, index, array) {
                    array[index] = new MediaModel(element);
                });
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    MediaFactory.get = function (model) {
        var deferred = $.Deferred();
        var isModel = _.isObject(model);
        var id = isModel ? model.get('id') : model;
        var settings = {
            url: Routing.generate('imdc_get_media', {mediaId: id})
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (isModel) {
                    model.update(data.media);
                    data.media = model;
                } else {
                    data.media = new MediaModel(data.media);
                }
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    MediaFactory.edit = function (model) {
        var deferred = $.Deferred();
        var settings = {
            method: 'PUT',
            url: Routing.generate('imdc_edit_media', {mediaId: model.get('id')}),
            data: {media: JSON.stringify(model.data)} // TODO add method to model to get json representation of underlying data
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                model.update(data.media);
                data.media = model;
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    MediaFactory.delete = function (model, confirmed) {
        var deferred = $.Deferred();
        var settings = {
            method: 'DELETE',
            url: Routing.generate('imdc_delete_media', {mediaId: model.get('id')}),
            data: {confirm: confirmed || false}
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);

                //TODO this will only make sense if 'confirmed' is false
                //TODO move. should be done at view level
                var data = jqXHR.responseJSON;
                var mediaInUseTexts = [];
                data.in_use.forEach(function(element, index, array) {
                    mediaInUseTexts.push(
                        Translator.trans('filesGateway.deleteMediaInUseConfirmation.' + element)
                    );
                });
                data.confirmText = Translator.trans('filesGateway.deleteMediaInUseConfirmation.finalMessage', {
                    'mediaUsedLocations': mediaInUseTexts.join(', ')
                });

                deferred.reject(data);
            });

        return deferred.promise();
    };

    MediaFactory.trim = function (model, startTime, endTime) {
        var deferred = $.Deferred();
        var settings = {
            method: 'PATCH',
            url: Routing.generate('imdc_trim_media', {mediaId: model.get('id')}),
            data: {
                startTime: startTime,
                endTime: endTime
            }
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                model = new MediaModel(data.media);
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    return MediaFactory;
});

define('factory/messageFactory',[],function () {
    

    var MessageFactory = {};

    MessageFactory.edit = function (model) {
        var deferred = $.Deferred();
        var settings = {
            method: 'POST',
            url: Routing.generate('imdc_message_mark_as_read', {messageid: model.get('id')})
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.wasEdited) {
                    deferred.resolve(data);
                } else {
                    deferred.reject(data);
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    return MessageFactory;
});

define('factory/myFilesFactory',[
    'model/mediaModel'
], function (MediaModel) {
    

    var MyFilesFactory = {};

    MyFilesFactory._prepForFormSubmit = function (form, settings, deferred) {
        settings.type = 'POST';
        settings.contentType = false;
        if (form) {
            settings.data = new FormData(form);
        }
        settings.processData = false;
        settings.xhr = function () {
            var xhr = $.ajaxSettings.xhr();
            xhr.upload.addEventListener('progress', function (e) {
                if (e.lengthComputable) {
                    deferred.notify(Math.floor((e.loaded / e.total) * 100));
                }
            }, false);

            return xhr;
        }
    };

    MyFilesFactory.addRecording = function (params) {
        var formData = new FormData();
        var isFirefox = !!navigator.mozGetUserMedia;
        var deferred = $.Deferred();
        var settings = {
            url: Routing.generate('imdc_myfiles_add_recording')
        };

//        formData.append('isFirefox', isFirefox);
//        if (!isFirefox) {
        formData.append('video-blob', params.video);
//        }
//        formData.append('audio-blob', params.audio);
        formData.append('title', params.title);
        formData.append('isInterpretation', params.isInterpretation);
        formData.append('sourceStartTime', params.sourceStartTime);
        formData.append('sourceId', params.sourceId);

        MyFilesFactory._prepForFormSubmit(null, settings, deferred);
        settings.data = formData;

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.responseCode == 200) {
                    data.media = new MediaModel(data.media);
                    deferred.resolve(data);
                } else {
                    deferred.reject(data);
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject();
            });

        return deferred.promise();
    };

    MyFilesFactory.add = function (form) {
        var deferred = $.Deferred();
        var settings = {
            url: Routing.generate('imdc_myfiles_add')
        };

        MyFilesFactory._prepForFormSubmit(form, settings, deferred);

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.wasUploaded) {
                    data.media = new MediaModel(data.media);
                    deferred.resolve(data);
                } else {
                    console.error(data.error);
                    deferred.reject(data);
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject();
            });

        return deferred.promise();
    };

    return MyFilesFactory;
});

define('model/postModel',[
    'model/model',
    'model/mediaModel',
    'extra'
], function (Model, MediaModel) {
    

    var PostModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);

        this.data.keyPoint = null;

        // replace key/value objects with models for all media
        // TODO consolidate under collection type?
        if (this.data.ordered_media) {
            this.data.ordered_media.forEach(function (element, index, array) {
                array[index] = new MediaModel(element);
            });
        }
    };

    PostModel.extend(Model);

    PostModel.prototype.isNew = function () {
        return String(this.data.id).substr(0, 1) === '0';
    };

    return PostModel;
});

define('factory/postFactory',[
    'model/postModel'
], function (PostModel) {
    

    var PostFactory = {};

    PostFactory._prepForFormSubmit = function (form, settings) {
        settings.contentType = false;
        if (form) {
            settings.data = new FormData(form);
        }
        settings.processData = false;
    };

    PostFactory._newPost = function (model, settings, isPost) {
        var deferred = $.Deferred();

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                // don't use model.update since any new post will not remain in the caller's context
                data.post = new PostModel(data.post);
                data.post.set('form', data.form);
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                if (isPost)
                    model.set('form', jqXHR.responseJSON.form);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    PostFactory.new = function (model) {
        var settings = {
            url: Routing.generate('imdc_new_post', {
                threadId: model.get('parent_thread_id'),
                parentPostId: model.get('parent_post_id') || model.get('id')
            })
        };

        return PostFactory._newPost(model, settings, false);
    };

    PostFactory.post = function (model, form) {
        var settings = {
            method: 'POST',
            url: Routing.generate('imdc_post_post', {
                threadId: model.get('parent_thread_id'),
                parentPostId: model.get('parent_post_id') || model.get('id')
            })
        };

        PostFactory._prepForFormSubmit(form, settings);

        return PostFactory._newPost(model, settings, true);
    };

    PostFactory.get = function (model) {
        var deferred = $.Deferred();
        var settings = {
            url: Routing.generate('imdc_get_post', {postId: model.get('id')})
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                model.update(data.post);
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    PostFactory._editPut = function (model, settings, isPut) {
        var deferred = $.Deferred();

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                model.update(data.post);
                model.set('form', data.form);
                model.set('keyPoint.startTime', model.get('start_time'));
                model.set('keyPoint.endTime', model.get('end_time'));
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                if (isPut)
                    model.set('form', jqXHR.responseJSON.form);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    PostFactory.edit = function (model) {
        var settings = {
            url: Routing.generate('imdc_edit_post', {postId: model.get('id')})
        };

        return PostFactory._editPut(model, settings, false);
    };

    PostFactory.put = function (model, form) {
        var settings = {
            method: 'POST',
            url: Routing.generate('imdc_put_post', {postId: model.get('id')})
        };

        PostFactory._prepForFormSubmit(form, settings);

        return PostFactory._editPut(model, settings, true);
    };

    PostFactory.delete = function (model) {
        var deferred = $.Deferred();
        var settings = {
            method: 'DELETE',
            url: Routing.generate('imdc_delete_post', {postId: model.get('id')})
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    return PostFactory;
});

define('factory/threadFactory',[],function () {
    

    var ThreadFactory = {};

    ThreadFactory.delete = function (model) {
        var deferred = $.Deferred();
        var settings = {
            method: 'DELETE',
            url: Routing.generate('imdc_delete_thread', {threadId: model.get('id')})
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    return ThreadFactory;
});

define('service/keyPointService',['underscore'], function () {
    

    var KeyPointService = function () {
        this.name = 'KeyPoint';
        this.keyPoints = [];
        this.subscriptions = [];
    };

    KeyPointService.Event = {
        ADD: 'eventAdd',
        DURATION: 'eventDuration',
        SELECTION_TIMES: 'eventSelectionTimes',
        HOVER: 'eventHover',
        CLICK: 'eventClick',
        EDIT: 'eventEdit',
        REMOVE: 'eventRemove'
    };

    KeyPointService._kIndex = function (keyPointId) {
        return _.isString(keyPointId) ? keyPointId : 'K' + keyPointId;
    };

    KeyPointService.prototype.register = function (keyPoint) {
        var kIndex = KeyPointService._kIndex(keyPoint.id);
        if (_.isObject(keyPoint))
            this.keyPoints[kIndex] = keyPoint;
    };

    KeyPointService.prototype.deregister = function (keyPointId) {
        var kIndex = KeyPointService._kIndex(keyPointId);
        if (_.has(this.keyPoints, kIndex)) {
            this.keyPoints.splice(kIndex, 1);
        }
    };

    KeyPointService.prototype.subscribe = function (keyPointId, callback) {
        var kIndex = KeyPointService._kIndex(keyPointId);
        if (!_.has(this.subscriptions, kIndex)) {
            this.subscriptions[kIndex] = [];
        }

        if (!_.contains(this.subscriptions[kIndex], callback)) {
            this.subscriptions[kIndex].push(callback);
        }
    };

    KeyPointService.prototype.unsubscribe = function (keyPointId, callback) {
        var kIndex = KeyPointService._kIndex(keyPointId);
        if (_.has(this.subscriptions, kIndex)) {
            var callbacks = this.subscriptions[kIndex];
            if (_.contains(callbacks, callback)) {
                var index = _.indexOf(callbacks, callback);
                callbacks.splice(index, 1);
            }
        }
    };

    KeyPointService.prototype.dispatch = function (keyPointId, event, args) {
        var kIndex = KeyPointService._kIndex(keyPointId);

        if ((kIndex !== 'all' && !_.has(this.keyPoints, kIndex))
            || !_.contains(KeyPointService.Event, event))
            return;

        var subscriptions = kIndex === 'all' ? _.values(this.subscriptions) : [this.subscriptions[kIndex]];

        var invoke = function (element, index, list) {
            var e = {
                type: event
            };

            if (kIndex === 'all') {
                e.keyPoints = this.keyPoints;
            } else {
                e.keyPoint = this.keyPoints[kIndex];
            }

            if (_.isObject(args)) {
                // add the extra args for this event to the event object
                _.each(args, function (value, key, list) {
                    e[key] = value;
                });
            }

            // loop through the callbacks
            _.each(element, function (element2, index, list) {
                if (_.isFunction(element2))
                    element2.call(this, e);
            }, this);
        };

        _.each(subscriptions, invoke, this);

        if (kIndex !== 'all') {
            _.each([this.subscriptions['all']], invoke, this);
        }
    };

    return KeyPointService;
});

/* sockjs-client v1.0.1 | http://sockjs.org | MIT license */
!function(t){if("object"==typeof exports&&"undefined"!=typeof module)module.exports=t();else if("function"==typeof define&&define.amd)define('sockjs',[],t);else{var e;"undefined"!=typeof window?e=window:"undefined"!=typeof global?e=global:"undefined"!=typeof self&&(e=self),e.SockJS=t()}}(function(){var t;return function e(t,n,r){function i(s,a){if(!n[s]){if(!t[s]){var u="function"==typeof require&&require;if(!a&&u)return u(s,!0);if(o)return o(s,!0);var l=new Error("Cannot find module '"+s+"'");throw l.code="MODULE_NOT_FOUND",l}var c=n[s]={exports:{}};t[s][0].call(c.exports,function(e){var n=t[s][1][e];return i(n?n:e)},c,c.exports,e,t,n,r)}return n[s].exports}for(var o="function"==typeof require&&require,s=0;s<r.length;s++)i(r[s]);return i}({1:[function(t,e){(function(n){var r=t("./transport-list");e.exports=t("./main")(r),"_sockjs_onload"in n&&setTimeout(n._sockjs_onload,1)}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{"./main":14,"./transport-list":16}],2:[function(t,e){function n(){i.call(this),this.initEvent("close",!1,!1),this.wasClean=!1,this.code=0,this.reason=""}var r=t("inherits"),i=t("./event");r(n,i),e.exports=n},{"./event":4,inherits:54}],3:[function(t,e){function n(){i.call(this)}var r=t("inherits"),i=t("./eventtarget");r(n,i),n.prototype.removeAllListeners=function(t){t?delete this._listeners[t]:this._listeners={}},n.prototype.once=function(t,e){function n(){r.removeListener(t,n),i||(i=!0,e.apply(this,arguments))}var r=this,i=!1;this.on(t,n)},n.prototype.emit=function(t){var e=this._listeners[t];if(e)for(var n=Array.prototype.slice.call(arguments,1),r=0;r<e.length;r++)e[r].apply(this,n)},n.prototype.on=n.prototype.addListener=i.prototype.addEventListener,n.prototype.removeListener=i.prototype.removeEventListener,e.exports.EventEmitter=n},{"./eventtarget":5,inherits:54}],4:[function(t,e){function n(t){this.type=t}n.prototype.initEvent=function(t,e,n){return this.type=t,this.bubbles=e,this.cancelable=n,this.timeStamp=+new Date,this},n.prototype.stopPropagation=function(){},n.prototype.preventDefault=function(){},n.CAPTURING_PHASE=1,n.AT_TARGET=2,n.BUBBLING_PHASE=3,e.exports=n},{}],5:[function(t,e){function n(){this._listeners={}}n.prototype.addEventListener=function(t,e){t in this._listeners||(this._listeners[t]=[]);var n=this._listeners[t];-1===n.indexOf(e)&&(n=n.concat([e])),this._listeners[t]=n},n.prototype.removeEventListener=function(t,e){var n=this._listeners[t];if(n){var r=n.indexOf(e);return-1!==r?void(n.length>1?this._listeners[t]=n.slice(0,r).concat(n.slice(r+1)):delete this._listeners[t]):void 0}},n.prototype.dispatchEvent=function(t){var e=t.type,n=Array.prototype.slice.call(arguments,0);if(this["on"+e]&&this["on"+e].apply(this,n),e in this._listeners)for(var r=this._listeners[e],i=0;i<r.length;i++)r[i].apply(this,n)},e.exports=n},{}],6:[function(t,e){function n(t){i.call(this),this.initEvent("message",!1,!1),this.data=t}var r=t("inherits"),i=t("./event");r(n,i),e.exports=n},{"./event":4,inherits:54}],7:[function(t,e){function n(t){this._transport=t,t.on("message",this._transportMessage.bind(this)),t.on("close",this._transportClose.bind(this))}var r=t("json3"),i=t("./utils/iframe");n.prototype._transportClose=function(t,e){i.postMessage("c",r.stringify([t,e]))},n.prototype._transportMessage=function(t){i.postMessage("t",t)},n.prototype._send=function(t){this._transport.send(t)},n.prototype._close=function(){this._transport.close(),this._transport.removeAllListeners()},e.exports=n},{"./utils/iframe":47,json3:55}],8:[function(t,e){var n=t("./utils/url"),r=t("./utils/event"),i=t("json3"),o=t("./facade"),s=t("./info-iframe-receiver"),a=t("./utils/iframe"),u=t("./location");e.exports=function(t,e){var l={};e.forEach(function(t){t.facadeTransport&&(l[t.facadeTransport.transportName]=t.facadeTransport)}),l[s.transportName]=s;var c;t.bootstrap_iframe=function(){var e;a.currentWindowId=u.hash.slice(1);var s=function(r){if(r.source===parent&&("undefined"==typeof c&&(c=r.origin),r.origin===c)){var s;try{s=i.parse(r.data)}catch(f){return}if(s.windowId===a.currentWindowId)switch(s.type){case"s":var h;try{h=i.parse(s.data)}catch(f){break}var d=h[0],p=h[1],v=h[2],m=h[3];if(d!==t.version)throw new Error('Incompatibile SockJS! Main site uses: "'+d+'", the iframe: "'+t.version+'".');if(!n.isOriginEqual(v,u.href)||!n.isOriginEqual(m,u.href))throw new Error("Can't connect to different domain from within an iframe. ("+u.href+", "+v+", "+m+")");e=new o(new l[p](v,m));break;case"m":e._send(s.data);break;case"c":e&&e._close(),e=null}}};r.attachEvent("message",s),a.postMessage("s")}}},{"./facade":7,"./info-iframe-receiver":10,"./location":13,"./utils/event":46,"./utils/iframe":47,"./utils/url":52,debug:void 0,json3:55}],9:[function(t,e){function n(t,e){r.call(this);var n=this,i=+new Date;this.xo=new e("GET",t),this.xo.once("finish",function(t,e){var r,a;if(200===t){if(a=+new Date-i,e)try{r=o.parse(e)}catch(u){}s.isObject(r)||(r={})}n.emit("finish",r,a),n.removeAllListeners()})}var r=t("events").EventEmitter,i=t("inherits"),o=t("json3"),s=t("./utils/object");i(n,r),n.prototype.close=function(){this.removeAllListeners(),this.xo.close()},e.exports=n},{"./utils/object":49,debug:void 0,events:3,inherits:54,json3:55}],10:[function(t,e){function n(t,e){var n=this;i.call(this),this.ir=new a(e,s),this.ir.once("finish",function(t,e){n.ir=null,n.emit("message",o.stringify([t,e]))})}var r=t("inherits"),i=t("events").EventEmitter,o=t("json3"),s=t("./transport/sender/xhr-local"),a=t("./info-ajax");r(n,i),n.transportName="iframe-info-receiver",n.prototype.close=function(){this.ir&&(this.ir.close(),this.ir=null),this.removeAllListeners()},e.exports=n},{"./info-ajax":9,"./transport/sender/xhr-local":37,events:3,inherits:54,json3:55}],11:[function(t,e){(function(n){function r(t,e){var r=this;i.call(this);var o=function(){var n=r.ifr=new u(l.transportName,e,t);n.once("message",function(t){if(t){var e;try{e=s.parse(t)}catch(n){return r.emit("finish"),void r.close()}var i=e[0],o=e[1];r.emit("finish",i,o)}r.close()}),n.once("close",function(){r.emit("finish"),r.close()})};n.document.body?o():a.attachEvent("load",o)}var i=t("events").EventEmitter,o=t("inherits"),s=t("json3"),a=t("./utils/event"),u=t("./transport/iframe"),l=t("./info-iframe-receiver");o(r,i),r.enabled=function(){return u.enabled()},r.prototype.close=function(){this.ifr&&this.ifr.close(),this.removeAllListeners(),this.ifr=null},e.exports=r}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{"./info-iframe-receiver":10,"./transport/iframe":22,"./utils/event":46,debug:void 0,events:3,inherits:54,json3:55}],12:[function(t,e){function n(t,e){var n=this;r.call(this),setTimeout(function(){n.doXhr(t,e)},0)}var r=t("events").EventEmitter,i=t("inherits"),o=t("./utils/url"),s=t("./transport/sender/xdr"),a=t("./transport/sender/xhr-cors"),u=t("./transport/sender/xhr-local"),l=t("./transport/sender/xhr-fake"),c=t("./info-iframe"),f=t("./info-ajax");i(n,r),n._getReceiver=function(t,e,n){return n.sameOrigin?new f(e,u):a.enabled?new f(e,a):s.enabled&&n.sameScheme?new f(e,s):c.enabled()?new c(t,e):new f(e,l)},n.prototype.doXhr=function(t,e){var r=this,i=o.addPath(t,"/info");this.xo=n._getReceiver(t,i,e),this.timeoutRef=setTimeout(function(){r._cleanup(!1),r.emit("finish")},n.timeout),this.xo.once("finish",function(t,e){r._cleanup(!0),r.emit("finish",t,e)})},n.prototype._cleanup=function(t){clearTimeout(this.timeoutRef),this.timeoutRef=null,!t&&this.xo&&this.xo.close(),this.xo=null},n.prototype.close=function(){this.removeAllListeners(),this._cleanup(!1)},n.timeout=8e3,e.exports=n},{"./info-ajax":9,"./info-iframe":11,"./transport/sender/xdr":34,"./transport/sender/xhr-cors":35,"./transport/sender/xhr-fake":36,"./transport/sender/xhr-local":37,"./utils/url":52,debug:void 0,events:3,inherits:54}],13:[function(t,e){(function(t){e.exports=t.location||{origin:"http://localhost:80",protocol:"http",host:"localhost",port:80,href:"http://localhost/",hash:""}}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{}],14:[function(t,e){(function(n){function r(t,e,n){if(!(this instanceof r))return new r(t,e,n);if(arguments.length<1)throw new TypeError("Failed to construct 'SockJS: 1 argument required, but only 0 present");b.call(this),this.readyState=r.CONNECTING,this.extensions="",this.protocol="",n=n||{},n.protocols_whitelist&&m.warn("'protocols_whitelist' is DEPRECATED. Use 'transports' instead."),this._transportsWhitelist=n.transports;var i=n.sessionId||8;if("function"==typeof i)this._generateSessionId=i;else{if("number"!=typeof i)throw new TypeError("If sessionId is used in the options, it needs to be a number or a function.");this._generateSessionId=function(){return l.string(i)}}this._server=n.server||l.numberString(1e3);var o=new s(t);if(!o.host||!o.protocol)throw new SyntaxError("The URL '"+t+"' is invalid");if(o.hash)throw new SyntaxError("The URL must not contain a fragment");if("http:"!==o.protocol&&"https:"!==o.protocol)throw new SyntaxError("The URL's scheme must be either 'http:' or 'https:'. '"+o.protocol+"' is not allowed.");var a="https:"===o.protocol;if("https"===g.protocol&&!a)throw new Error("SecurityError: An insecure SockJS connection may not be initiated from a page loaded over HTTPS");e?Array.isArray(e)||(e=[e]):e=[];var u=e.sort();u.forEach(function(t,e){if(!t)throw new SyntaxError("The protocols entry '"+t+"' is invalid.");if(e<u.length-1&&t===u[e+1])throw new SyntaxError("The protocols entry '"+t+"' is duplicated.")});var c=f.getOrigin(g.href);this._origin=c?c.toLowerCase():null,o.set("pathname",o.pathname.replace(/\/+$/,"")),this.url=o.href,this._urlInfo={nullOrigin:!v.hasDomain(),sameOrigin:f.isOriginEqual(this.url,g.href),sameScheme:f.isSchemeEqual(this.url,g.href)},this._ir=new _(this.url,this._urlInfo),this._ir.once("finish",this._receiveInfo.bind(this))}function i(t){return 1e3===t||t>=3e3&&4999>=t}t("./shims");var o,s=t("url-parse"),a=t("inherits"),u=t("json3"),l=t("./utils/random"),c=t("./utils/escape"),f=t("./utils/url"),h=t("./utils/event"),d=t("./utils/transport"),p=t("./utils/object"),v=t("./utils/browser"),m=t("./utils/log"),y=t("./event/event"),b=t("./event/eventtarget"),g=t("./location"),w=t("./event/close"),x=t("./event/trans-message"),_=t("./info-receiver");a(r,b),r.prototype.close=function(t,e){if(t&&!i(t))throw new Error("InvalidAccessError: Invalid code");if(e&&e.length>123)throw new SyntaxError("reason argument has an invalid length");if(this.readyState!==r.CLOSING&&this.readyState!==r.CLOSED){var n=!0;this._close(t||1e3,e||"Normal closure",n)}},r.prototype.send=function(t){if("string"!=typeof t&&(t=""+t),this.readyState===r.CONNECTING)throw new Error("InvalidStateError: The connection has not been established yet");this.readyState===r.OPEN&&this._transport.send(c.quote(t))},r.version=t("./version"),r.CONNECTING=0,r.OPEN=1,r.CLOSING=2,r.CLOSED=3,r.prototype._receiveInfo=function(t,e){if(this._ir=null,!t)return void this._close(1002,"Cannot connect to server");this._rto=this.countRTO(e),this._transUrl=t.base_url?t.base_url:this.url,t=p.extend(t,this._urlInfo);var n=o.filterToEnabled(this._transportsWhitelist,t);this._transports=n.main,this._connect()},r.prototype._connect=function(){for(var t=this._transports.shift();t;t=this._transports.shift()){if(t.needBody&&(!n.document.body||"undefined"!=typeof n.document.readyState&&"complete"!==n.document.readyState&&"interactive"!==n.document.readyState))return this._transports.unshift(t),void h.attachEvent("load",this._connect.bind(this));var e=this._rto*t.roundTrips||5e3;this._transportTimeoutId=setTimeout(this._transportTimeout.bind(this),e);var r=f.addPath(this._transUrl,"/"+this._server+"/"+this._generateSessionId()),i=new t(r,this._transUrl);return i.on("message",this._transportMessage.bind(this)),i.once("close",this._transportClose.bind(this)),i.transportName=t.transportName,void(this._transport=i)}this._close(2e3,"All transports failed",!1)},r.prototype._transportTimeout=function(){this.readyState===r.CONNECTING&&this._transportClose(2007,"Transport timed out")},r.prototype._transportMessage=function(t){var e,n=this,r=t.slice(0,1),i=t.slice(1);switch(r){case"o":return void this._open();case"h":return void this.dispatchEvent(new y("heartbeat"))}if(i)try{e=u.parse(i)}catch(o){}if("undefined"!=typeof e)switch(r){case"a":Array.isArray(e)&&e.forEach(function(t){n.dispatchEvent(new x(t))});break;case"m":this.dispatchEvent(new x(e));break;case"c":Array.isArray(e)&&2===e.length&&this._close(e[0],e[1],!0)}},r.prototype._transportClose=function(t,e){return this._transport&&(this._transport.removeAllListeners(),this._transport=null,this.transport=null),i(t)||2e3===t||this.readyState!==r.CONNECTING?void this._close(t,e):void this._connect()},r.prototype._open=function(){this.readyState===r.CONNECTING?(this._transportTimeoutId&&(clearTimeout(this._transportTimeoutId),this._transportTimeoutId=null),this.readyState=r.OPEN,this.transport=this._transport.transportName,this.dispatchEvent(new y("open"))):this._close(1006,"Server lost session")},r.prototype._close=function(t,e,n){var i=!1;if(this._ir&&(i=!0,this._ir.close(),this._ir=null),this._transport&&(this._transport.close(),this._transport=null,this.transport=null),this.readyState===r.CLOSED)throw new Error("InvalidStateError: SockJS has already been closed");this.readyState=r.CLOSING,setTimeout(function(){this.readyState=r.CLOSED,i&&this.dispatchEvent(new y("error"));var o=new w("close");o.wasClean=n||!1,o.code=t||1e3,o.reason=e,this.dispatchEvent(o),this.onmessage=this.onclose=this.onerror=null}.bind(this),0)},r.prototype.countRTO=function(t){return t>100?4*t:300+t},e.exports=function(e){return o=d(e),t("./iframe-bootstrap")(r,e),r}}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{"./event/close":2,"./event/event":4,"./event/eventtarget":5,"./event/trans-message":6,"./iframe-bootstrap":8,"./info-receiver":12,"./location":13,"./shims":15,"./utils/browser":44,"./utils/escape":45,"./utils/event":46,"./utils/log":48,"./utils/object":49,"./utils/random":50,"./utils/transport":51,"./utils/url":52,"./version":53,debug:void 0,inherits:54,json3:55,"url-parse":56}],15:[function(){function t(t){var e=+t;return e!==e?e=0:0!==e&&e!==1/0&&e!==-(1/0)&&(e=(e>0||-1)*Math.floor(Math.abs(e))),e}function e(t){return t>>>0}function n(){}var r,i=Array.prototype,o=Object.prototype,s=Function.prototype,a=String.prototype,u=i.slice,l=o.toString,c=function(t){return"[object Function]"===o.toString.call(t)},f=function(t){return"[object Array]"===l.call(t)},h=function(t){return"[object String]"===l.call(t)},d=Object.defineProperty&&function(){try{return Object.defineProperty({},"x",{}),!0}catch(t){return!1}}();r=d?function(t,e,n,r){!r&&e in t||Object.defineProperty(t,e,{configurable:!0,enumerable:!1,writable:!0,value:n})}:function(t,e,n,r){!r&&e in t||(t[e]=n)};var p=function(t,e,n){for(var i in e)o.hasOwnProperty.call(e,i)&&r(t,i,e[i],n)},v=function(t){if(null==t)throw new TypeError("can't convert "+t+" to object");return Object(t)};p(s,{bind:function(t){var e=this;if(!c(e))throw new TypeError("Function.prototype.bind called on incompatible "+e);for(var r=u.call(arguments,1),i=function(){if(this instanceof l){var n=e.apply(this,r.concat(u.call(arguments)));return Object(n)===n?n:this}return e.apply(t,r.concat(u.call(arguments)))},o=Math.max(0,e.length-r.length),s=[],a=0;o>a;a++)s.push("$"+a);var l=Function("binder","return function ("+s.join(",")+"){ return binder.apply(this, arguments); }")(i);return e.prototype&&(n.prototype=e.prototype,l.prototype=new n,n.prototype=null),l}}),p(Array,{isArray:f});var m=Object("a"),y="a"!==m[0]||!(0 in m),b=function(t){var e=!0,n=!0;return t&&(t.call("foo",function(t,n,r){"object"!=typeof r&&(e=!1)}),t.call([1],function(){n="string"==typeof this},"x")),!!t&&e&&n};p(i,{forEach:function(t){var e=v(this),n=y&&h(this)?this.split(""):e,r=arguments[1],i=-1,o=n.length>>>0;if(!c(t))throw new TypeError;for(;++i<o;)i in n&&t.call(r,n[i],i,e)}},!b(i.forEach));var g=Array.prototype.indexOf&&-1!==[0,1].indexOf(1,2);p(i,{indexOf:function(e){var n=y&&h(this)?this.split(""):v(this),r=n.length>>>0;if(!r)return-1;var i=0;for(arguments.length>1&&(i=t(arguments[1])),i=i>=0?i:Math.max(0,r+i);r>i;i++)if(i in n&&n[i]===e)return i;return-1}},g);var w=a.split;2!=="ab".split(/(?:ab)*/).length||4!==".".split(/(.?)(.?)/).length||"t"==="tesst".split(/(s)*/)[1]||4!=="test".split(/(?:)/,-1).length||"".split(/.?/).length||".".split(/()()/).length>1?!function(){var t=void 0===/()??/.exec("")[1];a.split=function(n,r){var o=this;if(void 0===n&&0===r)return[];if("[object RegExp]"!==l.call(n))return w.call(this,n,r);var s,a,u,c,f=[],h=(n.ignoreCase?"i":"")+(n.multiline?"m":"")+(n.extended?"x":"")+(n.sticky?"y":""),d=0;for(n=new RegExp(n.source,h+"g"),o+="",t||(s=new RegExp("^"+n.source+"$(?!\\s)",h)),r=void 0===r?-1>>>0:e(r);(a=n.exec(o))&&(u=a.index+a[0].length,!(u>d&&(f.push(o.slice(d,a.index)),!t&&a.length>1&&a[0].replace(s,function(){for(var t=1;t<arguments.length-2;t++)void 0===arguments[t]&&(a[t]=void 0)}),a.length>1&&a.index<o.length&&i.push.apply(f,a.slice(1)),c=a[0].length,d=u,f.length>=r)));)n.lastIndex===a.index&&n.lastIndex++;return d===o.length?(c||!n.test(""))&&f.push(""):f.push(o.slice(d)),f.length>r?f.slice(0,r):f}}():"0".split(void 0,0).length&&(a.split=function(t,e){return void 0===t&&0===e?[]:w.call(this,t,e)});var x="	\n\f\r   ᠎             　\u2028\u2029﻿",_="​",E="["+x+"]",j=new RegExp("^"+E+E+"*"),T=new RegExp(E+E+"*$"),S=a.trim&&(x.trim()||!_.trim());p(a,{trim:function(){if(void 0===this||null===this)throw new TypeError("can't convert "+this+" to object");return String(this).replace(j,"").replace(T,"")}},S);var O=a.substr,C="".substr&&"b"!=="0b".substr(-1);p(a,{substr:function(t,e){return O.call(this,0>t&&(t=this.length+t)<0?0:t,e)}},C)},{}],16:[function(t,e){e.exports=[t("./transport/websocket"),t("./transport/xhr-streaming"),t("./transport/xdr-streaming"),t("./transport/eventsource"),t("./transport/lib/iframe-wrap")(t("./transport/eventsource")),t("./transport/htmlfile"),t("./transport/lib/iframe-wrap")(t("./transport/htmlfile")),t("./transport/xhr-polling"),t("./transport/xdr-polling"),t("./transport/lib/iframe-wrap")(t("./transport/xhr-polling")),t("./transport/jsonp-polling")]},{"./transport/eventsource":20,"./transport/htmlfile":21,"./transport/jsonp-polling":23,"./transport/lib/iframe-wrap":26,"./transport/websocket":38,"./transport/xdr-polling":39,"./transport/xdr-streaming":40,"./transport/xhr-polling":41,"./transport/xhr-streaming":42}],17:[function(t,e){(function(n){function r(t,e,n,r){var o=this;i.call(this),setTimeout(function(){o._start(t,e,n,r)},0)}var i=t("events").EventEmitter,o=t("inherits"),s=t("../../utils/event"),a=t("../../utils/url"),u=n.XMLHttpRequest;o(r,i),r.prototype._start=function(t,e,n,i){var o=this;try{this.xhr=new u}catch(l){}if(!this.xhr)return this.emit("finish",0,"no xhr support"),void this._cleanup();e=a.addQuery(e,"t="+ +new Date),this.unloadRef=s.unloadAdd(function(){o._cleanup(!0)});try{this.xhr.open(t,e,!0),this.timeout&&"timeout"in this.xhr&&(this.xhr.timeout=this.timeout,this.xhr.ontimeout=function(){o.emit("finish",0,""),o._cleanup(!1)})}catch(c){return this.emit("finish",0,""),void this._cleanup(!1)}if(i&&i.noCredentials||!r.supportsCORS||(this.xhr.withCredentials="true"),i&&i.headers)for(var f in i.headers)this.xhr.setRequestHeader(f,i.headers[f]);this.xhr.onreadystatechange=function(){if(o.xhr){var t,e,n=o.xhr;switch(n.readyState){case 3:try{e=n.status,t=n.responseText}catch(r){}1223===e&&(e=204),200===e&&t&&t.length>0&&o.emit("chunk",e,t);break;case 4:e=n.status,1223===e&&(e=204),(12005===e||12029===e)&&(e=0),o.emit("finish",e,n.responseText),o._cleanup(!1)}}};try{o.xhr.send(n)}catch(c){o.emit("finish",0,""),o._cleanup(!1)}},r.prototype._cleanup=function(t){if(this.xhr){if(this.removeAllListeners(),s.unloadDel(this.unloadRef),this.xhr.onreadystatechange=function(){},this.xhr.ontimeout&&(this.xhr.ontimeout=null),t)try{this.xhr.abort()}catch(e){}this.unloadRef=this.xhr=null}},r.prototype.close=function(){this._cleanup(!0)},r.enabled=!!u;var l=["Active"].concat("Object").join("X");!r.enabled&&l in n&&(u=function(){try{return new n[l]("Microsoft.XMLHTTP")}catch(t){return null}},r.enabled=!!new u);var c=!1;try{c="withCredentials"in new u}catch(f){}r.supportsCORS=c,e.exports=r}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{"../../utils/event":46,"../../utils/url":52,debug:void 0,events:3,inherits:54}],18:[function(t,e){(function(t){e.exports=t.EventSource}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{}],19:[function(t,e){(function(t){e.exports=t.WebSocket||t.MozWebSocket}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{}],20:[function(t,e){function n(t){if(!n.enabled())throw new Error("Transport created when disabled");i.call(this,t,"/eventsource",o,s)}var r=t("inherits"),i=t("./lib/ajax-based"),o=t("./receiver/eventsource"),s=t("./sender/xhr-cors"),a=t("eventsource");r(n,i),n.enabled=function(){return!!a},n.transportName="eventsource",n.roundTrips=2,e.exports=n},{"./lib/ajax-based":24,"./receiver/eventsource":29,"./sender/xhr-cors":35,eventsource:18,inherits:54}],21:[function(t,e){function n(t){if(!i.enabled)throw new Error("Transport created when disabled");s.call(this,t,"/htmlfile",i,o)}var r=t("inherits"),i=t("./receiver/htmlfile"),o=t("./sender/xhr-local"),s=t("./lib/ajax-based");r(n,s),n.enabled=function(t){return i.enabled&&t.sameOrigin},n.transportName="htmlfile",n.roundTrips=2,e.exports=n},{"./lib/ajax-based":24,"./receiver/htmlfile":30,"./sender/xhr-local":37,inherits:54}],22:[function(t,e){function n(t,e,r){if(!n.enabled())throw new Error("Transport created when disabled");o.call(this);var i=this;this.origin=a.getOrigin(r),this.baseUrl=r,this.transUrl=e,this.transport=t,this.windowId=c.string(8);var s=a.addPath(r,"/iframe.html")+"#"+this.windowId;this.iframeObj=u.createIframe(s,function(t){i.emit("close",1006,"Unable to load an iframe ("+t+")"),i.close()}),this.onmessageCallback=this._message.bind(this),l.attachEvent("message",this.onmessageCallback)}var r=t("inherits"),i=t("json3"),o=t("events").EventEmitter,s=t("../version"),a=t("../utils/url"),u=t("../utils/iframe"),l=t("../utils/event"),c=t("../utils/random");r(n,o),n.prototype.close=function(){if(this.removeAllListeners(),this.iframeObj){l.detachEvent("message",this.onmessageCallback);try{this.postMessage("c")}catch(t){}this.iframeObj.cleanup(),this.iframeObj=null,this.onmessageCallback=this.iframeObj=null}},n.prototype._message=function(t){if(a.isOriginEqual(t.origin,this.origin)){var e;try{e=i.parse(t.data)}catch(n){return}if(e.windowId===this.windowId)switch(e.type){case"s":this.iframeObj.loaded(),this.postMessage("s",i.stringify([s,this.transport,this.transUrl,this.baseUrl]));break;case"t":this.emit("message",e.data);break;case"c":var r;try{r=i.parse(e.data)}catch(n){return}this.emit("close",r[0],r[1]),this.close()}}},n.prototype.postMessage=function(t,e){this.iframeObj.post(i.stringify({windowId:this.windowId,type:t,data:e||""}),this.origin)},n.prototype.send=function(t){this.postMessage("m",t)},n.enabled=function(){return u.iframeEnabled},n.transportName="iframe",n.roundTrips=2,e.exports=n},{"../utils/event":46,"../utils/iframe":47,"../utils/random":50,"../utils/url":52,"../version":53,debug:void 0,events:3,inherits:54,json3:55}],23:[function(t,e){(function(n){function r(t){if(!r.enabled())throw new Error("Transport created when disabled");o.call(this,t,"/jsonp",a,s)}var i=t("inherits"),o=t("./lib/sender-receiver"),s=t("./receiver/jsonp"),a=t("./sender/jsonp");i(r,o),r.enabled=function(){return!!n.document},r.transportName="jsonp-polling",r.roundTrips=1,r.needBody=!0,e.exports=r}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{"./lib/sender-receiver":28,"./receiver/jsonp":31,"./sender/jsonp":33,inherits:54}],24:[function(t,e){function n(t){return function(e,n,r){var i={};"string"==typeof n&&(i.headers={"Content-type":"text/plain"});var s=o.addPath(e,"/xhr_send"),a=new t("POST",s,n,i);return a.once("finish",function(t){return a=null,200!==t&&204!==t?r(new Error("http status "+t)):void r()}),function(){a.close(),a=null;var t=new Error("Aborted");t.code=1e3,r(t)}}}function r(t,e,r,i){s.call(this,t,e,n(i),r,i)}var i=t("inherits"),o=t("../../utils/url"),s=t("./sender-receiver");i(r,s),e.exports=r},{"../../utils/url":52,"./sender-receiver":28,debug:void 0,inherits:54}],25:[function(t,e){function n(t,e){i.call(this),this.sendBuffer=[],this.sender=e,this.url=t}var r=t("inherits"),i=t("events").EventEmitter;r(n,i),n.prototype.send=function(t){this.sendBuffer.push(t),this.sendStop||this.sendSchedule()},n.prototype.sendScheduleWait=function(){var t,e=this;this.sendStop=function(){e.sendStop=null,clearTimeout(t)},t=setTimeout(function(){e.sendStop=null,e.sendSchedule()},25)},n.prototype.sendSchedule=function(){var t=this;if(this.sendBuffer.length>0){var e="["+this.sendBuffer.join(",")+"]";this.sendStop=this.sender(this.url,e,function(e){t.sendStop=null,e?(t.emit("close",e.code||1006,"Sending error: "+e),t._cleanup()):t.sendScheduleWait()}),this.sendBuffer=[]}},n.prototype._cleanup=function(){this.removeAllListeners()},n.prototype.stop=function(){this._cleanup(),this.sendStop&&(this.sendStop(),this.sendStop=null)},e.exports=n},{debug:void 0,events:3,inherits:54}],26:[function(t,e){(function(n){var r=t("inherits"),i=t("../iframe"),o=t("../../utils/object");e.exports=function(t){function e(e,n){i.call(this,t.transportName,e,n)}return r(e,i),e.enabled=function(e,r){if(!n.document)return!1;var s=o.extend({},r);return s.sameOrigin=!0,t.enabled(s)&&i.enabled()},e.transportName="iframe-"+t.transportName,e.needBody=!0,e.roundTrips=i.roundTrips+t.roundTrips-1,e.facadeTransport=t,e}}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{"../../utils/object":49,"../iframe":22,inherits:54}],27:[function(t,e){function n(t,e,n){i.call(this),this.Receiver=t,this.receiveUrl=e,this.AjaxObject=n,this._scheduleReceiver()}var r=t("inherits"),i=t("events").EventEmitter;r(n,i),n.prototype._scheduleReceiver=function(){var t=this,e=this.poll=new this.Receiver(this.receiveUrl,this.AjaxObject);e.on("message",function(e){t.emit("message",e)}),e.once("close",function(n,r){t.poll=e=null,t.pollIsClosing||("network"===r?t._scheduleReceiver():(t.emit("close",n||1006,r),t.removeAllListeners()))})},n.prototype.abort=function(){this.removeAllListeners(),this.pollIsClosing=!0,this.poll&&this.poll.abort()},e.exports=n},{debug:void 0,events:3,inherits:54}],28:[function(t,e){function n(t,e,n,r,a){var u=i.addPath(t,e),l=this;o.call(this,t,n),this.poll=new s(r,u,a),this.poll.on("message",function(t){l.emit("message",t)}),this.poll.once("close",function(t,e){l.poll=null,l.emit("close",t,e),l.close()})}var r=t("inherits"),i=t("../../utils/url"),o=t("./buffered-sender"),s=t("./polling");r(n,o),n.prototype.close=function(){this.removeAllListeners(),this.poll&&(this.poll.abort(),this.poll=null),this.stop()},e.exports=n},{"../../utils/url":52,"./buffered-sender":25,"./polling":27,debug:void 0,inherits:54}],29:[function(t,e){function n(t){i.call(this);var e=this,n=this.es=new o(t);n.onmessage=function(t){e.emit("message",decodeURI(t.data))},n.onerror=function(t){var r=2!==n.readyState?"network":"permanent";e._cleanup(),e._close(r)}}var r=t("inherits"),i=t("events").EventEmitter,o=t("eventsource");r(n,i),n.prototype.abort=function(){this._cleanup(),this._close("user")},n.prototype._cleanup=function(){var t=this.es;t&&(t.onmessage=t.onerror=null,t.close(),this.es=null)},n.prototype._close=function(t){var e=this;setTimeout(function(){e.emit("close",null,t),e.removeAllListeners()},200)},e.exports=n},{debug:void 0,events:3,eventsource:18,inherits:54}],30:[function(t,e){(function(n){function r(t){a.call(this);var e=this;o.polluteGlobalNamespace(),this.id="a"+u.string(6),t=s.addQuery(t,"c="+decodeURIComponent(o.WPrefix+"."+this.id));var i=r.htmlfileEnabled?o.createHtmlfile:o.createIframe;n[o.WPrefix][this.id]={start:function(){e.iframeObj.loaded()},message:function(t){e.emit("message",t)},stop:function(){e._cleanup(),e._close("network")}},this.iframeObj=i(t,function(){e._cleanup(),e._close("permanent")})}var i=t("inherits"),o=t("../../utils/iframe"),s=t("../../utils/url"),a=t("events").EventEmitter,u=t("../../utils/random");i(r,a),r.prototype.abort=function(){this._cleanup(),this._close("user")},r.prototype._cleanup=function(){this.iframeObj&&(this.iframeObj.cleanup(),this.iframeObj=null),delete n[o.WPrefix][this.id]},r.prototype._close=function(t){this.emit("close",null,t),this.removeAllListeners()},r.htmlfileEnabled=!1;var l=["Active"].concat("Object").join("X");if(l in n)try{r.htmlfileEnabled=!!new n[l]("htmlfile")}catch(c){}r.enabled=r.htmlfileEnabled||o.iframeEnabled,e.exports=r}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{"../../utils/iframe":47,"../../utils/random":50,"../../utils/url":52,debug:void 0,events:3,inherits:54}],31:[function(t,e){(function(n){function r(t){var e=this;l.call(this),i.polluteGlobalNamespace(),this.id="a"+o.string(6);var s=a.addQuery(t,"c="+encodeURIComponent(i.WPrefix+"."+this.id));n[i.WPrefix][this.id]=this._callback.bind(this),this._createScript(s),this.timeoutId=setTimeout(function(){e._abort(new Error("JSONP script loaded abnormally (timeout)"))},r.timeout)}var i=t("../../utils/iframe"),o=t("../../utils/random"),s=t("../../utils/browser"),a=t("../../utils/url"),u=t("inherits"),l=t("events").EventEmitter;u(r,l),r.prototype.abort=function(){if(n[i.WPrefix][this.id]){var t=new Error("JSONP user aborted read");t.code=1e3,this._abort(t)}},r.timeout=35e3,r.scriptErrorTimeout=1e3,r.prototype._callback=function(t){this._cleanup(),this.aborting||(t&&this.emit("message",t),this.emit("close",null,"network"),this.removeAllListeners())},r.prototype._abort=function(t){this._cleanup(),this.aborting=!0,this.emit("close",t.code,t.message),this.removeAllListeners()},r.prototype._cleanup=function(){if(clearTimeout(this.timeoutId),this.script2&&(this.script2.parentNode.removeChild(this.script2),this.script2=null),this.script){var t=this.script;t.parentNode.removeChild(t),t.onreadystatechange=t.onerror=t.onload=t.onclick=null,this.script=null}delete n[i.WPrefix][this.id]},r.prototype._scriptError=function(){var t=this;this.errorTimer||(this.errorTimer=setTimeout(function(){t.loadedOkay||t._abort(new Error("JSONP script loaded abnormally (onerror)"))},r.scriptErrorTimeout))},r.prototype._createScript=function(t){var e,r=this,i=this.script=n.document.createElement("script");if(i.id="a"+o.string(8),i.src=t,i.type="text/javascript",i.charset="UTF-8",i.onerror=this._scriptError.bind(this),i.onload=function(){r._abort(new Error("JSONP script loaded abnormally (onload)"))},i.onreadystatechange=function(){if(/loaded|closed/.test(i.readyState)){if(i&&i.htmlFor&&i.onclick){r.loadedOkay=!0;try{i.onclick()}catch(t){}}i&&r._abort(new Error("JSONP script loaded abnormally (onreadystatechange)"))}},"undefined"==typeof i.async&&n.document.attachEvent)if(s.isOpera())e=this.script2=n.document.createElement("script"),e.text="try{var a = document.getElementById('"+i.id+"'); if(a)a.onerror();}catch(x){};",i.async=e.async=!1;
else{try{i.htmlFor=i.id,i.event="onclick"}catch(a){}i.async=!0}"undefined"!=typeof i.async&&(i.async=!0);var u=n.document.getElementsByTagName("head")[0];u.insertBefore(i,u.firstChild),e&&u.insertBefore(e,u.firstChild)},e.exports=r}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{"../../utils/browser":44,"../../utils/iframe":47,"../../utils/random":50,"../../utils/url":52,debug:void 0,events:3,inherits:54}],32:[function(t,e){function n(t,e){i.call(this);var n=this;this.bufferPosition=0,this.xo=new e("POST",t,null),this.xo.on("chunk",this._chunkHandler.bind(this)),this.xo.once("finish",function(t,e){n._chunkHandler(t,e),n.xo=null;var r=200===t?"network":"permanent";n.emit("close",null,r),n._cleanup()})}var r=t("inherits"),i=t("events").EventEmitter;r(n,i),n.prototype._chunkHandler=function(t,e){if(200===t&&e)for(var n=-1;;this.bufferPosition+=n+1){var r=e.slice(this.bufferPosition);if(n=r.indexOf("\n"),-1===n)break;var i=r.slice(0,n);i&&this.emit("message",i)}},n.prototype._cleanup=function(){this.removeAllListeners()},n.prototype.abort=function(){this.xo&&(this.xo.close(),this.emit("close",null,"user"),this.xo=null),this._cleanup()},e.exports=n},{debug:void 0,events:3,inherits:54}],33:[function(t,e){(function(n){function r(t){try{return n.document.createElement('<iframe name="'+t+'">')}catch(e){var r=n.document.createElement("iframe");return r.name=t,r}}function i(){o=n.document.createElement("form"),o.style.display="none",o.style.position="absolute",o.method="POST",o.enctype="application/x-www-form-urlencoded",o.acceptCharset="UTF-8",s=n.document.createElement("textarea"),s.name="d",o.appendChild(s),n.document.body.appendChild(o)}var o,s,a=t("../../utils/random"),u=t("../../utils/url");e.exports=function(t,e,n){o||i();var l="a"+a.string(8);o.target=l,o.action=u.addQuery(u.addPath(t,"/jsonp_send"),"i="+l);var c=r(l);c.id=l,c.style.display="none",o.appendChild(c);try{s.value=e}catch(f){}o.submit();var h=function(t){c.onerror&&(c.onreadystatechange=c.onerror=c.onload=null,setTimeout(function(){c.parentNode.removeChild(c),c=null},500),s.value="",n(t))};return c.onerror=function(){h()},c.onload=function(){h()},c.onreadystatechange=function(t){"complete"===c.readyState&&h()},function(){h(new Error("Aborted"))}}}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{"../../utils/random":50,"../../utils/url":52,debug:void 0}],34:[function(t,e){(function(n){function r(t,e,n){var r=this;i.call(this),setTimeout(function(){r._start(t,e,n)},0)}var i=t("events").EventEmitter,o=t("inherits"),s=t("../../utils/event"),a=t("../../utils/browser"),u=t("../../utils/url");o(r,i),r.prototype._start=function(t,e,r){var i=this,o=new n.XDomainRequest;e=u.addQuery(e,"t="+ +new Date),o.onerror=function(){i._error()},o.ontimeout=function(){i._error()},o.onprogress=function(){i.emit("chunk",200,o.responseText)},o.onload=function(){i.emit("finish",200,o.responseText),i._cleanup(!1)},this.xdr=o,this.unloadRef=s.unloadAdd(function(){i._cleanup(!0)});try{this.xdr.open(t,e),this.timeout&&(this.xdr.timeout=this.timeout),this.xdr.send(r)}catch(a){this._error()}},r.prototype._error=function(){this.emit("finish",0,""),this._cleanup(!1)},r.prototype._cleanup=function(t){if(this.xdr){if(this.removeAllListeners(),s.unloadDel(this.unloadRef),this.xdr.ontimeout=this.xdr.onerror=this.xdr.onprogress=this.xdr.onload=null,t)try{this.xdr.abort()}catch(e){}this.unloadRef=this.xdr=null}},r.prototype.close=function(){this._cleanup(!0)},r.enabled=!(!n.XDomainRequest||!a.hasDomain()),e.exports=r}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{"../../utils/browser":44,"../../utils/event":46,"../../utils/url":52,debug:void 0,events:3,inherits:54}],35:[function(t,e){function n(t,e,n,r){i.call(this,t,e,n,r)}var r=t("inherits"),i=t("../driver/xhr");r(n,i),n.enabled=i.enabled&&i.supportsCORS,e.exports=n},{"../driver/xhr":17,inherits:54}],36:[function(t,e){function n(){var t=this;r.call(this),this.to=setTimeout(function(){t.emit("finish",200,"{}")},n.timeout)}var r=t("events").EventEmitter,i=t("inherits");i(n,r),n.prototype.close=function(){clearTimeout(this.to)},n.timeout=2e3,e.exports=n},{events:3,inherits:54}],37:[function(t,e){function n(t,e,n){i.call(this,t,e,n,{noCredentials:!0})}var r=t("inherits"),i=t("../driver/xhr");r(n,i),n.enabled=i.enabled,e.exports=n},{"../driver/xhr":17,inherits:54}],38:[function(t,e){function n(t){if(!n.enabled())throw new Error("Transport created when disabled");s.call(this);var e=this,o=i.addPath(t,"/websocket");o="https"===o.slice(0,5)?"wss"+o.slice(5):"ws"+o.slice(4),this.url=o,this.ws=new a(this.url),this.ws.onmessage=function(t){e.emit("message",t.data)},this.unloadRef=r.unloadAdd(function(){e.ws.close()}),this.ws.onclose=function(t){e.emit("close",t.code,t.reason),e._cleanup()},this.ws.onerror=function(t){e.emit("close",1006,"WebSocket connection broken"),e._cleanup()}}var r=t("../utils/event"),i=t("../utils/url"),o=t("inherits"),s=t("events").EventEmitter,a=t("./driver/websocket");o(n,s),n.prototype.send=function(t){var e="["+t+"]";this.ws.send(e)},n.prototype.close=function(){this.ws&&this.ws.close(),this._cleanup()},n.prototype._cleanup=function(){var t=this.ws;t&&(t.onmessage=t.onclose=t.onerror=null),r.unloadDel(this.unloadRef),this.unloadRef=this.ws=null,this.removeAllListeners()},n.enabled=function(){return!!a},n.transportName="websocket",n.roundTrips=2,e.exports=n},{"../utils/event":46,"../utils/url":52,"./driver/websocket":19,debug:void 0,events:3,inherits:54}],39:[function(t,e){function n(t){if(!a.enabled)throw new Error("Transport created when disabled");i.call(this,t,"/xhr",s,a)}var r=t("inherits"),i=t("./lib/ajax-based"),o=t("./xdr-streaming"),s=t("./receiver/xhr"),a=t("./sender/xdr");r(n,i),n.enabled=o.enabled,n.transportName="xdr-polling",n.roundTrips=2,e.exports=n},{"./lib/ajax-based":24,"./receiver/xhr":32,"./sender/xdr":34,"./xdr-streaming":40,inherits:54}],40:[function(t,e){function n(t){if(!s.enabled)throw new Error("Transport created when disabled");i.call(this,t,"/xhr_streaming",o,s)}var r=t("inherits"),i=t("./lib/ajax-based"),o=t("./receiver/xhr"),s=t("./sender/xdr");r(n,i),n.enabled=function(t){return t.cookie_needed||t.nullOrigin?!1:s.enabled&&t.sameScheme},n.transportName="xdr-streaming",n.roundTrips=2,e.exports=n},{"./lib/ajax-based":24,"./receiver/xhr":32,"./sender/xdr":34,inherits:54}],41:[function(t,e){function n(t){if(!a.enabled&&!s.enabled)throw new Error("Transport created when disabled");i.call(this,t,"/xhr",o,s)}var r=t("inherits"),i=t("./lib/ajax-based"),o=t("./receiver/xhr"),s=t("./sender/xhr-cors"),a=t("./sender/xhr-local");r(n,i),n.enabled=function(t){return t.nullOrigin?!1:a.enabled&&t.sameOrigin?!0:s.enabled},n.transportName="xhr-polling",n.roundTrips=2,e.exports=n},{"./lib/ajax-based":24,"./receiver/xhr":32,"./sender/xhr-cors":35,"./sender/xhr-local":37,inherits:54}],42:[function(t,e){(function(n){function r(t){if(!u.enabled&&!a.enabled)throw new Error("Transport created when disabled");o.call(this,t,"/xhr_streaming",s,a)}var i=t("inherits"),o=t("./lib/ajax-based"),s=t("./receiver/xhr"),a=t("./sender/xhr-cors"),u=t("./sender/xhr-local"),l=t("../utils/browser");i(r,o),r.enabled=function(t){return t.nullOrigin?!1:l.isOpera()?!1:a.enabled},r.transportName="xhr-streaming",r.roundTrips=2,r.needBody=!!n.document,e.exports=r}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{"../utils/browser":44,"./lib/ajax-based":24,"./receiver/xhr":32,"./sender/xhr-cors":35,"./sender/xhr-local":37,inherits:54}],43:[function(t,e){(function(t){e.exports.randomBytes=t.crypto&&t.crypto.getRandomValues?function(e){var n=new Uint8Array(e);return t.crypto.getRandomValues(n),n}:function(t){for(var e=new Array(t),n=0;t>n;n++)e[n]=Math.floor(256*Math.random());return e}}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{}],44:[function(t,e){(function(t){e.exports={isOpera:function(){return t.navigator&&/opera/i.test(t.navigator.userAgent)},isKonqueror:function(){return t.navigator&&/konqueror/i.test(t.navigator.userAgent)},hasDomain:function(){if(!t.document)return!0;try{return!!t.document.domain}catch(e){return!1}}}}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{}],45:[function(t,e){var n,r=t("json3"),i=/[\x00-\x1f\ud800-\udfff\ufffe\uffff\u0300-\u0333\u033d-\u0346\u034a-\u034c\u0350-\u0352\u0357-\u0358\u035c-\u0362\u0374\u037e\u0387\u0591-\u05af\u05c4\u0610-\u0617\u0653-\u0654\u0657-\u065b\u065d-\u065e\u06df-\u06e2\u06eb-\u06ec\u0730\u0732-\u0733\u0735-\u0736\u073a\u073d\u073f-\u0741\u0743\u0745\u0747\u07eb-\u07f1\u0951\u0958-\u095f\u09dc-\u09dd\u09df\u0a33\u0a36\u0a59-\u0a5b\u0a5e\u0b5c-\u0b5d\u0e38-\u0e39\u0f43\u0f4d\u0f52\u0f57\u0f5c\u0f69\u0f72-\u0f76\u0f78\u0f80-\u0f83\u0f93\u0f9d\u0fa2\u0fa7\u0fac\u0fb9\u1939-\u193a\u1a17\u1b6b\u1cda-\u1cdb\u1dc0-\u1dcf\u1dfc\u1dfe\u1f71\u1f73\u1f75\u1f77\u1f79\u1f7b\u1f7d\u1fbb\u1fbe\u1fc9\u1fcb\u1fd3\u1fdb\u1fe3\u1feb\u1fee-\u1fef\u1ff9\u1ffb\u1ffd\u2000-\u2001\u20d0-\u20d1\u20d4-\u20d7\u20e7-\u20e9\u2126\u212a-\u212b\u2329-\u232a\u2adc\u302b-\u302c\uaab2-\uaab3\uf900-\ufa0d\ufa10\ufa12\ufa15-\ufa1e\ufa20\ufa22\ufa25-\ufa26\ufa2a-\ufa2d\ufa30-\ufa6d\ufa70-\ufad9\ufb1d\ufb1f\ufb2a-\ufb36\ufb38-\ufb3c\ufb3e\ufb40-\ufb41\ufb43-\ufb44\ufb46-\ufb4e\ufff0-\uffff]/g,o=function(t){var e,n={},r=[];for(e=0;65536>e;e++)r.push(String.fromCharCode(e));return t.lastIndex=0,r.join("").replace(t,function(t){return n[t]="\\u"+("0000"+t.charCodeAt(0).toString(16)).slice(-4),""}),t.lastIndex=0,n};e.exports={quote:function(t){var e=r.stringify(t);return i.lastIndex=0,i.test(e)?(n||(n=o(i)),e.replace(i,function(t){return n[t]})):e}}},{json3:55}],46:[function(t,e){(function(n){var r=t("./random"),i={},o=!1,s=n.chrome&&n.chrome.app&&n.chrome.app.runtime;e.exports={attachEvent:function(t,e){"undefined"!=typeof n.addEventListener?n.addEventListener(t,e,!1):n.document&&n.attachEvent&&(n.document.attachEvent("on"+t,e),n.attachEvent("on"+t,e))},detachEvent:function(t,e){"undefined"!=typeof n.addEventListener?n.removeEventListener(t,e,!1):n.document&&n.detachEvent&&(n.document.detachEvent("on"+t,e),n.detachEvent("on"+t,e))},unloadAdd:function(t){if(s)return null;var e=r.string(8);return i[e]=t,o&&setTimeout(this.triggerUnloadCallbacks,0),e},unloadDel:function(t){t in i&&delete i[t]},triggerUnloadCallbacks:function(){for(var t in i)i[t](),delete i[t]}};var a=function(){o||(o=!0,e.exports.triggerUnloadCallbacks())};s||e.exports.attachEvent("unload",a)}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{"./random":50}],47:[function(t,e){(function(n){var r=t("./event"),i=t("json3"),o=t("./browser");e.exports={WPrefix:"_jp",currentWindowId:null,polluteGlobalNamespace:function(){e.exports.WPrefix in n||(n[e.exports.WPrefix]={})},postMessage:function(t,r){n.parent!==n&&n.parent.postMessage(i.stringify({windowId:e.exports.currentWindowId,type:t,data:r||""}),"*")},createIframe:function(t,e){var i,o,s=n.document.createElement("iframe"),a=function(){clearTimeout(i);try{s.onload=null}catch(t){}s.onerror=null},u=function(){s&&(a(),setTimeout(function(){s&&s.parentNode.removeChild(s),s=null},0),r.unloadDel(o))},l=function(t){s&&(u(),e(t))},c=function(t,e){try{s&&s.contentWindow&&setTimeout(function(){s.contentWindow.postMessage(t,e)},0)}catch(n){}};return s.src=t,s.style.display="none",s.style.position="absolute",s.onerror=function(){l("onerror")},s.onload=function(){clearTimeout(i),i=setTimeout(function(){l("onload timeout")},2e3)},n.document.body.appendChild(s),i=setTimeout(function(){l("timeout")},15e3),o=r.unloadAdd(u),{post:c,cleanup:u,loaded:a}},createHtmlfile:function(t,i){var o,s,a,u=["Active"].concat("Object").join("X"),l=new n[u]("htmlfile"),c=function(){clearTimeout(o),a.onerror=null},f=function(){l&&(c(),r.unloadDel(s),a.parentNode.removeChild(a),a=l=null,CollectGarbage())},h=function(t){l&&(f(),i(t))},d=function(t,e){try{a&&a.contentWindow&&setTimeout(function(){a.contentWindow.postMessage(t,e)},0)}catch(n){}};l.open(),l.write('<html><script>document.domain="'+n.document.domain+'";</script></html>'),l.close(),l.parentWindow[e.exports.WPrefix]=n[e.exports.WPrefix];var p=l.createElement("div");return l.body.appendChild(p),a=l.createElement("iframe"),p.appendChild(a),a.src=t,a.onerror=function(){h("onerror")},o=setTimeout(function(){h("timeout")},15e3),s=r.unloadAdd(f),{post:d,cleanup:f,loaded:c}}},e.exports.iframeEnabled=!1,n.document&&(e.exports.iframeEnabled=("function"==typeof n.postMessage||"object"==typeof n.postMessage)&&!o.isKonqueror())}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{"./browser":44,"./event":46,debug:void 0,json3:55}],48:[function(t,e){(function(t){var n={};["log","debug","warn"].forEach(function(e){var r=t.console&&t.console[e]&&t.console[e].apply;n[e]=r?function(){return t.console[e].apply(t.console,arguments)}:"log"===e?function(){}:n.log}),e.exports=n}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{}],49:[function(t,e){e.exports={isObject:function(t){var e=typeof t;return"function"===e||"object"===e&&!!t},extend:function(t){if(!this.isObject(t))return t;for(var e,n,r=1,i=arguments.length;i>r;r++){e=arguments[r];for(n in e)Object.prototype.hasOwnProperty.call(e,n)&&(t[n]=e[n])}return t}}},{}],50:[function(t,e){var n=t("crypto"),r="abcdefghijklmnopqrstuvwxyz012345";e.exports={string:function(t){for(var e=r.length,i=n.randomBytes(t),o=[],s=0;t>s;s++)o.push(r.substr(i[s]%e,1));return o.join("")},number:function(t){return Math.floor(Math.random()*t)},numberString:function(t){var e=(""+(t-1)).length,n=new Array(e+1).join("0");return(n+this.number(t)).slice(-e)}}},{crypto:43}],51:[function(t,e){e.exports=function(t){return{filterToEnabled:function(e,n){var r={main:[],facade:[]};return e?"string"==typeof e&&(e=[e]):e=[],t.forEach(function(t){t&&("websocket"!==t.transportName||n.websocket!==!1)&&(e.length&&-1===e.indexOf(t.transportName)||t.enabled(n)&&(r.main.push(t),t.facadeTransport&&r.facade.push(t.facadeTransport)))}),r}}}},{debug:void 0}],52:[function(t,e){var n=t("url-parse");e.exports={getOrigin:function(t){if(!t)return null;var e=new n(t);if("file:"===e.protocol)return null;var r=e.port;return r||(r="https:"===e.protocol?"443":"80"),e.protocol+"//"+e.hostname+":"+r},isOriginEqual:function(t,e){var n=this.getOrigin(t)===this.getOrigin(e);return n},isSchemeEqual:function(t,e){return t.split(":")[0]===e.split(":")[0]},addPath:function(t,e){var n=t.split("?");return n[0]+e+(n[1]?"?"+n[1]:"")},addQuery:function(t,e){return t+(-1===t.indexOf("?")?"?"+e:"&"+e)}}},{debug:void 0,"url-parse":56}],53:[function(t,e){e.exports="1.0.1"},{}],54:[function(t,e){e.exports="function"==typeof Object.create?function(t,e){t.super_=e,t.prototype=Object.create(e.prototype,{constructor:{value:t,enumerable:!1,writable:!0,configurable:!0}})}:function(t,e){t.super_=e;var n=function(){};n.prototype=e.prototype,t.prototype=new n,t.prototype.constructor=t}},{}],55:[function(e,n,r){(function(e){(function(){function i(t,e){function n(t){if(n[t]!==m)return n[t];var i;if("bug-string-char-index"==t)i="a"!="a"[0];else if("json"==t)i=n("json-stringify")&&n("json-parse");else{var s,a='{"a":[1,true,false,null,"\\u0000\\b\\n\\f\\r\\t"]}';if("json-stringify"==t){var u=e.stringify,c="function"==typeof u&&g;if(c){(s=function(){return 1}).toJSON=s;try{c="0"===u(0)&&"0"===u(new r)&&'""'==u(new o)&&u(b)===m&&u(m)===m&&u()===m&&"1"===u(s)&&"[1]"==u([s])&&"[null]"==u([m])&&"null"==u(null)&&"[null,null,null]"==u([m,b,null])&&u({a:[s,!0,!1,null,"\x00\b\n\f\r	"]})==a&&"1"===u(null,s)&&"[\n 1,\n 2\n]"==u([1,2],null,1)&&'"-271821-04-20T00:00:00.000Z"'==u(new l(-864e13))&&'"+275760-09-13T00:00:00.000Z"'==u(new l(864e13))&&'"-000001-01-01T00:00:00.000Z"'==u(new l(-621987552e5))&&'"1969-12-31T23:59:59.999Z"'==u(new l(-1))}catch(f){c=!1}}i=c}if("json-parse"==t){var h=e.parse;if("function"==typeof h)try{if(0===h("0")&&!h(!1)){s=h(a);var d=5==s.a.length&&1===s.a[0];if(d){try{d=!h('"	"')}catch(f){}if(d)try{d=1!==h("01")}catch(f){}if(d)try{d=1!==h("1.")}catch(f){}}}}catch(f){d=!1}i=d}}return n[t]=!!i}t||(t=u.Object()),e||(e=u.Object());var r=t.Number||u.Number,o=t.String||u.String,a=t.Object||u.Object,l=t.Date||u.Date,c=t.SyntaxError||u.SyntaxError,f=t.TypeError||u.TypeError,h=t.Math||u.Math,d=t.JSON||u.JSON;"object"==typeof d&&d&&(e.stringify=d.stringify,e.parse=d.parse);var p,v,m,y=a.prototype,b=y.toString,g=new l(-0xc782b5b800cec);try{g=-109252==g.getUTCFullYear()&&0===g.getUTCMonth()&&1===g.getUTCDate()&&10==g.getUTCHours()&&37==g.getUTCMinutes()&&6==g.getUTCSeconds()&&708==g.getUTCMilliseconds()}catch(w){}if(!n("json")){var x="[object Function]",_="[object Date]",E="[object Number]",j="[object String]",T="[object Array]",S="[object Boolean]",O=n("bug-string-char-index");if(!g)var C=h.floor,A=[0,31,59,90,120,151,181,212,243,273,304,334],N=function(t,e){return A[e]+365*(t-1970)+C((t-1969+(e=+(e>1)))/4)-C((t-1901+e)/100)+C((t-1601+e)/400)};if((p=y.hasOwnProperty)||(p=function(t){var e,n={};return(n.__proto__=null,n.__proto__={toString:1},n).toString!=b?p=function(t){var e=this.__proto__,n=t in(this.__proto__=null,this);return this.__proto__=e,n}:(e=n.constructor,p=function(t){var n=(this.constructor||e).prototype;return t in this&&!(t in n&&this[t]===n[t])}),n=null,p.call(this,t)}),v=function(t,e){var n,r,i,o=0;(n=function(){this.valueOf=0}).prototype.valueOf=0,r=new n;for(i in r)p.call(r,i)&&o++;return n=r=null,o?v=2==o?function(t,e){var n,r={},i=b.call(t)==x;for(n in t)i&&"prototype"==n||p.call(r,n)||!(r[n]=1)||!p.call(t,n)||e(n)}:function(t,e){var n,r,i=b.call(t)==x;for(n in t)i&&"prototype"==n||!p.call(t,n)||(r="constructor"===n)||e(n);(r||p.call(t,n="constructor"))&&e(n)}:(r=["valueOf","toString","toLocaleString","propertyIsEnumerable","isPrototypeOf","hasOwnProperty","constructor"],v=function(t,e){var n,i,o=b.call(t)==x,a=!o&&"function"!=typeof t.constructor&&s[typeof t.hasOwnProperty]&&t.hasOwnProperty||p;for(n in t)o&&"prototype"==n||!a.call(t,n)||e(n);for(i=r.length;n=r[--i];a.call(t,n)&&e(n));}),v(t,e)},!n("json-stringify")){var k={92:"\\\\",34:'\\"',8:"\\b",12:"\\f",10:"\\n",13:"\\r",9:"\\t"},I="000000",P=function(t,e){return(I+(e||0)).slice(-t)},L="\\u00",R=function(t){for(var e='"',n=0,r=t.length,i=!O||r>10,o=i&&(O?t.split(""):t);r>n;n++){var s=t.charCodeAt(n);switch(s){case 8:case 9:case 10:case 12:case 13:case 34:case 92:e+=k[s];break;default:if(32>s){e+=L+P(2,s.toString(16));break}e+=i?o[n]:t.charAt(n)}}return e+'"'},U=function(t,e,n,r,i,o,s){var a,u,l,c,h,d,y,g,w,x,O,A,k,I,L,M;try{a=e[t]}catch(q){}if("object"==typeof a&&a)if(u=b.call(a),u!=_||p.call(a,"toJSON"))"function"==typeof a.toJSON&&(u!=E&&u!=j&&u!=T||p.call(a,"toJSON"))&&(a=a.toJSON(t));else if(a>-1/0&&1/0>a){if(N){for(h=C(a/864e5),l=C(h/365.2425)+1970-1;N(l+1,0)<=h;l++);for(c=C((h-N(l,0))/30.42);N(l,c+1)<=h;c++);h=1+h-N(l,c),d=(a%864e5+864e5)%864e5,y=C(d/36e5)%24,g=C(d/6e4)%60,w=C(d/1e3)%60,x=d%1e3}else l=a.getUTCFullYear(),c=a.getUTCMonth(),h=a.getUTCDate(),y=a.getUTCHours(),g=a.getUTCMinutes(),w=a.getUTCSeconds(),x=a.getUTCMilliseconds();a=(0>=l||l>=1e4?(0>l?"-":"+")+P(6,0>l?-l:l):P(4,l))+"-"+P(2,c+1)+"-"+P(2,h)+"T"+P(2,y)+":"+P(2,g)+":"+P(2,w)+"."+P(3,x)+"Z"}else a=null;if(n&&(a=n.call(e,t,a)),null===a)return"null";if(u=b.call(a),u==S)return""+a;if(u==E)return a>-1/0&&1/0>a?""+a:"null";if(u==j)return R(""+a);if("object"==typeof a){for(I=s.length;I--;)if(s[I]===a)throw f();if(s.push(a),O=[],L=o,o+=i,u==T){for(k=0,I=a.length;I>k;k++)A=U(k,a,n,r,i,o,s),O.push(A===m?"null":A);M=O.length?i?"[\n"+o+O.join(",\n"+o)+"\n"+L+"]":"["+O.join(",")+"]":"[]"}else v(r||a,function(t){var e=U(t,a,n,r,i,o,s);e!==m&&O.push(R(t)+":"+(i?" ":"")+e)}),M=O.length?i?"{\n"+o+O.join(",\n"+o)+"\n"+L+"}":"{"+O.join(",")+"}":"{}";return s.pop(),M}};e.stringify=function(t,e,n){var r,i,o,a;if(s[typeof e]&&e)if((a=b.call(e))==x)i=e;else if(a==T){o={};for(var u,l=0,c=e.length;c>l;u=e[l++],a=b.call(u),(a==j||a==E)&&(o[u]=1));}if(n)if((a=b.call(n))==E){if((n-=n%1)>0)for(r="",n>10&&(n=10);r.length<n;r+=" ");}else a==j&&(r=n.length<=10?n:n.slice(0,10));return U("",(u={},u[""]=t,u),i,o,r,"",[])}}if(!n("json-parse")){var M,q,D=o.fromCharCode,W={92:"\\",34:'"',47:"/",98:"\b",116:"	",110:"\n",102:"\f",114:"\r"},J=function(){throw M=q=null,c()},B=function(){for(var t,e,n,r,i,o=q,s=o.length;s>M;)switch(i=o.charCodeAt(M)){case 9:case 10:case 13:case 32:M++;break;case 123:case 125:case 91:case 93:case 58:case 44:return t=O?o.charAt(M):o[M],M++,t;case 34:for(t="@",M++;s>M;)if(i=o.charCodeAt(M),32>i)J();else if(92==i)switch(i=o.charCodeAt(++M)){case 92:case 34:case 47:case 98:case 116:case 110:case 102:case 114:t+=W[i],M++;break;case 117:for(e=++M,n=M+4;n>M;M++)i=o.charCodeAt(M),i>=48&&57>=i||i>=97&&102>=i||i>=65&&70>=i||J();t+=D("0x"+o.slice(e,M));break;default:J()}else{if(34==i)break;for(i=o.charCodeAt(M),e=M;i>=32&&92!=i&&34!=i;)i=o.charCodeAt(++M);t+=o.slice(e,M)}if(34==o.charCodeAt(M))return M++,t;J();default:if(e=M,45==i&&(r=!0,i=o.charCodeAt(++M)),i>=48&&57>=i){for(48==i&&(i=o.charCodeAt(M+1),i>=48&&57>=i)&&J(),r=!1;s>M&&(i=o.charCodeAt(M),i>=48&&57>=i);M++);if(46==o.charCodeAt(M)){for(n=++M;s>n&&(i=o.charCodeAt(n),i>=48&&57>=i);n++);n==M&&J(),M=n}if(i=o.charCodeAt(M),101==i||69==i){for(i=o.charCodeAt(++M),(43==i||45==i)&&M++,n=M;s>n&&(i=o.charCodeAt(n),i>=48&&57>=i);n++);n==M&&J(),M=n}return+o.slice(e,M)}if(r&&J(),"true"==o.slice(M,M+4))return M+=4,!0;if("false"==o.slice(M,M+5))return M+=5,!1;if("null"==o.slice(M,M+4))return M+=4,null;J()}return"$"},G=function(t){var e,n;if("$"==t&&J(),"string"==typeof t){if("@"==(O?t.charAt(0):t[0]))return t.slice(1);if("["==t){for(e=[];t=B(),"]"!=t;n||(n=!0))n&&(","==t?(t=B(),"]"==t&&J()):J()),","==t&&J(),e.push(G(t));return e}if("{"==t){for(e={};t=B(),"}"!=t;n||(n=!0))n&&(","==t?(t=B(),"}"==t&&J()):J()),(","==t||"string"!=typeof t||"@"!=(O?t.charAt(0):t[0])||":"!=B())&&J(),e[t.slice(1)]=G(B());return e}J()}return t},F=function(t,e,n){var r=H(t,e,n);r===m?delete t[e]:t[e]=r},H=function(t,e,n){var r,i=t[e];if("object"==typeof i&&i)if(b.call(i)==T)for(r=i.length;r--;)F(i,r,n);else v(i,function(t){F(i,t,n)});return n.call(t,e,i)};e.parse=function(t,e){var n,r;return M=0,q=""+t,n=G(B()),"$"!=B()&&J(),M=q=null,e&&b.call(e)==x?H((r={},r[""]=n,r),"",e):n}}}return e.runInContext=i,e}var o="function"==typeof t&&t.amd,s={"function":!0,object:!0},a=s[typeof r]&&r&&!r.nodeType&&r,u=s[typeof window]&&window||this,l=a&&s[typeof n]&&n&&!n.nodeType&&"object"==typeof e&&e;if(!l||l.global!==l&&l.window!==l&&l.self!==l||(u=l),a&&!o)i(u,a);else{var c=u.JSON,f=u.JSON3,h=!1,d=i(u,u.JSON3={noConflict:function(){return h||(h=!0,u.JSON=c,u.JSON3=f,c=f=null),d}});u.JSON={parse:d.parse,stringify:d.stringify}}o&&t(function(){return d})}).call(this)}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{}],56:[function(t,e){function n(t,e,u){if(!(this instanceof n))return new n(t,e,u);var l,c,f,h,d=s.test(t),p=typeof e,v=this,m=0;for("object"!==p&&"string"!==p&&(u=e,e=null),u&&"function"!=typeof u&&(u=o.parse),e=i(e);m<a.length;m++)c=a[m],l=c[0],h=c[1],l!==l?v[h]=t:"string"==typeof l?~(f=t.indexOf(l))&&("number"==typeof c[2]?(v[h]=t.slice(0,f),t=t.slice(f+c[2])):(v[h]=t.slice(f),t=t.slice(0,f))):(f=l.exec(t))&&(v[h]=f[1],t=t.slice(0,t.length-f[0].length)),v[h]=v[h]||(c[3]||"port"===h&&d?e[h]||"":""),c[4]&&(v[h]=v[h].toLowerCase());u&&(v.query=u(v.query)),r(v.port,v.protocol)||(v.host=v.hostname,v.port=""),v.username=v.password="",v.auth&&(c=v.auth.split(":"),v.username=c[0]||"",v.password=c[1]||""),v.href=v.toString()}var r=t("requires-port"),i=t("./lolcation"),o=t("querystringify"),s=/^\/(?!\/)/,a=[["#","hash"],["?","query"],["//","protocol",2,1,1],["/","pathname"],["@","auth",1],[0/0,"host",void 0,1,1],[/\:(\d+)$/,"port"],[0/0,"hostname",void 0,1,1]];n.prototype.set=function(t,e,n){var i=this;return"query"===t?("string"==typeof e&&(e=(n||o.parse)(e)),i[t]=e):"port"===t?(i[t]=e,r(e,i.protocol)?e&&(i.host=i.hostname+":"+e):(i.host=i.hostname,i[t]="")):"hostname"===t?(i[t]=e,i.port&&(e+=":"+i.port),i.host=e):"host"===t?(i[t]=e,/\:\d+/.test(e)&&(e=e.split(":"),i.hostname=e[0],i.port=e[1])):i[t]=e,i.href=i.toString(),i},n.prototype.toString=function(t){t&&"function"==typeof t||(t=o.stringify);var e,n=this,r=n.protocol+"//";return n.username&&(r+=n.username,n.password&&(r+=":"+n.password),r+="@"),r+=n.hostname,n.port&&(r+=":"+n.port),r+=n.pathname,n.query&&(e="object"==typeof n.query?t(n.query):n.query,r+=("?"===e.charAt(0)?"":"?")+e),n.hash&&(r+=n.hash),r},n.qs=o,n.location=i,e.exports=n},{"./lolcation":57,querystringify:58,"requires-port":59}],57:[function(t,e){(function(n){var r,i={hash:1,query:1};e.exports=function(e){e=e||n.location||{},r=r||t("./");var o,s={},a=typeof e;if("blob:"===e.protocol)s=new r(unescape(e.pathname),{});else if("string"===a){s=new r(e,{});for(o in i)delete s[o]}else if("object"===a)for(o in e)o in i||(s[o]=e[o]);return s}}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{"./":56}],58:[function(t,e,n){function r(t){for(var e,n=/([^=?&]+)=([^&]*)/g,r={};e=n.exec(t);r[decodeURIComponent(e[1])]=decodeURIComponent(e[2]));return r}function i(t,e){e=e||"";var n=[];"string"!=typeof e&&(e="?");for(var r in t)o.call(t,r)&&n.push(encodeURIComponent(r)+"="+encodeURIComponent(t[r]));return n.length?e+n.join("&"):""}var o=Object.prototype.hasOwnProperty;n.stringify=i,n.parse=r},{}],59:[function(t,e){e.exports=function(t,e){if(e=e.split(":")[0],t=+t,!t)return!1;switch(e){case"http":case"ws":return 80!==t;case"https":case"wss":return 443!==t;case"ftp":return 22!==t;case"gopher":return 70!==t;case"file":return!1}return 0!==t}},{}]},{},[1])(1)});
// Generated by CoffeeScript 1.7.1
/*
   Stomp Over WebSocket http://www.jmesnil.net/stomp-websocket/doc/ | Apache License V2.0

   Copyright (C) 2010-2013 [Jeff Mesnil](http://jmesnil.net/)
   Copyright (C) 2012 [FuseSource, Inc.](http://fusesource.com)
 */
(function(){var t,e,n,i,r={}.hasOwnProperty,o=[].slice;t={LF:"\n",NULL:"\x00"};n=function(){var e;function n(t,e,n){this.command=t;this.headers=e!=null?e:{};this.body=n!=null?n:""}n.prototype.toString=function(){var e,i,o,s,u;e=[this.command];o=this.headers["content-length"]===false?true:false;if(o){delete this.headers["content-length"]}u=this.headers;for(i in u){if(!r.call(u,i))continue;s=u[i];e.push(""+i+":"+s)}if(this.body&&!o){e.push("content-length:"+n.sizeOfUTF8(this.body))}e.push(t.LF+this.body);return e.join(t.LF)};n.sizeOfUTF8=function(t){if(t){return encodeURI(t).match(/%..|./g).length}else{return 0}};e=function(e){var i,r,o,s,u,a,c,f,h,l,p,d,g,b,m,v,y;s=e.search(RegExp(""+t.LF+t.LF));u=e.substring(0,s).split(t.LF);o=u.shift();a={};d=function(t){return t.replace(/^\s+|\s+$/g,"")};v=u.reverse();for(g=0,m=v.length;g<m;g++){l=v[g];f=l.indexOf(":");a[d(l.substring(0,f))]=d(l.substring(f+1))}i="";p=s+2;if(a["content-length"]){h=parseInt(a["content-length"]);i=(""+e).substring(p,p+h)}else{r=null;for(c=b=p,y=e.length;p<=y?b<y:b>y;c=p<=y?++b:--b){r=e.charAt(c);if(r===t.NULL){break}i+=r}}return new n(o,a,i)};n.unmarshall=function(n){var i,r,o,s;r=n.split(RegExp(""+t.NULL+t.LF+"*"));s={frames:[],partial:""};s.frames=function(){var t,n,o,s;o=r.slice(0,-1);s=[];for(t=0,n=o.length;t<n;t++){i=o[t];s.push(e(i))}return s}();o=r.slice(-1)[0];if(o===t.LF||o.search(RegExp(""+t.NULL+t.LF+"*$"))!==-1){s.frames.push(e(o))}else{s.partial=o}return s};n.marshall=function(e,i,r){var o;o=new n(e,i,r);return o.toString()+t.NULL};return n}();e=function(){var e;function r(t){this.ws=t;this.ws.binaryType="arraybuffer";this.counter=0;this.connected=false;this.heartbeat={outgoing:1e4,incoming:1e4};this.maxWebSocketFrameSize=16*1024;this.subscriptions={};this.partialData=""}r.prototype.debug=function(t){var e;return typeof window!=="undefined"&&window!==null?(e=window.console)!=null?e.log(t):void 0:void 0};e=function(){if(Date.now){return Date.now()}else{return(new Date).valueOf}};r.prototype._transmit=function(t,e,i){var r;r=n.marshall(t,e,i);if(typeof this.debug==="function"){this.debug(">>> "+r)}while(true){if(r.length>this.maxWebSocketFrameSize){this.ws.send(r.substring(0,this.maxWebSocketFrameSize));r=r.substring(this.maxWebSocketFrameSize);if(typeof this.debug==="function"){this.debug("remaining = "+r.length)}}else{return this.ws.send(r)}}};r.prototype._setupHeartbeat=function(n){var r,o,s,u,a,c;if((a=n.version)!==i.VERSIONS.V1_1&&a!==i.VERSIONS.V1_2){return}c=function(){var t,e,i,r;i=n["heart-beat"].split(",");r=[];for(t=0,e=i.length;t<e;t++){u=i[t];r.push(parseInt(u))}return r}(),o=c[0],r=c[1];if(!(this.heartbeat.outgoing===0||r===0)){s=Math.max(this.heartbeat.outgoing,r);if(typeof this.debug==="function"){this.debug("send PING every "+s+"ms")}this.pinger=i.setInterval(s,function(e){return function(){e.ws.send(t.LF);return typeof e.debug==="function"?e.debug(">>> PING"):void 0}}(this))}if(!(this.heartbeat.incoming===0||o===0)){s=Math.max(this.heartbeat.incoming,o);if(typeof this.debug==="function"){this.debug("check PONG every "+s+"ms")}return this.ponger=i.setInterval(s,function(t){return function(){var n;n=e()-t.serverActivity;if(n>s*2){if(typeof t.debug==="function"){t.debug("did not receive server activity for the last "+n+"ms")}return t.ws.close()}}}(this))}};r.prototype._parseConnect=function(){var t,e,n,i;t=1<=arguments.length?o.call(arguments,0):[];i={};switch(t.length){case 2:i=t[0],e=t[1];break;case 3:if(t[1]instanceof Function){i=t[0],e=t[1],n=t[2]}else{i.login=t[0],i.passcode=t[1],e=t[2]}break;case 4:i.login=t[0],i.passcode=t[1],e=t[2],n=t[3];break;default:i.login=t[0],i.passcode=t[1],e=t[2],n=t[3],i.host=t[4]}return[i,e,n]};r.prototype.connect=function(){var r,s,u,a;r=1<=arguments.length?o.call(arguments,0):[];a=this._parseConnect.apply(this,r);u=a[0],this.connectCallback=a[1],s=a[2];if(typeof this.debug==="function"){this.debug("Opening Web Socket...")}this.ws.onmessage=function(i){return function(r){var o,u,a,c,f,h,l,p,d,g,b,m,v;c=typeof ArrayBuffer!=="undefined"&&r.data instanceof ArrayBuffer?(o=new Uint8Array(r.data),typeof i.debug==="function"?i.debug("--- got data length: "+o.length):void 0,function(){var t,e,n;n=[];for(t=0,e=o.length;t<e;t++){u=o[t];n.push(String.fromCharCode(u))}return n}().join("")):r.data;i.serverActivity=e();if(c===t.LF){if(typeof i.debug==="function"){i.debug("<<< PONG")}return}if(typeof i.debug==="function"){i.debug("<<< "+c)}d=n.unmarshall(i.partialData+c);i.partialData=d.partial;m=d.frames;v=[];for(g=0,b=m.length;g<b;g++){f=m[g];switch(f.command){case"CONNECTED":if(typeof i.debug==="function"){i.debug("connected to server "+f.headers.server)}i.connected=true;i._setupHeartbeat(f.headers);v.push(typeof i.connectCallback==="function"?i.connectCallback(f):void 0);break;case"MESSAGE":p=f.headers.subscription;l=i.subscriptions[p]||i.onreceive;if(l){a=i;h=f.headers["message-id"];f.ack=function(t){if(t==null){t={}}return a.ack(h,p,t)};f.nack=function(t){if(t==null){t={}}return a.nack(h,p,t)};v.push(l(f))}else{v.push(typeof i.debug==="function"?i.debug("Unhandled received MESSAGE: "+f):void 0)}break;case"RECEIPT":v.push(typeof i.onreceipt==="function"?i.onreceipt(f):void 0);break;case"ERROR":v.push(typeof s==="function"?s(f):void 0);break;default:v.push(typeof i.debug==="function"?i.debug("Unhandled frame: "+f):void 0)}}return v}}(this);this.ws.onclose=function(t){return function(){var e;e="Whoops! Lost connection to "+t.ws.url;if(typeof t.debug==="function"){t.debug(e)}t._cleanUp();return typeof s==="function"?s(e):void 0}}(this);return this.ws.onopen=function(t){return function(){if(typeof t.debug==="function"){t.debug("Web Socket Opened...")}u["accept-version"]=i.VERSIONS.supportedVersions();u["heart-beat"]=[t.heartbeat.outgoing,t.heartbeat.incoming].join(",");return t._transmit("CONNECT",u)}}(this)};r.prototype.disconnect=function(t,e){if(e==null){e={}}this._transmit("DISCONNECT",e);this.ws.onclose=null;this.ws.close();this._cleanUp();return typeof t==="function"?t():void 0};r.prototype._cleanUp=function(){this.connected=false;if(this.pinger){i.clearInterval(this.pinger)}if(this.ponger){return i.clearInterval(this.ponger)}};r.prototype.send=function(t,e,n){if(e==null){e={}}if(n==null){n=""}e.destination=t;return this._transmit("SEND",e,n)};r.prototype.subscribe=function(t,e,n){var i;if(n==null){n={}}if(!n.id){n.id="sub-"+this.counter++}n.destination=t;this.subscriptions[n.id]=e;this._transmit("SUBSCRIBE",n);i=this;return{id:n.id,unsubscribe:function(){return i.unsubscribe(n.id)}}};r.prototype.unsubscribe=function(t){delete this.subscriptions[t];return this._transmit("UNSUBSCRIBE",{id:t})};r.prototype.begin=function(t){var e,n;n=t||"tx-"+this.counter++;this._transmit("BEGIN",{transaction:n});e=this;return{id:n,commit:function(){return e.commit(n)},abort:function(){return e.abort(n)}}};r.prototype.commit=function(t){return this._transmit("COMMIT",{transaction:t})};r.prototype.abort=function(t){return this._transmit("ABORT",{transaction:t})};r.prototype.ack=function(t,e,n){if(n==null){n={}}n["message-id"]=t;n.subscription=e;return this._transmit("ACK",n)};r.prototype.nack=function(t,e,n){if(n==null){n={}}n["message-id"]=t;n.subscription=e;return this._transmit("NACK",n)};return r}();i={VERSIONS:{V1_0:"1.0",V1_1:"1.1",V1_2:"1.2",supportedVersions:function(){return"1.1,1.0"}},client:function(t,n){var r,o;if(n==null){n=["v10.stomp","v11.stomp"]}r=i.WebSocketClass||WebSocket;o=new r(t,n);return new e(o)},over:function(t){return new e(t)},Frame:n};if(typeof exports!=="undefined"&&exports!==null){exports.Stomp=i}if(typeof window!=="undefined"&&window!==null){i.setInterval=function(t,e){return window.setInterval(e,t)};i.clearInterval=function(t){return window.clearInterval(t)};window.Stomp=i}else if(!exports){self.Stomp=i}}).call(this);
define("stomp", (function (global) {
    return function () {
        var ret, fn;
        return ret || global.Stomp;
    };
}(this)));

define('service/rabbitmqWebStompService',[
    'core/subscriber',
    'sockjs',
    'stomp'
], function (Subscriber, SockJS, Stomp) {
    

    var RabbitmqWebStompService = function () {
        Subscriber.prototype.constructor.apply(this);

        this.ws = null;
        this.client = null;

        this.bind__messageCallback = this._messageCallback.bind(this);
    };

    RabbitmqWebStompService.extend(Subscriber);

    RabbitmqWebStompService.Event = {
        CONNECTED: 'eventConnected',
        DISCONNECTED: 'eventDisconnected',
        MESSAGE: 'eventMessage'
    };

    RabbitmqWebStompService.prototype.isConnected = function () {
        return (this.ws && this.client);
    };

    RabbitmqWebStompService.prototype.connect = function () {
        if (this.isConnected())
            return;

        this.ws = new SockJS(window.location.protocol + '//' + window.location.hostname + '/stomp');
        this.client = Stomp.over(this.ws);

        // SockJS does not support heart-beat: disable heart-beats
        this.client.heartbeat.incoming = 0;
        this.client.heartbeat.outgoing = 0;

        var onConnect = function () {
            this._dispatch(RabbitmqWebStompService.Event.CONNECTED);
        }.bind(this);

        var onError = function (error) {
            console.error(error);
        };

        //TODO proper credentials
        this.client.connect('test', 'test', onConnect, onError, '/');
    };

    RabbitmqWebStompService.prototype.disconnect = function () {
        this.client.disconnect(function () {
            this._dispatch(RabbitmqWebStompService.Event.DISCONNECTED);
        }.bind(this));

        this.client = null;
        this.ws = null;
    };

    RabbitmqWebStompService.prototype._messageCallback = function (msg) {
        console.log(msg);

        this._dispatch(RabbitmqWebStompService.Event.MESSAGE, {
            message: JSON.parse(msg.body)
        });
    };

    RabbitmqWebStompService.prototype.subscribe = function (destination, event, callback) {
        Subscriber.prototype.subscribe.call(this, event, callback);

        if (!destination)
            return;

        return this.client.subscribe(destination, this.bind__messageCallback);
    };

    RabbitmqWebStompService.prototype.unsubscribe = function (subscription, callback) {
        Subscriber.prototype.unsubscribe.call(this, callback);

        if (subscription)
            this.client.unsubscribe(subscription.id);
    };

    return RabbitmqWebStompService;
});

define('service/subscriberService',[
    'core/subscriber'
], function (Subscriber) {
    

    var SubscriberService = function () {
        Subscriber.prototype.constructor.apply(this);
    };

    SubscriberService.extend(Subscriber);

    SubscriberService.prototype.dispatch = function (tag, event, args) {
        args = args || {};
        args.tag = tag;

        Subscriber.prototype._dispatch.call(this, event, args);
    };

    return SubscriberService;
});

define('service/threadPostService',[
    'core/subscriber',
    'underscore'
], function (Subscriber) {
    

    var ThreadPostService = function () {
        Subscriber.prototype.constructor.apply(this);

        this.name = 'ThreadPost';
    };

    ThreadPostService.extend(Subscriber);

    ThreadPostService.Event = {
        ADD: 'eventAdd',
        REPLACE: 'eventUpdate',
        REMOVE: 'eventRemove'
    };

    ThreadPostService.prototype.subscribe = function (callback) {
        _.each(ThreadPostService.Event, function (value, key, list) {
            Subscriber.prototype.subscribe.call(this, value, callback);
        }.bind(this));
    };

    ThreadPostService.prototype.dispatch = function (event, args) {
        Subscriber.prototype._dispatch.call(this, event, args);
    };

    return ThreadPostService;
});

define('model/forumModel',[
    'model/model',
    'model/mediaModel',
    'extra'
], function (Model, MediaModel) {
    

    var ForumModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);

        // replace key/value objects with models for all media
        // TODO consolidate under collection type?
        if (this.data.ordered_media) {
            this.data.ordered_media.forEach(function (element, index, array) {
                array[index] = new MediaModel(element);
            });
        }
    };

    ForumModel.extend(Model);

    return ForumModel;
});

define('model/groupModel',[
    'model/model',
    'model/mediaModel',
    'extra'
], function (Model, MediaModel) {
    

    var GroupModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);

        // replace key/value objects with models for all media
        // TODO consolidate under collection type?
        if (this.data.ordered_media) {
            this.data.ordered_media.forEach(function (element, index, array) {
                array[index] = new MediaModel(element);
            });
        }
    };

    GroupModel.extend(Model);

    return GroupModel;
});

define('model/messageModel',[
    'model/model',
    'model/mediaModel',
    'extra'
], function (Model, MediaModel) {
    

    var MessageModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);

        // replace key/value objects with models for all media
        // TODO consolidate under collection type?
        if (this.data.ordered_media) {
            this.data.ordered_media.forEach(function (element, index, array) {
                array[index] = new MediaModel(element);
            });
        }
    };

    MessageModel.extend(Model);

    return MessageModel;
});

define('model/myFilesModel',[
    'model/model',
    'model/mediaModel',
    'extra',
    'underscore'
], function (Model, MediaModel) {
    

    var MyFilesModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);

        // replace key/value objects with models for all media
        // TODO consolidate under collection type?
        if (this.data.media) {
            // special case: knp paginator 'items.getArrayCopy' returns associative array for page > 1
            // (spliced results > 0 in list) resulting in an object instead of an array after being serialized to json
            var old = this.data.media;
            this.data.media = [];

            _.each(old, function (element, index, list) {
                var media = new MediaModel(element);

                // subscribe to model events
                media.subscribe(Model.Event.CHANGE, function (e) {
                    var cIndex = _.findIndex(this.data.media, e.model);
                    if (cIndex > -1) {
                        // bubble for model changes. prefix this collection's keypath
                        this._dispatch(Model.Event.CHANGE, 'media.' + cIndex);
                    }
                }.bind(this));

                this.data.media.push(media);
            }, this);

            old = null;
        }
    };

    MyFilesModel.extend(Model);

    MyFilesModel.prototype.addMedia = function (element) {
	var media = new MediaModel(element);
	media.subscribe(Model.Event.CHANGE, function (e) {
            var cIndex = _.findIndex(this.data.media, e.model);
            if (cIndex > -1) {
                // bubble for model changes. prefix this collection's keypath
                this._dispatch(Model.Event.CHANGE, 'media.' + cIndex);
            }
        }.bind(this));

        this.data.media.push(media);
//        this.forceChange();
    };
    
    MyFilesModel.prototype.getMedia = function (mediaId) {
        return _.find(this.data.media, function (media) {
            return media.get('id') == mediaId;
        });
    };

    return MyFilesModel;
});

define('model/profileModel',[
    'model/model',
    'extra'
], function (Model) {
    

    var ProfileModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);
    };

    ProfileModel.extend(Model);

    return ProfileModel;
});

define('model/threadModel',[
    'model/model',
    'model/postModel',
    'extra',
    'underscore'
], function (Model, PostModel) {
    

    var ThreadModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);

        this.data.keyPoints = [];

        // replace key/value objects with models for all media
        // TODO consolidate under collection type?
        if (this.data.posts) {
            this.data.posts.forEach(function (element, index, array) {
                array[index] = new PostModel(element);
            });
        }
    };

    ThreadModel.extend(Model);

    ThreadModel.prototype.addKeyPoint = function (keyPoint) {
        this.removeKeyPoint(keyPoint.id);

        this.data.keyPoints.push(keyPoint);
        this._dispatch(Model.Event.CHANGE, 'keyPoints.' + (this.data.keyPoints.length - 1));
    };

    ThreadModel.prototype._findKeyPoint = function (keyPointId) {
        for (var index in this.data.keyPoints) {
            if (this.data.keyPoints[index].id == keyPointId) {
                return index;
            }
        }
    };

    ThreadModel.prototype.setKeyPointProperty = function (keyPointId, keyPath, value, doDispatch) {
        var index = this._findKeyPoint(keyPointId);
        if (index > -1)
            this.set('keyPoints.' + index + '.' + keyPath, value, doDispatch);
    };

    ThreadModel.prototype.removeKeyPoint = function (keyPointId) {
        var index = this._findKeyPoint(keyPointId);
        if (index > -1) {
            this.data.keyPoints.splice(index, 1);
            this._dispatch(Model.Event.CHANGE, 'keyPoints');
        }
    };

    ThreadModel.prototype.forceChangeKeyPoint = function (keyPointId, keyPath) {
        var index = this._findKeyPoint(keyPointId);
        if (index > -1)
            this.forceChange('keyPoints.' + index + '.' + keyPath);
    };

    ThreadModel.prototype.addPost = function (post, view) {
        //TODO add model.add method?
        var posts = this.get('posts', []);
        this.set('posts.' + posts.length, post, false);
        this.forceChange('posts.' + (posts.length - 1), {isNew: true, view: view});
    };

    ThreadModel.prototype.removePost = function (post, nested) {
        var posts = this.get('posts');
        var index = this.find(post.get('id'), 'id', posts);
        if (index > -1) {
            posts.splice(index, 1);
            var childPostsToRemove = [];
            _.each(posts, function (element, index, list) {
                if (element.get('parent_post_id') == post.get('id'))
                    childPostsToRemove.push(element);
            });
            this.set('posts', _.difference(posts, childPostsToRemove));
        }
    };

    ThreadModel.prototype.forceChangePost = function (post, view) {
        var index = this.find(post.get('id'), 'id', this.get('posts'));
        if (index > -1)
            this.forceChange('posts.' + index, {view: view});
    };

    return ThreadModel;
});

define('model/userModel',[
    'model/model',
    'extra',
    'underscore'
], function (Model) {
    

    var UserModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);
    };

    UserModel.extend(Model);

    UserModel.prototype.isUserOnFriendsList = function (username) {
        return _.contains(this.get('friends_list'), username);
    };

    UserModel.prototype.isUserOnMentorList = function (username) {
        return _.contains(this.get('mentor_list'), username);
    };

    UserModel.prototype.isUserOnMenteeList = function (username) {
        return _.contains(this.get('mentee_list'), username);
    };

    UserModel.prototype.isUserOnInvitedMentorList = function (username) {
        var result = _.find(this.get('created_invitations'), function (invite) {
            return invite.recipient == username && !invite.isMentor && !invite.isAccepted && !invite.isCancelled && !invite.isDeclined;
        });

        return result !== 'undefined';
    };

    UserModel.prototype.isUserOnInvitedMenteeList = function (username) {
        var result = _.find(this.get('created_invitations'), function (invite) {
            return invite.recipient == username && !invite.isMentee && !invite.isAccepted && !invite.isCancelled && !invite.isDeclined;
        });

        return result !== 'undefined';
    };

    return UserModel;
});

define('controller/contactController',[
    'factory/contactFactory'
], function (ContactFactory) {
    

    var Contact = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Contact.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        $tt._instances.push(this);
    };

    Contact.TAG = 'Contact';

    Contact.prototype.onViewLoaded = function () {

    };

    Contact.prototype.delete = function (userIds, contactList) {
        return ContactFactory.delete(userIds, contactList)
            .done(function (data) {
                window.location.reload(true);
            });
    };

    return Contact;
});

define('controller/forumController',[
    'factory/forumFactory'
], function (ForumFactory) {
    

    var Forum = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Forum.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        $tt._instances.push(this);
    };

    Forum.TAG = 'Forum';

    Forum.prototype.onViewLoaded = function () {

    };

    Forum.prototype.delete = function () {
        return ForumFactory.delete(this.model)
            .done(function (data) {
                window.location.assign(data.redirect_url);
            });
    };

    return Forum;
});

define('controller/groupController',[
    'factory/groupFactory'
], function (GroupFactory) {
    

    var Group = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Group.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        $tt._instances.push(this);
    };

    Group.TAG = 'Group';

    Group.prototype.onViewLoaded = function () {

    };

    Group.prototype.delete = function () {
        return GroupFactory.delete(this.model)
            .done(function (data) {
                window.location.assign(data.redirect_url);
            });
    };

    return Group;
});

define('controller/homeController',[],function () {
    

    var Home = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Home.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        $tt._instances.push(this);
    };

    Home.TAG = 'Home';

    Home.prototype.onViewLoaded = function () {

    };

    return Home;
});

define('controller/messageController',[
    'factory/messageFactory'
], function (MessageFactory) {
    

    var Message = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Message.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        $tt._instances.push(this);
    };

    Message.TAG = 'Message';

    Message.prototype.onViewLoaded = function () {
        if (this.model.get('id'))
            this.markAsRead();
    };

    //TODO rename to 'edit' after php controller changes
    Message.prototype.markAsRead = function () {
        return MessageFactory.edit(this.model);
    };

    return Message;
});

define('controller/myFilesController',[
    'factory/mediaFactory'
], function (MediaFactory) {
    

    var MyFilesController = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', MyFilesController.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        $tt._instances.push(this);
    };

    MyFilesController.TAG = 'MyFiles';

    MyFilesController.prototype.onViewLoaded = function () {

    };

    MyFilesController.prototype.edit = function (mediaId, properties) {
        var media = this.model.getMedia(mediaId);
        if (!media) {
            throw new Error('media not found');
        }

        Object.keys(properties).forEach(function (key, index, array) {
            media.set(key, properties[key]);
        });

        return MediaFactory.edit(media);
    };

    MyFilesController.prototype.delete = function (mediaId, confirmed) {
        var media = this.model.getMedia(mediaId);
        if (!media) {
            throw new Error('media not found');
        }

        return MediaFactory.delete(media, confirmed);
    };

    return MyFilesController;
});

define('controller/postController',[
    'factory/postFactory',
    'service',
    'service/keyPointService',
    'service/threadPostService'
], function (PostFactory, Service, KeyPointService, ThreadPostService) {
    

    var Post = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Post.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        this.keyPointService = Service.get('keyPoint');
        this.threadPostService = Service.get('threadPost');

        this.model.set('keyPoint', new KeyPoint(
            this.model.get('id'),
            this.model.get('start_time'),
            this.model.get('end_time'),
            '', {drawOnTimeLine: this.model.get('is_temporal', false)}
        ));

        this.bind__onKeyPointEvent = this._onKeyPointEvent.bind(this); // KeyPointService
        this.bind__onThreadPostEvent = this._onThreadPostEvent.bind(this); // ThreadPostService

        $tt._instances.push(this);
    };

    Post.TAG = 'Post';

    Post.prototype._onKeyPointEvent = function (e) {
        switch (e.type) {
            case KeyPointService.Event.DURATION:
                this.model.set('keyPoint.videoDuration', e.duration);
                break;
            case KeyPointService.Event.SELECTION_TIMES:
                this.model.set('keyPoint.selection', e.selection);
                break;
            case KeyPointService.Event.HOVER:
            case KeyPointService.Event.CLICK:
                this.model.forceChange();
                break;
            /*case KeyPointService.Event.HOVER:
                this.model.set('keyPoint.isHovering', e.isMouseOver);
                break;
            case KeyPointService.Event.CLICK:
                var prop = 'keyPoint.' + (e.isDblClick ? 'isPlaying' : 'isSeeking');
                this.model.set(prop, true);
                setTimeout(function () {
                    this.model.set(prop, false, false);
                }.bind(this), 100);
                break;*/
        }
    };

    Post.prototype._onThreadPostEvent = function (e) {
        switch (e.type) {
            case ThreadPostService.Event.REMOVE:
                // watch for new post forms being removed
                if (e.post.get('id') < 0 && e.post.get('parent_post_id') == this.model.get('id'))
                    this.model.forceChange();
                break;
        }
    };

    Post.prototype.onViewLoaded = function () {
        this.addKeyPoint();
        this.threadPostService.subscribe(this.bind__onThreadPostEvent);
    };

    Post.prototype.addKeyPoint = function () {
        var keyPoint = this.model.get('keyPoint');
        this.keyPointService.register(keyPoint);
        this.keyPointService.subscribe(keyPoint.id, this.bind__onKeyPointEvent);
        this.keyPointService.dispatch(keyPoint.id, KeyPointService.Event.ADD);
    };

    Post.prototype.hoverKeyPoint = function (args) {
        this.model.set('keyPoint.isHovering', args.isMouseOver);

        this.keyPointService.dispatch(this.model.get('keyPoint.id'), KeyPointService.Event.HOVER, args);
    };

    Post.prototype.clickKeyPoint = function (args) {
        var prop = 'keyPoint.' + (args.isDblClick ? 'isPlaying' : 'isSeeking');
        this.model.set(prop, true);
        setTimeout(function () {
            this.model.set(prop, false, false);
        }.bind(this), 100);

        this.keyPointService.dispatch(this.model.get('keyPoint.id'), KeyPointService.Event.CLICK, args);
    };

    Post.prototype.editKeyPoint = function (args) {
        this.keyPointService.dispatch(this.model.get('keyPoint.id'), KeyPointService.Event.EDIT, args);
    };

    Post.prototype.removeKeyPoint = function () {
        var keyPoint = this.model.get('keyPoint');
        this.keyPointService.dispatch(keyPoint.id, KeyPointService.Event.REMOVE);
        this.keyPointService.unsubscribe(keyPoint.id, this.bind__onKeyPointEvent);
        this.keyPointService.deregister(keyPoint.id);
    };

    Post.prototype.addToThread = function (post) {
        this.threadPostService.dispatch(ThreadPostService.Event.ADD, {post: post});
    };

    Post.prototype.updateInThread = function (view) {
        this.threadPostService.dispatch(ThreadPostService.Event.REPLACE, {post: this.model, view: view});
    };

    Post.prototype.removeFromThread = function () {
        this.threadPostService.dispatch(ThreadPostService.Event.REMOVE, {post: this.model});
    };

    Post.prototype.new = function () {
        return PostFactory.new(this.model);
    };

    Post.prototype.post = function (form) {
        return PostFactory.post(this.model, form);
    };

    Post.prototype.get = function () {
        return PostFactory.get(this.model);
    };

    Post.prototype.edit = function () {
        return PostFactory.edit(this.model);
    };

    Post.prototype.put = function (form) {
        return PostFactory.put(this.model, form);
    };

    Post.prototype.delete = function () {
        return PostFactory.delete(this.model)
            .done(function (data) {
                this.removeKeyPoint();
            }.bind(this));
    };

    return Post;
});

define('controller/profileController',[],function () {
    

    var Profile = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Profile.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        $tt._instances.push(this);
    };

    Profile.TAG = 'Profile';

    Profile.prototype.onViewLoaded = function () {

    };

    return Profile;
});

define('controller/threadController',[
    'factory/threadFactory',
    'service',
    'service/keyPointService',
    'service/threadPostService'
], function (ThreadFactory, Service, KeyPointService, ThreadPostService) {
    

    var Thread = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Thread.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        this.keyPointService = Service.get('keyPoint');
        this.threadPostService = Service.get('threadPost');

        this.videoSpeed = 0;

        this.bind__onKeyPointEvent = this._onKeyPointEvent.bind(this); // KeyPointService
        this.bind__onThreadPostEvent = this._onThreadPostEvent.bind(this); // ThreadPostService

        this.keyPointService.subscribe('all', this.bind__onKeyPointEvent);
        this.threadPostService.subscribe(this.bind__onThreadPostEvent);

        $tt._instances.push(this);
    };

    Thread.TAG = 'Thread';

    Thread.prototype._onKeyPointEvent = function (e) {
        /*if (!e.keyPoints)
            return; // only 'all' dispatches wanted*/

        switch (e.type) {
            case KeyPointService.Event.ADD:
                //if (e.keyPoint.startTime && e.keyPoint.endTime)
                    this.model.addKeyPoint(e.keyPoint);
                break;
            case KeyPointService.Event.HOVER:
            case KeyPointService.Event.CLICK:
                this.model.forceChangeKeyPoint(e.keyPoint.id);
                break;
            /*case KeyPointService.Event.HOVER:
                this.model.setKeyPointProperty(e.keyPoint.id, 'isHovering', e.isMouseOver);
                break;
            case KeyPointService.Event.CLICK:
                var prop = e.isDblClick ? 'isPlaying' : 'isSeeking';
                this.model.setKeyPointProperty(e.keyPoint.id, prop, true);
                setTimeout(function () {
                    this.model.setKeyPointProperty(e.keyPoint.id, prop, false, false);
                }.bind(this), 100);
                break;*/
            case KeyPointService.Event.EDIT:
                if (!e.cancel) {
                    this.model.setKeyPointProperty(e.keyPoint.id, 'options.drawOnTimeLine', false);
                    this.model.setKeyPointProperty(e.keyPoint.id, 'isEditing', true);
                    break;
                }
                // do not break;
            case KeyPointService.Event.REMOVE:
                this.model.setKeyPointProperty(e.keyPoint.id, 'isEditing', false);
                this.model.setKeyPointProperty(e.keyPoint.id, 'options.drawOnTimeLine', true);
                if (e.type == KeyPointService.Event.REMOVE) {
                    //setTimeout(function () {
                        this.model.removeKeyPoint(e.keyPoint.id);
                    //}.bind(this), 100);
                }
                break;
        }
    };

    Thread.prototype._onThreadPostEvent = function (e) {
        switch (e.type) {
            case ThreadPostService.Event.ADD:
                this.model.addPost(e.post, e.post.get('id') < 0 ? 'new' : 'view');
                break;
            case ThreadPostService.Event.REPLACE:
                this.model.forceChangePost(e.post, e.view);
                break;
            case ThreadPostService.Event.REMOVE:
                this.model.removePost(e.post);
                break;
        }
    };

    Thread.prototype.onViewLoaded = function () {

    };

    Thread.prototype.updateKeyPointDuration = function (duration) {
        if (!isNaN(duration)) {
            this.keyPointService.dispatch('all', KeyPointService.Event.DURATION, {duration: duration});
        }
    };

    Thread.prototype.updateKeyPointSelectionTimes = function (selection) {
        this.keyPointService.dispatch('all', KeyPointService.Event.SELECTION_TIMES, {
            selection: {
                startTime: parseFloat(selection.minTime).toFixed(2),
                endTime: parseFloat(selection.maxTime).toFixed(2)
            }
        });
    };

    Thread.prototype.hoverKeyPoint = function (keyPointId, args) {
        this.model.setKeyPointProperty(keyPointId, 'isPlayerHovering', args.isMouseOver);

        this.keyPointService.dispatch(keyPointId, KeyPointService.Event.HOVER, args);
    };

    Thread.prototype.rightClickKeyPoint = function (keyPointId) {
        this.model.setKeyPointProperty(keyPointId, 'isPlayerPlaying', true);
        setTimeout(function () {
            this.model.setKeyPointProperty(keyPointId, 'isPlayerPlaying', false, false);
        }.bind(this), 100);

        this.keyPointService.dispatch(keyPointId, KeyPointService.Event.CLICK, {which: 'right'});
    };

    Thread.prototype.adjustVideoSpeed = function () {
        this.videoSpeed = (this.videoSpeed + 1) % 3;
        switch (this.videoSpeed) {
            case 0:
                return {value: 1.0, image: this.options.player.speedImages.normal};
            case 1:
                return {value: 2.0, image: this.options.player.speedImages.fast};
            case 2:
                return {value: 0.5, image: this.options.player.speedImages.slow};
            default:
                return {value: 1.0, image: this.options.player.speedImages.normal};
        }
    };

    Thread.prototype.delete = function () {
        return ThreadFactory.delete(this.model)
            .done(function (data) {
                window.location.assign(data.redirect_url);
            });
    };

    return Thread;
});

define('component/accessTypeComponent',[],function () {
    

    var AccessTypeComponent = function (options) {
        this.options = options;
        this.typeToFieldMap = [
            {filter: '[value="4"]', fields: ['accessType_data_users']},
            {filter: '[value="6"]', fields: ['group']}
        ];

        this.bind__onChangeAccessType = this._onChangeAccessType.bind(this);

        this.$container = this.options.$container;
        this.$accessTypes = this.$container.find('input:radio');

        this.$accessTypes.on('change', this.bind__onChangeAccessType);

        this.$accessTypes.filter(':checked').trigger('change');
    };

    AccessTypeComponent.TAG = 'AccessTypeComponent';

    AccessTypeComponent.prototype._getFormField = function (fieldName) {
        return this.$container.find('[id$="_' + fieldName + '"]');
    };

    AccessTypeComponent.prototype._onChangeAccessType = function (e) {
        var selected = $(e.target);
        var toggleField = function (accessType, field) {
            var parent = field.parent();

            if (selected.attr('id') == accessType.attr('id')) {
                parent.find('label').addClass('required');
                field.attr('required', true);
                parent.children().show();
            } else {
                parent.find('label').removeClass('required');
                field.attr('required', false);
                parent.children().hide();
            }
        };

        $.each(this.typeToFieldMap, function (index, element) {
            var accessType = this.$accessTypes.filter(element.filter);
            $.each(element.fields, function (i, e) {
                toggleField(accessType, this._getFormField(e));
            }.bind(this));
        }.bind(this));
    };

    AccessTypeComponent.render = function ($form, options) {
        var defaults = {};

        options = options || defaults;
        for (var o in defaults) {
            options[o] = typeof options[o] != 'undefined' ? options[o] : defaults[o];
        }

        options.$container = $form;

        return new AccessTypeComponent(options);
    };

    return AccessTypeComponent;
});

define('component/tableComponent',[
    'core/subscriber',
    'extra'
], function (Subscriber) {
    

    var TableComponent = function (options) {
        Subscriber.prototype.constructor.apply(this);

        this.options = options;

        this.bind__onClickToggleSelection = this._onClickToggleSelection.bind(this);
        this.bind__onClickBulkAction = this._onClickBulkAction.bind(this);
        this.bind__onChangeItems = this._onChangeItems.bind(this);

        this.$table = this.options.$table;
        this.$toggleSelection = this.$table.find(TableComponent.Binder.TOGGLE_SELECTION);
        this.$bulkActions = this.$table.find(TableComponent.Binder.BULK_ACTION);
        this.$items = this.$table.find(TableComponent.Binder.ITEM);

        this.setMultiSelect(this.options.multiSelect);
        this.$toggleSelection.on('click', this.bind__onClickToggleSelection);

        this.$bulkActions.on('click', this.bind__onClickBulkAction);
        this.$items.on('change', this.bind__onChangeItems);
    };

    TableComponent.extend(Subscriber);

    TableComponent.TAG = 'TableComponent';

    TableComponent.Binder = {
        TOGGLE_SELECTION: '.table-component-toggle-selection',
        BULK_ACTION: '.table-component-bulk-action',
        ITEM: 'input.table-component-item:not([disabled])'
    };

    TableComponent.Event = {
        SELECTION_CHANGE: 'eventSelectionChange',
        CLICK_BULK_ACTION: 'eventClickBulkAction'
    };
    
    TableComponent.prototype.updateElements = function (elements) {
	this.$items.off('change');
	this.$table = elements;
	this.$items = this.$table.find(TableComponent.Binder.ITEM);
	this.$items.on('change', this.bind__onChangeItems);
    }

    TableComponent.prototype._dispatchToggleEvent = function (e) {
        var $checked = this.getSelection();
        this._dispatch(TableComponent.Event.SELECTION_CHANGE, {
            $selection: $checked,
            tableComponent: this,
            parentEvent: e
        });
    };

    TableComponent.prototype._onClickToggleSelection = function (e) {
        this.$items.prop('checked', $(e.target).is('input[type="checkbox"]') ? e.target.checked : this.getSelection().length == 0);
        this._dispatchToggleEvent(e);
    };

    TableComponent.prototype._onClickBulkAction = function (e) {
        var $bulkAction = $(e.currentTarget);
        this._dispatch(TableComponent.Event.CLICK_BULK_ACTION, {
            $bulkAction: $bulkAction,
            action: $bulkAction.data('action'),
            $selection: this.getSelection(),
            tableComponent: this,
            parentEvent: e
        });
    };

    TableComponent.prototype._onChangeItems = function (e) {
        var isChecked = e.target.checked;
        if (!this.options.multiSelect) {
            this.$items.prop('checked', false);
        }
        e.target.checked = isChecked;

        var $checked = this.getSelection();
        this._dispatch(TableComponent.Event.SELECTION_CHANGE, {
            $selection: $checked,
            tableComponent: this,
            parentEvent: e
        });

        var allChecked = $checked.length == this.$items.length;
        this.$toggleSelection.prop('checked', allChecked);
        if (allChecked) {
            this._dispatchToggleEvent(e);
        }
    };

    TableComponent.prototype.setMultiSelect = function (multiSelect) {
        this.options.multiSelect = multiSelect;
        this.$toggleSelection.attr('disabled', !this.options.multiSelect);
    };

    TableComponent.prototype.getTable = function () {
        return this.$table;
    };

    TableComponent.prototype.getBulkActions = function () {
        return this.$bulkActions;
    };

    TableComponent.prototype.getSelection = function () {
        return this.$items.filter(':checked');
    };

    TableComponent.table = function ($table, options) {
        var defaults = {
            multiSelect: true
        };

        options = options || defaults;
        for (var o in defaults) {
            options[o] = typeof options[o] != 'undefined' ? options[o] : defaults[o];
        }

        options.$table = $table;

        return new TableComponent(options);
    };

    return TableComponent;
});

define('component/myFilesSelectorComponent',[
    'core/subscriber',
    'service',
    'component/tableComponent',
    'factory/mediaFactory',
    'extra'
], function (Subscriber, Service, TableComponent, MediaFactory) {
    

    var MyFilesSelectorComponent = function (options) {
        Subscriber.prototype.constructor.apply(this);

        this.options = options;

        this.bind__onShownModal = this._onShownModal.bind(this);
        this.bind__onHiddenModal = this._onHiddenModal.bind(this);
        this.bind__onLoadPageSuccess = this._onLoadPageSuccess.bind(this);
        this.bind__onLoadPageError = this._onLoadPageError.bind(this);
        this.bind__onClickSelectSelected = this._onClickSelectSelected.bind(this);
        this.bind__onMyFilesListViewViewLoaded = this._onMyFilesListViewViewLoaded.bind(this);
        this.bind__onSelectionChange = this._onSelectionChange.bind(this);

        this.$container = this.options.$container;
        this.$modalDialog = this.$container.find(MyFilesSelectorComponent.Binder.MODAL_DIALOG);
        this.$selectSelected = this.$container.find(MyFilesSelectorComponent.Binder.SELECT_SELECTED);

        this.$modalDialog.modal({backdrop: 'static', show: false});
        this.$modalDialog.on('shown.bs.modal', this.bind__onShownModal);
        this.$modalDialog.on('hidden.bs.modal', this.bind__onHiddenModal);
        this.$selectSelected.on('click', this.bind__onClickSelectSelected);

        var sub = Service.get('subscriber');
        sub.subscribe('onViewLoaded', this.bind__onMyFilesListViewViewLoaded);
    };

    MyFilesSelectorComponent.extend(Subscriber);

    MyFilesSelectorComponent.TAG = 'MyFilesSelectorComponent';

    MyFilesSelectorComponent.Binder = {
        MODAL_DIALOG: '.my-files-selector-modal',
        SELECT_SELECTED: '.my-files-selector-select-selected'
    };

    MyFilesSelectorComponent.Event = {
        DONE: 'eventDone',
        HIDDEN: 'eventHidden'
    };

    MyFilesSelectorComponent.prototype._onSelectionChange = function (e) {
        this.$selectSelected.attr('disabled', e.$selection.length == 0);
    };

    MyFilesSelectorComponent.prototype._onMyFilesListViewViewLoaded = function (e) {
        var MyFilesListView = require('views/myFiles/listView');

        if (e.tag !== MyFilesListView.TAG) {
            return;
        }

        this.tblCmp = e.view.getTableComponent();
        this.tblCmp.setMultiSelect(this.options.multiSelect);
        this.tblCmp.subscribe(TableComponent.Event.SELECTION_CHANGE, this.bind__onSelectionChange);

        var urlOverride = (function (e) {
            e.preventDefault();
            this._loadPage($(e.currentTarget).attr('href'));
        }).bind(this);

        this.$modalDialog.find(MyFilesListView.Binder.TOGGLE_STYLE).on('click', urlOverride);

        // KnpPaginatorBundle:Pagination:twitter_bootstrap_v3_pagination.html.twig
        // override pagination urls
        this.$modalDialog.find('ul.pagination li a').on('click', urlOverride);
    };

    MyFilesSelectorComponent.prototype._onLoadPageSuccess = function (data, textStatus, jqXHR) {
        this.$modalDialog.find('.modal-body').html(data.page);
    };

    MyFilesSelectorComponent.prototype._onLoadPageError = function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
    };

    MyFilesSelectorComponent.prototype._makeUrl = function (url) {
        if (typeof url === 'undefined')
            return Routing.generate('imdc_myfiles_list', this.options.filter);

        for (var key in this.filter) {
            url += (url.substr('?') ? '&' : '?') + key + '=' + this.options.filter[key];
        }

        return url;
    };

    MyFilesSelectorComponent.prototype._loadPage = function (url) {
        $.ajax({
            url: this._makeUrl(url),
            success: this.bind__onLoadPageSuccess,
            error: this.bind__onLoadPageError
        });
    };

    MyFilesSelectorComponent.prototype._onShownModal = function (e) {
        this._loadPage();
    };

    MyFilesSelectorComponent.prototype._onHiddenModal = function (e) {
        this._dispatch(MyFilesSelectorComponent.Event.HIDDEN, {
            myFilesSelectorComponent: this
        });
    };

    MyFilesSelectorComponent.prototype._onClickSelectSelected = function (e) {
        e.preventDefault();

        this.$selectSelected.button('loading');

        var selectedFiles = this.tblCmp.getSelection();
        var mediaIds = [];

        if (this.options.multiSelect) {
            selectedFiles.each(function (index, element) {
                mediaIds.push($(element).data('mid'));
            });
        } else {
            mediaIds.push(selectedFiles.first().data('mid'));
        }

        MediaFactory.list(mediaIds)
            .done(function (data) {
                this._dispatch(MyFilesSelectorComponent.Event.DONE, {
                    media: data.media,
                    myFilesSelectorComponent: this
                });
            }.bind(this))
            .fail(function (data) {
                this.$selectSelected.button('reset');

                console.error('%s: media factory list', MyFilesSelectorComponent.TAG);
            }.bind(this));
    };

    MyFilesSelectorComponent.prototype.show = function () {
        this.$modalDialog.modal('show');
    };

    MyFilesSelectorComponent.prototype.hide = function () {
        this.$modalDialog.modal('hide');
    };

    MyFilesSelectorComponent.prototype.destroy = function () {
        this.$modalDialog.remove();
    };

    MyFilesSelectorComponent.render = function (options, callback) {
        var defaults = {
            $container: $('body'),
            multiSelect: true,
            filter: {}
        };

        options = options || defaults;
        for (var o in defaults) {
            options[o] = typeof options[o] != 'undefined' ? options[o] : defaults[o];
        }

        dust.render('myFilesSelector', {}, function (err, out) {
            options.$container.append(out);

            var cmp = new MyFilesSelectorComponent(options);
            callback.call(cmp, {
                myFilesSelectorComponent: cmp
            });
        });
    };

    return MyFilesSelectorComponent;
});

define('component/recorderComponent',[
    'core/subscriber',
    'service',
    'component/myFilesSelectorComponent',
    'model/mediaModel',
    'factory/mediaFactory',
    'factory/myFilesFactory',
    'service/rabbitmqWebStompService',
    'core/helper',
    'extra'
], function (Subscriber, Service, MyFilesSelectorComponent, MediaModel, MediaFactory, MyFilesFactory,
             RabbitmqWebStompService, Helper) {
    

    var RecorderComponent = function (options) {
        Subscriber.prototype.constructor.apply(this);

        this.options = options;
        this.isOnNormalTab = this.options.tab == RecorderComponent.Tab.NORMAL;
        this.isOnInterpTab = this.options.tab == RecorderComponent.Tab.INTERPRETATION;
        this.isInRecordMode = this.options.mode == RecorderComponent.Mode.RECORD;
        this.isInEditMode = this.options.mode == RecorderComponent.Mode.PREVIEW;
        this.player = null;
        this.recorder = null;
        this.currentRecording = null;
        this.currentTrim = null;
        this.sourceMedia = null;
        this.recordedMedia = null;
        this.messages = [];
        this.rabbitmqWebStompService = Service.get('rabbitmqWebStomp');
        this.isDonePostponed = false;
        this.doPost = false;

        this.forwardButton = '<button class="forwardButton"></button>';
        this.doneButton = '<button class="doneButton"></button>';
        this.doneAndPostButton = '<button class="doneAndPostButton"></button>';
        this.backButton = '<button class="backButton"></button>';

        this.bind__onShownModal = this._onShownModal.bind(this);
        this.bind__onShowModal = this._onShowModal.bind(this);
        this.bind__onHiddenModal = this._onHiddenModal.bind(this);
        this.bind__onShowTab = this._onShowTab.bind(this);
        this.bind__onShownTab = this._onShownTab.bind(this);
        this.bind__onBlurPlayerTitle = this._onBlurPlayerTitle.bind(this);
        this.bind__onClickInterpSelect = this._onClickInterpSelect.bind(this);
        this.bind__onPageAnimate = this._onPageAnimate.bind(this);
        this.bind__onRecordingStarted = this._onRecordingStarted.bind(this);
        this.bind__onRecordingStopped = this._onRecordingStopped.bind(this);
        this.bind__onClickTrim = this._onClickTrim.bind(this);
        this.bind__onClickBack = this._onClickBack.bind(this);
        this.bind__onClickDone = this._onClickDone.bind(this);
        this.bind__onClickDoneAndPost = this._onClickDoneAndPost.bind(this);

        this.$container = this.options.$container;
        this.$modalDialog = this.$container.find(RecorderComponent.Binder.MODAL_DIALOG);
        this.$tabPanes = this.$container.find(RecorderComponent.Binder.TAB_PANES);
        this.$containerRecord = this.$container.find(RecorderComponent.Binder.CONTAINER_RECORD);
        this.$normalTitle = this.$container.find(RecorderComponent.Binder.NORMAL_TITLE);
        this.$normalVideo = this.$container.find(RecorderComponent.Binder.NORMAL_VIDEO);
        this.$interpSelect = this.$container.find(RecorderComponent.Binder.INTERP_SELECT);
        this.$interpMain = this.$container.find(RecorderComponent.Binder.INTERP_MAIN);
        this.$interpVideoP = this.$container.find(RecorderComponent.Binder.INTERP_VIDEO_P);
        this.$interpTitle = this.$container.find(RecorderComponent.Binder.INTERP_TITLE);
        this.$interpVideoR = this.$container.find(RecorderComponent.Binder.INTERP_VIDEO_R);
        this.$controls = this.$container.find(RecorderComponent.Binder.CONTROLS);
        this.$containerUpload = this.$container.find(RecorderComponent.Binder.CONTAINER_UPLOAD);

        var tab = this.isOnNormalTab
            ? RecorderComponent.Binder.NORMAL
            : RecorderComponent.Binder.INTERP;
        this.$modalDialog.find('a[href!="' + tab + '"]').parent().removeClass('active');
        this.$modalDialog.find('a[href="' + tab + '"]').parent().addClass('active');
        this.$modalDialog.find('.tab-pane:not("' + tab + '")').removeClass('active');
        this.$modalDialog.find('.tab-pane' + tab).addClass('active');
        this.$modalDialog.modal({backdrop: 'static', show: false});

        this.$modalDialog.on('shown.bs.modal', this.bind__onShownModal);
        this.$modalDialog.on('show.bs.modal', this.bind__onShowModal);
        this.$modalDialog.on('hidden.bs.modal', this.bind__onHiddenModal);
        this.$tabPanes.on('show.bs.tab', this.bind__onShowTab);
        this.$tabPanes.on('shown.bs.tab', this.bind__onShownTab);
        this.$interpSelect.on('click', this.bind__onClickInterpSelect);
        // prevent media renaming if in record mode
        if (this.isInEditMode) {
            this.$normalTitle.blur(this.bind__onBlurPlayerTitle);
            this.$interpTitle.blur(this.bind__onBlurPlayerTitle);
        }

        this._clearCurrents();
        this._setupRabbitmq();

        //TODO interp preview/edit/trim
        if (this.isInEditMode) {
            this.$modalDialog.find('a[href="' + RecorderComponent.Binder.INTERP + '"]').hide();
        }
    };

    RecorderComponent.extend(Subscriber);

    RecorderComponent.TAG = 'RecorderComponent';

    RecorderComponent.MAX_RECORDING_TIME = 720; // 12 Minutes

    RecorderComponent.Binder = {
        MODAL_DIALOG: '.recorder-modal',
        TAB_PANES: 'a[data-toggle="tab"]',
        NORMAL: '.recorder-normal',
        INTERP: '.recorder-interp',

        CONTAINER_RECORD: '.recorder-container-record',
        NORMAL_TITLE: '.recorder-normal-title',
        NORMAL_VIDEO: '.recorder-normal-video',
        INTERP_SELECT: '.recorder-interp-select',
        INTERP_MAIN: '.recorder-interp-main',
        INTERP_VIDEO_P: '.recorder-interp-video-p',
        INTERP_TITLE: '.recorder-interp-title',
        INTERP_VIDEO_R: '.recorder-interp-video-r',
        CONTROLS: '.recorder-controls',

        CONTAINER_UPLOAD: '.recorder-container-upload'
    };

    RecorderComponent.Tab = {
        NORMAL: 0,
        INTERPRETATION: 1
    };

    RecorderComponent.Mode = {
        RECORD: 0,
        PREVIEW: 1
    };

    RecorderComponent.Event = {
        DONE: 'eventDone',
        HIDDEN: 'eventHidden'
    };

    RecorderComponent.prototype._setupRabbitmq = function () {
        // listen for status updates
        this.bind__onMessage = function (e) {
            console.log(e.message);

            // store the messages, since a request (_onRecordingSuccess) may not have completed yet
            this.messages.push(e.message);

            this._checkMessages();
        }.bind(this);
        this.bind__onConnect = function (e) {
            this.subscription = this.rabbitmqWebStompService.subscribe(
                '/exchange/'+window.parameters.prefix+'entity-status',
                RabbitmqWebStompService.Event.MESSAGE, this.bind__onMessage);
        }.bind(this);

        if (!this.rabbitmqWebStompService.isConnected()) {
            this.rabbitmqWebStompService.subscribe(
                null,
                RabbitmqWebStompService.Event.CONNECTED, this.bind__onConnect);
            this.rabbitmqWebStompService.connect();
        } else {
            this.bind__onConnect(null);
        }
    };

    RecorderComponent.prototype._cleanupRabbitmq = function () {
        this.rabbitmqWebStompService.unsubscribe(this.subscription, this.bind__onMessage);
        this.rabbitmqWebStompService.unsubscribe(null, this.bind__onConnect);
    };

    RecorderComponent.prototype._createPlayer = function (inPreviewMode, wasRecording) {
        console.log('%s: %s', RecorderComponent.TAG, '_createPlayer');

        inPreviewMode = typeof inPreviewMode != 'undefined' ? inPreviewMode : false;
        wasRecording = typeof wasRecording != 'undefined' ? wasRecording : false;

        var forwardButtons = [];
        var forwardFunctions = [];
        if (this.isOnNormalTab || inPreviewMode) {
            forwardButtons.push(this.doneButton);
            forwardFunctions.push(inPreviewMode ? this.bind__onClickTrim : this.bind__onClickDone);
        }
        if (this.options.enableDoneAndPost && inPreviewMode) {
            forwardButtons.push(this.doneAndPostButton);
            forwardFunctions.push(this.bind__onClickDoneAndPost);
        }

        var backButtons;
        var backFunctions;
        if (inPreviewMode && wasRecording) {
            backButtons = [this.backButton];
            backFunctions = [this.bind__onClickBack];
        }

        var container = this.isOnNormalTab
            ? this.$normalVideo
            : inPreviewMode ? this.$interpVideoR : this.$interpVideoP;

        var player = new Player(container, {
            areaSelectionEnabled: inPreviewMode,
            audioBar: false,
            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_RELATIVE,
            controlBarElement: this.$controls,
            forwardButtons: forwardButtons,
            forwardFunctions: forwardFunctions,
            backButtons: backButtons,
            backFunctions: backFunctions,
            selectedRegionColor: '#0000ff'
        });
        player.createControls();

        $(player.elementID)
            .find('.videoControlsContainer.controlsBar.doneButton').eq(0)
            .attr('title', Translator.trans('player.previewing.doneButton'));

        $(player.elementID)
            .find('.videoControlsContainer.controlsBar.doneAndPostButton').eq(0)
            .attr('title', Translator.trans('player.previewing.doneAndPostButton'));

        $(player.elementID)
            .find('.videoControlsContainer.controlsBar.backButton').eq(0)
            .attr('title', Translator.trans('player.previewing.backToRecordingButton'));

        if (inPreviewMode) {
            this.recorder = player;
        } else {
            this.player = player;
        }
    };

    RecorderComponent.prototype._onClickTrim = function (e) {
        console.log('%s: %s', RecorderComponent.TAG, '_onClickTrim');
        e.preventDefault();

        var previousMinMaxTimes = this.recorder.getCurrentMinMaxTime();
        var currentMinMaxTimes = this.recorder.getAreaSelectionTimes();
        this.recorder.setCurrentMinMaxTime(currentMinMaxTimes.minTime, currentMinMaxTimes.maxTime);

        console.log('Current Min/Max Times %s %s', currentMinMaxTimes.minTime, currentMinMaxTimes.maxTime);
        console.log('Cutting to Min/Max Times %s %s', currentMinMaxTimes.minTime - previousMinMaxTimes.minTime,
            currentMinMaxTimes.maxTime - previousMinMaxTimes.minTime);

        // a trim request will be attempted only if the recording is successfully uploaded
        this.currentTrim = {
            startTime: currentMinMaxTimes.minTime - previousMinMaxTimes.minTime,
            endTime: currentMinMaxTimes.maxTime - previousMinMaxTimes.minTime
        };

        //cut should always also call the done function
        this._onClickDone(e);
    };

    RecorderComponent.prototype._onClickBack = function (e) {
        console.log('%s: %s', RecorderComponent.TAG, '_onClickBack');
        e.preventDefault();

        this._destroyPlayers();

        // Go back to recording
        this._clearCurrents();
        this.setRecordedMedia(null);
        this.options.mode = RecorderComponent.Mode.RECORD;
        if (this.isOnInterpTab)
            this._createPlayer();
        this._createRecorder();
    };

    RecorderComponent.prototype._createRecorder = function () {
        console.log('%s: %s', RecorderComponent.TAG, '_createRecorder');

        var container = this.isOnNormalTab
            ? this.$normalVideo
            : this.$interpVideoR;

        this.recorder = new Player(container, {
            areaSelectionEnabled: false,
            audioBar: false,
            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE,
            controlBarElement: this.$controls,
            type: Player.DENSITY_BAR_TYPE_RECORDER,
            volumeControl: false,
            maxRecordingTime: RecorderComponent.MAX_RECORDING_TIME
        });
        $(this.recorder).on(Player.EVENT_RECORDING_STARTED, this.bind__onRecordingStarted);
        $(this.recorder).on(Player.EVENT_RECORDING_STOPPED, this.bind__onRecordingStopped);
        this.recorder.createControls();

        $(this.recorder.elementID)
            .find('.videoControlsContainer.controlsBar.videoControls.recordButton').eq(0)
            .attr('title', Translator.trans('player.recording.recordingButton'));

        $(this.recorder.elementID)
            .find('.videoControlsContainer.controlsBar.forwardButton').eq(0)
            .attr('title', Translator.trans('player.recording.forwardButton'));

        $(this.recorder.elementID)
            .find('.videoControlsContainer.controlsBar.doneButton').eq(0)
            .attr('title', Translator.trans('player.recording.doneButton'));

        $(this.recorder.elementID)
            .find('.videoControlsContainer.controlsBar.doneAndPostButton').eq(0)
            .attr('title', Translator.trans('player.recording.doneAndPostButton'));
    };

    RecorderComponent.prototype._onRecordingStarted = function () {
        console.log('%s: %s', RecorderComponent.TAG, '_onRecordingStarted');

        this._clearCurrents();

        if (this.isOnInterpTab) {
            this.currentRecording.sourceStartTime = this.player.getCurrentTime();
            this.player.setControlsEnabled(false);
            this.player.play();
        }
    };

    RecorderComponent.prototype._onRecordingStopped = function () {
        this.currentRecording.video = this.recorder.recordVideo.getBlob();
//        this.currentRecording.audio = this.recorder.recordAudio.getBlob();

        if (this.isOnInterpTab) {
            this.player.pause();
            this.player.setControlsEnabled(true);
        }

        this._destroyPlayers();

        this._injectCurrentRecording(this.isOnNormalTab
            ? this.$normalVideo
            : this.$interpVideoR);

        if (this.isOnInterpTab)
            this._createPlayer();
        this.options.mode = RecorderComponent.Mode.PREVIEW;
        this._createPlayer(true, true);
    };

    RecorderComponent.prototype._checkMessages = function () {
        if (!this.tempMedia)
            return;

        while (this.messages.length > 0) {
            var message = this.messages.pop();

            if (message.who.indexOf('\MultiplexConsumer') > -1 &&
                (message.what.indexOf('\Media') > -1 || message.what.indexOf('\Interpretation')) &&
                message.identifier == this.tempMedia.get('id')
            ) {
                if (message.status != 'Done') {
                    this.$containerUpload.find('label').eq(0).html(message.status + '...');
                } else {
                    console.log('done');
                    this.$containerUpload.find('label').eq(0).html('Cleaning up...');

                    MediaFactory.get(this.tempMedia.get('id'))
                        .done(function (data) {
                            this.tempMedia = null;
                            this.setRecordedMedia(data.media);

                            // was waiting for us?
                            if (this.isDonePostponed) {
                                setTimeout(function () {
                                    this._dispatchDone();
                                }.bind(this), 2000);
                            }
                        }.bind(this))
                        .fail(function (data) {
                            //TODO
                        });
                }
            }
        }
    };

    RecorderComponent.prototype._addRecording = function () {
        if (this.currentRecording.video == null && this.currentRecording.audio == null)
            return null;

        var params = {
            video: this.currentRecording.video,
//            audio: this.currentRecording.audio,
            title: this._getCurrentTitleElement().val()
        };

        if (this.isOnInterpTab) {
            params.isInterpretation = true;
            params.sourceStartTime = this.currentRecording.sourceStartTime;
            params.sourceId = this.sourceMedia.get('id');
        }

        //TODO progress updater
        this.$containerUpload.find('label').eq(0).html('Uploading recording...');

        return MyFilesFactory.addRecording(params)
            .progress(function (percent) {
                Helper.updateProgressBar(this.$containerUpload, percent);
            }.bind(this))
            .done(function (data) {
                console.log(data.media);

                if (data.media.get('state') == 2) {
                    this.setRecordedMedia(data.media);
                } else {
                    console.log('waiting for multiplex consumer');

                    //TODO progress updater
                    this.$containerUpload.find('.progress-bar').eq(0)
                        .addClass('progress-bar-striped active')
                        .html('');
                    this.$containerUpload.find('label').eq(0).html('Processing...');

                    this.tempMedia = data.media;
                    this._checkMessages();
                }
            }.bind(this))
            .fail(function () {
                //TODO
            });
    };

    RecorderComponent.prototype._trim = function (media) {
        if (this.currentTrim.startTime == null || this.currentTrim.endTime == null)
            return null;

        //TODO progress updater
        this.$containerUpload.find('label').eq(0).html('Queuing trim...');

        return MediaFactory.trim(media, this.currentTrim.startTime, this.currentTrim.endTime)
            .fail(function () {
                //TODO
            });
    };

    RecorderComponent.prototype._dispatchDone = function () {
        this._dispatch(RecorderComponent.Event.DONE, {
            media: this.recordedMedia,
            doPost: this.doPost,
            recorderComponent: this
        });
    };

    RecorderComponent.prototype._onClickDone = function (e) {
        console.log('%s: %s', RecorderComponent.TAG, '_onClickDone');
        e.preventDefault();

        this.$containerRecord.hide();
        this.$containerUpload.show();
        this._destroyPlayers();

        var done = function () {
            //TODO progress updater
            this.$containerUpload.find('label').eq(0).html('Cleaning up...');

            // if recorded media is not set, then
            // we're waiting to hear from the multiplex consumer
            if (this.recordedMedia) {
                setTimeout(function () {
                    this._dispatchDone();
                }.bind(this), 2000);
            } else {
                this.isDonePostponed = true;
            }
        }.bind(this);

        var arDone = function (media) {
            // send trim request, if any
            var tp = this._trim(media);
            if (!tp) {
                // nothing to trim, so finish up
                done();
                return;
            }

            tp.done(function (data) {
                // finish up
                done();
            });
        }.bind(this);

        var arp = this._addRecording();
        if (!arp) {
            // no recording to upload. go on to trim if in edit mode and recoding is set
            if (this.isInEditMode && this.recordedMedia) {
                //TODO progress updater
                Helper.updateProgressBar(this.$containerUpload, 100);
                this.$containerUpload.find('.progress-bar').eq(0)
                    .addClass('progress-bar-striped active')
                    .html('');
                arDone(this.recordedMedia);
            }
            return;
        }

        arp.done(function (data) {
            // go on to trim
            arDone(data.media);
        });
    };

    RecorderComponent.prototype._onClickDoneAndPost = function (e) {
        this.doPost = true;
        this._onClickDone(e);
    };

    RecorderComponent.prototype._clearCurrents = function () {
        this.currentRecording = {video: null, audio: null, sourceStartTime: null};
        this.currentTrim = {startTime: null, endTime: null};
    };

    RecorderComponent.prototype._destroyPlayers = function () {
        console.log('%s: %s', RecorderComponent.TAG, '_destroyPlayers');

        var reset = function ($normal) {
            var $old = $normal.parent();
            /*$.each($normal.find('source'), function (index, element) {
             var e = $(element);
             if (e.attr('src').indexOf('blob:') == 0)
             URL.revokeObjectURL(e.attr('src'));
             });*/
            $normal.removeAttr('src');
            $old.hide();
            $old.after($normal.detach());
            $old.remove();
        }.bind(this);

        if (this.player != null) {
            this.player.destroyRecorder();
            if (this.isOnNormalTab) {
                reset(this.$normalVideo);
            } else {
                if (this.sourceMedia != null)
                    reset(this.$interpVideoP);
            }
            this.player = null;
        }

        if (this.recorder != null) {
            this.recorder.destroyRecorder();
            if (this.isOnNormalTab) {
                reset(this.$normalVideo);
            } else {
                if (this.sourceMedia != null)
                    reset(this.$interpVideoR);
            }
            this.recorder = null;
        }

        this.$controls.html('');
    };

    RecorderComponent.prototype._onPageAnimate = function () {
        try {
            if (this.isInEditMode && this.recordedMedia != null) {
                if (this.isOnInterpTab && this.sourceMedia != null) {
                    return; //TODO interp preview/edit/trim
                }

                this._createPlayer(true);
            } else {
                if (this.isOnInterpTab) {
                    if (this.sourceMedia != null) {
                        this._createPlayer();
                    } else {
                        return; // don't create the recorder
                    }
                }

                this._createRecorder();
            }
        } catch (err) {
            console.error('%s: %s- err=%o', RecorderComponent.TAG, '_loadPage', err);
        }
    };

    RecorderComponent.prototype._loadPage = function () {
        if (this.isOnInterpTab) {
            if (this.sourceMedia != null) {
                this.$interpSelect.parent().hide();
                this.$interpMain.show();
            } else {
                this.$interpSelect.parent().show();
                this.$interpMain.hide();
            }
        }

        this.$modalDialog.find('.modal-dialog').animate({
            //width: this.page == Recorder.Page.NORMAL ? "900px" : (this.sourceMedia != null ? "90%" : "900px") //FIXME use css classes

            // 256px are needed for all the other things in the window except the video. Then using 4*3 aspect ratio we
            // set the width of the pop-up
            width: 4 * ($(window).height() * 0.9 - 256) / 3
        }, {
            complete: this.bind__onPageAnimate
        });
    };

    RecorderComponent.prototype._onShownModal = function (e) {
        this._destroyPlayers();
        this._loadPage();
    };
    RecorderComponent.prototype._onShowModal = function (e) {
	// Width needs to be kept consistent with the loadPage modal animation width to avoid too many sizings of the modal
	 this.$modalDialog.find('.modal-dialog').width(4 * ($(window).height() * 0.9 - 256) / 3);
    };

    RecorderComponent.prototype._onHiddenModal = function (e) {
        this._destroyPlayers();

        if (this.paused)
            return;

        this._dispatch(RecorderComponent.Event.HIDDEN, {
            recorderComponent: this
        });
    };

    RecorderComponent.prototype._onShowTab = function (e) {
        this._destroyPlayers();

        this.options.tab = $(e.target).attr('href') == RecorderComponent.Binder.NORMAL
            ? RecorderComponent.Tab.NORMAL
            : RecorderComponent.Tab.INTERPRETATION;

        this.isOnNormalTab = this.options.tab == RecorderComponent.Tab.NORMAL;
        this.isOnInterpTab = this.options.tab == RecorderComponent.Tab.INTERPRETATION;

        this._loadPage();
    };

    RecorderComponent.prototype._onShownTab = function (e) {
        this.$interpVideoP[0].load();
    };

    RecorderComponent.prototype._onBlurPlayerTitle = function (e) {
        console.log('updated title');
        this.recordedMedia.set('title', $(e.target).val());

        MediaFactory.edit(this.recordedMedia);
    };

    RecorderComponent.prototype._onClickInterpSelect = function (e) {
        e.preventDefault();

        MyFilesSelectorComponent.render({
            multiSelect: false,
            filter: {type: 1}
        }, function (e) {
            this.mfsCmp = e.myFilesSelectorComponent;
            this.mfsCmp.subscribe(MyFilesSelectorComponent.Event.DONE, (function (e) {
                this.mfsCmp.hide();
                this.setSourceMedia(e.media[0]);
            }).bind(this));
            this.mfsCmp.subscribe(MyFilesSelectorComponent.Event.HIDDEN, (function (e) {
                this.mfsCmp.destroy();
                this.show();
                this.paused = false;
            }).bind(this));

            this.paused = true;
            this.hide();
            this.mfsCmp.show();
        }.bind(this));
    };

    RecorderComponent.prototype.show = function () {
        this.$modalDialog.modal('show');
    };

    RecorderComponent.prototype.hide = function () {
        this.$modalDialog.modal('hide');
    };

    RecorderComponent.prototype.destroy = function () {
        this._clearCurrents();
        this._cleanupRabbitmq();

        this.$modalDialog.remove();
    };

    RecorderComponent.prototype._injectCurrentRecording = function (element) {
        //FIXME only video will play audio would need its own element and then playback needs to synced
        //TODO test with firefox
        var options = {
            resources: [
                {web_path: URL.createObjectURL(this.currentRecording.video)},
//                {web_path: URL.createObjectURL(this.currentRecording.audio)}
            ]
        };

        dust.render('recorder_source', options, function (err, out) {
            element.html(out);
        });
    };

    RecorderComponent.prototype._injectMedia = function (element, media) {
        var options = {
            resources: this.isInRecordMode
                ? [media.get('source_resource')]
                : media.get('resources')
        };

        dust.render('recorder_source', options, function (err, out) {
            element.html(out);
        });
    };

    RecorderComponent.prototype._getCurrentTitleElement = function () {
        return this.isOnNormalTab ? this.$normalTitle : this.$interpTitle;
    };

    RecorderComponent.prototype.setSourceMedia = function (media) {
        this.sourceMedia = media;

        if (this.sourceMedia != null) {
            this._injectMedia(this.$interpVideoP, this.sourceMedia);
            this._getCurrentTitleElement().val(this.sourceMedia.get('title'));
        }

        this._destroyPlayers();
        this._loadPage();
    };

    RecorderComponent.prototype.setRecordedMedia = function (media) {
        this.recordedMedia = media;

        if (this.recordedMedia != null) {
            this._injectMedia(this.isOnNormalTab
                    ? this.$normalVideo
                    : this.$interpVideoR,
                this.recordedMedia);

            this._getCurrentTitleElement().val(this.recordedMedia.get('title'));
        }
    };

    RecorderComponent.render = function (options, callback) {
        var defaults = {
            $container: $('body'),
            tab: RecorderComponent.Tab.NORMAL,
            mode: RecorderComponent.Mode.RECORD,
            enableDoneAndPost: false
        };

        options = options || defaults;
        for (var o in defaults) {
            options[o] = typeof options[o] != 'undefined' ? options[o] : defaults[o];
        }

        dust.render('recorder', {}, function (err, out) {
            options.$container.append(out);

            var cmp = new RecorderComponent(options);
            callback.call(cmp, {
                recorderComponent: cmp
            });
        });
    };

    return RecorderComponent;
});

/*! Sortable 1.1.1 - MIT | git://github.com/rubaxa/Sortable.git */
!function(a){"function"==typeof define&&define.amd?define('Sortable',a):"undefined"!=typeof module&&"undefined"!=typeof module.exports?module.exports=a():"undefined"!=typeof Package?Sortable=a():window.Sortable=a()}(function(){function a(a,b){this.el=a,this.options=b=b||{};var d={group:Math.random(),sort:!0,disabled:!1,store:null,handle:null,scroll:!0,scrollSensitivity:30,scrollSpeed:10,draggable:/[uo]l/i.test(a.nodeName)?"li":">*",ghostClass:"sortable-ghost",ignore:"a, img",filter:null,animation:0,setData:function(a,b){a.setData("Text",b.textContent)},dropBubble:!1,dragoverBubble:!1};for(var e in d)!(e in b)&&(b[e]=d[e]);var g=b.group;g&&"object"==typeof g||(g=b.group={name:g}),["pull","put"].forEach(function(a){a in g||(g[a]=!0)}),M.forEach(function(d){b[d]=c(this,b[d]||N),f(a,d.substr(2).toLowerCase(),b[d])},this),b.groups=" "+g.name+(g.put.join?" "+g.put.join(" "):"")+" ",a[F]=b;for(var h in this)"_"===h.charAt(0)&&(this[h]=c(this,this[h]));f(a,"mousedown",this._onTapStart),f(a,"touchstart",this._onTapStart),f(a,"dragover",this),f(a,"dragenter",this),Q.push(this._onDragOver),b.store&&this.sort(b.store.get(this))}function b(a){s&&s.state!==a&&(i(s,"display",a?"none":""),!a&&s.state&&t.insertBefore(s,q),s.state=a)}function c(a,b){var c=P.call(arguments,2);return b.bind?b.bind.apply(b,[a].concat(c)):function(){return b.apply(a,c.concat(P.call(arguments)))}}function d(a,b,c){if(a){c=c||H,b=b.split(".");var d=b.shift().toUpperCase(),e=new RegExp("\\s("+b.join("|")+")\\s","g");do if(">*"===d&&a.parentNode===c||(""===d||a.nodeName.toUpperCase()==d)&&(!b.length||((" "+a.className+" ").match(e)||[]).length==b.length))return a;while(a!==c&&(a=a.parentNode))}return null}function e(a){a.dataTransfer.dropEffect="move",a.preventDefault()}function f(a,b,c){a.addEventListener(b,c,!1)}function g(a,b,c){a.removeEventListener(b,c,!1)}function h(a,b,c){if(a)if(a.classList)a.classList[c?"add":"remove"](b);else{var d=(" "+a.className+" ").replace(/\s+/g," ").replace(" "+b+" ","");a.className=d+(c?" "+b:"")}}function i(a,b,c){var d=a&&a.style;if(d){if(void 0===c)return H.defaultView&&H.defaultView.getComputedStyle?c=H.defaultView.getComputedStyle(a,""):a.currentStyle&&(c=a.currentStyle),void 0===b?c:c[b];b in d||(b="-webkit-"+b),d[b]=c+("string"==typeof c?"":"px")}}function j(a,b,c){if(a){var d=a.getElementsByTagName(b),e=0,f=d.length;if(c)for(;f>e;e++)c(d[e],e);return d}return[]}function k(a){a.draggable=!1}function l(){K=!1}function m(a,b){var c=a.lastElementChild,d=c.getBoundingClientRect();return b.clientY-(d.top+d.height)>5&&c}function n(a){for(var b=a.tagName+a.className+a.src+a.href+a.textContent,c=b.length,d=0;c--;)d+=b.charCodeAt(c);return d.toString(36)}function o(a){for(var b=0;a&&(a=a.previousElementSibling);)"TEMPLATE"!==a.nodeName.toUpperCase()&&b++;return b}function p(a,b){var c,d;return function(){void 0===c&&(c=arguments,d=this,setTimeout(function(){1===c.length?a.call(d,c[0]):a.apply(d,c),c=void 0},b))}}var q,r,s,t,u,v,w,x,y,z,A,B,C,D,E={},F="Sortable"+(new Date).getTime(),G=window,H=G.document,I=G.parseInt,J=!!("draggable"in H.createElement("div")),K=!1,L=function(a,b,c,d,e,f){var g=H.createEvent("Event");g.initEvent(b,!0,!0),g.item=c||a,g.from=d||a,g.clone=s,g.oldIndex=e,g.newIndex=f,a.dispatchEvent(g)},M="onAdd onUpdate onRemove onStart onEnd onFilter onSort".split(" "),N=function(){},O=Math.abs,P=[].slice,Q=[],R=p(function(a,b,c){if(c&&b.scroll){var d,e,f,g,h=b.scrollSensitivity,i=b.scrollSpeed,j=a.clientX,k=a.clientY,l=window.innerWidth,m=window.innerHeight;if(w!==c&&(v=b.scroll,w=c,v===!0)){v=c;do if(v.offsetWidth<v.scrollWidth||v.offsetHeight<v.scrollHeight)break;while(v=v.parentNode)}v&&(d=v,e=v.getBoundingClientRect(),f=(O(e.right-j)<=h)-(O(e.left-j)<=h),g=(O(e.bottom-k)<=h)-(O(e.top-k)<=h)),f||g||(f=(h>=l-j)-(h>=j),g=(h>=m-k)-(h>=k),(f||g)&&(d=G)),(E.vx!==f||E.vy!==g||E.el!==d)&&(E.el=d,E.vx=f,E.vy=g,clearInterval(E.pid),d&&(E.pid=setInterval(function(){d===G?G.scrollTo(G.scrollX+f*i,G.scrollY+g*i):(g&&(d.scrollTop+=g*i),f&&(d.scrollLeft+=f*i))},24)))}},30);return a.prototype={constructor:a,_dragStarted:function(){t&&q&&(h(q,this.options.ghostClass,!0),a.active=this,L(t,"start",q,t,z))},_onTapStart:function(a){var b=a.type,c=a.touches&&a.touches[0],e=(c||a).target,g=e,h=this.options,i=this.el,l=h.filter;if(!("mousedown"===b&&0!==a.button||h.disabled)&&(e=d(e,h.draggable,i))){if(z=o(e),"function"==typeof l){if(l.call(this,a,e,this))return L(g,"filter",e,i,z),void a.preventDefault()}else if(l&&(l=l.split(",").some(function(a){return a=d(g,a.trim(),i),a?(L(a,"filter",e,i,z),!0):void 0})))return void a.preventDefault();if((!h.handle||d(g,h.handle,i))&&e&&!q&&e.parentNode===i){C=a,t=this.el,q=e,u=q.nextSibling,B=this.options.group,q.draggable=!0,h.ignore.split(",").forEach(function(a){j(e,a.trim(),k)}),c&&(C={target:e,clientX:c.clientX,clientY:c.clientY},this._onDragStart(C,"touch"),a.preventDefault()),f(H,"mouseup",this._onDrop),f(H,"touchend",this._onDrop),f(H,"touchcancel",this._onDrop),f(q,"dragend",this),f(t,"dragstart",this._onDragStart),J||this._onDragStart(C,!0);try{H.selection?H.selection.empty():window.getSelection().removeAllRanges()}catch(m){}}}},_emulateDragOver:function(){if(D){i(r,"display","none");var a=H.elementFromPoint(D.clientX,D.clientY),b=a,c=" "+this.options.group.name,d=Q.length;if(b)do{if(b[F]&&b[F].groups.indexOf(c)>-1){for(;d--;)Q[d]({clientX:D.clientX,clientY:D.clientY,target:a,rootEl:b});break}a=b}while(b=b.parentNode);i(r,"display","")}},_onTouchMove:function(a){if(C){var b=a.touches?a.touches[0]:a,c=b.clientX-C.clientX,d=b.clientY-C.clientY,e=a.touches?"translate3d("+c+"px,"+d+"px,0)":"translate("+c+"px,"+d+"px)";D=b,i(r,"webkitTransform",e),i(r,"mozTransform",e),i(r,"msTransform",e),i(r,"transform",e),a.preventDefault()}},_onDragStart:function(a,b){var c=a.dataTransfer,d=this.options;if(this._offUpEvents(),"clone"==B.pull&&(s=q.cloneNode(!0),i(s,"display","none"),t.insertBefore(s,q)),b){var e,g=q.getBoundingClientRect(),h=i(q);r=q.cloneNode(!0),i(r,"top",g.top-I(h.marginTop,10)),i(r,"left",g.left-I(h.marginLeft,10)),i(r,"width",g.width),i(r,"height",g.height),i(r,"opacity","0.8"),i(r,"position","fixed"),i(r,"zIndex","100000"),t.appendChild(r),e=r.getBoundingClientRect(),i(r,"width",2*g.width-e.width),i(r,"height",2*g.height-e.height),"touch"===b?(f(H,"touchmove",this._onTouchMove),f(H,"touchend",this._onDrop),f(H,"touchcancel",this._onDrop)):(f(H,"mousemove",this._onTouchMove),f(H,"mouseup",this._onDrop)),this._loopId=setInterval(this._emulateDragOver,150)}else c&&(c.effectAllowed="move",d.setData&&d.setData.call(this,c,q)),f(H,"drop",this);setTimeout(this._dragStarted,0)},_onDragOver:function(a){var c,e,f,g=this.el,h=this.options,j=h.group,k=j.put,n=B===j,o=h.sort;if(q&&(void 0!==a.preventDefault&&(a.preventDefault(),!h.dragoverBubble&&a.stopPropagation()),B&&!h.disabled&&(n?o||(f=!t.contains(q)):B.pull&&k&&(B.name===j.name||k.indexOf&&~k.indexOf(B.name)))&&(void 0===a.rootEl||a.rootEl===this.el))){if(R(a,h,this.el),K)return;if(c=d(a.target,h.draggable,g),e=q.getBoundingClientRect(),f)return b(!0),void(s||u?t.insertBefore(q,s||u):o||t.appendChild(q));if(0===g.children.length||g.children[0]===r||g===a.target&&(c=m(g,a))){if(c){if(c.animated)return;v=c.getBoundingClientRect()}b(n),g.appendChild(q),this._animate(e,q),c&&this._animate(v,c)}else if(c&&!c.animated&&c!==q&&void 0!==c.parentNode[F]){x!==c&&(x=c,y=i(c));var p,v=c.getBoundingClientRect(),w=v.right-v.left,z=v.bottom-v.top,A=/left|right|inline/.test(y.cssFloat+y.display),C=c.offsetWidth>q.offsetWidth,D=c.offsetHeight>q.offsetHeight,E=(A?(a.clientX-v.left)/w:(a.clientY-v.top)/z)>.5,G=c.nextElementSibling;K=!0,setTimeout(l,30),b(n),p=A?c.previousElementSibling===q&&!C||E&&C:G!==q&&!D||E&&D,p&&!G?g.appendChild(q):c.parentNode.insertBefore(q,p?G:c),this._animate(e,q),this._animate(v,c)}}},_animate:function(a,b){var c=this.options.animation;if(c){var d=b.getBoundingClientRect();i(b,"transition","none"),i(b,"transform","translate3d("+(a.left-d.left)+"px,"+(a.top-d.top)+"px,0)"),b.offsetWidth,i(b,"transition","all "+c+"ms"),i(b,"transform","translate3d(0,0,0)"),clearTimeout(b.animated),b.animated=setTimeout(function(){i(b,"transition",""),i(b,"transform",""),b.animated=!1},c)}},_offUpEvents:function(){g(H,"mouseup",this._onDrop),g(H,"touchmove",this._onTouchMove),g(H,"touchend",this._onDrop),g(H,"touchcancel",this._onDrop)},_onDrop:function(b){var c=this.el,d=this.options;clearInterval(this._loopId),clearInterval(E.pid),g(H,"drop",this),g(H,"mousemove",this._onTouchMove),g(c,"dragstart",this._onDragStart),this._offUpEvents(),b&&(b.preventDefault(),!d.dropBubble&&b.stopPropagation(),r&&r.parentNode.removeChild(r),q&&(g(q,"dragend",this),k(q),h(q,this.options.ghostClass,!1),t!==q.parentNode?(A=o(q),L(q.parentNode,"sort",q,t,z,A),L(t,"sort",q,t,z,A),L(q,"add",q,t,z,A),L(t,"remove",q,t,z,A)):(s&&s.parentNode.removeChild(s),q.nextSibling!==u&&(A=o(q),L(t,"update",q,t,z,A),L(t,"sort",q,t,z,A))),a.active&&L(t,"end",q,t,z,A)),t=q=r=u=s=v=w=C=D=x=y=B=a.active=null,this.save())},handleEvent:function(a){var b=a.type;"dragover"===b||"dragenter"===b?(this._onDragOver(a),e(a)):("drop"===b||"dragend"===b)&&this._onDrop(a)},toArray:function(){for(var a,b=[],c=this.el.children,e=0,f=c.length;f>e;e++)a=c[e],d(a,this.options.draggable,this.el)&&b.push(a.getAttribute("data-id")||n(a));return b},sort:function(a){var b={},c=this.el;this.toArray().forEach(function(a,e){var f=c.children[e];d(f,this.options.draggable,c)&&(b[a]=f)},this),a.forEach(function(a){b[a]&&(c.removeChild(b[a]),c.appendChild(b[a]))})},save:function(){var a=this.options.store;a&&a.set(this)},closest:function(a,b){return d(a,b||this.options.draggable,this.el)},option:function(a,b){var c=this.options;return void 0===b?c[a]:void(c[a]=b)},destroy:function(){var a=this.el,b=this.options;M.forEach(function(c){g(a,c.substr(2).toLowerCase(),b[c])}),g(a,"mousedown",this._onTapStart),g(a,"touchstart",this._onTapStart),g(a,"dragover",this),g(a,"dragenter",this),Array.prototype.forEach.call(a.querySelectorAll("[draggable]"),function(a){a.removeAttribute("draggable")}),Q.splice(Q.indexOf(this._onDragOver),1),this._onDrop(),this.el=null}},a.utils={on:f,off:g,css:i,find:j,bind:c,is:function(a,b){return!!d(a,b,a)},throttle:p,closest:d,toggleClass:h,dispatchEvent:L,index:o},a.version="1.1.1",a.create=function(b,c){return new a(b,c)},a});
define('component/galleryComponent',[
    'core/subscriber',
    'factory/mediaFactory',
    'component/recorderComponent',
    'core/helper',
    'Sortable',
    'extra'
], function (Subscriber, MediaFactory, RecorderComponent, Helper, Sortable) {
    

    var GalleryComponent = function (options) {
        Subscriber.prototype.constructor.apply(this);

        this.options = options;
        this.activeMedia = this.options.activeMedia;
        this.thumbsBounds = {
            width: 0,
            shiftWidth: 0,
            thumbsWidth: 0
        };

        this.bind__onClickItemContainer = this._onClickItemContainer.bind(this);
        this.bind__onClickPrevNext = this._onClickPrevNext.bind(this);
        this.bind__onClickAction = this._onClickAction.bind(this);
        this.bind__onClickLeftRight = this._onClickLeftRight.bind(this);
        this.bind__onClickThumbnail = this._onClickThumbnail.bind(this);
        this.bind__onClickThumbRemove = this._onClickThumbRemove.bind(this);
        this.bind__onRenderMedia = this._onRenderMedia.bind(this);
        this.bind__onRenderThumbnails = this._onRenderThumbnails.bind(this);
        this.bind__onWindowResize = this._onWindowResize.bind(this);

        this.$container = this.options.$container;
        this.$inner = this.$container.find('[id="' + this.options.identifier + '"]' + GalleryComponent.Binder.INNER);
        this.$item = this.$inner.find(GalleryComponent.Binder.ITEM);
        this.$prev = this.$inner.find(GalleryComponent.Binder.PREV);
        this.$next = this.$inner.find(GalleryComponent.Binder.NEXT);
        this.$actions = this.$inner.find(GalleryComponent.Binder.ACTION);
        this.$carousel = this.$inner.find(GalleryComponent.Binder.CAROUSEL);
        this.$thumbs = this.$inner.find(GalleryComponent.Binder.THUMBS);
        this.$left = this.$inner.find(GalleryComponent.Binder.LEFT);
        this.$right = this.$inner.find(GalleryComponent.Binder.RIGHT);

        this.$item.on('click', this.bind__onClickItemContainer);
        this.$prev.find('i.fa').on('click', this.bind__onClickPrevNext);
        this.$next.find('i.fa').on('click', this.bind__onClickPrevNext);
        this.$actions.on('click', this.bind__onClickAction);
        this.$left.find('i.fa').on('click', this.bind__onClickLeftRight);
        this.$right.find('i.fa').on('click', this.bind__onClickLeftRight);
        $(window).on('resize', this.bind__onWindowResize);

        if (this.options.canEdit) {
            this.sortable = Sortable.create(this.$thumbs.find('ul')[0], {
                onSort: function (evt) {
                    var media = this.options.media[evt.oldIndex];
                    var swapped = this.options.media[evt.newIndex];
                    this.options.media[evt.newIndex] = media;
                    this.options.media[evt.oldIndex] = swapped;

                    this._updateButtonStates();

                    this._dispatch(GalleryComponent.Event.CHANGE, {
                        galleryComponent: this
                    });
                }.bind(this)
            });
        }

        if (typeof this.options.mediaIds !== 'undefined') {
            MediaFactory.list(this.options.mediaIds)
                .done(function (data) {
                    this.options.media = data.media;
                    this._populate();
                }.bind(this))
                .fail(function (data) {
                    console.error('%s: media factory list', GalleryComponent.TAG);
                });
        }
    };

    GalleryComponent.extend(Subscriber);

    GalleryComponent.TAG = 'GalleryComponent';

    GalleryComponent.Mode = {
        PREVIEW: 0,
        INLINE: 1
    };

    GalleryComponent.Event = {
        CHANGE: 'eventChange',
        REMOVED_MEDIA: 'eventRemovedMedia',
        DONE: 'eventDone',
        HIDDEN: 'eventHidden'
    };

    GalleryComponent.Binder = {
        INNER: '.gallery-inner',
        ITEM: '.gallery-item',
        PREV: '.gallery-prev',
        NEXT: '.gallery-next',
        ACTION: '.gallery-action a',
        CAROUSEL : '.gallery-carousel',
        THUMBS: '.gallery-thumbs',
        THUMB_REMOVE: '.gallery-thumb-remove',
        LEFT: '.gallery-left',
        RIGHT: '.gallery-right'
    };

    GalleryComponent.prototype._launchRecorder = function (options, setMediaAsSource, setMediaAsRecorded) {
        RecorderComponent.render(options, function (e) {
            this.recorderCmp = e.recorderComponent;
            this.recorderCmp.subscribe(RecorderComponent.Event.DONE, function (e) {
                this.recorderCmp.hide();

                if (e.doPost)
                    window.location.assign(Routing.generate('imdc_thread_new_from_media', {mediaId: e.media.get('id')}));
            }.bind(this));
            this.recorderCmp.subscribe(RecorderComponent.Event.HIDDEN, function (e) {
                this.recorderCmp.destroy();
                if (this.options.mode == GalleryComponent.Mode.PREVIEW) {
                    this.show();
                }
                this.paused = false;
            }.bind(this));

            this.paused = true;
            if (this.options.mode == GalleryComponent.Mode.PREVIEW)
                this.hide();
            if (setMediaAsSource)
                this.recorderCmp.setSourceMedia(this.activeMedia);
            if (setMediaAsRecorded)
                this.recorderCmp.setRecordedMedia(this.activeMedia);
            this.recorderCmp.show();
        }.bind(this));
    };

    GalleryComponent.prototype._onClickItemContainer = function (e) {
        if (this.options.mode != GalleryComponent.Mode.PREVIEW)
            return;

        if (e.target === this.$item[0]) {
            this.hide();
        }
    };

    GalleryComponent.prototype._onClickPrevNext = function (e) {
        e.preventDefault();

        var next = $(e.currentTarget).hasClass('next');

        var index = $.inArray(this.activeMedia, this.options.media);
        if (!next && (index - 1) < 0 || next && (index + 1) >= this.options.media.length) {
            return;
        }

        this.activeMedia = this.options.media[index + (next ? 1 : -1)];

        this._renderMedia();
    };

    GalleryComponent.prototype._onClickAction = function (e) {
        var action = $(e.currentTarget).data('action');

        switch (action) {
            case 1: // close
                this.hide();
                break;
            case 2: // fullscreen
                Helper.toggleFullScreen(this.$item);
                break;
            case 3: // interp video
                this._launchRecorder({
                    tab: RecorderComponent.Tab.INTERPRETATION,
                    enableDoneAndPost: true
                }, true);
                break;
            case 4: // cut video
                this._launchRecorder({
                    tab: RecorderComponent.Tab.NORMAL,
                    mode: RecorderComponent.Mode.PREVIEW
                }, false, true);
                break;
        }
    };

    GalleryComponent.prototype._calcCarouselPosition = function (right) {
        var cPos = Math.abs(parseInt(this.$thumbs.find('ul').css('left'), 10));
        var nPos, // new
            tPos, // tail
            rNPos, // real new
            fPos; // final

        if (!right) {
            nPos = cPos - this.thumbsBounds.shiftWidth;
            fPos = Math.min(0, -nPos);
        } else {
            nPos = cPos + this.thumbsBounds.width;
            tPos = cPos + (this.thumbsBounds.thumbsWidth - nPos);
            rNPos = cPos + this.thumbsBounds.shiftWidth;
            fPos = -Math.min(rNPos, (nPos >= this.thumbsBounds.thumbsWidth) ? cPos : tPos);
        }

        return fPos;
    };

    GalleryComponent.prototype._onClickLeftRight = function (e) {
        e.preventDefault();

        var pos = this._calcCarouselPosition($(e.currentTarget).hasClass('right'));
        this._updateButtonStates(pos);
        this.$thumbs.find('ul').css({left: pos});
    };

    GalleryComponent.prototype._onClickThumbnail = function (e) {
        e.preventDefault();

        //TODO consolidate
        var mediaId = $(e.currentTarget).data('mid');
        this.activeMedia = $.grep(this.options.media, function (elementOfArray, indexInArray) {
            return elementOfArray.get('id') == mediaId;
        })[0];

        this._renderMedia();
    };

    GalleryComponent.prototype._onClickThumbRemove = function (e) {
        e.preventDefault();

        //TODO consolidate
        var mediaId = $(e.currentTarget).data('mid');
        var media = $.grep(this.options.media, function (elementOfArray, indexInArray) {
            return elementOfArray.get('id') == mediaId;
        })[0];

        this.removeMedia(media);

        this._dispatch(GalleryComponent.Event.REMOVED_MEDIA, {
            media: media,
            galleryComponent: this
        });
    };
    
    GalleryComponent.prototype.clear = function() {
	for (var i in this.options.media)
	{
	    var media = this.options.media[i];
	    this.removeMedia(media);
	    this._dispatch(GalleryComponent.Event.REMOVED_MEDIA, {
	            media: media,
	            galleryComponent: this
	        });
	}
    }

    GalleryComponent.prototype._updateButtonStates = function (slidePosition) {
        var $selThumbnail = this.$thumbs.find('li[data-mid="' + this.activeMedia.get('id') + '"]');
        this.$thumbs.find('div').removeClass('selected');

        // actions
        var $closeBtn = this.$actions.filter('[data-action="1"]');
        var $fullscreenBtn = this.$actions.filter('[data-action="2"]');
        var $recorderBtn = this.$actions.filter('[data-action="3"]');
        var $cutBtn = this.$actions.filter('[data-action="4"]');
        var isPreviewMode = this.options.mode == GalleryComponent.Mode.PREVIEW;

        $closeBtn.toggle(isPreviewMode);
        $fullscreenBtn.show();
        if (this.activeMedia.get('type') == 1) {
            $recorderBtn.show();
            $cutBtn.toggle(isPreviewMode);
        } else {
            $recorderBtn.hide();
            $cutBtn.hide();
        }

        // preview
        this.$prev.toggleClass('disabled', $selThumbnail.is(':first-child'));
        this.$next.toggleClass('disabled', $selThumbnail.is(':last-child'));

        // carousel
        var slidePosition = Math.abs(slidePosition !== undefined
            ? slidePosition
            : parseInt(this.$thumbs.find('ul').css('left'), 10));

        this.$left.find('i.fa').toggleClass('disabled', slidePosition <= 0);
        this.$right.find('i.fa').toggleClass('disabled',
            (slidePosition + this.thumbsBounds.width) >= this.thumbsBounds.thumbsWidth);
        $selThumbnail.find('div').addClass('selected');
    };

    GalleryComponent.prototype._focusSelectedThumbnail = function () {
        // bring selected thumbnail into view on carousel
        var $selThumbnail = this.$thumbs.find('li[data-mid="' + this.activeMedia.get('id') + '"]');
        var position = $selThumbnail.position();
        if (!position) {
            return;
        }

        var width = $selThumbnail.outerWidth(true);
        position.right = position.left + width;
        position.center = position.left + (width / 2);

        var slidePosition = Math.abs(parseInt(this.$thumbs.find('ul').css('left'), 10));
        var visibleRegion = {
            left: slidePosition,
            right: slidePosition + this.thumbsBounds.width,
            center: slidePosition + (this.thumbsBounds.width / 2)
        };
        var right = position.center > visibleRegion.center;
        var centerDiff = Math.abs(position.center - visibleRegion.center);
        var fPos = slidePosition + (right ? centerDiff : -centerDiff);

        if (!right) {
            fPos = Math.min(0, -fPos);
        } else {
            fPos = -Math.min(fPos, (visibleRegion.right >= this.thumbsBounds.thumbsWidth) ? visibleRegion.left : this.thumbsBounds.thumbsWidth - this.thumbsBounds.width);
        }

        this.$thumbs.find('ul').css({left: fPos});

        /*console.log(position);
        console.log(fPos);
        console.log(this.thumbsBounds.thumbsWidth);
        console.log(slidePosition);*/

        this._updateButtonStates(fPos);
    };

    GalleryComponent.prototype._onWindowResize = function (e) {
        // hide the carousel if there's less than two media
        this.options.media.length <= 1 && !this.options.canEdit
            ? this.$carousel.hide()
            : this.$carousel.show();

        var container = this.$container.prop('tagName').toLowerCase() === 'body'
            ? $(window)
            : this.$container;
        var carouselHeight = this.$carousel.is(':hidden') ? 0 : this.$carousel.outerHeight(true);
        var itemHeight = Helper.isFullscreen()
            ? $(window).height()
            : container.height() - carouselHeight;

        // container widths/heights/positioning
        var thumbsWidth = container.width() - (this.$left.outerWidth(true) + this.$right.outerWidth(true)) - 0.5;

        /*console.log(this.$left.outerWidth(true));
        console.log(this.$right.outerWidth(true));
        console.log(thumbsWidth);*/

        var verticalAlign = function (index, value) {
            return (itemHeight / 2) - ($(this).height() / 2);
        };

        this.$item.css({height: itemHeight, 'line-height': itemHeight + 'px'});
        this.$thumbs.width(thumbsWidth);
        this.$prev.css('top', verticalAlign);
        this.$next.css('top', verticalAlign);

        // set media element widths/heights/positioning for gallery context
        this.$item.find('.tt-media-video').css({height: itemHeight});
        this.$item.find('.tt-media-img').css({'max-height': itemHeight});

        // inner widths
        var width = this.$thumbs.width();
        var $thumbnails = this.$thumbs.find('li');
        var initialThumbMargin = parseInt($thumbnails.eq(0).css('margin-left'), 10);
        var thumbWidth = $thumbnails.eq(0).outerWidth(true) - initialThumbMargin;
        var numThumbsWidth = Math.floor(width / thumbWidth);

        this.thumbsBounds.width = width;
        this.thumbsBounds.shiftWidth = thumbWidth * numThumbsWidth;
        this.thumbsBounds.thumbsWidth = (thumbWidth * $thumbnails.length) + initialThumbMargin;

        if (!this.activeMedia) {
            this.$actions.hide();
            this.$prev.addClass('disabled');
            this.$next.addClass('disabled');
            this.$left.find('i.fa').addClass('disabled');
            this.$right.find('i.fa').addClass('disabled');
            return;
        }

        // bring selected thumbnail into focus
        this._focusSelectedThumbnail();

        // fullscreen
        this.$item.toggleClass('fullscreen', Helper.isFullscreen());
    };

    GalleryComponent.prototype._onRenderMedia = function (err, out) {
        //TODO append instead and add slider so that media is loaded on demand but only once
        this.$item.html(out);

        $(window).trigger('resize');
    };

    GalleryComponent.prototype._onRenderThumbnails = function (err, out) {
        this.$thumbs.find('ul').html(out);
        this.$thumbs.find('li').on('click', this.bind__onClickThumbnail);
        this.$thumbs.find('li').find('span').on('click', this.bind__onClickThumbRemove);

        $(window).trigger('resize');
    };

    GalleryComponent.prototype._renderMedia = function () {
        dust.render('media_element', {
            media: this.activeMedia ? this.activeMedia.data : null,
            enable_controls: true,
            preload_media: false
        }, this.bind__onRenderMedia);
    };

    GalleryComponent.prototype._renderMediaAndThumbnails = function () {
        if (!this.activeMedia) {
            this.$item.html('');
            this.$thumbs.find('ul').html('');

            $(window).trigger('resize');
            return;
        }

        //TODO array representation of media models
        this._renderMedia();
        dust.render('gallery_thumbnail', {media: this.options.media, can_edit: this.options.canEdit}, this.bind__onRenderThumbnails);
    };

    GalleryComponent.prototype._populate = function () {
        this.activeMedia = this.options.media.length == 0
            ? null
            : !this.activeMedia
                ? this.options.media[0]
                : this.activeMedia;

        this._renderMediaAndThumbnails();
    };

    GalleryComponent.prototype.show = function (animate) {
        animate = animate !== undefined
            ? animate
            : (this.options.mode == GalleryComponent.Mode.PREVIEW);

        if (this.options.mode == GalleryComponent.Mode.PREVIEW) {
            this.$container.addClass('tt-gallery-modal');
        }

        var complete = function () {
            this._populate();
        }.bind(this);

        if (animate) {
            this.$inner.fadeIn()
                .promise()
                .done(complete);
        } else {
            this.$inner.show()
                .promise()
                .done(complete);
        }
    };

    GalleryComponent.prototype.hide = function (animate) {
        animate = animate !== undefined
            ? animate
            : (this.options.mode == GalleryComponent.Mode.PREVIEW);

        if (this.options.mode == GalleryComponent.Mode.PREVIEW && !this.paused) {
            this.$container.removeClass('tt-gallery-modal');
        }

        var complete = function () {
            if (this.paused)
                return;

            this._dispatch(GalleryComponent.Event.HIDDEN, {
                galleryComponent: this
            });
        }.bind(this);

        if (animate) {
            this.$inner.fadeOut()
                .promise()
                .done(complete);
        } else {
            this.$inner.show()
                .promise()
                .done(complete);
        }
    };

    GalleryComponent.prototype.destroy = function () {
        this.$inner.remove();
    };

    GalleryComponent.prototype.setMedia = function (media) {
        this.options.media = media;
        this._populate();
    };

    GalleryComponent.prototype.addMedia = function (media) {
        this.options.media.push(media);
        this._populate();
    };

    GalleryComponent.prototype.removeMedia = function (media) {
        var index = $.inArray(media, this.options.media);
        if (index == -1) {
            return;
        }

        this.options.media.splice(index, 1);

        if (this.options.media.length == 0) {
            this.activeMedia = null;
        } else {
            if (index >= this.options.media.length) {
                index--;
            }

            this.activeMedia = this.options.media[index];
        }

        this._populate();
    };

    GalleryComponent.render = function (options, callback) {
        if (options.mode !== 'undefined') {
            if (options.mode == GalleryComponent.Mode.INLINE && options.$container === 'undefined') {
                throw new Error('Inline mode requires the $container option to be set explicitly.');
            }
        }

        var defaults = {
            $container: $('body'),
            identifier: Math.random(),
            mode: GalleryComponent.Mode.PREVIEW,
            canEdit: false,
            media: [],
            activeMedia: null
        };

        options = options || defaults;
        for (var o in defaults) {
            options[o] = typeof options[o] != 'undefined' ? options[o] : defaults[o];
        }

        var template = options.mode == GalleryComponent.Mode.INLINE
            ? 'gallery_inline'
            : 'gallery_preview';
        dust.render(template, {identifier: options.identifier}, function (err, out) {
            options.$container.append(out);

            var cmp = new GalleryComponent(options);
            callback.call(cmp, {
                galleryComponent: cmp
            });
        });
    };

    return GalleryComponent;
});

define('component/mediaChooserComponent',[
    'core/subscriber',
    'factory/mediaFactory',
    'factory/myFilesFactory',
    'component/recorderComponent',
    'component/myFilesSelectorComponent',
    'component/galleryComponent',
    'model/mediaModel',
    'core/helper',
    'extra'
], function (Subscriber, MediaFactory, MyFilesFactory, RecorderComponent, MyFilesSelectorComponent, GalleryComponent, MediaModel, Helper) {
    

    var MediaChooserComponent = function (options) {
        Subscriber.prototype.constructor.apply(this);

        this.options = options;
        this.media = [];

        this.bind__onClickRecordVideo = this._onClickRecordVideo.bind(this);
        this.bind__onClickUploadFile = this._onClickUploadFile.bind(this);
        this.bind__onClickSelect = this._onClickSelect.bind(this);
        this.bind__onChangeResourceFile = this._onChangeResourceFile.bind(this);
        //this.bind__onClickRemoveSelectedMedia = this._onClickRemoveSelectedMedia.bind(this);

        this.$container = this.options.$container;
        this.$containerUpload = this.$container.find(MediaChooserComponent.Binder.CONTAINER_UPLOAD);
        this.$uploadForm = $(this.$containerUpload.data('form'));
        this.$resourceFile = this._getFormField('source_resource_file');
        this.$recordVideo = this.$container.find(MediaChooserComponent.Binder.RECORD_VIDEO);
        this.$uploadFile = this.$container.find(MediaChooserComponent.Binder.UPLOAD_FILE);
        this.$select = this.$container.find(MediaChooserComponent.Binder.SELECT);
        this.$uploadTitle = this.$container.find(MediaChooserComponent.Binder.UPLOAD_TITLE);
        this.$working = this.$container.find(MediaChooserComponent.Binder.WORKING);
        //this.$selected = this.$container.find(MediaChooserComponent.Binder.SELECTED);
        this.$gallery = this.$container.find(MediaChooserComponent.Binder.GALLERY);
        //this.$uploaded = this.$container.find(MediaChooserComponent.Binder.UPLOADED);

        this.$resourceFile.on('change', this.bind__onChangeResourceFile);
        this.$recordVideo.on('click', this.bind__onClickRecordVideo);
        this.$uploadFile.on('click', this.bind__onClickUploadFile);
        this.$select.on('click', this.bind__onClickSelect);

        /*this.$selected.sortable({
            update: function (event, ui) {
                this._dispatch(MediaChooserComponent.Event.SUCCESS, {
                    mediaChooserComponent: this
                });
            }.bind(this)
        });*/

        if (this.$gallery.length > 0) {
            GalleryComponent.render({
                $container: this.$gallery,
                mode: GalleryComponent.Mode.INLINE,
                canEdit: true
            }, function (e) {
                this.galleryCmp = e.galleryComponent;
                this.galleryCmp.subscribe(GalleryComponent.Event.CHANGE, function (e) {
                    this.media = this.galleryCmp.options.media;
                    this._invokeSuccess();
                }.bind(this));
                this.galleryCmp.subscribe(GalleryComponent.Event.REMOVED_MEDIA, function (e) {
                    if (e.galleryComponent.options.media.length == 0) {
                        this.galleryCmp.hide(true);
                        this.$gallery.slideUp();
                    }
                    this._removeSelectedMedia(e.media);
                    this._invokeSuccess();
                }.bind(this));
            }.bind(this));
        }
    };

    MediaChooserComponent.extend(Subscriber);

    MediaChooserComponent.TAG = 'MediaChooserComponent';

    MediaChooserComponent.Binder = {
        RECORD_VIDEO: '.mediachooser-record-video',
        UPLOAD_FILE: '.mediachooser-upload-file',
        SELECT: '.mediachooser-select',

        CONTAINER_UPLOAD: '.mediachooser-container-upload',
        UPLOAD_TITLE: '.mediachooser-upload-title',

        WORKING: '.mediachooser-working',
        //SELECTED: '.mediachooser-selected',
        //SELECTED_MEDIA: '.mediachooser-selected-media',
        //REMOVE: '.mediachooser-remove',
        GALLERY: '.mediachooser-gallery',
        //UPLOADED: '.mediachooser-uploaded'
    };

    MediaChooserComponent.Event = {
        UPLOAD_START: 'eventUploadStart',
        SUCCESS: 'eventSuccess', //TODO rename to 'add'
        SUCCESS_AND_POST: 'eventSuccessAndPost', //TODO rename to 'add'
        REMOVE: 'eventRemove',
        ERROR: 'eventError',
        RESET: 'eventReset'
    };

    // this must be the same name defined in {bundle}/Form/Type/MediaType
    MediaChooserComponent.FORM_NAME = 'media';

    MediaChooserComponent.prototype._getFormField = function (fieldName) {
        return this.$uploadForm.find('#' + MediaChooserComponent.FORM_NAME + '_' + fieldName);
    };

    MediaChooserComponent.prototype._addSelectedMedia = function (media) {
        /*var count = this.$selected
            .find(MediaChooserComponent.Binder.SELECTED_MEDIA)
            .filter('[data-mid="' + media.get('id') + '"]')
            .length;

        if (count > 0)
            return; // exists

        var newSelectedMedia = this.$selected.data('prototype');
        newSelectedMedia = newSelectedMedia.replace(/__id__/g, media.get('id'));
        newSelectedMedia = newSelectedMedia.replace(/__title__/g, media.get('title'));
        newSelectedMedia = newSelectedMedia.replace(/__resource_webPath__/g, media.get('resource.web_path'));
        this.$selected.append(newSelectedMedia);

        this.$selected
            .find(MediaChooserComponent.Binder.SELECTED_MEDIA)
            .filter('[data-mid="' + media.get('id') + '"]')
            .find(MediaChooserComponent.Binder.REMOVE)
            .on('click', this.bind__onClickRemoveSelectedMedia);*/

        //TODO consolidate
        for (var m in this.media) {
            var mm = this.media[m];
            if (mm.get('id') == media.get('id')) {
                return; // exists
            }
        }

        this.media.push(media);

        if (this.$gallery.length == 0)
            return;

        this.$gallery.slideDown();
        this.galleryCmp.addMedia(media);
        this.galleryCmp.show(true);
    };

    MediaChooserComponent.prototype._removeSelectedMedia = function (media) {
        /*for (var m in this.media) {
            var media = this.media[m];
            if (media.get('id') == mediaId) {
                this.media.splice(m, 1);
                break;
            }
        }

        this.$selected
            .find(MediaChooserComponent.Binder.SELECTED_MEDIA)
            .filter('[data-mid="' + mediaId + '"]')
            .remove();*/

        //TODO consolidate
        for (var m in this.media) {
            var mm = this.media[m];
            if (mm.get('id') == media.get('id')) {
                var media = this.media.splice(m, 1);
                this.galleryCmp.removeMedia(m);

                this._dispatch(MediaChooserComponent.Event.REMOVE, {
                    media: media,
                    mediaChooserComponent: this
                });
            }
        }
    };

    MediaChooserComponent.prototype._resetUpload = function () {
        this.$containerUpload.hide();
        this.$uploadTitle.html('');
    };

    MediaChooserComponent.prototype._invokeSuccess = function (doPost) {
        this._resetUpload();

        var event = (typeof doPost != 'undefined' && doPost == true)
            ? MediaChooserComponent.Event.SUCCESS_AND_POST
            : MediaChooserComponent.Event.SUCCESS;

        var args = {
            media: this.media,
            mediaChooserComponent: this
        };

        this._dispatch(event, args);
        
    };

    MediaChooserComponent.prototype._onClickRecordVideo = function (e) {
        e.preventDefault();

        RecorderComponent.render({
            enableDoneAndPost: this.options.enableDoneAndPost
        }, function (e) {
            this.recorderCmp = e.recorderComponent;
            this.recorderCmp.subscribe(RecorderComponent.Event.DONE, function (e) {
                this.recorderCmp.hide();
                //if (this.$selected.length > 0)
                    this._addSelectedMedia(e.media);
                this._invokeSuccess(e.doPost);
            }.bind(this));
            this.recorderCmp.subscribe(RecorderComponent.Event.HIDDEN, function (e) {
                this.recorderCmp.destroy();
            }.bind(this));
            this.recorderCmp.show();
        }.bind(this));
    };

    MediaChooserComponent.prototype._onClickUploadFile = function (e) {
        e.preventDefault();

        this.$resourceFile.click();
    };

    MediaChooserComponent.prototype._onClickSelect = function (e) {
        e.preventDefault();

        MyFilesSelectorComponent.render({}, function (e) {
            this.mfsCmp = e.myFilesSelectorComponent;
            this.mfsCmp.subscribe(MyFilesSelectorComponent.Event.DONE, function (e) {
                this.mfsCmp.hide();
                $.each(e.media, (function (index, element) {
                    this._addSelectedMedia(element);
                }).bind(this));
                this._invokeSuccess();
            }.bind(this));
            this.mfsCmp.subscribe(MyFilesSelectorComponent.Event.HIDDEN, function (e) {
                this.mfsCmp.destroy();
            }.bind(this));
            this.mfsCmp.show();
        }.bind(this));
    };

    MediaChooserComponent.prototype._onChangeResourceFile = function (e) {
        e.preventDefault();

        this._toggleForm(true);

        var maxSize = this.$resourceFile.data('maxsize');
        var fileSize = this.$resourceFile[0].files[0].size;
        if (fileSize > maxSize) {
            alert(Translator.trans('form.upload.maxFileSizeExceeded', {
                'fileSize': (fileSize / 1048576).toFixed(1) + "MB",
                'maxUploadSize': (maxSize / 1048576).toFixed(1) + "MB"
            }));
            this.$resourceFile.val('');
            this._toggleForm(false);
            return;
        }

        MyFilesFactory.add(this.$uploadForm[0])
            .progress(function (percent) {
                Helper.updateProgressBar(this.$containerUpload.show(), percent);
            }.bind(this))
            .done(function (data) {
                //if (this.$selected.length > 0)
                this._addSelectedMedia(data.media);
                this._invokeSuccess();
                this._toggleForm(false);
            }.bind(this))
            .fail(function (data) {
                this._resetUpload();
                this._dispatch(MediaChooserComponent.Event.ERROR, {
                    mediaChooserComponent: this,
                    error: data ? data.error : 'Unknown error'
                });
                this._toggleForm(false);
            }.bind(this));

        this._dispatch(MediaChooserComponent.Event.UPLOAD_START, {
            mediaChooserComponent: this
        });

        this.$resourceFile.val('');
    };

    /*MediaChooserComponent.prototype._onClickRemoveSelectedMedia = function (e) {
        e.preventDefault();

        var mediaId = $(e.currentTarget).data('mid');
        this._removeSelectedMedia(mediaId);

        this._dispatch(MediaChooserComponent.Event.SUCCESS, {
            mediaChooserComponent: this
        });
    };*/

    MediaChooserComponent.prototype._toggleForm = function (disabled) {
        this.$recordVideo.attr('disabled', disabled);
        this.$uploadFile.attr('disabled', disabled);
        this.$select.attr('disabled', disabled);
    };

    MediaChooserComponent.prototype.setMedia = function (mediaIds) {
        console.log('%s: %s', MediaChooserComponent.TAG, 'setMedia');

        if (typeof mediaIds === 'undefined')
            return;

        this._toggleForm(true);
        this.$working.show();
        //this.$selected.html('');

        MediaFactory.list(mediaIds)
            .done(function (data) {
                this.$working.hide();
                this._toggleForm(false);

                $.each(data.media, function (index, element) {
                    this._addSelectedMedia(element);
                }.bind(this));
                this._invokeSuccess();
            }.bind(this))
            .fail(function (data) {
                this.$working.hide();
                this._toggleForm(false);

                console.error('%s: media factory list', MediaChooserComponent.TAG);
            }.bind(this));
    };

    MediaChooserComponent.prototype.generateFormData = function (prototype) {
        var media = '';

        /*this.$selected.find(MediaChooserComponent.Binder.SELECTED_MEDIA).each(function (index, element) {
            var newMedia = $(prototype.replace(/__name__/g, index));
            newMedia.val($(element).data('mid'));

            media += newMedia[0].outerHTML;
        });*/

        $.each(this.media, function(index, element) {
            var newMedia = $(prototype.replace(/__name__/g, index));
            newMedia.val(element.get('id'));

            media += newMedia[0].outerHTML;
        });

        return media;
    };

    MediaChooserComponent.prototype.reset = function () {
        console.log('%s: %s', MediaChooserComponent.TAG, 'reset');

        this._resetUpload();
        this.galleryCmp.clear();

//        this.media = [];

        this._dispatch(MediaChooserComponent.Event.RESET, {
            mediaChooserComponent: this
        });
    };

    MediaChooserComponent.render = function ($form, options) {
        var defaults = {
            enableDoneAndPost: false
        };

        options = options || defaults;
        for (var o in defaults) {
            options[o] = typeof options[o] != 'undefined' ? options[o] : defaults[o];
        }

        options.$container = $form;

        return new MediaChooserComponent(options);
    };

    return MediaChooserComponent;
});

define('views/contact/listView',[
    'component/tableComponent'
], function (TableComponent) {
    

    var ListView = function (controller, options) {
        this.controller = controller;

        this.bind__onSelectionChange = this._onSelectionChange.bind(this);
        this.bind__onClickBulkAction = this._onClickBulkAction.bind(this);

        this.$container = options.container;
        this.$form = this.$container.find('form[name^=' + ListView.FORM_NAME + ']');
        this.$tabPanes = this.$container.find(ListView.Binder.TAB_PANES);

        this.tabPaneTblCmps = [];
        this.$tabPanes.each(function (index, element) {
            this.tabPaneTblCmps[element.id] = TableComponent.table($(element));
            this.tabPaneTblCmps[element.id].subscribe(TableComponent.Event.SELECTION_CHANGE, this.bind__onSelectionChange);
            this.tabPaneTblCmps[element.id].subscribe(TableComponent.Event.CLICK_BULK_ACTION, this.bind__onClickBulkAction);
        }.bind(this));

        $tt._instances.push(this);
    };

    ListView.TAG = 'ContactListView';

    ListView.Binder = {
        TAB_PANES: '.tab-pane[id^=tab]'
    };

    ListView.FORM_NAME = 'users_select';

    ListView.prototype._getFormField = function (form, fieldName) {
        return form.find('#' + form.attr('name') + '_' + fieldName);
    };

    ListView.prototype._onSelectionChange = function (e) {
        var $bulkActions = e.tableComponent.getBulkActions();
        $bulkActions.attr('disabled', e.$selection.length == 0);
    };

    ListView.prototype._updateUsersSelectForm = function ($users) {
        var $userList = this._getFormField(this.$form, 'users');
        $userList.html('');

        $users.each(function (index, element) {
            var $newUser = $userList.data('prototype');
            $newUser = $($newUser.replace(/__name__/g, index));
            $newUser.val($(element).data('uid'));
            $userList.append($newUser);
        });
    };

    ListView.prototype._onClickBulkAction = function (e) {
        switch (e.action) {
            case 1: // delete
                var contactList = e.tableComponent.getTable()
                    .attr('id')
                    .replace(/^tab/, '')
                    .toLowerCase();

                if (contactList == 'all') {
                    if (!confirm('This will delete the selected contacts from all lists. Continue?'))
                        break;
                }

                e.$bulkAction.attr('disabled', true);

                var userIds = [];
                e.$selection.each(function (index, element) {
                    userIds.push($(element).data('uid'));
                });

                this.controller.delete(userIds, contactList)
                    .fail(function (data) {
                        if (data) {
                            alert(data.message);
                        } else {
                            alert('Something went wrong.');
                        }
                        e.$bulkAction.attr('disabled', false);
                    });
                break;
            case 2: // new group
                e.$bulkAction.attr('disabled', true);
                this._updateUsersSelectForm(e.$selection);
                this.$form.attr('action', Routing.generate('imdc_group_new'));
                this.$form.submit();
                break;
            case 3: // send message
                e.$bulkAction.attr('disabled', true);
                this._updateUsersSelectForm(e.$selection);
                this.$form.attr('action', Routing.generate('imdc_message_new'));
                this.$form.submit();
                break;
        }
    };

    return ListView;
});

define('views/forum/newView',[
    'component/mediaChooserComponent',
    'component/accessTypeComponent'
], function (MediaChooserComponent, AccessTypeComponent) {
    

    var NewView = function (controller, options) {
        this.controller = controller;

        //this.bind__onChangeAccessType = this._onChangeAccessType.bind(this);
        this.bind__onSubmitForm = this._onSubmitForm.bind(this);
        this.bind__onUploadStart = this._onUploadStart.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onRemove = this._onRemove.bind(this);
        this.bind__onReset = this._onReset.bind(this);
        this.bind__onError = this._onError.bind(this);

        this.$container = options.container;
        this.$form = this.$container.find('form[name^=' + NewView.FORM_NAME + ']');
        //this.$accessTypes = this.$form.find('input:radio');
        this.$submit = this.$container.find(NewView.Binder.SUBMIT);

        //this.$accessTypes.on('change', this.bind__onChangeAccessType);
        this.$form.on('submit', this.bind__onSubmitForm);

        //this.$accessTypes.filter(':checked').trigger('change');

        this.atCmp = AccessTypeComponent.render(this.$form);

        this.mcCmp = MediaChooserComponent.render(this.$form);
        this.mcCmp.subscribe(MediaChooserComponent.Event.UPLOAD_START, this.bind__onUploadStart);
        this.mcCmp.subscribe(MediaChooserComponent.Event.SUCCESS, this.bind__onSuccess);
        this.mcCmp.subscribe(MediaChooserComponent.Event.REMOVE, this.bind__onRemove);
        this.mcCmp.subscribe(MediaChooserComponent.Event.RESET, this.bind__onReset);
        this.mcCmp.subscribe(MediaChooserComponent.Event.ERROR, this.bind__onError);

        var mediaIds = [];
        this._getFormField('titleMedia').children().each(function (index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this.$submit.attr('disabled', true);
            this.mcCmp.setMedia(mediaIds);
        }

        $tt._instances.push(this);
    };

    NewView.TAG = 'ForumNewView';

    NewView.Binder = {
        SUBMIT: '.forum-submit'
    };

    // this must be the same name defined in {bundle}/Form/Type/ForumType
    NewView.FORM_NAME = 'forum';

    NewView.prototype._getFormField = function (fieldName) {
        return this.$form.find('#' + NewView.FORM_NAME + '_' + fieldName);
    };

    /*NewView.prototype._onChangeAccessType = function (e) {
        var group = this._getFormField('group');
        var parent = group.parent();

        if ($(e.target).attr('id') == this.$accessTypes.filter('[value="6"]').attr('id')) {
            parent.find('label').addClass('required');
            group.attr('required', true);
            parent.children().show();
        } else {
            parent.find('label').removeClass('required');
            group.attr('required', false);
            parent.children().hide();
        }
    };*/

    NewView.prototype._onSubmitForm = function (e) {
        if (this.$form[0].checkValidity()) {
            this.$submit.button('loading');
        }
    };

    NewView.prototype._updateForm = function () {
        var $formField = this._getFormField('titleMedia');
        $formField.html(
            this.mcCmp.generateFormData(
                $formField.data('prototype')
            )
        );

        $formField = this._getFormField('titleText');
        $formField.attr('required', this.mcCmp.media.length == 0);

        $formField = $formField.parent().find('label');
        if (this.mcCmp.media.length == 0) {
            $formField.addClass('required');
        } else {
            $formField.removeClass('required');
        }
    };

    NewView.prototype._onUploadStart = function (e) {
        this.$submit.attr('disabled', true);
    };

    NewView.prototype._onSuccess = function (e) {
        this._updateForm();

        this.$submit.attr('disabled', false);
    };

    NewView.prototype._onRemove = function (e) {
        this._updateForm();
    };

    NewView.prototype._onReset = function (e) {
        this._updateForm();
    };
    
    NewView.prototype._onError = function (e) {
	    alert('Error: ' + e.error);
    };

    return NewView;
});

define('views/forum/editView',[
    'views/forum/newView'
], function (NewView) {
    

    var EditView = function (controller, options) {
        NewView.prototype.constructor.apply(this, arguments);

        this.bind__onClickDelete = this._onClickDelete.bind(this);

        this.$deleteModal = this.$container.find(EditView.Binder.DELETE_MODAL);
        this.$delete = this.$container.find(EditView.Binder.DELETE);

        this.$delete.on('click', this.bind__onClickDelete);
    };

    EditView.extend(NewView);

    EditView.TAG = 'ForumEditView';

    EditView.Binder.DELETE_MODAL = '.forum-delete-modal';
    EditView.Binder.DELETE = '.forum-delete';

    EditView.prototype._onClickDelete = function (e) {
        e.preventDefault();

        this.$delete.button('loading');
        this.controller.delete()
            .done(function (data) {
                this.$deleteModal
                    .find('.modal-body')
                    .html('Forum deleted successfully.');
            }.bind(this))
            .fail(function () {
                this.$container
                    .find('.modal-body')
                    .prepend('Something went wrong. Try again.');
                this.$delete.button('reset');
            }.bind(this));
    };

    return EditView;
});

define('views/forum/listView',[
    'component/galleryComponent'
], function (GalleryComponent) {
    

    var ListView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickThumbnail = this._onClickThumbnail.bind(this);

        this.$container = options.container;
        this.$thumbnails = this.$container.find(ListView.Binder.THUMBNAILS);

        this.$thumbnails.on('click', this.bind__onClickThumbnail);

        $tt._instances.push(this);
    };

    ListView.TAG = 'ForumListView';

    ListView.Binder = {
        THUMBNAILS: '.tt-grid-div-body .expand'
    };

    ListView.prototype._onClickThumbnail = function (e) {
        e.preventDefault();

        GalleryComponent.render({
            mediaIds: [$(e.currentTarget).data('mid')]
        }, function (e) {
            this.galleryCmp = e.galleryComponent;
            this.galleryCmp.subscribe(GalleryComponent.Event.HIDDEN, function (e) {
                this.galleryCmp.destroy();
            }.bind(this));
            this.galleryCmp.show();
        }.bind(this));
    };

    return ListView;
});

define('views/forum/viewView',[
    'component/galleryComponent'
], function (GalleryComponent) {
    

    var ViewView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickThumbnail = this._onClickThumbnail.bind(this);

        this.$container = options.container;
        this.$gallery = this.$container.find(ViewView.Binder.GALLERY);
        this.$thumbnails = this.$container.find(ViewView.Binder.THUMBNAILS);

        this.$thumbnails.on('click', this.bind__onClickThumbnail);

        var media = this.controller.model.get('ordered_media');
        if (media.length > 0) {
            GalleryComponent.render({
                $container: this.$gallery,
                mode: GalleryComponent.Mode.INLINE,
                media: media
            }, function (e) {
                this.galleryCmp = e.galleryComponent;
                this.galleryCmp.show();
            }.bind(this));
        }

        $tt._instances.push(this);
    };

    ViewView.TAG = 'ForumViewView';

    ViewView.Binder = {
        GALLERY: '.forum-gallery',
        THUMBNAILS: '.tt-grid-div-body .expand'
    };

    ViewView.prototype._onClickThumbnail = function (e) {
        e.preventDefault();

        GalleryComponent.render({
            mediaIds: [$(e.currentTarget).data('mid')]
        }, function (e) {
            this.galleryCmp = e.galleryComponent;
            this.galleryCmp.subscribe(GalleryComponent.Event.HIDDEN, function (e) {
                this.galleryCmp.destroy();
            }.bind(this));
            this.galleryCmp.show();
        }.bind(this));
    };

    return ViewView;
});

define('views/group/newView',['component/mediaChooserComponent'], function (MediaChooserComponent) {
    

    var NewView = function (controller, options) {
        this.controller = controller;

        this.bind__onSubmitForm = this._onSubmitForm.bind(this);
        this.bind__onUploadStart = this._onUploadStart.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onReset = this._onReset.bind(this);
        this.bind__onError = this._onError.bind(this);

        this.$container = options.container;
        this.$form = this.$container.find('form[name^=' + NewView.FORM_NAME + ']');
        this.$submit = this.$container.find(NewView.Binder.SUBMIT);

        this.$form.on('submit', this.bind__onSubmitForm);

        this.mcCmp = MediaChooserComponent.render(this.$form);
        this.mcCmp.subscribe(MediaChooserComponent.Event.UPLOAD_START, this.bind__onUploadStart);
        this.mcCmp.subscribe(MediaChooserComponent.Event.SUCCESS, this.bind__onSuccess);
        this.mcCmp.subscribe(MediaChooserComponent.Event.RESET, this.bind__onReset);
        this.mcCmp.subscribe(MediaChooserComponent.Event.ERROR, this.bind__onError);

        var mediaIds = [];
        this._getFormField('media').children().each(function (index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this.$submit.attr('disabled', true);
            this.mcCmp.setMedia(mediaIds);
        }

        $tt._instances.push(this);
    };

    NewView.TAG = 'GroupNewView';

    NewView.Binder = {
        SUBMIT: '.group-submit'
    };

    // this must be the same name defined in {bundle}/Form/Type/UserGroupType
    NewView.FORM_NAME = 'user_group';

    NewView.prototype._getFormField = function (fieldName) {
        return this.$form.find('#' + NewView.FORM_NAME + '_' + fieldName);
    };

    NewView.prototype._onSubmitForm = function (e) {
        if (this.$form[0].checkValidity()) {
            this.$submit.button('loading');
        }
    };

    NewView.prototype._updateForm = function () {
        var formField = this._getFormField('media');
        formField.html(this.mcCmp.generateFormData(formField.data('prototype')));
    };

    NewView.prototype._onUploadStart = function (e) {
        this.$submit.attr('disabled', true);
    };

    NewView.prototype._onSuccess = function (e) {
        this._updateForm();
        this.$submit.attr('disabled', false);
    };

    NewView.prototype._onReset = function (e) {
        this._updateForm();
    };

    NewView.prototype._onError = function (e) {
        alert('Error: ' + e.error);
    };

    return NewView;
});

define('views/group/editView',[
    'views/group/newView'
], function (NewView) {
    

    var EditView = function (controller, options) {
        NewView.prototype.constructor.apply(this, arguments);

        this.bind__onClickDelete = this._onClickDelete.bind(this);

        this.$deleteModal = this.$container.find(EditView.Binder.DELETE_MODAL);
        this.$delete = this.$container.find(EditView.Binder.DELETE);

        this.$delete.on('click', this.bind__onClickDelete);
    };

    EditView.extend(NewView);

    EditView.TAG = 'GroupEditView';

    EditView.Binder.DELETE_MODAL = '.group-delete-modal';
    EditView.Binder.DELETE = '.group-delete';

    EditView.prototype._onClickDelete = function (e) {
        e.preventDefault();

        this.$delete.button('loading');
        this.controller.delete()
            .done(function (data) {
                this.$deleteModal
                    .find('.modal-body')
                    .html('Group deleted successfully.');
            }.bind(this))
            .fail(function () {
                this.$container
                    .find('.modal-body')
                    .prepend('Something went wrong. Try again.');
                this.$delete.button('reset');
            }.bind(this));
    };

    return EditView;
});

define('views/group/listView',[
    'component/galleryComponent'
], function (GalleryComponent) {
    

    var ListView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickThumbnail = this._onClickThumbnail.bind(this);

        this.$container = options.container;
        this.$thumbnails = this.$container.find(ListView.Binder.THUMBNAILS);

        this.$thumbnails.on('click', this.bind__onClickThumbnail);

        $tt._instances.push(this);
    };

    ListView.TAG = 'GroupListView';

    ListView.Binder = {
        THUMBNAILS: '.tt-grid-div-body .expand'
    };

    ListView.prototype._onClickThumbnail = function (e) {
        e.preventDefault();

        GalleryComponent.render({
            mediaIds: [$(e.currentTarget).data('mid')]
        }, function (e) {
            this.galleryCmp = e.galleryComponent;
            this.galleryCmp.subscribe(GalleryComponent.Event.HIDDEN, function (e) {
                this.galleryCmp.destroy();
            }.bind(this));
            this.galleryCmp.show();
        }.bind(this));
    };

    return ListView;
});

define('views/group/viewView',[
    'component/galleryComponent'
], function (GalleryComponent) {
    

    var ViewView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickThumbnail = this._onClickThumbnail.bind(this);

        this.$container = options.container;
        this.$gallery = this.$container.find(ViewView.Binder.GALLERY);
        this.$thumbnails = this.$container.find(ViewView.Binder.THUMBNAILS);

        this.$thumbnails.on('click', this.bind__onClickThumbnail);

        var media = this.controller.model.get('ordered_media');
        if (media.length > 0) {
            GalleryComponent.render({
                $container: this.$gallery,
                mode: GalleryComponent.Mode.INLINE,
                media: media
            }, function (e) {
                this.galleryCmp = e.galleryComponent;
                this.galleryCmp.show();
            }.bind(this));
        }

        $tt._instances.push(this);
    };

    ViewView.TAG = 'GroupViewView';

    ViewView.Binder = {
        GALLERY: '.group-gallery',
        THUMBNAILS: '.tt-grid-div-body .expand'
    };

    ViewView.prototype._onClickThumbnail = function (e) {
        e.preventDefault();

        GalleryComponent.render({
            mediaIds: [$(e.currentTarget).data('mid')]
        }, function (e) {
            this.galleryCmp = e.galleryComponent;
            this.galleryCmp.subscribe(GalleryComponent.Event.HIDDEN, function (e) {
                this.galleryCmp.destroy();
            }.bind(this));
            this.galleryCmp.show();
        }.bind(this));
    };

    return ViewView;
});

define('views/group/manageView',[
    'component/tableComponent'
], function (TableComponent) {
    

    var ManageView = function (controller, options) {
        this.controller = controller;

        this.bind___onClickFormCheckboxLink = this._onClickFormCheckboxLink.bind(this);
        this.bind__onShownTab = this._onShownTab.bind(this);
        this.bind__onClickRemove = this._onClickRemove.bind(this);
        this.bind__onClickAdd = this._onClickAdd.bind(this);

        this.$container = options.container;
        this.$formSearch = this.$container.find('form[name^=' + ManageView.FORM_NAME_SEARCH + ']');
        this.$formRemove = this.$container.find('form[name^=' + ManageView.FORM_NAME_REMOVE + ']');
        this.$formAdd = this.$container.find('form[name^=' + ManageView.FORM_NAME_ADD + ']');
        this.$formCheckboxLinks = this.$formSearch.find('label[class^="checkbox"] a');
        this.$tabs = this.$container.find(ManageView.Binder.TABS);
        this.$tabPanes = this.$container.find(ManageView.Binder.TAB_PANES);
        this.$remove = this.$container.find(ManageView.Binder.REMOVE);
        this.$add = this.$container.find(ManageView.Binder.ADD);

        this.$formCheckboxLinks.on('click', this.bind___onClickFormCheckboxLink);
        this.$tabs.on('shown.bs.tab', this.bind__onShownTab);
        this.$remove.on('click', this.bind__onClickRemove);
        this.$add.on('click', this.bind__onClickAdd);

        var removeToggle = function (e) {
            this.$remove.attr('disabled', e.$selection.length == 0);
        }.bind(this);
        var addToggle = function (e) {
            this.$add.attr('disabled', e.$selection.length == 0);
        }.bind(this);
        var removeOrAdd = function (type) {
            return type == 'remove' ? removeToggle : addToggle;
        };

        this.tableCmps = [];
        this.$tabPanes.each(function (index, element) {
            var $tabPane = $(element);
            var toggle = removeOrAdd($tabPane.data('type'));
            var tblCmp = TableComponent.table($tabPane);
            tblCmp.subscribe(TableComponent.Event.SELECTION_CHANGE, toggle);
            this.tableCmps.push(tblCmp);
        }.bind(this));

        this.$tabs.filter('[href="' + (location.hash
            ? '#tab' + location.hash.substr(1) // strip '#'
            : '#tabMembers') + '"]').tab('show');

        $tt._instances.push(this);
    };

    ManageView.TAG = 'GroupManageView';

    ManageView.Binder = {
        TABS: 'a[data-toggle="tab"]',
        TAB_PANES: '[id^=tab]',
        REMOVE: '.group-remove',
        ADD: '.group-add'
    };

    ManageView.FORM_NAME = 'ugm_';
    ManageView.FORM_NAME_SEARCH = ManageView.FORM_NAME + 'search';
    ManageView.FORM_NAME_REMOVE = ManageView.FORM_NAME + 'remove';
    ManageView.FORM_NAME_ADD = ManageView.FORM_NAME + 'add';

    ManageView.prototype._getFormField = function (form, fieldName) {
        return form.find('#' + form.attr('name') + '_' + fieldName);
    };

    ManageView.prototype._onClickFormCheckboxLink = function (e) {
        e.preventDefault();

        var $cbs = this.$formSearch.find('input[type="checkbox"]');
        var $cb = $(e.target).parent().find('input[type="checkbox"]');
        var checked = $cb.prop('checked');
        $cbs.prop('checked', false); // disable all others first
        $cb.prop('checked', !checked);
        this.$formSearch[0].submit();
    };

    ManageView.prototype._onShownTab = function (e) {
        var hash = '#' + $(e.target).attr('href').substr(4); // strip '#tab'

        if (history.pushState) {
            history.pushState(null, null, hash);
        } else {
            location.hash = hash;
        }

        // KnpPaginatorBundle:Pagination:twitter_bootstrap_v3_pagination.html.twig
        // update hash on pagination urls
        this.$tabPanes.find('ul.pagination li a').each(function (index, element) {
            var $link = $(element);
            var url = $link.attr('href');
            var index = url.lastIndexOf('#');
            if (index > 0) {
                url = url.substring(0, index);
            }
            $link.attr('href', url + hash);
        });
    };

    ManageView.prototype._updateUsersSelectForm = function ($form, $button) {
        var $userList = this._getFormField($form, 'users');

        $button.button('loading');
        $button.toggleClass('disabled');

        $userList.html('');
        $.each(this.tableCmps, function (index, element) {
            element.getSelection().each(function (index, element) {
                var $newUser = $userList.data('prototype');
                $newUser = $($newUser.replace(/__name__/g, index));
                $newUser.val($(element).data('uid'));
                $userList.append($newUser);
            });
        }.bind(this));
    };

    ManageView.prototype._onClickRemove = function (e) {
        e.preventDefault();

        this._updateUsersSelectForm(this.$formRemove, this.$remove);

        this.$formRemove.submit();
    };

    ManageView.prototype._onClickAdd = function (e) {
        e.preventDefault();

        this._updateUsersSelectForm(this.$formAdd, this.$add);

        this.$formAdd.submit();
    };

    return ManageView;
});

define('views/home/indexView',[
    'component/galleryComponent'
], function (GalleryComponent) {
    

    var IndexView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickThumbnail = this._onClickThumbnail.bind(this);

        this.$container = options.container;
        this.$thumbnails = this.$container.find(IndexView.Binder.THUMBNAILS);

        this.$thumbnails.on('click', this.bind__onClickThumbnail);

        $tt._instances.push(this);
    };

    IndexView.TAG = 'HomeIndexView';

    IndexView.Binder = {
        THUMBNAILS: '.tt-grid-div-body .expand'
    };

    IndexView.prototype._onClickThumbnail = function (e) {
        e.preventDefault();

        GalleryComponent.render({
            mediaIds: [$(e.currentTarget).data('mid')]
        }, function (e) {
            this.galleryCmp = e.galleryComponent;
            this.galleryCmp.subscribe(GalleryComponent.Event.HIDDEN, function (e) {
                this.galleryCmp.destroy();
            }.bind(this));
            this.galleryCmp.show();
        }.bind(this));
    };

    return IndexView;
});

define('views/message/newView',[
    'component/mediaChooserComponent'
], function (MediaChooserComponent) {
    

    var NewView = function (controller, options) {
        this.controller = controller;

        this.bind__onSubmitForm = this._onSubmitForm.bind(this);
        this.bind__onUploadStart = this._onUploadStart.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onReset = this._onReset.bind(this);
        this.bind__onError = this._onError.bind(this);

        this.$container = options.container;
        this.$form = this.$container.find('form[name^=' + NewView.FORM_NAME + ']');
        this.$submit = this.$container.find(NewView.Binder.SUBMIT);

        this.$form.on('submit', this.bind__onSubmitForm);

        this.mcCmp = MediaChooserComponent.render(this.$form);
        this.mcCmp.subscribe(MediaChooserComponent.Event.UPLOAD_START, this.bind__onUploadStart);
        this.mcCmp.subscribe(MediaChooserComponent.Event.SUCCESS, this.bind__onSuccess);
        this.mcCmp.subscribe(MediaChooserComponent.Event.RESET, this.bind__onReset);
        this.mcCmp.subscribe(MediaChooserComponent.Event.ERROR, this.bind__onError);

        var mediaIds = [];
        this._getFormField('attachedMedia').children().each(function (index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this.$submit.attr('disabled', true);
            this.mcCmp.setMedia(mediaIds);
        }

        this._getFormField('recipients').tagit();

        $tt._instances.push(this);
    };

    NewView.TAG = 'MessageNewView';

    NewView.Binder = {
        SUBMIT: '.message-submit'
    };

    // this must be the same name defined in {bundle}/Form/Type/PrivateMessageType
    NewView.FORM_NAME = 'PrivateMessageForm';

    NewView.prototype._getFormField = function (fieldName) {
        return this.$form.find('#' + NewView.FORM_NAME + '_' + fieldName);
    };

    NewView.prototype._onSubmitForm = function (e) {
        if (this.$form[0].checkValidity()) {
            this.$submit.button('loading');
        }
    };

    NewView.prototype._updateForm = function () {
        var formField = this._getFormField('attachedMedia');
        formField.html(
            this.mcCmp.generateFormData(
                formField.data('prototype')
            )
        );
    };

    NewView.prototype._onUploadStart = function (e) {
        this.$submit.attr('disabled', true);
    };

    NewView.prototype._onSuccess = function (e) {
        this.$submit.attr('disabled', false);

        this._updateForm();
    };

    NewView.prototype._onReset = function (e) {
        this._updateForm();
    };

    NewView.prototype._onError = function (e) {
        alert('Error: ' + e.error);
    };

    return NewView;
});

define('views/message/replyView',[
    'views/message/newView'
], function (NewView) {
    

    var ReplyView = function (controller, options) {
        NewView.prototype.constructor.apply(this, arguments);
    };

    ReplyView.extend(NewView);

    ReplyView.TAG = 'MessageReplyView';

    return ReplyView;
});

define('views/message/viewView',[
    'component/galleryComponent'
], function (GalleryComponent) {
    

    var ViewView = function (controller, options) {
        this.controller = controller;

        this.$container = options.container;
        this.$gallery = this.$container.find(ViewView.Binder.GALLERY);

        var media = this.controller.model.get('ordered_media');
        if (media.length > 0) {
            GalleryComponent.render({
                $container: this.$gallery,
                mode: GalleryComponent.Mode.INLINE,
                media: media
            }, function (e) {
                this.galleryCmp = e.galleryComponent;
                this.galleryCmp.show();
            }.bind(this));
        }

        $tt._instances.push(this);
    };

    ViewView.TAG = 'MessageViewView';

    ViewView.Binder = {
        GALLERY: '.message-gallery'
    };

    return ViewView;
});

define('views/myFiles/listView',[
    'service',
    'model/model',
    'component/tableComponent',
    'component/mediaChooserComponent',
    'component/galleryComponent',
    'core/helper'
], function (Service, Model, TableComponent, MediaChooserComponent, GalleryComponent, Helper) {
    

    var ListView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickBulkAction = this._onClickBulkAction.bind(this);
        this.bind__onClickFile = this._onClickFile.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onError = this._onError.bind(this);
        this.bind__onAddGridElement = this._onAddGridElement.bind(this);
        this.bind__onAddListElement = this._onAddListElement.bind(this);

        this.$container = options.container;
        this.$filesList = this.$container.find(ListView.Binder.FILES_LIST);
        this.$fileButtons = this.$container.find(ListView.Binder.FILE_BUTTON);

        this.tblCmp = TableComponent.table(this.$filesList);
        this.tblCmp.subscribe(TableComponent.Event.CLICK_BULK_ACTION, this.bind__onClickBulkAction);

        this.$filesList.find('button.edit-title').on('click', function (e) {
            e.stopPropagation();
            $(this).parent().parent().parent().find('span.edit-title').editable('toggle');
        });

        this.$fileButtons.not('[disabled]').on('click', this.bind__onClickFile);

        if (this.$filesList.children('div.table-responsive').length > 0)
        {
           this.style = ListView.Binder.STYLE_LIST;
        }
        else
        {
            this.style = ListView.Binder.STYLE_GRID;
        }
        
        this.mcCmp = MediaChooserComponent.render(this.$container);
        this.mcCmp.subscribe(MediaChooserComponent.Event.SUCCESS, this.bind__onSuccess);
        this.mcCmp.subscribe(MediaChooserComponent.Event.RESET, this.bind__onReset);
        this.mcCmp.subscribe(MediaChooserComponent.Event.ERROR, this.bind__onError);
        
        this.$filesList.find('span.edit-title').editable({
            toggle: 'manual',
            unsavedclass: null,
            pk: function () {
                return $(this).data('mid')
            },
            url: function (params) {
                return this.controller.edit(params.pk, {title: params.value});
            }.bind(this)
        });

        var sub = Service.get('subscriber');
        sub.dispatch(ListView.TAG, 'onViewLoaded', {
            view: this
        });

        //TODO update/re-render the entire thumbnail container
        this.controller.model.subscribe(Model.Event.CHANGE, function (e) {
            var media = e.model.get(e.keyPath);
            var $title = this.$filesList.find('span.edit-title[data-mid="' + media.get('id') + '"]');
            $title.html(media.get('title'));
        }.bind(this));

        $tt._instances.push(this);
    };

    ListView.TAG = 'MyFilesListView';

    ListView.Binder = {
        TOGGLE_STYLE: '.my-files-selector-toggle-style',
        FILES_LIST: '.my-files-selector-files-list',
        FILE_BUTTON: '.my-files-selector-file-button',
        STYLE_LIST: 'list',
        STYLE_GRID: 'grid'
    };
    
    ListView.MAX_PER_PAGE = 24;
    ListView.MAX_PER_ROW = 4;

    ListView.prototype._onClickBulkAction = function (e) {
        switch (e.action) {
            case 1: // delete
                // FIXME confirmation. update controller to allow mass deletion
                if (confirm(Translator.trans('filesGateway.deleteConfirmMessage'))) {
                    $.each(e.$selection, (function (index, element) {
                        this.controller.delete($(element).data('mid'), true);
                        var $fullElement; 
                        if (this.style == ListView.Binder.STYLE_LIST)
                            $fullElement = $(element).parents('tr');
                        else
                            $fullElement = $(element).parents('div.col-md-3');
                        $fullElement.fadeOut(1000, function() {
                    		$fullElement.remove();
                        });
                        $(element).prop('checked', false);
                    }).bind(this));
                    
                    var wait = setInterval((function () {
                	this.$filesList = this.$container.find(ListView.Binder.FILES_LIST);
                	this.$fileButtons = this.$container.find(ListView.Binder.FILE_BUTTON);
                	var elements = $(this.$fileButtons).parent("div.thumbnail.tt-grid-div-body").parent();
                	if (!$(elements).is(":animated"))
                	{
                	    this.tblCmp.updateElements(this.$filesList);
                	    clearInterval(wait)
                	    if (this.style == ListView.Binder.STYLE_GRID)
                	    {
                		var allRows = $(elements).parent();
                		var count = $(this.$fileButtons).parent("div.thumbnail.tt-grid-div-body").length;
                		for (var i=1; i<count; i++)
                		{
                		    if (!$(elements[i]).parent().is($(elements[i-1]).parent()) && i % ListView.MAX_PER_ROW !=0)
                		    {
                			$(elements[i]).insertAfter($(elements[i-1]));
                		    }
                		}
                		//remove empty rows
                		$.each($(allRows), function (index, element) {
                		    if ($(element).children().length == 0) 
                			$(element).remove();
                		});
                	    }
                	}
                	
                    }).bind(this), 200);
                    
                }

                // FIXME: make me better
//                setTimeout(function () {
//                    window.location.reload(true);
//                }, 2000);
                break;
        }
    };

    ListView.prototype._onClickFile = function (e) {
        e.stopPropagation();

        var media = this.controller.model.getMedia($(e.currentTarget).data('mid'));
        if (!media) {
            throw new Error('media not found');
        }

        GalleryComponent.render({
            // mediaIds: [$(e.currentTarget).data('mid')]
            media: this.controller.model.get('media'),
            activeMedia: media
        }, function (e) {
            this.galleryCmp = e.galleryComponent;
            this.galleryCmp.subscribe(GalleryComponent.Event.HIDDEN, function (e) {
                this.galleryCmp.destroy();
            }.bind(this));
            this.galleryCmp.show();
        }.bind(this));
    };

    ListView.prototype.getTableComponent = function () {
        return this.tblCmp;
    };

    ListView.prototype._onAddGridElement = function (err, out) {
//	console.log ("OnAddGridElement");
//	console.log (err);
	var newElement = $(out);
	var count = $(this.$fileButtons).parent("div.thumbnail.tt-grid-div-body").length;
	var elements = $(this.$fileButtons).parent("div.thumbnail.tt-grid-div-body").parent();
	if (count >= ListView.MAX_PER_PAGE)
	{
//	    console.log("Maximum elements on the page, removing last from view");
	    newElement.insertBefore(elements[0]);
	    for (var i=ListView.MAX_PER_ROW-1; i<count; i+=ListView.MAX_PER_ROW)
	    {
		$(elements[i]).insertBefore($(elements[i+1]));
	    }
	    $(elements[count-1]).remove();
	}
	else if (count % ListView.MAX_PER_ROW == 0)
	{
	    //Need to add row
	    var newRow = $("<div class='row'></div>"); 
	    var parent = $(this.$filesList.children('div.tt-myFiles-grid-div')[0]);
	    if (count == 0)
	    {
		parent.empty();
		newRow.append(newElement);
		parent.append(newRow);
	    }
	    else
	    {
    	    	newElement.insertBefore(elements[0]);
    	    	for (var i=ListView.MAX_PER_ROW-1; i<count; i+=ListView.MAX_PER_ROW)
    	    	{
    	    	    $(elements[i]).insertBefore($(elements[i+1]));
    	    	}
    	    	parent.append(newRow);	    
    	    	newRow.append($(elements[count-1]).detach())
	    }
	}
	else
	{
	    newElement.insertBefore(elements[0]);
	    for (var i=ListView.MAX_PER_ROW-1; i<count; i+=ListView.MAX_PER_ROW)
	    {
	        $(elements[i]).insertBefore($(elements[i+1]));
	    }
	}
	newElement.find('button.edit-title').on('click', function (e) {
            e.stopPropagation();
            $(this).parent().parent().parent().find('span.edit-title').editable('toggle');
        });
	newElement.find('span.edit-title').editable({
            toggle: 'manual',
            unsavedclass: null,
            pk: function () {
                return $(this).data('mid')
            },
            url: function (params) {
                return this.controller.edit(params.pk, {title: params.value});
            }.bind(this)
        });
	newElement.find('.my-files-selector-file-click').not('[disabled]').on('click', this.bind__onClickFile);
	//Reinitialize the files and filesList
	this.$filesList = this.$container.find(ListView.Binder.FILES_LIST);
        this.$fileButtons = this.$container.find(ListView.Binder.FILE_BUTTON);
        this.tblCmp.updateElements(this.$filesList);
	
    }
    
    ListView.prototype._onAddListElement = function (err, out) {
//	console.log ("OnAddListElement");
//	console.log (err);
//	console.log (out);
	var newElement = $(out);
	var count = $(this.$fileButtons).parent("tr").length;
	var elements = $(this.$fileButtons).parent("tr");
	if (count >= ListView.MAX_PER_PAGE)
	{
//	    console.log("Maximum elements on the page, removing last from view");
	    newElement.insertBefore(elements[0]);
	    elements[count-1].remove();
	}
	else if (count == 0)
	{
//	    console.log("No elements on page");
	    //Remove the text saying there are no elements.
	    $(this.$filesList).find('div.table-responsive > div').remove();
	    elements = $(this.$filesList).find('tbody > tr');
	    newElement.insertBefore(elements[0]);
	    $(elements[0]).remove();
	}
	else
	{
//	    console.log("Else");
	    newElement.insertBefore(elements[0]);
	}
	
	newElement.find('button.edit-title').on('click', function (e) {
            e.stopPropagation();
            $(this).parent().parent().parent().find('span.edit-title').editable('toggle');
        });
	newElement.find('span.edit-title').editable({
            toggle: 'manual',
            unsavedclass: null,
            pk: function () {
                return $(this).data('mid')
            },
            url: function (params) {
                return this.controller.edit(params.pk, {title: params.value});
            }.bind(this)
        });
	newElement.find('.my-files-selector-file-click').not('[disabled]').on('click', this.bind__onClickFile);
	
	this.$filesList = this.$container.find(ListView.Binder.FILES_LIST);
        this.$fileButtons = this.$container.find(ListView.Binder.FILE_BUTTON);
        this.tblCmp.updateElements(this.$filesList);
	
    }
    
    ListView.prototype._onSuccess = function (e) {
// window.location.reload(true); //TODO load to last page?
//	console.log("On success:");
	console.log(e);
	var media = e.media[e.media.length - 1].data;
//	console.log(media);
	this.controller.model.addMedia(media);
	if (this.style == ListView.Binder.STYLE_GRID)
	{
	    
	    dust.render('myFilesGridElement', {
	            media: media,
	            previewTitle: Translator.trans('filesGateway.previewLink'),
	            editTitle: Translator.trans('form.media.title'),
	            shareTitle: Translator.trans('filesGateway.shareLink'),
	            shareUrl: Routing.generate('imdc_thread_new_from_media', {mediaId: media.id}),
	            mediaIcon: Helper.getIconForMediaType(media.type),
	        }, this.bind__onAddGridElement);
	}
	else
	{
	    var size = Helper.formatSize(media.source_resource.meta_data.size);
	    var spinner = false;
	    if (size <0)
		spinner = true;
	    dust.render('myFilesListElement', {
	            media: media,
	            previewTitle: Translator.trans('filesGateway.previewLink'),
	            editTitle: Translator.trans('form.media.title'),
	            shareTitle: Translator.trans('filesGateway.shareLink'),
	            shareUrl: Routing.generate('imdc_thread_new_from_media', {mediaId: media.id}),
	            mediaIcon: Helper.getIconForMediaType(media.type),
	            timeUploaded: dust.filters.date(media.source_resource.meta_data.time_created),
	            spinner: spinner,
	            formattedSize: size
	        }, this.bind__onAddListElement);
	}
    };
    
    ListView.prototype._onError = function (e) {
        alert('Error: ' + e.error);
    };

    return ListView;
});

define('views/post/newView',[
    'model/model',
    'component/mediaChooserComponent'
], function (Model, MediaChooserComponent) {
    

    var NewView = function (controller, options) {
        this.controller = controller;
        this.options = options;

        this.bind__onClickSubmit = this._onClickSubmit.bind(this);
        this.bind__onClickReset = this._onClickReset.bind(this);
        this.bind__onClickCancel = this._onClickCancel.bind(this);
        this.bind__onUploadStart = this._onUploadStart.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onSuccessAndPost = this._onSuccessAndPost.bind(this);
        this.bind__onReset = this._onReset.bind(this);
        this.bind__onModelChange = this._onModelChange.bind(this);
        this.bind__onError = this._onError.bind(this);

        this.$container = $(NewView.Binder.CONTAINER + '[data-pid="' + this.controller.model.get('id') + '"]');
        this.$form = this.$container.find('form[name^=' + NewView.FORM_NAME + ']');
        this.$submit = this.$container.find(NewView.Binder.SUBMIT);
        this.$reset = this.$container.find(NewView.Binder.RESET);
        this.$cancel = this.$container.find(NewView.Binder.CANCEL);

        this.$submit.on('click', this.bind__onClickSubmit);
        this.$reset.on('click', this.bind__onClickReset);
        this.$cancel.on('click', this.bind__onClickCancel);

        this.mcCmp = MediaChooserComponent.render(this.$form, {enableDoneAndPost: true});
        this.mcCmp.subscribe(MediaChooserComponent.Event.UPLOAD_START, this.bind__onUploadStart);
        this.mcCmp.subscribe(MediaChooserComponent.Event.SUCCESS, this.bind__onSuccess);
        this.mcCmp.subscribe(MediaChooserComponent.Event.SUCCESS_AND_POST, this.bind__onSuccessAndPost);
        this.mcCmp.subscribe(MediaChooserComponent.Event.RESET, this.bind__onReset);
        this.mcCmp.subscribe(MediaChooserComponent.Event.ERROR, this.bind__onError);

        this.controller.model.subscribe(Model.Event.CHANGE, this.bind__onModelChange);

        $tt._instances.push(this);
    };

    NewView.TAG = 'PostNewView';

    NewView.Binder = {
        CONTAINER: '.post-container',
        SUBMIT: '.post-submit-new',
        RESET: '.post-reset',
        CANCEL: '.post-cancel-new'
    };

    // this must be the same name defined in {bundle}/Form/Type/PostType
    NewView.FORM_NAME = 'post';

    NewView.prototype.loadView = function () {
        var mediaIds = [];
        this._getFormField('attachedFile').children().each(function (index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this._toggleForm(true);
            this.mcCmp.setMedia(mediaIds);
        }
    };

    NewView.prototype._getFormField = function (fieldName) {
        return this.$form.find('#' + NewView.FORM_NAME + '_' + fieldName);
    };

    NewView.prototype._toggleForm = function (disabled) {
        this.$submit.button(disabled ? 'loading' : 'reset');
        this.$reset.attr('disabled', disabled);
        this.$cancel.attr('disabled', disabled);
    };

    NewView.prototype._preSubmit = function () {
        if (this._getFormField('content').val() == '' && this.mcCmp.media.length == 0) {
            alert('Your post cannot be blank. You must either select a file or write a comment.');
            return false;
        }
        this._toggleForm(true);
        return true;
    };

    NewView.prototype._reset = function () {
        this.mcCmp.reset();
        this._getFormField('startTime').val(this.controller.model.get('keyPoint.startTime'));
        this._getFormField('endTime').val(this.controller.model.get('keyPoint.endTime'));
        this._getFormField('content').val('');

        this.controller.editKeyPoint({cancel: true});
    };

    NewView.prototype._destroy = function () {
        this.$container.remove();
        this.controller.removeKeyPoint();
        this.controller.removeFromThread();
    };

    NewView.prototype._onClickSubmit = function (e) {
        e.preventDefault();

        if (!this._preSubmit())
            return;

        this.controller.post(this.$form[0])
            .done(function (data) {
                this.controller.addToThread(data.post);

                if (this.options.is_permanent) {
                    this._reset();
                    this._toggleForm(false);
                } else {
                    this._destroy();
                }
            }.bind(this))
            .fail(function (data) {
                this.controller.updateInThread('new');
            }.bind(this));
    };

    NewView.prototype._onClickReset = function (e) {
        e.preventDefault();

        this._reset();
    };

    NewView.prototype._onClickCancel = function (e) {
        e.preventDefault();

        this._destroy();
    };

    NewView.prototype._onSelectionTimes = function (startTime, endTime) {
        this._getFormField('startTime').val(startTime);
        this._getFormField('endTime').val(endTime);
    };

    NewView.prototype._onModelChange = function (e) {
        this._onSelectionTimes(
            e.model.get('keyPoint.selection.startTime', ''),
            e.model.get('keyPoint.selection.endTime', '')
        );
    };

    NewView.prototype._updateForm = function () {
        var formField = this._getFormField('attachedFile');
        formField.html(
            this.mcCmp.generateFormData(
                formField.data('prototype')
            )
        );
    };

    NewView.prototype._onUploadStart = function (e) {
        this.$submit.attr('disabled', true);
    };

    NewView.prototype._onSuccess = function (e) {
        this._updateForm();
        this.$submit.attr('disabled', false);
        this._toggleForm(false);
    };

    NewView.prototype._onSuccessAndPost = function (e) {
        this._updateForm();
        this.$submit.trigger('click');
    };

    NewView.prototype._onReset = function (e) {
        this._updateForm();
    };

    NewView.prototype._onError = function (e) {
        alert('Error: ' + e.error);
    };

    return NewView;
});

define('views/post/editView',[
    'views/post/newView'
], function (NewView) {
    

    var EditView = function (controller, options) {
        NewView.prototype.constructor.apply(this, arguments);

        this.bind__onClickSubmit = this._onClickSubmit.bind(this);
        this.bind__onClickCancel = this._onClickCancel.bind(this);

        this.$submit = this.$container.find(EditView.Binder.SUBMIT);
        this.$cancel = this.$container.find(EditView.Binder.CANCEL);

        this.$submit.on("click", this.bind__onClickSubmit);
        this.$cancel.on("click", this.bind__onClickCancel);
    };

    EditView.extend(NewView);

    EditView.TAG = 'PostEditView';

    EditView.Binder.SUBMIT = '.post-submit-edit';
    EditView.Binder.CANCEL = '.post-cancel-edit';

    EditView.prototype._toggleForm = function (disabled) {
        this.$submit.button(disabled ? 'loading' : 'reset');
        this.$cancel.attr('disabled', disabled);
    };

    EditView.prototype._onClickSubmit = function (e) {
        e.preventDefault();

        if (!this._preSubmit())
            return;

        this.controller.put(this.$form[0])
            .done(function (data) {
                this.controller.updateInThread('view');

                if (this.controller.model.get('is_temporal', false)) {
                    this.controller.editKeyPoint({cancel: true});
                }
            }.bind(this))
            .fail(function (data) {
                this.controller.updateInThread('edit');
            }.bind(this));
    };

    EditView.prototype._onClickCancel = function (e) {
        e.preventDefault();

        this.controller.get()
            .done(function (data) {
                this.controller.updateInThread('view');

                if (this.controller.model.get('is_temporal', false)) {
                    this.controller.editKeyPoint({cancel: true});
                }
            }.bind(this))
            .fail(function (data) {
                //TODO
            });
    };

    return EditView;
});

define('views/post/viewView',[
    'model/model',
    'component/galleryComponent'
], function (Model, GalleryComponent) {
    

    var ViewView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickHoverKeyPoint = this._onClickHoverKeyPoint.bind(this);
        this.bind__onClickNew = this._onClickNew.bind(this);
        this.bind__onClickEdit = this._onClickEdit.bind(this);
        this.bind__onClickDelete = this._onClickDelete.bind(this);
        this.bind__onModelChange = this._onModelChange.bind(this);

        this.$container = $(ViewView.Binder.CONTAINER + '[data-pid="' + this.controller.model.get('id') + '"]');
        this.$timelineKeyPoint = this.$container.find(ViewView.Binder.TIMELINE_KEYPOINT);
        this.$gallery = this.$container.find(ViewView.Binder.GALLERY);
        this.$new = this.$container.find(ViewView.Binder.NEW);
        this.$edit = this.$container.find(ViewView.Binder.EDIT);
        this.$deleteModal = this.$container.find(ViewView.Binder.DELETE_MODAL);
        this.$delete = this.$container.find(ViewView.Binder.DELETE);

        this.$timelineKeyPoint.on('click', this.bind__onClickHoverKeyPoint);
        this.$timelineKeyPoint.on('dblclick', this.bind__onClickHoverKeyPoint);
        this.$timelineKeyPoint.hover(
            this.bind__onClickHoverKeyPoint,
            this.bind__onClickHoverKeyPoint);
        this.$new.on('click', this.bind__onClickNew);
        this.$edit.on('click', this.bind__onClickEdit);
        this.$delete.on('click', this.bind__onClickDelete);

        this.controller.model.subscribe(Model.Event.CHANGE, this.bind__onModelChange);

        var media = this.controller.model.get('ordered_media');
        if (media && media.length > 0) {
            GalleryComponent.render({
                $container: this.$gallery,
                mode: GalleryComponent.Mode.INLINE,
                media: media
            }, function (e) {
                this.galleryCmp = e.galleryComponent;
                this.galleryCmp.show();
            }.bind(this));
        }

        $tt._instances.push(this);
    };

    ViewView.TAG = 'PostViewView';

    ViewView.Binder = {
        CONTAINER: '.post-container',
        TIMELINE_KEYPOINT: '.post-timeline-keypoint',
        GALLERY: '.post-gallery',
        NEW: '.post-new',
        EDIT: '.post-edit',
        DELETE_MODAL: '.post-delete-modal',
        DELETE: '.post-delete'
    };

    // clicking the clock icon will move the density bar to the comments time
    // and highlight the comment on the density bar
    // mousing over the clock icon should highlight the comment on the density bar
    ViewView.prototype._onClickHoverKeyPoint = function (e) {
        switch (e.type) {
            case 'mouseenter':
            case 'mouseleave':
                this.controller.hoverKeyPoint({
                    isMouseOver: e.type == 'mouseenter'
                });
                break;
            case 'click':
            case 'dblclick':
                e.preventDefault();
                this.controller.clickKeyPoint({
                    isDblClick: e.type == 'dblclick'
                });
                break;
        }
    };

    ViewView.prototype._onClickNew = function (e) {
        e.preventDefault();

        this.$new.hide();
        this.controller.new(null)
            .done(function (data) {
                this.controller.addToThread(data.post);
            }.bind(this))
            .fail(function (data) {
                this.$new.show();
            }.bind(this));
    };

    ViewView.prototype._onClickEdit = function (e) {
        e.preventDefault();

        this.controller.edit()
            .done(function (data) {
                this.controller.updateInThread('edit');

                if (this.controller.model.get('is_temporal', false)) {
                    this.controller.editKeyPoint({cancel: false});
                }
            }.bind(this))
            .fail(function (data) {
                //TODO
            });
    };

    ViewView.prototype._onClickDelete = function (e) {
        e.preventDefault();

        this.$delete.button('loading');
        this.controller.delete()
            .done(function (data) {
                this.$deleteModal
                    .find('.modal-body')
                    .html('Post deleted successfully.');

                if (this.$deleteModal.data('bs.modal').isShown) {
                    this.$deleteModal.on('hidden.bs.modal', function (e) {
                        this.controller.removeFromThread();
                    }.bind(this));
                    this.$deleteModal.modal('hide');
                } else {
                    this.controller.removeFromThread();
                }
            }.bind(this))
            .fail(function (data) {
                this.$container
                    .find('.modal-body')
                    .prepend('Something went wrong. Try again.');
                this.$delete.button('reset');
            }.bind(this));
    };

    ViewView.prototype._renderKeyPoint = function (startTime, endTime, videoDuration) {
        console.log('%s: %s', ViewView.TAG, '_renderKeyPoint');

        var startTimePercentage = ((100 * startTime) / videoDuration).toFixed(2);
        var endTimePercentage = ((100 * endTime) / videoDuration).toFixed(2);
        var widthPercentage = (endTimePercentage - startTimePercentage).toFixed(2);

        this.$timelineKeyPoint.css({
            left: startTimePercentage + '%',
            width: widthPercentage + '%'
        });
    };

    ViewView.prototype._hoverKeyPoint = function (isHovering) {
        if (isHovering) {
            this.$container.addClass('tt-post-container-highlight');
        } else {
            this.$container.removeClass('tt-post-container-highlight');
        }
    };

    ViewView.prototype._clickKeyPoint = function (isPlaying) {
        var video = this.$container.find('video')[0];
        if (video && isPlaying) {
            video.currentTime = 0;
            video.play();
        }
    };

    ViewView.prototype._onModelChange = function (e) {
        this._renderKeyPoint(
            e.model.get('keyPoint.startTime', ''),
            e.model.get('keyPoint.endTime', ''),
            e.model.get('keyPoint.videoDuration', '')
        );
        this._hoverKeyPoint(
            e.model.get('keyPoint.isPlayerHovering', false)
        );
        this._clickKeyPoint(
            e.model.get('keyPoint.isPlayerPlaying', false)
        );
        this.$new.show();
    };

    return ViewView;
});

define('views/profile/editView',[],function () {
    

    var EditView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickAddLanguage = this._onClickAddLanguage.bind(this);
        this.bind__onClickDeleteLanguage = this._onClickDeleteLanguage.bind(this);

        this.$container = options.container;
        this.$addLang = this.$container.find(EditView.Binder.ADD_LANGUAGE);
        this.$delLang = this.$container.find(EditView.Binder.DELETE_LANGUAGE);

        this.$addLang.on('click', this.bind__onClickAddLanguage);
        this.$delLang.on('click', this.bind__onClickDeleteLanguage);

        $tt._instances.push(this);
    };

    EditView.TAG = 'ProfileEditView';

    EditView.Binder = {
        ADD_LANGUAGE: '.profile-add-lang',
        DELETE_LANGUAGE: '.profile-del-lang'
    };

    EditView.prototype._onClickAddLanguage = function(e) {
        e.preventDefault();

        var languageList = $("#foss_user_profile_form_languages");
        var deleteButton;
        var deleteLanguageButtonText = languageList.data("delete-language-text");
        var newWidget = languageList.data("prototype"); // grab the prototype template
        var languageCount = $("#foss_user_profile_form_languages > li").length;

        $('#no-languages').remove();

        //TODO dustjs?
        deleteButton = $("<button class=\"btn btn-danger btn-sm profile-del-lang\"></a>").html(deleteLanguageButtonText);
        deleteButton.on('click', this.bind__onClickDeleteLanguage);

        // replace the "__name__" used in the id and name of the prototype
        // with a number that's unique to your languages
        newWidget = newWidget.replace(/__name__/g, languageCount);
        languageCount++;

        // create a new list element and add it to the list
        var newLi = $("<li></li>").html(newWidget);
        newLi.append(deleteButton);
        $('#foss_user_profile_form_languages').append(newLi);
    };

    EditView.prototype._onClickDeleteLanguage = function(e) {
        e.preventDefault();

        var languageList = $("#foss_user_profile_form_languages");
        var emptyTitle = languageList.data("no-languages-text");
        var noLanguages = $("<span id=\"no-languages\"></span>").html(emptyTitle); //TODO dustjs?

        $(e.target).parent().remove();

        if ($("#foss_user_profile_form_languages > li").length == 0) {
            $('#foss_user_profile_form_languages').append(noLanguages);
        }
    };

    return EditView;
});

define('views/thread/newView',[
    'component/mediaChooserComponent',
    'component/accessTypeComponent'
], function (MediaChooserComponent, AccessTypeComponent) {
    

    var NewView = function (controller, options) {
        this.controller = controller;

        this.bind__onSubmitForm = this._onSubmitForm.bind(this);
        this.bind__onUploadStart = this._onUploadStart.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onRemove = this._onRemove.bind(this);
        this.bind__onReset = this._onReset.bind(this);
        this.bind__onError = this._onError.bind(this);

        this.$container = options.container;
        this.$form = this.$container.find('form[name^=' + NewView.FORM_NAME + ']');
        this.$submit = this.$container.find(NewView.Binder.SUBMIT);

        this.$form.on('submit', this.bind__onSubmitForm);

        this.atCmp = AccessTypeComponent.render(this.$form);

        this.mcCmp = MediaChooserComponent.render(this.$form);
        this.mcCmp.subscribe(MediaChooserComponent.Event.UPLOAD_START, this.bind__onUploadStart);
        this.mcCmp.subscribe(MediaChooserComponent.Event.SUCCESS, this.bind__onSuccess);
        this.mcCmp.subscribe(MediaChooserComponent.Event.REMOVE, this.bind__onRemove);
        this.mcCmp.subscribe(MediaChooserComponent.Event.RESET, this.bind__onReset);
        this.mcCmp.subscribe(MediaChooserComponent.Event.ERROR, this.bind__onError);

        var mediaIds = [];
        this._getFormField('mediaIncluded').children().each(function (index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this.$submit.attr('disabled', true);
            this.mcCmp.setMedia(mediaIds);
        }

        $tt._instances.push(this);
    };

    NewView.TAG = 'ThreadNewView';

    NewView.Binder = {
        SUBMIT: '.thread-submit'
    };

    // this must be the same name defined in {bundle}/Form/Type/ThreadType
    NewView.FORM_NAME = 'thread';

    NewView.prototype._getFormField = function (fieldName) {
        return this.$form.find('#' + NewView.FORM_NAME + '_' + fieldName);
    };

    NewView.prototype._onSubmitForm = function (e) {
        if (this.$form[0].checkValidity()) {
            this.$submit.button('loading');
        }
    };

    NewView.prototype._updateForm = function () {
        var $formField = this._getFormField('mediaIncluded');
        $formField.html(
            this.mcCmp.generateFormData(
                $formField.data('prototype')
            )
        );

        $formField = this._getFormField('title');
        $formField.attr('required', this.mcCmp.media.length == 0);

        $formField = $formField.parent().find('label');
        if (this.mcCmp.media.length == 0) {
            $formField.addClass('required');
        } else {
            $formField.removeClass('required');
        }
    };

    NewView.prototype._onUploadStart = function (e) {
        this.$submit.attr('disabled', true);
    };

    NewView.prototype._onSuccess = function (e) {
        this._updateForm();

        this.$submit.attr('disabled', false);
    };

    NewView.prototype._onRemove = function (e) {
        this._updateForm();
    };

    NewView.prototype._onReset = function (e) {
        this._updateForm();
    };
    
    NewView.prototype._onError = function (e) {
	    alert('Error: ' + e.error);
    };

    return NewView;
});

define('views/thread/editView',[
    'views/thread/newView'
], function (NewView) {
    

    var EditView = function (controller, options) {
        NewView.prototype.constructor.apply(this, arguments);

        this.bind__onClickDelete = this._onClickDelete.bind(this);

        this.$deleteModal = this.$container.find(EditView.Binder.DELETE_MODAL);
        this.$delete = this.$container.find(EditView.Binder.DELETE);

        this.$delete.on('click', this.bind__onClickDelete);
    };

    EditView.extend(NewView);

    EditView.TAG = 'ThreadEditView';

    EditView.Binder.DELETE_MODAL = '.thread-delete-modal';
    EditView.Binder.DELETE = '.thread-delete';

    EditView.prototype._onClickDelete = function (e) {
        e.preventDefault();

        this.$delete.button('loading');
        this.controller.delete()
            .done(function (data) {
                this.$deleteModal
                    .find('.modal-body')
                    .html('Topic deleted successfully.');
            }.bind(this))
            .fail(function () {
                this.$container
                    .find('.modal-body')
                    .prepend('Something went wrong. Try again.');
                this.$delete.button('reset');
            }.bind(this));
    };

    return EditView;
});

define('views/thread/viewView',[
    'model/model',
    'core/helper',
    'bootstrap'
], function (Model, Helper, bootstrap) {
    

    var ViewView = function (controller, options) {
        this.controller = controller;
        this.options = options;

        this.player = null;
        this.playingKeyPoint = null;

        this.bind__onClickVideoSpeed = this._onClickVideoSpeed.bind(this);
        this.bind__onClickClosedCaptions = this._onClickClosedCaptions.bind(this);
        this.bind__onAreaSelectionChanged = this._onAreaSelectionChanged.bind(this);
        this.bind__onMouseOverKeyPoint = this._onMouseOverKeyPoint.bind(this);
        this.bind__onMouseOutKeyPoint = this._onMouseOutKeyPoint.bind(this);
        this.bind__onRightClickKeyPoint = this._onRightClickKeyPoint.bind(this);
        this.bind__onEndKeyPoint = this._onEndKeyPoint.bind(this);
        this.bind__onPlaybackStarted = this._onPlaybackStarted.bind(this);
        this.bind__onPlaybackStopped = this._onPlaybackStopped.bind(this);
        this.bind__onSeek = this._onSeek.bind(this);
        this.bind__onModelChange = this._onModelChange.bind(this);

        this.$container = options.container;
        this.$opContainer = options.op_container;
        this.$postContainer = options.post_container;
        var $videos = this.$opContainer.find(ViewView.Binder.MEDIA_ELEMENT + ' video');
        this.$video = $videos.eq($videos.length == 2 ? 1 : 0);
        this.$pipVideo = $videos.eq($videos.length == 2 ? 0 : 1);

        $('#videoSpeed').on('click', this.bind__onClickVideoSpeed);
        $('#closedCaptions').on('click', this.bind__onClickClosedCaptions);

        if (this.controller.model.get('type') === 1 && this.controller.model.get('ordered_media').length > 0) {
            this.$video.on('loadedmetadata', function (e) {
                this.controller.updateKeyPointDuration(e.target.duration);
            }.bind(this));

            this._createPlayer();

            var media = this.controller.model.get('ordered_media.0');
            if (media && media.is_interpretation) {
                this.$pipVideo[0].currentTime = media.source_start_time;
            }
        }

        this.controller.model.subscribe(Model.Event.CHANGE, this.bind__onModelChange);

        $tt._instances.push(this);
    };

    ViewView.TAG = 'ThreadViewView';
    ViewView.DEFAULT_TEMPORAL_COMMENT_LENGTH = 3;

    ViewView.Binder = {
        MEDIA_ELEMENT: '.thread-media-element'
    };

    ViewView.prototype.loadView = function () {
        //TODO model: get collection data as json array
        var posts = [];
        _.each(this.controller.model.get('posts'), function (element, index, list) {
            posts.push(element.data);
        });

        // exclude new post forms
        posts = _.filter(posts, function (element) {
            return element.id > 0;
        });

        dust.render('thread_view_posts', {posts: posts}, function (err, out) {
            this.$postContainer.html(out);
            _.each(this.controller.model.get('posts'), function (element, index, list) {
                if (element.get('id') < 0) {
                    bootstrap(element, 'post', 'post/new', {is_permanent: true});
                } else {
                    bootstrap(element, 'post', 'post/view', {});
                }
            });
            //TODO layout bindings
            $(window).trigger('resize');
        }.bind(this));
    };

    ViewView.prototype._createPlayer = function () {
        console.log("%s: %s", ViewView.TAG, "_createPlayer");

        this.player = new Player(this.$video, {
            areaSelectionEnabled: false,
            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE,
            audioBar: false,
            overlayControls: true,
            playHeadImage: this.options.player.playHeadImage,
            playHeadImageOnClick: (function () {
                var currentTime = this.player.getCurrentTime();
                var keyPoint = new KeyPoint(
                    -1, currentTime, currentTime + ViewView.DEFAULT_TEMPORAL_COMMENT_LENGTH,
                    "", {drawOnTimeLine: true}
                );
                this._toggleTemporal(false, keyPoint);
            }).bind(this)
        });

        $(this.player).on(Player.EVENT_AREA_SELECTION_CHANGED, this.bind__onAreaSelectionChanged);
        $(this.player).on(Player.EVENT_KEYPOINT_MOUSE_OVER, this.bind__onMouseOverKeyPoint);
        $(this.player).on(Player.EVENT_KEYPOINT_MOUSE_OUT, this.bind__onMouseOutKeyPoint);
        $(this.player).on(Player.EVENT_KEYPOINT_RIGHT_CLICK, this.bind__onRightClickKeyPoint);
        $(this.player).on(Player.EVENT_KEYPOINT_END, this.bind__onEndKeyPoint);
        $(this.player).on(Player.EVENT_PLAYBACK_STARTED, this.bind__onPlaybackStarted);
        $(this.player).on(Player.EVENT_PLAYBACK_STOPPED, this.bind__onPlaybackStopped);
        $(this.player).on(Player.EVENT_SEEK, this.bind__onSeek);

        this.player.createControls();
    };

    ViewView.prototype._onAreaSelectionChanged = function (e) {
        console.log("%s: %s", ViewView.TAG, Player.EVENT_AREA_SELECTION_CHANGED);

        this.controller.updateKeyPointSelectionTimes(this.player.getAreaSelectionTimes());
    };

    ViewView.prototype._onMouseOverKeyPoint = function (e, keyPoint, coords) {
        console.log("%s: %s- keyPoint=%o, coords=%o", ViewView.TAG, Player.EVENT_KEYPOINT_MOUSE_OVER, keyPoint, coords);

        this.controller.hoverKeyPoint(keyPoint.id, {isMouseOver: true});

        // avoid animating when key points are overlapped and multiple invokes of this event are called
        if (!this.$postContainer.is(':animated')) {
            this.$postContainer.animate({
                scrollTop: $(".post-container[data-pid=" + keyPoint.id + "]").position().top
            });
        }
    };

    ViewView.prototype._onMouseOutKeyPoint = function (e, keyPoint) {
        console.log("%s: %s- keyPoint=%o", ViewView.TAG, Player.EVENT_KEYPOINT_MOUSE_OUT, keyPoint);

        this.controller.hoverKeyPoint(keyPoint.id, {isMouseOver: false});
    };

    ViewView.prototype._onRightClickKeyPoint = function (e, keyPoint, coords) {
        console.log("%s: %s- keyPoint=%o, coords=%o", ViewView.TAG, Player.EVENT_KEYPOINT_RIGHT_CLICK, keyPoint, coords);

        this.controller.rightClickKeyPoint(keyPoint.id);
    };

    ViewView.prototype._onEndKeyPoint = function (e, keyPoint) {
        console.log("%s: %s- keyPoint=%o", ViewView.TAG, Player.EVENT_KEYPOINT_END, keyPoint);

        if (this.playingKeyPoint && this.playingKeyPoint.id == keyPoint.id) {
            this.player.pause();
        }
    };

    ViewView.prototype._onPlaybackStarted = function (e) {
        console.log("%s: %s", ViewView.TAG, Player.EVENT_PLAYBACK_STARTED);

        if (this.$pipVideo.length == 0)
            return;

        this.$pipVideo[0].play();
    };

    ViewView.prototype._onPlaybackStopped = function (e) {
        console.log("%s: %s", ViewView.TAG, Player.EVENT_PLAYBACK_STOPPED);

        if (this.$pipVideo.length == 0)
            return;

        this.$pipVideo[0].pause();
    };

    ViewView.prototype._onSeek = function (e, time) {
        console.log("%s: %s- time=%s", ViewView.TAG, Player.EVENT_SEEK, time);

        if (this.$pipVideo.length == 0)
            return;

        this.$pipVideo[0].currentTime = Math.min(
            this.player.getCurrentTime() + this.controller.model.get('ordered_media.0.source_start_time'),
            this.$pipVideo[0].duration
        );
    };

    ViewView.prototype._toggleTemporal = function (disabled, keyPoint) {
        this.player.pause();
        this.playingKeyPoint = null;
        if (!disabled && keyPoint) {
            this.player.setPlayHeadImage('');
            this.player.seek(keyPoint.startTime);
            this.player.setAreaSelectionStartTime(keyPoint.startTime);
            this.player.setAreaSelectionEndTime(keyPoint.endTime);
            this.player.setAreaSelectionEnabled(true);
        } else {
            this.player.setPlayHeadImage(this.options.player.playHeadImage);
            this.player.setAreaSelectionEnabled(false);
        }
    };

    ViewView.prototype._onModelChange = function (e) {
        if (e.keyPath.indexOf('posts.') == 0 && e.view) { // trailing dot is for an element of the array/object
            var post = e.model.get(e.keyPath);

            if (e.view == 'new') {
                dust.render('post_new', post.data, function (err, out) {
                    // use the main container to include the permanent new post form that's in the thread OP container
                    var $post = this.$container.find('.post-container[data-pid="' + post.get('id') + '"]');
                    if ($post.length > 0) { // replace if it exists (form errors)
                        $post.replaceWith(out);
                        // find the element again for use later since has been replaced
                        $post = this.$container.find('.post-container[data-pid="' + post.get('id') + '"]');
                    } else {
                        var $post = this.$postContainer.find('.post-container[data-pid="' + post.get('parent_post_id') + '"]');
                        $post.after(out);
                    }
                    Helper.autoSize();
                    // is permanent if it's the permanent new post form that's in the thread OP container
                    bootstrap(post, 'post', 'post/new', {is_permanent: $.contains(this.$opContainer[0], $post[0])});
                    //TODO layout bindings
                    $(window).trigger('resize');
                }.bind(this));
            }

            if (e.view == 'edit') {
                dust.render('post_edit', post.data, function (err, out) {
                    var $post = this.$postContainer.find('.post-container[data-pid="' + post.get('id') + '"]');
                    $post.replaceWith(out);
                    Helper.autoSize();
                    bootstrap(post, 'post', 'post/edit', {});
                    //TODO layout bindings
                    $(window).trigger('resize');
                }.bind(this));
            }

            if (e.view == 'view') {
                var $posts = this.$postContainer.find('.post-container[data-pid]');
                // render the entire container it's the first post
                var template = $posts.length == 0 ? 'thread_view_posts' : 'post_view';
                var data = template == 'thread_view_posts' ? {posts: [post.data]} : post.data;
                dust.render(template, data, function (err, out) {
                    if (template == 'thread_view_posts') {
                        this.$postContainer.html(out);
                    } else {
                        if (e.isNew) { // new post
                            if (!post.get('is_post_reply')) {
                                $posts.last().after(out);
                            } else {
                                var parentPostId = post.get('parent_post_id');
                                $post = $posts.filter('[data-ppid="' + parentPostId + '"]');
                                if ($post.length == 0) // parent post has no child posts
                                    $post = $posts.filter('[data-pid="' + parentPostId + '"]');
                                $post.last().after(out);
                            }
                        } else {
                            var $post = this.$postContainer.find('.post-container[data-pid="' + post.get('id') + '"]');
                            $post.replaceWith(out);
                        }
                    }
                    bootstrap(post, 'post', 'post/view', {});
                    //TODO layout bindings
                    $(window).trigger('resize');
                }.bind(this));
            }
        }

        if (e.keyPath == 'posts') {
            var $posts = this.$postContainer.find('.post-container[data-pid]');
            var postIds = [];
            var $postsToRemove = [];

            _.each(e.model.get('posts'), function (element, index, list) {
                postIds.push(element.get('id'));
            });

            $postsToRemove = _.filter($posts, function (post) {
                // ignore new post forms (pid < 0)
                var postId = parseInt($(post).data('pid'), 10);
                return postId > 0 && !_.contains(postIds, postId);
            });

            // thread model removes child posts
            /*_.each($postsToRemove, function (element, index, list) {
                var filtered = $posts.filter('[data-ppid="' + $(element).data('pid') + '"]').toArray();
                $postsToRemove = _.union($postsToRemove, filtered);
            });*/

            _.each($postsToRemove, function (element, index, list) {
                var $post = $(element);
                $post.fadeOut('slow', function (e) {
                    $post.remove();

                    // the last post to be removed has been removed.
                    if ($posts.length == $postsToRemove.length) {
                        // render the container if it's expected to have no posts
                        if (index == ($posts.length - 1)) {
                            dust.render('thread_view_posts', {}, function (err, out) {
                                this.$postContainer.html(out);
                                //TODO layout bindings
                                $(window).trigger('resize');
                            }.bind(this));
                        } else {
                            //TODO layout bindings
                            $(window).trigger('resize');
                        }
                    }
                }.bind(this));
            }.bind(this));
        }

        if (!this.player)
            return;

        this.player.setKeyPoints(e.model.get('keyPoints', []));

        this.controller.updateKeyPointDuration(this.$video[0].duration);

        // check if a key point was changed
        if (e.keyPath.indexOf('keyPoints.') == 0) { // trailing dot is for an element of the array/object
            // get the key point, not just the property of the key point that changed
            var keyPoint = e.model.get(e.keyPath.substr(0, e.keyPath.lastIndexOf('.')));

            if (keyPoint instanceof KeyPoint) {
                if (keyPoint.isEditing) {
                    this._toggleTemporal(!(keyPoint.startTime && keyPoint.endTime), keyPoint);
                } else {
                    this._toggleTemporal(true);
                }

                if (keyPoint.isSeeking) {
                    keyPoint.paintHighlightedTimeout = true;
                    keyPoint.paintHighlighted = true;
                    this.player.seek(keyPoint.startTime);
                    this.player.redrawKeyPoints = true;

                    // clear the highlighted comment after 3 seconds
                    setTimeout((function () {
                        keyPoint.paintHighlightedTimeout = false;
                        keyPoint.paintHighlighted = false;
                        this.player.redrawKeyPoints = true;
                        this.player.repaint();
                    }).bind(this), 3000);
                }

                if (keyPoint.isPlaying) {
                    this.playingKeyPoint = keyPoint;
                    this.player.seek(keyPoint.startTime);
                    this.player.play();
                } else if (keyPoint.isHovering) {
                    // highlight the comment
                    keyPoint.paintHighlighted = true;
                    this.player.redrawKeyPoints = true;
                } else {
                    if (!keyPoint.paintHighlightedTimeout) {
                        keyPoint.paintHighlighted = false;
                        this.player.redrawKeyPoints = true;
                    }
                }

                this.player.repaint();
            }
        }
    };

    // change the video speed when the slowdown button is clicked
    ViewView.prototype._onClickVideoSpeed = function (e) {
        e.preventDefault();

        var rate = this.controller.adjustVideoSpeed();
        this.$video[0].playbackRate = rate.value;
        if (this.$pipVideo.length != 0) {
            this.$pipVideo[0].playbackRate = rate.value;
        }
        $(e.target).attr('src', rate.image);
    };

    // change the captioning display when you click the captioning button
    ViewView.prototype._onClickClosedCaptions = function (e) {
        e.preventDefault();

        var image = $('#closed-caption-button img').attr('src');
        image = image == this.options.player.captionImages.on
            ? this.options.player.captionImages.off
            : this.options.player.captionImages.on;
        $('#closed-caption-button img').attr('src', image);
    };

    return ViewView;
});

/*!
 * version=#version
 */

define('main',[
    // init
    'bootstrap',
    'service',

    // core
    'core/dust',
    'core/helper',
    'core/subscriber',

    // factory
    'factory/contactFactory',
    'factory/forumFactory',
    'factory/groupFactory',
    'factory/mediaFactory',
    'factory/messageFactory',
    'factory/myFilesFactory',
    'factory/postFactory',
    'factory/threadFactory',

    // service
    'service/keyPointService',
    'service/rabbitmqWebStompService',
    'service/subscriberService',
    'service/threadPostService',

    // model
    'model/forumModel',
    'model/groupModel',
    'model/mediaModel',
    'model/messageModel',
    'model/myFilesModel',
    'model/postModel',
    'model/profileModel',
    'model/threadModel',
    'model/userModel',

    // controller
    'controller/contactController',
    'controller/forumController',
    'controller/groupController',
    'controller/homeController',
    'controller/messageController',
    'controller/myFilesController',
    'controller/postController',
    'controller/profileController',
    'controller/threadController',

    // component
    'component/accessTypeComponent',
    'component/galleryComponent',
    'component/mediaChooserComponent',
    'component/myFilesSelectorComponent',
    'component/recorderComponent',
    'component/tableComponent',

    // view
    'views/contact/listView',

    'views/forum/newView',
    'views/forum/editView',
    'views/forum/listView',
    'views/forum/viewView',

    'views/group/newView',
    'views/group/editView',
    'views/group/listView',
    'views/group/viewView',
    'views/group/manageView',

    'views/home/indexView',

    'views/message/newView',
    'views/message/replyView',
    'views/message/viewView',

    'views/myFiles/listView',

    'views/post/newView',
    'views/post/editView',
    'views/post/viewView',

    'views/profile/editView',

    'views/thread/newView',
    'views/thread/editView',
    'views/thread/viewView'
], function () {
    

    var TerpTube = {};

    TerpTube._services = [];
    TerpTube._instances = [];

    window.TerpTube = TerpTube;
    window.$tt = window.TerpTube;

    ///////////////////////////////

    var Dust = require('core/dust');
    var Helper = require('core/helper');

    Dust.inject();
    Helper.autoSize();
});

require(["main"]);
