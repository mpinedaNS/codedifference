<?php

define('RETRY_COUNT', 2);

if (!defined('PDFTOPPM_COMMAND')) {
    define('PDFTOPPM_COMMAND', '/usr/bin/pdftoppm');
}



/* stuff */
abstract class AppFax
{
  protected $nslog;

  public function getCallbackHostname()
  {
      if (Configure::read('NsCallbackHostname') != null && Configure::read('NsCallbackHostname')!="") {
          return Configure::read('NsCallbackHostname');
      } else {
          return gethostname();
      }
  }

  public function nullResponse($http_status_code = 200)
  {
      header("HTTP/1.1 $http_status_code");
      header('X-Powered-By: netsapiens');
      header('Content-type: text/plain');
      exit;
  }

  public function errorResponse($http_status_code, $error_description = null)
  {
      header("HTTP/1.1 {$http_status_code}. $error_description");
      header('X-Powered-By: netsapiens');
      header('Content-type: text/plain');
      if ($error_description) header("Warning: $error_description");
      exit;
  }

  public function grab_image($url, $saveto)
  {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
      $raw = curl_exec($ch);
      $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      if (file_exists($saveto)) {
          unlink($saveto);
      }
      if ($httpcode != 400 &&  $httpcode!= "400")
      {
        $fp = fopen($saveto, 'x');
        fwrite($fp, $raw);
        fclose($fp);
      }


      if (!$raw || strlen($raw)<100)
      {
        usleep(1500000);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        $raw = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (file_exists($saveto)) {
            unlink($saveto);
        }
        if ($httpcode != 400 && $httpcode!= "400")
        {
          $fp = fopen($saveto, 'x');
          fwrite($fp, $raw);
          fclose($fp);
        }
        else {
          return false;
        }
      }

      return true;
  }

  public function generate_jpg($url, $saveto)
  {
      $this->nslog('fax', '('.$this->name.".generate_jpg) url='".$url."'");
      $this->nslog('fax', '('.$this->name.".generate_jpg) saveto='".$saveto."'");
      if (!is_executable(PDFTOPPM_COMMAND) ) {
          return FALSE;
      }

      try{
        $saveto=trim(preg_replace('/[[:^print:]]/', '', $saveto));
        $url=trim(preg_replace('/[[:^print:]]/', '', $url));
        
        $outName = str_replace(".jpg","",escapeshellarg($saveto));
        $arg = '-jpeg ' .escapeshellarg($url).' '.$outName. ' -f 1 -l 1 -singlefile -r 15';
        $strCmd = PDFTOPPM_COMMAND.' '.$arg;
        $this->nslog('fax', '('.$this->name.".generate_jpg) cmd='".$strCmd."'");
        exec($strCmd, $error_log);
        $this->nslog('fax', '('.$this->name.'.generate_jpg) log= '.print_r($error_log, true));

      }
      catch(Exception  $E){
        return FALSE;
      }


      return true;
  }



  function __construct() {
      $this->nslog = new nslog();
      $this->name = "Fax";

      $this->Audiofile = ClassRegistry::init('Audiofile');


  }

  protected function nslog($type, $message) {
      $this->nslog->write($type,$message,$this);
  }

