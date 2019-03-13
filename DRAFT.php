<?php
/*
|--------------------------------------------------------------------------
| PHP Data Structures - API Draft - v2.0
|
| Target PHP Version: 7.3.1
|
| https://pecl.php.net/package/ds
|
|--------------------------------------------------------------------------
|
| This file is will be used for planning the API of the next major version of
| the data structures extension. Please feel free to suggest modifications.
*/
namespace Ds;

use Countable;
use RuntimeException;
use Throwable;
use Traversable;

/******************************************************************************/
/*                           ORDERING & EQUALITY                              */
/******************************************************************************/

/**
 * @todo Proposal: == for objects, === otherwise.
 */

/******************************************************************************/
/*                                EXCEPTIONS                                  */
/******************************************************************************/

/**
 * Should be thrown when a structure is accessed unexpectedly, or in a way that
 * is undefined, or to avoid ambiguous results. This is a marker interface to
 * support catching all access exceptions.
 */
interface AccessException extends Throwable {}

/**
 * Marker interface for all state-related exceptions, such as heap corruption.
 */
interface StateException extends Throwable {}

/**
 * Should be thrown when an empty container is accessed a clear, obvious result.
 */
class EmptyStateException extends RuntimeException implements StateException {}

/**
 * Should be thrown when an index or key is not within the given access bounds
 * of a structure, such as attempting to access beyond the length.
 */
class InvalidOffsetException extends RuntimeException implements AccessException  {}

/**
 * Should be thrown when a key is not supported, eg. when an attempt is made to
 * associate a value with NULL in Map, causing ambiguity in `find`.
 */
class InvalidKeyException extends RuntimeException implements AccessException {}


/******************************************************************************/
/*                                INTERFACES                                  */
/******************************************************************************/

/**
 * Marker interface to indicate that a class is immutable.
 */
interface Immutable 
{
}

/**
 * Indicates that a structure can be cleared.
 */
interface Clearable
{
    function clear();
}

/**
 * Indicates that a structure contains elements and is aware of the number of
 * items contained at any point in time. How a container must determine the
 * count is implementation-specific.
 *
 * This interface does not imply that a structure is traversable.
 */
interface Countable extends \Countable
{
    /**
     *
     */
    function count(): int;
}

/**
 * Indicates that a structure can be sorted by value.
 *
 * This interface does not guarantee that the sorting algorithm will be stable.
 * Implementations should use a domain-specific tiebreaker when required.
 */
interface Sortable
{
    /**
     * @return static A sorted copy, or $this structure sorted in-place.
     */
    function sort(callable $comparator = null);
}

/**
 * Indicates that an object is designed to be used in hash-based structures.
 */
interface Hashable extends Immutable
{
    /**
     * @return mixed A scalar value to be used when generating a hashcode.
     */
    function getHashSource(): string;
}

/**
 * Indicates that a structure contains elements and is aware of the number of
 * items contained at any point in time. How a container must determine the
 * count is implementation-specific.
 */
interface Container
{
    /**
     * @return bool TRUE if the value is in $this structure, FALSE otherwise.
     */
    function contains($value): bool;

    /**
     *
     */
    function isEmpty(): bool;
}

/**
 * Indicates that a structure can be accessed using a zero-based integer index
 * indicating the position of an element from the beginning of the structure.
 *
 * We extend Countable because it should always be possible to determine bounds.
 */
interface Sequence extends Countable, Container
{
    /**
     * @return mixed The value at the
     *
     * @throws InvalidOffsetException if the $offset is not within [0, size).
     */
    function get(int $offset);
    
    /**
     * @return int The offset of the given value, or -1 if the value could not be found.
     */
    function indexOf($value): int;

    /**
     * @return mixed The first value in $this structure.
     *
     * @throws EmptyStateException
     */
    function first();

    /**
     * @return mixed The last value in $this structure.
     *
     * @throws EmptyStateException
     */
    function last();
}

/**
 * A sequence which can be modified, either in-place or as a copy.
 */
interface MutableSequence extends Sequence
{
    /**
     * Sets the value at the given offset.
     *
     * @throws InvalidOffsetException if the index is not within [0, size)
     */
    function set(int $offset, $value);

