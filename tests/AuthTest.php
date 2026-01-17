<?php

namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthTest extends WebTestCase
{   
    public function testRegistration()
    {
        $client = static::createClient();

        // simulate an account creation
        $crawler = $client->request('GET', '/register');
        $form = $crawler->filter('form')->form(
            [
                'registration_form[email]' => 'njong5@gmail.com',
                'registration_form[plainPassword]' => 'test123',
                'registration_form[agreeTerms]' => true
            ]
        );

        $client->submit($form);

        // Assert that the user is redirected to the login page
        $this->assertResponseRedirects('/login');

        // follow the redirect
        $client->followRedirect();

        // Assert that we are on the login page
        $this->assertAnySelectorTextContains('h1', 'Please sign in');


    }

    public function testDuplicateEmail()
    {

        $client = static::createClient();

        // simulate an account creation
        $crawler = $client->request('GET', '/register');
        $form = $crawler->filter('form')->form(
            [
                'registration_form[email]' => 'njong2@gmail.com',
                'registration_form[plainPassword]' => 'test123',
                'registration_form[agreeTerms]' => true
            ]
        );

        $client->submit($form);

        // Assert that there is a duplicate email error
        $this->assertAnySelectorTextContains('li', 'There is already an account with this email');


    }

    /**
     * Test for the login fucntionality
     * @return void
     */
    public function testLogin()
    {   
        $client = static::createClient();

        // Simulate a user login
        $crawler = $client->request('GET', '/login');
        $form = $crawler->filter('form')->form([
            '_username' => 'njong2@gmail.com',
            '_password' => 'bebe123'
        ]);

        $client->submit($form);

        // $this->assertResponseIsSuccessful();
        // $this->assertResponseStatusCodeSame(200);
        // $this->assertJson($client->getResponse()->getContent());

        // Assert that the user is redirected to the homepage
        $this->assertResponseRedirects('/');

        // Follow the redirect
        $client->followRedirect();

        // Assert that the dashboard is displayed
        $this->assertSelectorTextContains('h1','Welcome');
    }

 }