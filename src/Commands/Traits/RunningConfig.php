<?php

namespace Sinnbeck\LaravelServed\Commands\Traits;

use Sinnbeck\LaravelServed\ServiceManager;

trait RunningConfig
{
    use Logo;
    protected function servedRunning(ServiceManager $manager)
    {
        $this->line('      Laravel has been', 'fg=blue');
        $this->drawLogo();
        $this->line('<fg=green>Visit the development server at:</> <fg=white>http://localhost:' . $manager->web()->container()->port() . '</>');
    }
}