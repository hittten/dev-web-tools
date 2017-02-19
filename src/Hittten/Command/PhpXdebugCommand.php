<?php

namespace Hittten\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Class PhpXdebugCommand
 *
 * @package Command
 * @author Gilberto López Ambrosino <gilberto.amb@gmail.com>
 */
class PhpXdebugCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('php:xdebug')
            ->setDescription('Enable or disable php xdebug')
            ->addArgument('switch', InputArgument::REQUIRED, 'off|on')
            ->addUsage('on')
            ->addUsage('off')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $switch = $input->getArgument('switch');

        if ($switch !== 'on' && $switch !== 'off') {
            throw new \InvalidArgumentException('switch must be "on" or "off"');
        }

        $io->title("Switching {$switch} {$this->getName()}");

        $commands = [];
        if ($switch === 'on') {
            $commands[] = 'sudo ln -s /etc/php/7.0/mods-available/xdebug.ini /etc/php/7.0/cli/conf.d/20-xdebug.ini';
            $commands[] = 'sudo ln -s /etc/php/7.0/mods-available/xdebug.ini /etc/php/7.0/fpm/conf.d/20-xdebug.ini';
        }

        if ($switch === 'off') {
            $commands[] = 'sudo rm /etc/php/7.0/cli/conf.d/20-xdebug.ini';
            $commands[] = 'sudo rm /etc/php/7.0/fpm/conf.d/20-xdebug.ini';
        }

        $commands[] = 'sudo service php7.0-fpm restart';

        $fullCommand = implode(' && ', $commands);

        if ($io->isVerbose()) {
            $io->section("\nCommand list:");
            $io->listing($commands);
        }

        $process = new Process($fullCommand);

        $process->setTty(true);
        $process->run();
    }
}
