<?php

declare(strict_types=1);

namespace Tempest\Console;

interface Console extends ConsoleInput, ConsoleOutput
{
    public function component(ConsoleComponent $component): mixed;
}
