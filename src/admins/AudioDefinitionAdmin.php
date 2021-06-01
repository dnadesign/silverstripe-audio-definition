<?php

namespace DNADesign\AudioDefinition\Admins;

use DNADesign\AudioDefinition\Models\AudioDefinition;
use SilverStripe\Admin\ModelAdmin;

class AudioDefinitionAdmin extends ModelAdmin
{
    private static $managed_models = [
        AudioDefinition::class
    ];

    private static $url_segment = 'audio-definitions';

    private static $menu_title = 'Audio Definition';

    private static $menu_icon = 'dnadesign/silverstripe-audio-definition:client/icons/audiodef-icon.svg';
}
