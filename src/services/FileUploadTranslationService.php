<?php

namespace DNADesign\AudioDefinition\Services;

use DNADesign\AudioDefinition\Models\AudioDefinition;
use DNADesign\AudioDefinition\Services\TranslationService;

class FileUploadTranslationService implements TranslationService
{

    /**
     * Always set to true as this doesn't use an API.   
     *
     * @return boolean
     */
    public function enabled(): bool
    {
        return true;
    }

    /**
     * Sets the audioSrc to the AbsoluteLink of of the AudioFile 
     * uploaded to the AudioDefinition. As no API is used the definitions 
     * will be added manually by the CMS user.
     *
     * @param AudioDefinition $object
     * @return array 
     */
    public function getDefinitionAndAudio(AudioDefinition $object): array
    {
        $audioSrc = null;
        if ($object && $object->AudioFile()->exists()) {
            $audioSrc = $object->AudioFile()->AbsoluteLink();
        }

        return [
            'definitions' =>  [],
            'audioSrc' => $audioSrc
        ];
    }
}
