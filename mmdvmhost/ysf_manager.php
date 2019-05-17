<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code

// Load the ysfgateway config file
$ysfgatewayConfigFile = '/etc/ysfgateway';
$configysfgateway = parse_ini_file($ysfgatewayConfigFile, true);

if (!empty($_POST) && isset($_POST["ysfHostSubmit"])):
    if (empty($_POST['ysfStartupHost']) != TRUE ) {
	$newYSFStartupHostArr = explode(',', escapeshellcmd($_POST['ysfStartupHost']));
	if (isset($configysfgateway['FCS Network'])) {
	    if ($newYSFStartupHostArr[0] == "none")
	    {
		unset($configysfgateway['Network']['Startup']);
	    }
	    else {
		$configysfgateway['Network']['Startup'] = $newYSFStartupHostArr[1];
	    }
	}
	else {
	    if ($newYSFStartupHostArr[0] == "none")
	    {
		unset($configysfgateway['Network']['Startup']);
	    }
	    else {
		$configysfgateway['Network']['Startup'] = $newYSFStartupHostArr[0];
	    }
	}
	
        // ysfgateway config file wrangling
	$ysfgwContent = "";
        foreach($configysfgateway as $ysfgwSection=>$ysfgwValues) {
            // UnBreak special cases
            $ysfgwSection = str_replace("_", " ", $ysfgwSection);
            $ysfgwContent .= "[".$ysfgwSection."]\n";
            // append the values
            foreach($ysfgwValues as $ysfgwKey=>$ysfgwValue) {
                $ysfgwContent .= $ysfgwKey."=".$ysfgwValue."\n";
            }
            $ysfgwContent .= "\n";
        }
	
        if (!$handleYSFGWconfig = fopen('/tmp/eXNmZ2F0ZXdheQ.tmp', 'w')) {
            return false;
        }
	
	system('sudo systemctl stop ysf2p25.service > /dev/null 2>/dev/null &');		// YSF2P25
	system('sudo systemctl stop ysf2nxdn.service > /dev/null 2>/dev/null &');		// YSF2NXDN
	system('sudo systemctl stop ysf2dmr.service > /dev/null 2>/dev/null &');		// YSF2DMR
	system('sudo systemctl stop ysfgateway.service > /dev/null 2>/dev/null &');		// YSFGateway

	if (!is_writable('/tmp/eXNmZ2F0ZXdheQ.tmp')) {
            echo "<br />\n";
            echo "<table>\n";
            echo "<tr><th>ERROR</th></tr>\n";
            echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
            echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
            echo "</table>\n";
            unset($_POST);
            echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
            die();
	}
	else {
	    $success = fwrite($handleYSFGWconfig, $ysfgwContent);
	    fclose($handleYSFGWconfig);
	    if (intval(exec('cat /tmp/eXNmZ2F0ZXdheQ.tmp | wc -l')) > 35 ) {
		exec('sudo mv /tmp/eXNmZ2F0ZXdheQ.tmp /etc/ysfgateway');		// Move the file back
		exec('sudo chmod 644 /etc/ysfgateway');					// Set the correct runtime permissions
		exec('sudo chown root:root /etc/ysfgateway');				// Set the owner
	    }

	    system('sudo systemctl start ysfgateway.service > /dev/null 2>/dev/null &');	// YSFGateway
	    system('sudo systemctl start ysf2dmr.service > /dev/null 2>/dev/null &');		// YSF2DMR
	    system('sudo systemctl start ysf2nxdn.service > /dev/null 2>/dev/null &');		// YSF2NXDN
	    system('sudo systemctl start ysf2p25.service > /dev/null 2>/dev/null &');		// YSF2P25
	}
    }
    unset($_POST);
    echo '<b>Yaesu System Fusion Manager</b>'."\n";
    echo '<table>\n<tr><th>Processing</th></tr>'."\n";
    echo '<tr><td>Restarting YSF services...</td></tr>\n</table>'."\n";
    echo "<br />\n";
    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
