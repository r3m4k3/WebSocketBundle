<?php

namespace Gos\Bundle\WebSocketBundle\Topic;

use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

class TopicPeriodicTimer implements \IteratorAggregate
{
    /**
     * @var TimerInterface[]
     */
    protected $registry = [];

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * @param TopicInterface $topic
     * @param string         $name
     *
     * @return bool
     */
    public function getAllPeriodicTimers(TopicInterface $topic, $name)
    {
        if (!$this->isPeriodicTimerActive($topic, $name)) {
            return false;
        }

        $namespace = spl_object_hash($topic);

        return $this->registry[$namespace][$name];
    }

    /**
     * @param TopicInterface $topic
     *
     * @return TimerInterface[]
     */
    public function getPeriodicTimers(TopicInterface $topic)
    {
        $namespace = spl_object_hash($topic);

        if (!isset($this->registry[$namespace])) {
            return [];
        }

        return $this->registry[$namespace];
    }

    /**
     * @param TopicInterface $topic
     * @param string         $name
     * @param int|float      $timeout
     * @param mixed          $callback
     */
    public function addPeriodicTimer(TopicInterface $topic, $name, $timeout, $callback)
    {
        $namespace = spl_object_hash($topic);

        if (!isset($this->registry[$namespace])) {
            $this->registry[$namespace] = [];
        }

        $this->registry[$namespace][$name] = $this->loop->addPeriodicTimer($timeout, $callback);
    }

    /**
     * @param TopicInterface $topic
     *
     * @return bool
     */
    public function isRegistered(TopicInterface $topic)
    {
        $namespace = spl_object_hash($topic);

        return isset($this->registry[$namespace]);
    }

    /**
     * @param TopicInterface $topic
     * @param string         $name
     *
     * @return bool
     */
    public function isPeriodicTimerActive(TopicInterface $topic, $name)
    {
        $namespace = spl_object_hash($topic);

        return isset($this->registry[$namespace][$name]);
    }

    /**
     * @param TopicInterface $topic
     * @param string         $name
     */
    public function cancelPeriodicTimer(TopicInterface $topic, $name)
    {
        $namespace = spl_object_hash($topic);

        if (!isset($this->registry[$namespace][$name])) {
            return;
        }

        $timer = $this->registry[$namespace][$name];
        $this->loop->cancelTimer($timer);
        unset($this->registry[$namespace][$name]);
    }

    /**
     * @param TopicInterface $topic
     */
    public function clearPeriodicTimer(TopicInterface $topic)
    {
        $namespace = spl_object_hash($topic);
        unset($this->registry[$namespace]);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->registry);
    }
}
