<?php

namespace waqar\otploginauthentication\otploginauthentication\Commands;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ViewCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otploginauthentication:views {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates login views';

    protected $type = 'Views';
    private $current_stub;

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return [
            'login.blade.php' => __DIR__ . '/../../resources/stubs/login.stub',
            'otp-login.blade.php' => __DIR__ . '/../../resources/stubs/otp.stub',
            'otp-verification.blade.php' => __DIR__ . '/../../resources/stubs/otp-verification.stub',
            'register.blade.php' => __DIR__ . '/../../resources/stubs/register.stub',
            'otp-email.blade.php' => __DIR__ . '/../../resources/stubs/otp-mail.stub',
        ];
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        return resource_path('views/auth');
    }

    protected function getDirectoryName($name)
    {
        return  Str::plural(strtolower(Str::kebab($name)));
    }

    /**
     * Replace the service variable in the stub using pluralization
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceServiceVar($name)
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);
        $class = strtolower(Str::snake(str_replace('Service', '', $class)));
        return Str::plural($class);
    }

    /**
     * Replace the service variable in stubs in singular
     * @param type $name
     * @return type
     */
    protected function replaceSingularServiceVar($name)
    {
        //dd($name);
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);
        $class = strtolower(Str::snake(str_replace('Service', '', $class)));
        //dd($class);
        return $class;
    }

    protected function replaceViewPath($name)
    {
        return Str::plural(Str::kebab(str_replace($this->getNamespace($name) . '\\', '', $name)));
    }


    protected function generateClass($name)
    {
        $stub = $this->files->get($this->current_stub);
        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $controllerNamespace = $this->getNamespace($name);
        $replace = [
            'DummyServiceVar' => $this->replaceServiceVar($name),
            'DummyViewPath' => $this->replaceViewPath($name),
            'DummyHeading' => ucfirst($name),
            'DummySingularServiceVar' => $this->replaceSingularServiceVar($name)
        ];
        return str_replace(
            array_keys($replace),
            array_values($replace),
            $this->generateClass($name)
        );
    }

    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        return $this->files->exists($this->getPath($this->getNameInput()));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('Building new views.');

        $path = $this->getPath(strtolower(Str::kebab($this->getNameInput())));

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        foreach ($this->getStub() as $name => $stub) {
            $this->current_stub = $stub;
            $this->makeDirectory($path . '/' . $name);
            $this->files->put($path . '/' . $name, $this->buildClass($this->getNameInput()));
        }
        $this->info($this->type . ' created successfully.');
    }
}
