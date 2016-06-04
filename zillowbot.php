<?php

/*
CREDITS

Used David McCreath's IsItUp slackbot(https://github.com/mccreath/isitup-for-slack) as a skeleton. Thanks David!

REQUIREMENTS

* A custom slash command on a Slack team
* A web server running PHP5 with cURL enabled
* A Zillow Web Service Identifier (http://www.zillow.com/howto/api/APIOverview.htm)

USAGE

This custom slash command will provide mortgage rates, monthly payments, and monthly insurance given a price and a percentage down payment.

Sample input in Slack:
/zillow 400000 15

Output:
30 Year Fixed: Rate 3.389% | Monthly Principle & Interest $1505 | Mortgage Insurance $124
15 Year Fixed: Rate 2.658% | Monthly Principle & Interest $2292 | Mortgage Insurance $124
5/1 Adjustable: Rate 2.726% | Monthly Principle & Interest $1383 | Mortgage Insurance $192

*/

# Grab some of the values from the slash command, create vars for post back to Slack
$command = $_POST['command'];
$text = $_POST['text'];
$token = $_POST['token'];
$zws_id = "ENTER ZILLOW WEB SERVICE IDENTIFIER HERE"; # Zillow Web Service Identifier

# Check the token and make sure the request is from our team 
if($token != 'ENTER SLACK TOKEN HERE'){ #replace this with the token from your slash command configuration page
  $msg = "The token for the slash command doesn't match. Check your script.";
  die($msg);
  echo $msg;
}

# Take in user's input (price and down payment percentage) and build the Zillow API URL
$input = explode(" ", $text);
$mortgage_estimator = "http://www.zillow.com/webservice/GetMonthlyPayments.htm?zws-id=".$zws_id."&price=".$input[0]."&down=".$input[1]."&output=json";

# Set up cURL 
$ch = curl_init($mortgage_estimator);

# Set up options for cURL 
# We want to get the value back from our query 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

# Make the call and get the response 
$ch_response = curl_exec($ch);

# Close the connection 
curl_close($ch);

#If an error is returned from Zillow, the response will be sent in XML instead of the requested JSON. If XML, parse so we can find out which error code was thrown.
if(substr($ch_response, 0, 5) == "<?xml") {
    $isxml = True;
    $xml = simplexml_load_string($ch_response, "SimpleXMLElement", LIBXML_NOCDATA);
    $json = json_encode($xml);
    $response_array = json_decode($json,TRUE);
} else {
    # Decode the JSON array sent back by Zillow
    $response_array = json_decode($ch_response,true);
}

# Build our response 
if($ch_response === FALSE){
  # Zillow could not be reached 
  $reply = "Sorry, Zillow could not be reached. Please try again in a few moments.";
}else{
  } if($isxml){ 
        if ($response_array["message"]["code"] == 500) { # Invalid Price
            $reply = "Sorry, the price you entered is invalid. Check to make sure you entered a valid integer.";
        } else if ($response_array["message"]["code"] == 502){ # Invalid Down Payment Percentage
            $reply = "Sorry, the down payment percentage you entered is invalid. Check to make sure you entered a valid integer.";
        } else { #Catch all
            $reply = "Sorry, your request could not be completed. Please try again.";
        }
  } else{
  	# Request Processed
    $reply = "30 Year Fixed: *Rate* ".$response_array["response"]["thirtyYearFixed"]["rate"]."% | *Monthly Principle & Interest* $".$response_array["response"]["thirtyYearFixed"]["monthlyPrincipalAndInterest"]." | *Mortgage Insurance* $".$response_array["response"]["thirtyYearFixed"]["monthlyMortgageInsurance"]."\r\n";
    $reply .= "15 Year Fixed: *Rate* ".$response_array["response"]["fifteenYearFixed"]["rate"]."% | *Monthly Principle & Interest* $".$response_array["response"]["fifteenYearFixed"]["monthlyPrincipalAndInterest"]." | *Mortgage Insurance* $".$response_array["response"]["fifteenYearFixed"]["monthlyMortgageInsurance"]."\r\n";
    $reply .= "5/1 Adjustable: *Rate* ".$response_array["response"]["fiveOneARM"]["rate"]."% | *Monthly Principle & Interest* $".$response_array["response"]["fiveOneARM"]["monthlyPrincipalAndInterest"]." | *Mortgage Insurance* $".$response_array["response"]["fiveOneARM"]["monthlyMortgageInsurance"]."\r\n";
}

# Send the reply back to the user. 
echo $reply;