  public function makeCover($form, $pImage,$userArray)
  {

    $this->nslog('fax', "(".$this->name.".upload) makeCover " . print_r($form, true));

    $this->nslog('fax', "(".$this->name.".upload) userArr " . print_r($userArray, true));

    //http://www.onlinecode.org/update-docx-file-using-php/
    $pInfo= array();

    $tz = "UTC";
    if (isset($userArray['time_zone']))
      $tz =$userArray['time_zone'];
    $date = new DateTime("now", new DateTimeZone($tz ) );
    $pInfo['Date'] = $date->format("F j, Y, g:i a T");
    if (isset($form['subject']))
      $pInfo['Subject'] = $form['subject'];

    if (isset($form['sender_name']))
      $pInfo['SenderName'] = $form['sender_name'];
    if (isset($form['sender_company']))
      $pInfo['SenderCompany'] = $form['sender_company'];
    $pInfo['SenderNumber'] = $form['caller_id'];

    if (isset($form['recipient_name']))
      $pInfo['ReceiverName'] = $form['recipient_name'];
    if (isset($form['recipient_company']))
      $pInfo['ReceiverCompany'] = $form['recipient_company'];
    $pInfo['ReceiverNumber'] = $form['phonenumber'];

    if (isset($form['note']))
      $pInfo['Note'] = $form['note'];
    $pImage = $form['pImage'];
    $strBody = $pImage['Image']['bin_data'];
    $tmpName = '/tmp/cover_letter'.uniqid().".docx";


    $this->nslog('fax', "(".$this->name.".upload) makeCover pInfo" . print_r($pInfo, true));


    file_put_contents($tmpName, $strBody);



    $template_file_name = $tmpName;

    $rand_no = rand(11111111, 99999999);
    $fileName = "cover_letter_" . $rand_no . ".docx";

    $folder   = "/tmp/";
    $full_path = $folder . '/' . $fileName;

    try
    {

      $this->nslog('fax', "(".$this->name.".upload) makeCover template_file_name   " . print_r($template_file_name, true));
      $this->nslog('fax', "(".$this->name.".upload) makeCover full_path   " . print_r($full_path, true));

      //Copy the Template file to the Result Directory
      copy($template_file_name, $full_path);

      // add calss Zip Archive
      $zip_val = new ZipArchive;

      //Docx file is nothing but a zip file. Open this Zip File
      if($zip_val->open($full_path) == true)
      {
          // In the Open XML Wordprocessing format content is stored.
          // In the document.xml file located in the word directory.

          $key_file_name = 'word/document.xml';
          $message = $zip_val->getFromName($key_file_name);
          $this->nslog('fax', "(".$this->name.".upload) message " . print_r($message, true));

          $timestamp = date('d-M-Y H:i:s');

          $this->nslog('fax', "(".$this->name.".upload) pInfo " . print_r($pInfo, true));
          // this data Replace the placeholders with actual values
          $options= array("Date","Subject","ReceiverName","ReceiverCompany","ReceiverNumber","SenderName","SenderCompany","SenderNumber","Note");

          if (isset($pInfo)) {
              foreach ($pInfo as $strKey => $strValue) {
                  if (in_array($strKey ,$options))
                  {
                    $strTag = "$($strKey)";
                    $strValue =  str_replace("{{LINERETURN}}","\r\n",$strValue);
                    $message = str_replace($strTag, htmlspecialchars($strValue), $message);

                    $strTag = "<w:t>$strKey</w:t>";

                    $message = str_replace($strTag,"<w:t>".htmlspecialchars($strValue)."</w:t>" , $message);
                  }
              }
          }

          foreach ($options as $strKey) {
              $strTag = "$($strKey)";
              $message = str_replace($strTag, "", $message);
              $strTag = "<w:t>".htmlspecialchars($strKey)."</w:t>";
              $message = str_replace($strTag, "", $message);
          }
          $message = str_replace("<w:t>$(</w:t>", "", $message);
          $message = str_replace("<w:t>)</w:t>", "", $message);

          $this->nslog('fax', "(".$this->name.".upload) message " . print_r($message, true));
          //Replace the content with the new content created above.
          $zip_val->addFromString($key_file_name, $message);
          $zip_val->close();
        }
      }
      catch (Exception $exc)
      {
          $error_message =  "Error creating the Word Document";
        $this->nslog('fax', "(".$this->name.".upload) error_message " . print_r($error_message, true));
$this->nslog('fax', "(".$this->name.".upload) error_message " . print_r($exc, true));
          var_dump($exc);
      }

    //http://www.onlinecode.org/update-docx-file-using-php/

    $this->nslog('fax', "(".$this->name.".upload) $tmpName " . print_r($tmpName, true));


    return $full_path;
  }