    /**
     * Removes the value at the given offset, moving all successive
     * values one position towards the front.
     *
     * @throws InvalidOffsetException if the index is not within [0, size)
     */
    function unset(int $offset);

    /**
     * Moves all values between the given index and the end of the sequence
     * towards the back, then inserts the given values into the gap.
     *
     * @throws InvalidOffsetException if the index is out of range [0, size]
     */
    function insert(int $offset, ...$values);
}

/**
 * Indicates that a structure is a sequence that is always sorted.
 */
interface SortedSequence extends Sequence 
{
}

/**
 * Indicates that a structure is designed to quickly determine whether a given
 * value is already contained by it.
 */
interface Set extends Container
{
    /**
     * @return static A set containing values in both $this and $other.
     */
    function or(Set $other): Set;

    /**
     * @return static A set containing values in either $this or $other,
     *                but not in both.
     */
    function xor(Set $other): Set;

    /**
     * @return static A set containing values in $this but not in $other.
     */
    function not(Set $other): Set;

    /**
     * @return static A set containing values in $this that are also in $other.
     */
    function and(Set $other): Set;
}

/**
 * A set which can be modified, either in-place or as a copy.
 */
interface MutableSet extends Set
{
    /**
     * Adds the given value to $this set if it is not already in $this set.
     */
    function add(...$values);

    /**
     * Removes a given value from $this set, or does nothing if that value could
     * not be found. The caller can use `has` to determine membership before
     * removal. This method therefore promises only that the given value is not
     * a member of the set on return.
     */
    function remove($value);
}

/**
 * Indicates that a structure is a set that is always sorted.
 */
interface SortedSet extends Set 
{
}

/**
 * A structure that associates one value with another and provides the ability
 * to query or adjust associations efficiently.
 *
 * @todo Should Map extend Set (not mutable)?
 *       What about the set operation methods then?
 *
 * @todo Should Map require some kind of merge, diff, and intersection support?
 */
interface Map
{
    /**
     * @todo if NULL keys are not allowed, should we throw if $key is NULL?
     *
     * @return mixed The value associated with the $key, or $default if the key
     *               could not be found and a $default was provided.
     *
     * @throws AccessException if the $key was not found with default given.
     */
    function get($key, $default = null);

    /**
     * @return Set All keys from the map added in traversal order.
     *
     * @todo Should this return an iterator?
     */
    function keys(): Set;

    /**
     * @return Set All keys from the map added in traversal order.
     *
     * @todo Should this return an iterator?
     */
    function values(): Sequence;
}

/**
 * A map which can be modified, either in-place or as a copy.
 */
interface MutableMap extends Map
{
    /**
     * Associates the $key with the $value, overriding any previous association.
     *
     * @throws InvalidKeyException if the map implementation does not support
     *                             the given key, eg. NULL
     */
    function set($key, $value);

    /**
     * Removes the given $key from the map, and does nothing if they key could
     * not be found. This emulates `unset`
     */
    function unset($key);
}

/**
 * Indicates that a structure is a map that is always sorted.
 */
interface SortedMap extends Map 
{
}

/**
 * Indicates that a structure can receive and produce values efficiently in a
 * semantically defined order. Both operations should be O(1).
 */
interface Transferable
{
    /**
     * Offers one or more values to this transferable.
     */
    function send(...$values);

    /**
     * Removes and returns the next value produced by this transferable.
     *
     * @throws EmptyStateException
     */
    function poll();
}


/******************************************************************************/
/*                                 CLASSES                                    */
/******************************************************************************/

/**
 *
 */
