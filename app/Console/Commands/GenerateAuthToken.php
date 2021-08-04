<?php

namespace App\Console\Commands;

use App\Models\Emr;
use Illuminate\Console\Command;

class GenerateAuthToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:generate-token {--key= : Choose an EMR by its key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an API token for an EMR';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $emr = $this->whichEmr();

        $emr->tokens()->delete();
        $token = $emr->createToken($name = "{$emr->key}-token");

        $this->info("Generated a new {$emr->name} token...");
        $this->line($token->plainTextToken);

        return 0;
    }

    /**
     * Determine which EMR to generate a token for.
     *
     * @return  \App\Models\Emr
     */
    private function whichEmr(): Emr
    {
        if ($emrKey = $this->option('key')) {
            return Emr::firstWhere('key', $emrKey);
        }

        $emrs = Emr::all();
        $emrName = $this->choice('Choose an EMR?', $emrs->pluck('name')->toArray());

        return $emrs->firstWhere('name', $emrName);
    }
}
