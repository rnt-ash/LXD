<?php
/**
* @copyright Copyright (c) ARONET GmbH (https://aronet.swiss)
* @license AGPL-3.0
*
* This code is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License, version 3,
* as published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License, version 3,
* along with this program.  If not, see <http://www.gnu.org/licenses/>
*
*/

namespace RNTForest\ovz\models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\StringLength as StringLengthValitator;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;
use Phalcon\Validation\Validator\Between as BetweenValidator;
use Phalcon\Mvc\Model\Message as Message;

class Dcoipobjects extends \RNTForest\core\models\ModelBase
{

    // IP Versions
    const VERSION_IPV4 = 4;
    const VERSION_IPV6 = 6;

    // IP Types
    const TYPE_IPADDRESS = 1;
    const TYPE_IPRANGE = 2;
    const TYPE_IPNET = 3;

    // Allocated
    const ALLOC_RESERVED = 1;
    const ALLOC_ASSIGNED = 2;
    const ALLOC_AUTOASSIGNED = 3;

    /**
    *
    * @var integer
    * @Primary
    * @Identity
    */
    protected $id;

    /**
    * @var integer
    */
    protected $version;

    /**
    * @var integer
    */
    protected $type;

    /**
    *
    * @var string
    */
    protected $value1;

    /**
    *
    * @var string
    */
    protected $value2;

    /**
    *
    * @var integer
    */
    protected $allocated;

    /**
    *
    * @var integer
    */
    protected $main;

    /**
    *
    * @var integer
    */
    protected $colocations_id;

    /**
    *
    * @var integer
    */
    protected $physical_servers_id;

    /**
    *
    * @var integer
    */
    protected $virtual_servers_id;

    /**
    *
    * @var string
    */
    protected $comment;

    /**
    * Method to set the value of field id
    *
    * @param integer $id
    * @return $this
    */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
    * Set the IP version
    *
    * @param integer $version 4 or 6
    * @return $this
    */
    public function setVersion($version)
    {
        if ($version == 4)
            $this->version = 4;
        else
            $this->version = 6;

        return $this;
    }

    /**
    * Set the DCO IP Type
    *
    * @param integer $type TYPE_IPADDRESS, TYPE_IPRANGE, TYPE_IPNET
    * @return $this
    */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
    * Method to set the value of field value1
    *
    * @param string $value1 an Ipv4 or IPv6 Address
    * @return $this
    */
    public function setValue1($value1)
    {
        $this->value1 = $value1;

        return $this;
    }

    /**
    * Method to set the value of field value2
    *
    * @param string $value2 an IP-Address, Suffix or none (depending on type)
    * @return $this
    */
    public function setValue2($value2)
    {
        $this->value2 = $value2;

        return $this;
    }

    /**
    * Set the allocated state
    *
    * @param integer $allocated ALLOC_RESERVED, ALLOC_ASSIGNED, ALLOC_AUTOASSIGNED
    * @return $this
    */
    public function setAllocated($allocated)
    {
        $this->allocated = $allocated;

        return $this;
    }

    /**
    * Method to set the value of field main
    *
    * @param integer $main
    * @return $this
    */
    public function setMain($main)
    {
        $this->main = $main;

        return $this;
    }

    /**
    * @param integer $colocations_id
    * @return $this
    */
    public function setColocationsId($colocations_id)
    {
        if(empty($colocations_id))$colocations_id = NULL;
        else $this->colocations_id = $colocations_id;

        return $this;
    }

    /**
    * @param integer $physical_servers_id
    * @return $this
    */
    public function setPhysicalServersId($physical_servers_id)
    {
        if(empty($physical_servers_id))$physical_servers_id = NULL;
        else $this->physical_servers_id = $physical_servers_id;

        return $this;
    }

    /**
    * @param integer $virtual_servers_id
    * @return $this
    */
    public function setVirtualServersId($virtual_servers_id)
    {
        if(empty($virtual_servers_id))$virtual_servers_id = NULL;
        else $this->virtual_servers_id = $virtual_servers_id;

        return $this;
    }

    /**
    * Method to set the value of field comment
    *
    * @param string $comment
    * @return $this
    */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
    * Returns the value of field id
    *
    * @return integer
    */
    public function getId()
    {
        return $this->id;
    }

