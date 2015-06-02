<?php
namespace Devbot\Install;

use Symfony\Component\Console\Question\Question;

interface QuestionManagerInterface
{
    function askQuestion(Question $question);
}
