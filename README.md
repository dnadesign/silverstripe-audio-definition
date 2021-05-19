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



