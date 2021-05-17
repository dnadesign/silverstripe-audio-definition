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
}
