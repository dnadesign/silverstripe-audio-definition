<?php

namespace DNADesign\AudioDefinition\Extensions;

use DNADesign\AudioDefinition\Models\TextDefinition;
use DNADesign\AudioDefinition\Models\TextDefinitionContext;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ListboxField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;

class TextDefinition_ContextExtension extends DataExtension
{
    private static $db = [
        'ContextsKey' => 'Text'
    ];

    private static $many_many = [
        'Contexts' => TextDefinitionContext::class
    ];

    /**
     * Keep track of contexts changes so the LastEdited date changes on the text definition table
     * and the cache key is updated accordingly
     */
    public function onBeforeWrite()
    {
        $this->owner->ContextsKey = serialize($this->owner->Contexts()->map()->toArray());
    }

    /**
     * Add context selector
     *
     * @param FieldList $fields
     * @return void
     */
    public function updateCMSFields(FieldList $fields)
    {
        // Remove Context Tab
        $fields->removeByName([
            'Contexts',
            'ContextsKey'
        ]);

        // Show context selector only of parent is in a locale that requires context
        if ($this->owner->requireContext()) {
            $contexts = ListboxField::create('Contexts', 'Contexts', TextDefinitionContext::get()->map());
            $fields->addFieldsToTab('Root.Main', $contexts);
        }
    }

    /**
     * Add context list to gridfield
     *
     * @param array $fields
     * @return void
     */
    public function updateSummaryFields(&$fields)
    {
        if (TextDefinition::contexts_in_use()) {
            $fields['getContextsList'] = 'Contexts';
        }
    }

    /**
     * Return the comma separated list of context name
     *
     * @return DBHTMLText
     */
    public function getContextsList()
    {
        $list = $this->owner->Contexts()->column('Name');
        return DBField::create_field(DBHTMLText::class, implode(',', $list));
    }
}
