<?php
/** 
 * Twitter Client begining!
 * PHP Version 7.2
 * 
 * @category Twitter-client
 * @package  TwitterClient
 * @author   Lutchy Horace <lutchy.horace@lhpmail.us>
 * @license  GNU 3.0
 * @link     https://github.com/lhorace/twitter-client/LICENSE
 **/

class TwitterClient
{
    /**
     * __construct
     */
    public function __construct()
    {

    }

    public function streamingThread($track)
    {
        
    }

    public function streamingThreadCallback($message)
    {
        global $logger, $twitter;


        echo time() . "\r\n";
        if ($message !== null) {
            $logger->notice('Inside callable!');

            $text = $this->getText($message);
            $text = preg_replace('/@/', '', $text);
            $screenName = $this->getScreenName($message);
            $newStatus = "RT @{$screenName} $text";
            if ($screenName == 'ChannelsSmall') {
                return false;
            } else if (strlen($newStatus) > 260) {
                $id = $this->getPostID($message);
                $twitter->statuses_retweet_ID('id=' . $id);
            } else {
                $params = ['status' => $newStatus];
                //var_dump($params);
                echo 'In status update' . "\r\n";
                $reply = $twitter->statuses_update($params);
                var_dump($reply);
            }
            
            flush();
            //sleep(120);
        }        
        
        return false;
    }

    private function getScreenName($msg)
    {
        return $msg['user']['screen_name'];
    }

    private function getPostID($msg)
    {
        return $msg['id'];
    }

    private function getText($msg)
    {
        //var_dump($msg);
        if (!empty($msg['extended_tweet'])) {
            return $msg['extended_tweet']['full_text'];
        } elseif (!empty($msg['retweeted_status'])) {
            // This tweet has been is a Retweeted!
            $screen_name = $msg['retweeted_status']['user']['screen_name'];
        
            // Let's try to get the full text, if it has been truncated
            if (!empty($msg['retweeted_status']['extended_tweet'])) {
                $text = $msg['retweeted_status']['extended_tweet']['full_text'];
            } else {
                $text = $msg['retweeted_status']['text'];
            }
            return "RT $screen_name " . $text;
        } else {
            return $msg['text'];
        }
    }
}