<?php

namespace Grafite\Maintenance\Concerns;

use Grafite\Maintenance\Support\ShellResult;

trait HandlesShellResults
{
    /**
     * Print the outcome of one or more shell results and return an exit code.
     */
    protected function reportResults(ShellResult ...$results): int
    {
        $exitCode = self::SUCCESS;

        foreach ($results as $result) {
            if ($result->successful()) {
                if ($result->output !== '') {
                    $this->info($result->output);
                }

                continue;
            }

            if ($result->error !== '') {
                $this->warn($result->error);
            }

            $this->error("Command failed with exit code {$result->exitCode}");
            $exitCode = self::FAILURE;
        }

        return $exitCode;
    }
}