    /**
    * Returns the value of field version
    *
    * @return integer
    */
    public function getVersion()
    {
        return $this->version;
    }

    /**
    * Returns the value of field type
    *
    * @return integer
    */
    public function getType()
    {
        return $this->type;
    }

    /**
    * Returns the value of field value1
    *
    * @return string
    */
    public function getValue1()
    {
        return $this->value1;
    }

    /**
    * Returns the value of field value2
    *
    * @return string
    */
    public function getValue2()
    {
        return $this->value2;
    }

    /**
    * Returns the value of field allocated
    *
    * @return integer
    */
    public function getAllocated()
    {
        return $this->allocated;
    }

    /**
    * Returns the value of field main
    *
    * @return integer
    */
    public function getMain()
    {
        return $this->main;
    }

    /**
    * Returns the value of field colocations_id
    *
    * @return integer
    */
    public function getColocationsId()
    {
        return $this->colocations_id;
    }

    /**
    * Returns the value of field physical_servers_id
    *
    * @return integer
    */
    public function getPhysicalServersId()
    {
        return $this->physical_servers_id;
    }

    /**
    * Returns the value of field virtual_servers_id
    *
    * @return integer
    */
    public function getVirtualServersId()
    {
        return $this->virtual_servers_id;
    }

    /**
    * Returns the value of field comment
    *
    * @return string
    */
    public function getComment()
    {
        return $this->comment;
    }

    /**
    * Method to set the value of field dco_id
    *
    * @param integer $dco_id
    * @return $this
    */
    public function setDcoId($dco_id)
    {
        $this->dco_id = $dco_id;

        return $this;
    }

    /**
    * Returns the value of field dco_id
    *
    * @return integer
    */
    public function getDcoId()
    {
        return $this->dco_id;
    }

    /**
    * Initialize method for model.
    */
    public function initialize()
    {
        $this->belongsTo("colocations_id",'RNTForest\ovz\models\Colocations',"id",array("alias"=>"Colocations", "foreignKey"=>array("allowNulls"=>true)));
        $this->belongsTo("physical_servers_id",'RNTForest\ovz\models\PhysicalServers',"id",array("alias"=>"PhysicalServers", "foreignKey"=>array("allowNulls"=>true)));
        $this->belongsTo("virtual_servers_id",'RNTForest\ovz\models\VirtualServers',"id",array("alias"=>"VirtualServers", "foreignKey"=>array("allowNulls"=>true)));
    }

