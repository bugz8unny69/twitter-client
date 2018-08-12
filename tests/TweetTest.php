<?php

/**
 * This file is part of LHPROJECTS Twitter Client package
 *
 * (c) Lutchy Horace <lutchy.horace@lhpmail.us> Sun Aug 12 2018
 *
 * PHP Version 7.2
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use PHPUnit\Framework\TestCase;

require 'Tweet.php';

class TweetTest extends TestCase
{
    private $jsonFile = 'tests/twitData.json';

    public function testJsonFileExists()
    {
        $this->assertFileExists($this->jsonFile);
    }

    public function testGetText()
    {
        $tweetArr = json_decode(file_get_contents($this->jsonFile), true);

        $tweet = new TwitterClient\Tweet($tweetArr);

        $text = $tweet->getText();

        $this->assertInternalType('string', $text, "Got a ".gettype($text)." instead of a string");
    }
}