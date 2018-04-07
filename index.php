<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>CoinsMonitor</title>
		<link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
		<link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
		<link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
		<link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
		<link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
		<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap.min.css">
	<style type="text/css" class="init">

	</style>


	<script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.12.4.js">
	</script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js">
	</script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js">
	</script>

	<script>
		$(document).ready(function() {
		$('#output').DataTable();
		} );
	</script>
</head>
<body style="padding:10px">
<table id="output" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
            <tr>
				<th>Open date</th>
				<th>USD buy rate</th>
				<th>USD current rate</th>
				<th>USD ratio</th>
				<th>Market</th>
				<th>Buy lot price (in BTC)</th>
                <th title = 'in BTC'>Current lot price</th>
                <th title = 'in BTC'>Desired lot <price></price></th>
				<th title ='shows how much of orrginal price has been filled'>Current position</th>
				<th>Goal completion</th>
				<th>Expected profit </th>
				<th title = 'in USD'>Expected profit  </th>
				<th title='data from market cap'>Weekly changes</th>

               </tr>
        </thead>
        <tfoot>
            <tr>
				<th>Open date</th>
				<th>USD buy rate</th>
				<th>USD current rate</th>
				<th>USD ratio</th>
				<th>Market</th>
				<th>Buy lot price (in BTC) </th>
				<th>Current lot price (in BTC)</th>
                <th>Desired lot price (in BTC)</th>
				<th title ='shows how much of orrginal price has been filled'>Current position</th>
				<th>Goal completion</th>
				<th>Expected profit</th>
				<th>Expected profit (in USD)</th>
				<th title='data from market cap'>Weekly changes</th>

            </tr>
        </tfoot>


 <tbody>
 <?
$config = fopen('config.json', 'r') or die("Unable to open config.json!");
$apicred = fread($config, filesize('config.json'));
fclose($config);
$apicred = json_decode($apicred, true);

$apikey = $apicred['apikey'];
$apisecret = $apicred['apisecret'];

function bittrexbalance($apikey, $apisecret){
    $nonce=time();
   #$uri='http://bittbot.bot/walletdata';
	$uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&nonce='.$nonce;

    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
   return $obj["result"];
}


function openorders($apikey, $apisecret){
    $nonce=time();
	$uri='https://bittrex.com/api/v1.1/market/getopenorders?apikey='.$apikey.'&nonce='.$nonce;
	#$uri='http://bittbot.bot/ordersdata';
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult,true);
   return $obj["result"];
}
function gethistory($apikey, $apisecret,$market){
    $nonce=time();
	$uri = 'https://bittrex.com/api/v1.1/account/getorderhistory?apikey='.$apikey.'&nonce='.$nonce.'&market='.$market ;
	#$uri='http://bittbot.bot/ordersdata';
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult,true);


	for($i=0;$i<sizeof($obj['result']);$i++){
		if ($obj['result'][$i]['OrderType'] === "LIMIT_BUY"){
			return $obj['result'][$i];
		}
	}

}



$marketcapsticker = 'https://api.coinmarketcap.com/v1/ticker/?limit=500';
$getcont = json_decode(file_get_contents($marketcapsticker), true);

function marketcap($coin){
	for ($i=0;$i<500; $i++){
		global $getcont;
		if($getcont[$i]["symbol"] == $coin){
			$percent_change_7d = $getcont[$i]["percent_change_7d"];
			$percent_change_24h = $getcont[$i]["percent_change_24h"];
			$percent_change_1h = $getcont[$i]["percent_change_1h"];
			$getrank = $getcont[$i]["rank"];
			$changes = array($percent_change_7d, $percent_change_24h, $percent_change_1h,$getrank);
			return $changes;
		}
	}
}

function getsummuries(){
   $uri = 'https://bittrex.com/api/v1.1/public/getmarketsummaries';
	#$uri = 'http://bittbot.bot/getmarketsummaries';
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
	return $obj['result'];
	}

$summuries = getsummuries();

function getcoinprice($market){
	global $summuries;
	for($i=0;$i<sizeof($summuries);$i++){
		if ( $summuries[$i]['MarketName'] === $market )
				return $summuries[$i]['Last'];
	}
}


