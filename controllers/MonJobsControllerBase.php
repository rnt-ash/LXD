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

namespace RNTForest\ovz\controllers;

use RNTForest\ovz\models\MonJobs;
use RNTForest\ovz\forms\MonJobsEditForm;
use RNTForest\ovz\forms\MonJobsNewForm;

class MonJobsControllerBase extends \RNTForest\core\controllers\ControllerBase
{
    /**
    * Show form to add a mon job
    * 
    * @throws Exceptions
    */
    public function monJobsAddAction(){
        // get values from session
        $session = $this->session->get('MonJobsForm');
        
        // sanitize
        $serverId = $this->filter->sanitize($session['server_id'],"int");

        try{
            // Validate
            $server = $session['server_class']::tryFindById($serverId);
            $this->tryCheckPermission("mon_jobs", "general", array('item' => $server));
            
            // Create new MonJob objet
            $monJob = new MonJobs();
            $monJob->setServerId($serverId);
            $monJob->setServerClass($session['server_class']);
            
            // Call view
            $this->view->form = new \RNTForest\ovz\forms\MonJobsNewForm($monJob);
            $this->view->pick("mon_jobs/monJobsNewForm");
        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->redirectToTableSlideDataAction();
        }
        return;
    }
    
    
    /**
    * saves a new monjob
    * 
    * @throws Exceptions
    */
    public function monJobsAddExecuteAction(){
        // get values from session
        $session = $this->session->get('MonJobsForm');
        
        // POST request?
        if (!$this->request->isPost()) 
            return $this->forwardToOrigin();
        
        try{
            // get post data
            $data = $this->request->getPost();
            $serverId = $this->filter->sanitize($data['server_id'],"int");
            $monBehavior = $this->filter->sanitize($data['mon_behavior'],"string");
            
            // validate
            $server = $session['server_class']::tryFindById($serverId);
            $this->tryCheckPermission("mon_jobs", "general", array('item' => $server));
            
            // create new MonJob object
            if(strpos($monBehavior,'MonLocalBehavior')){
                $monJob = $server->addMonLocalJob($monBehavior);
            }else{
                $monJob = $server->addMonRemoteJob($monBehavior);
            }
            
            // validate form
            $form = new \RNTForest\ovz\forms\MonJobsNewForm($monJob);
            if(!$form->isValid($data, $monJob)) {
                $this->view->form = $form; 
                $this->view->pick("mon_jobs/monJobsNewForm");
                return; 
            }
            
            // validate model
            if($monJob->validation() === false) {
                // fetch all messages from model
                foreach($monJob->getMessages() as $message) {
                    $form->appendMessage(new \Phalcon\Validation\Message($message->getMessage(),$message->getField()));
                }
                $this->view->form = $form; 
                $this->view->pick("mon_jobs/monJobsNewForm");
                return;
            }
            
            // business logic
            $this->MonJobsCheckContacts($monJob);
            
            // save monJob
            $monJob->save();
            
            // clean up
            $form->clear();
            $message = $this->translate("monitoring_monjobs_add_successful");
            $this->flashSession->success($message);
        }catch(\Exception $e){
            $message = $this->translate("monitoring_monjobs_add_failed");
            $this->flashSession->error($message.$e->getMessage());
            $this->logger->error($e->getMessage());
        }
        $this->forwardToOrigin();
        return;
    }
    
    /**
    * Calls form to edit a MonJob
    * 
    * @param mixed $monJobId
    * @return mixed
    * @throws Exceptions
    */
    public function monJobsEditAction($monJobId){
        // get values from session
        $session = $this->session->get('MonJobsForm');
        
        // sanitize
        $monJobId = $this->filter->sanitize($monJobId,"int");
        
        try{
            // find MonJob
            $monJob = MonJobs::tryFindById($monJobId);  
            // find server and check permissions
            $server = $session['server_class']::findFirst($monJob->getServerId());
            $this->tryCheckPermission('mon_jobs', 'general', array('item' => $server));

            // Call view
            $this->forwardToEditForm($monJob,$server->getName(),new \RNTForest\ovz\forms\MonJobsEditForm($monJob));
        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->redirectToTableSlideDataAction();
            return;
        }
    }
    