    /**
    * Validations and business logic
    *
    * @return boolean
    */
    public function validation()
    {
        // get params from session
        $session = $this->getDI()->get("session")->get("DcoipobjectsForm");
        $op = $session['op'];

        // check DCO
        $level = 0;
        if($op == 'new'){
            if(isset($session['colocations_id']) && !empty($session['colocations_id'])){
                $this->colocations_id = $session['colocations_id'];
            }elseif(isset($session['physical_servers_id']) && !empty($session['physical_servers_id'])){
                $this->physical_servers_id = $session['physical_servers_id'];
            }elseif(isset($session['virtual_servers_id']) && !empty($session['virtual_servers_id'])){
                $this->virtual_servers_id = $session['virtual_servers_id'];
            }else{
                $message1 = self::translate("ipobjects_dco_submit");
                $message = new Message($message1,"id");            
                $this->appendMessage($message);
                return false;        
            }
        }

        // Validator
        $validator = $this->generateValidator($op);
        if(!$this->validate($validator)) return false;

        /** 
        * Business Logic
        */

        // IPv4 or IPv6
        $this->checkVersion();

        // valid IP format in value1
        if(!$this->isValidIP($this->value1)){
            $message1 = self::translate("ipobjects_ip_not_valid");
            $message = new Message($message1,"value1");            
            $this->appendMessage($message);
            return false;        
        }

        // IP Object type
        if(empty($this->value2)){
            $this->type = self::TYPE_IPADDRESS;
            $this->value2 = "255.255.255.0";
        }else {

            if($this->isValidSubnetMask($this->value2)) {
                $this->type = self::TYPE_IPADDRESS;

            } elseif($this->isValidIP($this->value2)){
                $this->type = self::TYPE_IPRANGE;

            } elseif($this->isValidPrefix($this->value2)) {
                $this->type = self::TYPE_IPNET;

            } else {
                $message1 = self::translate("ipobjects_secont_value_valid");
                $message = new Message(
                    $message1,
                    "value2"
                );            
                $this->appendMessage($message);
                return false;        

            }
        }

        // Reserved must be an IP
        if($this->allocated != self::ALLOC_RESERVED && $this->type != self::TYPE_IPADDRESS){
            $message1 = self::translate("ipobjects_assigned_ip");
            $message = new Message(
                $message1,
                "id"
            );            
            $this->appendMessage($message);
            return false;        
        }

        // Check for possible reservations
        if($this->allocated != self::ALLOC_RESERVED){
            $reservations = $this->getReservations();
            if($reservations === false){
                $message1 = self::translate("ipobjects_no_reservation");
                $message = new Message($message1,"id");            
                $this->appendMessage($message);
                return false;        
            }

            $ok = false;
            foreach($reservations as $reservation){
                if($this->isPartOf($reservation)) $ok = true;
            }
            if(!$ok){
                $message1 = self::translate("ipobjects_ip_notpart_reservation");
                $message = new Message($message1,"id");            
                $this->appendMessage($message);
                return false;        
            }

            // Check for already in use
            if($op == 'new'){
                $found = self::findFirst(array(
                    "id != '".$this->id."' AND value1 = '".$this->value1."' AND allocated != '".self::ALLOC_RESERVED."'",
                ));

                if($found){
                    $message1 = self::translate("ipobjects_ip_already_exists");
                    $message = new Message($message1,"id");            
                    $this->appendMessage($message);
                    return false;        
                }
            }            

            // Check if there is already a main IP
            $found = self::findFirst(array(
                "colocations_id ".(is_null($this->colocations_id)?"IS NULL":"=".$this->colocations_id)." ".
                "AND physical_servers_id ".(is_null($this->physical_servers_id)?"IS NULL":"=".$this->physical_servers_id)." ".
                "AND virtual_servers_id ".(is_null($this->virtual_servers_id)?"IS NULL":"=".$this->virtual_servers_id)." ".
                "AND main = 1",
            ));

            if(!$found){
                $this->main = 1;
            }
        }
    }

    /**
    * searching vor existing reservations of a DCO
    * 
    */
    protected function getReservations() {
        $searching = false;
        $reservations = NULL;

        if(!empty($this->virtual_servers_id)){
            $searching = true;
            $reservations = self::find(array(
                "conditions" => "virtual_servers_id = ".$this->virtual_servers_id.
                " AND allocated = ".self::ALLOC_RESERVED,
            ));
            if($reservations->count() > 0) return $reservations;
        }

        if($searching || !empty($this->physical_servers_id)){
            $searching = true;
            if(!empty($this->virtual_servers_id)){
                $condition = "physical_servers_id = ".$this->virtualServers->physical_servers_id;
            }
            if(!empty($this->physical_servers_id)){
                $condition = "physical_servers_id = ".$this->physical_servers_id;
            }
            $reservations = self::find(array(
                "conditions" => $condition .
                " AND allocated = ".self::ALLOC_RESERVED,
            ));
            if($reservations->count() > 0) return $reservations;
        }            

        if($searching || !empty($this->colocations_id)){
            if(!empty($this->virtual_servers_id)){
                $condition = "colocations_id = ".$this->virtualServers->physicalServers->colocations_id;
            }
            if(!empty($this->physical_servers_id)){
                $condition = "colocations_id = ".$this->physicalServers->colocations_id;
            }
            if(!empty($this->colocations_id)){
                $condition = "colocations_id = ".$this->colocations_id;
            }
            $reservations = self::find(array(
                "conditions" => $condition. 
                " AND allocated = ".self::ALLOC_RESERVED,
            ));
            if($reservations->count() > 0) return $reservations;
        }            

        // no reservations found
        return false;        

    }


