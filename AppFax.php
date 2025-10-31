<?php


require_once(WWW_ROOT.DS.'../Vendor/AppFax.php');

class Company {
    public $COMPANYID = 0;
    public $PPASSWORD= 0;
    public $LOGONNAME= 0;
    public $COMPANYNAME= 0;
    public $FIRSTNAME= 0;
    public $LASTNAME= 0;
    public $ADDRESS1= 0;
    public $ADDRESS2= 0;
    public $CITY= 0;
    public $COUNTRY= 0;
    public $ZIP= 0;
    public $STATE= 0;
    public $EMAIL= 0;
    public $PHONE= 0;
    public $FAX= 0;
    public $REFERENCE= 0;
    public $TARIFFPLAN= 0;
    public $MLIMIT_M_= 0;
    public $MUSED_M_= 0;
    public $ISACTIVE= 0;
    public $DESCRIPTION= 0;
    public $TZ= 0;
    public $MONTHLYLIMIT= 0;
    public $STARTDAY_D_= 0;
    public $CAN_EDIT_USERS= 0;
}

class User {
public $USERID= 0;
public $COMPANYID= "";
public $LOGONNAME= "";
public $PPASSWORD= "";
public $JOINDATE= "";
public $DESCRIPTION= "";
public $FAXSTRID= "";
public $ISACTIVE= 1;
public $E2FNOPASS= "";
public $ALLOWRESEND= "";
public $ALLOWVIEWFAX= "";
public $ALLOWPCTOFAX= "";
public $REPORTEMAIL= "";
public $REPORTSENDEMAIL= "";
public $REPORTEMAILTYPE= "";
public $REPORTSENDFAX= "";
public $REPORTFAXTYPE= "";
public $FAXTOEMAILNO= "";
public $FAXTOEMAILEMAIL= "";
public $FAXTOEMAILFLAG= "";
public $FAXTOEMAILTYPE= "";
public $FIRSTNAME= "";
public $LASTNAME= "";
public $TARIFFPLAN= 0;
public $MLIMIT_M_= "";
public $MUSED_M_= "";
public $CHARGEBYPAGE= "";
public $COUNTRY= "";
public $CITY= "";
public $ADDRESS1= "";
public $ADDRESS2= "";
public $STATE= "";
public $ZIP= "";
public $COUNTRYCODE= "";
public $AREACODE= "";
public $PHONENUMBER= "";
public $FAXNUMBER= "";
public $EMAIL= "";
public $TZ= "";
public $BILL_VOICE= "";
public $BILL_PARTIAL= "";
public $MONTHLYLIMIT= "";
public $STARTDAY_D_= "";
public $FAXTOFAXFLAG= "";
public $FAXTOFAXEMAIL="";
public $FAXTOFAXNO= "";
public $FAXTOFAXNO2= "";
public $FAXTOFAXDELIVERY= "";
public $ATAMAC= "";
public $SSL_FAX= "";
public $DELIVER_OFFLINE= 1;
public $HTTPPROXY= "";
public $SOCKSPROXY= "";
public $PROXYTYPE= "";
public $SOCKSPROXYVER= "";


}

// class SoapClientDebug extends SoapClient
// {
//
//   function __construct($wsdl, $options) {
//
//         parent::__construct($wsdl, $options);
//     }
//
//     function __doRequest($request, $location, $action, $version, $one_way = 0) {
//         echo $request;
//         try {
//             parent::__doRequest($request, $location, $action, $version, $one_way);
//         } catch (Exception $e) {
//             return $e->getMessage();
//             throw new Exception($request);
//         }
//
//     }
//
//     function __call($function_name, $arguments)
//     {
//         try {
//             parent::__call($function_name, $arguments);
//         } catch (Exception $e) {
//             return $e->getMessage();
//         }
//     }
// }

class PangeaFax extends AppFax
{
  private $userByDomain = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
   <soapenv:Header/>
   <soapenv:Body>
      <tem:ConcertService___UserAccount>
         <tem:ACred>
            <tem:LOGONNAME>{{USERNAME}}</tem:LOGONNAME>
            <tem:PPASSWORD>{{PASSWORD}}</tem:PPASSWORD>
         </tem:ACred>
         <tem:ALevel>Agent</tem:ALevel>
         <tem:AOperation>Select</tem:AOperation>
        <tem:AUser>
            <tem:COMPANYID>{{COMPANYID}}</tem:COMPANYID>
         </tem:AUser>
      </tem:ConcertService___UserAccount>
   </soapenv:Body>
</soapenv:Envelope>';

private $userByID = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
 <soapenv:Header/>
 <soapenv:Body>
    <tem:ConcertService___UserAccount>
       <tem:ACred>
          <tem:LOGONNAME>{{USERNAME}}</tem:LOGONNAME>
          <tem:PPASSWORD>{{PASSWORD}}</tem:PPASSWORD>
       </tem:ACred>
       <tem:ALevel>Agent</tem:ALevel>
       <tem:AOperation>Select</tem:AOperation>
    <tem:AUser>
          <tem:USERID>{{USERID}}</tem:USERID>
       </tem:AUser>
    </tem:ConcertService___UserAccount>
 </soapenv:Body>
</soapenv:Envelope>';

private $getFaxesAllUsers = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
 <soapenv:Header/>
 <soapenv:Body>
    <tem:ConcertService___Cdr>
       <tem:ACred>
          <tem:LOGONNAME>{{USERNAME}}</tem:LOGONNAME>
          <tem:PPASSWORD>{{PASSWORD}}</tem:PPASSWORD>
       </tem:ACred>
       <tem:ALevel>Agent</tem:ALevel>
       <tem:AOperation>Select</tem:AOperation>
       <tem:AReportType>LastHour</tem:AReportType>
    </tem:ConcertService___Cdr>
 </soapenv:Body>
</soapenv:Envelope>';

  private $territory = "";



  function __construct() {
      parent::__construct();

      $this->url = Configure::read('NsPangeaURL');
      if ($this->url== null)
        $this->url = "https://secure.ipfax.net:8445/SOAP";



        $this->auth = new stdClass();
        $this->auth->LOGONNAME = new SoapVar(Configure::read('Pangea.username'), XSD_STRING);
        $this->auth->PPASSWORD = new SoapVar(Configure::read('Pangea.password'), XSD_STRING);

        $context = stream_context_create(array(
      'ssl' => array(
          // set some SSL/TLS specific options
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
      )));

      try {
        $this->client = new SoapClient($this->url, array('trace' => true,'stream_context' => $context) );  //,'classmap' => array('COMPANY' => "Company")
      }
      catch (Exception $e )
      {
        $this->nslog('fax', '('.$this->name.'.SoapClient excpetion ' . print_r($e,true));

      }

  }

