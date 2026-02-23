<?php

namespace Reno\Dashboard\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeWidgetCommand extends GeneratorCommand
{
    protected $name = 'dashboard:widget';

    protected $description = 'Create a new dashboard widget class';

    protected $type = 'Widget';

    protected function getStub(): string
    {
        return __DIR__.'/../../stubs/widget.php.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Dashboard\\Widgets';
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $className = class_basename($name);
        $key = Str::kebab(Str::replaceLast('Widget', '', $className));
        $label = Str::headline(Str::replaceLast('Widget', '', $className));

        $stub = str_replace('{{ key }}', $key, $stub);

        return str_replace('{{ label }}', $label, $stub);
    }
}
