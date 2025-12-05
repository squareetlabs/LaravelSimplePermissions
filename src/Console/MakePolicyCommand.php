<?php

namespace Squareetlabs\LaravelSimplePermissions\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakePolicyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:policy {name} {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new policy class';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $model = $this->option('model') ?? Str::singular(Str::replace('Policy', '', $name));

        $policyPath = app_path('Policies/' . $name . '.php');
        $policyNamespace = 'App\\Policies';
        $modelNamespace = 'App\\Models\\' . Str::studly($model);

        if (File::exists($policyPath)) {
            $this->error("Policy {$name} already exists!");
            return self::FAILURE;
        }

        $stub = $this->getStub();
        $stub = str_replace('{{ namespace }}', $policyNamespace, $stub);
        $stub = str_replace('{{ class }}', $name, $stub);
        $stub = str_replace('{{ model }}', Str::studly($model), $stub);
        $stub = str_replace('{{ modelVariable }}', Str::camel($model), $stub);
        $stub = str_replace('{{ modelNamespace }}', $modelNamespace, $stub);

        File::ensureDirectoryExists(app_path('Policies'));
        File::put($policyPath, $stub);

        $this->info("Policy {$name} created successfully!");

        return self::SUCCESS;
    }

    /**
     * Get the stub file for the policy.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return <<<'STUB'
<?php

namespace {{ namespace }};

use {{ modelNamespace }};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Access\HandlesAuthorization;

class {{ class }}
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any models.
     */
    public function viewAny(Model $user): bool
    {
        return $user->hasPermission('{{ modelVariable }}.viewAny');
    }

    /**
     * Determine if the user can view the model.
     */
    public function view(Model $user, {{ model }} ${{ modelVariable }}): bool
    {
        return $user->hasPermission('{{ modelVariable }}.view');
    }

    /**
     * Determine if the user can create models.
     */
    public function create(Model $user): bool
    {
        return $user->hasPermission('{{ modelVariable }}.create');
    }

    /**
     * Determine if the user can update the model.
     */
    public function update(Model $user, {{ model }} ${{ modelVariable }}): bool
    {
        return $user->hasAbility('{{ modelVariable }}.update', ${{ modelVariable }});
    }

    /**
     * Determine if the user can delete the model.
     */
    public function delete(Model $user, {{ model }} ${{ modelVariable }}): bool
    {
        return $user->hasAbility('{{ modelVariable }}.delete', ${{ modelVariable }});
    }

    /**
     * Determine if the user can restore the model.
     */
    public function restore(Model $user, {{ model }} ${{ modelVariable }}): bool
    {
        return $this->update($user, ${{ modelVariable }});
    }

    /**
     * Determine if the user can permanently delete the model.
     */
    public function forceDelete(Model $user, {{ model }} ${{ modelVariable }}): bool
    {
        return $this->delete($user, ${{ modelVariable }});
    }
}
STUB;
    }
}

