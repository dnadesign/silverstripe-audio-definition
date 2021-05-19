<?php

use DNADesign\AudioDefinition\Models\AudioDefinition;
use DNADesign\AudioDefinition\Shortcodes\AudioDefinitionShortcodeProvider;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\HTMLEditor\TinyMCEConfig;
use SilverStripe\View\Parsers\ShortcodeParser;
use SilverStripe\View\Requirements;

call_user_func(function () {
    $module = ModuleLoader::inst()->getManifest()->getModule('dnadesign/silverstripe-audio-definition');

    // Enable insert-link to internal pages
    TinyMCEConfig::get('cms')
        ->enablePlugins([
            'audiodef' => $module->getResource('client/js/tinymce/plugins/audiodefinition/plugin.js')
        ])
       ->addButtonsToLine(2, 'audiodef');
       
    // Add options for the wysiwyg selector
    Requirements::customScript(sprintf('var audioDefinitionOptions = %s', AudioDefinition::getOptionsForCmsSelector()));
});

ShortcodeParser::get('default')
    ->register('audiodef', [AudioDefinitionShortcodeProvider::class, 'handle_shortcode']);
