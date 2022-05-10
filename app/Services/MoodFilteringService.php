<?php

namespace App\Services;

use App\Models\ForbiddenWord;

class MoodFilteringService
{

    protected $mood;
    protected $forbiddenWords = [];

    public function __construct($mood)
    {
        $this->mood = $mood;
        $this->forbiddenWords = ForbiddenWord::where("status", 1)->get()->toArray();
    }

    public function filter()
    {
        if (count($this->forbiddenWords) == 0) {
            return $this->mood;
        }

        foreach ($this->forbiddenWords as $forbiddenWord) {
            if (str_contains($this->mood, "{$forbiddenWord["word"]} ")) {
                $this->mood = str_replace("{$forbiddenWord["word"]} ", "***", $this->mood);
            }
        }

        return $this->mood;
    }
}
