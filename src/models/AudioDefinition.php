<?php

namespace DNADesign\AudioDefinition\Models;

use DNADesign\AudioDefinition\Models\TextDefinition;
use DNADesign\AudioDefinition\Services\MaoriTranslationService;
use DNADesign\AudioDefinition\Shortcodes\AudioDefinitionShortcodeProvider;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

class AudioDefinition extends DataObject implements PermissionProvider
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
        $this->beforeUpdateCMSFields(function ($fields) {
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
                // Remove default description from DateTimeField
                $fetched->setDescription('');
            }

            if ($this->IsInDB()) {
                // Definitions
                $definitions = $fields->dataFieldByName('Definitions');
                if ($definitions) {
                    $config = $definitions->getConfig();
                    if ($config) {
                        $config->addComponent(new GridFieldSortableRows('Sort'));
                        // Delete text definition rather than unlinking them
                        $config->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
                        $delete = $config->getComponentByType(GridFieldDeleteAction::class);
                        if ($delete) {
                            $delete->setRemoveRelation(false);
                        }
                    }

                    $fields->removeByName('Definitions');
                    $fields->addFieldToTab('Root.Main', $definitions);
                }

                // Signatures
                $list = sprintf('<div class="field">%s</div>', implode('<br/>', $this->getSignaturesList()));
                $signatures = ToggleCompositeField::create('Signatures', 'Shortcodes Signatures', LiteralField::create('SignaturesList', $list));
                $fields->addFieldToTab('Root.Main', $signatures);
            }
        });

        return parent::getCMSFields();
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
    public function getDefinitionsToDisplay($args = null)
    {
        $definitions = $this->Definitions();

        $this->extend('updateDefinitionsToDisplay', $definitions, $args);

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

        $cacheKey = static::singleton()->getCachekey();

        // Attempt to load from cache
        $cache = Injector::inst()->get(CacheInterface::class . '.audioDefinitionCache');

        $options = []; //($cache->has($cacheKey)) ? $cache->get($cacheKey) : [];

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

            static::singleton()->extend('updateOptionsForCmsSelector', $options);

            $options = json_encode($options);
    
            // set a value and save it via the adapter
            $cache->set($cacheKey, $options);
        }
        
        return $options;
    }

    /**
     * Return a string reflecting a change in the AudioDefinition
     * to be used for refreshing the cache when needed
     *
     * @return string
     */
    public function getCacheKey()
    {
        $audioDefinitions = static::get();
        $textDefinitions = TextDefinition::get();

        $params = [
            $audioDefinitions->count(),
            strtotime($audioDefinitions->max('LastEdited')),
            $textDefinitions->count(),
            strtotime($textDefinitions->max('LastEdited')),
        ];

        $this->extend('updateCacheKeyParams', $params);

        return implode('.', $params);
    }

    /**
     * This method allows to inject additional fields that will appear under the "word" selector
     * when adding a definition in the wysiwyg. SeeTinyMCE docs for format.
     *
     * @return json
     */
    public static function getAdditionalCmsSelectorFields()
    {
        $fields = [];

        static::singleton()->extend('updateAdditionalCmsSelectorFields', $fields);

        return json_encode($fields);
    }

    /**
     * This method checks if the Audio Defintion is requested by ID (default behaviour)
     * but gives the opportunity to extensions to find the objet with a different identifier pattern
     *
     * @param string|int $identifier
     * @return AudioDefinition
     */
    public static function getByIdentifier($identifier)
    {
        $definition = null;

        if (is_numeric($identifier)) {
            $definition = static::get()->byID($identifier);
        }

        static::singleton()->extend('getByAlternateIdentifier', $definition, $identifier);

        return $definition;
    }

    /**
     * Return an array of all the possible shortcode signature that can be used
     * in a text area (in case they need to be manually added)
     *
     * @return array
     */
    public function getSignaturesList()
    {
        $list = [];

        $codes = AudioDefinitionShortcodeProvider::get_shortcodes();
        foreach ($codes as $code) {
            $signature = sprintf('[%s id="%s"]%s[/%s]', $code, $this->ID, $this->Term, $code);
            $list[] = $signature;
        }

        $this->extend('updateSignaturedList', $list, $codes);

        return $list;
    }

    /**
     * Permissions
     */
    public function canView($member = false)
    {
        return Permission::check('VIEW_DEFINITION');
    }

    public function canCreate($member = null, $context = [])
    {
        return Permission::check('CREATE_DEFINITION');
    }

    public function canEdit($member = false)
    {
        return Permission::check('EDIT_DEFINITION');
    }

    public function canDelete($member = false)
    {
        return Permission::check('DELETE_DEFINITION');
    }

    public function providePermissions()
    {
        return [
            'VIEW_DEFINITION' => array(
                'name' => _t(
                    __CLASS__ . '.ViewDefinition',
                    'View Audio Definitions'
                ),
                'category' => _t(
                    __CLASS__ . '.Category',
                    'Audio Definition'
                )
            ),
            'CREATE_DEFINITION' => array(
                'name' => _t(
                    __CLASS__ . '.CreateDefinition',
                    'Create Audio Definitions'
                ),
                'category' => _t(
                    __CLASS__ . '.Category',
                    'Audio Definition'
                )
            ),
            'EDIT_DEFINITION' => array(
                'name' => _t(
                    __CLASS__ . '.EditDefinition',
                    'Edit Audio Definitions'
                ),
                'category' => _t(
                    __CLASS__ . '.Category',
                    'Audio Definition'
                )
            ),
            'DELETE_DEFINITION' => array(
                'name' => _t(
                    __CLASS__ . '.DeleteDefinition',
                    'Delete Audio Definitions'
                ),
                'category' => _t(
                    __CLASS__ . '.Category',
                    'Audio Definition'
                )
            )
        ];
    }
}
