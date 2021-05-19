(function () {

    var defs = {}; // id -> {dependencies, definition, instance (possibly undefined)}
    
    // Used when there is no 'main' module.
    // The name is probably (hopefully) unique so minification removes for releases.
    var register_3795 = function (id) {
      var module = dem(id);
      var fragments = id.split('.');
      var target = Function('return this;')();
      for (var i = 0; i < fragments.length - 1; ++i) {
        if (target[fragments[i]] === undefined)
          target[fragments[i]] = {};
        target = target[fragments[i]];
      }
      target[fragments[fragments.length - 1]] = module;
    };
    
    var instantiate = function (id) {
      var actual = defs[id];
      var dependencies = actual.deps;
      var definition = actual.defn;
      var len = dependencies.length;
      var instances = new Array(len);
      for (var i = 0; i < len; ++i)
        instances[i] = dem(dependencies[i]);
      var defResult = definition.apply(null, instances);
      if (defResult === undefined)
         throw 'module [' + id + '] returned undefined';
      actual.instance = defResult;
    };
    
    var def = function (id, dependencies, definition) {
      if (typeof id !== 'string')
        throw 'module id must be a string';
      else if (dependencies === undefined)
        throw 'no dependencies for ' + id;
      else if (definition === undefined)
        throw 'no definition function for ' + id;
      defs[id] = {
        deps: dependencies,
        defn: definition,
        instance: undefined
      };
    };
    
    var dem = function (id) {
      var actual = defs[id];
      if (actual === undefined)
        throw 'module [' + id + '] was undefined';
      else if (actual.instance === undefined)
        instantiate(id);
      return actual.instance;
    };
    
    var req = function (ids, callback) {
      var len = ids.length;
      var instances = new Array(len);
      for (var i = 0; i < len; ++i)
        instances.push(dem(ids[i]));
      callback.apply(null, callback);
    };
    
    var ephox = {};
    
    ephox.bolt = {
      module: {
        api: {
          define: def,
          require: req,
          demand: dem
        }
      }
    };
    
    var define = def;
    var require = req;
    var demand = dem;
    // this helps with minificiation when using a lot of global references
    var defineGlobal = function (id, ref) {
      define(id, [], function () { return ref; });
    };
    /*jsc
    ["tinymce.plugins.audiodef.Plugin","tinymce.core.Env","tinymce.core.PluginManager","global!tinymce.util.Tools.resolve"]
    jsc*/
    defineGlobal("global!tinymce.util.Tools.resolve", tinymce.util.Tools.resolve);
    /**
     * ResolveGlobal.js
     *
     * Released under LGPL License.
     * Copyright (c) 1999-2017 Ephox Corp. All rights reserved
     *
     * License: http://www.tinymce.com/license
     * Contributing: http://www.tinymce.com/contributing
     */
    
    define(
      'tinymce.core.Env',
      [
        'global!tinymce.util.Tools.resolve'
      ],
      function (resolve) {
        return resolve('tinymce.Env');
      }
    );
    
    /**
     * ResolveGlobal.js
     *
     * Released under LGPL License.
     * Copyright (c) 1999-2017 Ephox Corp. All rights reserved
     *
     * License: http://www.tinymce.com/license
     * Contributing: http://www.tinymce.com/contributing
     */
    
    define(
      'tinymce.core.PluginManager',
      [
        'global!tinymce.util.Tools.resolve'
      ],
      function (resolve) {
        return resolve('tinymce.PluginManager');
      }
    );
    
    /**
     * Plugin.js
     *
     * Released under LGPL License.
     * Copyright (c) 1999-2017 Ephox Corp. All rights reserved
     *
     * License: http://www.tinymce.com/license
     * Contributing: http://www.tinymce.com/contributing
     */
    
    /**
     * This class contains all core logic for the audiodef plugin.
     *
     * @class tinymce.audiodef.Plugin
     * @private
     */
    define(
      'tinymce.plugins.audiodef.Plugin',
      [
        'tinymce.core.Env',
        'tinymce.core.PluginManager'
      ],
    
      function (Env, PluginManager) {
        PluginManager.add('audiodef', function (editor) {
          var options = window.audioDefinitionOptions;

          var showDialog = function () {
            var selectedWords = editor.selection.getContent();

            // Don't open the dialogue if there are no options or no word has been selected
            if (!selectedWords || options.length < 1) return;
            
            editor.windowManager.open({
              title: 'Insert Audio Definition',
              body: [{ 
                type: 'listbox',
                name: 'wordid',
                label: 'Word',
                size: 'large',
                minWidth: 300,
                values: options,
                onPostRender: function () {
                  // If we have selected words, we can try to match them to the options
                  // and preselect the option
                  const match = options.find(option => {
                    return option.text.trim().toLowerCase() === selectedWords.trim().toLowerCase();
                  });
                  if (match) {
                    this.value(match.value);
                  }
                }
              }],
              onsubmit: function (e) {
                var shortcode = `[audiodef id='${e.data.wordid}']${selectedWords}[/audiodef]`;
                editor.execCommand('mceInsertContent', false, shortcode);
              }
            });
          };
    
          editor.addCommand('mceAudiodef', showDialog);
    
          editor.addButton('audiodef', {
            type: 'button',
            icon: 'translate',
            tooltip: 'Audio Definition',
            onclick: showDialog,
            disabled: options.length < 1
          });
        });
    
        return function () { };
      }
    );
    dem('tinymce.plugins.audiodef.Plugin')();
})();