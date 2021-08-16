<?php

namespace DNADesign\AudioDefinition\Extensions;

use DNADesign\AudioDefinition\Models\AudioDefinition;
use DNADesign\AudioDefinition\Models\TextDefinition;
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
            && isset($params['id'])
            && strpos($params['id'], '|') !== false) {
            $idParams = explode('|', $params['id']);
            if ($idParams && isset($idParams[1])) {
                $definitions = $definitions->filter('Contexts.ID', $idParams[1]);
            }
        }
    }

    /**
     * When context is in use,
     * user can select the words in the dropdown with their associated context
     * NOTE: if there isn't a context associated with any of the text definition
     * then show only the default word
     *
     * @param array $options
     * @return void
     */
    public function updateOptionsForCmsSelector(&$options)
    {
        $alteredOptions = [];
        foreach ($options as $option) {
            $id = isset($option['value']) ? $option['value'] : null;
            if (is_numeric($id)) {
                if ($id === 0) {
                    $alteredOptions[] = $option;
                } else {
                    // Find the context associated with the text definition
                    // TODO: optimise queries
                    $textDefinitions = TextDefinition::get()->filter('AudioDefinitionID', $id);
                    if ($textDefinitions && $textDefinitions->exists()) {
                        $contextsIDs = [];
                        foreach ($textDefinitions as $textDefinition) {
                            $contextsIDs = array_merge($contextsIDs, $textDefinition->Contexts()->column('ID'));
                        }
                        array_unique($contextsIDs);
                        if (empty($contextsIDs)) {
                            $alteredOptions[] = $option;
                        } else {
                            $contexts = TextDefinitionContext::get()->filter('ID', $contextsIDs);
                            if ($contexts && $contexts->exists()) {
                                foreach ($contexts as $context) {
                                    $value = $id . '|' . $context->ID;
                                    $text = sprintf('%s (%s)', $option['text'], $context->Name);
                                    $alteredOptions[] = ['value' => $value, 'text' => $text];
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($alteredOptions)) {
            $options = $alteredOptions;
        }
    }

    /**
     * As the identifier is can be passed as a string $id|$contextID
     * we need to deconstruct the string to get the ID of the definition
     *
     * @param AudioDefinition|null $definition
     * @param int|string $identifier
     * @return void
     */
    public function getByAlternateIdentifier(&$definition, $identifier)
    {
        if ($definition === null && strpos($identifier, '|') !== false) {
            $idParams = explode('|', $identifier);
            if (isset($idParams[0]) && is_numeric($idParams[0])) {
                $definition =  AudioDefinition::get()->byID($idParams[0]);
            }
        }
    }

    /**
     * Update the list of short code that can be included in a text area
     * with added contexts
     *
     * @param array $list
     * @param  array $codes
     * @return void
     */
    public function updateSignaturedList(&$list, $codes)
    {
        if (TextDefinition::contexts_in_use()) {
            if ($this->owner->Definitions()->exists()) {
                $contexts = TextDefinitionContext::get()->filter('Definitions.ID', $this->owner->Definitions()->column('ID'));
                if ($contexts && $contexts->exists()) {
                    $signatures = [];
                    foreach ($contexts as $context) {
                        foreach ($codes as $code) {
                            $signatures[] = sprintf('<strong>%s</strong> [%s id="%s|%s"]%s[/%s]', $context->Name, $code, $this->owner->ID, $context->ID, $this->owner->Term, $code);
                        }
                    }
                    $list = $signatures;
                }
            }
        }
    }
}
