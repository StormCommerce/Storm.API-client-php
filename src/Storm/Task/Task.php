<?php


namespace Storm\Task;

use Carbon\Carbon;
use Storm\Contract\Taskable;
use Storm\Traits\CacheStore;


/**
 * Class Task
 * @package Storm\Task
 */
class Task
{
    use CacheStore;
    /**
     *
     */
    const DAILY = 1;
    /**
     *
     */
    const HOURLY = 2;
    /**
     *
     */
    const MINUTE = 3;

    /**
     * @var string
     */
    protected $taskable;
    /**
     * @var int
     */
    protected $when = self::DAILY;
    protected $span = 1;

    /**
     * Task constructor.
     * @param $taskable
     * @param int $span
     */
    public function __construct( $taskable, $span = 1)
    {
        $this->taskable = $taskable;
        $this->span = $span;
    }

    /**
     *
     */
    public function daily()
    {
        $this->when = static::DAILY;
        return $this;
    }

    /**
     *
     */
    public function hourly()
    {
        $this->when = static::HOURLY;
        return $this;
    }

    /**
     *
     */
    public function minute()
    {
        $this->when = static::MINUTE;
        return $this;
    }

    /**
     * @return bool
     */
    public function shouldExecute()
    {
        return Carbon::now() > $this->timeStamp();
    }

    /**
     *
     */
    public function execute()
    {
        call_user_func([$this->taskable, 'task']);
    }

    /**
     * @return Carbon
     */
    public function timeStamp()
    {
        return Carbon::parse($this->cache()->get($this->identifier(), Carbon::now()->subYear()->toAtomString()));
    }

    /**
     *
     */
    public function updateTimestamp()
    {
        $timestamp = Carbon::now();
        switch ($this->when) {
            case self::DAILY:
                $timestamp->addDays($this->getSpan());
                break;
            case self::HOURLY:
                $timestamp->addHours($this->getSpan());
                break;
            case self::MINUTE:
                $timestamp->addMinutes($this->getSpan());
                break;
        }
        $this->cache()->forever($this->identifier(), $timestamp->toAtomString());
    }

    /**
     * @return int
     */
    public function getSpan()
    {
        return $this->span;
    }

    /**
     * @param int $span
     */
    public function setSpan($span)
    {
        $this->span = $span;
        return $this;
    }

    /**
     * @return string
     */
    public function identifier()
    {
        return "task-timestamp-{$this->className()}-{$this->when}";
    }

    /**
     * @return string
     */
    public function className()
    {
        return $this->taskable;
    }
}