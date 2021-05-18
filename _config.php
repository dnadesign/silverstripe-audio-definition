<?php

use DNADesign\AudioDefinition\Shortcodes\AudioDefinitionShortcodeProvider;
use SilverStripe\View\Parsers\ShortcodeParser;

ShortcodeParser::get('default')
    ->register('audiodef', [AudioDefinitionShortcodeProvider::class, 'handle_shortcode']);