    /**
    * Saves an existing MonJob
    * 
    * @throws Exceptions
    */
    public function monJobsEditExecuteAction(){
        // get values from session
        $session = $this->session->get('MonJobsForm');
        
        // POST request?
        if (!$this->request->isPost()) 
            return $this->forwardToOrigin();
        
        try{
            // get post data
            $data = $this->request->getPost();
            $monJobId = $this->filter->sanitize($data['id'],"int");
            
            // get MonJob
            $monJob = MonJobs::tryFindById($monJobId);
            // get Server and check permission
            $server = $session['server_class']::tryFindById($monJob->getServerId());
            $this->tryCheckPermission('mon_jobs', 'general', array('item' => $server));
            
            // validate form
            $form = new \RNTForest\ovz\forms\MonJobsEditForm($monJob);
            if (!$form->isValid($data, $monJob)) {
                $this->forwardToEditForm($monJob,$server->getName(),$form);
                return; 
            }
            
            // business logic
            $monJob->setMonContactsMessage(implode(',',$monJob->getMonContactsMessage()));
            $monJob->setMonContactsAlarm(implode(',',$monJob->getMonContactsAlarm()));
            $this->MonJobsCheckContacts($monJob);
            
            // save MonJob
            if($monJob->save() === false) {
                // fetch all messages from model
                foreach($monJob->getMessages() as $message) {
                    $form->appendMessage(new \Phalcon\Validation\Message($message->getMessage(),$message->getField()));
                }
                $this->forwardToEditForm($monJob,$server->getName(),$form);
                return;
            }
            
            // clean up
            $form->clear();
            $message = $this->translate("monitoring_monjobs_save_successful");
            $this->flashSession->success($message);
        }catch(\Exception $e){
            $message = $this->translate("monitoring_monjobs_save_failed");
            $this->flashSession->error($message.$e->getMessage());
            $this->logger->error($e->getMessage());
        }
        $this->forwardToOrigin();
        return;
    }
    
    public function forwardToEditForm($monJob,$serverName,$form){
        // get values from session
        $session = $this->session->get('MonJobsForm');
        
        if(strpos($session['server_class'],'Virtual') > 0){
            $this->view->monJobName = $monJob->getShortName('virtual');
        }elseif(strpos($session['server_class'],'Physical') > 0){
            $this->view->monJobName = $monJob->getShortName('physical');
        }
        $this->view->serverName = $serverName;
        $this->view->form = $form; 
        $this->view->pick("mon_jobs/monJobsEditForm");
    }
    
    /**
    * helper method to go through all contacts and validate them
    * 
    * @param mixed $monJob
    */
    private function MonJobsCheckContacts($monJob){
        // check if selected contacts are valid (throws Exceptions)
        $contactsMessage = explode(',',$monJob->getMonContactsMessage());
        foreach($contactsMessage as $monContactMessageId){
            $this->tryMonJobsCheckContact($monContactMessageId);
        }
        $contactsAlarm = explode(',',$monJob->getMonContactsAlarm());
        foreach($contactsAlarm as $monContactAlarmId){
            $this->tryMonJobsCheckContact($monContactAlarmId);
        }
    }
    
    /**
    * helper method to check if contact or login exists and if it belongs to the same customer as the login
    * 
    * @param mixed $monContactId
    * @throws Exceptions
    */
    private function tryMonJobsCheckContact($monContactId){
        // check if login exists
        $login = \RNTForest\core\models\Logins::findFirst($monContactId);
        if(!$login){
            $message = $this->translate("monitoring_monjobs_login_not_exist");
            throw new \Exception($message);
        }
        
        // check if contact has the same customer as the login
        $loginCustomerId = $this->session->get('auth')['customers_id'];
        if($login->getCustomersId() != $loginCustomerId){
            $message = $this->translate("monitoring_monjobs_login_not_from_customer");
            throw new \Exception($message);
        }
    }
    
    /**
    * Mutes or unmutes a monjob based on the current state
    * 
    * @param mixed $monJobId
    * @return mixed
    * @throws Exceptions
    */
    public function monJobsMuteAction($monJobId){
        // get values from session
        $session = $this->session->get('MonJobsForm');
        
        // sanitize
        $monJobId = $this->filter->sanitize($monJobId,"int");
        
        try{
            // find MonJob
            $monJob = MonJobs::tryFindById($monJobId);  
            // find server and check permissions
            $server = $session['server_class']::findFirst($monJob->getServerId());
            $this->tryCheckPermission('mon_jobs', 'general', array('item' => $server));
            
            // business logic
            if($monJob->getMuted() == 0){
                $monJob->setMuted(1);
                $messageSuccess = $this->translate("monitoring_monjobs_mute_successful");
                $messageError = $this->translate("monitoring_monjobs_mute_failed");
            }else{
                $monJob->setMuted(0);
                $messageSuccess = $this->translate("monitoring_monjobs_unmute_successful");
                $messageError = $this->translate("monitoring_monjobs_unmute_failed");
            }
            
            // save MonJob
            if($monJob->save() === false) {
                // fetch all messages from model
                foreach ($monJob->getMessages() as $message) {
                    $this->flashSession->error($message->getMessage());
                }
                $this->redirectToTableSlideDataAction();
                return;
            }
            
            // success message
            $this->flashSession->success($messageSuccess);
        }catch(\Exception $e){
            $this->flashSession->error($messageError.$e->getMessage());
            $this->logger->error($e->getMessage());
        }
        $this->forwardToOrigin();
        return;
    }
    
