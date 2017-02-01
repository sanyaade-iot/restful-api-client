<?php
/*
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


include_once 'SuplaCloudClient.php';

$result = FALSE;
$scc = new SuplaCloudClient(array('server' => 'devel-cloud.supla.org',
		                    'clientId' => '31_255p10f3xf404s8swsg08400kw84kc8o4cocco48o840ccgkgo',
		                    'secret' => '1fgmr1v3wbwgwcck8cos4og8cw8w0oosc8w8ckkgc8co840044',
		                    'username' => 'api_5',
		                    'password' => '',
));

$scc->setDebug(true);
//$scc->setToken('O:8:"stdClass":5:{s:12:"access_token";s:86:"ZDIzNjA1Zjg0ZWEzYjI0NTUxOGFlZDBhMTM3ZWY1MGJiMzIxNmE2ZjBiYTIyOGYwN2FiM2IwNGM3MTA3NzU0Mw";s:10:"expires_in";i:1479915417;s:10:"token_type";s:6:"bearer";s:5:"scope";s:7:"restapi";s:13:"refresh_token";s:86:"OGNiOGMxOTBjODQ0ZTYyZDg0ZDgwNGFhMTBjYjQxZGU1NjA1N2Q0NWRiNDAxODAyMTAyODEzZWNkNTQzMWZjZQ";}');



//$result = $scc->getServerInfo();
//$result = $scc->locations();
//$result = $scc->accessIDs();
//$result = $scc->ioDevices();
//$result = $scc->device_isEnabled(1);
//$result = $scc->device_isConnected(1);
//$result = $scc->temperatureLog_ItemCount(2334);
//$result = $scc->temperatureLog_GetItems(2334);
//$result = $scc->temperatureAndHumidityLog_ItemCount(5);
//$result = $scc->temperatureAndHumidityLog_GetItems(5);
//$result = $scc->channelValue_On(6);
//$result = $scc->channelValue_Hi(2395);
//$result = $scc->channelValue_Humidity(5);
//$result = $scc->channelValue_Temperature(5);
//$result = $scc->channelValue_TemperatureAndHumidity(5);
//$result = $scc->channelValue_RGBW(1);
//$result = $scc->channelValue_Color(1);
//$result = $scc->channelValue_ColorBrightness(1);
//$result = $scc->channelValue_Brightness(1);
//$result = $scc->channelValue_Distance(4);
//$result = $scc->channelValue_Depth(4);

if ( false === $result ) {
	echo "------ Error ------\n";
	var_dump($scc->getLastError());
} else {
	echo "------ Success ------\n";
	var_dump($result);
}