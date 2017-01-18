<?php
// This file is part of Stack - http://stack.maths.ed.ac.uk/
//
// Stack is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Stack is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Stack.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

/**
 * Prototype GAP answer test.
 *
 * @copyright  2017 University of Edinburgh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stack_anstest_gap extends stack_anstest {

    public function do_test() {
        $sa = $this->sanskey;
        $ta = $this->tanskey;

        $result = $this->ValidateBySCSCP ( $this->sanskey, $this->tanskey, 
                             'GeneratingSameGroup', 'localhost', 26133 ){

        if ($result) {
            $this->atmark = 1;
            return true;
        } else {
            $this->atmark = 0;
            return false;
        }
    }

    public function process_atoptions() {
        return false;
    }

    public function validate_atoptions($opt) {
        return array(true, '');
    }

    protected function get_casfunction() {
        return 'ATGap';
    }

###########################################################################
#
# PingSCSCPservice( server, port )
#
function PingSCSCPservice ( $server, $port ){
$socket=fsockopen( $server, $port );
if ($socket == false ) {
    echo 'Can not establish connection to '.$server.':'.$port."\n";
    return false;
} else {
    fclose($socket);
    echo 'Can establish connection to '.$server.':'.$port."\n";
    return true;
}
}

###########################################################################
#
# ParseSCSCPresult( data );
# 
function ParseSCSCPresult( $string ){
$xml = simplexml_load_string($string, 'SimpleXMLElement'); 
print_r($xml->OMATTR->OMA); 
$result = (string) $xml->OMATTR->OMA->OMA->OMS[1]['name']; 
$hint = (string) $xml->OMATTR->OMA->OMA->OMSTR; 
return [ $result, $hint ];
}


###########################################################################
#
# ComposeSCSCPcall( command, arg, cd=scscp_transient_1 );
# 
function ComposeSCSCPcall ( $command, $arg, $cd='scscp_transient_1' ){

$call_id = substr(MD5(microtime()), 0, 10);

$str = "<?scscp start ?>\n<OMOBJ><OMATTR><OMATP><OMS cd=\"scscp1\" name=\"call_id\"/><OMSTR>".$call_id."</OMSTR><OMS cd=\"scscp1\" name=\"option_return_object\"/><OMSTR></OMSTR></OMATP><OMA><OMS cd=\"scscp1\" name=\"procedure_call\"/><OMA><OMS cd=\"".$cd."\" name=\"".$command."\"/>".$arg."</OMA></OMA></OMATTR></OMOBJ>\n<?scscp end ?>\n";

return $str;

}

    
###########################################################################
#
# EvaluateBySCSCP( command, arg, server, port, cd=scscp_transient_1 );
# 
function EvaluateBySCSCP ( $command, $arg, $server, $port, $cd='scscp_transient_1' ){

# open socket connection

$socket=fsockopen( $server, $port);

# read SCSCP connection initiation message

$data = fread($socket, 4096);
if($data !== "") 
  echo '### Received connection initiation message '.$data."\n";

# respond with the protocol version 

fwrite($socket, "<?scscp version=\"1.3\" ?>\n");  

# get back agreed protocol version

$data = fread($socket, 4096);
if($data !== "") 
  echo '### Agreed protocol version '.$data."\n";

# assemble and send SCSCP procedure call
  
$str = ComposeSCSCPcall( $command, $arg, $cd );

echo "### Sending procedure call \n\n";

echo $str;
	
fwrite($socket, $str);

# get the reply 

echo "\n### Receiving result \n\n";

$data = '';

# start reading OpenMath object. We expect the first line to be SCSCP start 
# processing instruction) but do not check it explicitly. The parser will 
# just ignore that line.

do {
    $line = fgets($socket, 4096);
    echo $line;
    $data .= $line;
} while ( $line != "<?scscp end ?>\n");
    
fclose($socket);

echo "-------------------------\n";

$res = ParseSCSCPresult($data); 

echo "!!!!!!!!!!!!!!!!!!!!!!!!!\n";
print_r($res);
echo "\n#########################\n";

}

###########################################################################
#
# ValidateBySCSCP( server, port )
#
private function ValidateBySCSCP ( $student_answer, $model_answer, $mode, $server, $port ){
return EvaluateBySCSCP( 'ValidateAnswer', '<OMSTR>'.$student_answer.'</OMSTR><OMSTR>'.$model_answer.'</OMSTR><OMSTR>'.$mode.'</OMSTR>', $server, $port );
} 

}