    /**
    * generates validator for VirtualServer model
    * 
    * return \Phalcon\Validation $validator
    * 
    */
    public static function generateValidator($op){

        // validator
        $validator = new Validation();

        // value1 
        $message = self::translate("ipobjects_ip_required");
        $validator->add('value1', new PresenceOfValidator([
            'message' => $message
        ]));        

        $message = self::translate("ipobjects_ip_valid");
        $validator->add('value1', new RegexValidator([
            'pattern' => '/^[0-9a-f:.]*$/',
            'message' => $message
        ]));        

        // value2
        $message = self::translate("ipobjects_second-value_check");
        $validator->add('value2', new RegexValidator([
            'pattern' => '/^[0-9a-f:.]*$/',
            'message' => $message,
            'allowEmpty' => true,
        ]));        

        // main
        $message = self::translate("ipobjects_main");
        $validator->add('main', new BetweenValidator([
            'minimum' => 0,
            'maximum' => 1,
            'message' => $message
        ]));        

        // allocated
        $message = self::translate("ipobjects_allocated_value");
        $validator->add('allocated', new BetweenValidator([
            'minimum' => 1,
            'maximum' => 3,
            'message' => $message
        ]));        

        // comment
        $message = self::translate("ipobjects_comment_length");
        $validator->add('comment', new StringLengthValitator([
            'max' => 50,
            'messageMaximum' => $message,
        ]));

        return $validator;
    }

    /**
    * 
    * @return boolean
    */
    public function isMain(){
        if(empty($this->main)) return false;
        else return true;
    }

    /**
    * try to check out the IP Version 
    * 
    * @return void
    */
    public function checkVersion()
    {
        // Value 1 is always a IP Address
        if(strpos($this->value1,':') === false) $this->version = 4;
        else $this->version = 6;
    }

    /**
    * 
    * @param string $ip IP-Address as V4 or V6
    */
    public function isValidIP($ip)
    {
        if($this->version == 4){
            return $this->isValidIPv4($ip);
        } else {
            return $this->isValidIPv6($ip);
        }
    }

    /**
    * 
    * @param string $ip
    */
    public function isValidIPv4($ip)
    {
        $a = explode('.',$ip);
        if(count($a) != 4) return false;

        foreach($a as $byte){
            if(!is_numeric($byte)) return false;
            if($byte < 0 || $byte > 255) return false;
        }

        return true;
    }

    /**
    * 
    * @param string $ip
    */
    public function isValidIPv6($ip)
    {
        // ToDo: IPv6 Check
        return false;
    }

    public function isValidSubnetMask($subnetMask){
        if($this->version == 4){
            return $this->isValidSubnetMaskV4($subnetMask);
        }else {
            // IPv6 has no subnetmask!
            return false;
        }
    }
    
    public function isValidSubnetMaskV4($subnetMask){
        $n = ip2long("128.0.0.0");
        $allMasks = array();
        for($i=0;$i<=31;$i++){
            $allMasks[] = long2ip($n);
            $n = $n >> 1 | $n;
        }
        if(in_array($subnetMask,$allMasks)) return true;
        return false;
    }

    
    
    /**
    * 
    * @param mixed $prefix
    */
    public function isValidPrefix($prefix)
    {
        if($this->version == 4){
            return $this->isValidPrefixV4($prefix);
        } else {
            return $this->isValidPrefixV6($prefix);
        }
    }

    /**
    * 
    * @param mixed $prefix
    */
    public function isValidPrefixV4($prefix){
        $prefix = intval($prefix);
        if ($prefix <=0 || $prefix > 32) return false;
        return true;
    }

    /**
    * 
    * @param mixed $prefix
    */
    public function isValidPrefixV6($prefix){
        $prefix = intval($prefix);
        if ($prefix <=0 || $prefix > 128) return false;
        return true;
    }

    /**
    * Checks if this Dcoipobject is within the given Dcoipobject.
    *     
    * @param IPObject $ip
    */
    public function isPartOf(self $ip)
    {
        if($this->version <> $ip->getVersion()) return false;

        if((gmp_cmp($this->getStart(),$ip->getStart())>=0 && gmp_cmp($this->getStart(),$ip->getEnd())<=0) ||
            (gmp_cmp($this->getEnd(),$ip->getStart())>=0 && gmp_cmp($this->getEnd(),$ip->getEnd())<=0))

            return true;
        else
            return false;
    }

