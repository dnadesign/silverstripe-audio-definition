<?php

namespace DNADesign\AudioDefinition\Services;

use DNADesign\AudioDefinition\Services\TranslationService;

class FileUploadTranslationService implements TranslationService
{

    /**
     * Always set to false as this is handled in onBeforeWrite
     *
     * @return boolean
     */
    public function enabled(): bool
    {
        return false;
    }

    /**
     * Function only exists to fulfill the contract of the TranslationService interface.
     *
     * @param string $wordOrSentence
     * @return array
     */
    public function getDefinitionAndAudio($wordOrSentence): array
    {
        return [
            'definitions' => [],
            'audioSrc' => null
        ];
    }
}
