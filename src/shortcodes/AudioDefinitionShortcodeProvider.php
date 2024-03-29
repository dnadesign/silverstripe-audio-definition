<?php

namespace DNADesign\AudioDefinition\Shortcodes;

use DNADesign\AudioDefinition\Models\AudioDefinition;
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
        if (!isset($arguments['id'])) {
            return $content;
        }

        $audioDefinition = AudioDefinition::getByIdentifier($arguments['id']);
        if (!$audioDefinition || !$audioDefinition->exists()) {
            return $content;
        }

        $result = $audioDefinition
                    ->customise([
                        'Content' => $content,
                        'DefinitionsToDisplay' => $audioDefinition->getDefinitionsToDisplay($arguments),
                        'JSON' =>  $audioDefinition->toJSON($arguments)
                    ])
                    ->renderWith('DNADesign\\AudioDefinition\\AudioDefinition');

        return $result;
    }
}
