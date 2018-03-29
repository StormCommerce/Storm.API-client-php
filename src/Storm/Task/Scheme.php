<?php


namespace Storm\Task;


use Storm\Model\Support\ParametricsRepository;
use Storm\StormClient;

/**
 * Class Scheme
 * @package Storm\Task
 */
class Scheme
{
    /**
     * @var Task[]
     */
    protected $tasks;

    protected $taskables = [
        ParametricsRepository::class
    ];

    /**
     * @param Task $task
     */
    public function addTask(Task $task)
    {
        $this->tasks[] = $task;
    }

    public function setTaskList($taskable = [])
    {
        $this->taskables = $taskable;
    }

    public function addTaskable($taskable)
    {
        if (!is_string($taskable)) {
            $taskable = get_class($taskable);
        }
        if ($taskable !== false) {
            $this->taskables[] = $taskable;
        }
    }

    public function addDefaultTasks()
    {
        foreach ($this->taskables as $taskable) {
            $this->addTask(new Task($taskable));
        }
    }

    /**
     *
     */
    public function execute()
    {
        $this->addDefaultTasks();
        foreach ($this->tasks as $task) {
            if ($task->shouldExecute()) {
                $task->execute();
                $task->updateTimestamp();
            }
        }
        StormClient::self()->batcher()->fireIfNotFired();
        StormClient::self()->batcher()->resolve();
    }
}