final class Allocation implements
    ArrayAccess,
    Clearable,
    Sortable
    {
        public const TYPE_BOOL      = 0; // undefined => false
        public const TYPE_INTEGER   = 1; // undefined => 0
        public const TYPE_FLOAT     = 2; // undefined => 0.0
        public const TYPE_STRING    = 3; // undefined => NULL
        public const TYPE_ARRAY     = 4; // undefined => NULL
        public const TYPE_OBJECT    = 5; // undefined => NULL

        /**
         * Allow buffers to be truncated to 0.
         */
        public const MIN_CAPACITY = 0;

        /**
         * We must be able to accept and return capacity as integer, so we can
         * only support up to max int values. This is absolutely massive though
         * so should be incredibly rare to approach.
         *
         * @todo Should we consider when USE_ZEND_ALLOC might be 0? Is this a
         *       potential security or buffer overflow issue? I'm not sure...
         */
        public const MAX_CAPACITY = PHP_INT_MAX;

        /**
         * @throws UnexpectedValueException if the type is not a valid constant.
         */
        public function __construct(int $capacity, int $type) {}

        /**
         * @return int The type of the values in this buffer.
         */
        public function type(): int {}

        /**
         * @return int The current capacity of this buffer.
         */
        public function capacity(): int {}

        /**
         * Re-allocates this buffer to a new capacity, which may truncate the
         * current buffer. It is up to user to manage this case.
         *
         * @throws RangeException if the capacity is not within the valid range:
         *                        MIN_CAPACITY <= $capacity <= MAX_CAPACITY
         */
        public function realloc(int $capacity) {}

        /**
         * Returns the value at the given offset as a standard value of the type
         * that this buffer stores. Type validation is done on write. If nothing
         * has been stored at the given offset, this method will return an
         * appropriate "null" value for the type (see notes on constants).
         *
         * @throws InvalidOffsetException if the offset is not within [0, capacity)
         */
        public function offsetGet($offset) {}

        /**
         * Sets the value at the given offset after validating its value type.
         *
         * Note: NULL is not supported. Use `unset` to clear a value by offset.
         *
         * @throws InvalidOffsetException if the offset is not within [0, capacity)
         */
        public function offsetSet($offset, $value) {}

        /**
         * Sets the value at the given offset to NULL.
         *
         * @throws InvalidOffsetException if the offset is not within [0, capacity),
         *                         unless called as part of a silent `unset`.
         */
        public function offsetUnset($offset) {}

        /**
         * Returns whether there is a non-NULL value at the given offset. This
         * method returns FALSE if the offset is not within [0, capacity).
         */
        public function offsetExists($offset) {}
    }

/**
 * A fixed-size immutable sequence.
 */
final class Tuple implements
    Traversable,
    Sequence,  /* Countable, Container */
    Hashable   /* Immutable */
    {
        public function __construct(iterable $iter) {}
    }

/**
 * List structure, dynamic size, contiguous, no ops at the front.
 */
final class Vector implements
    ArrayAccess,
    Traversable,
    Clearable,
    Sortable,
    MutableSequence /* Sequence, Countable, Container */
    {
        /**
         * Adds one or more values to the end of the vector.
         */
        function push(...$values);

        /**
         * Removes and returns the value at the end of the vector.
         *
         * @throws EmptyStateException
         */
        function pop();
    }

/**
 * Double-ended-queue, supports prepend and append, but nothing in-between.
 */
final class Deque implements
    Clearable,
    Transferable,
    MutableSequence  /* Sequence, Countable, Container */
    {
        /**
         * Adds one or more values to the end of the deque.
         */
        function push(...$values);

        /**
         * Removes and returns the value at the end of the deque.
         *
         * @throws EmptyStateException
         */
        function pop();

        /**
         * Adds one or more values to the start of the deque.
         */
        public function unshift(...$values);

        /**
         * Removes and returns the value at the start of the deque.
         *
         * @throws EmptyStateException
         */
        function shift();
    }

/**
 * An immutable sequence that is evaluated and sorted on first access.
 */
final class SetSequence implements
    ArrayAccess,
    Traversable,
    Immutable,
    SortedSet,      /* Set, Container */
    SortedSequence, /* Sequence, Countable, Container */
    {
        public function __construct(iterable $iter) {}
    }

/**
 * The Set equivalent of HashMap, which might not use a HashMap internally, but
 * will always preserve insertion order.
 */
final class HashSet implements
    ArrayAccess,
    Traversable,
    Countable,
    Clearable,
    Transferable,
    Sortable,
    Sequence,     /* Countable, Container */
    MutableSet    /* Set, Container */
    {}