  public function setAuth($territory)
  {
    $this->$territory = $territory;
    if (Configure::read('Pangea.'.$territory.'.username')!= null)
      $this->auth->LOGONNAME = new SoapVar(Configure::read('Pangea.'.$territory.'.username'), XSD_STRING);
     if (Configure::read('Pangea.'.$territory.'.password')!= null)
      $this->auth->PPASSWORD = new SoapVar(Configure::read('Pangea.'.$territory.'.password'), XSD_STRING);
  }

  public function getConfig($config_name,$default )
  {
    if (isset($this->$territory) && $this->$territory !="")
      if (Configure::read('Pangea.'.$this->$territory.'.'.$config_name)!= null)
        return Configure::read('Pangea.'.$this->$territory.'.'.$config_name);
    if (Configure::read('Pangea.'.$config_name)!= null)
      return Configure::read('Pangea.'.$config_name);
    return $default;
  }

  public function delete($form)
  {
      $this->nslog('fax', '('.$this->name.'.delete $form3 ' . print_r($form,true));
      if (!isset($form)) {
          $form=array();
      }
      if (!isset($form['domain'])) {
          $this->errorResponse('400 Bad Request', 'Missing domain');
      }
      if (!isset($form['phonenumber'])) {
          $this->errorResponse('400 Bad Request', 'Missing phonenumber');
      }

      $AUser = $this->getUsers($this->getCompanyID($form['domain']), null, $form['phonenumber'] );

      if (isset($AUser) && isset($AUser[0]))
      {
        $AUser = $AUser[0];
      }
      $this->nslog('fax', '('.$this->name.'.read) delete $AUser'.print_r($AUser, true));
      $delete_query = new StdClass();
      $delete_query->ACred = $this->auth;
      $delete_query->AOperation = "Delete";
      $delete_query->ALevel = "Agent";
      $delete_query->AUser = $AUser;

      $this->nslog('fax', '('.$this->name.'.read pangea ) delete $delete_query '.print_r($delete_query, true));
      try{
        $response = $this->client->UserAccount($delete_query);
      }
      catch(Exception $e)
      {
        $this->nslog('fax', '('.$this->name.'.read pangea ) delete $delete_query '.print_r($e, true));
        $this->errorResponse('400 Bad Request', $response );
      }

      try{

        $CACHE_ID = 'pangea_domain_id'.'_'.$form['domain'];
        if (Cache::read($CACHE_ID, '_long_cache_')!= FALSE)
          Cache::delete($CACHE_ID, '_long_cache_');

      }
      catch(Exception $e)
      {

      }

      $this->nullResponse(HTTP_STATUS_CODE_UPDATE_SUCCESS);

      exit;
  }

