<?php

namespace App\Services;

class ContentModerationService
{
    private $filipinoProfanities;

    public function __construct()
    {
        // Add Filipino profanity words
        $this->filipinoProfanities = [
            'gago', 'gaga', 'putangina', 'putang ina', 'puta', 'tangina', 'tang ina',
            'bobo', 'tanga', 'ulol', 'inutil', 'kupal', 'tarantado', 'taena',
            'leche', 'punyeta', 'hinayupak', 'hayop', 'hayup', 'buwisit',
            'pakyu', 'pak yu', 'fuck you', 'pokpok', 'pok pok', 'bulbol',
            'kingina', 'kinginamo', 'kingina mo', 'ogag', 'olol', 'jakol',
            'jakolin', 'pakshet', 'paksheet', 'shet', 'shit', 'burat',
            'tite', 'puke', 'puki', 'bilat', 'bayag', 'betlog', 'ratbu',
        ];
    }

    public function hasProfanity($text)
    {
        if (empty($text)) {
            return false;
        }

        // Convert text to lowercase for case-insensitive matching
        $lowerText = mb_strtolower($text);

        // Check for Filipino profanities with word boundary matching
        foreach ($this->filipinoProfanities as $word) {
            // Use word boundaries to match whole words only, with case-insensitive matching
            if (preg_match('/\b'.preg_quote(mb_strtolower($word), '/').'\b/u', $lowerText)) {
                return true;
            }
        }

        return false;
    }

    public function sanitizeProfanity($text)
    {
        if (empty($text)) {
            return $text;
        }

        $lowerText = mb_strtolower($text);

        // Replace each profanity with asterisks
        foreach ($this->filipinoProfanities as $word) {
            $lowerWord = mb_strtolower($word);
            $replacement = str_repeat('*', mb_strlen($word));

            // Case-insensitive whole word replacement
            $text = preg_replace('/\b'.preg_quote($lowerWord, '/').'\b/iu', $replacement, $text);
        }

        return $text;
    }

    public function checkMultipleFields(array $fields)
    {
        foreach ($fields as $fieldName => $text) {
            if ($this->hasProfanity($text)) {
                return [
                    'has_profanity' => true,
                    'field' => $fieldName,
                ];
            }
        }

        return ['has_profanity' => false];
    }
}
