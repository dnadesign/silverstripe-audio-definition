<?php

namespace DNADesign\AudioDefinition\Services;

interface TranslationService
{
    /**
     * Should return an array containing
     * definitions and/or audioSrc
     *
     * @param string $wordOrSentence
     * @param AudioDefinition || null $object
     * @return array
     */
    public function getDefinitionAndAudio($wordOrSentence, $object = null): array;

    /**
     * Return whether a service is ready to be used
     * eg: in case it needs an api key, check that it is supplied
     *
     * @return boolean
     */
    public function enabled(): bool;
}
