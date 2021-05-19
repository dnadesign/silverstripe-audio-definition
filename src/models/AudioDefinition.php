<?php

namespace DNADesign\AudioDefinition\Models;

use DNADesign\AudioDefinition\Models\TextDefinition;
use DNADesign\AudioDefinition\Services\MaoriTranslationService;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBText;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

class AudioDefinition extends DataObject
{
    private static $table_name = 'AudioDefinition';

    private static $sources = [
        'mi_NZ' => MaoriTranslationService::class
    ];

    private static $db = [
        'Term' => 'Varchar(100)',
        'Locale' => 'Varchar(10)',
        'LinkToAudioFile' => 'Varchar(255)',
        'FetchedFromService' => 'Datetime'
    ];

    private static $has_many = [
        'Definitions' => TextDefinition::class
    ];

    private static $defaults = [
        'Locale' => 'mi_NZ'
    ];

    private static $summary_fields = [
        'ID' => 'ID',
        'Term' => 'Term',
        'getLanguageName' => 'Language',
        'Definitions.Count' => 'Definitions #'
    ];

    private static $default_sort = 'Term ASC';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Language
        $language = DropdownField::create('Locale', 'Language', $this->getLanguageOptions());
        $fields->replaceField('Locale', $language);

        // LinkToAudioFile
        $audio = $fields->dataFieldByName('LinkToAudioFile');
        if ($audio) {
            $audio->setReadonly(true);
        }

        // FetchedFromService
        $fetched = $fields->dataFieldByName('FetchedFromService');
        if ($fetched) {
            $fetched->setReadonly(true);
        }

        // Definitions
        if ($this->IsInDB()) {
            $definitions = $fields->dataFieldByName('Definitions');
            if ($definitions) {
                $config = $definitions->getConfig();
                if ($config) {
                    $config->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
                    $config->addComponent(new GridFieldSortableRows('Sort'));
                }

                $fields->removeByName('Definitions');
                $fields->addFieldToTab('Root.Main', $definitions);
            }
        }

        return $fields;
    }

    /**
     * For CMS
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->Term;
    }

    /**
     * Return an array of Locale > Language Name
     * to choose from as source of the definition
     *
     * @return array
     */
    public function getLanguageOptions()
    {
        $options = [];

        $sources = array_keys($this->config()->get('sources'));
        foreach ($sources as $locale) {
            $options[$locale] =  \Locale::getDisplayLanguage($locale, i18n::get_locale());
        }

        $this->extend('updateLanguageOptions', $options);

        return $options;
    }

    /**
     * Return the right service for the language
     *
     * @return TranslationService|null
     */
    public function getSourceService()
    {
        if ($this->Locale) {
            $sources = $this->config()->get('sources');
            if ($sources && is_array($sources) && isset($sources[$this->Locale])) {
                $class = $sources[$this->Locale];
                $service = new $class();
                if ($service->enabled()) {
                    return $service;
                }
            }
        }

        return null;
    }

    /**
     * Attempt to fetch the definition upon saving the object
     *
     * @return void
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();

        if ($this->Locale && trim($this->Term) && !$this->FetchedFromService) {
            $source = $this->getSourceService();
            if ($source) {
                try {
                    $data = $source->getDefinitionAndAudio($this->Term);
                    if ($data && is_array($data)) {
                        if (isset($data['definitions']) && is_array($data['definitions'])) {
                            foreach ($data['definitions'] as $definition) {
                                // if an id is supplied, we can check if the definition exist already
                                $exists = false;
                                if (isset($definition['id']) && !empty($definition['id'])) {
                                    $exists = $this->Definitions()->filter('UID', $definition['id'])->count() > 0;
                                }

                                // Create object
                                if ($exists === false && isset($definition['content'])) {
                                    $defObject = new TextDefinition();
                                    $defObject->UID = isset($definition['id']) ? $definition['id'] : null;
                                    $defObject->Content = ucfirst($definition['content']);
                                    $defObject->Type = isset($definition['type']) ? ucfirst($definition['type']) : null;
                                    $defObject->AudioDefinitionID = $this->ID;
                                    $defObject->write();
                                }
                            }
                        }

                        if (isset($data['audioSrc'])) {
                            $this->LinkToAudioFile = $data['audioSrc'];
                        }

                        $this->FetchedFromService = DBDatetime::now()->format(DBDatetime::ISO_DATETIME);
                        $this->write();
                    }
                } catch (Exception $e) {
                    Injector::inst()->get(LoggerInterface::class)->error($e->getMessage());
                }
            }
        }
    }

    /**
     * Return the language as a string valid for the html lang attribute
     *
     * @return string
     */
    public function getLangAttr()
    {
        $lang = '';

        $language = $this->Locale;
        if ($language) {
            $lang = substr($language, 0, 2);
        }

        $this->extend('updateLangAttribute', $lang);

        return $lang;
    }

    /**
     * Return the list of text definitions that could be displayed
     * if the template allows for it.
     *
     * @return DataList
     */
    public function getDefinitionsToDisplay()
    {
        $definitions = $this->Definitions()->filter('Displayed', true);

        $this->extend('updateDefinitionsToDisplay', $definitions);

        return $definitions;
    }

    /**
     * Return the language as a readable string
     *
     * @return void
     */
    public function getLanguageName()
    {
        return DBField::create_field(DBText::class, \Locale::getDisplayLanguage($this->Locale, i18n::get_locale()));
    }

    /**
     * Produce the json used by TinyMCE to populate the list of words
     * than can be used in order to link to ta definition
     *
     * @return json
     */
    public static function getOptionsForCmsSelector()
    {
        $definitions = static::get();

        $cacheKey = implode('.', [
            $definitions->count(),
            strtotime($definitions->max('LastEdited'))
        ]);

        // Attempt to load from cache
        $cache = Injector::inst()->get(CacheInterface::class . '.audioDefinitionCache');

        $options = ($cache->has($cacheKey)) ? $cache->get($cacheKey) : [];

        // If no options have been cached, then create the json
        if (empty($options)) {
            if ($definitions->count() > 0) {
                $options = [
                    ['value' => 0, 'text' => 'Select a word']
                ];
        
                foreach ($definitions as $desc) {
                    $options[] = ['value' => $desc->ID, 'text' => $desc->Term];
                }
            }

            $options = json_encode($options);
    
            // set a value and save it via the adapter
            $cache->set($cacheKey, $options);
        }
        
        return $options;
    }
}
