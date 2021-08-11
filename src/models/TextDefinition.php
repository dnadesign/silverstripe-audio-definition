<?php

namespace DNADesign\AudioDefinition\Models;

use DNADesign\AudioDefinition\Models\AudioDefinition;
use SilverStripe\Forms\CompositeValidator;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\DataObject;

class TextDefinition extends DataObject
{
    private static $table_name = 'TextDefinition';

    /**
     * Some language may have different definitions for the same word
     * depending on context. Switch this config to true to allow
     * tagging text definition with contexts for later filtering.
     * Set array per locale eg:
     * [mi_NZ => true, en_NZ => false]
     *
     * @var array
     */
    private static $use_context_for_locales = [];

    private static $db = [
        'UID' => 'Varchar(100)',
        'Content' => 'Text',
        'Type' => 'Varchar(50)',
        'Sort' => 'Int'
    ];

    private static $has_one = [
        'AudioDefinition' => AudioDefinition::class
    ];

    private static $default_sort = 'Sort ASC';

    private static $summary_fields = [
        'UID' => 'UID',
        'Content' => 'Definition',
        'Type' => 'Type'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'UID',
            'Sort',
            'AudioDefinitionID'
        ]);
        
        return $fields;
    }

    /**
     * Require Content and Type as a definition would not make sense without them
     *
     * @return CompositeValidator
     */
    public function getCMSCompositeValidator(): CompositeValidator
    {
        $compositeValidator = parent::getCMSCompositeValidator();

        $compositeValidator->addValidator(RequiredFields::create(['Content', 'Type']));
    
        return $compositeValidator;
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

    /**
     * Return whether this text definition can have contexts.
     * Its parent Audio Definition need to be a locale present in
     *
     * @return boolean
     */
    public function requireContext()
    {
        $use = static::config()->get('use_context_for_locales');
        if (!$use || empty($use)) {
            return false;
        }

        $definition = $this->AudioDefinition();
        if ($definition) {
            $locale = $definition->Locale;
            if ($locale) {
                return in_array($locale, $use);
            }
        }

        return false;
    }

    /**
     * Check if at least one locale requires contexts to be added to text definitions
     *
     * @return boolean
     */
    public static function contexts_in_use()
    {
        $use = static::config()->get('use_context_for_locales');
        if (!$use || empty($use)) {
            return false;
        }

        // Check that there is a sources available for the locale for which the context should be used
        $sources = AudioDefinition::config()->get('sources');
        if ($sources && is_array($sources) && !empty($sources)) {
            $locales = array_intersect(array_values($use), array_keys($sources));
            return count($locales) > 0;
        }

        return false;
    }
}
