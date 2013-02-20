<?php
include 'tmhOAuth-master/tmhOAuth.php';
include 'tmhOAuth-master/tmhUtilities.php';

$feed = 'http://feeds.feedburner.com/FEED_NAME_HERE?format=xml';
$feed_to_array = (array) new SimpleXmlElement(file_get_contents($feed),LIBXML_NOCDATA);

foreach($feed_to_array as $post) {
        foreach($post as $post2) {
                if ($post2->link){
                        $tag = "";

                        foreach($post2->category as $category) {
                                $tag[] = $category;
                        }

                        unset($tag[0]);

                        $arraynum = count($tag);
                        $rand = mt_rand(1,$arraynum);

                        $hashtag = str_replace(" ","",$tag[$rand]);
                        $hashtag = "#$hashtag";

                        $rand2 = mt_rand(1,$arraynum);

                        if ($rand2 = $rand) {
                                $rand2++;
                                if(!$tag[$rand2]) {
                                        $hashtag .= " #GENERIC_HASH_TAG";
                                } else {
                                        $hashtag2 = str_replace(" ","",$tag[$rand2]);
                                        $hashtag .= " #$hashtag2";
                                }
                        }

                        $title = trim(htmlentities($post2->title, ENT_QUOTES, "UTF-8"));
                        $title = substr($title,0,20)."... ";
                        $shurl = $post2->link;
                        $blink = trim(bitly_shorten($shurl));
                        $chkurl = check_bitly($blink);
                        if ($chkurl) {
                                echo "exists!";
                        } else {
                                $desc = trim(substr(strip_tags($post2->description),0,strpos($post2->description,' ',75)))."...";
                                $tweet = "$title $desc $blink $hashtag";
                                tweet($tweet);
                        }



               }

        }

}

function bitly_shorten($url,$format='txt') {
        $buser = "bitly_user_key";
        $bapi = "bitly_api_key";

        $connectURL = 'http://api.bit.ly/v3/shorten?login='.$buser.'&apiKey='.$bapi.'&uri='.urlencode($url).'&format='.$format;
        return file_get_contents($connectURL);
}

function check_bitly($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($curl);
        curl_close($curl);
        preg_match("/HTTP\/1\.[1|0]\s(\d{3})/",$data,$matches);
        return ($matches[1] == 301);
}


function tweet($tweet) {
        $tmhOAuth = new tmhOAuth(array(
                'consumer_key' => 'zzz',
                'consumer_secret' => 'zzz',
                'user_token' => 'zzz',
                'user_secret' => 'zzz',
        ));

        $response = $tmhOAuth->request('POST', $tmhOAuth->url('1.1/statuses/update'), array('status' => $tweet));

        if ($response != 200) {
                //Do something if the request was unsuccessful
                echo 'There was an error posting the message.';
        }
}

?>
