<?php

namespace DNADesign\AudioDefinition\Extensions;

use DNADesign\AudioDefinition\Services\FileUploadTranslationService;
use SilverStripe\Assets\File;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;

class AudioDefinition_FileUploadExtension extends Extension
{
    private static $has_one = [
        'AudioFile' => File::class
    ];


    private static $owns = [
        'AudioFile'
    ];

    /**
     * @param FieldList $fields
     * @return void
     */
    public function updateCMSFields(FieldList $fields)
    {
        // AudioFile
        $audioFileField = $fields->dataFieldByName('AudioFile');
        $audioFileField->setDescription('Please upload your translation in mp3 format.')
            ->setFolderName('AudioTranslations')
            ->setAllowedExtensions(['mp3']);
        $this->setupDisplayRulesForField($audioFileField);
    }


    /**
     * Sets up display rules for CMS fields base on whether or not
     * a Locale is using the FileUploadTranslationService.
     *
     * @param FormField $field
     * @param string $criteria 
     */
    private function setupDisplayRulesForField($field, $criteria = 'displayIf')
    {
        $rules = []; 
        $sources = $this->owner->config()->get('sources');
        if ($sources && is_array($sources)) {
            foreach ($sources as $locale => $class) {
                $service = new $class();
                if($service instanceof FileUploadTranslationService) {  
                    $rules[] = "->$criteria('Locale')->isEqualTo('$locale')";
                    $criteria = 'orIf';
                }
            }
        }

        if (!empty($rules)) {
            // Apply the generated rules to the field.
            eval('$field' . implode('', $rules) . ';');
        }
    }
}
