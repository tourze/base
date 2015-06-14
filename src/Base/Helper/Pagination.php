<?php

namespace tourze\Base\Helper;

use Exception;
use tourze\Base\Config;
use tourze\Http\HttpRequest;
use tourze\Base\Route;
use tourze\View\View;
use tourze\Base\Security\Valid;

/**
 * Pagination links generator.
 *
 * @package    Base/Pagination
 * @category   Base
 * @author     YwiSax
 */
class Pagination
{

    // Merged configuration settings
    protected $config = [
        'currentPage'    => [
            'source' => 'query_string',
            'key'    => 'page'
        ],
        'totalItems'     => 0,
        'itemsPerPage'   => 10,
        'view'           => 'sdk/pagination/basic',
        'autoHide'       => true,
        'firstPageInUrl' => false,
    ];

    // Current page number
    protected $currentPage;

    // Total item count
    protected $totalItems;

    // How many items to show per page
    protected $itemsPerPage;

    // Total page count
    protected $totalPages;

    // Item offset for the first item displayed on the current page
    protected $currentFirstItem;

    // Item offset for the last item displayed on the current page
    protected $currentLastItem;

    // Previous page number; false if the current page is the first one
    protected $previousPage;

    // Next page number; false if the current page is the last one
    protected $nextPage;

    // First page number; false if the current page is the first one
    protected $firstPage;

    // Last page number; false if the current page is the last one
    protected $lastPage;

    // Query offset
    protected $offset;

    // Request object
    protected $_request;

    // Route to use for URIs
    protected $_route;

    // Parameters to use with Route to create URIs
    protected $_routeParams = [];

    /**
     * Creates a new Pagination object.
     *
     * @param   array       $config configuration
     * @param   HttpRequest $request
     *
     * @return $this
     */
    public static function factory(array $config = [], HttpRequest $request = null)
    {
        return new static($config, $request);
    }

    /**
     * Creates a new Pagination object.
     *
     * @param   array     $config configuration
     * @param HttpRequest $request
     */
    public function __construct(array $config = [], HttpRequest $request = null)
    {
        // Overwrite system defaults with application defaults
        $this->config = $this->configGroup() + $this->config;

        if (null === $request)
        {
            $request = HttpRequest::current();
        }

        $this->_request = $request;

        // Assign default Route
        $this->_route = $request->route;

        // Assign default route params
        $this->_routeParams = $request->param();
        $this->_routeParams['directory'] = $request->directory;
        $this->_routeParams['controller'] = $request->controller;
        $this->_routeParams['action'] = $request->action;

        // Pagination setup
        $this->setup($config);
    }

    /**
     * Retrieves a pagination config group from the config file. One config group can
     * refer to another as its parent, which will be recursively loaded.
     *
     * @param string $group
     *
     * @return array config settings
     */
    public function configGroup($group = 'default')
    {
        // Load the pagination config file
        $fileConfig = Config::load('pagination.php');

        // Initialize the $config array
        $config['group'] = (string) $group;

        // Recursively load requested config groups
        while (isset($config['group']) && isset($fileConfig->$config['group']))
        {
            // Temporarily store config group name
            $group = $config['group'];
            unset($config['group']);

            // Add config group values, not overwriting existing keys
            $config += $fileConfig->$group;
        }

        // Get rid of possible stray config group names
        unset($config['group']);

        // Return the merged config group settings
        return $config;
    }

    /**
     * Loads configuration settings into the object and (re)calculates pagination if needed.
     * Allows you to update config settings after a Pagination object has been constructed.
     *
     * @param   array $config configuration
     *
     * @return  object  Pagination
     */
    public function setup(array $config = [])
    {
        if (isset($config['group']))
        {
            // Recursively load requested config groups
            $config += $this->configGroup($config['group']);
        }

        // Overwrite the current config settings
        $this->config = $config + $this->config;

        // Only (re)calculate pagination when needed
        if (null === $this->currentPage
            || isset($config['currentPage'])
            || isset($config['totalItems'])
            || isset($config['itemsPerPage'])
        )
        {
            // Retrieve the current page number
            if ( ! empty($this->config['currentPage']['page']))
            {
                // The current page number has been set manually
                $this->currentPage = (int) $this->config['currentPage']['page'];
            }
            else
            {
                $queryKey = $this->config['currentPage']['key'];

                switch ($this->config['currentPage']['source'])
                {
                    case 'query_string':
                        $this->currentPage = (null !== $this->_request->query($queryKey))
                            ? (int) $this->_request->query($queryKey)
                            : 1;
                        break;

                    case 'route':
                        $this->currentPage = (int) $this->_request->param($queryKey, 1);
                        break;
                }
            }

            // Calculate and clean all pagination variables
            $this->totalItems = (int) max(0, $this->config['totalItems']);
            $this->itemsPerPage = (int) max(1, $this->config['itemsPerPage']);
            $this->totalPages = (int) ceil($this->totalItems / $this->itemsPerPage);
            $this->currentPage = (int) min(max(1, $this->currentPage), max(1, $this->totalPages));
            $this->currentFirstItem = (int) min((($this->currentPage - 1) * $this->itemsPerPage) + 1, $this->totalItems);
            $this->currentLastItem = (int) min($this->currentFirstItem + $this->itemsPerPage - 1, $this->totalItems);
            $this->previousPage = ($this->currentPage > 1) ? $this->currentPage - 1 : false;
            $this->nextPage = ($this->currentPage < $this->totalPages) ? $this->currentPage + 1 : false;
            $this->firstPage = ($this->currentPage === 1) ? false : 1;
            $this->lastPage = ($this->currentPage >= $this->totalPages) ? false : $this->totalPages;
            $this->offset = (int) (($this->currentPage - 1) * $this->itemsPerPage);
        }

        // Chainable method
        return $this;
    }

