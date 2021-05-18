<?php

namespace DNADesign\AudioDefinition\Services;

interface TranslationService
{
    /**
     * Should return an array containing
     * definitions and/or audioSrc
     *
     * @param string $wordOrSentence
     * @return array
     */
    public function getDefinitionAndAudio($wordOrSentence): array;

    /**
     * Return whether a service is ready to be used
     * eg: in case it needs an api key, check that it is supplied
     */
    public function enabled(): bool;
}
