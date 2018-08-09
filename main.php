<?php

/** 
 * Twitter Client begining!
 * PHP Version 7.2
 * 
 * @category Twitter-client-Main
 * @package  TwitterClientMain
 * @author   Lutchy Horace <lutchy.horace@lhpmail.us>
 * @license  GNU 3.0
 * @link     https://github.com/lhorace/twitter-client/LICENSE
 **/


// Initialize first
require 'config.php';
require 'vendor/autoload.php';
require 'twitter-client.php';

// Require all the clases I need here
use Codebird\Codebird as Twitter;
use Consolidation\Log\Logger as Logger;
use Consolidation\Log\LogOutputStyler as LogOutputStyler;
use Symfony\Component\Console\Output\ConsoleOutput as ConsoleOutput;

// Startup logger
$output = new ConsoleOutput();
$logger = new Logger($output);
$logger->setLogOutputStyler(new LogOutputStyler());

// MY TwitterClient
$tc = new TwitterClient;

// Auth to twitter
Twitter::setConsumerKey(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
$twitter = Twitter::getInstance();
$twitter->setUseCurl(false);
$twitter->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
$twitter->setToken(TWITTER_ACCESS_TOKEN, TWITTER_ACCESS_TOKEN_SECRET);

$twitter->setStreamingCallback([$tc, 'streamingThreadCallback']);

$params = ['track' => 'youtu be #smallyoutuber,youtube com #smallyoutuber,youtu be #smallyoutubers,youtube com #smallyoutubers,youtu be #smallyoutuber ChannelsSmall,youtube com #smallyoutuber ChannelsSmall,youtu be #smallyoutubers ChannelsSmall,youtube com #smallyoutubers ChannelsSmall'];
$reply = $twitter->statuses_filter($params);



var_dump($reply);

//$reply = $twitter->user();


//var_dump($twitter);