    /**
    * Translates the given IP-Address to a GMP-value.
    * 
    * @param string $ip
    * @return string
    */
    protected function toGMP($ip)
    {
        $gmp = "0";

        if($this->version == 4) {
            $a = explode('.',$ip);
            $gmp = gmp_add($gmp,gmp_mul($a[0],gmp_pow(2,24)));
            $gmp = gmp_add($gmp,gmp_mul($a[1],gmp_pow(2,16)));
            $gmp = gmp_add($gmp,gmp_mul($a[2],gmp_pow(2,8)));
            $gmp = gmp_add($gmp,$a[3]);
        } else {
            $ip = $this->expandIPv6($ip);
            $a = explode(':',$ip);
            for($i=0;$i<8;$i++){
                $gmp = gmp_add($gmp,gmp_mul($a[$i],gmp_pow(2,24)));
            } 
        }
        return gmp_strval($gmp);
    } 

    /**
    * Expands a compressed IPv6 address to its full lenght.
    *     
    * @param string $ip
    * @return string
    */
    protected function expandIPv6($ip) 
    {
        if (strpos($ip, '::') !== false)
            $ip = str_replace('::', 
                str_repeat(':0', 8 - substr_count($ip,':')).':', $ip);
        if (strpos($ip, ':') === 0) $ip = '0'.$ip;
        return $ip;
    }

    /**
    * Compares two Dcoipobjects in terms of start-address.
    * Needed for usort()
    * 
    * @param Dcoipobjects $ipo1
    * @param Dcoipobjects $ipo2
    * @return integer 0:even, -1:ipo1<ipo2, 1:ipo1>ipo2
    */
    public function cmp(self $ipo1,self $ipo2){
        if ($ipo1->getStart() == $ipo2->getStart()) {
            return 0;
        }
        return ($ipo1->getStart() < $ipo2->getStart()) ? -1 : 1;
    }

    /**
    * Gives the start-value of this object as an GMP-value.
    * 
    * @return GMP-Value
    */
    public function getStart(){
        switch($this->type){
            case self::TYPE_IPADDRESS:
                return gmp_strval($this->toGMP($this->value1));
                break;
            case self::TYPE_IPNET:
                # Netz-Maske berechnen
                $mask = gmp_xor(gmp_sub(gmp_pow(2,32),1),gmp_sub(pow(2,32-$this->value2),1));
                # Netz-Nummer berechnen (unterste IP)
                return gmp_strval(gmp_and($this->toGMP($this->value1),$mask));
                break;
            case self::TYPE_IPRANGE:
                return gmp_strval($this->toGMP($this->value1));
                break;
            default:
                $message = self::translate("ipobjects_unexpected_type");
                return $message;
        }

    }

    /**
    * Gives the end-value of this object as an GMP-value.
    * 
    * @return GMP-Value
    */
    public function getEnd(){
        switch($this->type){
            case self::TYPE_IPADDRESS:
                return gmp_strval($this->toGMP($this->value1));
                break;
            case self::TYPE_IPNET:
                # Netz-Maske berechnen
                $mask = gmp_xor(gmp_sub(gmp_pow(2,32),1),
                    gmp_sub(pow(2,32-$this->value2),1));
                # Broadcast berechnen (oberste IP)
                $bcmask = gmp_sub(gmp_pow(2,32-$this->value2),1);
                return gmp_strval(gmp_or($this->toGMP($this->value1),$bcmask));
                break;
            case self::TYPE_IPRANGE:
                return gmp_strval($this->toGMP($this->value2));
                break;
            default:
            $message = self::translate("ipobjects_unexpected_type");
                return $message;
        }

    }

    /**
    * Gives this object as string in one line.
    * 
    * @return string
    */
    public function toString(){
        switch($this->type){
            case self::TYPE_IPADDRESS:
                return $this->value1;
                break;
            case self::TYPE_IPNET:
                return $this->value1."/".$this->value2;
                break;
            case self::TYPE_IPRANGE:
                return $this->value1." - ".$this->value2;
                break;
            default:
            $message = self::translate("ipobjects_unexpected_type");
                return $message;
        }
    }
}
