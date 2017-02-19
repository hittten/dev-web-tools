<?php

namespace Hittten\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Class TestCommand
 *
 * @package Command
 * @author Gilberto López Ambrosino <gilberto.amb@gmail.com>
 */
class SymfonyTestCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('symfony:test')
            ->setDescription('Synchronize with rsync from <source> folder to <destination> folder and then execute the commands according to the options you specify')
            ->addArgument('source', InputArgument::OPTIONAL, 'project source', '.')
            ->addArgument('destination', InputArgument::OPTIONAL, 'destination test copy', '/tmp')
            ->addOption('full', null, InputOption::VALUE_NONE, 'do full tests')
            ->addOption('composer', null, InputOption::VALUE_OPTIONAL, 'composer', 'install --no-interaction')
            ->addOption('behat', null, InputOption::VALUE_OPTIONAL, 'vendor/bin/behat', '')
            ->addOption('phing', null, InputOption::VALUE_OPTIONAL, 'vendor/bin/phing', 'install')
            ->addOption('delete', null, InputOption::VALUE_NONE, 'delete entire destination directory before run tests')
            ->addUsage('--composer update')
            ->addUsage('--composer "install --no-dev"')
            ->addUsage('--behat "--tags @tag1,@tag2"')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Running {$this->getName()}");

        $source = $input->getArgument('source');
        $source = realpath($source);
        $destination = $input->getArgument('destination');
        $destination = realpath($destination);


        $full = $input->getOption('full');
        $composer = $input->getOption('composer');
        $phing = $input->getOption('phing');
        $behat = $input->getOption('behat');
        $delete = $input->getOption('delete');

        shell_exec('cd');
        $userHome = trim(shell_exec('cd && realpath .'));
        $destination .= $source;
        $commands = [];

        if ($delete) {
            $io->note("Path $destination will be removed");
            $commands[] = "rm -rf $destination";
        }

        $commands[] = "mkdir -p $destination";
        $commands[] = "cd $source";
        $commands[] = "rsync -r -u --delete --exclude-from=.gitignore --exclude-from=$userHome/.gitignore --exclude=.git . $destination";
        $commands[] = "cd $destination";

        if ($input->hasParameterOption('--composer') || $full) {
            $io->note("file app/config/parameters.yml will be removed");
            $commands[] = 'rm -rf app/config/parameters.yml';
            $commands[] = "composer $composer";
        }

        if ($input->hasParameterOption('--phing') || $full) {
            $commands[] = "vendor/bin/phing $phing";
        }

        if ($input->hasParameterOption('--behat') || $full) {
            $commands[] = "vendor/bin/behat $behat";
        }

        $fullCommand = implode(' && ', $commands);

        if ($io->isDebug()) {
            $output->writeln("<info>User home:</info> $userHome");
            $output->writeln("<info>Source dir:</info> $source");
            $output->writeln("<info>Destination dir:</info> $destination");
            $output->writeln("<info>Full command:</info> $fullCommand");
        }

        if ($io->isVerbose()) {
            $io->section("\nCommand list:");
            $io->listing($commands);
        }

        $process = new Process($fullCommand);

        $process->setTty(true);
        $process->run();
    }
}