else: ?>
    <b>Yaesu System Fusion Manager</b>
    <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
	<table>
	    <tr>
		<th width="150"><a class="tooltip" href="#">YSF Host<span><b>YSF Host</b></span></a></th>
		<th><a class="tooltip" href="#">Action<span><b>Action</b></span></a></th>
	    </tr>
	    <tr>
		<td>
		    <select name="ysfStartupHost">
			<?php
			if (isset($configysfgateway['Network']['Startup'])) {
			    $testYSFHost = $configysfgateway['Network']['Startup'];
			    echo "      <option value=\"none\">None</option>\n";
        		}
			else {
			    $testYSFHost = "none";
			    echo "      <option value=\"none\" selected=\"selected\">None</option>\n";
    			}
			
			if ($testYSFHost == "ZZ Parrot")  {
			    echo "      <option value=\"00001,ZZ Parrot\" selected=\"selected\">YSF00001 - Parrot</option>\n";
			}
			else {
			    echo "      <option value=\"00001,ZZ Parrot\">YSF00001 - Parrot</option>\n";
			}
			
			if ($testYSFHost == "YSF2DMR")  {
			    echo "      <option value=\"00002,YSF2DMR\"  selected=\"selected\">YSF00002 - Link YSF2DMR</option>\n";
			}
			else {
			    echo "      <option value=\"00002,YSF2DMR\">YSF00002 - Link YSF2DMR</option>\n";
			}
			
			if ($testYSFHost == "YSF2NXDN") {
			    echo "      <option value=\"00003,YSF2NXDN\" selected=\"selected\">YSF00003 - Link YSF2NXDN</option>\n";
			}
			else {
			    echo "      <option value=\"00003,YSF2NXDN\">YSF00003 - Link YSF2NXDN</option>\n";
			}
			
			if ($testYSFHost == "YSF2P25")  {
			    echo "      <option value=\"00004,YSF2P25\"  selected=\"selected\">YSF00004 - Link YSF2P25</option>\n";
			}
			else {
			    echo "      <option value=\"00004,YSF2P25\">YSF00004 - Link YSF2P25</option>\n";
			}
			
			if (file_exists("/usr/local/etc/YSFHosts.txt")) {
			    $ysfHosts = fopen("/usr/local/etc/YSFHosts.txt", "r");
			    while (!feof($ysfHosts)) {
				$ysfHostsLine = fgets($ysfHosts);
				$ysfHost = preg_split('/;/', $ysfHostsLine);
				
				if ((strpos($ysfHost[0], '#') === FALSE ) && ($ysfHost[0] != '')) {
				    if ( ($testYSFHost == $ysfHost[0]) || ($testYSFHost == $ysfHost[1]) ) {
					echo "      <option value=\"$ysfHost[0],$ysfHost[1]\" selected=\"selected\">YSF$ysfHost[0] - ".htmlspecialchars($ysfHost[1])." - ".htmlspecialchars($ysfHost[2])."</option>\n";
				    }
				    else {
					echo "      <option value=\"$ysfHost[0],$ysfHost[1]\">YSF$ysfHost[0] - ".htmlspecialchars($ysfHost[1])." - ".htmlspecialchars($ysfHost[2])."</option>\n";
				    }
				}
			    }
			    fclose($ysfHosts);
			}
			
			if (file_exists("/usr/local/etc/FCSHosts.txt")) {
			    $fcsHosts = fopen("/usr/local/etc/FCSHosts.txt", "r");
			    while (!feof($fcsHosts)) {
				$ysfHostsLine = fgets($fcsHosts);
				$ysfHost = preg_split('/;/', $ysfHostsLine);
				if ((strpos($ysfHost[0], '#') === FALSE ) && ($ysfHost[0] != '')) {
                                    if ( ($testYSFHost == $ysfHost[0]) || ($testYSFHost == $ysfHost[1]) )
				    {
					echo "      <option value=\"$ysfHost[0],$ysfHost[0]\" selected=\"selected\">$ysfHost[0] - ".htmlspecialchars($ysfHost[1])."</option>\n";
				    }
                                    else {
					echo "      <option value=\"$ysfHost[0],$ysfHost[0]\">$ysfHost[0] - ".htmlspecialchars($ysfHost[1])."</option>\n";
				    }
				}
			    }
			    fclose($fcsHosts);
			}
			?>
		    </select>
		</td>
		<td>
		    <input type="submit" name="ysfHostSubmit" value="Request Change" />
		</td>
	    </tr>
	</table>
    </form>
<?php endif; ?>