  public function update($form, $newAccount = array())
  {
      if (!isset($form)) {
          $form=array();
      }
      if (!isset($form['domain'])) {
          $this->errorResponse('400 Bad Request', 'Missing domain');
      }
      if (!isset($form['phonenumber'])) {
          $this->errorResponse('400 Bad Request', 'Missing phonenumber');
      }

      $this->nslog('fax', '('.$this->name.'.read) update $form'.print_r($form, true));

      $create_query = new StdClass();
      $create_query->ACred = $this->auth;
      $create_query->ALevel = "Agent";


      if (isset($form['USERID']))
      {
        //$AUser = $this->getUserByID($form['USERID'],$this->getCompanyID($form['domain']));

        $AUser = $this->getUsers($this->getCompanyID($form['domain']), null, null, $form['USERID']);
        $this->nslog('fax', '('.$this->name.'.read) update $AUser'.print_r($AUser, true));
        $AUser->USERID = $form['USERID'];
        $create_query->AOperation = "Edit";
      }
      else
      {
        $create_query->AOperation = "Add";
        $AUser =  new User();
        $AUser->PPASSWORD = uniqid();
        $AUser->CHARGEBYPAGE= $this->getConfig("CHARGEBYPAGE",1 );
        $AUser->MONTHLYLIMIT= $this->getConfig("MONTHLYLIMIT",1 );

        $AUser->MLIMIT_M_= $this->getConfig("MLIMIT_M_",0 );//"0-F2E 1-NA"
        $AUser->TARIFFPLAN= $this->getConfig("TARIFFPLAN",30 );//"0-F2E 1-NA"

        $AUser->ALLOWRESEND= $this->getConfig("ALLOWRESEND",1 );
        $AUser->ALLOWVIEWFAX= $this->getConfig("ALLOWVIEWFAX",1 );
        $AUser->ALLOWPCTOFAX= $this->getConfig("ALLOWPCTOFAX",1 );

        $AUser->REPORTEMAILTYPE= $this->getConfig("REPORTEMAILTYPE",0 );
        $AUser->REPORTFAXTYPE= $this->getConfig("REPORTFAXTYPE",0 );



        $AUser->E2FNOPASS= $this->getConfig("E2FNOPASS",1 );
      }

      $emailCount=0;
      if (isset($form['success_email']) && $form['success_email'] == "1")
        $emailCount += 2;
      if (isset($form['failure_email']) && $form['failure_email'] == "1")
        $emailCount += 1;

      $ataCount =0;
      if (isset($form['success_ata']) && $form['success_ata'] == "1")
        $ataCount += 2;
      if (isset($form['failure_ata']) && $form['failure_ata'] == "1")
        $ataCount += 1;

      $this->nslog('fax', '('.$this->name.'.read pangea ) update sharing '.print_r($form['sharing'], true));
      if (isset($form) && isset($form['sharing']))
          $AUser->ZIP= "shared:".$form['sharing'];
      $this->nslog('fax', '('.$this->name.'.read pangea ) $AUser->ZIP '.print_r($AUser->ZIP, true));


      $AUser->REPORTSENDEMAIL= $this->getConfig("REPORTSENDEMAIL",$emailCount );
      $AUser->REPORTSENDFAX= $this->getConfig("REPORTSENDFAX",$ataCount );


      $AUser->COMPANYID = $this->getCompanyID($form['domain']);


      $AUser->ISACTIVE = "1";
      $AUser->DELIVER_OFFLINE = "1";

      $pPostsUser = $form['userArray'];
      if (isset($pPostsUser) && count($pPostsUser)>0)
      {
        $email = $pPostsUser['email'];
        if (!isset($form['USERID']))
          $AUser->LOGONNAME = explode(";",$email)[0];
        $email = str_replace(";",",",$email);
        $AUser->FIRSTNAME = $pPostsUser['first_name'];
        $AUser->LASTNAME = $pPostsUser['last_name'];
        $AUser->EMAIL = $email;

        $AUser->TZ = 0;
        if (isset($pPostsUser['time_zone']))
        {
          date_default_timezone_set($pPostsUser['time_zone']);
          $AUser->TZ =  date('Z') / 3600;
          $this->nslog('fax', '('.$this->name.'.read) update $AUser->TZ  '.print_r($AUser->TZ , true));
        }
      }
      else {
        $this->errorResponse(400, "invalid user");
      }


      $uid = $form['user']."@".$form['domain'];
      $AUser->DESCRIPTION = $uid;
      $AUser->FAXNUMBER   = $form['phonenumber'];

      if (isset($form['enable_email']) && $form['enable_email'] == "1")
      {
        $AUser->FAXTOEMAILNO= $form['phonenumber'];
        $AUser->FAXTOEMAILEMAIL= $email;
        $AUser->FAXTOEMAILFLAG= 1;
        $AUser->FAXTOEMAILTYPE= 1;

        if (isset($form['inbound_attachment']) && $form['inbound_attachment']=="1")
          $AUser->SSL_FAX= 1;
        else if (isset($form['inbound_email']) && $form['inbound_email']=="1")
          $AUser->SSL_FAX= 0;

      }
      elseif (isset($form['enable_email'])) {
        $AUser->FAXTOEMAILFLAG= 0;
      }

      if (isset($form['enable_ata']) && $form['enable_ata'] == "1")
      {

        $AUser->FAXTOFAXEMAIL= $email;
        $AUser->FAXTOFAXFLAG= 1;
        $AUser->FAXTOFAXNO= $form['phonenumber'];

      }
      else
      {
        $form['inbound_ata']=0; //force inbound ata off if not enabled
          
        $AUser->FAXTOFAXFLAG= 0;
        $AUser->FAXTOFAXNO= "";
        $AUser->FAXTOFAXEMAIL= "";
      }

      if (isset($form['SerialNumber']) )
      {
        $AUser->ATAMAC = $form['SerialNumber'];
      }


      $binaryCount=0;
      if ((isset($form['inbound_attachment']) && $form['inbound_attachment']=="1") ||
          (isset($form['inbound_email']) && $form['inbound_email']=="1"))
      {
        $binaryCount += 1;
      }
      if (isset($form['inbound_ata']) && $form['inbound_ata']==1)
        $binaryCount += 2;

      if($binaryCount == 3) $AUser->FAXTOFAXDELIVERY= 2;
      if($binaryCount == 2) $AUser->FAXTOFAXDELIVERY= 0;
      if($binaryCount <2) $AUser->FAXTOFAXDELIVERY= 1;


      $AUser->REPORTEMAIL= $email;



      $create_query->AUser = $AUser;

      $this->nslog('fax', '('.$this->name.'.read pangea ) update $create_query '.print_r($create_query, true));
      try{
        $response = $this->client->UserAccount($create_query);
        $this->nslog('fax', '('.$this->name.'.read pangea ) update $$response OK '.print_r($response, true));
      }
      catch(Exception $e)
      {
        $this->nslog('fax', '('.$this->name.'.read pangea ) CATCH  '.print_r($$e, true));
        if (!isset($form['USERID']))
        {
          $AUser->LOGONNAME = uniqid()."@".$form['domain'];
          $create_query->AUser = $AUser;

        }

        $this->nslog('fax', '('.$this->name.'.read pangea ) update $create_query2 '.print_r($create_query, true));
        $response = $this->client->UserAccount($create_query);
      }

      $this->nslog('fax', '('.$this->name.'.read pangea ) update $response'. print_r($response, true));

      if (isset($response->AccountGuid))
      {
        //we created a user
        $this->nslog('fax', '('.$this->name.'.read pangea ) update '. print_r("we created a user2", true));
      }
      else if (isset($response->Result->USERS) && isset($response->Result->USERS->USERID))
      {
        //we created a user
        $this->nslog('fax', '('.$this->name.'.read pangea ) update'. print_r("we created a user2", true));
      }
      else {
        $this->nslog('fax', '('.$this->name.'.read pangea ) sending 400');
        $this->errorResponse(400, "Account Setup Failed");
      }

      $this->nslog('fax', '('.$this->name.'.read pangea ) update'. print_r("returning 200ok", true));
      $this->nullResponse(HTTP_STATUS_CODE_UPDATE_SUCCESS);

      exit;
  }



  public function create($form)
  {
      $newAccount = array();

      $newAccount['SaveCDR']=1;
      $newAccount['AllowSnd'] = 1;
      $newAccount['AllowRcv'] = 1;
      $newAccount['PasswordStr'] = uniqid("PAss_")."$!";

      return $this->update($form, $newAccount);
    }

    public function upload($form)
    {
        $this->nslog('fax', '('.$this->name.'.upload) form '.print_r($form, true));

        if (empty($form)) {
            $form = $_REQUEST;
        }

        App::import('Controller', 'Mailers');
        $MailersController = new MailersController;

        $uid= $form['owner']."@".$form['owner_domain'];
        $users = $this->getUsers($this->getCompanyID($form['owner_domain']), $uid, $form['caller_id'], null, true);

        $this->nslog('fax', '('.$this->name.'.upload) $users '.print_r($users, true));
        $user = $users[0];
        $this->nslog('fax', '('.$this->name.'.upload) $user '.print_r($user, true));

        $username = $user['LOGONNAME'];
        $password = $user['PPASSWORD'];

        if (isset($form['pImage']) )
        {
          $full_path = $this->makeCover($form,$form['pImage'],$form['userArray']);
        }

        $receipent = "catchall@ipfax.net";

        $name = (isset($form['recipient_name']) && $form['recipient_name']!= "") ?$form['recipient_name']:$form['phonenumber'];
        $newId = uniqid();
        if (strlen($form['phonenumber']) ==10)
          $form['phonenumber'] = "1" . $form['phonenumber'];
        $subject = $name. " u=".$username ." n=".$form['phonenumber']. " s=fax".$newId;
        $this->nslog('fax', '('.$this->name.'.upload) $subject '.print_r($subject, true));

        $newTmp = '/usr/local/NetSapiens/netsapiens-api/tmp/'.$newId.$form['file']['name'];

        $this->nslog('fax', '('.$this->name.'.upload) $newTmp '.print_r($newTmp, true));
        if (move_uploaded_file($form['file']['tmp_name'], $newTmp )) {
            echo "Uploaded";
        } else {
           echo "File was not uploaded";
        }

        $pAttachments= array();
        if($full_path)
          $pAttachments[] = $full_path;
        $pAttachments[] = $newTmp;

        $options['domain']=$form['owner_domain'];
        $options['sender']="vmail@netsapiens.com";
        $MailersController->setEmailSender($options);
        $this->nslog('fax', '('.$this->name.'.upload) pangea email_sender '.print_r($options['sender'], true));
        $result = $MailersController->sendMail(
            $options['sender'],
            $receipent,
            $subject,
            '', //message
            ' ',
            $pAttachments,
            null
        );

        Cache::write("sentFaxfax".$newId,$uid, '_long_cache_');

        if (true) //not sure how to validate this...
        {
          try {
              $fax['id'] = 'out'.$newId;

              $conditions['object'] = 'audio';
              $ext = pathinfo($form['file']['tmp_name'], PATHINFO_EXTENSION);
              $conditions['type'] = 'pdf';
              $conditions['index'] = $fax['id'];
              $conditions['tmp_name'] = $newTmp;
            //  $conditions['file'] = 'fax-'.$fax['id'].'.'.$ext;
              $conditions['file'] = 'fax-'.$fax['id'].'.'."pdf";
              $conditions['owner'] = $form['owner'];
              $conditions['owner_domain'] = $form['owner_domain'];

              $conditions['fax'] = $fax;
              $this->nslog('fax', '('.$this->name.'.upload) conditions '.print_r($conditions, true));
              $conditions['NmsAni']=$form['phonenumber'];
              $conditions['fax']['status']="pending";
              $conditions['tx_hostname']=gethostname();
              $conditions['fax']['tx_hostname']=gethostname();
              // $conditions['fax']['transcription']=$mesg;
              $this->nslog('fax', '('.$this->name.'.upload) conditions '.print_r($conditions, true));
              $this->Audiofile->addImage($conditions);
          } catch (Exception $e) {
          }
        }

        $this->nslog('fax', '('.$this->name.'.upload) $result '.print_r($result, true));

        exit;
    }

