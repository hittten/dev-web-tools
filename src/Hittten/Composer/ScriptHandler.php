<?php

namespace Hittten\Composer;

use Symfony\Component\Process\Process;

/**
 * Class ScriptHandler
 *
 * @package Hittten\Composer
 * @author Gilberto López Ambrosino <gilberto.amb@gmail.com>
 */
class ScriptHandler
{
    /**
     * Install a bash bin executable
     */
    public static function bashInstall()
    {
        $console = realpath('bin/console');
        $process = new Process("rm ~/bin/devtools && ln -s $console ~/bin/devtools");
        $process->run();
    }
}
