<?php

namespace DNADesign\AudioDefinition\Services;

use DNADesign\AudioDefinition\Models\AudioDefinition;

interface TranslationService
{
    /**
     * Should return an array containing
     * definitions and/or audioSrc
     *
     * @param AudioDefinition $object
     * @return array
     */
    public function getDefinitionAndAudio(AudioDefinition $object): array;

    /**
     * Return whether a service is ready to be used
     * eg: in case it needs an api key, check that it is supplied
     *
     * @return boolean
     */
    public function enabled(): bool;
}