    private function createCompanyID($domain)
    {
      $create_query = new StdClass();
      $create_query->ACred = $this->auth;
      $create_query->ALevel = "Agent";
      $create_query->AOperation = "Add";
      $ACompany= new Company();
      $ACompany->COMPANYNAME = $domain;
      $ACompany->PPASSWORD = $domain;
      $ACompany->LOGONNAME = "manager@".uniqid();
      $ACompany->ISACTIVE = 1;
      $ACompany->CBTARIFF = 0;


      $create_query->ACompany = $ACompany;
      $this->nslog('fax', '('.$this->name.'.read pangea ) createCompanyID $create_query'.print_r($create_query, true));
      $response = $this->client->CompanyAccount($create_query);

      $this->nslog('fax', '('.$this->name.'.read pangea ) createCompanyID $response'.print_r($response, true));

      if (isset($response->Result->COMPANY->COMPANYID))
        return $response->Result->COMPANY->COMPANYID;

      return 999999;

    }

    private function getCompanyID($domain)
   {
     $CACHE_ID = 'pangea_domain_id'.'_'.$domain;

     if (Cache::read($CACHE_ID, '_long_cache_')!= FALSE)
       return Cache::read($CACHE_ID, '_long_cache_');

     $search_query = new StdClass();
     $search_query->ACred = $this->auth;
     $search_query->ALevel = "Agent";
     $search_query->AOperation = "Select";
     $search_query->ACompany = null;

     $this->nslog('fax', '('.$this->name.'.read pangea ) getCompanyID $search_query'. $this->redact(print_r($search_query, true)));
     $response = $this->client->CompanyAccount($search_query);

     foreach ($response->Result->COMPANY as  $x)
     {
       if (strtoupper($domain) == strtoupper($x->COMPANYNAME))
       {
         Cache::write($CACHE_ID, $x->COMPANYID, '_long_cache_');
         return $x->COMPANYID;
       }

     }

     $newId = $this->createCompanyID($domain);

     if ($newId!=999999)
      Cache::write($CACHE_ID, $newId, '_long_cache_');

     return $this->createCompanyID($domain);

   }

   private function IsATAOnline($mac)
  {
    $search_query = new StdClass();
    $search_query->ACred = $this->auth;
    $search_query->ATAMAC = $mac;

    $response = $this->client->IsATAOnline($search_query);

    if (isset($response->Result))
    {
      return $response->Result;
    }
    return false;

  }

  private function redact($str)
  {
    return str_replace($this->auth->PPASSWORD->enc_value,"<REMOVED>",$str);
  }

   private function getUserByID($id, $companyID)
  {
    $search_query = new StdClass();
    $search_query->ACred = $this->auth;
    $search_query->ALevel = "Agent";
    $search_query->AOperation = "Select";
    $user_query= new stdClass();
    $user_query->USERID = new SoapVar($id, XSD_INT);
    $user_query->COMPANYID = new SoapVar($companyID, XSD_INT);

    $search_query->AUser = $user_query;

    $this->nslog('fax', '('.$this->name.'.read pangea ) getUserByID $search_query'.$this->redact(print_r($search_query), true));
    $response = $this->client->UserAccount($search_query);

    $this->nslog('fax', '('.$this->name.'.read pangea ) getUserByID $response'.print_r($response, true));


    return;

  }

  private function getCdrs($form,$companyID, $uid= null, $phonenumber = null, $id = null)
  {
    $results['xml']['faxnumber']= array();

    $search_query = new StdClass();
    $search_query->ACred = $this->auth;
    $search_query->ALevel = "Agent";
    $search_query->AOperation = "Select";

    if (isset($form['type']) && in_array($form['type'],array("MonthToDate","Last30Days","Today","LastHour")))
      $search_query->AReportType = $form['type'];
    else {
      $search_query->AReportType = "Last30Days";

    }

    $this->nslog('fax', '('.$this->name.'.upload) getCdrs '.print_r("getCdrs", true));
    $this->nslog('fax', '('.$this->name.'.upload) $phonenumber '.print_r($phonenumber, true));
    $this->nslog('fax', '('.$this->name.'.upload) $uid '.print_r($uid, true));

    $users = $this->getUsers($companyID, $uid, $phonenumber);
    if (isset($users[0]))
      $user = $users[0];
    else {
      return $results;
    }

    $this->nslog('fax', '('.$this->name.'.upload) $users '.print_r($users, true));

    $search_query->AClientID = $user['USERID'];


    //print_r($search_query);
    try{
      $this->nslog('fax', '('.$this->name.'.read pangea ) getCdrs $search_query'.$this->redact(print_r($search_query, true)));
      $response = $this->client->Cdr($search_query);


      $idx = 0;
      if (isset($response->Result->CDR))
      {
        if (isset($response->Result->CDR->MESSAGEID))
        {
          $results['xml']['faxnumber'][$idx]['cdr'] = (array)$response->Result->CDR;
        }
        else
          foreach ($response->Result->CDR as $cdr)
          {
              $results['xml']['faxnumber'][$idx]['cdr'] = (array)$cdr;
              $idx++;
          }

      }



      $this->nslog('fax', '('.$this->name.'.read pangea ) getCdrs $response'.print_r($response, true));
      }
      catch(Exception $e)
    {
      if ($e->faultstring)
        $this->nslog('fax', '('.$this->name.'.read pangea ) getCdrs error'.print_r($e->faultstring, true));
    }


    return $results;
  }

