<?php

/**
 * This file is part of LHPROJECTS Twitter Client package
 *
 * (c) Lutchy Horace <lutchy.horace@lhpmail.us>
 *
 * PHP Version 7.2
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */


// Initialize first
require 'config.php';
require 'vendor/autoload.php';
require 'Tweet.php';
require 'TweetPost.php';
require 'TwitterClient.php';

// Require all the clases I need here
use TwitterClient\TwitterClient as TwitterClient;
use Codebird\Codebird as Twitter;
use Consolidation\Log\Logger as Logger;
use Consolidation\Log\LogOutputStyler as LogOutputStyler;
use Symfony\Component\Console\Output\ConsoleOutput as ConsoleOutput;

// Startup logger
$output = new ConsoleOutput();
$logger = new Logger($output);
$logger->setLogOutputStyler(new LogOutputStyler());

// MY TwitterClient
$tc = new TwitterClient();

echo "Now running!\r\n";
if (empty($_SERVER['PRE_START'])) {

   // $tc->startStreamingThread();
}


//$reply = $twitter->user();


//var_dump($twitter);