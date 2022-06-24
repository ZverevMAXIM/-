<?php
session_start();

$conn = mysqli_connect('localhost', "polik", "345polik34324rr", "polik");
if(mysqli_connect_error()){
    echo "Connection Error";
    die();
}
mysqli_set_charset($conn, "utf8mb4");

class AuthClass {

    /**
     * Проверяет, авторизован пользователь или нет
     * Возвращает true если авторизован, иначе false
     * @return boolean 
     */
    public function isAuth() {
        if (isset($_SESSION["is_auth"])) { //Если сессия существует
            return $_SESSION["is_auth"]; //Возвращаем значение переменной сессии is_auth (хранит true если авторизован, false если не авторизован)
        }
        else return false; //Пользователь не авторизован, т.к. переменная is_auth не создана
    }
     
    /**
     * Авторизация пользователя
     * @param string $login
     * @param string $passwors 
     */
    public function auth($login, $pass) {
        global $conn;
		
		$query = "SELECT * FROM users WHERE login='$login' AND pass='$pass'";
		$result = mysqli_query($conn, $query);
		$user = mysqli_fetch_assoc($result);
		
		if (!empty($user)) {
            $_SESSION["is_auth"] = true; //Делаем пользователя авторизованным
            $_SESSION["login"] = $login; //Записываем в сессию логин пользователя
            $_SESSION["id"] = $user["id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["familiya"] = $user["familiya"];
            $_SESSION["mail"] = $user["mail"];
            return true;
		} else {
            $_SESSION["is_auth"] = false;
            return false; 
		}
    }
     
    /**
     * Метод возвращает логин авторизованного пользователя 
     */
    public function getInfo() {
        if ($this->isAuth()) { //Если пользователь авторизован
            return array (
                "id" => $_SESSION["id"],
                "login" => $_SESSION["login"],
                "name" => $_SESSION["name"],
                "familiya" => $_SESSION["familiya"],
                "mail" => $_SESSION["mail"]
            );
        }
    }
     
     
    public function out() {
        $_SESSION = array(); //Очищаем сессию
        session_destroy(); //Уничтожаем
    }

    public function reg($login, $pass, $mail, $name, $familiya, $pass2){
        global $conn;

        if ($pass != $pass2){
            return "Пароль и подтверждение пароля не совпадают!";
        }

        if (!$login || !$pass || !$pass2 || !$mail || !$name || !$familiya){
            return "Не все поля заполнены!";
        }

       
        try {
            $query = "INSERT INTO `users` (`login`,`mail`,`pass`,`name`,`familiya`) VALUES ('$login','$mail','$pass','$name', '$familiya');";          
            mysqli_query($conn, $query);
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                return "Логин или email уже используются!";
            } else {
                throw $e;// in case it's any other error
            }
        }


        return ""; 
    }

    public function send($user_id, $mess){
        global $conn;
        $query = "INSERT INTO `mails` ( `user_id`, `txt`) VALUES ('$user_id', '$mess');";          
        mysqli_query($conn, $query);
    }
}
 
$auth = new AuthClass();

$error = "";
$message = "";

if (isset($_POST["auth"])) { 
    if (!$auth->auth($_POST["login"], $_POST["password"])) { //Если логин и пароль введен не правильно
        $error = "Логин или пароль введен не правильно!";
    }
}

//если регистрация
if (isset($_POST["reg"])){
    $error = $auth->reg($_POST["login"], $_POST["password"], $_POST["mail"], $_POST["name"], $_POST["familiya"], $_POST["password2"]);

    if (!$error){
        $auth->auth($_POST["login"], $_POST["password"]);
    }
}

//если отправка письма
if (isset($_POST["send"])){
    $cur_user = $auth->getInfo();
    $auth->send($cur_user["id"], $_POST["mess"]);
    $message = "Ваше сообщение отправлено!";

}
 