    private function getUsers($companyID, $uid= null, $phonenumber = null, $id = null, $include_shared = false,$pPostsUser= null)
    {
      if ($id!= null)
      {
        $xmlrequest = $this->userByID;
        $xmlrequest = str_replace("{{USERID}}",$id, $xmlrequest);
      }
      else {
        $xmlrequest = $this->userByDomain;
      }
      $xmlrequest = str_replace("{{USERNAME}}",$this->auth->LOGONNAME->enc_value , $xmlrequest);
      $xmlrequest = str_replace("{{PASSWORD}}",$this->auth->PPASSWORD->enc_value , $xmlrequest);
      $xmlrequest = str_replace("{{COMPANYID}}",$companyID, $xmlrequest);

      $this->nslog('fax', '('.$this->name.'.read pangea ) getUsers $xmlrequest'. $this->redact(print_r($xmlrequest, true)));

      //print_r($xmlrequest);
//
      $headers = array(
          "Content-type: text/xml;charset=\"utf-8\"",
          "Accept: text/xml",
          "Cache-Control: no-cache",
          "Pragma: no-cache",
          "SOAPAction: UserAccount",
          "Content-length: ".strlen($xmlrequest),
      ); //SOAPAction: your op URL
//
    //  $url = $soapUrl;

      // PHP cURL  for https connection with auth
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

      curl_setopt($ch, CURLOPT_URL, $this->url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      // curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
      // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlrequest); // the SOAP request
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//
      // converting
      $response = curl_exec($ch);
      curl_close($ch);

//

 $response = str_replace("v1:","",$response);

 $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);

 try{
   $xml = new SimpleXMLElement($response);
 }catch (Exception $e)
 {
   return array();
 }

 $body = $xml->xpath('//SOAP-ENV:Body')[0];


  if ($id!= null && isset($body->ConcertService___UserAccountResponse->Result->USERS))
  {
    return $body->ConcertService___UserAccountResponse->Result->USERS;
  }

// $encoded = json_encode($body);
//   $this->nslog('fax', '('.$this->name.'.read pangea ) getUsers $encoded'.print_r($encoded, true));
//
// $encoded = str_replace('{}', '""', $encoded);
//   $this->nslog('fax', '('.$this->name.'.read pangea ) getUsers $encoded'.print_r($encoded, true));
//
//  $array = json_decode($encoded, TRUE);

$users= array();
if(isset($body->ConcertService___UserAccountResponse->Result->USERS))
{
  foreach($body->ConcertService___UserAccountResponse->Result->USERS as $u)
  {
    if ($u->COMPANYID == $companyID)
    {
      if ($phonenumber != null && ($phonenumber != $u->FAXTOEMAILNO && $phonenumber != $u->FAXTOFAXNO))
      {
          continue;
      }
      if ($uid != null && ($uid != $u->DESCRIPTION))
      {
          //We hide the shared settings in ZIP code, because why not...
          if ($include_shared && substr( $u->ZIP, 0, 7 ) ==="shared:")
          {
            $share = substr( $u->ZIP, 7 );

            if ($share=="all")
            {
              //ok
            }
            elseif (substr( $share, 0, 5 ) ==="site:")
            {
              if (isset($pPostsUser['site']) && str_replace("site:","",$share) == $pPostsUser['site'] )
              {
                // ok
              }
              else {
                continue;
              }

            }
            elseif (substr( $share, 0, 5 ) ==="dept:")
            {
              if (isset($pPostsUser['group']) && str_replace("dept:","",$share) == $pPostsUser['group'] )
              {
                // ok
              }
              else {
                continue;
              }
            }
            elseif (substr( $share, 0, 5 ) ==="user:")
            {
              $this->nslog('fax', '('.$this->name.'.read pangea ) getUsers $share '.print_r($share,true));
              $userCheck = explode("@",$uid);
              $this->nslog('fax', '('.$this->name.'.read pangea ) $userCheck $share '.print_r($userCheck,true));

              $this->nslog('fax', '('.$this->name.'.read pangea ) explode(",",str_replace("user:","",$share)) '.print_r(explode(",",str_replace("user:","",$share)),true));
              if (in_array($userCheck[0],explode(",",str_replace("user:","",$share))) )
              {
                // ok
                  $this->nslog('fax', '('.$this->name.'.read pangea ) OK?'.print_r("OK",true));
              }
              else {
                $this->nslog('fax', '('.$this->name.'.read pangea ) OK?'.print_r("NOPE",true));
                continue;
              }
            }
            else {
              continue;
            }
          }
          else {
              continue;
          }
      }

      $this->nslog('fax', '('.$this->name.'.read pangea ) getUsers $u '.print_r($u,true));

      $users[]=(array)$u;

    }

  }
}



return $users;
    }