  public function _emailFax($uid,$newPdf,$conditions,$strOption ="attnew", $cc_list=array(),$sendAttments = true)
  {
    if (!isset($this->Subscriber))
    {
      APP::import('Model', 'Subscriber');
      $this->Subscriber = new Subscriber();
    }
    if (!isset($this->Domain))
    {
      APP::import('Model', 'Domain');
      $this->Domain = new Domain();
    }
    if (!isset($this->Image)) {
      APP::import('Model', 'Image');
      $this->Image = new Image();
    }

    App::import('Controller', 'Faxes');
    $FaxesController = new FaxesController;


    $fax = isset($conditions['fax'])?$conditions['fax']:array();


    try {
        list($user,$domain) = explode("@",$uid);
        $q['conditions']['aor_user'] = $user;
        $q['conditions']['aor_host'] = $domain;

        $this->nslog('fax', '('.$this->name.'.read)  q '.print_r($q, true));

        $q['fields'] = 'aor_user,aor_host,firstname,lastname,subscriber_login,subscriber_pin,email_address,email_vmail,time_zone,scope,language';

        $result = $this->Subscriber->find('first', $q);

        $this->nslog('fax', '('.$this->name.'.read) sub result '.print_r($result, true));
        $pSubscriberInfo = $result['Subscriber'];
        $this->_requestorRole = $pSubscriberInfo['scope'];
        $this->_requestorUser = $pSubscriberInfo['aor_user'];
        $this->_requestorDomain = $pSubscriberInfo['aor_host'];
        $FaxesController->_requestorRole = $pSubscriberInfo['scope'];
        $FaxesController->_requestorUser = $pSubscriberInfo['aor_user'];
        $FaxesController->_requestorDomain = $pSubscriberInfo['aor_host'];
        $this->_requestorTerritory = $this->Domain->getTerritory($this->_requestorDomain);
        $FaxesController->_requestorTerritory =$this->_requestorTerritory;
        $strType = 'fax';

        $strRecipient = $pSubscriberInfo['email_address'];

        //$queryX['fields'] = 'email_sender';
        $queryX['conditions']['domain'] = $domain;
        $this->nslog('fax', '('.$this->name.'._emailFax) queryX '.print_r($queryX, true));
        $pResultX = $this->Domain->find('first', $queryX);

        $this->nslog('fax', '('.$this->name.'._emailFax) pResultX '.print_r($pResultX, true));
        $strSender = $pResultX['Domain']['email_sender'];

        $this->nslog('fax', '('.$this->name.'._emailFax) pSubscriberInfo '.print_r($pSubscriberInfo, true));

        if ($strOption === 'storage_full') {
          $strBody = $FaxesController->__getEmailVmailBody($pSubscriberInfo, array('caller_id' => $conditions['caller_id']), '', 'storage_full', null);

          $FaxesController->__sendMail($strSender, $pSubscriberInfo['email_address'], 'Warning: Failed to recieve fax - your fax mailbox is full', /*strMessage*/'', $strBody, null, null);
          return;
        }

        if (isset($newPdf)) {
            $pVmailInfo = $this->Audiofile->GetVmailInfo($strType, $pSubscriberInfo['aor_host'], $pSubscriberInfo['aor_user'], basename($newPdf));
        }

        $this->nslog('fax', '('.$this->name.'._emailFax) pVmailInfo '.print_r($pVmailInfo, true));

        $strCalleeNmbr = $this->_requestorUser;
        $strCallerNmbr = $pVmailInfo['FromUser'];

        $strSubject = 'Fax from '.$FaxesController->formatPhoneNumber(str_replace('+', '', $pVmailInfo['from_number'])).' to '.$FaxesController->formatPhoneNumber(str_replace('+', '', $pVmailInfo['to_number']));

        $this->nslog('fax', '('.$this->name.'._emailFax) strSubject '.print_r($strSubject, true));



        $this->nslog('fax', '('.$this->name."._emailFax) strSender='".$strSender."'");

        //--------------------------------------------------------------------------------------------------------------------------------
        //$strOption = $pSubscriberInfo['email_vmail'];
        if (isset($fax['num_pages'])  && $fax['num_pages']=="0") {
            //do not email if 0 pages.
        } elseif (($strOption == 'attnew')
                || ($strOption == 'attsave')
                || ($strOption == 'atttrash')
                || ($strOption == 'briefattnew')
                || ($strOption == 'briefattsave')
                || ($strOption == 'briefatttrash')
        ) {
            $this->nslog('fax', '('.$this->name."._emailFax) __IsAttach='");
            $strPwd = $pSubscriberInfo['subscriber_pin'];

            $this->nslog('fax', '('.$this->name."._emailFax) __IsAttach='".$strPwd);
            $pAttachments = array();
            if($sendAttments)
            {
              $strFilePath = $this->Audiofile->GetFilePath($strType, $pSubscriberInfo['aor_host'], $pSubscriberInfo['aor_user'], basename($newPdf));
              $this->nslog('fax', '('.$this->name."._emailFax) strFilePath='".$strFilePath);
              $pAttachments[] = $strFilePath;
            }


            $this->nslog('fax', '('.$this->name."._emailFax) pSubscriberInfo='".print_r($pSubscriberInfo,true));
            $this->nslog('fax', '('.$this->name."._emailFax) pVmailInfo='".print_r($pVmailInfo,true));

            $strBody = $FaxesController->__getEmailVmailBody($pSubscriberInfo, $pVmailInfo, /*$strVMailUrl*/'', /*bAsAttachment*/true, basename($newPdf));
            $this->nslog('fax', '('.$this->name."._emailFax) strBody='".$strBody."'");


            $FaxesController->__sendMail($strSender, $strRecipient, $strSubject, /*strMessage*/'', $strBody, $pAttachments, $pVmailInfo);
        }
        else if (
                ($strOption == 'yes')
                || ($strOption == 'brief')
        ) {
            $this->nslog('fax', '('.$this->name."._emailFax) __IsNOTAttach='");
            $strPwd = $pSubscriberInfo['subscriber_pin'];

            $this->nslog('fax', '('.$this->name."._emailFax) __IsNOTAttach='".$strPwd);
            $pAttachments = array();


            $this->nslog('fax', '('.$this->name."._emailFax) pSubscriberInfo='".print_r($pSubscriberInfo,true));
            $this->nslog('fax', '('.$this->name."._emailFax) pVmailInfo='".print_r($pVmailInfo,true));
            $this->nslog('fax', '('.$this->name."._emailFax) fax='".print_r($fax,true));

            if (!isset($this->Domain))
            {
              APP::import('Model', 'Domain');
              $this->Domain = new Domain();
            }

            if (!isset($this->Uiconfig))
            {
              APP::import('Model', 'Uiconfig');
              $this->Uiconfig = new Uiconfig();
            }

            $requestorTerritory = $this->Domain->getTerritory($pSubscriberInfo['aor_host']);
            $host = $this->Uiconfig->Query('PORTAL_FQDN', "", '*', $pSubscriberInfo['aor_host'], "*", $pSubscriberInfo['aor_user'],$requestorTerritory);
            if ($host=="")
            {
              if (Configure::read('NsCallbackHostname') != null && Configure::read('NsCallbackHostname')!="") {
                  $host = Configure::read('NsCallbackHostname');
              } else {
                  $host = gethostname();
              }
            }

            $strVMailUrl = 'https://'.$host.'/portal/voicemails/fax/';
            $this->nslog('fax', '('.$this->name."._emailFax) $strVMailUrl='".$strVMailUrl."'");

            $strBody = $FaxesController->__getEmailVmailBody($pSubscriberInfo, $pVmailInfo, $strVMailUrl, /*bAsAttachment*/false, basename($newPdf));
            $this->nslog('fax', '('.$this->name."._emailFax) strBody='".$strBody."'");


            $FaxesController->__sendMail($strSender, $strRecipient, $strSubject, /*strMessage*/'', $strBody, $pAttachments, $pVmailInfo);
        }
        else {

        }
    } catch (Exception $e) {
    }


  }

    /* abstract transcribe function, to be implemented in the vendor file */
  abstract public function read($form);
  abstract public function update($form);
  abstract public function create($form);
  abstract public function count($form);
  abstract public function upload($form);


}
