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
        'Sort' => 'Int'
    ];

    private static $has_one = [
        'AudioDefinition' => AudioDefinition::class
    ];

    private static $summary_fields = [
        'UID' => 'UID',
        'Content' => 'Definition',
        'Type' => 'Type'
    ];

    private static $default_sort = 'Sort ASC';
}