/**
 * A structure that is always sorted and does not allow duplicate elements.
 *
 * This would be useful for building sorted datasets, or sets without hashing.
 *
 * See: https://github.com/php-ds/ext-ds/issues/121
 */
final class TreeSet implements
    Traversable,
    Countable,
    Clearable,
    MutableSet, /* Set, Container */
    SortedSet   /* Set, Container */
    {
        /**
         * Creates a new tree set using an optional comparator.
         */
        public function __construct(callable $comparator = null) {}
    }

 /**
 * A set that allows duplicate values, and keeps a count of the number of times
 * a value appears in the set. This is a set because we can determine whether
 * it contains a value _at all_, and it is also a sequence because the traversal
 * can yield a value more than once.
 */
final class MultiSet implements
    ArrayAccess,
    Traversable,
    Countable,
    Clearable,
    Transferable,
    MutableSet     /* Set */
    {
        /**
         * Creates a new multiset using values from $iter.
         */
        public function __construct(callable $comparator = null) {}

        /**
         * Adjusts the count of a value by a given count. If the resulting count
         * becomes <= 0, the value will be removed. If a value is not already in
         * the set, it will be added before the count is adjusted.
         *
         *   > 0: Add
         *   < 0: Remove
         *
         * @return The resulting frequency after the adjustment.
         */
        public function adjust($value, int $count = 1): int {}

        /**
         * @return int The number of instances of a given value that $this set
         *             contains, which could be 0 if not in the set at all.
         */
        public function freq($value): int {}

        /**
         * Returns an iterator of the values with the highest frequencies, where
         * the key is the element and the value is the frequency.
         */
        public function rank(): iterable {}

        /**
         * Creates an iterable where the key is the element in the set and the
         * value is the frequency / multiplicity.
         */
        public function enumerate(): iterable {}
    }

/**
 * This structure is based on the same internal structure of the PHP array,
 * but is not identical. We can use hashable objects as keys, and insertion
 * order is still preserved.
 */
final class HashMap implements
    ArrayAccess,
    Traversable,
    Countable,
    Clearable,
    Sortable,
    MutableMap  /* Map */
    {}

/**
 *
 */
final class TreeMap implements
    ArrayAccess,
    Traversable,
    Countable,
    Clearable,
    MutableMap, /* Map */
    SortedMap   /* Map */
    {}

/**
 * A basic first-in-last-out structure.
 */
final class Stack implements
    Countable,
    Transferable
    {
        /**
         * Adds a value to the top of the stack.
         */
        function push(...$values);

        /**
         * Removes and returns the value at the top of the stack.
         *
         * @throws EmptyStateException
         */
        function pop();
    }

/**
 * A basic first-in-first-out structure.
 */
final class Queue implements
    Countable,
    Transferable
    {
        /**
         * Adds a value to the queue.
         */
        function push(...$values);

        /**
         * Removes and returns the next value in the queue.
         */
        function shift();
    }

/**
 * Stable heap with an optional comparator, defaulting to minimum.
 */
final class Heap implements
    Countable,
    Clearable,
    Transferable
    {
        /**
         * Creates a new heap using an optional comparator.
         */
        public function __construct(callable $comparator = null) {}

        /**
         * Adds a value to the heap.
         */
        public function push(...$values) {}

        /**
         * Removes and returns the value at the top of the heap.
         *
         * @throws EmptyStateException
         */
        public function shift() {}
    }

/**
 * A queue that yields values in order of priority, from high to low.
 */
final class PriorityQueue implements
    Countable,
    Clearable,
    Transferable
    {
        /**
         * Creates a new priority queue using an optional comparator.
         */
        public function __construct(callable $comparator = null) {}

        /**
         * Adjusts the priority of a given value, setting it to the return value
         * of the given mutator, then ensures that heap invariants are resolved.
         */
        public function adjust($value, callable $mutator) {}

        /**
         * Adds a value to the priority queue, using a given initial priority.
         */
        public function push($value, $priority) {}

        /**
         * Removes and returns the value at the front of the priority queue.
         *
         * @throws EmptyStateException
         */
        public function shift() {}
    }
