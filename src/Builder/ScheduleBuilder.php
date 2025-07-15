<?php

namespace LEDController\Builder;

use LEDController\Manager\ScheduleManager;

/**
 * Schedule builder for fluent interface
 */
class ScheduleBuilder
{
    private ScheduleManager $manager;
    private array $schedule = [];

    public function __construct(ScheduleManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Set program ID
     */
    public function program(int $programId): self
    {
        $this->schedule['programId'] = $programId;
        return $this;
    }

    /**
     * Set playlist ID
     */
    public function playlist(int $playlistId): self
    {
        $this->schedule['playlistId'] = $playlistId;
        return $this;
    }

    /**
     * Set start time
     */
    public function startTime(string $time): self
    {
        $this->schedule['startTime'] = $time;
        return $this;
    }

    /**
     * Set end time
     */
    public function endTime(string $time): self
    {
        $this->schedule['endTime'] = $time;
        return $this;
    }

    /**
     * Set weekdays
     */
    public function weekdays(array $days): self
    {
        $this->schedule['weekdays'] = $days;
        return $this;
    }

    /**
     * Set Monday to Friday
     */
    public function weekdaysOnly(): self
    {
        $this->schedule['weekdays'] = [1, 2, 3, 4, 5];
        return $this;
    }

    /**
     * Set weekends
     */
    public function weekends(): self
    {
        $this->schedule['weekdays'] = [6, 7];
        return $this;
    }

    /**
     * Set all days
     */
    public function everyday(): self
    {
        $this->schedule['weekdays'] = [1, 2, 3, 4, 5, 6, 7];
        return $this;
    }

    /**
     * Set repeat times
     */
    public function repeatTimes(int $times): self
    {
        $this->schedule['repeatTimes'] = $times;
        return $this;
    }

    /**
     * Set play time
     */
    public function playTime(int $seconds): self
    {
        $this->schedule['playTime'] = $seconds;
        return $this;
    }

    /**
     * Create the schedule
     */
    public function create(int $planId): ScheduleManager
    {
        $this->manager->createPlan($planId, $this->schedule);
        return $this->manager;
    }

    /**
     * Get the built schedule
     */
    public function getSchedule(): array
    {
        return $this->schedule;
    }
}
