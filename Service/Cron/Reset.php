<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service\Cron;

use \Magento\Cron\Model\Schedule;

class Reset implements ResetInterface
{

    const SECONDS_IN_MINUTE = 60;

    const RESET_CRON_AFTER_MIN = 60;

    /**
     * @var ScheduleFactory
     */
    protected $_scheduleFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Cron\Model\ScheduleFactory $scheduleFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_scheduleFactory = $scheduleFactory;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
    }

    public function deleteUnrealizedJobs()
    {
        $runningJobs = $this->_getRunningSchedules();
        $currentTime = $this->dateTime->gmtTimestamp();

        /* @var $schedule \Magento\Cron\Model\Schedule */
        foreach ($runningJobs as $schedule) {

            $scheduledTime = strtotime($schedule->getScheduledAt());
            if ($scheduledTime > $currentTime) {
                continue;
            }

            $scheduleLifetime = self::RESET_CRON_AFTER_MIN * self::SECONDS_IN_MINUTE;

            if ($scheduledTime < $currentTime - $scheduleLifetime) {
                try {
                    $schedule->delete();
                } catch (\Exception $e) {
                    $this->logger->info($e);
                }
            }
        }
    }

    /**
     * @return \Magento\Cron\Model\ResourceModel\Schedule\Collection
     */
    protected function _getRunningSchedules()
    {
        return $this->_scheduleFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', Schedule::STATUS_RUNNING)
            ->addFieldToFilter('messages', ['notnull' => true])
            ->load();
    }

}
