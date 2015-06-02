<?php
namespace Devbot\Install;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;


class ConsoleQuestionManager implements QuestionManagerInterface
{
    protected $questionHelper;
    protected $input;
    protected $output;
    
    public function setInput(InputInterface $input)
    {
        $this->input = $input;
        return $this;
    }
    
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        return $this;
    }
    
    public function __construct(QuestionHelper $helper)
    {
        $this->questionHelper = $helper;
    }
    
    public function askQuestion(Question $question)
    {
        if ($this->input === null) {
            throw new \LogicException('No input interface for console question');
        }
        if ($this->output === null) {
            throw new \LogicException('No output interface for console question');
        }
        return $this->questionHelper->ask($this->input, $this->output, $question);
    }
}