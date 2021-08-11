<?php

use DNADesign\AudioDefinition\Extensions\AudioDefinition_ContextExtension;
use DNADesign\AudioDefinition\Extensions\TextDefinition_ContextExtension;
use DNADesign\AudioDefinition\Models\AudioDefinition;
use DNADesign\AudioDefinition\Models\TextDefinition;
use DNADesign\AudioDefinition\Shortcodes\AudioDefinitionShortcodeProvider;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\HTMLEditor\TinyMCEConfig;
use SilverStripe\ORM\DB;
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
       
    // Make sure AudioDefinition table exists before requiring
    // otherwise it will break dev/build
    if (in_array(AudioDefinition::config()->get('table_name'), DB::table_list())) {
        // Add options for the wysiwyg selector
        Requirements::customScript(sprintf('var audioDefinitionOptions = %s', AudioDefinition::getOptionsForCmsSelector()));
    }
});

ShortcodeParser::get('default')
    ->register('audiodef', [AudioDefinitionShortcodeProvider::class, 'handle_shortcode']);

/**
 * Add necessary extension to allow user to manage text definition context
 */
if (TextDefinition::contexts_in_use()) {
    TextDefinition::add_extension(TextDefinition_ContextExtension::class);
    AudioDefinition::add_extension(AudioDefinition_ContextExtension::class);
}
