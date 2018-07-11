<?php 
require dirname(dirname(__FILE__)).'/vendor/autoload.php';
require_once dirname(dirname(__FILE__)).'/config/config.php';

class Users
{
    protected $id;
    protected $email;
    protected $password;
    protected $status;
    protected $rebate;
    protected $dateOpenAccount;
    protected $codeAutoLogin;
    protected $codeActivation;

    public function getId() { return $this->id; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getStatus() { return $this->status; }
    public function getRebate() { return $this->rebate; }
    public function getDateOpenAccount() { return $this->dateOpenAccount; }
    public function getCodeAutoLogin() { return $this->codeAutoLogin; }
    public function getCodeActivation() { return $this->codeActivation; }
    
    function __construct($id = null)
    {
        if ($id != null) {
            $this->id = $id;
            $this->loadUser();
        }
    }

    public function loadUser()
    {
        $database = dbCon();
        $sql = $database -> select("obrazomat_users", '*', ['id' => $this->id]);
        foreach ($sql as $v) {
            $this->id = $v['id'];
            $this->email = $v['email'];
            $this->password = $v['password'];
            $this->status = $v['status'];
            $this->rebate = $v['rebate'];
            $this->dateOpenAccount = $v['date_open_account'];
            $this->codeAutoLogin = $v['code_auto_login'];
            $this->codeActivation = $v['code_activation'];
        }
    }

    public function register($data)
    {
        $database = dbCon();
        $this->email = htmlspecialchars(strip_tags(trim($data['email'])));
        $this->password = htmlspecialchars(strip_tags(trim($data['password'])));
        $rePassword = htmlspecialchars(strip_tags(trim($data['re-password'])));
        if (empty($this->email) || $this->email == "") {
            throw new Exception("Adres e-mail jest wymagany.");
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Wpisz poprawny adres email.");
        }
        if ($database -> has("obrazomat_users", ['email' => $this->email])) {
	    	throw new Exception('Podany email jest zajęty.');
        }
        if (empty($this->password) || $this->password == "") {
            throw new Exception('Wpisz hasło.');
        }
        if (empty($rePassword) || $rePassword == "") {
            throw new Exception('Wpisz hasło.');
        }
        if (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,15}$/',$this->password)) {
            throw new Exception('Hasło musi zawierać od 6 do 15 znaków i zawierać przynajmniej jedną cyfrę, jedną małą i dużą literę.');
        }
        if ($this->password != $rePassword) {
            throw new Exception('Powtórz hasło wpisane wyżej.');
        }
        if (!isset($data['check'])) {
            throw new Exception("Wymagana jest akceptacja regulaminu.");
        }
        $this->codeActivation = uniqid();
        if (!$database -> insert("obrazomat_users", [
            "email" => $this->email,
            "password" => password_hash($this->password, PASSWORD_BCRYPT),
            "status" => 0,
            "rebate" => 0,
            "date_open_account" => date("Y-m-d H:i:s"),
            'code_activation' => $this->codeActivation,
            'code_auto_login' => 0
            
        ])) {
            throw new Exception("Nie udało się otworzyć konta. Spróbuj jeszcze raz.");
        }
        $id= $database->id();
        $this->id = $id;
        $this->activationEmail();
        return $id;
    }

    public function activationEmail()
    {
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        ); 

