<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;

class BotManController extends AbstractController
{
    /**
     * @Route("/message", name="message")
     */
    function messageAction(Request $request)
    {
        DriverManager::loadDriver(\BotMan\Drivers\Web\WebDriver::class);

        // Configuration for the BotMan WebDriver
        $config = [];

        // Create BotMan instance
        $botman = BotManFactory::create($config);

        // Give the bot some things to listen for.
        $botman->hears('(hello|hi|hey)', function (BotMan $bot) {
            $bot->reply('Hello!');
        });

        $botman->hears('help', function (BotMan $bot) {
            $bot->reply('Try saying: what can I do here? Or where can I buy stuff?');
        });

        $botman->hears('what can I do here?', function (BotMan $bot) {
            $bot->reply('This is a gaming platform that allows you to stay upto date everyday with new games');
        });

        $botman->hears('where can I buy stuff?', function (BotMan $bot) {
            $bot->reply('Go visit the shop, there is a lot of cool stuff you can buy');
        });

        $botman->hears('Chkawlek monsieur', function (BotMan $bot) {
            $bot->reply('Borjlouleya t3ebna barcha, nchallah taatina 20');
        });

        // Set a fallback
        $botman->fallback(function (BotMan $bot) {
            $bot->reply('Sorry, I did not understand.');
        });

        // Start listening
        $botman->listen();

        return new Response();
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('home/index.html.twig');
    }
    
    /**
     * @Route("/chatframe", name="chatframe")
     */
    public function chatframeAction(Request $request)
    {
        return $this->render('chat_frame.html.twig');
    }
}
