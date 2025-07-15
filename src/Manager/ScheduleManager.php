<?php

namespace LEDController\Manager;

use LEDController\LEDController;
use LEDController\Packet;
use LEDController\Exception\ScheduleException;
use LEDController\Enum\Command;

/**
 * Schedule Manager for program scheduling and timing
 */
class ScheduleManager
{
    private LEDController $controller;
    private array $schedules = [];
    private array $playLists = [];
    private bool $schedulingEnabled = false;

    public function __construct(LEDController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Create a new schedule plan
     */
    public function createPlan(int $planId, array $schedule): self
    {
        if ($planId < 0 || $planId > 255) {
            throw new ScheduleException("Plan ID must be between 0 and 255");
        }

        $this->validateSchedule($schedule);
        $this->schedules[$planId] = $schedule;

        return $this;
    }

    /**
     * Play a program immediately
     */
    public function playProgram(int $programId, array $options = []): self
    {
        $packet = new Packet($this->controller->getConfig()['cardId'], Command::EXTERNAL_CALLS->value);
        $packet->setSubCommand(0x08); // Play program

        $data = chr($programId);
        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new ScheduleException("Failed to play program: " . $response->getReturnCodeMessage());
        }

        return $this;
    }

    /**
     * Enable scheduling
     */
    public function enable(): self
    {
        $this->schedulingEnabled = true;
        $this->sendSchedulingControl(true);

        return $this;
    }

    /**
     * Disable scheduling
     */
    public function disable(): self
    {
        $this->schedulingEnabled = false;
        $this->sendSchedulingControl(false);

        return $this;
    }

    /**
     * Set brightness schedule
     */
    public function setBrightnessSchedule(array $brightnessSchedule): self
    {
        if (count($brightnessSchedule) !== 24) {
            throw new ScheduleException("Brightness schedule must have exactly 24 values");
        }

        $packet = new Packet($this->controller->getConfig()['cardId'], Command::BRIGHTNESS_QUERY_SET->value);

        $data = chr(0x00); // Set command
        foreach ($brightnessSchedule as $brightness) {
            $data .= chr($brightness);
        }

        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new ScheduleException("Failed to set brightness schedule: " . $response->getReturnCodeMessage());
        }

        return $this;
    }

    /**
     * Get schedule plan
     */
    public function getPlan(int $planId): ?array
    {
        return $this->schedules[$planId] ?? null;
    }

    /**
     * Get all plans
     */
    public function getAllPlans(): array
    {
        return $this->schedules;
    }

    /**
     * Delete a schedule plan
     */
    public function deletePlan(int $planId): self
    {
        unset($this->schedules[$planId]);
        return $this;
    }

    /**
     * Clear all schedules
     */
    public function clearAll(): self
    {
        $this->schedules = [];
        $this->playLists = [];

        return $this;
    }

    /**
     * Validate schedule structure
     */
    private function validateSchedule(array $schedule): void
    {
        if (!isset($schedule['startTime']) || !isset($schedule['endTime'])) {
            throw new ScheduleException("Schedule must have startTime and endTime");
        }

        if (!isset($schedule['program']) && !isset($schedule['playlist'])) {
            throw new ScheduleException("Schedule must have program or playlist");
        }

        if (isset($schedule['weekdays']) && !is_array($schedule['weekdays'])) {
            throw new ScheduleException("Weekdays must be an array");
        }
    }

    /**
     * Send scheduling control command
     */
    private function sendSchedulingControl(bool $enable): void
    {
        $packet = new Packet($this->controller->getConfig()['cardId'], Command::EXTERNAL_CALLS->value);
        $packet->setSubCommand(0x09); // Schedule control

        $data = chr($enable ? 0x01 : 0x00);
        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new ScheduleException("Failed to control scheduling: " . $response->getReturnCodeMessage());
        }
    }
}
