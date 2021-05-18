<?php

namespace DNADesign\AudioDefinition\Shortcodes;

use DNADesign\AudioDefinition\Models\AudioDefinition;
use SilverStripe\View\HTML;
use SilverStripe\View\Parsers\ShortcodeHandler;

class AudioDefinitionShortcodeProvider implements ShortcodeHandler
{
    /**
     * Gets the list of shortcodes provided by this handler
     *
     * @return mixed
     */
    public static function get_shortcodes()
    {
        return ['audiodef'];
    }

    public static function handle_shortcode($arguments, $content, $parser, $shortcode, $extra = [])
    {
        if (!isset($arguments['id']) || !is_numeric($arguments['id'])) {
            return $content;
        }

        $audioDefinition = AudioDefinition::get()->byID($arguments['id']);
        if (!$audioDefinition || !$audioDefinition->exists()) {
            return $content;
        }

        $result = $audioDefinition->renderWith('DNADesign\\AudioDefinition\\AudioDefinition');

        return $result;
    }
}
