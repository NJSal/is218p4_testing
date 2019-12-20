<?php

require('ModelPDO/pdo.php');
require('ModelPDO/pdomethods.php');
require('ModelPDO/helperfunctions.php');

session_start();

$action = filter_input(INPUT_POST, 'action');
if($action == NULL){
    $action = filter_input(INPUT_GET, 'action');
    if($action == NULL){
        $action = 'log_in';
    }
}

switch($action){
    case 'log_in': {
        include('views/login.php');
        break;
    }

    case 'validate_login':
    {
        $email = filter_input(INPUT_POST, 'email');
        $password = filter_input(INPUT_POST, 'password');
        if ($email == NULL || $password == NULL) {
            $error = "Email and Password are required";
            include('errors/error.php');
        } else {
            $userId = validate_login($email, $password);
            if ($userId !== NULL) {                 /* 12/18/19: **********ADDED******** */
                $_SESSION["ucid"] = $userId;
                $_SESSION["logged"] = true;                                                  //action tells controller what to do
                header("Location: .?action=display_questions&userId=$userId");
            }
            else {
                header("location: .?action=display_registration");

            }
        }
        break;
    }

    case 'display_registration':
    {
        include('views/registration.php');
        break;
    }

    case 'register': {
        $firstname = filter_input(INPUT_POST, 'fname');
        $lastname = filter_input(INPUT_POST, 'lname');
        $birthday = filter_input(INPUT_POST, 'birthday');
        $email = filter_input(INPUT_POST, 'email');
        $password = filter_input(INPUT_POST, 'password');

        if ($firstname == NULL || $lastname == NULL || $birthday == NULL || $email == NULL || $password == NULL) {
            $error = "Missing a field";
            echo $error;
        }
        else{
            registeruser($email, $firstname, $lastname, $birthday, $password);
            header("Location: .?action=log_in");

            /*
            echo "User ID: $register";
            if($register == false){
                header("Location: .?action=display_login");
            }
            else{
                $userId = validate_login($email,$password);
                header("Location: .?display_questions&userId=$userId");
            */
            }
        break;
    }




    case 'display_questions': {
        $userId = filter_input(INPUT_GET, 'userId');
        if ($userId == NULL || $userId < 0) {
            header('Location: .?action=display_login');
        } else {
            $questions = get_users_questions($userId);
            $username = get_name($userId);
            include('views/display_questions.php');
        }
        break;
    }

    case 'display_question_form': {
        $userId = filter_input(INPUT_GET, 'userId', FILTER_VALIDATE_INT);

        $_SESSION['userId'] =$userId;

        //$emailVal = get_email($userId);                 //////////
        //name and last name?
        include('views/question_form.php');             //includes the form
        break;
    }

    case 'display_single_question':{
        //$userId = $_SESSION['userId'];
        $userId = filter_input(INPUT_GET, 'userId', FILTER_VALIDATE_INT);
        $questionId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if($questionId == NULL){
            $error = 'no such question id';
            include('errors/error.php');
        }

        else{
            $questions = one_question($questionId);
            include('views/display_single.php');
        }
        break;

    }

    case 'display_allusersquest':{
        $userId = filter_input(INPUT_POST, 'userId', FILTER_VALIDATE_INT);
        if($userId == NULL) {
            $error = 'no such user id';
            include('errors/error.php');
        }
        else{
            $questions = every_question($userId);
            include('views/display_allusersquest.php');
        }
        break;
    }



    case 'createquestion':{

        //session_start();
        $userId = $_SESSION['userId'];

        $questionname = filter_input(INPUT_POST, 'questioname');
        $questionbody = filter_input(INPUT_POST, 'questionbody');
        $questionskills = filter_input(INPUT_POST, 'questionskills');

        $skills = explode(',', $questionskills);

        if (($questionbody == NULL || strlen($questionbody) >= 500)|| $questionname == NULL || $questionskills == NULL || count($skills) < 2) {
            $error = "fields are empty & question body needs to be less than 500 words";
            echo $error;
        }
        else{
            add_question($userId, $questionname, $questionbody, $questionskills);
            header("Location: .?action=display_questions&userId=$userId");
        }
        break;
    }


    case 'delete_question': {
        $questionId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);           //
        $userId = filter_input(INPUT_POST, 'userId', FILTER_VALIDATE_INT);


        delete_question($questionId );
        header("Location: .?action=display_questions&userId=$userId");
        break;
    }

    case 'logoutuser':
    {
        session_destroy();
        header('Location:.');
        break;
    }

    default: {
        $error = 'Unknown Action';
        include('errors/error.php');
    }

}
?>

