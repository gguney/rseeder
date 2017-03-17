<?php
namespace GGuney\RSeeder\Commands;

use Illuminate\Console\Command;

class MakeReverseSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:reverseSeeder {table_name} {--date} {--except}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cretes seeder from a given table.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $seedsPath = 'seeds/';

        $fromDate = $this->option('date');
        $exceptColumns = $this->option('except');

        $tableName = ucfirst($this->argument('table_name'));
        $seederName = studly_case($tableName).'TableSeeder';
        $seederVariableName = camel_case($tableName);

        $txt = file_get_contents(__DIR__.'/SeederStub.stub') or die("Unable to open file!");

        $txt = str_replace('{SEEDER_NAME}', $seederName, $txt);
        $txt = str_replace('{SEEDER_VARIABLE_NAME}', $seederVariableName, $txt);
        $txt = str_replace('{TABLE_NAME}', $tableName, $txt);

        $path = database_path($seedsPath.$seederName.'.php');
        $file = fopen($path, "w") or die("Unable to open file!");
        fwrite($file, $txt);
        fclose($file);

        $this->info($seederName.' named seeder created in seeds folder.');
    }
}
