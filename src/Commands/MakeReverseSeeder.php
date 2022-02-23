<?php

namespace GGuney\RSeeder\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MakeReverseSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:reverseSeeder {table_name} {--from=} {--by=} {--except=}';

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
        $seedersPath = 'seeders/';

        $tableName = lcfirst($this->argument('table_name'));
        $seederName = Str::studly($tableName) . 'TableSeeder';
        $seederVariableName = Str::camel($tableName);

        $columns = $this->setColumns($tableName);
        $rows = $this->getRows($tableName);
        $string = $this->rowsToString($rows, $columns);
        $txt = $this->replaceStub($seederName, $seederVariableName, $tableName, $string);
        $this->saveFile($seedersPath, $seederName, $txt);
    }

    /**
     * Get table column names.
     *
     * @param  string  $tableName
     * @return array
     */
    private function getColumns(string $tableName): array
    {
        return DB::connection()->getSchemaBuilder()->getColumnListing($tableName);
    }

    /**
     * Set table columns.
     *
     * @param  string  $tableName
     * @return array
     */
    private function setColumns(string $tableName): array
    {
        $excepts = $this->option('except');
        $tmpColumns = $this->getColumns($tableName);
        $columns = [];

        if (isset($excepts)) {
            $excepColumnsArray = explode(',', $excepts);

            foreach ($tmpColumns as $tmpColumn) {
                if ((!in_array($tmpColumn, $excepColumnsArray))) {
                    $columns[] = $tmpColumn;
                }
            }
        } else {
            $columns = $tmpColumns;
        }

        return $columns;
    }

    /**
     * Get rows from a table name.
     *
     * @param  string  $tableName
     * @return Collection
     */
    private function getRows(string $tableName): Collection
    {
        $fromColumn = $this->option('by');
        $fromDate = $this->option('from');

        return isset($fromDate) && isset($fromColumn) ?
            DB::table($tableName)->where($fromColumn, '>', $fromDate)->get() :
            DB::table($tableName)->get();
    }

    /**
     * DB Rows to array string.
     *
     * @param  Collection  $rows
     * @param  array  $columns
     * @return string
     */
    private function rowsToString(Collection $rows, array $columns): string
    {
        $rowsAsString = "";

        foreach ($rows as $row) {
            $rowsAsString .= "\n\t\t\t[";

            foreach ($columns as $column) {
                if (
                    filter_var($row->$column, FILTER_VALIDATE_INT) ||
                    filter_var($row->$column, FILTER_VALIDATE_FLOAT)
                ) {
                    $value = $row->$column;
                } else {
                    if (!isset($row->$column)) {
                        $value = 'NULL';
                    } else {
                        $value = "'" . str_replace("'", "\'", $row->$column) . "'";
                    }
                }

                $rowsAsString .= "'$column' => " . $value . ", ";
            }

            $rowsAsString = rtrim($rowsAsString, ', ') . "],";
        }

        return $rowsAsString;
    }

    /**
     * Get stub.
     *
     * @return string
     */
    private function getStub(): string
    {
        if ($content = file_get_contents(__DIR__ . '/SeederStub.stub')) {
            return $content;
        }

        return die("Unable to open file!");
    }

    /**
     * Replace stub with variables.
     *
     * @param  string  $seederName
     * @param  string  $seederVariableName
     * @param  string  $tableName
     * @param  string  $rows
     */
    private function replaceStub(
        string $seederName,
        string $seederVariableName,
        string $tableName,
        string $rows
    ): string {
        return str_replace(
            ['{SEEDER_NAME}', '{SEEDER_VARIABLE_NAME}', '{TABLE_NAME}', '{ARRAY}'],
            [$seederName, $seederVariableName, $tableName, $rows],
            $this->getStub()
        );
    }

    /**
     * Save replaced stub as seeder.
     *
     * @param  string  $seedersPath
     * @param  string  $seederName
     * @param  string  $content
     */
    private function saveFile(string $seedersPath, string $seederName, string $content): void
    {
        $path = database_path($seedersPath . $seederName . '.php');
        $file = fopen($path, "w") or die("Unable to open file!");
        fwrite($file, $content);
        fclose($file);
        $this->info($seederName . ' named seeder created in seeds folder.');
    }
}
