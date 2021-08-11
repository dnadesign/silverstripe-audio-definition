<?php

namespace DNADesign\AudioDefinition\Admins;

use DNADesign\AudioDefinition\Models\AudioDefinition;
use DNADesign\AudioDefinition\Models\TextDefinition;
use DNADesign\AudioDefinition\Models\TextDefinitionContext;
use SilverStripe\Admin\ModelAdmin;

class AudioDefinitionAdmin extends ModelAdmin
{
    private static $managed_models = [
        AudioDefinition::class,
        TextDefinitionContext::class
    ];

    private static $url_segment = 'audio-definitions';

    private static $menu_title = 'Audio Definition';

    private static $menu_icon = 'dnadesign/silverstripe-audio-definition:client/icons/audiodef-icon.svg';

    /**
     * Hide the Context tab if not required
     *
     * @return array
     */
    public function getManagedModels()
    {
        $models = parent::getManagedModels();

        if ($this->showContextTab() === false) {
            unset($models[TextDefinitionContext::class]);
        }

        return $models;
    }

    /**
     * Check if USer are allowed to create contexts
     *
     * @return boolean
     */
    private function showContextTab()
    {
        return TextDefinition::contexts_in_use();
    }
}
