<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\View\Components;

/**
 * Breadcrumb trail.
 *
 * @version v17
 * @since   v17
 */
class Breadcrumbs
{
    protected $baseURL = '';
    protected $items = [];

    /**
     * Start the trail at Home.
     */
    public function __construct()
    {
        $this->add(__('Home'));
        $this->setBaseURL('index.php?q=');
    }

    /**
     * Set the URL that will be prepended to all following routes.
     *
     * @param string $baseURL
     * @return self
     */
    public function setBaseURL(string $baseURL)
    {
        $this->baseURL = trim($baseURL, '/ ').'/';
        
        return $this;
    }

    /**
     * Add a named route to the trail.
     *
     * @param string $title   Name to display on this route's link
     * @param string $route   URL relative to the trail's BaseURL
     * @param array  $params  Additional URL params to append to the route
     * @return self
     */
    public function add(string $title, string $route = '', array $params = [])
    {
        $route = !empty($params)
            ? trim($route, '/ ').'&'.http_build_query($params)
            : trim($route, '/ ');

        $this->items[$title] = !empty($route)? $this->baseURL . $route : '';

        return $this;
    }

    /**
     * Get all items in the trail. Don't return 'Home' if there's no other items.
     *
     * @return array
     */
    public function getItems() : array
    {
        return count($this->items) > 1 ? $this->items : [];
    }
}
