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

namespace TwitterClient;

use Codebird\Codebird as Twitter;

/**
 * This is the main TwitterClient that will process
 * twitter post.
 */
class TwitterClient
{

    private $twitter;
    private $mongocl;
    private $db;

    /**
     * Init TwitterClient
     */
    public function __construct()
    {
        // Auth to twitter
        Twitter::setConsumerKey(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
        $this->twitter = Twitter::getInstance();
        $this->twitter->setUseCurl(false);
        $this->twitter->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
        $this->twitter->setToken(TWITTER_ACCESS_TOKEN, TWITTER_ACCESS_TOKEN_SECRET);

        $this->mongocl = new \MongoDB\Client();
        $this->db = $this->mongocl->twitterClient;
    }

    public function enqueue(Tweet $tweet)
    {
        global $logger;

        $prio = 'low';

        if ($this->isSupporter($tweet->getScreenName())) {
            $prio = 'now';
        }
        if ($tweet->hasMedia()) {
            $prio = 'high';
        }
        if ($this->isFollower($tweet->getUserId())) {
            $prio = 'med';
        }
        //$data = \serialize($tweet);
        $twitData = $tweet->getTweetArray();
        $args = ['twitPrio' => $prio, 'twitArr' => $twitData];

        \Resque::enqueue($prio, '\TwitterClient\TweetPost', $args);
        echo sprintf('Tweet ID "%d" is now queued with prio %s', $tweet->getTweetId(), $prio)."\r\n";
        $logger->debug(sprintf('Tweet ID "%b" is now queued with prio %s', $tweet->getTweetId(), $prio));

        //$tweetQueue = $db->tweetQueue;

        //$arr = ['twit_id' => $tweet->getTweetID(), 'twit_data' => $data];
        //$status = $tweetQueue->insertOne($arr);
    }

    public function startStreamingThread()
    {
        try {
            $this->twitter->setStreamingCallback([$this, 'streamingThreadCallback']);
        } catch (Exception $e) {
            echo 'Unable to set streamer callback'.$e->getMessage()."\r\n";
        }

        foreach (TWITTER_TRACK_KEYWORDS as $keyword) {
            @$keywords .= (empty($keywords)) ? $keyword : ','.$keyword;
        }
        var_dump($keywords);
        $reply = $this->twitter->statuses_filter(['track' => $keywords]);

        var_dump($reply);
    }

    public function streamingThreadCallback($message)
    {
        global $logger;


        echo time()."\r\n";
        if ($message !== null) {
            $logger->notice('Inside callable!');

            $this->enqueue(new Tweet($message));
            
            flush();
            //sleep(120);
        }
        
        return false;
    }

    public function postNewTweet($msg)
    {
        $params = ['status' => $msg];
        //var_dump($params);
        echo 'In posting tweet'."\r\n";
        $reply = $this->twitter->statuses_update($params);
        if ('200' !== $reply['httpstatus']) {
            $error = sprintf('Error posting new tweet: %d %s %d', $reply['httpstatus'], $reply['errors'][0]['message'], $reply['errors'][0]['code']);
            throw new \RuntimeException($error, 1);
        }
    }

    public function retweet($id)
    {
        echo "In retweeting post\r\n";
        $reply = $this->twitter->statuses_retweet_ID('id='.$id);
        if ('200' !== $reply['httpstatus']) {
            $error = sprintf('Error retweeting tweet: %d %s %d', $reply['httpstatus'], $reply['errors'][0]['message'], $reply['errors'][0]['code']);
            throw new \RuntimeException($error, 1);
        }
    }

    private function isSupporter(String $sn)
    {
        return \in_array($sn, TWITTER_SUPPORTERS);
    }

    public function isFollower($id)
    {
        $params = ['source_id' => $id, 'target_screen_name' => 'ChannelsSmall'];
        $reply = $this->twitter->friendships_show($params);

        return $reply["relationship"]["source"]["following"];
    }
}