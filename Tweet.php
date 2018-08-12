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
 * The actual Tweet Message
 */
class Tweet
{
    /**
     * @var array
     */
    private $msg;

    /**
     * Main construct
     *
     * @param Array $msg
     */
    public function __construct(Array $msg)
    {
        $this->msg = $msg;
    }

    public function getScreenName()
    {
        return $this->msg['user']['screen_name'];
    }

    public function getUserId()
    {
        return $this->msg['user']['id'];
    }

    public function getTweetArray()
    {
        return $this->msg;
    }

    public function getTweetId()
    {
        return $this->msg['id'];
    }

    public function getRetweetId()
    {
        $rtId = @$this->msg['retweeted_status']['id'];
        $rtId = (!empty($rtId)) ? $rtId : null;
    }

    public function isARetweet()
    {
        return (!empty($this->msg['retweeted_status']));
    }

    public function hasMedia()
    {
        return (!empty(@$this->msg['entities']['media']));
    }

    public function getRetweetScreenName()
    {
        $sn = @$this->msg['retweeted_status']['user']['screen_name'];
        $sn = (!empty($sn)) ? $sn : null;

        return $sn;
    }

    public function getRetweetText()
    {
        return $this->msg['retweeted_status']['text'];
    }

    public function getRetweetFullText()
    {
        $et = @$this->msg['retweeted_status']['extended_tweet'];

        return (!empty($et)) ? $et['full_text'] : null;
    }

    public function getText()
    {
        //var_dump($msg);
        $text = $this->getFullText();
        if (null !== $text) {
            return $text;
        }

        if ($this->isARetweet()) {
            // This tweet has is a Retweet!
            $screenName = $this->getRetweetScreenName();

            // Let's try to get the full text, if it has been truncated
            $text = $this->getRetweetFullText();
            if (null === $text) {
                $text = $this->getRetweetText();
            }

            return "RT $screenName ".$text;
        } else {
            return $this->msg['text'];
        }
    }

    public function getTextLen()
    {
        return \strlen($this->getText());
    }

    private function getFullText()
    {
        $ft = @$this->msg['extended_tweet']['full_text'];
        $text = (!empty($ft)) ? $ft : null;

        return $text;
    }
}
