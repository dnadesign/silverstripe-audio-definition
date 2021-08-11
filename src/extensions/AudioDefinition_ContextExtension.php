<?php

namespace DNADesign\AudioDefinition\Extensions;

use DNADesign\AudioDefinition\Models\TextDefinitionContext;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataList;

class AudioDefinition_ContextExtension extends Extension
{
    /**
    * Alter the list of definitions that should be displayed
    * when a TextDefinitionContext ID is supplied
    *
    * @param DataList
    * @param array
    */
    public function updateDefinitionsToDisplay(DataList &$definitions, $params = null)
    {
        if ($params
            && is_array($params)
            && isset($params['context'])
            && is_numeric($params['context'])) {
            $context = TextDefinitionContext::get()->byID($params['context']);
            if ($context && $context->exists()) {
                $definitions = $definitions->filter('Contexts.ID', $context->ID);
            }
        }
    }
}
