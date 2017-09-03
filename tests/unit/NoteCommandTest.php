<?php
use Craftsman\Commands\Notes;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class NoteCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testNotMarked()
    {
        $app = new Application();
        $app->add(new Notes);

        $command = $app->find('notes');

        $tester = new CommandTester($command);

        $tester->execute(array(
            'command' => $command->getName(),
            '--env' => sprintf('%s/tests/.craftsman', getcwd())
        ));

        $output = trim(preg_replace('/\s\s+/', ' ', $tester->getDisplay()));

        $this->assertRegexp('/There are not notes marked./', $output);
    }
}