$wallets =bittrexbalance($apikey, $apisecret);
$openorders = openorders($apikey, $apisecret);

function division($a, $b) {
    if($b == 0)
      return null;

    return $a/$b;
}
$boughtforttl=0;
$currentttl =0;
$expectedprofitttl=0;

function usddata(){
	$uri = 'https://bittrex.com/Api/v2.0/pub/market/GetTicks?marketName=USDT-BTC&tickInterval=thirtyMin';
	#$uri='http://bittbot.bot/GetTicksusdt';
	$ch = curl_init($uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    return $obj;
}

$usddata = usddata();
function usdtTimeRate($time){
	global $usddata;
    $time = substr($time,0, 14)."00:00";
	for($i=0;$i<count($usddata['result']);$i++){
		if ($usddata['result'][$i]['T'] == $time)
			return $usddata['result'][$i]['C'];
		}
}

$lastone = count($usddata['result'])-1;
$currentusdrate = $usddata['result'][$lastone]['C'];


for ($i=0;$i<sizeof($openorders);$i++){
	$market = $openorders[$i]['Exchange'];
	$amount = $openorders[$i]['QuantityRemaining'];
	$ask = $openorders[$i]["Limit"];
	$desired = $amount*$ask*0.9975;
		$desired=  round ($desired, 8);
	$lastbid =getcoinprice($market);
	$currentprice = $lastbid*$amount;
		$currentprice=  round ($currentprice, 8);
	$completion = round(division($currentprice, $desired), 3)*100;
	$history = gethistory($apikey, $apisecret,$market);
	$buyprice = $history['Price']*1.0025;
		$buyprice = round ($buyprice, 8);
		$buybid = $history['Limit'];
	$opendate  = $history['Closed'];
	$expectedprofit = (division($desired, $buyprice)-1)*100;
		$expectedprofit = round($expectedprofit, 1);
	$expectedprofitusd = ($desired-$buyprice)*getcoinprice('USDT-BTC')*0.9975;
		$expectedprofitusd = round($expectedprofitusd, 1);
	$position = round(division($currentprice, $buyprice)*99.75, 1);#ration bought to current
	if (preg_match('/USDT/', $market) == false){
		$boughtforttl+=$buyprice;
		$currentttl +=$currentprice;
		$expectedprofitttl+=$desired;
		$coin = substr($market, 4);
		$changes = marketcap($coin);
		$buyusdrate = usdtTimeRate($history['Closed']);
		$usdbuyprice = round($buyusdrate*$buyprice, 1);
		$usdcurrentprice = round($currentprice*$currentusdrate, 1);
		$usdratio  = division($usdcurrentprice, $usdbuyprice);


			echo "<tr>
				<td>$opendate</td>
				<td title='$buyusdrate'>$usdbuyprice</td>
				<td title ='$currentusdrate'>$usdcurrentprice</td>
				<td title ='current/buy'>$usdratio</td>
				<td><a title='Go to Bittrex' $market' href='https://bittrex.com/Market/Index?MarketName=$market'>$market</a></td>
				<td title='($buybid) including fee'>$buyprice</td>
                <td title='$lastbid'><a title='Fullscreen chart' $market' href='https://bittrex.com/market/marketStandardChart?MarketName=$market'>$currentprice</a></td>
                <td>$desired</td>
                <td title ='Including fee '>$position%</td>
                <td>$completion%</td>
                <td>$expectedprofit%</td>
                <td title='fee has been deducted'>$$expectedprofitusd</td>
                <td ><img src='https://files.coinmarketcap.com/generated/sparklines/$changes[3].png'></td>

			</tr>";

 	}
}
			echo "<div title='totals might be incorrect' class='well'><ul class='list-group'>
						<li title='All these lots' class='list-group-item list-group-item-success'>Total in:<strong> $boughtforttl </strong></li>
						<li title='All these lots' class='list-group-item list-group-item-info'>Current total: <strong>$currentttl</strong></li>
						<li title='All these lots' class='list-group-item list-group-item-danger'>Expected total: <strong>$expectedprofitttl</strong></li>
					</ul></div>";



?>


        </tbody>
    </table>
</body>