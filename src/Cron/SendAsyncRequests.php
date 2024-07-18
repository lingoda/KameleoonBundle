<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Cron;

use DateTimeInterface;
use Lingoda\CronBundle\Cron\Schedule;
use Lingoda\CronBundle\Cron\ScheduleBasedCronJob;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class SendAsyncRequests extends ScheduleBasedCronJob
{
    public function __construct(
        private readonly string $projectDir,
        private readonly string $kameleoonWorkDir,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function getSchedule(): Schedule
    {
        return Schedule::everyMinute();
    }

    public function run(?DateTimeInterface $lastStartedAt): void
    {
        $command = $this->projectDir
            . '/vendor/lingoda/kameleoon-bundle/scripts/kameleoon-client-php-process-queries.sh';
        $process = new Process([$command, $this->kameleoonWorkDir]);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->logger->error('Failed to send data to Kameleoon. Output: ' . $process->getErrorOutput());
        }
    }
}
