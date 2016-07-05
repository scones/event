<?php
/**
 * Phossa Project
 *
 * PHP version 5.4
 *
 * @category  Library
 * @package   Phossa2\Event
 * @copyright Copyright (c) 2016 phossa.com
 * @license   http://mit-license.org/ MIT License
 * @link      http://www.phossa.com/
 */
/*# declare(strict_types=1); */

namespace Phossa2\Event\Traits;

use Phossa2\Event\Interfaces\EventInterface;
use Phossa2\Event\Interfaces\CountableInterface;

/**
 * CountableTrait
 *
 * Implmentation of CountableInterface
 *
 * @package Phossa2\Event
 * @author  Hong Zhang <phossa@126.com>
 * @see     CountableInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
trait CountableTrait
{
    /**
     * callable mapping
     *
     * @var    callable[];
     * @access protected
     */
    protected $callable_map = [];

    /**
     * {@inheritDoc}
     */
    public function many(
        /*# int */ $times,
        /*# string */ $eventName,
        callable $callable,
        /*# int */ $priority = 50
    ) {
        // wrap the callable
        $wrapper = function(EventInterface $event) use ($callable, $times) {
            static $cnt = 0;
            if ($cnt++ < $times) {
                call_user_func($callable, $event);
            }
        };

        // mapping callable
        $oid = $this->hashCallable($callable);
        $this->callable_map[$eventName][$oid] = $wrapper;

        // bind wrapper instead of the $callable
        $this->on($eventName, $wrapper, $priority);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function one(
        /*# string */ $eventName,
        callable $callable,
        /*# int */ $priority = 50
    ) {
        return $this->many(1, $eventName, $callable, $priority);
    }

    /**
     * Override `off()` in EventManager
     *
     * Added support for countable callable
     *
     * {@inheritDoc}
     */
    public function off(
        /*# string */ $eventName = '',
        callable $callable = null
    ) {
        if (null !== $callable) {
            $oid = $this->hashCallable($callable);
            if (isset($this->callable_map[$eventName][$oid])) {
                $callable = $this->callable_map[$eventName][$oid];
                unset($this->callable_map[$eventName][$oid]);
            }
        } else {
            unset($this->callable_map[$eventName]);
        }
        return parent::off($eventName, $callable);
    }

    /**
     * Returns a unique id for $callable with $eventName
     *
     * @param  callable $callable
     * @return string
     * @access protected
     */
    protected function hashCallable(callable $callable)/*# string */
    {
        return spl_object_hash((object) $callable);
    }

    // from EventManagerInterface
    abstract public function on(/*# string */ $eventName, callable $callable, /*# int */ $priority = 50);
}
