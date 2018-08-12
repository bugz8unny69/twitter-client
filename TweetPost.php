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

/**
 * That will post the tweet
 */
class TweetPost
{
    private $twitterUsers;
    private $twitPosts;

    public function setUp()
    {
        $client = new \MongoDB\Client();
        $this->twitterUsers = $client->twitterClient->twitterUsers;
        $this->twitPosts = $client->twitterClient->twitPosts;
    }

    public function perform()
    {
        global $tc;

        $tweet = new Tweet($this->args['twitArr']);
        $text = $tweet->getText();

        // Parse text
        $discardReason = null;
        $discared = false;
        foreach (TWITTER_TEXT_FILTER_REGEX as $regex) {
            if (true === $regex['inverse']) {
                //TODO: Impliment the inverse action
            } else {
                if (\preg_match($regex['regex'], $text)) {
                    $arr = [
                        'tweet_id' => $tweet->getTweetId(),
                        'tweet_sn' => $tweet->getScreenName(),
                        'tweet_post' => $text,
                        'tweet_discard_reason' => $regex['filter_reason'],
                        'regex_used' => $regex['regex'],
                    ];

                    $discared = true;
                    $discardReason = $arr['tweet_discard_reason'];
                }
            }
        }

        foreach (TWITTER_TEXT_REPLACE_REGEX as $regex) {
            if ($text = \preg_replace($regex['regex'], $regex['replace'], $text)) {
                $arr = [
                    'tweet_id' => $tweet->getTweetId(),
                    'tweet_sn' => $tweet->getScreenName(),
                    'tweet_post' => $text,
                    'tweet_txt_replace_reason' => $regex['replace_reason'],
                    'regex_used' => $regex['regex'],
                    'regex_value' => $regex['replace'],
                ];
            }
        }

        // Check if I've already retweeted this!
        $tid = $tweet->getRetweetId();
        $tid = (!empty($tid)) ? $tid : $tweet->getTweetId();
        if ($this->findPost($tid)) {
            $arr = [
                'tweet_id' => $tweet->getTweetId(),
                'tweet_sn' => $tweet->getScreenName(),
                'tweet_post' => $text,
                'tweet_discard_reason' => 'Already Retweeted',
            ];

            $json = \json_encode($arr, JSON_PRETTY_PRINT);
            var_dump($json);

            return;
        }

        // Check if this post has already been posted!
        $screenName = $tweet->getScreenName();
        $newStatus = "RT @{$screenName} $text";
        if ('ChannelsSmall' === $screenName) {
            return false;
        }

        $retweeted = false;
        $posted = false;
        if (false === $discared) {
            if (strlen($newStatus) > 260 or $this->args['twitPrio'] === 'low') {
                try {
                    $tc->retweet($tid);
                    $retweeted = true;
                } catch (\RuntimeException $e) {
                    echo 'TwitterClient\TweetPost->perform(): '.$e->getMessage()."\r\n";
                    $retweeted = false;
                }
            } else {
                try {
                    $tc->postNewTweet($newStatus);
                    $posted = true;
                } catch (\RuntimeException $e) {
                    echo 'TwitterClient\TweetPost->perform(): '.$e->getMessage()."\r\n";
                    $posted = false;
                }
            }
        }

        $dbArr = [
            '_id' => $tweet->getTweetId(),
            'twit_sn' => $tweet->getScreenName(),
            'twit_retweet_count' => 0,
            'twit_post_count' => 0,
        ];

        $this->updateDb($this->twitterUsers, $arr, ['_id' => $dbArr['_id']]);

        // Add this tweet post to database
        $dbArr = [
            'twit_id' => $tid,
            'twit_sn' => $tweet->getScreenName(),
            'twit_post' => $text,
            'twit_discarded' => $discared,
            'twit_discared_reason' => $discardReason,
            'twit_posted_status' => $posted,
            'twit_retweet_status' => $retweeted,
        ];

        $this->updateDb($this->twitPosts, $dbArr, ['twit_id' => $dbArr['twit_id']]);
    }

    private function updateDb(Object $collections, Array $data, Array $key)
    {
        if ($collections->findOne($key)) {
            $collections->updateOne($key, $data);
        } else {
            $collections->insertOne($data);
        }
    }

    private function findPost(Int $id)
    {
        return $this->twitPosts->findOne(['twit_id' => $id]);
    }
}