    public function readCdrs($form)
    {

    }
    public function read($form, $count = false)
    {
        $this->nslog('fax', '('.$this->name.'.read pangea ) form '.print_r($form, true));

        if (empty($form)) {
            $form = $_REQUEST;
        }

        if (!isset($form['domain'])) {
            $this->errorResponse('400 Bad Request', 'Missing domain');
        }
        $domainId = $this->getCompanyID($form['domain']);

        if (isset($form['cdr'])) {

          $this->nslog('fax', '('.$this->name.'.read pangea ) form '.print_r($form, true));
          $this->nslog('fax', '('.$this->name.'.read pangea ) _REQUEST '.print_r($_REQUEST, true));

          if (strpos(Configure::read('NsPangeaURL'), "secure.ipfax.net:8444") === false)
            $this->errorResponse('400 Bad Request', 'CDR request invalid ');

          $uid = null;
          if (isset($form['uid']))
            $uid = $form['uid'];
          if (isset($form['user']))
            $uid = $form['user']."@".$form['domain'];
          return $this->getCdrs($form,$domainId,isset($uid)?$uid:null, isset($phonenumber)?$uid:null );
        }



        $this->nslog('fax', '('.$this->name.'.read pangea ) $domainId '.print_r($domainId, true));

        if (isset($form['user']))
          $uid = $form['user']."@".$form['domain'];
        else
          $uid = null;
        $phonenumber = null;
        if (isset($form['phonenumber']))
          $phonenumber = $form['phonenumber'] ;

        if (isset($form['include_shared']) && ($form['include_shared']=="yes" || $form['include_shared']=="1"))
          $user_list = $this->getUsers($domainId, $uid, $phonenumber,null, true, $form['userArray']);
        else
          $user_list = $this->getUsers($domainId, $uid, $phonenumber,null, false);

        $this->nslog('fax', '('.$this->name.'.read pangea ) $user_list '.print_r($user_list, true));

        $idx=0;
        $results['xml']['faxnumber']= array();
        foreach ($user_list as $user)
        {
          //$this->nslog('fax', '('.$this->name.'.read pangea ) $user '.print_r($user, true));
          $results['xml']['faxnumber'][$idx] = array();


          if (!empty($user['FAXTOEMAILNO']))
            $results['xml']['faxnumber'][$idx]['number'] = $user['FAXTOEMAILNO'];
          else if (!empty($user['FAXTOFAXNO']))
            $results['xml']['faxnumber'][$idx]['number'] = $user['FAXTOFAXNO'];
          $results['xml']['faxnumber'][$idx]['domain'] = $form['domain'];

          $uid = $user['DESCRIPTION'];
          if ($uid == null || $uid=="" || (is_array($uid) && count($uid)==0))
            $uid = $user['ADDRESS2'];
          if (!is_array($uid))
            $uidArr = explode("@",$uid);
          if (isset($uidArr[1]) && $uidArr[1]==$form['domain'])
          {
            $results['xml']['faxnumber'][$idx]['user'] = $uidArr[0];
          }
          else {
            $results['xml']['faxnumber'][$idx]['user'] = "";
          }




          if (isset($user['ATAMAC']))
          {
            $user['ata_status'] = $this->IsATAOnline($user['ATAMAC']);
          }

          $results['xml']['faxnumber'][$idx]['failure_email'] = FALSE;
          $results['xml']['faxnumber'][$idx]['success_email'] = FALSE;
          $results['xml']['faxnumber'][$idx]['failure_ata'] = FALSE;
          $results['xml']['faxnumber'][$idx]['success_ata'] = FALSE;
          $results['xml']['faxnumber'][$idx]['sharing'] = "no";

          if (isset($user) && isset($user['REPORTSENDEMAIL']))
          {

            if ($user['REPORTSENDEMAIL'] == "1" || $user['REPORTSENDEMAIL'] == "3")
              $results['xml']['faxnumber'][$idx]['failure_email'] = TRUE;
            if ($user['REPORTSENDEMAIL'] == "2" || $user['REPORTSENDEMAIL'] == "3")
              $results['xml']['faxnumber'][$idx]['success_email'] = TRUE;
            if ($user['REPORTSENDFAX'] == "1" || $user['REPORTSENDFAX'] == "3")
              $results['xml']['faxnumber'][$idx]['failure_ata'] = TRUE;
            if ($user['REPORTSENDFAX'] == "2" || $user['REPORTSENDFAX'] == "3")
              $results['xml']['faxnumber'][$idx]['success_ata'] = TRUE;
          }

          if (isset($user) && isset($user['ZIP']) && substr( $user['ZIP'], 0, 7 ) ==="shared:")
          {
            list($devnull,$results['xml']['faxnumber'][$idx]['sharing']) = explode(":", $user['ZIP'],2);
          }




          // if (isset($results['xml']['faxnumber'][$idx]['sharing']) == "site"  )
          // {
          //
          //   if ($match != $this->Session->read("sub_site") && $xSite == "*" && $this->Session->read("sub_site") == "*")
          //     continue;
          // }
          //
          // if (isset($results['xml']['faxnumber'][$idx]['sharing']) == "dept" )
          // {
          //   if ($match != $this->Session->read("sub_site") && $xSite == "*" && $this->Session->read("sub_site") == "*")
          //     continue;
          // }



          $results['xml']['faxnumber'][$idx]['pangea']= $user;

          $idx++;




        }

        if ($count)
          return count($results['xml']['faxnumber']);


        if (isset($form['start'] ) && isset($form['limit'] ))
        {
          //print_r($results['xml']['faxnumber']);
          $results['xml']['faxnumber'] = array_splice($results['xml']['faxnumber'],$form['start'],$form['limit']);
        }

        return $results;
    }

    public function count($form)
    {
      $results =  array('xml' => array('total' => $this->read($form, true)));

      return $results;
    }

    public function processRecieved()
    {
          $xmlrequest = $this->getFaxesAllUsers;
          $xmlrequest = str_replace("{{USERNAME}}",$this->auth->LOGONNAME->enc_value , $xmlrequest);
          $xmlrequest = str_replace("{{PASSWORD}}",$this->auth->PPASSWORD->enc_value , $xmlrequest);

          // print_r($xmlrequest);
          // echo "<BR>";

          $headers = array(
              "Content-type: text/xml;charset=\"utf-8\"",
              "Accept: text/xml",
              "Cache-Control: no-cache",
              "Pragma: no-cache",
              "SOAPAction: Cdr",
              "Content-length: ".strlen($xmlrequest),
          ); //SOAPAction: your op URL

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
          curl_setopt($ch, CURLOPT_URL, $this->url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          // curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
          // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
          curl_setopt($ch, CURLOPT_TIMEOUT, 10);
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlrequest); // the SOAP request
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //
          // converting
          $response = curl_exec($ch);
          curl_close($ch);
          if (!isset($response) || $response == null || $response == "")
            return;
          // print_r($response);
          // echo "<BR>";

          //$this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved $response'.print_r($response, true));

          $response = str_replace("v1:","",$response);

          $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);


          //$this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved $response'.print_r($response, true));

          try{

            $xml = new SimpleXMLElement($response);
          }catch (Exception $e)
          {
            return;
          }


          $body = $xml->xpath('//SOAP-ENV:Body')[0];

           //$this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved $body '.print_r($body, true));

           $ResultsArray = array();


$this->nslog('error', '('.$this->name.'.read pangea ) aaker0 ');
           if (isset($body->ConcertService___CDRResponse->Result->CDR[0])) {
                   $idx = 0;
                   $cntUsed = 0;
             while (isset($body->ConcertService___CDRResponse->Result->CDR[$idx]) && $cntUsed <500)
             {
                     $idx++;
             if (isset($body->ConcertService___CDRResponse->Result->CDR[$idx]))
             {
                             $this->nslog('error', '('.$this->name.'.read pangea ) aaker1 ');
                  $CACHE_ID = 'pangea_complete'.'_'.$body->ConcertService___CDRResponse->Result->CDR[$idx]->IMAGEFILEPREFIX;
        echo "$CACHE_ID check\r\n"; 
                             $this->nslog('error', '('.$this->name.'.read pangea ) aaker2 ', $CACHE_ID ); 
                     if (Cache::read($CACHE_ID, '_long_cache_')!= FALSE)
                     {
        echo "aaker3";
                             continue;
                     }
               }
               $cntUsed++;
               $ResultsArray[] = $body->ConcertService___CDRResponse->Result->CDR[$idx];
             }
           }
           else if (isset($body->ConcertService___CDRResponse->Result->CDR->MESSAGEID))
            {
              $ResultsArray[] = $body->ConcertService___CDRResponse->Result->CDR;
            }