    /**
    * Deletes a MonJob
    * 
    * @param mixed $monJobId
    * @return mixed
    * @throws Exceptions
    */
    public function monJobsDeleteAction($monJobId){
        // get values from session
        $session = $this->session->get('MonJobsForm');
        
        // sanitize
        $monJobId = $this->filter->sanitize($monJobId,"int");
        
        try{
            // find MonJob
            $monJob = MonJobs::tryFindById($monJobId);  
            // find server and check permissions
            $server = $session['server_class']::findFirst($monJob->getServerId());
            $this->tryCheckPermission('mon_jobs', 'general', array('item' => $server));
            
            // save MonJob
            if($monJob->delete() === false) {
                // fetch all messages from model
                foreach ($monJob->getMessages() as $message) {
                    $this->flashSession->error($message->getMessage());
                }
                $this->forwardToOrigin();
                return;
            }
            
            // success message
            $message = $this->translate("monitoring_monjobs_delete_sucessful");
            $this->flashSession->success($message);
        }catch(\Exception $e){
            $message = $this->translate("monitoring_monjobs_delete_failed");
            $this->flashSession->error($message.$e->getMessage());
            $this->logger->error($e->getMessage());
        }
        $this->forwardToOrigin();
        return;
    }
    
    /**
    * Show details of a MonJob
    * 
    * @param mixed $monJobId
    * @return mixed
    */
    public function monJobsDetailsAction($monJobId){
        // get values from session
        $session = $this->session->get('MonJobsForm');
        
        // sanitize
        $monJobId = $this->filter->sanitize($monJobId,"int");
        
        try{
            // find MonJob
            $monJob = MonJobs::tryFindById($monJobId);  
            // find server and check permissions
            $server = $session['server_class']::findFirst($monJob->getServerId());
            $this->tryCheckPermission('mon_jobs', 'general', array('item' => $server));
            
            // check if the monJob has type remote
            if($monJob->getMonType() != 'remote'){
                $message = $this->translate("monitroing_monjobs_not_remote");
                throw new Exception($message);
            }
            
            // get downtimes
            $downtimes = $monJob->getDownTimeInformation();
            foreach($downtimes as $downtime){
                $healJob = $monJob->getLastHealJobOfMonLogsBetween($downtime->getStartString(),$downtime->getEndString());
                if($healJob instanceof \RNTForest\core\models\Jobs){
                    $downtime->setHealJob($healJob);
                }
            }
            
            // check if it's a virtual or physical server
            if(strpos($session['server_class'],'Virtual') > 0){
                $this->view->serverType = 'virtual';
            }elseif(strpos($session['server_class'],'Physical') > 0){
                $this->view->serverType = 'physical';
            }
            
            // get message and alarm contacts
            $messageContacts = array();
            $messageContactIds = explode(',',$monJob->getMonContactsMessage());
            foreach($messageContactIds as $contactId){
                $messageContacts[] = \RNTForest\core\models\Logins::findFirst(array("columns"=>"CONCAT(firstname, ' ', lastname, ' (',email, ')') as name","order"=>"name","id = ".$contactId));
            }
            $alarmContacts = array();
            $alarmContactIds = explode(',',$monJob->getMonContactsAlarm());
            foreach($alarmContactIds as $contactId){
                $alarmContacts[] = \RNTForest\core\models\Logins::findFirst(array("columns"=>"CONCAT(firstname, ' ', lastname, ' (',email, ')') as name","order"=>"name","id = ".$contactId));
            }
            
            // assign everything to the template
            $this->view->messageContacts = $messageContacts;
            $this->view->alarmContacts = $alarmContacts;
            $this->view->downtimes = $downtimes;
            $this->view->serverName = $server->getName();
            $this->view->monJob = $monJob;
            $this->view->pick("mon_jobs/monJobsDetails");
            return; 
        }catch(\Exception $e){
            $message = $this->translate("monitoring_monjobs_show_details_failed");
            $this->flashSession->error($message.$e->getMessage());
            $this->logger->error($e->getMessage());
        }
        $this->forwardToOrigin();
        return;
    }
    
    /**
    * helper method to go back to the controller
    * 
    */
    public function cancelAction(){
        $this->forwardToOrigin();
    }
    
    /**
    * helper method. Forward to origin
    * 
    */
    protected function forwardToOrigin(){
        $session = $this->session->get("MonJobsForm");
        return $this->redirectTo($session['origin']['controller']."/".$session['origin']['action']);
    }
}
