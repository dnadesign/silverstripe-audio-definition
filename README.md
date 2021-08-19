# Keywords  text + audio definition for SilverStripe

## Introduction

This modules provides an interface to associate foreign words, within a content area, with an audio description and optional text definition. By Default, this module ships with the necessary interfaces for translating Te Reo Maori words and expression via the [https://maoridictionary.co.nz](https://maoridictionary.co.nz) API.

## Requirements

* SilverStripe 4
* maoridictionary.co.nz API key (if using the default Te Reo Maori API)

## Installation

```
composer require dnadesign/silverstripe-audio-definition
```

## How to

### Create Definitions

In the CMS, open the Audio Definition interface, and click on `Add New`.
Type in the terms (could be one or more words) and select the language they are in (by default, only option is Maori).

If you are using an API and it is set up correctly, the `Link to Audio file` and `Text Definitions` should be populated upon saving.
If no API is provided, you can add `Text Definitions` manually.

![](docs/en/_images/audio-definition-cms-section.png)
![](docs/en/_images/saved-audio-definition.png)

### Insert definitions in content area

This module adds a button to the default Silverstripe text editor (currently TinyMCE 4) allowing to insert a shortcode to render the audio definition. 
TO add the shortcode, select the word you wish to associated with the definition, click on the `audio defnintion` button and select the correct term. If the selected text match any of the audio definition term, it will be selected by default.

![](docs/en/_images/audio-definition-wysiwyg.jpeg)

### Customise definition appearance

By default, the rendered definition is a `span` with the correct `lang` attribute set.
In addition, if an audio file is supplied, a button will precede the word which will play the audio when clicked.

It is recommended that you override the `DNADesign\AudioDefinition\AudioDefinition` template to suit you needs.
For instance, you could add the text definition in a tooltip displayed when a user hovers the word.

### Add different languages/translators

If you would like to add a different language to choose from when creating a definition, you can add a new local to the AudioDefinition sources via the config:
```
DNADesign\AudioDefinition\Models\AudioDefinition:
  sources:
    es_ES: 'SpanishTranslationServiceClass'
```
A translation service is optional. If you choose to use one, you can create a new service which must implement `DNADesign\AudioDefinition\Services`. This class must define a method `getDefinitionAndAudio` which return an array that must contain:
```
$data = [
    'audioSrc' => 'Link to audio file',
    'definitions' => [
        [
            'id' => 'Unique id of the definition (optional),
            'type' => 'Eg: noun, verb (optional),
            'content' => 'The text definition'
        ]
    ]
]
```

## Extensions

### Context Extension
Some languages can have multiple text definitions for the same word depending on the context.
To tag different text definitions with keywords that depict a context, activate the context extension for the locale as follow:
```
DNADesign\AudioDefinition\Models\TextDefinition:
  use_context_for_locales:
    - mi_NZ
```
Once activated, users can create contexts in the Audio Definition > Contexts tab, then tag text definitions with one or more context.
This won't have an influence on the way the definitions is displayed out-of-the-box, but if you implement a way of displaying the text definitions,
then these can be filtered by context.

Note: if at least one text definitions is tagged with a context, the wysiwyg dropdown will only give the choice of word with a context. If a word also required to display every definitions, then each definitions will need to be tagged with the "default" context.

Note: if you add the context config before running dev/build after installing the module, you will need to run dev/build twice for all the tables to be created.

## NOTES
Icons made by [Pixel perfect](https://www.flaticon.com/authors/pixel-perfect) from [www.flaticon.com](https://www.flaticon.com/)

