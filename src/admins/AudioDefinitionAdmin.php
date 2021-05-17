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
}
