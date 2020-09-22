<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Contracts\Services;

/**
 * Session Interface 
 * Borrowed in part from Illuminate\Contracts\Session\Session
 *
 * @version	v17
 * @since	v17
 */
interface Session
{
    /**
     * Checks if one or more keys exist.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function exists($keys);

    /**
     * Checks if one or more keys are present and not null.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function has($keys);

    /**
     * Get an item from the session.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Set a key / value pair or array of key / value pairs in the session.
     *
     * @param  string|array  $key
     * @param  mixed       $value
     * @return void
     */
    public function set($key, $value = null);

    /**
     * Remove an item from the session, returning its value.
     *
     * @param  string  $key
     * @return mixed
     */
    public function remove($key);
    
    /**
     * Remove one or many items from the session.
     *
     * @param  string|array  $keys
     * @return void
     */
    public function forget($keys);
}
