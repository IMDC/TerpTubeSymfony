<?php

namespace IMDC\TerpTubeBundle\Definition;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Knp\Component\Pager\Paginator;
use Symfony\Component\HttpFoundation\Request;

class MultiPagination implements PaginatorAwareInterface
{
    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @var array
     */
    private $pages;

    public function __construct(array $pages = array())
    {
        $this->pages = $pages;
    }

    /**
     * extract paginator params from request
     * @param Request $request
     */
    public function prepare(Request $request)
    {
        foreach ($this->pages as &$params) {
            $params['page'] = $request->query->get($params['knp']['pageParameterName'], $params['page']);
        }
    }

    /**
     * @param $name
     * @param $object
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     * @throws \Exception
     */
    public function paginate($name, $object)
    {
        if (!array_key_exists($name, $this->pages)) {
            throw new \Exception(sprintf('"%s" not found in paginator params', $name));
        }

        $params = $this->pages[$name];

        $paginated = $this->paginator->paginate(
            $object,
            $params['page'],
            $params['pageLimit'],
            $params['knp']
        );

        if (array_key_exists('urlParams', $params)) {
            foreach ($params['urlParams'] as $key => $value) {
                $paginated->setParam($key, $value);
            }
        }

        return $paginated;
    }

    /**
     * Sets the KnpPaginator instance.
     *
     * @param Paginator $paginator
     *
     * @return mixed
     */
    public function setPaginator(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @param $name
     * @param array $page
     * @return $this
     */
    public function addPage($name, array $page)
    {
        $this->pages[$name] = $page;

        return $this;
    }

    /**
     * @return array
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param array $pages
     */
    public function setPages(array $pages)
    {
        $this->pages = $pages;
    }
}
