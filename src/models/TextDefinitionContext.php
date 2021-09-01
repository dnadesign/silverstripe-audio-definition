<?php

namespace DNADesign\AudioDefinition\Models;

use DNADesign\AudioDefinition\Models\AudioDefinition;
use SilverStripe\ORM\DataObject;

class TextDefinitionContext extends DataObject
{
    private static $table_name = 'TextDefinitionContext';

    private static $singular_name = 'Context';

    private static $plural_name = 'Contexts';

    private static $db = [
        'Name' => 'Varchar(255)'
    ];

    private static $belongs_many_many = [
        'Definitions' => TextDefinition::class
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // As we cannot create TextDefinition without an Audio Definition
        // Remove the definitions tab to avoid confusion
        $fields->removeByName('Definitions');

        return $fields;
    }

    /**
     * Return an array of all available context formatted to be used
     * by the TinyMCE config
     *
     * @return array
     */
    public static function getOptionsForCmsSelector()
    {
        $contexts = static::get();

        $options = [];

        if ($contexts && $contexts->exists()) {
            $options[] = ['value' => 0, 'text' => 'Select a context'];

            foreach ($contexts as $context) {
                $options[] =  ['value' => $context->ID, 'text' => $context->Name];
            }
        }

        static::singleton()->extend('updateOptionsForCmsSelector', $options);

        return $options;
    }

    /**
     * Permissions
     */
    public function canView($member = null)
    {
        return AudioDefinition::singleton()->canView($member);
    }

    public function canCreate($member = null, $context = [])
    {
        return AudioDefinition::singleton()->canCreate($member, $context);
    }

    public function canEdit($member = null)
    {
        return AudioDefinition::singleton()->canEdit($member);
    }

    public function canDelete($member = null)
    {
        return AudioDefinition::singleton()->canDelete($member);
    }
}