            //$this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved ResultsArray '.print_r($ResultsArray, true));


           for ($f = 0; $f < count($ResultsArray); $f++) {

             $fax = $ResultsArray[$f];
             try{


               if (!isset($fax->USERID))
               {
                 continue;
               }
               if (isset($fax->ERRORCODE) && $fax->ERRORCODE == "Processing")
               {
                 $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved $fax '.print_r("Processing", true));
                 continue;
               }


                $CACHE_ID = 'pangea_complete'.'_'.$fax->IMAGEFILEPREFIX;

              if (Cache::read($CACHE_ID, '_long_cache_')!= FALSE)
                continue;

                $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved $fax '.print_r($fax, true));

               $AUser = $this->getUsers($fax->COMPANYID, null, null, $fax->USERID);

               $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved $fax $AUser'.print_r($AUser, true));

               if (isset($AUser) && isset($AUser->DESCRIPTION) && strpos($AUser->DESCRIPTION, "@")!== false)
               {

                 $aorArray=explode("@",$AUser->DESCRIPTION);
                 $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved $aorArr '.print_r($aorArray, true));

                 if (isset($fax->IMAGEFILEPREFIX))
                 {
                   $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved $fax->IMAGEFILEPREFIX '.print_r($fax->IMAGEFILEPREFIX, true));
                   $imagePrefix = $fax->IMAGEFILEPREFIX;
                   $pageCount = $fax->PAGES;
                   $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved $pageCount '.print_r($pageCount, true));

                   for ($x = 1; $x <= $pageCount; $x++) {
                     $image_query = new StdClass();
                     $image_query->ACred = $this->auth;
                     $image_query->ALevel = "Agent";
                     $image_query->AOperation = "Select";
                     $image_query->AFilePrefix = $imagePrefix;
                     $image_query->APage = $x;

                     $TmpName = "/usr/local/NetSapiens/netsapiens-api/tmp/$imagePrefix-$x.tiff";
                     $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved page '.$pageCount.' $TmpName '.print_r($TmpName, true));
                     if (!is_file($TmpName))
                     {
                       try{
                         $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved page '.$pageCount.' $image_query '.print_r($image_query, true));
                         $imageResponse = $this->client->FaxPage($image_query);
                         if (isset($imageResponse->Result))
                         {
                            //$this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved page '.$pageCount.' $imageResponse->Result '.print_r($imageResponse->Result, true));

                            $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved page '.$pageCount.' file_put_contents ');

                            $byteResult = file_put_contents($TmpName,$imageResponse->Result);

                            $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved page '.$pageCount.' $byteResult = '. $byteResult);

                        }
                         else {
                           $this->nslog('fax', '('.$this->name.'.read pangea ) $imageResponse->Result NOT set ');
                         }
                       }
                       catch(Exception $e)
                       {
                         $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved page '.$pageCount.' exception '.print_r($e, true));

                         //print_r($e);
                       }
                     }
                     else {
                       $this->nslog('fax', '('.$this->name.'.read pangea ) TmpName exists already? '.  $TmpName );
                     }

                   }

                   $pdfPath = "/usr/local/NetSapiens/netsapiens-api/tmp/$imagePrefix.pdf";
                   $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved  $pdfPath '.print_r($pdfPath, true));
                   if (!file_exists ($pdfPath))
                   {
                     $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved  converting '.print_r($pdfPath, true));
                     $good=false;
                     $execCmd = "/usr/bin/tiffcp ";
                     for ($x = 1; $x <= $pageCount; $x++) {
                       $TmpName = "/usr/local/NetSapiens/netsapiens-api/tmp/$imagePrefix-$x.tiff";
                       if (file_exists ($TmpName))
                       {
                         $execCmd .= " ". escapeshellarg($TmpName);
                         $good=true;
                       }
                     }
                     $mergedTiff ="/usr/local/NetSapiens/netsapiens-api/tmp/$imagePrefix.tiff";

                     $execCmd .= " ". escapeshellarg($mergedTiff). " 2> /dev/null";

                     $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved  $execCmd '.print_r($execCmd, true));
                     echo "$execCmd \r\n";
                     $output=null;
                     $retval=null;

                     exec($execCmd,$output,$retval);
                     $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved  $retval '.print_r($retval, true));
                     $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved  $output '.print_r($output, true));
                     echo "Returned with status $retval and output:\n";
                     print_r($output);

                     $execCmd = "/usr/bin/tiff2pdf ". escapeshellarg($mergedTiff). " -o " . escapeshellarg($pdfPath);
                     $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved  $execCmd '.print_r($execCmd, true));
                     echo "$execCmd \r\n";
                     $output=null;
                     $retval=null;

                     exec($execCmd,$output,$retval);
                     $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved  $retval '.print_r($retval, true));
                     $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved  $output '.print_r($output, true));
                     echo "Returned with status $retval and output:\n";
                     print_r($output);


                   }
                   else {
                     echo "EXISTS1! $pdfPath";
                   }
                 }


                 if (file_exists ($pdfPath))
                 {

                   if (empty($fax->EMAILDESTINATION))
                    $this->nslog('fax', '('.$this->name.'.processSending)  empty($fax->EMAILDESTINATION) ' );
                  else {
                    $this->nslog('fax', '('.$this->name.'.processSending)  !empty($fax->EMAILDESTINATION) ' );
                  }

                   if (isset($fax->SUBJECT) && !empty($fax->SUBJECT) && isset($fax->FAXTYPE) && ( $fax->FAXTYPE =='ftBroadcast'  ) && substr( $fax->SUBJECT, 0, 3 ) === "fax") //&& !empty($fax->EMAILDESTINATION)
                   {
                     $this->nslog('fax', '('.$this->name.'.upload) OUTBOUND '.print_r($fax->SUBJECT, true));
                     $prefix = 'out';
                     $conditions['index'] = $prefix;
                     $fax->id = $prefix.preg_replace('/\D/', '', $imagePrefix);
                     $conditions['file'] = 'fax-out'.str_replace("fax","",$fax->SUBJECT).'.'."pdf";

                     Cache::write("sentFaxfax".$newId,$uid, '_long_cache_');

                     $existingFileInfo = $this->Audiofile->GetVmailInfo("outfax",$conditions['owner_domain'],$conditions['owner'], $this->Audiofile->GetFilename($conditions));
                     $this->nslog('fax', '('.$this->name.'.processSending)  $existingFileInfo ' . print_r($existingFileInfo, true));

                     if (isset($existingFileInfo['tx_hostname']) && $existingFileInfo['tx_hostname']!= gethostname())
                     {
                       Cache::write($CACHE_ID,true, '_long_cache_');
                       echo "$CACHE_ID write\r\n";
                       continue;
                     }
                   }
                   else {
                     $prefix = '';
                      $fax->id = $prefix.preg_replace('/\D/', '', $imagePrefix);
                     //$conditions['index'] = $prefix;
                     $conditions['file'] = 'fax-'.$fax->id.'.'."pdf";


                     if (isset($fax->ORIGPHONE))
                     {
                       $conditions['NmsAni']=str_replace("*","",str_replace("+","",$fax->ORIGPHONE));
                       $conditions['from_number']=str_replace("*","",str_replace("+","",$fax->ORIGPHONE));
                     }
                     else if (isset($fax->DESFAXNUM[0]))
                     {
                       $conditions['NmsAni']=str_replace("*","",$fax->DESFAXNUM[0]);
                       $conditions['from_number']=str_replace("*","",$fax->DESFAXNUM[0]);
                     }
                     else {
                       $conditions['NmsAni']=str_replace("*","",$fax->DESFAXNUM);
                       $conditions['from_number']=str_replace("*","",$fax->DESFAXNUM);
                     }

                   }


                   $conditions['tmp_name'] = $pdfPath;
                   $conditions['object'] = 'audio';
                   $conditions['type'] = 'pdf';


                   if (isset($fax->SUBJECT) && Cache::read("sentFax".$fax->SUBJECT, '_long_cache_')!== FALSE)
                   {
                     $aorArray=explode("@",Cache::read("sentFax".$fax->SUBJECT, '_long_cache_'));
                     $this->nslog('fax', '('.$this->name.'.upload) found sentFax Uid $aorArray'.print_r($aorArray, true));
                   }

                   if (!isset($aorArray) || !isset($aorArray[0]) || !isset($aorArray[1]))
                   {
                     $this->nslog('fax', '('.$this->name.'.upload) $aorarray is not set or couldn\'t decode');
                     continue;
                   }

                   $conditions['owner'] = $aorArray[0];
                   $conditions['owner_domain'] = $aorArray[1];

                   if (!isset($this->Subscriber))
                   {
                     APP::import('Model', 'Subscriber');
                     $this->Subscriber = new Subscriber();
                   }

                   if (isset($this->Subscriber) && $this->Subscriber->getLoginFromUid($conditions['owner']."@". $conditions['owner_domain']) == null) {
                    $this->nslog('fax', '('.$this->name.'.upload) subscriber is not found lookup was '. print_r($conditions, true));
                     continue;
                    }

                   if (isset($this->Subscriber) && !$this->Subscriber->canAcceptFiles($conditions['owner'], $conditions['owner_domain'])) {
                    $this->nslog('fax', '('.$this->name.'.processRecieved) insufficient space for user to recieve fax '.print_r($conditions, true));
                    $conditions['caller_id'] = $conditions['from_number'];
                    Cache::write($CACHE_ID,true, '_long_cache_');
                    echo "$CACHE_ID write2\r\n";
                    $this->_emailFax($aorArray[0].'@'.$aorArray[1],null,$conditions,'storage_full',false);
                    continue;
                   }

                   $this->nslog('fax', '('.$this->name.'.upload) conditions '.print_r($conditions, true));


                   $conditions['fax'] = (array) $fax;

                   if (isset($fax->ERRORCODE))
                   {
                     $conditions['fax']['status']=$conditions['fax']['ERRORCODE'];
                     $conditions['status']=$conditions['fax']['ERRORCODE'];
                   }

                  if (isset($fax->PAGES))
                  {
                    $conditions['fax']['num_pages']=$conditions['fax']['PAGES'];
                    $conditions['num_pages']=$conditions['fax']['PAGES'];
                  }

                  $conditions['status'] = str_replace("Fax to Email","success",$conditions['status'] );
                  $conditions['fax']['status'] = str_replace("Fax to Email","success",$conditions['fax']['status']);
                  $conditions['status'] = str_replace("Sent to ATA","success",$conditions['status'] );
                  $conditions['fax']['status'] = str_replace("Sent to ATA","success",$conditions['fax']['status']);
                  $conditions['status'] = str_replace("Delivered","success",$conditions['status'] );
                  $conditions['fax']['status'] = str_replace("Delivered","success",$conditions['fax']['status']);

                   // $conditions['fax']['transcription']=$mesg;
                   $this->nslog('fax', '('.$this->name.'.upload) conditions '.print_r($conditions, true));



                   $this->nslog('fax', '('.$this->name.'.upload) $prefix pre addImage '.print_r($prefix, true));


                   if ($prefix == "out")
                   {
                     unset($conditions['fax']['NmsAni']); 
                     unset($conditions['fax']['from_number']); 
                     unset($conditions['NmsAni']); 
                     unset($conditions['from_number']); 
                     unset($conditions['FromUser']); 
                     $this->nslog('fax', '('.$this->name.'.upload) $prefix pre addImage prefix in');

                     $newPdf = $this->Audiofile->addImage($conditions,true);
                   }
                  else
                    $newPdf = $this->Audiofile->addImage($conditions);


                  $pdfLocation = $this->Audiofile->GetMediaDirectory($conditions). "/" .$this->Audiofile->GetFilename($conditions);

                  $this->nslog('fax', '('.$this->name.'.upload) $pdfLocation '.print_r($pdfLocation, true));


                  $conditions['file'] = str_replace(".pdf",".jpg",$conditions['file']);
                  $conditions['tmp_name'] = '/usr/local/NetSapiens/netsapiens-api/tmp/'.$conditions['file'];

                  $this->nslog('fax', '('.$this->name.'.upload) pre generate_jpg '.print_r($conditions, true));

                  $jpg_status = $this->generate_jpg($pdfLocation, $conditions['tmp_name']);


                  if ($jpg_status!== FALSE)
                  {
                    $this->nslog('fax', '('.$this->name.'.upload) $jpg_status sucess ');

                    if ($prefix == "out")
                     $newPdf = $this->Audiofile->addImage($conditions,true);
                    else
                     $newPdf = $this->Audiofile->addImage($conditions);
                  }
echo "$CACHE_ID write3\r\n";
                  Cache::write($CACHE_ID,true, '_long_cache_');

                 }
                 else {
                   $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved  $pdfPath NOT EXISTS BAD?'.print_r("", true));

                   //TODO: add status only update;
                 }

               }
               else {
                       echo "$CACHE_ID write4\r\n";
                       Cache::write($CACHE_ID,true, '_long_cache_');
                $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved no user '.print_r("", true));
               }


             }
             catch(Exception $e)
             {
               print_r($e);
               $this->nslog('fax', '('.$this->name.'.read pangea ) processRecieved no user $e'.print_r($e, true));

             }

           }

           exit;





    }






}
