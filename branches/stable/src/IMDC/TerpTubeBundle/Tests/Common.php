<?php

namespace IMDC\TerpTubeBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;

class Common
{
    public static function login(Client $client, $username = 'test', $password = 'test') //TODO drop default for username param
    {
        $crawler = $client->request('GET', '/login');

        file_put_contents(
            '../test_logs/' . substr(get_called_class(), strrpos(get_called_class(), '\\')) . '.' . __FUNCTION__ . '_form.html',
            $client->getResponse()->getContent()
        );

        $form = $crawler->selectButton('_submit')->form(array(
            '_username' => $username,
            '_password' => $password
        ));

        $client->submit($form);

        file_put_contents(
            '../test_logs/' . substr(get_called_class(), strrpos(get_called_class(), '\\')) . '.' . __FUNCTION__ . '_result.html',
            $client->getResponse()->getContent()
        );

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
