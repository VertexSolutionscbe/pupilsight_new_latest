<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\View;

/**
 * Base class for Views.
 *
 * @version  v17
 * @since    v17
 */
class View implements \ArrayAccess
{
    protected $templateEngine;

    protected $data = [];

    /**
     * Create a new view.
     *
     * @param $templateEngine
     */
    public function __construct(\Twig_Environment $templateEngine = null)
    {
        // Do some duck typing, since Twig does not have a common Interface.
        if (!is_null($templateEngine) && !method_exists($templateEngine, 'render')) {
            throw new \InvalidArgumentException("The template engine passed into a View constructor must implement a render() method.");
        }

        $this->templateEngine = $templateEngine;
    }

    /**
     * Add a piece of data to the view.
     *
     * @param  string|array  $key
     * @param  mixed   $value
     */
    public function addData($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
    }

    /**
     * Include a PHP file in a protected scope, and returns the output-buffered
     * contents as a string. The data array is extracted into individual
     * variables in the current scope.
     *
     * @param string $filepath
     * @param array  $data
     * @return string
     */
    public function fetchFromFile(string $filepath, array $data = []) : string
    {
        if (!is_file($filepath)) {
            return '';
        }

        extract($data);

        try {
            ob_start();
            $included = include $filepath;
            $output = ob_get_clean() . (is_string($included)? $included : '');
        } catch (\Exception $e) {
            $output = '';
            ob_end_clean();
            throw $e;
        }

        return $output;
    }

    /**
     * Render a given template using the template engine + provided data
     * and returns the result as a string.
     *
     * @param string $template
     * @param array  $data
     * @return string
     */
    public function fetchFromTemplate(string $template, array $data = []) : string
    {
        return $this->templateEngine->render($template, $data);
    }

    /**
     * Render the view with the given template and return the result as a string.
     *
     * @param string $template
     * @param array $data
     * @return string
     */
    public function render(string $template, array $data = []) : string
    {
        $data = array_merge($this->data, $data);

        return $this->templateEngine->render($template, $data);
    }

    /**
     * Determine if a piece of data is bound.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key) : bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get a piece of bound data to the view.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->data[$key];
    }

    /**
     * Set a piece of data on the view.
     *
     * @param  string  $key
     * @param  mixed   $value
     */
    public function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Unset a piece of data from the view.
     *
     * @param  string  $key
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }
}
