<?php

namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeTest extends WebTestCase
{
    public function testHomepage()
    {
        // create a client to simulate a browser
        $client = static::createClient();

        // Request the homepage
        $crawler = $client->request('GET', '/');

        // Assert that the response is successful
        $this->assertResponseIsSuccessful();

        // Check that the correct template is used
        $this->assertSelectorTextContains('h1', 'Welcome');
    }
}