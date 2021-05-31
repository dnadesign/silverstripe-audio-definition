<?php

namespace DNADesign\AudioDefinition\Models;

use DNADesign\AudioDefinition\Models\AudioDefinition;
use SilverStripe\ORM\DataObject;

class TextDefinition extends DataObject
{
    private static $table_name = 'TextDefinition';

    private static $db = [
        'UID' => 'Varchar(100)',
        'Content' => 'Text',
        'Type' => 'Varchar(50)',
        'Displayed' => 'Boolean',
        'Sort' => 'Int'
    ];

    private static $has_one = [
        'AudioDefinition' => AudioDefinition::class
    ];

    private static $defaults = [
        'Displayed' => true
    ];

    private static $default_sort = 'Sort ASC';

    private static $summary_fields = [
        'UID' => 'UID',
        'Content' => 'Definition',
        'Type' => 'Type',
        'Displayed.Nice' => 'Displayed'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'Sort',
            'AudioDefinitionID'
        ]);
        
        return $fields;
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
