<?php

namespace App\Console\Commands\DB;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

class MakeModelWithRepository extends Command
{
    protected $signature = 'make:model-c {name}';
    protected $description = 'Create a new model, migration, and repository.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $name = $this->argument('name');

        Artisan::call("make:model $name -m");

        $this->createRepository($name);

        $this->info('Model, migration, and repository created successfully.');
    }

    protected function createRepository($name): void
    {
        $filesystem = new Filesystem();

        $repositoryPath = app_path("Repositories/{$name}/{$name}Repository.php");
        $modelVariable = lcfirst('model');

        $repositoryContent = "<?php

namespace App\Repositories\\{$name};

use App\Repositories\BaseRepository;
use App\Models\\{$name};

class {$name}Repository extends BaseRepository
{
   public function __construct($name \$$modelVariable)
    {
        \$this->{$modelVariable} = \$$modelVariable;
    }
}";

        if (!is_dir(app_path('Repositories'))) {
            mkdir(app_path('Repositories'), 0777, true);
        }

        if (!is_dir(app_path("Repositories/{$name}"))) {
            mkdir(app_path("Repositories/{$name}"), 0777, true);
        }

        $filesystem->put($repositoryPath, $repositoryContent);
    }
}
