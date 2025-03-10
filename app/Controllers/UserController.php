<?php

class UserController
{
    public function home()
    {
        $user_roles = Controller::accessCheck(["admin", "organization", "registered_user", "guest_user"]);
        if ($user_roles["admin"])
            Controller::redirect("/Admin/dashboard");
        else if ($user_roles["organization"])
            Controller::redirect("/Organisation/dashboard");
        elseif ($user_roles["registered_user"] || $user_roles["guest_user"])
            View::render("home", [], $user_roles);
    }

    public function profile()
    {
        $user_roles = Controller::accessCheck(["admin", "organization", "registered_user"]);
        if ($user_roles["admin"])
            Controller::redirect("/Admin/profile");
        else if ($user_roles["organization"])
            Controller::redirect("/Organisation/profile");
        elseif ($user_roles["registered_user"] || $user_roles["guest_user"])
            Controller::redirect("/RegisteredUser/profile");
    }

    public function getController()
    {
        $user_roles = Controller::accessCheck(["admin", "organization", "registered_user"]);
        if ($user_roles["admin"])
            $controller = "Admin";
        else if ($user_roles["organization"])
            $controller = "Organisation";
        elseif ($user_roles["registered_user"] || $user_roles["guest_user"])
            $controller = "RegisteredUser";
        return $controller;
    }

    public function updatePassword()
    {
        Controller::validateForm(["current_password", "new_password", "password"]);
        $controller = $this->getController();
        $user = new $controller();
        $validate = new Validation();
        $uid = $_SESSION["user"]["uid"];
        $data = ["uid" => $_POST['uid'], "password" => password_hash($_POST['password'], PASSWORD_DEFAULT)];
        if (!(new User)->checkCurrentPassword($uid, $_POST['current_password'])) {
            $error["currentpassworderr"] = "Password incorrect";
            Controller::redirect("/$controller/profile", $error);
        }
        if (!$validate->password($_POST["new_password"])) {
            $error["newpassworderr"] = "Strong password required<br> Combine least 8 of the following: uppercase letters,lowercase letters,numbers and symbols";
            Controller::redirect("/$controller/profile", $error);
        } else {
            $user->changePassword($uid, $data);
            $this->addActivity("You changed your password");
            Controller::redirect("/$controller/profile");
        }
    }

    public function updateProfilePic()
    {
        // Controller::validateForm(["profile_pic"]);
        Controller::accessCheck(["admin", "registered_user"]);
        $controller = $this->getController();
        $user = new $controller();
        $uid = $_SESSION["user"]["uid"];
        $data = ["uid" => $uid, "profile_pic" => $_FILES['profile_pic']];
        $user->changeProfilePic($data);
        $this->addActivity("You changed your profile picture");
    }

    public function updateUsername()
    {
        Controller::validateForm(["username"]);
        $controller = $this->getController();
        $user = new $controller();
        $User = new UserController();
        $uid = $_SESSION["user"]["uid"];
        $user->changeUsername($uid, $_POST['username']);
        $this->addActivity("You changed your username");
        Controller::redirect("/$controller/profile");
    }

    public function updateContactNumber()
    {
        Controller::validateForm(["contact_number"]);
        $controller = $this->getController();
        $validate = new Validation();
        $user = new $controller();
        $uid = $_SESSION["user"]["uid"];
        $data = ["contact_number" => $_POST['contact_number']];
        if (!$validate->telephone($_POST["contact_number"])) {
            $error["telephoneerr"] = "Valid telephone number required !";
        }
        if (isset($error)) {

            View::render('profile', $error);
        } else
            $user->changeContactNumber($uid, $data);
        $this->addActivity("You changed your contact number");
        Controller::redirect("/$controller/profile");
    }

    public function updateEmail()
    {
        Controller::validateForm(["email"]);
        $controller = $this->getController();
        $user = new $controller();
        $validate = new Validation();
        $uid = $_SESSION["user"]["uid"];
        $data = ["email" => $_POST['email']];
        if (!$validate->email($_POST["email"])) {
            $error["invaliderr"] = "Invalid email";
        }
        if ((new User)->checkUserEmail($_POST["email"])) {
            $error["emailErr"] = "Email already taken";
        }
        if (isset($error["invaliderr"])) {
            Controller::redirect("/$controller/profile", $error);
        } else
            $user->changeEmail($uid, $data);
        $this->addActivity("You changed your email");
        Controller::redirect("/$controller/profile");
    }

    function addActivity($activity, $event_id = -1)
    {
        $user = new User();
        $user->insertActivity($activity, $event_id);
    }

    function viewActivityLog()
    {
        $uid = $_SESSION['user']['uid'];
        $user_roles = Controller::accessCheck(["admin", "registered_user", "organization"]);
        $pagination = Model::pagination("activity_log", 10, " WHERE uid = :uid", ["uid" => $uid]);
        $user = new User();
        $data['activities'] = $user->getActivity(["uid" => $uid, "offset" => $pagination["offset"], "no_of_records_per_page" => $pagination["no_of_records_per_page"]]);
        View::render("history", array_merge($data, $pagination), $user_roles);
    }


    function removeActivity()
    {
        $user = new User();
        $user->deleteActivity($_POST['time_stamp']);
        Controller::redirect("viewActivityLog");
    }

    public function notifications()
    {
        $user_roles = Controller::accessCheck(["registered_user"]);
        (new User)->setNotificationViewed();
        View::render("notification", [], $user_roles);
    }

    function sendNotifications($notification, $uid, $status, $path, $event_id = -1, $body_file, $data, $subject)
    {
        $user = new User();
        $email = new Mail();
        $user_details = $user->getDetails($uid);
        $data["receiver_name"] = $user_details['username'];
        $user->insertNotification($notification, $uid, $status, $path, $event_id);
        $email->notificationEmail($user_details['email'], $body_file, $data, $subject);
    }

    function checkNotificationViewed()
    {
        $user = new User();
        $not_viewed = $user->getNotificationsViewed();
        echo json_encode($not_viewed);
    }
    function getNotifications()
    {
        $user = new User();
        $notifications = $user->getNotifications();
        echo json_encode($notifications);
    }

    function checkEmailAvailable()
    {
        Controller::validateForm(["email"]);
        if ((new Validation)->email($_POST["email"]));
        echo json_encode(array("taken" => (new User)->checkUserEmail($_POST["email"])));
    }

    function removeUser($uid)
    {
        $protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === 0 ? 'https://' : 'http://';
        $DOMAIN = $protocol . $_SERVER['HTTP_HOST'];
        if (stripos($uid, "ORG")!==false) {
            $events = (new Events)->query(["org_uid" => $uid]);
            foreach ($events as $event)
                Controller::send_post_request($DOMAIN . "/Event/remove", ["event_id" => $event['event_id']]);
           
        } elseif (stripos($uid, "REG")!==false) {
            $administrations = (new RegisteredUser)->getAdministrations(["uid" => $uid, "offset" => 0, "no_of_records_per_page" => 18446744073709551615]);
            foreach ($administrations as $administration) {
                if ($administration["moderator_flag"])
                    Controller::send_post_request($DOMAIN . "/Organisation/deleteUserRole?event_id" . $administration["event_id"], ["role" => "Moderator", "uid" => $uid]);
                if ($administration["treasurer_flag"])
                    Controller::send_post_request($DOMAIN . "/Organisation/deleteUserRole?event_id" . $administration["event_id"], ["role" => "Treasurer", "uid" => $uid]);
            }
        }
        $user = new User();
        $user->removeUser($uid);
    }
}