    /**
     * Generates the full URL for a certain page.
     *
     * @param   integer $page page number
     *
     * @return  string   page URL
     */
    public function url($page = 1)
    {
        // Clean the page number
        $page = max(1, (int) $page);

        // No page number in URLs to first page
        if ($page === 1 && ! $this->config['firstPageInUrl'])
        {
            $page = null;
        }

        switch ($this->config['currentPage']['source'])
        {
            case 'query_string':

                return Url::site($this->_route->uri($this->_routeParams) .
                    $this->query([$this->config['currentPage']['key'] => $page]));

            case 'route':

                return Url::site($this->_route->uri(array_merge($this->_routeParams,
                        [$this->config['currentPage']['key'] => $page])) . $this->query());
        }

        return '#';
    }

    /**
     * Checks whether the given page number exists.
     *
     * @param   integer $page page number
     *
     * @return  boolean
     */
    public function validPage($page)
    {
        // Page number has to be a clean integer
        if ( ! Valid::digit($page))
        {
            return false;
        }

        return $page > 0 && $page <= $this->totalPages;
    }

    /**
     * 渲染分页html
     *
     * @param   mixed $view 要使用的视图，或传入一个视图实例
     *
     * @return  string  HTML
     */
    public function render($view = null)
    {
        // Automatically hide pagination whenever it is superfluous
        if (true === $this->config['autoHide'] && $this->totalPages <= 1)
        {
            return '';
        }

        if (null === $view)
        {
            // Use the view from config
            $view = $this->config['view'];
        }

        if ( ! $view instanceof View)
        {
            // Load the view file
            $view = View::factory($view);
        }

        // Pass on the whole Pagination object
        return $view->set(get_object_vars($this))
            ->set('page', $this)
            ->render();
    }


    /**
     * Request setter / getter
     *
     * @param \tourze\Http\HttpRequest $request
     *
     * @return \tourze\Http\HttpRequest If used as getter
     * @internal param $HttpRequest
     *
     */
    public function request(HttpRequest $request = null)
    {
        if (null === $request)
        {
            return $this->_request;
        }

        $this->_request = $request;

        return $this;
    }

    /**
     * Route setter / getter
     *
     * @param \tourze\Base\Route $route
     *
     * @return \tourze\Base\Route Route if used as getter
     * @internal param $Route
     *
     */
    public function route(Route $route = null)
    {
        if (null === $route)
        {
            return $this->_route;
        }

        $this->_route = $route;

        return $this;
    }

    /**
     * Route parameters setter / getter
     *
     * @param    array    Route parameters to set
     *
     * @return    array    Route parameters if used as getter
     * @return    $this    Dbal as setter
     */
    public function routeParams(array $routeParams = null)
    {
        if (null === $routeParams)
        {
            return $this->_routeParams;
        }

        $this->_routeParams = $routeParams;

        return $this;
    }

    /**
     * URL::query() replacement for Pagination use only
     *
     * @param   array $params Parameters to override
     *
     * @return  string
     */
    public function query(array $params = null)
    {
        if (null === $params)
        {
            // Use only the current parameters
            $params = $this->_request->query();
        }
        else
        {
            // Merge the current and new parameters
            $params = array_merge($this->_request->query(), $params);
        }

        if (empty($params))
        {
            // No query parameters
            return '';
        }

        // Note: http_build_query returns an empty string for a params array with only null values
        $query = http_build_query($params, '', '&');

        // Don't prepend '?' to an empty string
        return ('' === $query) ? '' : ('?' . $query);
    }

    /**
     * Renders the pagination links.
     *
     * @return  string  pagination output (HTML)
     */
    public function __toString()
    {
        try
        {
            return $this->render();
        }
        catch (Exception $e)
        {
            return '';
        }
    }

    /**
     * Returns a Pagination property.
     *
     * @param   string $key property name
     *
     * @return  mixed   Pagination property; null if not found
     */
    public function __get($key)
    {
        return isset($this->$key) ? $this->$key : null;
    }

    /**
     * Updates a single config setting, and recalculates pagination if needed.
     *
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->setup([$key => $value]);
    }

}