if (isset($_GET["is_exit"])) { //Если нажата кнопка выхода
    if ($_GET["is_exit"] == 1) {
        $auth->out(); //Выходим
        header("Location: ?is_exit=0"); //Редирект после выхода
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<title>Поликлинника</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap">

    <div class="header">
        <div>
            <img src="krest1.gif" >
        </div>
        <div id="zagl">Поликлинника</div>
        <div>
            <img src="hear.jpeg" >
        </div>
    </div>

    <div class="content">
        <div class="left_part">
            <h2>Меню:</h2>
            <ul>
                <li><a href="#hello">Приветствие</a></li>
                <li><a href="#news">Новости</a></li>
                <li><a href="#feed">Обратная связь</a></li>
            </ul>

            <br /><br />
            <?php if (!$auth->isAuth()) { ?> 
                <h2>Вход</h2>
<form method="post" action="">
    Логин: 
    <br/><input type="text" name="login" 
    value="<?php echo (isset($_POST["login"])) ? $_POST["login"] : null; // Заполняем поле по умолчанию ?>" />
    <br/>
    Пароль:
    <br/> <input type="password" name="password" value="" /><br/>
    <input type="submit" value="Войти" name="auth"/>
</form>
<br /><br />
                <?php } ?> 
        </div>

        <div class="center_part">
            <div id="hello">
                <h2>Приветствие</h2>
                <br />
                Уважаемые пациенты! <br />
   Министерство здравоохранения Российской Федерации <br />
   П А М Я Т К А <br />
   для граждан <br />
   о гарантиях бесплатного оказания медицинской помощи <br />
   <br /> <br />
   В соответствии со статьей 41 Конституции Российской Федерации каждый гражданин имеет право на охрану здоровья и бесплатную медицинскую помощь, оказываемую в гарантированном объеме без взимания платы в соответствии с Программой государственных гарантий бесплатного оказания гражданам медицинской помощи (далее – Программа), ежегодно утверждаемой Правительством Российской Федерации.
   Основными государственными источниками финансирования Программы являются средства системы обязательного медицинского страхования и бюджетные средства. <br />
   На основе Программы субъекты Российской Федерации ежегодно утверждают территориальные программы государственных гарантий бесплатного оказания медицинской помощи (далее – территориальные программы).
   <br /> <br />
            </div>

            <div id="news">
                <h2>Новости</h2>
                <br />
                <i>16.06.2022</i><br /> <br />
                Уважаемые медицинские работники и ветераны здравоохранения! <br />
   Примите искренние поздравления с профессиональным праздником – Днём медицинского работника! <br />
   Ваш ежедневный непростой труд приносит людям здоровье, счастье, спасает жизни. Ваша профессия сложна и ответственна, а труд требует полной отдачи сил, опыта, знаний, душевной щедрости. Человеколюбие, самоотверженность, высокая ответственность — вот те черты характера, которые ежедневно проявляются в вашей работе. Ваши умелые руки, неравнодушные сердца творят добро, а порой и подлинные чудеса. <br />
   В этот праздничный день разрешите выразить вам искреннюю признательность за ваш добросовестный труд, за доброту и внимание, готовность прийти на помощь! От всего сердца желаю всем благополучия, стабильности и, самое главное, того, что вы так щедро даете людям, – здоровья! Мира и добра вам и вашим семьям! <br />
   Пусть ваши забота и теплота всегда отзываются в сердцах пациентов и возвращаются к вам, согревая в любой жизненной ситуации. Пусть никогда вам не придется усомниться в той пользе, которую вы приносите людям. Пусть вся ваша дальнейшая деятельность ознаменуется новыми успехами и достижениями! Огромное спасибо за спасенные жизни и верность избранной профессии! <br /> <br />
   <i>10.06.2022</i><br />
   Уважаемые медицинские работники и ветераны здравоохранения! <br />
   Приглашаем вас на торжественное мероприятие, посвященное профессиональному празднику-Дню медицинского работника, которое состоится 17 июня с 15 часов в Доме культуры. <br /> <br />
            </div>
            <div id="feed">

                <h2>Обратная связь</h2>
                <br />
                Если вы недовольны работой нашей поликлиники, то отправьте нам сообщение
                <br />

<?php if ($auth->isAuth()) { ?>
                <form method="post" action="">
                    <textarea name="mess" placeholder="Ваше обращение в поликлиннику" rows="6"></textarea>
                    <br />
                    <input type="submit" value="Отправить" name="send" />
                </form>
                <?php } else { ?>   
                    <br />
                <br />      
                    Чтобы отправить сообщение необходимо войти на сайт или зарегистрироваться!
                    <?php }  ?>    
                <br />
                <br />
            </div>
        </div>

        <div class="right_part">
        <?php
if ($error){
    echo "<div class='error'>".$error."</div>";
}
if ($message){
    echo "<div class='ok'>".$message."</div>";
}

if ($auth->isAuth()) { // Если пользователь авторизован, приветствуем:  
    $cur_user = $auth->getInfo();

    echo "Логин: <b>" . $cur_user["login"]."</b><br/>";
    echo "Имя: <b>". $cur_user["name"]."</b><br/>";
    echo "Фамилия: <b>" . $cur_user["familiya"]."</b><br/>";
    echo "Email: <b>" . $cur_user["mail"]."</b><br/>";
?>



<?php
    echo "<br/><br/><a href='?is_exit=1' id='logaut'>Выход</a>"; //Показываем кнопку выхода
} 
else { //Если не авторизован, показываем форму ввода логина и пароля
?>
<h2>Регистрация</h2>
<form method="post" action="">
    Логин<br/> 
    <input type="text" name="login" 
    value="<?php echo (isset($_POST["login"])) ? $_POST["login"] : null; // Заполняем поле по умолчанию ?>" />
    <br/><br/>
    Пароль:<br/>
     <input type="password" name="password" value="" /><br/>
    <br/>
    Подтвердите пароль:
    <br/> <input type="password" name="password2" value="" /><br/>
    <br/>
    mail:
    <br/> <input type="email" name="mail" value="" /><br/>
    <br/>
    Имя:
    <br/> <input type="text" name="name" value="" /><br/>
    <br/>
    Фамилия: 
    <br/><input type="text" name="familiya" value="" /><br/>
    <input type="submit" value="Зарегистрироваться" name="reg"/>
</form>

<?php 
}
?>

        </div> 
    </div>

    <div class="footer">
    © Copyright 2022, Сайт для курсовой, не является реальным проектом
    </div>

</div>

</body>
</html>