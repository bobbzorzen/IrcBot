<?php
require_once("phpbotdefines.php");
$trustedUsers = array("Bobbzorzen");
$channels = array("#bobbzorzen"); //array('#bobbzorzen','#kungsmarken')
$prefix = NICK.": ";

include_once('/home/pi/pear/share/pear/Net/SmartIRC.php');

 

class Bot {
    function handleCommands(&$irc, &$data) {
        /**
         * @var string The command recived
         */
        global $prefix;
        $msgNoPrefix = substr($data->message, strlen($prefix));
        $splitMsg = explode(" ", $msgNoPrefix);
        $command = $splitMsg[0];

        $message = '';
        $this->debug(count($prefix));
        $this->debug($msgNoPrefix);
        $this->debug($splitMsg);
        $this->debug($command);
        
        /**
         * Sends a command confirmation to the channel notifying that command was recived.
         */
        //$this->channelMessage($irc, $data->channel, "I recived the command: $command, from: ".$data->nick);

        if($command == 'join') {
            global $trustedUsers;
            if(in_array($data->nick, $trustedUsers)) {
                //check if channel is suplied and is of the correct format
                $channel = (isset($splitMsg[1]) && (substr($splitMsg[1],0,1) == "#")) ? $splitMsg[1] : false;

                //if channel is correct then join and print feedback message. If not then print error message
                if($channel != false) {
                    $message = "Joining channel: $channel";
                    $irc->join(array($channel));
                } else {
                    $message = "Could not join channel because it was either not suplied or of an incorrect format.";
                }
            } else {
                $message = "I don't trust you!";
            }
            $this->channelMessage($irc, $data->channel, $message);
        }
            

        if($command == 'slap') {
            //Check if slapee was suplied
            $slapee = (isset($splitMsg[1])) ? $splitMsg[1] : false;

            //If slapee is valid
            if($slapee != false) {
                //$message = $data->nick .' slaps '. $slapee .' with a moist cod!';
                //$this->channelMessage($irc, $data->channel, $message);
                $this->performAction($irc, $data->channel, "Slaps $slapee with a moist cod!");
            }
        }
            
        if($command == 'part') {
            $this->channelMessage($irc, $data->channel, "KTHXBYE!");
            $irc->part(array($data->channel),"I do as i'm asked!");
        }

        if($command == 'opall') {
            $message = "op all";
            $this->privateMessage($irc,'chan', $message);
        }

        if($command == 'coc') {
            $message = "";
            if(isset($splitMsg[1])) {
                $id = $splitMsg[1];
                $url = "http://dbwebb.se/coc/?id=$id";
                // create curl resource 
                $ch = curl_init(); 

                // set url 
                curl_setopt($ch, CURLOPT_URL, $url); 

                //return the transfer as a string 
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

                // $output contains the output string 
                $output = curl_exec($ch);

                // close curl resource to free up system resources 
                curl_close($ch);   
                $jsonOutput = json_decode($output);
                if(isset($jsonOutput->id)) {
                    $message = $jsonOutput->name;
                } else {
                    $message = "Invalid coc id";
                }
            } else {
                $message = "Wrong format! Correct format: ". $prefix ."coc <cocId>";
            }
            $this->channelMessage($irc, $data->channel, $message);
        }

        if($command == 'github') {
            $message = "My insides are on display here: https://github.com/bobbzorzen/IrcBot";
            $this->channelMessage($irc,$data->channel, $message);
        }
    }


    function youtubeLister(&$irc, &$data) {
        $regex = "";
        if(preg_match("/youtu.be\/.{11}/", $data->message)) {
            $regex = "/youtu.be\/(.{11})/";
        } else {
            $regex = "/.*\?v=(.{11}).*/";
        }
        $id = preg_replace($regex,"$1",$data->message);
        $url = "https://www.googleapis.com/youtube/v3/videos?id=$id&key=". API ."&part=snippet";
        // create curl resource 
        $ch = curl_init(); 

        // set url 
        curl_setopt($ch, CURLOPT_URL, $url); 

        //return the transfer as a string 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

        // $output contains the output string 
        $output = curl_exec($ch);

        // close curl resource to free up system resources 
        curl_close($ch);   
        $jsonOutput = json_decode($output);

        $this->debug($data->message);
        $this->debug($id);
        $this->debug($url);
        $this->debug($jsonOutput);

        $sender = $data->nick;
        $title = $jsonOutput->items[0]->snippet->title;
        $message = "Yotube video: \"$title\"";
        $this->channelMessage($irc, $data->channel, $message);
    }

    /**
     * Prints welcome message to all who join
     * 
     * @param  irc Object     $irc Reference variable to the IRC object
     * @param  message Object $data Object containing message recived
     * @return void
     * 
     */
    function welcomeMessage(&$irc, &$data) {
        $nick = $data->nick;
        $channel = $data->channel;
        if($nick != NICK) {
            $message = "Welcome to $channel, $nick!";
            $this->channelMessage($irc, $channel, $message);
        }
    }


    /**
     * Prints data to console
     * 
     * @param  mixed $data Data to be printed
     * @return void
     * 
     */
    function debug($data) {
        echo "\n\n=====================================================\n";
        print_r($data);
        echo "\n=====================================================\n\n";
    }

 
    /**
     * Sends a private message to $reciver
     * 
     * @param  irc Object $irc     Reference variable to the IRC object
     * @param  string     $reciver The person who will recive the message
     * @param  string     $message The message to be sent
     * @return void
     * 
     */
    function privateMessage(&$irc, $reciver, $message) {
        // result is send to #smartirc-test (we don't want to spam #test)
        $irc->message(SMARTIRC_TYPE_QUERY, $reciver, $message);
    }

    /**
     * Sends a channelmessage to $channel
     * 
     * @param  irc Object $irc     Reference variable to the IRC object
     * @param  string     $channel The channel who will recive the message
     * @param  string     $message The message to be sent
     * @return void
     * 
     */
    function channelMessage(&$irc, $channel, $message) {
        // result is send to #smartirc-test (we don't want to spam #test)
        $irc->message(SMARTIRC_TYPE_CHANNEL, $channel, $message);
    }

    /**
     * Execute an action(/me)
     * 
     * @param  irc Object $irc     Reference variable to the IRC object
     * @param  string     $channel The channel where the action will take palce
     * @param  string     $action The action to perform
     * @return void
     * 
     */
    function performAction(&$irc, $channel, $action) {
        $irc->message(SMARTIRC_TYPE_ACTION, $channel, $action);
    }
}

 

$bot = &new Bot();

$irc = &new Net_SmartIRC();

$irc->setDebug(SMARTIRC_DEBUG_ALL);

$irc->setUseSockets(TRUE);

$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^'.$prefix, $bot, 'handleCommands');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^.*youtube.com\/watch\?v.*$', $bot, 'youtubeLister');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^.*youtu.be\/.*$', $bot, 'youtubeLister');
$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $bot, 'welcomeMessage');

$irc->connect(SERVER, 6667);

$irc->login(NICK, 'bobba bot (bobbzorzen.php)', 0, NICK, PASS);

$irc->message(SMARTIRC_TYPE_QUERY, 'nick', 'IDENTIFY '.PASS);
$irc->message(SMARTIRC_TYPE_QUERY, 'chan', 'invite #kungsmarken bobbabot');

$irc->join($channels);

$irc->listen();

$irc->disconnect();

?>