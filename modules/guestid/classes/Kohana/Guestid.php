<? defined('SYSPATH') or die('No direct script access.');

class Kohana_Guestid { 

public $guest_id = -1;

	public static function factory(array $config = array()) 
	{
		return new Guestid($config); 
	}

	public function __construct(array $config = array()) 
	{
		$this->config = Kohana::$config->load('guestid')->as_array(); 
	}


	
	public function renew ()
	{
	$g_id = false;

	if ($this->get_id_from_session() )
	 {
	 $this->guest_id = $this->get_id_from_session();
	 return true;
	 }
	
	if ($this->get_id_from_cookie() )
	 {
	 $this->guest_id = $this->get_id_from_cookie();
	 return true;
	 }

	$this->guest_id = $this->get_new_id();
	return true;
	}
	
	
	public function get_id()
	{
	if ($this->guest_id > 0)
	 {
	return $this->guest_id;
	 } else
	 {
	$this->renew();
	return $this->guest_id;	 
	 }
	}

//-------------------------

private function get_id_from_session()
{
$session = Session::instance();
$guest_id = $session->get('guest_id');
$md5sum = $session->get('guest_md5sum');

if ($this->check_id($guest_id,$md5sum))
{
return $guest_id;
} else
{
return false;
}
}

//----------------------------------
private function get_id_from_cookie()
{
$guest_id = Cookie::get('guest_id');
$md5sum = Cookie::get('guest_md5sum');

if ($this->check_id($guest_id,$md5sum))
{
if ($this->config["expiration"])
{
Cookie::$expiration = $this->config["expiration"];
} else
{
Cookie::$expiration = 2419200;
}

Cookie::set('guest_id', $guest_id);
Cookie::set('guest_md5sum', $md5sum);
return $guest_id;
} else
{
return false;
}
}

//----------------------------------------------
private function check_id($guest_id,$md5sum)
{

if (($guest_id <> '') && ($md5sum <> ''))
{

$sql = "select id from guest_id where id = :guest_id and md5sum = :md5sum";
$query = DB::query(Database::SELECT, $sql);
$query->param(':guest_id', $guest_id);
$query->param(':md5sum', $md5sum);
$res = $query->execute();
if ($res->count() > 0 )
  {
  $sql = "update  guest_id set last_dt = UNIX_TIMESTAMP() where id = :guest_id";
  $query = DB::query(Database::UPDATE, $sql);
  $query->param(':guest_id', $guest_id);
  $query->execute(); 
  return true;
  } else  return false;

} else return false;




}



//-------------------------------------------------------
private function get_new_id()
{

$sql = "insert into guest_id values (null, :md5sum , UNIX_TIMESTAMP(),UNIX_TIMESTAMP())";
$md5sum = md5(date('dmY').'-mega-'.rand(0,1000));
$query = DB::query(Database::INSERT, $sql);
$query->param(':md5sum', $md5sum);
$query->execute();
$sql = "select LAST_INSERT_ID() as guest_id from dual";
$query = DB::query(Database::SELECT, $sql);
$res = $query->execute();
$res = $res->as_array();
$guest_id =  $res[0]['guest_id'];

$session = Session::instance();

$session->set('guest_id', $guest_id);
$session->set('guest_md5sum', $md5sum);

if ($this->config["expiration"])
{
Cookie::$expiration = $this->config["expiration"];
} else
{
Cookie::$expiration = 2419200;
}

Cookie::set('guest_id', $guest_id);
Cookie::set('guest_md5sum', $md5sum);

return $guest_id;
}




}
?>