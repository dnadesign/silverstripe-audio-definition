<?php

namespace DNADesign\AudioDefinition\Models;

use DNADesign\AudioDefinition\Models\TextDefinition;
use DNADesign\AudioDefinition\Services\MaoriTranslationService;
use Exception;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

class AudioDefinition extends DataObject
{
    private static $table_name = 'AudioDefinition';

    private static $sources = [
        'mi_NZ' => MaoriTranslationService::class
    ];

    private static $db = [
        'Term' => 'Varchar(100)',
        'Language' => 'Varchar(10)',
        'LinkToAudioFile' => 'Varchar(255)',
        'FetchedFromService' => 'Datetime'
    ];

    private static $has_many = [
        'Definitions' => TextDefinition::class
    ];

    private static $defaults = [
        'Language' => 'mi_NZ'
    ];

    private static $summary_fields = [
        'Term' => 'Term',
        'Language' => 'Language',
        'Definitions.Count' => 'Definitions #'
    ];

    private static $default_sort = 'Term ASC';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Language
        $language = DropdownField::create('Language', 'Language', $this->getLanguageOptions());
        $fields->replaceField('Language', $language);

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
        $options = [
            'mi_NZ' => 'Te Reo Maori'
        ];

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
        if ($this->Language) {
            $sources = $this->config()->get('sources');
            if ($sources && is_array($sources) && isset($sources[$this->Language])) {
                $class = $sources[$this->Language];
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

        if ($this->Language && trim($this->Term) && !$this->FetchedFromService) {
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
                                    $defObject->Content = $definition['content'];
                                    $defObject->Type = isset($definition['type']) ? $definition['type'] : null;
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

        $language = $this->Language;
        if ($language) {
            $lang = substr($language, 0, 2);
        }

        $this->extend('updateLangAttribute', $lang);

        return $lang;
    }
}
