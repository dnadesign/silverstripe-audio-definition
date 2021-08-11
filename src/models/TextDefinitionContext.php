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
