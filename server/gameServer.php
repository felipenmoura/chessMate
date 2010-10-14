<?php
    session_start();
    date_default_timezone_set("America/Sao_Paulo");
    error_reporting(E_ALL);

    // verifying for new moves
    if(isset($_SESSION['gameId']) && isset($_POST['verifying']) && trim($_SESSION['gameId'])!='')
    {
        echo ">:";
        if(!file_exists('games/'.$_SESSION['gameId'].'.json'))
        {
            echo "###: This game has finished";
            exit;
        }
        $obj= json_decode(file_get_contents('games/'.$_SESSION['gameId'].'.json'));
        if($obj->oponent && (!isset($_SESSION['oponent']) || trim($_SESSION['oponent']) =='' || $_SESSION['oponent']!= $obj->oponent) && $obj->owner == $_SESSION['userName'])
        {
            $_SESSION['oponent']= $obj->oponent;
            echo "User ".$obj->oponent." has just got into this game to play with you.\n";
            exit;
        }
        if(sizeof($obj->moves) >0)
        {
           //print_r($obj->moves);
           for($i=0, $j=sizeof($obj->moves); $i<$j; $i++)
           {
               $mvt= JSON_decode(stripslashes($obj->moves[$i]));
              // echo stripslashes($obj->moves[$i]);
               if($mvt->color != $_SESSION['color'])
               {
               	   if($mvt->color == 'black')
	                   echo "MovimentReturn:".$mvt->color."|".$mvt->piece."|".$mvt->to."|".$mvt->el."|".$obj->oponent."\n";
	               else
		               echo "MovimentReturn:".$mvt->color."|".$mvt->piece."|".$mvt->to."|".$mvt->el."|".$obj->owner."\n";
                   array_shift($obj->moves);
                   file_put_contents('games/'.$_SESSION['gameId'].'.json', JSON_encode($obj));
                   exit;
               }
           }
        }
        if(sizeof($obj->messages) >0)
        {
           //echo "MessageReturn: ";
            $ret= "";
           for($i=0, $j=sizeof($obj->messages); $i<$j; $i++)
           {
               if(isset($obj->messages[$i]) && $obj->messages[$i][0] != $_SESSION['userName'])
               {
                   $ret.= $obj->messages[$i][0]." said: ".$obj->messages[$i][1]."\n";
                   array_shift($obj->messages);
               }
           }
           if(strlen($ret) > 0)
               echo "MessageReturn: ".$ret;
           file_put_contents('games/'.$_SESSION['gameId'].'.json', JSON_encode($obj));
        }
        //echo "\n";
        exit;
    }

    if(isset($_POST['moviment']) && isset($_SESSION['gameId']))
    {
        $obj= JSON_decode(file_get_contents('games/'.$_SESSION['gameId'].'.json'));
        $obj->moves[]= stripslashes($_POST['moviment']);
        $mvt= JSON_decode(stripslashes($_POST['moviment']));
        if(file_put_contents('games/'.$_SESSION['gameId'].'.json', JSON_encode($obj)))
           echo ">: MovimentReturn ".$mvt->color."|".$mvt->piece."|".$mvt->to."|".$mvt->el."\n";
        else
           echo ">Error";
        exit;
    }

    if(isset($_SESSION['gameId']) && isset($_POST['message']))
    {
        $obj= JSON_decode(file_get_contents('games/'.$_SESSION['gameId'].'.json'));
        $obj->messages[]= Array($_SESSION['userName'], strip_tags($_POST['message']));
        if(file_put_contents('games/'.$_SESSION['gameId'].'.json', JSON_encode($obj)))
           echo ">OK";
        else
           echo ">Error";
        exit;
    }

    // starting a new game
    if(!isset($_POST['verifying']) && !isset($_POST['message']))
    {
	    $gameId= '';
    	if(isset($_POST['gameId']) && trim($_POST['gameId']) != '')
    		$gameId= $_POST['gameId'];
    	else
    		$gameId= time() - 1286000000;
        if(!file_exists('games/'.$gameId.'.json'))
        {
            $_SESSION['userName']= $_POST['userName'];
            $_SESSION['color']= 'white';
            echo ">: Creating the new game.\n Welcome ".$_POST['userName']."(<b style='color:white;'>white</b>)\nwaiting for the oponent.\nThe key for this game is <b>".$gameId."</b>";
            /*if(!isset($_POST['gameId']) || trim($_POST['gameId']) == '')
	        	echo "<script>alert('A new game has been created with the ID= ".$gameId." Your friend can use this id to get into this game and play with you.');</script>";
        	else{
    	    	echo "<script>alert('This game does not exist. Pleasy, verify with your friend, who started the game, if the key is correct')</script>";
    	    	exit;
    	    }*/
            $_SESSION['oponent']= false;
            $f= fopen('games/'.$gameId.'.json', 'w+');
            $obj= Array();
            $obj['owner']= $_SESSION['userName'];
            $obj['oponent']= false;
            $obj['startAt']= date('d/m/Y - H:i:s');
            $obj['lastMove']= time();
            $obj['moves']= Array();
            $obj['messages']= Array();
            fputs($f, JSON_encode($obj));
            fclose($f);
            $_SESSION['gameId']= $gameId;
        }else{
            $_SESSION['userName']= $_POST['userName'];
            $obj= json_decode(file_get_contents('games/'.$gameId.'.json', 'w+'));
            if(!$obj->oponent && $obj->owner != $_SESSION['userName'])
            {
                $_SESSION['color']= 'black';
                $obj->oponent= $_SESSION['userName'];
                file_put_contents('games/'.$gameId.'.json', JSON_encode($obj));
                echo ">: Entering in the game with ".$obj->owner."\n>: Welcome ".$_SESSION['userName']." (<b>black</b>)\n";
                $_SESSION['gameId']= $gameId;
            }else{
                echo "###: Sorry, this game is already running and you cannot play on it.";
                exit;
            }
        }
    }