        $link = file_get_contents("https://". $_SERVER['HTTP_HOST'] .'/templates/mail_aktywacyjny.html.php?id='.$this->id .'&code=' .$this->codeActivation,false, stream_context_create($arrContextOptions) ); 								
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        // $mail->isSMTP(); 
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('no-reply@xyz.pl', 'xyz');
        $mail->addAddress($this->email);
        $mail->Subject = 'Aktywacja konta.';
        $mail->msgHTML( $link ); 
        $mail->send();
    }

    public function activeAccount()
    {
        $database = dbCon();
        if(!$database->has("obrazomat_users", ["AND" => ['id'=> $this->id, 'code_activation' => $this->codeActivation, 'status' => 0 ]])) {
            throw new Exception("");
        }
        if (!$database->update("obrazomat_users", ['status' => 1], ['id' => $this->id ])) {
             throw new Exception("");
        }
    }

    public function login($data)
    {
        $database=dbCon();
        $email = htmlspecialchars(strip_tags(trim($data['email-login'])));
        if (empty($email) || $email == "") {
            throw new Exception("Adres e-mail jest wymagany.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Wpisz poprawny adres email.");
        }
        if (!$database -> has("obrazomat_users",['email'=> $email])) {
            throw new Exception("Błędny login lub hasło.");
        }
        if (!$database->has("obrazomat_users", ["AND" => ['email'=> $email, 'status' => 1]])) {
            throw new Exception("Konto nie jest aktywne.");
        }
        $dataPassword = htmlspecialchars(strip_tags(trim($data['password'])));
        $password = $database->get("obrazomat_users",'password',['email' => $email]);

        if (!password_verify($dataPassword, $password)) {
            throw new Exception("Błędny login lub hasło.");
        }
        $this->id = $database->get("obrazomat_users",'id',['email' => $email]);

        if(isset($data['remember'])){
            $code = uniqid();
            $database->update("obrazomat_users", ['code_auto_login' => $code], ['id' => $this->id]);
            setcookie('remember', $code, time() + (86400 * 30), "/");
        }
    }

    public function autoLogin($code)
    {
		$database = dbCon();
		$user = $database -> select("obrazomat_users", '*', ['code_auto_login' => $code]);
		if(!empty($user)){
			$this->id = $user[0]['id'];
			$this->loadUser();
			return true;
		}else{
			return false;
		}
    }

    public function sendNewPassword($data)
    {
        $database = dbCon();
        $password = uniqid() .'Y';
        $email = htmlspecialchars(strip_tags(trim($data['email'])));
        if (!$database -> has("obrazomat_users", ['email' => $email])) {
                throw new Exception("E-mail nie został wysłany, proszę podać adres email na który zostało zarejestrowane konto.");
        }
        $id = $database->get("obrazomat_users", 'id', ['email' => $email]);
        if (!$database->update("obrazomat_users", [
                "password" => password_hash($password, PASSWORD_BCRYPT)
                ],['id' => $id])) {
            throw new Exception("Nie udało się zresetować hasła. Spróbuj jeszcze raz.");
        }

        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        ); 
        $link = file_get_contents("https://". $_SERVER['HTTP_HOST'] .'/templates/mail_haslo.html.php?code=' . $password,false, stream_context_create($arrContextOptions) ); 								
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        // $mail->isSMTP(); 
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('no-reply@xyz.pl', 'xyz.PL');
        $mail->addAddress($email);
        $mail->Subject = 'Resetowanie hasła. xyz.pl';
        $mail->msgHTML( $link ); 
        $mail->send();
    }

    public function changeEmailData($data)
    {
        $database = dbCon();
        $email = htmlspecialchars(strip_tags(trim($data['email'])));
        $newEmail = htmlspecialchars(strip_tags(trim($data['newEmail'])));
        if (empty($email) || $email == "") {
            throw new Exception('Wpisz adres e-mail.');
        }
        if (empty($newEmail) || $newEmail == "") {
            throw new Exception('Wpisz adres e-mail.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Wpisz poprawny adres e-mail.");
        }
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Wpisz poprawny adres e-mail.");
        }
        if ($email != $this->email ) {
            throw new Exception("Wpisz poprawny aktualny adres e-mail." . $this->email);
        }
        if ($newEmail == $this->email ) {
            throw new Exception("E-mail powinnien różnić się od obecnie używanego.");
        }
        if ($database -> has("obrazomat_users", ['email' => $newEmail])) {
            throw new Exception("Podany e-mail jest zajęty.");
        }
        if (!$database -> update("obrazomat_users",
            ["email" => $newEmail],
            ['id' => $this->id])){
                throw new Exception("Wystąpił błąd podczas aktualizacji danych.");
        } 
    }

    public function changePasswordData($data)
    {
        $database = dbCon();
        $oldPassword = htmlspecialchars(strip_tags(trim($data['oldPassword'])));
        $newPassword = htmlspecialchars(strip_tags(trim($data['newPassword'])));
        $newRePassword = htmlspecialchars(strip_tags(trim($data['newRePassword'])));
        if (empty($oldPassword) || $oldPassword == "") {
            throw new Exception('Wpisz aktualne hasło.');
        }
        if (empty($newPassword) || $newPassword == "") {
            throw new Exception('Wpisz nowe hasło.');
        }
        if (empty($newRePassword) || $newRePassword == "") {
            throw new Exception('Powtórz nowe hasło.');
        }
        if (!password_verify($oldPassword, $this->password)) {
            throw new Exception("Aktualne hasło nie jest prawidłowe.");
        }
        if (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,15}$/',$newPassword)) {
            throw new Exception('Hasło musi zawierać od 6 do 15 znaków i zawierać przynajmniej jedną cyfrę, jedną małą i dużą literę.');
        }
        if ($newPassword != $newRePassword) {
            throw new Exception('Nowe hasło nie jest identyczne.');
        }
        if (password_verify($newPassword, $this->password)) {
            throw new Exception("Nowe hasło musi różnić się od starego.");
        }
        if (!$database->update("obrazomat_users", [
            "password" => password_hash($newPassword, PASSWORD_BCRYPT)
        ],["id" => $this->id])) {
            throw new Exception("Wystąpił błąd podczas aktualizacji danych.");
        }
    }
}
