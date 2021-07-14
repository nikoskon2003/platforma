<?php
require __DIR__ . '/vendor/autoload.php';
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

function sendNotification($users, $message, $openDir = null){

    require __DIR__ . '/../dbh.inc.php';
    require __DIR__ . '/../config.php';
    
    $auth = array(
        'VAPID' => array(
            'subject' => 'mailto:' . $contactEmail,
            'publicKey' => $publicKey,
            'privateKey' => $privateKey
        )
    );

    $payload = $message;
    if(!is_null($openDir))
        $payload = 'o^*' . $openDir . '^*' . $message;
    
	$endpointsSent = [];
	
    if(!isset($users)){
        $webPush = new WebPush($auth);

        $res = mysqli_query($conn, "SELECT * FROM notif_subs");
        while($row = $res->fetch_assoc()){
            $UEndpoint = $row['subscription_endpoint'];
			
			if(in_array($UEndpoint, $endpointsSent)) continue;
			$endpointsSent[] = $UEndpoint;
			
            $UPublicKey = $row['subscription_publickey'];
            $UAuthToken = $row['subscription_authtoken'];
            $sub = Subscription::create(["endpoint" => $UEndpoint, "publicKey" => $UPublicKey, "authToken" => $UAuthToken]);
            
            $webPush->sendNotification($sub, $payload);
        }

        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();

            $endpoint = mysqli_real_escape_string($conn, $endpoint);
        
            if (!$report->isSuccess())
                mysqli_query($conn, "DELETE FROM notif_subs WHERE subscription_endpoint='$endpoint'");  
        }

    }
    elseif(is_array($users)){
        foreach($users as $user){
            if(is_string($user)){
                $user = mysqli_real_escape_string($conn, $user);

                $webPush = new WebPush($auth);

                $res = mysqli_query($conn, "SELECT * FROM notif_subs WHERE subscription_username='$user'");
                while($row = $res->fetch_assoc()){
                    $UEndpoint = $row['subscription_endpoint'];
					
					if(in_array($UEndpoint, $endpointsSent)) continue;
					$endpointsSent[] = $UEndpoint;
					
                    $UPublicKey = $row['subscription_publickey'];
                    $UAuthToken = $row['subscription_authtoken'];
                    $sub = Subscription::create(["endpoint" => $UEndpoint, "publicKey" => $UPublicKey, "authToken" => $UAuthToken]);
                    
                    $webPush->sendNotification($sub, $payload);
                }

                foreach ($webPush->flush() as $report) {
                    $endpoint = $report->getRequest()->getUri()->__toString();
        
                    $endpoint = mysqli_real_escape_string($conn, $endpoint);
                
                    if (!$report->isSuccess())
                        mysqli_query($conn, "DELETE FROM notif_subs WHERE subscription_endpoint='$endpoint'");  
                }
            }
        }
    }
    elseif(is_string($users)){
        $user = mysqli_real_escape_string($conn, $users);

        $webPush = new WebPush($auth);
        
        $res = mysqli_query($conn, "SELECT * FROM notif_subs WHERE subscription_username='$user'");
        while($row = $res->fetch_assoc()){
            $UEndpoint = $row['subscription_endpoint'];
			
			if(in_array($UEndpoint, $endpointsSent)) continue;
			$endpointsSent[] = $UEndpoint;
			
            $UPublicKey = $row['subscription_publickey'];
            $UAuthToken = $row['subscription_authtoken'];
            $sub = Subscription::create(["endpoint" => $UEndpoint, "publicKey" => $UPublicKey, "authToken" => $UAuthToken]);
            
            $webPush->sendNotification($sub, $payload);
        }

        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();

            $endpoint = mysqli_real_escape_string($conn, $endpoint);
        
            if (!$report->isSuccess())
                mysqli_query($conn, "DELETE FROM notif_subs WHERE subscription_endpoint='$endpoint'");  
        }       
    }
}