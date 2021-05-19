<?php

use DNADesign\AudioDefinition\Shortcodes\AudioDefinitionShortcodeProvider;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\HTMLEditor\TinyMCEConfig;
use SilverStripe\View\Parsers\ShortcodeParser;

call_user_func(function () {
    $module = ModuleLoader::inst()->getManifest()->getModule('dnadesign/silverstripe-audio-definition');

    // Enable insert-link to internal pages
    TinyMCEConfig::get('cms')
        ->enablePlugins([
            'audiodef' => $module
                ->getResource('client/js/tinymce/plugins/audiodefinition/plugin.js')
        ]);
});

ShortcodeParser::get('default')
    ->register('audiodef', [AudioDefinitionShortcodeProvider::class, 'handle_shortcode']);
