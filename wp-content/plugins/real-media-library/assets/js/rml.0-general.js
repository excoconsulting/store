/* global jQuery */

/**
 * Code-Port: "Hello {0}".format("World"); returns "Hello World"
 */
/* @not-implement because some plugins destroy String.prototype => this is not referenced
if (!String.prototype.format) {
  String.prototype.format = function() {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function(match, number) { 
      return typeof args[number] != 'undefined'
        ? args[number]
        : match
      ;
    });
  };
}
*/

/**
 * Code-Port: Check if a string starts with a other string
 */
/* @not-implement because some plugins destroy String.prototype => this is not referenced
if (typeof String.prototype.startsWith != 'function') {
  String.prototype.startsWith = function (str){
    return this.indexOf(str) === 0;
  };
}
*/

function RMLFormat() {
    var args = arguments;
    return args[0].replace(/{(\d+)}/g, function(match, number) { 
      return typeof args[(+number)+1] != 'undefined'
        ? args[(+number)+1]
        : match
      ;
    });
}

function RMLStartsWith(str, search) {
    return str.indexOf(search) === 0;
}

function RMLisDefined(attr) {
    if (typeof attr !== typeof undefined && attr !== false && attr !== null) {
        return true;
    }
    return false;
}

function RMLWpIs(name) {
    return typeof window.wp !== "undefined" && typeof window.wp[name] !== "undefined";
}

function RMLisAIO(object) {
    return object instanceof jQuery && document.body.contains(object[0]) && object.data("allInOneTree");
}

/** Function.prototype.bind polyfill */
Function.prototype.bind=(function(){}).bind||function(b){if(typeof this!=="function"){throw new TypeError("Function.prototype.bind - what is trying to be bound is not callable");}function c(){}var a=[].slice,f=a.call(arguments,1),e=this,d=function(){return e.apply(this instanceof c?this:b||window,f.concat(a.call(arguments)));};c.prototype=this.prototype;d.prototype=new c();return d;};

/**
 * Hook System
 */
var RML_HOOK = {
    hooks: [],

    register: function(name, callback) {
        var names = name.split(" "),
            curName;
        for (var i = 0; i < names.length; i++) {
            curName = names[i];
            if ('undefined' == typeof(RML_HOOK.hooks[curName]))
                RML_HOOK.hooks[curName] = [];
            RML_HOOK.hooks[curName].push(callback);
        }
    },

    call: function(name, args, context) {
        if ('undefined' != typeof(RML_HOOK.hooks[name])) {
            for (var i = 0; i < RML_HOOK.hooks[name].length; ++i) {
                if (typeof args === "object") {
                    if (Object.prototype.toString.call(args) === '[object Array]') {
                        args.push(jQuery);
                    }else{
                        args = [args, jQuery];
                    }
                    
                    if (false == RML_HOOK.hooks[name][i].apply(context, args)) {
                        break;
                    } 
                }else{
                    if (false == RML_HOOK.hooks[name][i].apply(context, [ jQuery ])) {
                        break;
                    }
                }
            }
        }
    },

    exists: function(name) {
        return 'undefined' != typeof(RML_HOOK.hooks[name]);
    }
};

/**
 * General informations
 */
window.rml = {
    hooks: RML_HOOK,
    typeAccept: { }
}