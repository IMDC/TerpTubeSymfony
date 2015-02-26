<?php

namespace IMDC\TerpTubeBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;

class Common
{
    public static function login(Client $client)
    {
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('_submit')->form(array(
            '_username'  => 'test',
            '_password'  => 'test'
        ));

        $client->submit($form);

        if (!$client->getResponse()->isRedirect()) {
            throw new \Exception('login failed');
        }

        $client->followRedirect();
    }

    /**
     * @param Crawler $crawler
     * @return array
     */
    public static function getModel(Crawler $crawler)
    {
        return json_decode($crawler->filter('#__testModel')->text(), true);
    }

    public static function formViewToPhpValues($view, &$values)
    {
        $values[$view->vars['name']] = $view->vars['value'];
        if (count($view->children) > 0) {
            foreach ($view->children as $child) {
                Common::formViewToPhpValues($child, $values[$view->vars['name']]);
            }
        }
    }
}
